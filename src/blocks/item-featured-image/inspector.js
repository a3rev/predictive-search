/**
 * Internal dependencies
 */

/**
 * Inspector controls
 */

const { __ } = wp.i18n;

const {
	PanelBody,
	ToggleControl,
} = wp.components;

const {
	InspectorControls,
	store: blockEditorStore,
} = wp.blockEditor || wp.editor;

const { Component, Fragment } = wp.element;

const { useSelect, useDispatch } = wp.data;

/**
 * Internal dependencies
 */
import DimensionControls from './dimension-controls';

export default class Inspector extends Component {
	render() {
		const {
			clientId,
			attributes,
			setAttributes,
		} = this.props;

		const {
				isLink,
				linkTarget,
				height,
				width,
				scale,
				sizeSlug,
			} = attributes;

		const imageSizes = predictive_search_vars.image_sizes.map( ( { label, value } ) => ( {
			slug:value,
			name: label,
		} ) );

		const imageSizeOptions = imageSizes
			.map( ( { name, slug } ) => ( {
				value: slug,
				label: name,
			} ) );

		return (
			<Fragment>
				<DimensionControls
					clientId={ clientId }
					attributes={ attributes }
					setAttributes={ setAttributes }
					imageSizeOptions={ imageSizeOptions }
				/>
				<InspectorControls>
					<PanelBody title={ __( 'Link settings' ) }>
						<ToggleControl
							label={ __( 'Link to item' ) }
							onChange={ () => setAttributes( { isLink: ! isLink } ) }
							checked={ isLink }
						/>
						{ isLink && (
							<ToggleControl
								label={ __( 'Open in new tab' ) }
								onChange={ ( value ) =>
									setAttributes( {
										linkTarget: value ? '_blank' : '_self',
									} )
								}
								checked={ linkTarget === '_blank' }
							/>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
	 	);
	}
}
