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
	RichText,
} = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;

const {
	useEffect,
	Fragment
} = wp.element;

export default function ReadMoreEdit( props ) {
	const {
		clientId,
		attributes: {
			blockID,
			content,
		},
		setAttributes,
	} = props;

	useEffect( () => {
		if ( ! blockID ) {
			setAttributes( { blockID: shorthash.unique( clientId ) } );
		}
	}, [ blockID ] );

	const blockProps = useBlockProps();

	return (
		<Fragment>
			<Inspector { ...{ ...props } } />
			<RichText
				tagName="a"
				aria-label={ __( '"Read more" link text' ) }
				placeholder={ __( 'Read more' ) }
				value={ content }
				onChange={ ( newValue ) =>
					setAttributes( { content: newValue } )
				}
				withoutInteractiveFormatting={ true }
				{ ...blockProps }
			/>
		</Fragment>
	);
}
