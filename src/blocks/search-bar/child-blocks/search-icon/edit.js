/**
 * Internal dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';
import {
	GenIcon,
	Ico,
	FaIco,
} from '@bit/a3revsoftware.blockpress.icons';

import Inspector from './inspector';

const { Component, Fragment } = wp.element;
const { withSelect } = wp.data;
const { compose } = wp.compose;
const { useBlockProps } = wp.blockEditor;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function SearchIconEdit( props ) {
	const { attributes, setAttributes, isSelected, position } = props;

	const { blockID, icon, iconSize } = attributes;

	let { className = '' } = props;

	className = classnames(
		'wpps_search_submit',
		`wpps_search_submit-${ blockID }`,
		`wpps_nav_${ position }`,
		className
	);

	const mIcon = icon ? icon : 'fe_search';

	const searchIcon = mIcon ? (
		<GenIcon
			className={ classnames(
				'a3blockpress-svg-icon',
				`a3blockpress-svg-icon-${ mIcon }`,
				'wpps_nav_submit_icon',
			) }
			name={ mIcon }
			icon={
				'fa' === mIcon.substring( 0, 2 ) ?
					FaIco[ mIcon ] :
					Ico[ mIcon ]
			}
			size={ iconSize }
		/>
	) : null;

	const blockProps = useBlockProps( { className } );

	return (
		<div { ...blockProps }>
			<div className={ 'wpps_nav_submit' }>
				{ searchIcon }
			</div>
		</div>
	);
}

class BlockEdit extends Component {
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
				<SearchIconEdit { ...this.props } />
			</Fragment>
		);
	}
}

export default compose( [
	withSelect( ( select, ownProps ) => {
		const { clientId } = ownProps;
		const rootClientId = select( 'core/block-editor' ).getBlockParentsByBlockName( clientId, 'wp-predictive-search/search-bar' );
		const parentAttributes = select( 'core/block-editor' ).getBlockAttributes( rootClientId );
		const position = 'submit-cat' === parentAttributes.layout ? 'left' : 'right';

		return {
			position,
		};
	} ),
] )( BlockEdit );
