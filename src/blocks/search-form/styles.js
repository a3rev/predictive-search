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
		align,
		mIconEnable,
		widthUnit,
		tabletmIconEnable,
		tabletwidthUnit,
	} = attributes;

	let { width, tabletwidth } = attributes;

	// Validate Max Content Width
	if ( ! width ) {
		width = 100;
	}
	if ( 'px' === widthUnit && width < 200 ) {
		width = 200;
	} else if ( 'px' !== widthUnit && width > 100 ) {
		width = 100;
	}

	if ( ! tabletwidth ) {
		tabletwidth = 100;
	}
	if ( 'px' === tabletwidthUnit && tabletwidth < 200 ) {
		tabletwidth = 200;
	} else if ( 'px' !== tabletwidthUnit && tabletwidth > 100 ) {
		tabletwidth = 100;
	}

	let styleCSS = `
.wpps_block-${ blockID } {
	${ spacingStyles( fieldName, attributes ) }
}
`;

	if ( mIconEnable ) {
		styleCSS += `
@media only screen and (min-width: 1025px) {
.wpps_block-${ blockID } [data-type="wp-predictive-search/search-bar"],
.wpps_block-${ blockID } [data-type="wp-predictive-search/results-dropdown"] {
	width: ${ width }${ widthUnit };
}
}
`;
	} else {
		styleCSS += `
@media only screen and (min-width: 1025px) {
.wpps_block-${ blockID } .wpps_bar {
	max-width: ${ width }${ widthUnit };
}
}
`;
	}

	if ( mIconEnable && 'right' === align ) {
		styleCSS += `
@media only screen and (min-width: 1025px) {
.wpps_block-${ blockID }.align-right [data-type="wp-predictive-search/search-bar"] {
	margin-left: calc( 100% - ${ width }${ widthUnit } )!important;
}
}
`;
	} else if ( mIconEnable && 'center' === align ) {
		styleCSS += `
@media only screen and (min-width: 1025px) {
.wpps_block-${ blockID }.align-center [data-type="wp-predictive-search/results-dropdown"] {
	left: calc( 50% - ( ${ width }${ widthUnit } / 2 ) )!important;
}
}
`;
	}

	if ( tabletmIconEnable ) {
		styleCSS += `
@media only screen and (max-width: 1024px) and (min-width: 681px) {
.wpps_block-${ blockID } [data-type="wp-predictive-search/search-bar"],
.wpps_block-${ blockID } [data-type="wp-predictive-search/results-dropdown"] {
	width: ${ tabletwidth }${ tabletwidthUnit };
}
}
`;
	} else {
		styleCSS += `
@media only screen and (max-width: 1024px) and (min-width: 681px) {
.wpps_block-${ blockID } .wpps_bar {
	max-width: ${ tabletwidth }${ tabletwidthUnit };
}
}
`;
	}

	if ( tabletmIconEnable && 'right' === align ) {
		styleCSS += `
@media only screen and (max-width: 1024px) and (min-width: 681px) {
.wpps_block-${ blockID }.align-right [data-type="wp-predictive-search/search-bar"] {
	margin-left: calc( 100% - ${ tabletwidth }${ tabletwidthUnit } )!important;
}
}
`;
	} else if ( tabletmIconEnable && 'center' === align ) {
		styleCSS += `
@media only screen and (max-width: 1024px) and (min-width: 681px) {
.wpps_block-${ blockID }.align-center [data-type="wp-predictive-search/results-dropdown"] {
	left: calc( 50% - ( ${ tabletwidth }${ tabletwidthUnit } / 2 ) )!important;
}
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
					{ 'wp-predictive-search/form' === name ? (
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
	'wp-predictive-search/form-styles',
	withCustomStyles
);
