/*
 * Inspector Settings
 */
import {
	MarginControl,
	IconBox,
} from '@bit/a3revsoftware.blockpress.spacing';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	RangeControl,
	ColorIndicator,
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
			iconSize,
			iconColor,
		} = attributes;

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
					title={ iconColors( __( 'Icon style' ) ) }
					initialOpen={ false }
				>
					<RangeControl
						label={ __( 'Size' ) }
						value={ iconSize }
						onChange={ value => setAttributes( { iconSize: value ? value : 20 } ) }
						min={ 10 }
						max={ 50 }
						allowReset
					/>
					<BaseControl label={ __( 'Colour' ) }>
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
					<BaseControl className="a3-blockpress-control-spacing">
						<IconBox />
						<MarginControl { ...this.props } fieldName={ '' } />
					</BaseControl>
				</PanelBody>
			</InspectorControls>
		);
	}
}
