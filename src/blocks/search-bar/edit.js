/**
 * Internal dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';

import Inspector from './inspector';

const { Component, Fragment } = wp.element;
const {
	useBlockProps,
	useInnerBlocksProps: __stableUseInnerBlocksProps,
	__experimentalUseInnerBlocksProps,
} = wp.blockEditor;

const ALLOWED_BLOCKS = [
	'wp-predictive-search/category-dropdown',
	'wp-predictive-search/search-icon',
	'wp-predictive-search/search-input',
];

const TEMPLATE = [
	[ 'wp-predictive-search/category-dropdown' ],
	[ 'wp-predictive-search/search-icon' ],
	[ 'wp-predictive-search/search-input' ],
];

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function SearchBarEdit( props ) {
	const { attributes } = props;

	const { blockID } = attributes;

	let { className = '' } = props;

	className = classnames(
		'wpps_container',
		`wpps_container-${ blockID }`,
		className
	);

	const blockProps = useBlockProps( { className } );

	const useInnerBlocksProps = __stableUseInnerBlocksProps ? __stableUseInnerBlocksProps : __experimentalUseInnerBlocksProps;

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: 'wpps_form',
		}, {
			template: TEMPLATE,
			templateLock: 'all',
			allowedBlocks: ALLOWED_BLOCKS,
			renderAppender: false,
		}
	);

	return (
		<div { ...blockProps }>
			<div { ...innerBlocksProps } />
		</div>
	);
}

export default class BlockEdit extends Component {
	componentDidMount() {
		this.setBlockID();
	}

	componentDidUpdate( previousProps, previousState ) {
		const { attributes } = this.props;

		if ( attributes.blockID !== previousProps.attributes.blockID ) {
			this.setBlockID();
		}
	}

	setBlockID() {
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
	}

	render() {
		const { isSelected } = this.props;

		return (
			<Fragment>
				{ isSelected && <Inspector { ...this.props } /> }
				<SearchBarEdit { ...this.props } />
			</Fragment>
		);
	}
}
