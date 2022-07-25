const DropdownFooterAttributes = {
	blockID: {
		type: 'string',
	},
	rootID: {
		type: 'string',
	},
	parentID: {
		type: 'string',
	},
	// Section Footer
	moreTextColor: {
		type: 'string',
	},
	moreLinkColor: {
		type: 'string',
	},
	backgroundColor: {
		type: 'string',
	},
	moreText: {
		type: 'string',
		default: 'See more search results for \'%s\' in:',
	},
	enableCustomMoreTextFont: {
		type: 'boolean',
		default: false,
	},
	enableCustomMoreLinkFont: {
		type: 'boolean',
		default: false,
	},
	moreIconSize: {
		type: 'number',
		default: 12,
	},
	moreIconColor: {
		type: 'string',
	},
};

export default DropdownFooterAttributes;
