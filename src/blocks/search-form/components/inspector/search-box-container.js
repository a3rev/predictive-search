/**
 * Internal dependencies
 */
import map from 'lodash/map';

import TabPanelScreensControl from '@bit/a3revsoftware.blockpress.tab-panel-screens';

import { MarginControl, IconBox } from '@bit/a3revsoftware.blockpress.spacing';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	RangeControl,
	ToggleControl,
	__experimentalToggleGroupControl: ToggleGroupControl,
	__experimentalToggleGroupControlOption: ToggleGroupControlOption,
} = wp.components;

/**
 * Inspector controls
 */
export default class InspectorSearchBoxContainer extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const widthUnitList = [ { key: 'px', name: __( 'px' ) }, { key: '%', name: __( '%' ) } ];

		return (
			<PanelBody
				className="a3-blockpress-inspect-panel"
				title={ __( 'Search Box on Devices' ) }
				initialOpen={ false }
			>
				<TabPanelScreensControl editorClass="a3-blockpress-inspect-tabs-full-wide">
					{ tab => {
						const width = 'mobile' !== tab.name ? attributes[ `${ tab.name }width` ] : '';
						const widthUnit = 'mobile' !== tab.name ? attributes[ `${ tab.name }widthUnit` ] : '';
						const searchIconEnable = attributes[ `${ tab.name }mIconEnable` ];
						return (
							<Fragment>
								<ToggleControl
									label={ __( 'Show Search Icon Only' ) }
									checked={ !! searchIconEnable }
									onChange={ () => setAttributes( { [ `${ tab.name }mIconEnable` ]: ! searchIconEnable } ) }
								/>
								{ 'mobile' !== tab.name && (
									<Fragment>
										<ToggleGroupControl
											__next40pxDefaultSize
											__nextHasNoMarginBottom
											label={ __( 'Search Box Width Type' ) }
											value={ widthUnit }
											onChange={ ( value ) => setAttributes( { [ `${ tab.name }widthUnit` ]: value } ) }
											isBlock
										>
											{ map( widthUnitList, ( { name, key } ) => (
												<ToggleGroupControlOption
													key={ key }
													value={ key }
													label={ name }
												/>
											) ) }
										</ToggleGroupControl>
										<RangeControl
											__next40pxDefaultSize
											label={ __( 'Search Box Width' ) }
											value={ width }
											onChange={ value => setAttributes( { [ `${ tab.name }width` ]: value ? value : 100 } ) }
											min={ 'px' === widthUnit ? 200 : 10 }
											max={ 'px' === widthUnit ? 1000 : 100 }
											allowReset
										/>
									</Fragment>
								) }
							</Fragment>
						);
					} }
				</TabPanelScreensControl>
				<BaseControl
					className="a3-blockpress-control-spacing"
				>
					<IconBox />
					<MarginControl { ...this.props } />
				</BaseControl>
			</PanelBody>
		);
	}
}
