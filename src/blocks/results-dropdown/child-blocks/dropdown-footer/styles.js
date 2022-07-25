const { addFilter } = wp.hooks;
const { Fragment } = wp.element;

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

const customStyles = attributes => {
	const {
		blockID,
		moreTextColor,
		moreLinkColor,
		backgroundColor,
		enableCustomMoreTextFont,
		enableCustomMoreLinkFont,
		moreIconColor,
	} = attributes;

	let styleCSS = `
.predictive_results .ac_odd-${ blockID } .more_result {
	${ spacingStyles( fieldName, attributes ) }
	${ backgroundColor ? `background-color: ${ backgroundColor }` : '' }
}
`;

	if ( moreTextColor || enableCustomMoreTextFont ) {
		styleCSS += `
.predictive_results .ac_odd-${ blockID } .more_result span {
	${ moreTextColor ? `color: ${ moreTextColor }!important;` : '' }
	${ enableCustomMoreTextFont ? typographyStyles( 'moreTextTypo', attributes ) : '' }
}
`;
	}

	if ( moreLinkColor || enableCustomMoreLinkFont ) {
		styleCSS += `
.predictive_results .ac_odd-${ blockID } .more_result a {
	${ moreLinkColor ? `color: ${ moreLinkColor }!important;` : '' }
	${ enableCustomMoreLinkFont ? typographyStyles( 'moreLinkTypo', attributes ) : '' }
}
`;
	}

	if ( moreIconColor ) {
		styleCSS += `
.predictive_results .ac_odd-${ blockID } .more_result .see_more_arrow,
.predictive_results .ac_odd-${ blockID } .more_result .see_more_arrow * {
	color: ${ moreIconColor }!important;
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
					{ 'wp-predictive-search/dropdown-footer' === name ? (
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
	'wp-predictive-search/dropdown-footer-styles',
	withCustomStyles
);
