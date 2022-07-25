/*
 * Inspector Settings
 */
import BorderControl from '@bit/a3revsoftware.blockpress.border';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	ColorIndicator,
	RadioControl,
	ToggleControl,
} = wp.components;
const { ColorPalette, InspectorControls } = wp.blockEditor;

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			containerWide,
			borderColor,
			enableCustomBorder,
		} = attributes;

		const containerColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ borderColor && ( <ColorIndicator colorValue={ borderColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Dropdown Container' ) }
					initialOpen={ false }
				>
					<RadioControl
						label={ __( 'Wide' ) }
						options={ [
							{ value: 'full', label: __( 'Full Wide' ) },
							{ value: 'input', label: __( 'Input Wide' ) },
						] }
						help={
							'full' === containerWide ?
								__( 'Results Dropdown has wide equal with Search Bar above' ) :
								__( 'Results Dropdown has wide equal with Search Input above' )
						}
						selected={ containerWide }
						onChange={ value => setAttributes( { containerWide: value } ) }
					/>
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ containerColors( __( 'Container Border' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ 'Border Colour' }>
						{ borderColor && ( <ColorIndicator colorValue={ borderColor } /> ) }
						<ColorPalette
							value={ borderColor }
							onChange={ value => setAttributes( { borderColor: value } )
							}
						/>
					</BaseControl>
					<ToggleControl
						label={ __( 'Enable Custom Border' ) }
						checked={ !! enableCustomBorder }
						onChange={ () =>
							setAttributes( { enableCustomBorder: ! enableCustomBorder } )
						}
					/>
					{ enableCustomBorder && (
						<BorderControl
							{ ...this.props }
							fieldName=""
							elementDisabled={ { borderColor: true } }
						/>
					) }
				</PanelBody>
			</InspectorControls>
		);
	}
}
