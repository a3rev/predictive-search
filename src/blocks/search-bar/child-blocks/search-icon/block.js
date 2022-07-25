/**
 * BLOCK: Profile
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/**
 * Internal dependencies
 */
import { BorderAttributes, BorderRadiusAttributes } from '@bit/a3revsoftware.blockpress.border';

import BlockEdit from './edit';
import save from './save';

// icons
import IconChild from './../../../../assets/icons/child-icon.svg';

import SearchIconAttributes from './attributes';

// custom style for editor
import './styles';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks;

const { Fragment } = wp.element;

const borderAttributes = BorderAttributes( '' );
const borderRadiusAttributes = BorderRadiusAttributes( '' );

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
registerBlockType( 'wp-predictive-search/search-icon', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Search Icon Button' ), // Block title.
	apiVersion: 2,
	icon: {
		src: IconChild,
		foreground: '#24b6f1',
	}, // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'a3rev-blocks', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'Search Icon' ),
		__( 'a3rev' ),
	],

	attributes: {
		...SearchIconAttributes,
		...borderAttributes,
		...borderRadiusAttributes,
	},

	parent: [ 'wp-predictive-search/search-form' ],

	supports: {
		inserter: false,
		reusable: false,
		html: false,
	},

	// The "edit" property must be a valid function.
	edit( props ) {

		return (
			<Fragment>
				<BlockEdit { ...props } />
			</Fragment>
		);
	},

	// The "save" property must be specified and must be a valid function.
	save,
} );
