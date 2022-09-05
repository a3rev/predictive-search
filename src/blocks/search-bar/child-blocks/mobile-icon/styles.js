const { addFilter } = wp.hooks;
const { Fragment } = wp.element;

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
		normalIconColor,
		hoverIconColor,
		activeIconColor,
	} = attributes;

	let styleCSS = '';

	if ( normalIconColor ) {
		styleCSS += `
.wpps_mobile_icon-${ blockID } svg {
	color: ${ normalIconColor }!important;
}
`;
	}

	if ( hoverIconColor ) {
		styleCSS += `
.wpps_mobile_icon-${ blockID } svg:hover {
	color: ${ hoverIconColor }!important;
}
`;
	}

	if ( activeIconColor ) {
		styleCSS += `
.wpps_mobile_icon-${ blockID }.active svg {
	color: ${ activeIconColor }!important;
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
					{ 'wp-predictive-search/mobile-icon' === name ? (
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
	'wp-predictive-search/mobile-icon-styles',
	withCustomStyles
);
