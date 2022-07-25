const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { select } = wp.data;

import {
	borderStyles,
	borderRadiusStyles,
	currentBorderValue,
} from '@bit/a3revsoftware.blockpress.border';

import { typographyStyles } from '@bit/a3revsoftware.blockpress.typography';

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
		maxWidth,
		normalTextColor,
		hoverTextColor,
		normalBackgroundColor,
		hoverBackgroundColor,
		normalBorderColor,
		hoverBorderColor,
		enableCustomFont,
		enableCustomBorder,
		normalIconColor,
		hoverIconColor,
	} = attributes;

	const rootClientId = select( 'core/block-editor' ).getBlockRootClientId( clientId );
	const parentAttributes = select( 'core/block-editor' ).getBlockAttributes( rootClientId );
	let height = 35;
	if ( parentAttributes ) {
		height = parentAttributes.height;
	}

	const parentBlock = document.getElementById( 'block-' + rootClientId );
	let parentBlockWidth = 300;

	if ( parentBlock ) {
		parentBlockWidth = parentBlock.offsetWidth;
	}

	let styleCSS = `
.wpps_cat_dropdown-${ blockID } .wpps_nav_scope {
	${ normalBackgroundColor ? `background-color: ${ normalBackgroundColor };` : '' }
	${ normalBorderColor ? `border-color: ${ normalBorderColor };` : '' }
	${ enableCustomBorder ? borderStyles( fieldName, attributes, important ) : '' }
	${ enableCustomBorder ? borderRadiusStyles( fieldName, attributes, important ) : '' }
}
.wpps_cat_dropdown-${ blockID } .wpps_nav_facade_label {
	max-width: ${ parentBlockWidth * maxWidth / 100 }px;
	${ normalTextColor ? `color: ${ normalTextColor };` : '' }
	${ enableCustomFont ? typographyStyles( 'typo', attributes ) : '' }
}
`;

	if ( enableCustomBorder && height ) {
		styleCSS += `
.wpps_container .wpps_cat_dropdown-${ blockID } .wpps_nav_facade_label,
.wpps_container .wpps_cat_dropdown-${ blockID } .wpps_nav_down_icon {
	line-height: ${ height - currentBorderValue( fieldName, attributes, 'Top' ) - currentBorderValue( fieldName, attributes, 'Bottom' ) }px!important;
}
`;
	}

	if ( hoverBackgroundColor || hoverBorderColor ) {
		styleCSS += `
.wpps_cat_dropdown-${ blockID } .wpps_nav_scope:hover {
	${ hoverBackgroundColor ? `background-color: ${ hoverBackgroundColor }!important;` : '' }
	${ hoverBorderColor ? `border-color: ${ hoverBorderColor }!important;` : '' }
}
`;
	}

	if ( hoverTextColor ) {
		styleCSS += `
.wpps_cat_dropdown-${ blockID } .wpps_nav_scope:hover .wpps_nav_facade_label {
	color: ${ hoverTextColor }!important;
}
`;
	}

	if ( normalIconColor ) {
		styleCSS += `
.wpps_cat_dropdown-${ blockID } .wpps_nav_scope svg,
.wpps_cat_dropdown-${ blockID } .wpps_nav_scope svg * {
	color: ${ normalIconColor };
}
`;
	}

	if ( hoverIconColor ) {
		styleCSS += `
.wpps_cat_dropdown-${ blockID } .wpps_nav_scope:hover svg,
.wpps_cat_dropdown-${ blockID } .wpps_nav_scope:hover svg * {
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
					{ 'wp-predictive-search/category-dropdown' === name ? (
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
	'wp-predictive-search/category-dropdown-styles',
	withCustomStyles
);
