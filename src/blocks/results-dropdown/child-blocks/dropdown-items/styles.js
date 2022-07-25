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

const customStyles = attributes => {
	const {
		blockID,
		normalBackgroundColor,
		hoverBackgroundColor,
		enableBorder,
		normalBorderColor,
		hoverBorderColor,
		showImg,
		itemImgSize,
		normalNameColor,
		hoverNameColor,
		enableCustomNameFont,
		showDesc,
		normalDescColor,
		hoverDescColor,
		enableCustomDescFont,
		showCat,
		normalCatColor,
		hoverCatColor,
		normalCatWordColor,
		hoverCatWordColor,
		enableCustomCatFont,
	} = attributes;

	let styleCSS = `
.predictive_results .ac_odd-${ blockID } .ajax_search_content {
	${ spacingStyles( '', attributes ) }
	${ enableBorder ? borderStyles( '', attributes ) : '' }
}
.predictive_results .ac_odd-${ blockID }:not(.ac_over) .ajax_search_content {
	${ enableBorder && normalBorderColor ? `border-color: ${ normalBorderColor }!important;` : '' }
	${ normalBackgroundColor ? `background-color: ${ normalBackgroundColor }!important;` : '' }
}
.predictive_results .ac_odd-${ blockID }.ac_over .ajax_search_content {
	${ enableBorder && hoverBorderColor ? `border-color: ${ hoverBorderColor }!important;` : '' }
	${ hoverBackgroundColor ? `background-color: ${ hoverBackgroundColor }!important;` : '' }
}
.predictive_results .ac_odd-${ blockID } .rs_content_popup {
	width: 100%;
}
.predictive_results .ac_odd-${ blockID } .rs_content_popup .rs_name {
	${ enableCustomNameFont ? typographyStyles( 'nameTypo', attributes, true ) : '' }
}
.predictive_results .ac_odd-${ blockID }:not(.ac_over) .rs_content_popup .rs_name {
	${ normalNameColor ? `color: ${ normalNameColor }!important;` : '' }
}
.predictive_results .ac_odd-${ blockID }.ac_over .rs_content_popup .rs_name {
	${ hoverNameColor ? `color: ${ hoverNameColor }!important;` : '' }
}
`;

	if ( showImg ) {
		styleCSS += `
.predictive_results .ac_odd-${ blockID } .rs_avatar {
	${ itemImgSize ? `width: ${ itemImgSize }px!important;` : '' }
}
.predictive_results .ac_odd-${ blockID } .rs_content_popup {
	width: calc( 100% - ${ itemImgSize + 10 }px )!important;
}
`;
	}

	if ( showDesc ) {
		styleCSS += `
.predictive_results .ac_odd-${ blockID } .rs_content_popup .rs_description {
	${ enableCustomDescFont ? typographyStyles( 'descTypo', attributes ) : '' }
}
.predictive_results .ac_odd-${ blockID }:not(.ac_over) .rs_content_popup .rs_description {
	${ normalDescColor ? `color: ${ normalDescColor }!important;` : '' }
}
.predictive_results .ac_odd-${ blockID }.ac_over .rs_content_popup .rs_description {
	${ hoverDescColor ? `color: ${ hoverDescColor }!important;` : '' }
}
`;
	}

	if ( showCat ) {
		styleCSS += `
.predictive_results .ac_odd-${ blockID }:not(.ac_over) .rs_content_popup .rs_cat {
	${ normalCatWordColor ? `color: ${ normalCatWordColor }!important;` : '' }
}
.predictive_results .ac_odd-${ blockID }.ac_over .rs_content_popup .rs_cat {
	${ hoverCatWordColor ? `color: ${ hoverCatWordColor }!important;` : '' }
}
.predictive_results .ac_odd-${ blockID } .rs_content_popup .rs_cat,
.predictive_results .ac_odd-${ blockID } .rs_content_popup .rs_cat > a {
	${ enableCustomCatFont ? typographyStyles( 'catTypo', attributes ) : '' }
}
.predictive_results .ac_odd-${ blockID }:not(.ac_over) .rs_content_popup .rs_cat > a {
	${ normalCatColor ? `color: ${ normalCatColor }!important;` : '' }
}
.predictive_results .ac_odd-${ blockID }.ac_over .rs_content_popup .rs_cat > a {
	${ hoverCatColor ? `color: ${ hoverCatColor }!important;` : '' }
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
					{ 'wp-predictive-search/dropdown-items' === name ? (
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
	'wp-predictive-search/dropdown-items-styles',
	withCustomStyles
);
