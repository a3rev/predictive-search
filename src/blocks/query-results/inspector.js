/**
 * External dependencies
 */

const { __ } = wp.i18n;

const {
	PanelBody,
	ToggleControl,
	RangeControl,
} = wp.components;

const {
	InspectorControls,
} = wp.blockEditor || wp.editor;

const { Component } = wp.element;

export default class Inspector extends Component {
	render() {
		const {
			clientId,
			attributes: {
				perPage,
				columns,
			},
			setAttributes,
		} = this.props;

		return (
			<InspectorControls>
				<PanelBody title={ __( 'Settings' ) }>
					<RangeControl
						label={ __( 'Items per Page' ) }
						value={ perPage }
						onChange={ ( value ) =>
							setAttributes( { perPage: value } )
						}
						min={ 1 }
						max={ 100 }
					/>
					<RangeControl
						label={ __( 'Columns' ) }
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value } )
						}
						min={ 1 }
						max={ 6 }
					/>
				</PanelBody>
			</InspectorControls>
		);
	}
}
