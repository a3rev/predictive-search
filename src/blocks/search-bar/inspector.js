/*
 * Inspector Settings
 */
import BorderControl from '@bit/a3revsoftware.blockpress.border';

import ShadowContol from '@bit/a3revsoftware.blockpress.shadow';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, BaseControl, RangeControl, ColorIndicator, ToggleControl, RadioControl } = wp.components;
const { InspectorControls, ColorPalette } = wp.blockEditor;

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const { layout, height, enableCustomBorder, borderColor, borderFocusColor } = attributes;

		const currentColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ borderColor && ( <ColorIndicator colorValue={ borderColor } /> ) }
					{ borderFocusColor && ( <ColorIndicator colorValue={ borderFocusColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Layout' ) }
					initialOpen={ false }
				>
					<RadioControl
						label={ __( 'Choose Layout' ) }
						selected={ layout }
						options={ [
							{ label: __( 'Category - Submit' ), value: 'cat-submit' },
							{ label: __( 'Submit - Category' ), value: 'submit-cat' },
						] }
						help={
							'submit-cat' === layout ?
								__( 'Predictive Search Button on LEFT and Category Dropdown on RIGHT' ) :
								__( 'Category Dropdown on LEFT and Predictive Search Button on RIGHT' )
						}
						onChange={ value => setAttributes( { layout: value } ) }
					/>
					<RangeControl
						label={ __( 'Search Bar Height' ) }
						value={ height }
						onChange={ value => setAttributes( { height: value ? value : 35 } ) }
						min={ 10 }
						max={ 100 }
						allowReset
					/>
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ currentColors( __( 'Border' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ __( 'Border Color' ) }>
						{ borderColor && <ColorIndicator colorValue={ borderColor } /> }
						<ColorPalette
							value={ borderColor }
							onChange={ value => setAttributes( { borderColor: value } ) }
						/>
					</BaseControl>
					<BaseControl label={ __( 'Border Focus Color' ) }>
						{ borderFocusColor && <ColorIndicator colorValue={ borderFocusColor } /> }
						<ColorPalette
							value={ borderFocusColor }
							onChange={ value => setAttributes( { borderFocusColor: value } ) }
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
						<BorderControl { ...this.props } elementDisabled={ { borderColor: true } } />
					) }

				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Shadow' ) }
					initialOpen={ false }
				>
					<ShadowContol
						label={ __( 'Shadow' ) }
						heading={ __( 'Shadow' ) }
						optionName={ 'shadow' }
						help=""
						{ ...this.props }
					/>
				</PanelBody>
			</InspectorControls>
		);
	}
}
