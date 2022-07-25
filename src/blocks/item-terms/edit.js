/**
 * External dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';

import Inspector from './inspector';

/**
 * WordPress dependencies
 */
const {
	AlignmentToolbar,
	BlockControls,
	useBlockProps,
	RichText,
} = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;

const {
	useEffect,
	Fragment
} = wp.element;

const { createBlock, getDefaultBlockName } = wp.blocks;

// Allowed formats for the prefix and suffix fields.
const ALLOWED_FORMATS = [
	'core/bold',
	'core/image',
	'core/italic',
	'core/link',
	'core/strikethrough',
	'core/text-color',
];

const postTerms = [
	{
		id: 1,
		link: '#',
		name: __( 'Term 1' ),
	},
	{
		id: 2,
		link: '#',
		name: __( 'Term 2' ),
	},
]

export default function ItemTermsEdit( props ) {
	const {
		clientId,
		attributes: {
			blockID,
			term,
			textAlign,
			separator,
			prefix,
			suffix,
		},
		setAttributes
	} = props;

	useEffect( () => {
		if ( ! blockID ) {
			setAttributes( { blockID: shorthash.unique( clientId ) } );
		}
	}, [ blockID ] );

	const blockProps = useBlockProps( {
		className: classnames( {
			[ `has-text-align-${ textAlign }` ]: textAlign,
			'wp-block-post-terms': 'wp-block-post-terms',
			[ `taxonomy-${ term }` ]: term,
		} ),
	} );

	return (
		<Fragment>
			<Inspector { ...{ ...props } } />
			<BlockControls>
				<AlignmentToolbar
					value={ textAlign }
					onChange={ ( newAlign ) =>
						setAttributes( { textAlign: newAlign } )
					}
				/>
			</BlockControls>
			<div { ...blockProps }>
				<RichText
					allowedFormats={ ALLOWED_FORMATS }
					className="wp-block-post-terms__prefix wp-block-wpps-result-item-terms__prefix"
					multiline={ false }
					aria-label={ __( 'Prefix' ) }
					placeholder={ __( 'Prefix' ) + ' ' }
					value={ prefix }
					onChange={ ( value ) =>
						setAttributes( { prefix: value } )
					}
					tagName="span"
				/>
				{ postTerms.map( ( postTerm ) => (
						<a
							key={ postTerm.id }
							href={ postTerm.link }
							onClick={ ( event ) => event.preventDefault() }
						>
							{ unescape( postTerm.name ) }
						</a>
					) )
					.reduce( ( prev, curr ) => (
						<Fragment>
							{ prev }
							<span className="wp-block-post-terms__separator wp-block-wpps-result-item-terms__separator">
								{ separator || ' ' }
							</span>
							{ curr }
						</Fragment>
				) ) }
				<RichText
					allowedFormats={ ALLOWED_FORMATS }
					className="wp-block-post-terms__suffix wp-block-wpps-result-item-terms__suffix"
					multiline={ false }
					aria-label={ __( 'Suffix' ) }
					placeholder={ ' ' + __( 'Suffix' ) }
					value={ suffix }
					onChange={ ( value ) =>
						setAttributes( { suffix: value } )
					}
					tagName="span"
					__unstableOnSplitAtEnd={ () =>
						insertBlocksAfter(
							createBlock( getDefaultBlockName() )
						)
					}
				/>
			</div>
		</Fragment>
	);
}
