const { addFilter } = wp.hooks;
const { Fragment } = wp.element;

import {
	borderStyles,
	borderRadiusStyles,
} from '@bit/a3revsoftware.blockpress.border';

import { shadowStyles } from '@bit/a3revsoftware.blockpress.shadow';

/**
 * Set inline styles.
 * @param  {object} props - The block object.
 * @return {object} The inline Shadow type CSS.
 */

const { createHigherOrderComponent } = wp.compose;
const { Component } = wp.element;

const fieldName = '';

const customStyles = attributes => {
	const {
		blockID,
		height,
		enableCustomBorder,
		borderColor,
		borderFocusColor,
		backgroundColor,
	} = attributes;

	const important = true;

	let styleCSS = `
.wpps_container-${ blockID } {
	${ enableCustomBorder ? borderStyles( fieldName, attributes, important ) : '' }
	${ enableCustomBorder ? borderRadiusStyles( fieldName, attributes, important ) : '' }
	${ borderColor ? `border-color: ${ borderColor };` : '' }
	${ backgroundColor ? `background-color: ${ backgroundColor };` : '' }
	${ shadowStyles( 'shadow', attributes ) }
}
`;

	if ( height ) {
		styleCSS += `
.wpps_container-${ blockID } .wpps_nav_left,
.wpps_container-${ blockID } .wpps_nav_right,
.wpps_container-${ blockID } .wpps_nav_fill,
.wpps_container-${ blockID } .wpps_nav_scope,
.wpps_container-${ blockID } .wpps_nav_submit,
.wpps_container-${ blockID } .wpps_nav_field,
.wpps_container-${ blockID } .wpps_search_keyword {
	height: ${ height }px!important;
}
.wpps_container-${ blockID } .wpps_nav_facade_label,
.wpps_container-${ blockID } .wpps_nav_down_icon,
.wpps_container-${ blockID } .wpps_category_selector,
.wpps_container-${ blockID } .wpps_nav_submit_icon,
.wpps_container-${ blockID } .wpps_searching_icon {
	line-height: ${ height }px!important;
}
`;
	}

	if ( borderFocusColor ) {
		styleCSS += `
.wpps_container-${ blockID }.has-child-selected {
	border-color: ${ borderFocusColor }!important;
}
`;
	}

	return styleCSS;
};

const withCustomStyles = createHigherOrderComponent( BlockEdit => {
	class newBlockEdit extends Component {
		render() {
			const { name, attributes } = this.props;

			return (
				<Fragment>
					<BlockEdit { ...this.props } />
					{ 'wp-predictive-search/search-bar' === name ? (
						<style>{ customStyles( attributes ) }</style>
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
	'wp-predictive-search/search-bar-styles',
	withCustomStyles
);
