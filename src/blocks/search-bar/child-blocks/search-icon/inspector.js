/*
 * Inspector Settings
 */
import IconControl from '@bit/a3revsoftware.blockpress.icons';
import BorderControl from '@bit/a3revsoftware.blockpress.border';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	TabPanel,
	RangeControl,
	ColorIndicator,
	ToggleControl,
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
];

const fieldName = '';

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			iconSize,
			normalIconColor,
			normalBackgroundColor,
			normalBorderColor,
			enableCustomBorder,
		} = attributes;

		const currentColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ normalIconColor && ( <ColorIndicator colorValue={ normalIconColor } /> ) }
					{ normalBackgroundColor && ( <ColorIndicator colorValue={ normalBackgroundColor } /> ) }
					{ normalBorderColor && ( <ColorIndicator colorValue={ normalBorderColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Search Icon' ) }
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
						onChange={ value => setAttributes( { iconSize: value ? value : 16 } ) }
						min={ 10 }
						max={ 50 }
						allowReset
					/>
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ currentColors( __( 'Colours' ) ) }
					initialOpen={ false }
				>
					<TabPanel
						className="a3-blockpress-inspect-tabs"
						activeClass="active-tab"
						tabs={ tabList }
					>
						{ tab => {
							const iconColorKey = `${ tab.name }IconColor`;
							const bgColorKey = `${ tab.name }BackgroundColor`;
							const borderColorKey = `${ tab.name }BorderColor`;

							return (
								<Fragment>
									<BaseControl label={ 'Icon Colour' }>
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
									<BaseControl label={ 'Background Colour' }>
										{ attributes[ bgColorKey ] && ( <ColorIndicator colorValue={ attributes[ bgColorKey ] } /> ) }
										<ColorPalette
											value={ attributes[ bgColorKey ] }
											onChange={ value =>
												setAttributes( {
													[ bgColorKey ]: value,
												} )
											}
										/>
									</BaseControl>
									<BaseControl label={ 'Border Colour' }>
										{ attributes[ borderColorKey ] && ( <ColorIndicator colorValue={ attributes[ borderColorKey ] } /> ) }
										<ColorPalette
											value={ attributes[ borderColorKey ] }
											onChange={ value =>
												setAttributes( {
													[ borderColorKey ]: value,
												} )
											}
										/>
									</BaseControl>
								</Fragment>
							);
						} }
					</TabPanel>
				</PanelBody>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Container Border' ) }
					initialOpen={ false }
				>
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
							fieldName={ fieldName }
							elementDisabled={ { borderColor: true } }
						/>
					) }
				</PanelBody>
			</InspectorControls>
		);
	}
}
