/**
 * BLOCK: single-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

import './style.scss';

import edit from './edit';
import metadata from './block.json';

const { registerBlockType } = wp.blocks;
const { name, attributes } = metadata;

const { useInnerBlocksProps, useBlockProps, InnerBlocks } = wp.blockEditor;

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

import icon from './../../assets/icons/query-results.svg';

const settings = {
	icon: {
		src: icon,
		foreground: '#7f54b3',
	},

	attributes,
	edit,
    // save: (  { attributes: { tagName: Tag = 'div' } } ) => {
    //     const blockProps = useBlockProps.save();
    //     const innerBlocksProps = useInnerBlocksProps.save( blockProps );
    //     return <Tag { ...innerBlocksProps } />;
    // },
	save: () => {
       return (
            <InnerBlocks.Content />
        );
	},
};

registerBlockType( { name, ...metadata }, settings );
