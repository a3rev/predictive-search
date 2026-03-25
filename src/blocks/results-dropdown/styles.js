const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { select } = wp.data;

import {
	borderStyles,
	borderRadiusStyles,
} from '@bit/a3revsoftware.blockpress.border';

/**
 * Set inline styles.
 * @param  {object} props - The block object.
 * @return {object} The inline Shadow type CSS.
 */

const { createHigherOrderComponent } = wp.compose;
const { Component } = wp.element;

const fieldName = '';

const customStyles = ( attributes, clientId ) => {
	const {
		blockID,
		containerWide,
		borderColor,
		enableCustomBorder,
	} = attributes;

	let styleCSS = `
.predictive_results.ac_results_${ blockID } {
	${ borderColor ? `border-color: ${ borderColor };` : '' }
	${ enableCustomBorder ? borderStyles( fieldName, attributes ) : '' }
	${ enableCustomBorder ? borderRadiusStyles( fieldName, attributes ) : '' }
}
`;

	let inputWide, inputOffsetLeft, inputOffsetRight;
	if ( 'input' === containerWide ) {
		const rootFormClientIds = select( 'core/block-editor' ).getBlockParentsByBlockName( clientId, 'wp-predictive-search/form' );
		const rootFormClientId = rootFormClientIds && rootFormClientIds.length > 0 ? rootFormClientIds[ 0 ] : null;
		const rootFormAttributes = rootFormClientId ? select( 'core/block-editor' ).getBlockAttributes( rootFormClientId ) : null;
		const align = rootFormAttributes ? rootFormAttributes.align : undefined;

		const currentElement = document.getElementById( 'block-' + clientId );
		if ( currentElement ) {
			const searchBarElement = currentElement.parentElement.querySelector( '.wpps_container' );
			if ( searchBarElement ) {
				const inputElement = searchBarElement.querySelector( '.wpps_nav_fill' );
				if ( inputElement ) {
					const searchBarWide = searchBarElement.offsetWidth;
					inputWide = inputElement.offsetWidth;
					inputOffsetLeft = inputElement.offsetLeft;
					inputOffsetRight = searchBarWide - inputWide - inputOffsetLeft;
				}
			}
		}

		styleCSS += `
.predictive_results.ac_results_${ blockID } {
	${ inputWide ? `width: ${ inputWide }px!important;` : '' }
	${ inputOffsetLeft && 'right' !== align ? `margin-left: ${ inputOffsetLeft }px!important;` : '' }
	${ inputOffsetRight && 'right' === align ? `margin-right: ${ inputOffsetRight }px!important;` : '' }
}
`;
	}

	return styleCSS;
};

const withCustomStyles = createHigherOrderComponent( BlockEdit => {
	class newBlockEdit extends Component {
		render() {
			const { clientId, name, attributes } = this.props;

			return (
				<Fragment>
					<BlockEdit { ...this.props } />
					{ 'wp-predictive-search/results-dropdown' === name ? (
						<style>{ customStyles( attributes, clientId ) }</style>
					) : (
						''
					) }
				</Fragment>
			);
		}
	}
	return newBlockEdit;
}, 'withCustomStyles' );

addFilter(
	'editor.BlockEdit',
	'wp-predictive-search/results-dropdown-styles',
	withCustomStyles
);
