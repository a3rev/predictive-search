const DropdownItemsAttributes = {
	blockID: {
		type: 'string',
	},
	parentID: {
		type: 'string',
	},
	// global
	normalBackgroundColor: {
		type: 'string',
	},
	hoverBackgroundColor: {
		type: 'string',
	},
	enableBorder: {
		type: 'boolean',
		default: false,
	},
	normalBorderColor: {
		type: 'string',
	},
	hoverBorderColor: {
		type: 'string',
	},

	// Image
	showImg: {
		type: 'boolean',
		default: true,
	},
	itemImgSize: {
		type: 'number',
		default: 64,
	},

	// Item Name
	normalNameColor: {
		type: 'string',
	},
	hoverNameColor: {
		type: 'string',
	},
	enableCustomNameFont: {
		type: 'boolean',
		default: false,
	},

	// Description
	showDesc: {
		type: 'boolean',
		default: true,
	},
	charactersDescCount: {
		type: 'number',
		default: 100,
	},
	normalDescColor: {
		type: 'string',
	},
	hoverDescColor: {
		type: 'string',
	},
	enableCustomDescFont: {
		type: 'boolean',
		default: false,
	},

	// Categories
	showCat: {
		type: 'boolean',
		default: true,
	},
	normalCatColor: {
		type: 'string',
	},
	hoverCatColor: {
		type: 'string',
	},
	normalCatWordColor: {
		type: 'string',
	},
	hoverCatWordColor: {
		type: 'string',
	},
	enableCustomCatFont: {
		type: 'boolean',
		default: false,
	},
};

export default DropdownItemsAttributes;
