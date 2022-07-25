/**
 * BLOCK: single-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

/**
 * External dependencies
 */
import classnames from 'classnames';

import './style.scss';

import edit from './edit';
import metadata from './block.json';

const { registerBlockType } = wp.blocks;
const { name, attributes } = metadata;

const { useBlockProps, RichText } = wp.blockEditor;

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

import icon from './../../assets/icons/results-heading.svg';

const settings = {
	icon: {
		src: icon,
		foreground: '#7f54b3',
	},

	attributes,
	edit,
	save( props ) {
        const { attributes } = props;

        const { textAlign, content } = attributes;
        const TagName = 'div';

        const className = classnames( {
            [ `has-text-align-${ textAlign }` ]: textAlign,
        } );

        return (
            <TagName { ...useBlockProps.save( { className } ) }>
                <RichText.Content value={ content } />
            </TagName>
        );
    },
};

registerBlockType( { name, ...metadata }, settings );
