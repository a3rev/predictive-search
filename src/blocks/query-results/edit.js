/**
 * External dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';

/**
 * WordPress dependencies
 */
const {
	BlockControls,
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
} = wp.blockEditor;

const { SelectControl } = wp.components;

const { __ } = wp.i18n;

const {
	useEffect,
	Fragment
} = wp.element;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

const TEMPLATE = [ 
	[ 'wpps-result/heading' ],
	[ 'wpps-result/filter-by' ],
	[ 'wpps-result/item-template' ], 
];
export function QueryContent( {
	attributes,
	setAttributes,
} ) {
	const {
		tagName: TagName = 'div',
	} = attributes;

	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps( blockProps, {
		template: TEMPLATE
	} );

	return (
		<Fragment>
			<InspectorControls __experimentalGroup="advanced">
				<SelectControl
					label={ __( 'HTML element' ) }
					options={ [
						{ label: __( 'Default (<div>)' ), value: 'div' },
						{ label: '<main>', value: 'main' },
						{ label: '<section>', value: 'section' },
						{ label: '<aside>', value: 'aside' },
					] }
					value={ TagName }
					onChange={ ( value ) =>
						setAttributes( { tagName: value } )
					}
				/>
			</InspectorControls>
			<TagName { ...innerBlocksProps } />
		</Fragment>
	);
}

const QueryEdit = ( props ) => {
	const {
		clientId,
		attributes: {
			blockID,
		},
		setAttributes
	} = props;

	useEffect( () => {
		if ( ! blockID ) {
			setAttributes( { blockID: shorthash.unique( clientId ) } );
		}
	}, [ blockID ] );

	const Component = QueryContent;
	return (
		<Fragment>
			<Inspector { ...{ ...props } } />
			<Component { ...props } />
		</Fragment>
	);
};

export default QueryEdit;
