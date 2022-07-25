/**
 * BLOCK: Profile
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/**
 * Internal dependencies
 */
import { SpacingAttributes } from '@bit/a3revsoftware.blockpress.spacing';

import BlockEdit from './edit';

// icons
import IconSearch from './../../assets/icons/icon.svg';

import SearchFormAttributes from './attributes';

// custom style for editor
import './styles';

// editor style
import './editor.scss';
import './style.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks;
const { InnerBlocks } = wp.blockEditor;
const { Fragment } = wp.element;

const spacingAttributes = SpacingAttributes( '' );

/**
 * Register: a3 Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'wp-predictive-search/form', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Predictive Search' ), // Block title.
	apiVersion: 2,
	icon: {
		src: IconSearch,
		foreground: '#24b6f1',
	}, // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'a3rev-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'WordPress Predictive Search' ),
		__( 'Predictive Search' ),
		__( 'Post Search' ),
		__( 'a3rev' ),
	],
	example: {
		attributes: {
			isPreview: true,
		},
	},

	attributes: {
		...SearchFormAttributes,
		...spacingAttributes,
	},

	// The "edit" property must be a valid function.
	edit( props ) {
		const { attributes } = props;

		if ( attributes.isPreview ) {
			return ( <img
				src={ predictive_search_vars.preview }
				alt={ __( 'Predictive Search Preview' ) }
				style={ {
					width: '100%',
					height: 'auto',
				} }
			/> );
		}

		return (
			<Fragment>
				<BlockEdit { ...props } />
			</Fragment>
		);
	},

	// The "save" property must be specified and must be a valid function.
	save() {
		return (
			<InnerBlocks.Content />
		);
	},
} );
