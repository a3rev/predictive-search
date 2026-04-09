( function ( wp ) {
	var el                          = wp.element.createElement;
	var Fragment                    = wp.element.Fragment;
	var registerPlugin              = wp.plugins.registerPlugin;
	var PluginDocumentSettingPanel  = wp.editor.PluginDocumentSettingPanel
	                                  || ( wp.editPost && wp.editPost.PluginDocumentSettingPanel );
	var TextControl                 = wp.components.TextControl;
	var ToggleControl               = wp.components.ToggleControl;
	var useSelect                   = wp.data.useSelect;
	var useDispatch                 = wp.data.useDispatch;

	if ( ! PluginDocumentSettingPanel ) {
		return;
	}

	var PredictiveSearchPanel = function () {
		var meta = useSelect( function ( select ) {
			return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		}, [] );

		var editPost = useDispatch( 'core/editor' ).editPost;

		var focusKeywords = meta._predictive_search_focuskw || '';
		var isExcluded    = meta._ps_exclude_item === '1';

		return el(
			PluginDocumentSettingPanel,
			{
				name:  'wpps-predictive-search-panel',
				title: wp.i18n.__( 'Predictive Search', 'wp-predictive-search' ),
				icon:  'search',
			},
			el(
				ToggleControl,
				{
					__nextHasNoMarginBottom: true,
					label:    wp.i18n.__( 'Hide from Predictive Search results', 'wp-predictive-search' ),
					checked:  isExcluded,
					onChange: function ( value ) {
						editPost( {
							meta: { _ps_exclude_item: value ? '1' : '' },
						} );
					},
				}
			),
			! isExcluded
				? el(
					Fragment,
					null,
					el(
						TextControl,
						{
							__nextHasNoMarginBottom: true,
							label:    wp.i18n.__( 'Focus Keywords', 'wp-predictive-search' ),
							help:     wp.i18n.__( 'Enter keywords separated by commas. Example: iPhone, ios', 'wp-predictive-search' ),
							value:    focusKeywords,
							onChange: function ( value ) {
								editPost( {
									meta: { _predictive_search_focuskw: value },
								} );
							},
						}
					)
				)
				: el(
					'p',
					{ style: { fontStyle: 'italic', color: '#757575' } },
					wp.i18n.__( 'This post is hidden from Predictive Search results. Uncheck the toggle above to configure Focus Keywords.', 'wp-predictive-search' )
				)
		);
	};

	registerPlugin( 'wpps-predictive-search', {
		render: PredictiveSearchPanel,
		icon:   'search',
	} );

} )( window.wp );
