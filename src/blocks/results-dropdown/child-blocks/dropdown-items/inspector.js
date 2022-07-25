/*
 * Inspector Settings
 */
import TypographyControl, {
} from '@bit/a3revsoftware.blockpress.typography';

import {
	PaddingControl,
	IconBox,
} from '@bit/a3revsoftware.blockpress.spacing';

import BorderControl, {
} from '@bit/a3revsoftware.blockpress.border';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	PanelBody,
	BaseControl,
	RangeControl,
	ColorIndicator,
	ToggleControl,
	TabPanel,
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

/**
 * Inspector controls
 */
export default class Inspector extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			normalBackgroundColor,
			hoverBackgroundColor,
			enableBorder,
			showImg,
			itemImgSize,
			normalNameColor,
			hoverNameColor,
			enableCustomNameFont,
			showDesc,
			charactersDescCount,
			normalDescColor,
			hoverDescColor,
			enableCustomDescFont,
			showCat,
			normalCatColor,
			normalCatWordColor,
			enableCustomCatFont,
		} = attributes;

		const itemColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ normalBackgroundColor && ( <ColorIndicator colorValue={ normalBackgroundColor } /> ) }
					{ hoverBackgroundColor && ( <ColorIndicator colorValue={ hoverBackgroundColor } /> ) }
				</Fragment>
			);
		};

		const nameColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ normalNameColor && ( <ColorIndicator colorValue={ normalNameColor } /> ) }
					{ hoverNameColor && ( <ColorIndicator colorValue={ hoverNameColor } /> ) }
				</Fragment>
			);
		};

		const descColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ showDesc && normalDescColor && ( <ColorIndicator colorValue={ normalDescColor } /> ) }
					{ showDesc && hoverDescColor && ( <ColorIndicator colorValue={ hoverDescColor } /> ) }
				</Fragment>
			);
		};

		const catColors = ( title ) => {
			return (
				<Fragment>
					{ title }
					{ showCat && normalCatColor && ( <ColorIndicator colorValue={ normalCatColor } /> ) }
					{ showCat && normalCatWordColor && ( <ColorIndicator colorValue={ normalCatWordColor } /> ) }
				</Fragment>
			);
		};

		return (
			<InspectorControls>
				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ itemColors( __( 'Item Container' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ __( 'Background Colour' ) }>
						<TabPanel
							className="a3-blockpress-inspect-tabs"
							activeClass="active-tab"
							tabs={ tabList }
						>
							{ tab => {
								const colorKey = `${ tab.name }BackgroundColor`;

								return (
									<BaseControl>
										{ attributes[ colorKey ] && ( <ColorIndicator colorValue={ attributes[ colorKey ] } /> ) }
										<ColorPalette
											value={ attributes[ colorKey ] }
											onChange={ value =>
												setAttributes( {
													[ colorKey ]: value,
												} )
											}
										/>
									</BaseControl>
								);
							} }
						</TabPanel>
					</BaseControl>
					<BaseControl className="a3-blockpress-control-spacing">
						<IconBox />
						<PaddingControl { ...this.props } fieldName={ '' } />
					</BaseControl>
					<ToggleControl
						label={ __( 'Enable Border' ) }
						checked={ !! enableBorder }
						onChange={ () =>
							setAttributes( { enableBorder: ! enableBorder } )
						}
					/>
					{ enableBorder && (
						<Fragment>
							<BaseControl label={ __( 'Border Colour' ) }>
								<TabPanel
									className="a3-blockpress-inspect-tabs"
									activeClass="active-tab"
									tabs={ tabList }
								>
									{ tab => {
										const colorKey = `${ tab.name }BorderColor`;

										return (
											<BaseControl>
												{ attributes[ colorKey ] && ( <ColorIndicator colorValue={ attributes[ colorKey ] } /> ) }
												<ColorPalette
													value={ attributes[ colorKey ] }
													onChange={ value =>
														setAttributes( {
															[ colorKey ]: value,
														} )
													}
												/>
											</BaseControl>
										);
									} }
								</TabPanel>
							</BaseControl>
							<BorderControl
								{ ...this.props }
								fieldName={ '' }
								elementDisabled={ { borderRadius: true, borderColor: true } }
							/>
						</Fragment>
					) }
				</PanelBody>

				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ nameColors( __( 'Item Name' ) ) }
					initialOpen={ false }
				>
					<BaseControl label={ __( 'Colour' ) }>
						<TabPanel
							className="a3-blockpress-inspect-tabs"
							activeClass="active-tab"
							tabs={ tabList }
						>
							{ tab => {
								const colorKey = `${ tab.name }NameColor`;

								return (
									<BaseControl>
										{ attributes[ colorKey ] && ( <ColorIndicator colorValue={ attributes[ colorKey ] } /> ) }
										<ColorPalette
											value={ attributes[ colorKey ] }
											onChange={ value =>
												setAttributes( {
													[ colorKey ]: value,
												} )
											}
										/>
									</BaseControl>
								);
							} }
						</TabPanel>
					</BaseControl>
					<ToggleControl
						label={ __( 'Enable Custom Font' ) }
						checked={ !! enableCustomNameFont }
						onChange={ () =>
							setAttributes( { enableCustomNameFont: ! enableCustomNameFont } )
						}
					/>
					{ enableCustomNameFont && (
						<TypographyControl
							elementDisabled={ { color: true } }
							optionName="nameTypo"
							{ ...this.props }
						/>
					) }
				</PanelBody>

				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ __( 'Item Image' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Image' ) }
						help={
							showImg ?
								__( 'Showing the Image in Search Popup Results.' ) :
								__( 'Hiding the Image in Search Popup Results.' )
						}
						checked={ !! showImg }
						onChange={ () => setAttributes( { showImg: ! showImg } ) }
					/>
					{ showImg && (
						<RangeControl
							label={ __( 'Image Size' ) }
							value={ itemImgSize }
							onChange={ value => setAttributes( { itemImgSize: value ? value : 64 } ) }
							min={ 32 }
							max={ 96 }
							allowReset
						/>
					) }
				</PanelBody>

				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ descColors( __( 'Item Description' ) ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Description' ) }
						help={
							showDesc ?
								__( 'Showing the Description in Search Popup Results.' ) :
								__( 'Hiding the Description in Search Popup Results.' )
						}
						checked={ !! showDesc }
						onChange={ () => setAttributes( { showDesc: ! showDesc } ) }
					/>
					{ showDesc && (
						<Fragment>
							<RangeControl
								label={ __( 'Characters Count' ) }
								help={ __( 'Number of results description characters' ) }
								value={ charactersDescCount }
								onChange={ value => setAttributes( { charactersDescCount: value ? value : 100 } ) }
								min={ 10 }
								max={ 1000 }
								step={ 10 }
								allowReset={ true }
							/>
							<BaseControl label={ __( 'Colour' ) }>
								<TabPanel
									className="a3-blockpress-inspect-tabs"
									activeClass="active-tab"
									tabs={ tabList }
								>
									{ tab => {
										const colorKey = `${ tab.name }DescColor`;

										return (
											<BaseControl>
												{ attributes[ colorKey ] && ( <ColorIndicator colorValue={ attributes[ colorKey ] } /> ) }
												<ColorPalette
													value={ attributes[ colorKey ] }
													onChange={ value =>
														setAttributes( {
															[ colorKey ]: value,
														} )
													}
												/>
											</BaseControl>
										);
									} }
								</TabPanel>
							</BaseControl>
							<ToggleControl
								label={ __( 'Enable Custom Font' ) }
								checked={ !! enableCustomDescFont }
								onChange={ () =>
									setAttributes( { enableCustomDescFont: ! enableCustomDescFont } )
								}
							/>
						</Fragment>
					) }
					{ showDesc && enableCustomDescFont && (
						<TypographyControl
							elementDisabled={ { color: true } }
							optionName="descTypo"
							{ ...this.props }
						/>
					) }
				</PanelBody>

				<PanelBody
					className="a3-blockpress-inspect-panel"
					title={ catColors( __( 'Item Categories' ) ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Item Categories' ) }
						help={
							showCat ?
								__( 'Showing the Item Categories in Search Popup Results.' ) :
								__( 'Hiding the Item Categories in Search Popup Results.' )
						}
						checked={ !! showCat }
						onChange={ () => setAttributes( { showCat: ! showCat } ) }
					/>
					{ showCat && (
						<Fragment>
							<BaseControl label={ __( 'Colour' ) }>
								<TabPanel
									className="a3-blockpress-inspect-tabs"
									activeClass="active-tab"
									tabs={ tabList }
								>
									{ tab => {
										const colorKey = `${ tab.name }CatColor`;
										const wordColorKey = `${ tab.name }CatWordColor`;

										return (
											<Fragment>
												<BaseControl label={ __( 'Cat Colour' ) }>
													{ attributes[ colorKey ] && ( <ColorIndicator colorValue={ attributes[ colorKey ] } /> ) }
													<ColorPalette
														value={ attributes[ colorKey ] }
														onChange={ value =>
															setAttributes( {
																[ colorKey ]: value,
															} )
														}
													/>
												</BaseControl>
												<BaseControl label={ __( 'Item "Category" Colour' ) }>
													{ attributes[ wordColorKey ] && ( <ColorIndicator colorValue={ attributes[ wordColorKey ] } /> ) }
													<ColorPalette
														value={ attributes[ wordColorKey ] }
														onChange={ value =>
															setAttributes( {
																[ wordColorKey ]: value,
															} )
														}
													/>
												</BaseControl>
											</Fragment>
										);
									} }
								</TabPanel>
							</BaseControl>
							<ToggleControl
								label={ __( 'Enable Custom Font' ) }
								checked={ !! enableCustomCatFont }
								onChange={ () =>
									setAttributes( { enableCustomCatFont: ! enableCustomCatFont } )
								}
							/>
						</Fragment>
					) }
					{ showCat && enableCustomCatFont && (
						<TypographyControl
							elementDisabled={ { color: true } }
							optionName="catTypo"
							{ ...this.props }
						/>
					) }
				</PanelBody>

			</InspectorControls>
		);
	}
}
