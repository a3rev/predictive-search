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
const { useBlockProps } = wp.blockEditor;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function MobileIconEdit( props ) {
	const { attributes } = props;

	const { blockID, icon, iconSize } = attributes;

	let { className = '' } = props;

	className = classnames(
		'wpps_mobile_icon',
		`wpps_mobile_icon-${ blockID }`,
		className
	);

	const mIcon = icon ? icon : 'fe_search';

	const mobileIcon = mIcon ? (
		<GenIcon
			className={ classnames(
				'a3blockpress-svg-icon',
				`a3blockpress-svg-icon-${ mIcon }`,
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
			{ mobileIcon }
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
				<MobileIconEdit { ...this.props } />
			</Fragment>
		);
	}
}
