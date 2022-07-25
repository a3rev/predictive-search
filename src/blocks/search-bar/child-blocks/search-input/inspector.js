/*
 * Inspector Settings
 */
import TypographyControl from '@bit/a3revsoftware.blockpress.typography';

import {
	PaddingControl,
	IconBox,
} from '@bit/a3revsoftware.blockpress.spacing';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	RangeControl,
	ColorIndicator,
	ToggleControl,
} = wp.components;
const { ColorPalette } = wp.blockEditor;
const { InspectorControls } = wp.blockEditor;

const fieldName = '';

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			textColor,
			backgroundColor,
			iconColor,
			iconSize,
			enableCustomFont,
		} = attributes;

		const currentColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ textColor && ( <ColorIndicator colorValue={ textColor } /> ) }
					{ backgroundColor && ( <ColorIndicator colorValue={ backgroundColor } /> ) }
				</Fragment>
			);
		};

		const iconColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ iconColor && ( <ColorIndicator colorValue={ iconColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ currentColors( __( 'Colours & Spacing' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ 'Text Colour' }>
						{ textColor && ( <ColorIndicator colorValue={ textColor } /> ) }
						<ColorPalette
							value={ textColor }
							onChange={ value =>
								setAttributes( {
									textColor: value,
								} )
							}
						/>
					</BaseControl>
					<BaseControl label={ 'Background Colour' }>
						{ backgroundColor && ( <ColorIndicator colorValue={ backgroundColor } /> ) }
						<ColorPalette
							value={ backgroundColor }
							onChange={ value =>
								setAttributes( {
									backgroundColor: value,
								} )
							}
						/>
					</BaseControl>
					<BaseControl className="a3-blockpress-control-spacing">
						<IconBox />
						<PaddingControl { ...this.props } fieldName={ fieldName } />
					</BaseControl>

				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Search Text Font' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Enable Custom Font' ) }
						checked={ !! enableCustomFont }
						onChange={ () =>
							setAttributes( { enableCustomFont: ! enableCustomFont } )
						}
					/>
					{ enableCustomFont && (
						<TypographyControl
							elementDisabled={ { color: true } }
							optionName="typo"
							{ ...this.props }
						/>
					) }
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ iconColors( __( 'Loading Icon' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ 'Loading Icon Colour' }>
						{ iconColor && ( <ColorIndicator colorValue={ iconColor } /> ) }
						<ColorPalette
							value={ iconColor }
							onChange={ value =>
								setAttributes( {
									iconColor: value,
								} )
							}
						/>
					</BaseControl>
					<RangeControl
						label={ __( 'Size' ) }
						value={ iconSize }
						onChange={ value => setAttributes( { iconSize: value ? value : 12 } ) }
						min={ 6 }
						max={ 100 }
						allowReset
					/>
				</PanelBody>
			</InspectorControls>
		);
	}
}
