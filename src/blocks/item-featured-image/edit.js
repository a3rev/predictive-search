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
	useBlockProps,
} = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;

const {
	useEffect,
	Fragment
} = wp.element;

function PostFeaturedImageDisplay( {
	attributes,
} ) {
	const { height, width, scale, sizeSlug } = attributes;

	const blockProps = useBlockProps( {
		style: { width, height },
		className: classnames( {
			'wp-block-post-featured-image': 'wp-block-post-featured-image'
		} ),
	} );

	const image = (
		<img
			src={ predictive_search_vars.placeholder }
			alt={ __( 'Featured image' ) }
			style={ { height, objectFit: height && scale } }
		/>
	);

	return (
		<figure { ...blockProps }>{ image }</figure>
	);
}

export default function PostFeaturedImageEdit( props ) {
	const {
		clientId,
		attributes: {
			blockID,
		},
		setAttributes,
	} = props;

	useEffect( () => {
		if ( ! blockID ) {
			setAttributes( { blockID: shorthash.unique( clientId ) } );
		}
	}, [ blockID ] );

	return (
		<Fragment>
			<Inspector { ...{ ...props } } />
			<PostFeaturedImageDisplay { ...props } />
		</Fragment>
	);
}
