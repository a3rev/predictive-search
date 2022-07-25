/**
 * Internal dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';
import map from 'lodash/map';

import Inspector from './inspector';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const {
	useBlockProps,
	useInnerBlocksProps: __stableUseInnerBlocksProps,
	__experimentalUseInnerBlocksProps,
} = wp.blockEditor;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

const ALLOWED_BLOCKS = [
	'wp-predictive-search/dropdown-close-icon',
	'wp-predictive-search/dropdown-title',
	'wp-predictive-search/dropdown-items',
	'wp-predictive-search/dropdown-footer',
];

const TEMPLATE = [
	[ 'wp-predictive-search/dropdown-close-icon' ],
	[ 'wp-predictive-search/dropdown-title' ],
	[ 'wp-predictive-search/dropdown-items' ],
	[ 'wp-predictive-search/dropdown-footer' ],
];

function ResultsDropdownEdit( props ) {
	const { attributes } = props;

	const { blockID } = attributes;

	let { className = '' } = props;

	className = classnames(
		'predictive_results',
		`ac_results_${ blockID }`,
		className
	);

	const blockProps = useBlockProps( { className } );

	const useInnerBlocksProps = __stableUseInnerBlocksProps ? __stableUseInnerBlocksProps : __experimentalUseInnerBlocksProps;

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: 'predictive_search_results',
		}, {
			template: TEMPLATE,
			templateLock: 'all',
			allowedBlocks: ALLOWED_BLOCKS,
			renderAppender: false,
		}
	);

	return (
		<div { ...blockProps }>
			<ul { ...innerBlocksProps } />
		</div>
	);
}

export default class BlockEdit extends Component {
	componentDidMount() {
		this.setParentIdForChildBlocks();
	}

	componentDidUpdate( previousProps, previousState ) {
		const { attributes } = this.props;

		if ( attributes.blockID !== previousProps.attributes.blockID ) {
			this.setParentIdForChildBlocks();
		}
	}

	setParentIdForChildBlocks() {
		const { clientId, attributes, setAttributes } = this.props;

		const { blockID = shorthash.unique( clientId ) } = attributes;

		if ( ! attributes || typeof attributes.blockID === 'undefined' ) {
			setAttributes( { blockID: blockID } );
			listUniqueIDs.push( blockID );
		} else if ( listUniqueIDs.includes( blockID ) ) {
			const newBlockID = shorthash.unique( clientId );
			setAttributes( { blockID: newBlockID } );
			listUniqueIDs.push( newBlockID );
		} else {
			listUniqueIDs.push( blockID );
		}

		const childBlocks = wp.data.select( 'core/block-editor' ).getBlocksByClientId( clientId );

		if ( childBlocks[ 0 ] && childBlocks[ 0 ].innerBlocks.length > 0 ) {
			map( childBlocks[ 0 ].innerBlocks, child => {
				wp.data
					.dispatch( 'core/block-editor' )
					.updateBlockAttributes( child.clientId, {
						parentID: blockID,
					} );
			} );
		}
	}

	render() {
		const { isSelected } = this.props;

		return (
			<Fragment>
				{ isSelected && <Inspector { ...this.props } /> }
				<ResultsDropdownEdit { ...this.props } />
			</Fragment>
		);
	}
}
