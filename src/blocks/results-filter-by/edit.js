/**
 * External dependencies
 */
import classnames from 'classnames';
import shorthash from 'shorthash';

/**
 * WordPress dependencies
 */
const {
	AlignmentControl,
	BlockControls,
	useBlockProps,
	useInnerBlocksProps,
} = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;

const {
	useEffect,
	Fragment
} = wp.element;

import Inspector from './inspector';
const TEMPLATE = [ [ 'core/paragraph', { content: __( 'Sort Search Results by' ) } ] ];

export default function ResultsFilterEdit( props ) {
	const {
		clientId,
		attributes: {
			blockID,
			textAlign,
		},
		setAttributes
	} = props;

	useEffect( () => {
		if ( ! blockID ) {
			setAttributes( { blockID: shorthash.unique( clientId ) } );
		}
	}, [ blockID ] );

	let { className = '' } = props;

	className = classnames(
		`has-text-align-${ textAlign }`,
		'rs_result_other_item',
		className
	);

	const blockProps = useBlockProps( {
		className: classnames( {
			[ `has-text-align-${ textAlign }` ]: textAlign,
			'rs_result_other_item': 'rs_result_other_item'
		} ),
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'wp-block-media-text__content' },
		{ template: TEMPLATE }
	);

	return (
		<Fragment>
			<BlockControls group="block">
				<AlignmentControl
					value={ textAlign }
					onChange={ ( nextAlign ) => {
						setAttributes( { textAlign: nextAlign } );
					} }
				/>
			</BlockControls>
			<Inspector { ...{ ...props } } />
			<div { ...innerBlocksProps } />
			<div className={ classnames( {
					[ `has-text-align-${ textAlign }` ]: textAlign,
					'wpps-result-filter-by-container': 'wpps-result-filter-by-container',
					[ `wpps-result-filter-by-container-${ blockID }` ]: blockID
				} ) }
			>
				<span { ...	( { className: classnames( 'rs_result_other_item_activated', className ) } ) }>
					<a className="ps_navigation ps_navigationpost" href="#">{ __( 'Post' ) }</a>
				</span>
				<span className="ps_navigation_divider"></span>
				<span { ...useBlockProps( { className } ) }>
					<a className="ps_navigation ps_navigationpage" href="#">{ __( 'Page' ) }</a>
				</span>
				<span className="ps_navigation_divider"></span>
				<span { ...useBlockProps( { className } ) }>
					<a className="ps_navigation ps_navigationpost_categories" href="#">{ __( 'Post Categories' ) }</a>
				</span>
				<span className="ps_navigation_divider"></span>
				<span { ...useBlockProps( { className } ) }>
					<a className="ps_navigation ps_navigationpost_tags" href="#">{ __( 'Post Tags' ) }</a>
				</span>
			</div>
		</Fragment>
	);
}
