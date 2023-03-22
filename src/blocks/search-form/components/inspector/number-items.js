/**
 * Internal dependencies
 */
import map from 'lodash/map';

const { __ } = wp.i18n;
const { Component } = wp.element;
const { PanelBody, RangeControl, TextControl, BaseControl } = wp.components;
const { applyFilters } = wp.hooks;

const postTypes = applyFilters( 'wpps.posttypes_support', [ { key: 'post', name: __( 'Post' ) }, { key: 'page', name: __( 'Page' ) } ] );
const customTypes = applyFilters( 'wpps.customtypes_support', [] );
const taxonomies = applyFilters( 'wpps.taxonomies_support', [ { key: 'category', name: __( 'Post Category' ) }, { key: 'post_tag', name: __( 'Post Tag' ) } ] );

/**
 * Inspector controls
 */
export default class InspectorNumberItems extends Component {
	render() {
		const { attributes, setAttributes } = this.props;

		const {
			numberPostTypes,
			numberCustomTypes,
			numberTaxonomies,
			orderItems,
		} = attributes;

		const handleNumberPostTypes = ( value, key ) => {
			const newNumberPostTypes = JSON.parse( JSON.stringify( numberPostTypes ) );
			newNumberPostTypes[ key ] = value;
			setAttributes( { numberPostTypes: newNumberPostTypes } );
		};

		const handleNumberCustomTypes = ( value, key ) => {
			const newNumberCustomTypes = JSON.parse( JSON.stringify( numberCustomTypes ) );
			newNumberCustomTypes[ key ] = value;
			setAttributes( { numberCustomTypes: newNumberCustomTypes } );
		};

		const handleNumberTaxonomies = ( value, key ) => {
			const newNumberTaxonomies = JSON.parse( JSON.stringify( numberTaxonomies ) );
			newNumberTaxonomies[ key ] = value;
			setAttributes( { numberTaxonomies: newNumberTaxonomies } );
		};

		const handleOrderItems = ( value, key ) => {
			const newOrderItems = JSON.parse( JSON.stringify( orderItems ) );
			newOrderItems[ key ] = value;
			setAttributes( { orderItems: newOrderItems } );
		};

		return (
			<PanelBody
				className="a3-blockpress-inspect-panel"
				title={ __( 'Number of Items' ) }
				initialOpen={ false }
			>
				<p>{ __( 'Number of items and Order Display to show in dropdown' ) }</p>
				{ map( postTypes, ( { name, key } ) => (
					<BaseControl label={ name } key={ `${key}_post` }>
						<div className="ps-number-items-container">
							<div className="left-col">
								<RangeControl
									key={ key }
									label={ __( 'Number of Items' ) }
									value={ numberPostTypes[ key ] ? numberPostTypes[ key ] : 0 }
									onChange={ value => handleNumberPostTypes( value, key ) }
									min={ 0 }
									max={ 20 }
									renderTooltipContent={ value => value }
									showTooltip={ true }
									withInputField={ false }
								/>
							</div>
							<div className="right-col">
						        <TextControl
						        	label={ __( 'Order Display' ) }
									value={ orderItems[ key ] ? orderItems[ key ] : 0 }
									type={ 'number' }
									onChange={ value => handleOrderItems( value, key ) }
								/>
							</div>
				        </div>
			        </BaseControl>
				) ) }

				{ map( customTypes, ( { name, key } ) => (
					<BaseControl label={ name } key={ `${key}_custom` }>
						<div className="ps-number-items-container">
							<div className="left-col">
								<RangeControl
									key={ key }
									label={ __( 'Number of Items' ) }
									value={ numberCustomTypes[ key ] ? numberCustomTypes[ key ] : 0 }
									onChange={ value => handleNumberCustomTypes( value, key ) }
									min={ 0 }
									max={ 20 }
									renderTooltipContent={ value => value }
									showTooltip={ true }
									withInputField={ false }
								/>
							</div>
							<div className="right-col">
						        <TextControl
						        	label={ __( 'Order Display' ) }
									value={ orderItems[ key ] ? orderItems[ key ] : 0 }
									type={ 'number' }
									onChange={ value => handleOrderItems( value, key ) }
								/>
							</div>
				        </div>
			        </BaseControl>
				) ) }

				{ map( taxonomies, ( { name, key } ) => (
					<BaseControl label={ name } key={ `${key}_taxonomy` }>
						<div className="ps-number-items-container">
							<div className="left-col">
								<RangeControl
									key={ key }
									label={ __( 'Number of Items' ) }
									value={ numberTaxonomies[ key ] ? numberTaxonomies[ key ] : 0 }
									onChange={ value => handleNumberTaxonomies( value, key ) }
									min={ 0 }
									max={ 20 }
									renderTooltipContent={ value => value }
									showTooltip={ true }
									withInputField={ false }
								/>
							</div>
							<div className="right-col">
						        <TextControl
						        	label={ __( 'Order Display' ) }
									value={ orderItems[ key ] ? orderItems[ key ] : 0 }
									type={ 'number' }
									onChange={ value => handleOrderItems( value, key ) }
								/>
							</div>
				        </div>
			        </BaseControl>
				) ) }
			</PanelBody>
		);
	}
}
