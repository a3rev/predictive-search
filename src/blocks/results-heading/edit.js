/**
 * External dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';

/**
 * WordPress dependencies
 */
const {
	AlignmentControl,
	BlockControls,
	useBlockProps,
	RichText,
} = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;

const {
	useEffect,
	Fragment
} = wp.element;

export default function ResultsHeadingEdit( props ) {
	const {
		clientId,
		attributes: {
			blockID,
			textAlign,
			content,
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

	const onContentChange = value => {
			setAttributes( { content: value } );
		};

	return (
		<Fragment>
			<BlockControls group="block">
				<AlignmentControl
					value={ textAlign }
					onChange={ ( nextAlign ) => {
						setAttributes( { textAlign: nextAlign } );
					} }
				/>
			</BlockControls>
			<RichText
				identifier="content"
				tagName="div"
				value={ content }
				onChange={ onContentChange }
				placeholder={ __( 'Viewing all %%object%% search results for your search query %%keyword%%' ) }
				textAlign={ textAlign }
				keepplaceholderonfocus="true"
				{ ...blockProps }
			/>
		</Fragment>
	);
}
