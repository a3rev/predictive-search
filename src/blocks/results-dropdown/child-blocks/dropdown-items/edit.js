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
import { googleFontLoader } from '@bit/a3revsoftware.blockpress.typography';

import Inspector from './inspector';

const { __ } = wp.i18n;
const { Component, Fragment } = wp.element;
const { useBlockProps } = wp.blockEditor;

const longDescText = 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo';

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function DropdownItemsEdit( props ) {
	const { attributes } = props;

	const {
		blockID,
		showImg,
		showDesc,
		showCat,
		charactersDescCount,
	} = attributes;

	let { className = '' } = props;

	className = classnames(
		'ac_odd',
		`ac_odd-${ blockID }`,
		className
	);

	const shortDescText = longDescText.substring( 0, charactersDescCount );

	const iconKey1 = 'fas_tshirt';
	const item1 = (
		<GenIcon
			className={ classnames(
				'a3blockpress-svg-icon',
			) }
			name={ iconKey1 }
			icon={
				'fa' === iconKey1.substring( 0, 2 ) ?
					FaIco[ iconKey1 ] :
					Ico[ iconKey1 ]
			}
			size={ 64 }
		/>
	);

	const iconKey2 = 'fas_glasses';
	const item2 = (
		<GenIcon
			className={ classnames(
				'a3blockpress-svg-icon',
			) }
			name={ iconKey2 }
			icon={
				'fa' === iconKey2.substring( 0, 2 ) ?
					FaIco[ iconKey2 ] :
					Ico[ iconKey2 ]
			}
			size={ 64 }
		/>
	);

	const myItem = ( avatar, name, cat ) => {
		return (
			<div className={ 'ajax_search_content' } >
				<div className={ 'result_row' }>
					{ showImg && ( <span className={ 'rs_avatar' }><a href="#">{ avatar }</a></span> ) }
					<div className={ 'rs_content_popup' }>
						<a href="#">
							<span className="rs_name">{ name }</span>
							{ showDesc && ( <span className="rs_description">{ shortDescText }...</span> ) }
						</a>
						{ showCat && ( <span className="rs_cat posted_in">{ __( 'Category' ) }: <a className="rs_cat_link" href="#">{ cat }</a></span> ) }
					</div>
				</div>
			</div>
		);
	};

	const blockProps = useBlockProps( { className } );
	const blockProps2 = useBlockProps( { className: classnames( 'ac_over', className ) } );

	return (
		<Fragment>
			<li { ...blockProps }>
				{ myItem( item1, 'Hello World', 'UnCategories' ) }
			</li>
			<li { ...blockProps2 }>
				{ myItem( item2, 'First Blog Post', 'Newsletter' ) }
			</li>
		</Fragment>
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

		const {
			enableCustomNameFont,
			enableCustomDescFont,
			enableCustomCatFont,
		} = attributes;

		return (
			<Fragment>
				{ isSelected && <Inspector { ...this.props } /> }
				<DropdownItemsEdit { ...this.props } />
				{ enableCustomNameFont && googleFontLoader( 'nameTypo', attributes ) }
				{ enableCustomDescFont && googleFontLoader( 'descTypo', attributes ) }
				{ enableCustomCatFont && googleFontLoader( 'catTypo', attributes ) }
			</Fragment>
		);
	}
}
