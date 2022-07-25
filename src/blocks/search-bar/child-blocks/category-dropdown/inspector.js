/*
 * Inspector Settings
 */
import TypographyControl from '@bit/a3revsoftware.blockpress.typography';
import IconControl from '@bit/a3revsoftware.blockpress.icons';
import BorderControl from '@bit/a3revsoftware.blockpress.border';

const { applyFilters } = wp.hooks;
const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	TabPanel,
	RangeControl,
	ColorIndicator,
	Disabled,
	ToggleControl,
	SelectControl,
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

const taxonomies = applyFilters( 'wpps.taxonomies_dropdown', [ { value: 'category', label: __( 'Post Category' ) } ] );


/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			enable,
			taxonomy,
			defaultCategory,
			maxWidth,
			enableCustomFont,
			enableCustomBorder,
			iconSize,
			normalIconColor,
			hoverIconColor,
			normalTextColor,
			normalBackgroundColor,
			normalBorderColor,
		} = attributes;

		let categoryList = [
			{
				label: __( 'All' ),
				value: '',
			},
		];

		if ( 0 != predictive_search_vars.taxonomy_terms[taxonomy] ) {
			categoryList = [ ...categoryList, ...JSON.parse( predictive_search_vars.taxonomy_terms[taxonomy] ) ];
		}

		const currentColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ normalTextColor && ( <ColorIndicator colorValue={ normalTextColor } /> ) }
					{ normalBackgroundColor && ( <ColorIndicator colorValue={ normalBackgroundColor } /> ) }
					{ normalBorderColor && ( <ColorIndicator colorValue={ normalBorderColor } /> ) }
				</Fragment>
			);
		};

		const currentIconColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ normalIconColor && ( <ColorIndicator colorValue={ normalIconColor } /> ) }
					{ hoverIconColor && ( <ColorIndicator colorValue={ hoverIconColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Category Dropdown' ) }
				>
					{ 1 == predictive_search_vars.disabled_cat_dropdown ? (
						<Fragment>
							<Disabled>
								<ToggleControl
									label={ __( 'Enable Category Dropdown' ) }
									help={ __( 'Hiding the Category Dropdown in Search Box.' ) }
									checked={ false }
								/>
							</Disabled>
							<p>
								{ __( 'Activate and build ' ) }
								<a
									href="admin.php?page=wp-predictive-search&tab=search-box-settings&box_open=predictive_search_category_cache_box#predictive_search_category_cache_box"
									target="_blank"
								>
									{ __( 'Category Cache' ) }
								</a>
								{ __( ' to activate this feature' ) }
							</p>
						</Fragment>
					) : (
						<ToggleControl
							label={ __( 'Enable Category Dropdown' ) }
							help={
								enable ?
									__( 'Showing the Category Dropdown in Search Box.' ) :
									__( 'Hiding the Category Dropdown in Search Box.' )
							}
							checked={ !! enable }
							onChange={ () => setAttributes( { enable: ! enable } ) }
						/>
					) }
					{ 1 != predictive_search_vars.disabled_cat_dropdown && enable && (
						<Fragment>
							<SelectControl
								label={ __( 'Select Taxonomy' ) }
								help={ __( 'Select a taxonomy for Category Dropdown' ) }
								value={ taxonomy }
								onChange={ value => setAttributes( { taxonomy: value } ) }
								options={ taxonomies }
							/>
							<SelectControl
								label={ __( 'Default Category' ) }
								help={ __( 'Set category as default selected category for Category Dropdown' ) }
								value={ defaultCategory }
								onChange={ value => setAttributes( { defaultCategory: value } ) }
								options={ categoryList }
							/>
							<RangeControl
								label={ __( 'Maximum Width' ) }
								help={ __( '% width of Search Box' ) }
								value={ maxWidth }
								onChange={ value => setAttributes( { maxWidth: value ? value : 30 } ) }
								min={ 10 }
								max={ 50 }
								allowReset
							/>
						</Fragment>
					) }
				</PanelBody>
				{ 1 != predictive_search_vars.disabled_cat_dropdown && enable && (
					<Fragment>
						<PanelBody
							className="a3-blockpress-inspect-panel"
							title={ currentColors( __( 'Colours' ) ) }
							initialOpen={ false }
						>
							<BaseControl>
								<TabPanel
									className="a3-blockpress-inspect-tabs"
									activeClass="active-tab"
									tabs={ tabList }
								>
									{ tab => {
										const textColorKey = `${ tab.name }TextColor`;
										const bgColorKey = `${ tab.name }BackgroundColor`;
										const borderColorKey = `${ tab.name }BorderColor`;

										return (
											<Fragment>
												<BaseControl label={ 'Text Colour' }>
													{ attributes[ textColorKey ] && ( <ColorIndicator colorValue={ attributes[ textColorKey ] } /> ) }
													<ColorPalette
														value={ attributes[ textColorKey ] }
														onChange={ value =>
															setAttributes( {
																[ textColorKey ]: value,
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
							</BaseControl>
						</PanelBody>
						<PanelBody
							className="a3-blockpress-inspect-panel"
							title={ __( 'Category Text Font' ) }
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
						<PanelBody
							className="a3-blockpress-inspect-panel"
							title={ currentIconColors( __( 'Down Icon' ) ) }
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
								onChange={ value => setAttributes( { iconSize: value ? value : 12 } ) }
								min={ 6 }
								max={ 50 }
								allowReset
							/>
							<BaseControl>
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
					</Fragment>
				) }
			</InspectorControls>
		);
	}
}
