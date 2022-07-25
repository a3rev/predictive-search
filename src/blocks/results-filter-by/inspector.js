/**
 * External dependencies
 */

const { __ } = wp.i18n;

const {
	PanelBody,
	ToggleControl,
	RangeControl,
	BaseControl,
} = wp.components;

const { ColorPalette } = wp.blockEditor;

const {
	InspectorControls,
} = wp.blockEditor || wp.editor;

const { Fragment, Component } = wp.element;

export default class Inspector extends Component {
	render() {
		const {
			clientId,
			attributes: {
				enableDivider,
				dividerSize,
				dividerColor,
				dividerSpace,
				itemActiveColor,
				itemActiveBgColor,
			},
			setAttributes,
		} = this.props;

		return (
			<InspectorControls>
				<PanelBody title={ __( 'Divider' ) }>
					<ToggleControl
						label={ __( 'Enable Divider' ) }
						checked={ !! enableDivider }
						onChange={ () =>
							setAttributes( { enableDivider: ! enableDivider } )
						}
					/>
					{ enableDivider && (
						<Fragment>
							<RangeControl
								label={ __( 'Size' ) }
								value={ dividerSize }
								onChange={ ( value ) =>
									setAttributes( { dividerSize: value } )
								}
								min={ 1 }
								max={ 20 }
							/>
							<RangeControl
								label={ __( 'Space' ) }
								value={ dividerSpace }
								onChange={ ( value ) =>
									setAttributes( { dividerSpace: value } )
								}
								min={ 1 }
								max={ 100 }
							/>
							<BaseControl label={ __( 'Colour' ) }>
								<ColorPalette
									value={ dividerColor }
									onChange={ value =>
										setAttributes( { dividerColor: value } )
									}
								/>
							</BaseControl>
						</Fragment>
					) }
				</PanelBody>
				<PanelBody title={ __( 'Actived Tab' ) }>
					<BaseControl label={ __( 'Text' ) }>
						<ColorPalette
							value={ itemActiveColor }
							onChange={ value =>
								setAttributes( { itemActiveColor: value } )
							}
						/>
					</BaseControl>
					<BaseControl label={ __( 'Background' ) }>
						<ColorPalette
							value={ itemActiveBgColor }
							onChange={ value =>
								setAttributes( { itemActiveBgColor: value } )
							}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
		);
	}
}
