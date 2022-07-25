/**
 * Internal dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';

import Inspector from './inspector';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { useBlockProps } = wp.blockEditor;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function DropdownCloseIconEdit( props ) {
	const { attributes } = props;

	const { blockID } = attributes;

	let { className = '' } = props;

	className = classnames(
		'ps_close',
		'dashicons',
		'dashicons-dismiss',
		`ps_close-${ blockID }`,
		className
	);

	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<span className={ className }></span>
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
				<DropdownCloseIconEdit { ...this.props } />
			</Fragment>
		);
	}
}
