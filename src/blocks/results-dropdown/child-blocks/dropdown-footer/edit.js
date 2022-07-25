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
const { RichText, useBlockProps } = wp.blockEditor;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function DropdownFooterEdit( props ) {
	const { attributes, setAttributes } = props;

	const {
		blockID,
		moreText,
		enableCustomMoreTextFont,
		enableCustomMoreLinkFont,
		moreIconSize,
	} = attributes;

	let { className = '' } = props;

	className = classnames(
		'ac_odd',
		`ac_odd-${ blockID }`,
		className
	);

	const mIcon = 'fas_angle-right';

	const myIcon = (
		<GenIcon
			className={ classnames(
				'a3blockpress-svg-icon',
				'see_more_arrow',
				`a3blockpress-svg-icon-${ mIcon }`,
			) }
			name={ mIcon }
			icon={
				'fa' === mIcon.substring( 0, 2 ) ?
					FaIco[ mIcon ] :
					Ico[ mIcon ]
			}
			size={ moreIconSize }
		/>
	);

	const blockProps = useBlockProps( { className } );

	return (
		<li { ...blockProps }>
			<div rel="more_result" className="more_result">
				<RichText
					tagName="span"
					onChange={ value => setAttributes( { moreText: value } ) }
					value={ moreText }
					allowedFormats={ [ 'core/bold', 'core/italic' ] }
				/>
				<a href="#">
					{ __( 'Item Name' ) }
					{ myIcon }
				</a>
			</div>
			{ enableCustomMoreTextFont && googleFontLoader( 'moreTextTypo', attributes ) }
			{ enableCustomMoreLinkFont && googleFontLoader( 'moreLinkTypo', attributes ) }
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
		const { isSelected } = this.props;

		return (
			<Fragment>
				{ isSelected && <Inspector { ...this.props } /> }
				<DropdownFooterEdit { ...this.props } />
			</Fragment>
		);
	}
}
