const { __ } = wp.i18n;

import iconCategory from './../../assets/icons/item-categories.svg';
import iconTag from './../../assets/icons/item-tags.svg';

const variations = [
	{
		name: 'category',
		title: __( 'Post Categories' ),
		description: __( 'Display the assigned taxonomy: Post Categories.' ),
		attributes: { term: 'category' },
		isActive: [ 'term' ],
		icon: {
			src: iconCategory,
			foreground: '#7f54b3',
		},
		isDefault: true,
	},
	{
		name: 'post_tag',
		title: __( 'Post Tags' ),
		description: __( 'Display the assigned taxonomy: Post Tags.' ),
		attributes: { term: 'post_tag' },
		isActive: [ 'term' ],
		icon: {
			src: iconTag,
			foreground: '#7f54b3',
		},
	},
];

export default variations;