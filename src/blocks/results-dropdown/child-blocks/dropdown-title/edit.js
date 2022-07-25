/**
 * Internal dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';
import { googleFontLoader } from '@bit/a3revsoftware.blockpress.typography';

import Inspector from './inspector';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { useBlockProps } = wp.blockEditor;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function DropdownTitleEdit( props ) {
	const { attributes } = props;

	const { blockID } = attributes;

	let { className = '' } = props;

	className = classnames(
		'ac_odd',
		`ac_odd-${ blockID }`,
		className
	);

	const blockProps = useBlockProps( { className } );

	return (
		<li { ...blockProps }>
			<div className="ajax_search_content_title">
				{ __( 'Item Name' ) }
			</div>
		</li>
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
		const { attributes, isSelected } = this.props;

		const { enableCustomFont } = attributes;

		return (
			<Fragment>
				{ isSelected && <Inspector { ...this.props } /> }
				<DropdownTitleEdit { ...this.props } />
				{ enableCustomFont && googleFontLoader( 'typo', attributes ) }
			</Fragment>
		);
	}
}
