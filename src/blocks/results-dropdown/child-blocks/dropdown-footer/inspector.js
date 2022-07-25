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

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			moreTextColor,
			moreLinkColor,
			backgroundColor,
			enableCustomMoreTextFont,
			enableCustomMoreLinkFont,
			moreIconSize,
			moreIconColor,
		} = attributes;

		const footerColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ moreTextColor && ( <ColorIndicator colorValue={ moreTextColor } /> ) }
					{ moreLinkColor && ( <ColorIndicator colorValue={ moreLinkColor } /> ) }
					{ backgroundColor && ( <ColorIndicator colorValue={ backgroundColor } /> ) }
				</Fragment>
			);
		};

		const iconColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ moreIconColor && ( <ColorIndicator colorValue={ moreIconColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ footerColors( __( 'Colours & Spacing' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ 'More Text Colour' }>
						{ moreTextColor && ( <ColorIndicator colorValue={ moreTextColor } /> ) }
						<ColorPalette
							value={ moreTextColor }
							onChange={ value =>
								setAttributes( {
									moreTextColor: value,
								} )
							}
						/>
					</BaseControl>
					<BaseControl label={ 'More Link Colour' }>
						{ moreLinkColor && ( <ColorIndicator colorValue={ moreLinkColor } /> ) }
						<ColorPalette
							value={ moreLinkColor }
							onChange={ value =>
								setAttributes( {
									moreLinkColor: value,
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
						<PaddingControl { ...this.props } fieldName={ '' } />
					</BaseControl>
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'More Text Font' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Enable Custom More Text Font' ) }
						checked={ !! enableCustomMoreTextFont }
						onChange={ () =>
							setAttributes( { enableCustomMoreTextFont: ! enableCustomMoreTextFont } )
						}
					/>
					{ enableCustomMoreTextFont && (
						<TypographyControl
							optionName="moreTextTypo"
							{ ...this.props }
							elementDisabled={ { color: true } }
						/>
					) }
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'More Link Font' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Enable Custom More Link Font' ) }
						checked={ !! enableCustomMoreLinkFont }
						onChange={ () =>
							setAttributes( { enableCustomMoreLinkFont: ! enableCustomMoreLinkFont } )
						}
					/>
					{ enableCustomMoreLinkFont && (
						<TypographyControl
							optionName="moreLinkTypo"
							{ ...this.props }
							elementDisabled={ { color: true } }
						/>
					) }
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ iconColors( __( 'More Icon' ) ) }
					initialOpen={ false }
				>
					<RangeControl
						label={ __( 'Icon Size' ) }
						value={ moreIconSize }
						onChange={ value => setAttributes( { moreIconSize: value ? value : 12 } ) }
						min={ 6 }
						max={ 50 }
						allowReset
					/>
					<BaseControl label={ 'Icon Colour' }>
						{ moreIconColor && ( <ColorIndicator colorValue={ moreIconColor } /> ) }
						<ColorPalette
							value={ moreIconColor }
							onChange={ value =>
								setAttributes( {
									moreIconColor: value,
								} )
							}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
		);
	}
}
