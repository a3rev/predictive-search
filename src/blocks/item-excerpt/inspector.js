/**
 * Internal dependencies
 */

/**
 * Inspector controls
 */

const { __ } = wp.i18n;

const {
	PanelBody,
	ToggleControl,
} = wp.components;

const {
	InspectorControls,
} = wp.blockEditor || wp.editor;

const { Component } = wp.element;

export default class Inspector extends Component {
	render() {
		const {
			attributes: {
				showMoreOnNewLine,
				linkTarget,
			},
			setAttributes,
		} = this.props;

		return (
			<InspectorControls>
				<PanelBody title={ __( 'Link settings' ) }>
					<ToggleControl
						label={ __( 'Show link on new line' ) }
						checked={ showMoreOnNewLine }
						onChange={ ( newShowMoreOnNewLine ) =>
							setAttributes( {
								showMoreOnNewLine: newShowMoreOnNewLine,
							} )
						}
					/>
					<ToggleControl
						label={ __( 'Open in new tab' ) }
						onChange={ ( value ) =>
							setAttributes( {
								linkTarget: value ? '_blank' : '_self',
							} )
						}
						checked={ linkTarget === '_blank' }
					/>
				</PanelBody>
			</InspectorControls>
	 	);
	}
}
