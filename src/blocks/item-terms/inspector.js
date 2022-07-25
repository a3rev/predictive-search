/**
 * Internal dependencies
 */

/**
 * Inspector controls
 */

const { __ } = wp.i18n;

const {
	PanelBody,
	TextControl,
} = wp.components;

const {
	InspectorControls,
} = wp.blockEditor || wp.editor;

const { Component } = wp.element;

export default class Inspector extends Component {
	render() {
		const {
			attributes: {
				separator,
			},
			setAttributes,
		} = this.props;

		return (
			<InspectorControls>
				<PanelBody title={ __( 'Settings' ) }>
					<TextControl
						autoComplete="off"
						label={ __( 'Separator' ) }
						value={ separator || '' }
						onChange={ ( nextValue ) => {
							setAttributes( { separator: nextValue } );
						} }
						help={ __( 'Enter character(s) used to separate terms.' ) }
					/>
				</PanelBody>
			</InspectorControls>
	 	);
	}
}
