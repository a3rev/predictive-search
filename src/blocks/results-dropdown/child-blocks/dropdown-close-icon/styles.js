const { addFilter } = wp.hooks;
const { Fragment } = wp.element;

import { spacingStyles } from '@bit/a3revsoftware.blockpress.spacing';

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
		iconSize,
		iconColor,
	} = attributes;

	const styleCSS = `
.predictive_results .ps_close.ps_close-${ blockID } {
	${ spacingStyles( fieldName, attributes ) }
	${ iconSize ? `width: ${ iconSize }px!important;` : '' }
	${ iconColor ? `fill: ${ iconColor }!important;` : '' }
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
					{ 'wp-predictive-search/dropdown-close-icon' === name ? (
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
	'wp-predictive-search/dropdown-close-icon-styles',
	withCustomStyles
);
