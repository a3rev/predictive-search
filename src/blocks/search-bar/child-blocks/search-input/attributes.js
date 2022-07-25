const SearchInputAttributes = {
	blockID: {
		type: 'string',
	},
	rootID: {
		type: 'string',
	},
	placeholder: {
		type: 'string',
		default: '',
	},
	enableCustomFont: {
		type: 'boolean',
		default: false,
	},
	textColor: {
		type: 'string',
	},
	backgroundColor: {
		type: 'string',
	},
	iconSize: {
		type: 'number',
		default: 16,
	},
	iconColor: {
		type: 'string',
	},
};

export default SearchInputAttributes;
