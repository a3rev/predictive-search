/**
 * External dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';

/**
 * WordPress dependencies
 */
const {
	BlockContextProvider,
	__experimentalUseBlockPreview: useBlockPreview,
	store: blockEditorStore,
	useBlockProps,
	useInnerBlocksProps,
} = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;

const {
	Fragment,
	useState,
	useEffect,
	memo,
	useMemo,
} = wp.element;

const { useSelect } = wp.data;

// const TEMPLATE = [
// 	[ 'core/template-part', {
// 			slug: 'ps-all-results-item',
// 			theme: predictive_search_vars.theme,
// 		},
// 	],
// ];

const TEMPLATE = [ 
	[ 'wpps-result/item-featured-image' ],
	[ 'wpps-result/item-title' ],
	[ 'wpps-result/item-excerpt' ],
	[ 'wpps-result/read-more' ],
	[ 'wpps-result/item-terms', {
		term: 'category',
		prefix: __( 'Categories: ' ),
	} ],
	[ 'wpps-result/item-terms', {
		term: 'post_tag',
		prefix: __( 'Tags: ' ),
	} ],
];

function PostTemplateInnerBlocks() {
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-post' },
		{ template: TEMPLATE }
	);

	return <div { ...innerBlocksProps } />;
}

function PostTemplateBlockPreview( {
	blocks,
	blockContextId,
	isHidden,
	setActiveBlockContextId,
} ) {
	const blockPreviewProps = useBlockPreview( {
		blocks,
		props: {
			className: 'wp-block-post',
		},
	} );

	const handleOnClick = () => {
		setActiveBlockContextId( blockContextId );
	};

	const style = {
		display: isHidden ? 'none' : undefined,
	};

	return (
		<div
			{ ...blockPreviewProps }
			tabIndex={ 0 }
			// eslint-disable-next-line jsx-a11y/no-noninteractive-element-to-interactive-role
			role="button"
			onClick={ handleOnClick }
			onKeyPress={ handleOnClick }
			style={ style }
		/>
	);
}

const MemoizedPostTemplateBlockPreview = memo( PostTemplateBlockPreview );

export default function ItemTemplateEdit( {
	clientId,
	attributes,
	setAttributes,
	context: {
		perPage,
		columns
	},
} ) {

	const { blockID } = attributes;

	useEffect( () => {
		if ( ! blockID ) {
			setAttributes( { blockID: shorthash.unique( clientId ) } );
		}
	}, [ blockID ] );

	const [ activeBlockContextId, setActiveBlockContextId ] = useState();

	const { blocks } = useSelect(
		( select ) => {
			const { getBlocks } = select( blockEditorStore );
			
			return {
				blocks: getBlocks( clientId ),
			};
		},
		[
			clientId,
		]
	);

	let itemArray = [];
	const totalItems = perPage > ( columns * 2 ) ? columns * 2 : perPage;
	for ( let i = 1; i <= totalItems; i++ ) {
		itemArray.push(i);
	}

	const blockContexts = useMemo(
		() =>
			itemArray.map( ( id ) => ( {
				postId: id,
			} ) ),
		[ itemArray ]
	);

	const hasLayoutFlex = columns > 1;
	const blockProps = useBlockProps( {
		className: classnames( {
			'is-flex-container': hasLayoutFlex,
			[ `columns-${ columns }` ]: hasLayoutFlex,
			'wp-block-post-template' : hasLayoutFlex,
		} ),
	} );

	// To avoid flicker when switching active block contexts, a preview is rendered
	// for each block context, but the preview for the active block context is hidden.
	// This ensures that when it is displayed again, the cached rendering of the
	// block preview is used, instead of having to re-render the preview from scratch.
	return (
		<div { ...blockProps }>
			{ blockContexts &&
				blockContexts.map( ( blockContext ) => (
					<BlockContextProvider
						key={ blockContext.postId }
						value={ blockContext }
					>
						{ blockContext.postId ===
						( activeBlockContextId ||
							blockContexts[ 0 ].postId ) ? (
							<PostTemplateInnerBlocks />
						) : null }
						<MemoizedPostTemplateBlockPreview
							blocks={ blocks }
							blockContextId={ blockContext.postId }
							setActiveBlockContextId={ setActiveBlockContextId }
							isHidden={
								blockContext.postId ===
								( activeBlockContextId ||
									blockContexts[ 0 ].postId )
							}
						/>
					</BlockContextProvider>
				) ) }
		</div>
	);
}
