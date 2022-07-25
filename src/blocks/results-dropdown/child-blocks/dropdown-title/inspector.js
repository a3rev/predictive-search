/*
 * Inspector Settings
 */
import TypographyControl from '@bit/a3revsoftware.blockpress.typography';

import {
	PaddingControl,
	IconBox,
} from '@bit/a3revsoftware.blockpress.spacing';

import BorderControl, { currentBorderValue } from '@bit/a3revsoftware.blockpress.border';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	ColorIndicator,
	ToggleControl,
} = wp.components;
const { ColorPalette } = wp.blockEditor;
const { InspectorControls } = wp.blockEditor;

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			textColor,
			backgroundColor,
			enableCustomFont,
			enableCustomBorder,
		} = attributes;

		const titleColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ textColor && ( <ColorIndicator colorValue={ textColor } /> ) }
					{ backgroundColor && ( <ColorIndicator colorValue={ backgroundColor } /> ) }
				</Fragment>
			);
		};

		const borderColors = ( title ) => {
			const borderColor = currentBorderValue( '', attributes, 'Color' );
			return (
				<Fragment>
					{ title }
					{ enableCustomBorder && borderColor && ( <ColorIndicator colorValue={ borderColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ titleColors( __( 'Colours' ) ) }
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
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Title Font' ) }
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
							optionName={ 'typo' }
							{ ...this.props }
							elementDisabled={ { color: true } }
						/>
					) }
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ borderColors( __( 'Container Border & Spacing' ) ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Enable Border' ) }
						checked={ !! enableCustomBorder }
						onChange={ () =>
							setAttributes( { enableCustomBorder: ! enableCustomBorder } )
						}
					/>
					{ enableCustomBorder && (
						<BorderControl
							{ ...this.props }
							fieldName={ '' }
							elementDisabled={ { borderRadius: true } }
						/>
					) }
					<BaseControl className="a3-blockpress-control-spacing">
						<IconBox />
						<PaddingControl { ...this.props } fieldName={ '' } />
					</BaseControl>
				</PanelBody>
			</InspectorControls>
		);
	}
}
