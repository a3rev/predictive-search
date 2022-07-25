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

export default function ItemExcerptEdit( props ) {
	const {
		clientId,
		attributes: {
			blockID,
			textAlign,
			moreText,
			showMoreOnNewLine,
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
		} ),
	} );

	const readMoreLink = (
		<RichText
			className="wp-block-wpps-result-item-excerpt__more-link"
			tagName="a"
			aria-label={ __( '"Read more" link text' ) }
			placeholder={ __( 'Add "read more" link text (leave empty for hide on frontend) ' ) }
			value={ moreText }
			onChange={ ( newMoreText ) =>
				setAttributes( { moreText: newMoreText } )
			}
			withoutInteractiveFormatting={ true }
		/>
	);

	const excerptClassName = classnames( 'wp-block-wpps-result-item-excerpt__excerpt', {
		'is-inline': ! showMoreOnNewLine,
	} );

	const excerptContent = (
		<div className={ excerptClassName }>{ __( 'Item excerpt text' ) }</div>
	);

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
				{ excerptContent }
				{ ! showMoreOnNewLine && ' ' }
				{ showMoreOnNewLine ? (
					<div className="wp-block-wpps-result-item-excerpt__more-text">
						{ readMoreLink }
					</div>
				) : (
					readMoreLink
				) }
			</div>
		</Fragment>
	);
}
