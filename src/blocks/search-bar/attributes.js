const SearchBarAttributes = {
	blockID: {
		type: 'string',
	},
	rootID: {
		type: 'string',
	},
	layout: {
		type: 'string',
		default: 'cat-submit',
	},
	height: {
		type: 'number',
		default: 35,
	},
	enableCustomBorder: {
		type: 'boolean',
		default: false,
	},
	borderColor: {
		type: 'string',
	},
	borderFocusColor: {
		type: 'string',
	},
	backgroundColor: {
		type: 'string',
	},
};

export default SearchBarAttributes;
