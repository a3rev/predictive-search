/**
 * Internal dependencies
 */
import map from 'lodash/map';

import TabPanelScreensControl from '@bit/a3revsoftware.blockpress.tab-panel-screens';

import { MarginControl, IconBox } from '@bit/a3revsoftware.blockpress.spacing';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { PanelBody, BaseControl, ButtonGroup, Button, RangeControl, ToggleControl } = wp.components;

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
										<ButtonGroup
											className="a3-blockpress-size-type-options"
											aria-label={ __( 'Search Box Width Type' ) }
										>
											{ map( widthUnitList, ( { name, key } ) => (
												<Button
													key={ key }
													className="size-type-btn"
													isSmall
													isPrimary={ widthUnit === key }
													aria-pressed={ widthUnit === key }
													onClick={ () => setAttributes( { [ `${ tab.name }widthUnit` ]: key } ) }
												>
													{ name }
												</Button>
											) ) }
										</ButtonGroup>
										<RangeControl
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
