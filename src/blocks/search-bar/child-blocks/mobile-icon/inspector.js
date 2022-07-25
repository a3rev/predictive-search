/*
 * Inspector Settings
 */
import IconControl from '@bit/a3revsoftware.blockpress.icons';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	TabPanel,
	RangeControl,
	ColorIndicator,
} = wp.components;
const { ColorPalette } = wp.blockEditor;
const { InspectorControls } = wp.blockEditor;

const tabList = [
	{
		name: 'normal',
		title: __( 'Normal' ),
		label: __( 'Normal' ),
		className: 'a3-blockpress-inspect-tab',
	},
	{
		name: 'hover',
		title: __( 'Hover' ),
		label: __( 'Hover' ),
		className: 'a3-blockpress-inspect-tab',
	},
	{
		name: 'active',
		title: __( 'Active' ),
		label: __( 'Active' ),
		className: 'a3-blockpress-inspect-tab',
	},
];

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			iconSize,
			normalIconColor,
			hoverIconColor,
			activeIconColor,
		} = attributes;

		const currentColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ normalIconColor && ( <ColorIndicator colorValue={ normalIconColor } /> ) }
					{ hoverIconColor && ( <ColorIndicator colorValue={ hoverIconColor } /> ) }
					{ activeIconColor && ( <ColorIndicator colorValue={ activeIconColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ currentColors( __( 'Icon style' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ __( 'Choose Icon' ) }>
						<IconControl
							optionName="icon"
							isMulti={ false }
							{ ...this.props }
						/>
					</BaseControl>
					<RangeControl
						label={ __( 'Size' ) }
						value={ iconSize }
						onChange={ value => setAttributes( { iconSize: value ? value : 25 } ) }
						min={ 10 }
						max={ 50 }
						allowReset
					/>
					<BaseControl label={ __( 'Colour' ) }>
						<TabPanel
							className="a3-blockpress-inspect-tabs"
							activeClass="active-tab"
							tabs={ tabList }
						>
							{ tab => {
								const iconColorKey = `${ tab.name }IconColor`;

								return (
									<BaseControl label={ 'Colour' }>
										{ attributes[ iconColorKey ] && ( <ColorIndicator colorValue={ attributes[ iconColorKey ] } /> ) }
										<ColorPalette
											value={ attributes[ iconColorKey ] }
											onChange={ value =>
												setAttributes( {
													[ iconColorKey ]: value,
												} )
											}
										/>
									</BaseControl>
								);
							} }
						</TabPanel>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
		);
	}
}
