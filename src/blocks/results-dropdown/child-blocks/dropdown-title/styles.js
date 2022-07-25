const { addFilter } = wp.hooks;
const { Fragment } = wp.element;

import { spacingStyles } from '@bit/a3revsoftware.blockpress.spacing';
import { typographyStyles } from '@bit/a3revsoftware.blockpress.typography';
import { borderStyles } from '@bit/a3revsoftware.blockpress.border';

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
		textColor,
		backgroundColor,
		enableCustomFont,
		enableCustomBorder,
	} = attributes;

	const styleCSS = `
.predictive_results .ac_odd-${ blockID } .ajax_search_content_title {
	${ textColor ? `color: ${ textColor }!important;` : '' }
	${ backgroundColor ? `background-color: ${ backgroundColor }!important;` : '' }
	${ spacingStyles( fieldName, attributes ) }
	${ enableCustomBorder ? borderStyles( fieldName, attributes ) : '' }
	${ enableCustomFont ? typographyStyles( `${ fieldName }typo`, attributes ) : '' }
}
`;

	return styleCSS;
};

const withCustomStyles = createHigherOrderComponent( BlockEdit => {
	class newBlockEdit extends Component {
		render() {
			const { name, attributes } = this.props;

			return (
				<Fragment>
					<BlockEdit { ...this.props } />
					{ 'wp-predictive-search/dropdown-title' === name ? (
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
	'wp-predictive-search/dropdown-title-styles',
	withCustomStyles
);
