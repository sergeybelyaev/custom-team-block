<?php
/*
Plugin Name: Custom Team Block
Text Domain: custom-team-block
Domain Path: /languages/
*/

function custom_team_block_load_plugin_textdomain() {
	load_plugin_textdomain( 'custom-team-block', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
  
add_action( 'plugins_loaded', 'custom_team_block_load_plugin_textdomain' );

function custom_team_block_init() {

	$labels = array(
		'name' => __( 'Team', 'custom-team-block' ),
		'singular_name' => __( 'Team Member', 'custom-team-block' ),
		'add_new' => __( 'Add New', 'custom-team-block' ),
		'add_new_item' => __( 'Add New Team Member', 'custom-team-block' ),
		'edit_item' => __( 'Edit Team Member', 'custom-team-block' ),
		'new_item' => __( 'New Team Member', 'custom-team-block' ),
		'all_items' => __( 'All Team Members', 'custom-team-block' ),
		'view_item' => __( 'View Team Member', 'custom-team-block' ),
		'search_items' => __( 'Search Team Members', 'custom-team-block' ),
		'not_found' => __( 'No Team Members found', 'custom-team-block' ),
		'not_found_in_trash' => __( 'No Team Members in Trash', 'custom-team-block' ),
		'parent_item_colon' => '',
		'menu_name' => __( 'Team', 'custom-team-block' ),
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'team' ),
		'capability_type' => 'post',
		'has_archive' => false,
		'hierarchical' => false,
        'menu_position' => null,
        'menu_icon' => 'dashicons-groups',
		'supports' => array( 'title', 'editor', 'thumbnail' ),
		'show_in_rest' => false,
	);
	register_post_type( 'team', $args );

    wp_register_script(
        'custom_team_block_script',
        plugins_url( 'js/custom_team_block_script.js', __FILE__ ),
        array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' )
    );

    wp_register_style(
        'custom_team_block_style',
        plugins_url( 'css/custom_team_block_style.css', __FILE__ ),
        array( 'wp-edit-blocks' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'css/custom_team_block_style.css' )
    );

    register_block_type( 'custom-team-block/team', array(
        'editor_script' => 'custom_team_block_script',
		'style'  => 'custom_team_block_style',
		'render_callback' => 'custom_team_block_render_block_team',
		'attributes'      => array(
            'block_title'    => array(
                'type'      => 'string',
			),
			'block_description'    => array(
                'type'      => 'string',
			),
        ),
    ) );

}
add_action( 'init', 'custom_team_block_init' );

function custom_team_block_render_block_team( $attributes, $content ) {
	$team_posts = get_posts( array(
		'post_type' => 'team',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'order' => 'ASC',
		'orderby' => 'meta_value_num',
		'meta_key' => 'team_member_order_number',
		'meta_query' => array(
			'relation' => 'OR',
			array(
				'key'=>'team_member_order_number',
				'compare' => 'EXISTS',       
			),
			array( 
				'key'=>'team_member_order_number',
				'compare' => 'NOT EXISTS',
			)
		),
		'post_per_page'=>-1
	) );
	ob_start();
	if ( $team_posts ) : ?>
		<div class="wp-block-custom-team-block">
			<div class="container">
				<?php if ( ! empty( $attributes['block_title'] ) ) : ?>
					<h2><?php echo esc_html( $attributes['block_title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( ! empty( $attributes['block_description'] ) ) : ?>
					<?php echo wpautop( $attributes['block_description'] ); ?>
				<?php endif; ?>
				<div class="row">
					<?php foreach ( $team_posts as $p ) : ?>
						<div class="box">
							<?php echo get_the_post_thumbnail( $p->ID, 'thumbnail' ); ?>
							<h3><?php echo get_the_title( $p->ID ); ?></h3>
							<?php if ( $team_member_postition = get_post_meta( $p->ID, 'team_member_postition', true ) ) : ?>
								<h4><?php echo esc_html( $team_member_postition ); ?></h4>
							<?php endif; ?>
							<?php echo wpautop( $p->post_content ); ?>
							<?php if ( $team_member_linkedin = get_post_meta( $p->ID, 'team_member_linkedin', true ) ) : ?>
								<a href="<?php echo esc_url( $team_member_linkedin ); ?>" target="_blank">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M4.98 3.5c0 1.381-1.11 2.5-2.48 2.5s-2.48-1.119-2.48-2.5c0-1.38 1.11-2.5 2.48-2.5s2.48 1.12 2.48 2.5zm.02 4.5h-5v16h5v-16zm7.982 0h-4.968v16h4.969v-8.399c0-4.67 6.029-5.052 6.029 0v8.399h4.988v-10.131c0-7.88-8.922-7.593-11.018-3.714v-2.155z"/></svg>
								</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	<?php endif;
	$data = ob_get_contents();
	ob_end_clean();
	return $data;
}

function custom_team_block_add_team_metaboxes() {
	add_meta_box(
		'team_member_fields',
		__( 'Team member fields', 'custom-team-block' ),
		'custom_team_block_team_member_metaboxes_html',
		'team',
		'side',
		'low'
	);
}

add_action( 'add_meta_boxes', 'custom_team_block_add_team_metaboxes' );

function custom_team_block_team_member_metaboxes_html() {
	global $post;
	wp_nonce_field( basename( __FILE__ ), 'custom_team_block_metaboxes' );
	$team_member_postition = get_post_meta( $post->ID, 'team_member_postition', true );
	echo '<p><label>' . __( 'Team member position', 'custom-team-block' );
	echo '<input type="text" name="team_member_postition" value="' . esc_attr( $team_member_postition )  . '" class="widefat"></label></p>';
	$team_member_order_number = get_post_meta( $post->ID, 'team_member_order_number', true );
	echo '<p><label>' . __( 'Team member order number', 'custom-team-block' );
	echo '<input type="number" name="team_member_order_number" value="' . esc_attr( $team_member_order_number )  . '" class="widefat" min="1"></label></p>';
	$team_member_linkedin = get_post_meta( $post->ID, 'team_member_linkedin', true );
	echo '<p><label>' . __( 'Team member LinkedIn', 'custom-team-block' );
	echo '<input type="url" name="team_member_linkedin" value="' . esc_attr( $team_member_linkedin )  . '" class="widefat"></label></p>';
}

function custom_team_block_team_member_save_meta( $post_id, $post ) {
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
<<<<<<< HEAD
=======
	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
>>>>>>> 27e31c67535071584cdfe1f1f315d4b70cc83492
	if ( ! ( isset( $_POST['team_member_postition'] ) || isset( $_POST['team_member_order_number'] ) ) || ! wp_verify_nonce( $_POST['custom_team_block_metaboxes'], basename(__FILE__) ) ) {
		return $post_id;
	}
	$team_member_meta['team_member_postition'] = sanitize_text_field( $_POST['team_member_postition'] );
	$team_member_meta['team_member_order_number'] = intval( $_POST['team_member_order_number'] );
	$team_member_meta['team_member_linkedin'] = esc_url_raw( $_POST['team_member_linkedin'] );
<<<<<<< HEAD
=======
	// Cycle through the $team_member_meta array.
	// Note, in this example we just have one item, but this is helpful if you have multiple.
>>>>>>> 27e31c67535071584cdfe1f1f315d4b70cc83492
	foreach ( $team_member_meta as $key => $value ) :
		if ( 'revision' === $post->post_type ) {
			return;
		}
		if ( get_post_meta( $post_id, $key, false ) ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			add_post_meta( $post_id, $key, $value);
		}
		if ( ! $value ) {
			delete_post_meta( $post_id, $key );
		}
	endforeach;
}
add_action( 'save_post', 'custom_team_block_team_member_save_meta', 1, 2 );