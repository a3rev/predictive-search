const { addFilter } = wp.hooks;
const { Fragment } = wp.element;
const { select } = wp.data;

import { spacingStyles } from '@bit/a3revsoftware.blockpress.spacing';
import { typographyStyles } from '@bit/a3revsoftware.blockpress.typography';

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
		enableCustomFont,
		textColor,
		backgroundColor,
		iconSize,
		iconColor,
	} = attributes;

	const rootClientId = select( 'core/block-editor' ).getBlockRootClientId( clientId );

	let styleCSS = `
.wpps_nav_fill-${ blockID } .wpps_nav_fill_placeholder {
	${ spacingStyles( fieldName, attributes ) }
	${ enableCustomFont ? typographyStyles( `${ fieldName }typo`, attributes ) : '' }
	${ textColor ? `color: ${ textColor };` : '' }
}
`;

	if ( backgroundColor ) {
		styleCSS += `
.wpps_nav_fill-${ blockID } .wpps_nav_field {
	background-color: ${ backgroundColor }!important;
}
`;
	}

	if ( iconSize ) {
		styleCSS += `
.wpps_nav_fill-${ blockID } .wpps_searching_icon {
	width: ${ iconSize }px!important;
}
`;
	}

	if ( iconColor ) {
		styleCSS += `
.wpps_nav_fill-${ blockID } .wpps_searching_icon,
.wpps_nav_fill-${ blockID } .wpps_searching_icon * {
	color: ${ iconColor }!important;
	fill: ${ iconColor }!important;
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
					{ 'wp-predictive-search/search-input' === name ? (
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
	'wp-predictive-search/search-input-styles',
	withCustomStyles
);
