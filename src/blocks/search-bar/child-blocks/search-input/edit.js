/**
 * Internal dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';
import { googleFontLoader } from '@bit/a3revsoftware.blockpress.typography';

import Inspector from './inspector';

import IconLoading from './loading-icon.svg';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { RichText, useBlockProps } = wp.blockEditor;
const { select, dispatch } = wp.data;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function SearchInputEdit( props ) {
	const { attributes, setAttributes } = props;

	const { blockID, placeholder, enableCustomFont } = attributes;

	let { className = '' } = props;

	className = classnames(
		'wpps_nav_fill',
		`wpps_nav_fill-${ blockID }`,
		className
	);

	const blockProps = useBlockProps( { className } );

	return (
		<div { ...blockProps }>
			<div className={ 'wpps_nav_field' }>
				<RichText
					className="wpps_nav_fill_placeholder wpps_search_keyword"
					tagName="div"
					placeholder={ __( 'Placeholder...' ) }
					onChange={ value => setAttributes( { placeholder: value } ) }
					value={ placeholder }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>
				<IconLoading className={ 'wpps_searching_icon' } />
			</div>
			{ enableCustomFont && googleFontLoader( 'typo', attributes ) }
		</div>
	);
}

export default class BlockEdit extends Component {
	componentDidMount() {
		this.setBlockID();
		this.setBackgroundSearchBar();
	}

	componentDidUpdate( previousProps, previousState ) {
		const { attributes } = this.props;

		if ( attributes.blockID !== previousProps.attributes.blockID ) {
			this.setBlockID();
		}

		if ( attributes.backgroundColor !== previousProps.attributes.backgroundColor ) {
			this.setBackgroundSearchBar();
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

	setBackgroundSearchBar() {
		const { clientId, attributes } = this.props;
		const { backgroundColor } = attributes;

		const rootClientId = select( 'core/block-editor' ).getBlockRootClientId( clientId );

		dispatch( 'core/block-editor' ).updateBlockAttributes( rootClientId, { backgroundColor } );
	}

	render() {
		const { isSelected } = this.props;

		return (
			<Fragment>
				{ isSelected && <Inspector { ...this.props } /> }
				<SearchInputEdit { ...this.props } />
			</Fragment>
		);
	}
}
