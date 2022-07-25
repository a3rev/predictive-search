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

	const { enable, icon, iconSize } = attributes;

	const dIcon = icon ? icon : 'fe_chevronDown';

	let { className = '' } = attributes;

	className = classnames(
		'a3blockpress-svg-icon',
		`a3blockpress-svg-icon-${ dIcon }`,
		'wpps_nav_down_icon',
		className
	);

	const blockProps = useBlockProps.save( { className } );

	const downIcon = enable && dIcon ? (
		<GenIcon
			{ ...blockProps }
			name={ dIcon }
			icon={
				'fa' === dIcon.substring( 0, 2 ) ?
					FaIco[ dIcon ] :
					Ico[ dIcon ]
			}
			size={ iconSize }
		/>
	) : null;

	return downIcon;
}
