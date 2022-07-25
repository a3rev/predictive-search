const { addFilter } = wp.hooks;
const { Fragment } = wp.element;

/**
 * Set inline styles.
 * @param  {object} props - The block object.
 * @return {object} The inline Shadow type CSS.
 */

const { createHigherOrderComponent } = wp.compose;
const { Component } = wp.element;

const fieldName = '';

const customStyles = ( attributes ) => {
	const {
		blockID,
		enableDivider,
		dividerSize,
		dividerColor,
		dividerSpace,
		itemActiveColor,
		itemActiveBgColor,
	} = attributes;

	let styleCSS = '';

	if ( ! enableDivider ) {
		styleCSS += `
.wpps-result-filter-by-container-${ blockID } .ps_navigation_divider {
	display: none !important;
}
`;
	} else {
		styleCSS += `
.wpps-result-filter-by-container-${ blockID } .ps_navigation_divider {
	${ dividerSize ? `border-left-width: ${ dividerSize }px;` : '' }
	${ dividerColor ? `border-left-color: ${ dividerColor };` : '' }
	${ dividerSpace ? `margin: 0 ${ dividerSpace }px;` : '' }
}
`;
	}

	if ( itemActiveBgColor ) {
		styleCSS += `
.wpps-result-filter-by-container-${ blockID } .rs_result_other_item_activated,
.wpps-result-filter-by-container-${ blockID } .rs_result_other_item:hover {
	background-color: ${ itemActiveBgColor }!important;
}
`;
	}

	if ( itemActiveColor ) {
		styleCSS += `
.wpps-result-filter-by-container-${ blockID } .rs_result_other_item_activated a,
.wpps-result-filter-by-container-${ blockID } .rs_result_other_item:hover a {
	color: ${ itemActiveColor }!important;
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
					{ 'wpps-result/filter-by' === name ? (
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
	'wpps-result/filter-by-styles',
	withCustomStyles
);
