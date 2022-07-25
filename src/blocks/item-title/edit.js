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
	AlignmentControl,
	BlockControls,
	useBlockProps,
} = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;

/**
 * Internal dependencies
 */
import HeadingLevelDropdown from './heading-level-dropdown';

const {
	useEffect,
	Fragment
} = wp.element;

export default function ItemTitleEdit( props ) {
	const {
		clientId,
		attributes: {
			blockID,
			textAlign,
			level,
			isLink,
			linkTarget,
			rel,
			isPreview,
		},
		setAttributes
	} = props;

	useEffect( () => {
		if ( ! blockID ) {
			setAttributes( { blockID: shorthash.unique( clientId ) } );
		}
	}, [ blockID ] );

	const TagName = 0 === level ? 'p' : 'h' + level;
	const blockProps = useBlockProps( {
		className: classnames( {
			[ `has-text-align-${ textAlign }` ]: textAlign,
			// 'wp-block-post-title': 'wp-block-post-title'
		} ),
	} );

	let titleElement = (
		<TagName { ...blockProps }>{ __( 'Item Title' ) }</TagName>
	);

	if ( isLink ) {
		titleElement =
			(
				<TagName { ...blockProps }>
					<a
						href={ '#' }
						target={ linkTarget }
						rel={ rel }
						onClick={ ( event ) => event.preventDefault() }
						dangerouslySetInnerHTML={ {
							__html: __( 'Item Title' ),
						} }
					/>
				</TagName>
			);
	}

	return (
		<Fragment>
			<Inspector { ...{ ...props } } />
			<BlockControls group="block">
				<HeadingLevelDropdown
					selectedLevel={ level }
					onChange={ ( newLevel ) =>
						setAttributes( { level: newLevel } )
					}
				/>
				<AlignmentControl
					value={ textAlign }
					onChange={ ( nextAlign ) => {
						setAttributes( { textAlign: nextAlign } );
					} }
				/>
			</BlockControls>
			{ titleElement }
		</Fragment>
	);
}
