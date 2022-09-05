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
const { withSelect } = wp.data;
const { compose } = wp.compose;
const { useBlockProps } = wp.blockEditor;

/**
 * List uniqueID of this block is using on a post to see if the block needs to generate a new ID.
 */
const listUniqueIDs = [];

function CatDropdownEdit( props ) {
	const { attributes, defaultCategoryName, context } = props;

	const { blockID, enable, enableCustomFont, icon, iconSize } = attributes;

	const position = 'submit-cat' === context['wpps/layout'] ? 'right' : 'left';

	let { className = '' } = props;

	className = classnames(
		'wpps_cat_dropdown',
		`wpps_cat_dropdown-${ blockID }`,
		`wpps_nav_${ position }`,
		className,
		{
			disabled: 1 == predictive_search_vars.disabled_cat_dropdown || ! enable,
		}
	);

	const dIcon = icon ? icon : 'fe_chevronDown';

	const downIcon = dIcon ? (
		<GenIcon
			className={ classnames(
				'a3blockpress-svg-icon',
				`a3blockpress-svg-icon-${ dIcon }`,
				'wpps_nav_down_icon',
			) }
			name={ dIcon }
			icon={
				'fa' === dIcon.substring( 0, 2 ) ?
					FaIco[ dIcon ] :
					Ico[ dIcon ]
			}
			size={ iconSize }
		/>
	) : null;

	const blockProps = useBlockProps( { className } );

	return (
		<div { ...blockProps }>
			<div className={ 'wpps_nav_scope' }>
				<div className={ 'wpps_nav_facade' }>
					{ downIcon }
					<span className="wpps_nav_facade_label">{ defaultCategoryName }</span>
				</div>
			</div>
			{ enableCustomFont && googleFontLoader( 'typo', attributes ) }
		</div>
	);
}

class BlockEdit extends Component {
	constructor() {
		super( ...arguments );
		this.state = {
			defaultCategoryName: __( 'All' ),
		};
	}

	componentDidMount() {
		this.setBlockID();
		this.updateDefaultCategoryName();
	}

	componentDidUpdate( previousProps, previousState ) {
		const { attributes } = this.props;

		if ( attributes.blockID !== previousProps.attributes.blockID ) {
			this.setBlockID();
		}

		if ( attributes.defaultCategory !== previousProps.attributes.defaultCategory ) {
			this.updateDefaultCategoryName();
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

	updateDefaultCategoryName() {
		const { attributes } = this.props;
		const { taxonomy, defaultCategory } = attributes;

		let defaultCategoryName = __( 'All' );

		if ( defaultCategory && 0 != predictive_search_vars.taxonomy_terms[taxonomy] ) {
			const categoryList = JSON.parse( predictive_search_vars.taxonomy_terms[taxonomy] );

			const currentCategory = categoryList.find( ( element ) => {
				return element.value === defaultCategory;
			} );

			if ( currentCategory ) {
				defaultCategoryName = currentCategory.label.trim();
			}
		}

		this.setState( { defaultCategoryName } );
	}

	render() {
		const { isSelected } = this.props;

		return (
			<Fragment>
				{ isSelected && <Inspector { ...this.props } /> }
				<CatDropdownEdit { ...this.props } { ...this.state } />
			</Fragment>
		);
	}
}

export default BlockEdit;
