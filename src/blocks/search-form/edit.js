/**
 * Internal dependencies
 */
import shorthash from 'shorthash';
import map from 'lodash/map';
import classnames from 'classnames';

import Inspector from './inspector';

const { Component, Fragment } = wp.element;
const {
	BlockControls,
	BlockAlignmentToolbar,
	useBlockProps,
	useInnerBlocksProps: __stableUseInnerBlocksProps,
	__experimentalUseInnerBlocksProps,
} = wp.blockEditor;

const ALLOWED_BLOCKS = [
	'wp-predictive-search/mobile-icon',
	'wp-predictive-search/search-bar',
	'wp-predictive-search/results-dropdown',
];

const TEMPLATE = [
	[ 'wp-predictive-search/mobile-icon' ],
	[ 'wp-predictive-search/search-bar' ],
	[ 'wp-predictive-search/results-dropdown' ],
];

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function SearchFormEdit( props ) {
	const { attributes } = props;

	const { blockID, align, mIconEnable, tabletmIconEnable, mobilemIconEnable } = attributes;

	let { className = '' } = props;

	className = classnames(
		'wpps_shortcode_container',
		'wpps_block',
		`wpps_block-${ blockID }`,
		`align-${ align }`,
		className
	);

	const blockProps = useBlockProps();

	const useInnerBlocksProps = __stableUseInnerBlocksProps ? __stableUseInnerBlocksProps : __experimentalUseInnerBlocksProps;

	const innerBlocksProps = useInnerBlocksProps(
		{
			className: classnames( 'wpps_bar', {
				search_icon_desktop_only: mIconEnable,
				search_icon_tablet_only: tabletmIconEnable,
				search_icon_only: mobilemIconEnable,
			} ),
		}, {
			template: TEMPLATE,
			templateLock: 'all',
			allowedBlocks: ALLOWED_BLOCKS,
			renderAppender: false,
		}
	);

	return (
		<div { ...blockProps }>
			<div className={ className }
				id={ `wpps-${ blockID }` }
			>
				<div { ...innerBlocksProps } />
			</div>
		</div>
	);
}

export default class BlockEdit extends Component {
	componentDidMount() {
		this.setRootId();
	}

	componentDidUpdate( previousProps, previousState ) {
		const { attributes } = this.props;

		if ( attributes.blockID !== previousProps.attributes.blockID ) {
			this.setRootId();
		}
	}

	setRootId() {
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
			this.setRootIdForChildBlocks( childBlocks[ 0 ], blockID );
		}
	}

	setRootIdForChildBlocks( child, rootID ) {
		const childAllows = [
			'wp-predictive-search/search-bar',
			'wp-predictive-search/search-input',
			'wp-predictive-search/dropdown-footer',
		];

		if ( childAllows.indexOf( child.name ) >= 0 ) {
			wp.data
				.dispatch( 'core/block-editor' )
				.updateBlockAttributes( child.clientId, {
					rootID,
				} );
		}

		if ( child.innerBlocks.length > 0 ) {
			map( child.innerBlocks, superChild => {
				this.setRootIdForChildBlocks( superChild, rootID );
			} );
		}
	}

	render() {
		const { attributes, isSelected, setAttributes } = this.props;

		const { align } = attributes;

		return (
			<Fragment>
				<BlockControls>
					<BlockAlignmentToolbar
						value={ align }
						onChange={ value => setAttributes( { align: value } ) }
						controls={ [ 'left', 'center', 'right' ] }
					/>
				</BlockControls>
				{ isSelected && <Inspector { ...this.props } /> }
				<SearchFormEdit { ...this.props } />
			</Fragment>
		);
	}
}
