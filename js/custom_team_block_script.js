( function( blocks, editor, i18n, element, components ) {
	var el = element.createElement;
	var ServerSideRender = components.ServerSideRender;
	var InspectorControls = editor.InspectorControls;
	var TextControl = components.TextControl;
	var TextareaControl = components.TextareaControl;
	blocks.registerBlockType( 'custom-team-block/team', {
		title: i18n.__( 'Custom Team Block', 'custom-team-block' ),
		icon: 'groups',
		description: i18n.__( 'Block displays posts from CPT Team.', 'custom-team-block' ),
		category: 'layout',
		supports: {
			anchor: false,
			customClassName: false,
		},
		attributes: {
			'block_title': {
				type: 'string',
			},
			'block_description': {
				type: 'string',
			},
		},
		edit: function( props ) {
			return [
				el( ServerSideRender, {
					block: 'custom-team-block/team',
					attributes:  props.attributes
				} ),
				el( InspectorControls,
					{}, [
						el( "hr", {} ),
						el( TextControl, {
							label: i18n.__( 'Block Title', 'custom-team-block' ),
							value: props.attributes.block_title,
							onChange: ( value ) => {
								props.setAttributes( { block_title: value } );
							}
						} ),
						el( TextareaControl, {
							label: i18n.__( 'Block Description', 'custom-team-block' ),
							value: props.attributes.block_description,
							onChange: ( value ) => {
								props.setAttributes( { block_description: value } );
							}
						} ),
					]
				)
			];
		},
		save: function() {
			// Rendering in PHP
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.editor,
	window.wp.i18n,
	window.wp.element,
	window.wp.components,
);
