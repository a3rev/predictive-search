const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { select } = wp.data;

import {
	borderStyles,
	borderRadiusStyles,
	currentBorderValue,
} from '@bit/a3revsoftware.blockpress.border';

/**
 * Set inline styles.
 * @param  {object} props - The block object.
 * @return {object} The inline Shadow type CSS.
 */

const { createHigherOrderComponent } = wp.compose;
const { Component } = wp.element;

const fieldName = '';

const important = true;

const customStyles = ( attributes, clientId ) => {
	const {
		blockID,
		normalIconColor,
		hoverIconColor,
		normalBackgroundColor,
		hoverBackgroundColor,
		normalBorderColor,
		hoverBorderColor,
		enableCustomBorder,
	} = attributes;

	const rootClientId = select( 'core/block-editor' ).getBlockRootClientId( clientId );
	const parentAttributes = select( 'core/block-editor' ).getBlockAttributes( rootClientId );
	let height = 35;
	if ( parentAttributes ) {
		height = parentAttributes.height;
	}

	let styleCSS = `
.wpps_search_submit-${ blockID } .wpps_nav_submit {
	${ normalBackgroundColor ? `background-color: ${ normalBackgroundColor };` : '' }
	${ normalBorderColor ? `border-color: ${ normalBorderColor };` : '' }
	${ enableCustomBorder ? borderStyles( fieldName, attributes, important ) : '' }
	${ enableCustomBorder ? borderRadiusStyles( fieldName, attributes, important ) : '' }
}
`;

	if ( enableCustomBorder && height ) {
		styleCSS += `
.wpps_container .wpps_search_submit-${ blockID } .wpps_nav_submit_icon {
	line-height: ${ height - currentBorderValue( fieldName, attributes, 'Top' ) - currentBorderValue( fieldName, attributes, 'Bottom' ) }px!important;
}
`;
	}

	if ( hoverBackgroundColor || hoverBorderColor ) {
		styleCSS += `
.wpps_search_submit-${ blockID } .wpps_nav_submit:hover {
	${ hoverBackgroundColor ? `background-color: ${ hoverBackgroundColor }!important;` : '' }
	${ hoverBorderColor ? `border-color: ${ hoverBorderColor }!important;` : '' }
}
`;
	}

	if ( normalIconColor ) {
		styleCSS += `
.wpps_search_submit-${ blockID } .wpps_nav_submit svg,
.wpps_search_submit-${ blockID } .wpps_nav_submit svg * {
	color: ${ normalIconColor }!important;
}
`;
	}

	if ( hoverIconColor ) {
		styleCSS += `
.wpps_search_submit-${ blockID } .wpps_nav_submit:hover svg,
.wpps_search_submit-${ blockID } .wpps_nav_submit:hover svg * {
	color: ${ hoverIconColor }!important;
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
					{ 'wp-predictive-search/search-icon' === name ? (
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
	'wp-predictive-search/search-icon-styles',
	withCustomStyles
);
