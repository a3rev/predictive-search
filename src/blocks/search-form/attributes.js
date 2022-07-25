const SearchFormAttributes = {
	blockID: {
		type: 'string',
	},
	// Search Box Container
	align: {
		type: 'string',
		default: 'none',
	},
	mIconEnable: {
		type: 'boolean',
		default: false,
	},
	width: {
		type: 'number',
		default: 100,
	},
	widthUnit: {
		type: 'string',
		default: '%',
	},
	tabletmIconEnable: {
		type: 'boolean',
		default: false,
	},
	tabletwidth: {
		type: 'number',
		default: 100,
	},
	tabletwidthUnit: {
		type: 'string',
		default: '%',
	},
	mobilemIconEnable: {
		type: 'boolean',
		default: true,
	},
	numberPostTypes: {
		type: 'object',
		default: { post: 6, page: 6 },
	},
	numberCustomTypes: {
		type: 'object',
		default: {},
	},
	numberTaxonomies: {
		type: 'object',
		default: { category: 6, post_tag: 6 },
	},
	orderItems: {
		type: 'object',
		default: { post: 0, page: 0, category: 0, post_tag: 0 },
	},
	/**
	 * For previewing?
	 */
	isPreview: {
		type: 'boolean',
		default: false,
	},
};

export default SearchFormAttributes;
