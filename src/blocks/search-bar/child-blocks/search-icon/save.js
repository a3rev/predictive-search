/**
 * Internal dependencies
 */
import classnames from 'classnames';
import {
	GenIcon,
	Ico,
	FaIco,
} from '@bit/a3revsoftware.blockpress.icons';

const { useBlockProps } = wp.blockEditor;

export default function save( props ) {
	const { attributes } = props;

	const { icon, iconSize } = attributes;

	const mIcon = icon ? icon : 'fe_search';

	let { className = '' } = attributes;

	className = classnames(
		'a3blockpress-svg-icon',
		`a3blockpress-svg-icon-${ mIcon }`,
		'wpps_nav_submit_icon',
		className
	);

	const blockProps = useBlockProps.save( { className } );

	const searchIcon = mIcon ? (
		<GenIcon
			{ ...blockProps }
			name={ mIcon }
			icon={
				'fa' === mIcon.substring( 0, 2 ) ?
					FaIco[ mIcon ] :
					Ico[ mIcon ]
			}
			size={ iconSize }
		/>
	) : null;

	return searchIcon;
}
