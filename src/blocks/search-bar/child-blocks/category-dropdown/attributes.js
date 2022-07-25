const CategoryDropdownAttributes = {
	blockID: {
		type: 'string',
	},
	enable: {
		type: 'boolean',
		default: true,
	},
	position: {
		type: 'string',
		default: 'left',
	},
	taxonomy: {
		type: 'string',
		default: 'category',
	},
	defaultCategory: {
		type: 'string',
		default: '',
	},
	maxWidth: {
		type: 'number',
		default: 30,
	},
	normalTextColor: {
		type: 'string',
	},
	hoverTextColor: {
		type: 'string',
	},
	normalBackgroundColor: {
		type: 'string',
	},
	hoverBackgroundColor: {
		type: 'string',
	},
	normalBorderColor: {
		type: 'string',
	},
	hoverBorderColor: {
		type: 'string',
	},
	enableCustomFont: {
		type: 'boolean',
		default: false,
	},
	enableCustomBorder: {
		type: 'boolean',
		default: false,
	},
	icon: {
		type: 'string',
		default: 'fe_chevronDown',
	},
	iconSize: {
		type: 'number',
		default: 12,
	},
	normalIconColor: {
		type: 'string',
	},
	hoverIconColor: {
		type: 'string',
	},
};

export default CategoryDropdownAttributes;
