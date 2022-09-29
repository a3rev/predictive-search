(function($) {
$(function(){
	var wpps_legacy_results_api_url = wpps_results_vars.legacy_api_url;
	var wpps_legacy_results_permalink_structure = wpps_results_vars.permalink_structure;
	
	var wp_psearch_results = { apps:{}, models:{}, collections:{}, views:{} };

	function validatePSThemeContainer( elem, className ) {
		var already_container = false;
		if ( elem.children().length === 0 ) {
			if ( elem.hasClass( className ) ) {
				already_container = true;
			}
	    } else {
	        elem.children().each( function() {
				already_container = validatePSThemeContainer( $(this), className );
				if ( already_container ) {
					return false;
				}
	        });
	    }

	    return already_container;
	};

	function addClassToEndChildren( elem, className ) {
		if ( elem.children().length === 0 ) {
			if ( ! elem.hasClass( 'no-add' ) ) {
	       		elem.addClass( className );
			}
	    } else {
	        elem.children().each( function() {
				addClassToEndChildren( $(this), className );
	        });
	    }
	};
	
	_.templateSettings = {
  		evaluate: /[<{][%{](.+?)[%}][}>]/g,
    	interpolate: /[<{][%{]=(.+?)[%}][}>]/g,
    	escape: /[<{][%{]-(.+?)[%}][}>]/g
	}
	
	wp_psearch_results.models.Item = Backbone.Model.extend({
		defaults: {
			title: 'Empty Post',
			url: null,
			image_url: null,
			description: null,
			categories: [],
			tags: [],
			type: 'post',
			status: true,
			card : '',
			...wpps_results_vars.item_extra_data
		}
	});
	
	wp_psearch_results.collections.Items = Backbone.Collection.extend({
		model: wp_psearch_results.models.Item	
	});
	
	wp_psearch_results.views.Item = Backbone.View.extend({
		tagName: 'div',
		className: 'rs_result_row',
		
		template: _.template( $('#wp_psearch_result_itemTpl').html().replace( '/*<![CDATA[*/', '' ).replace( '/*]]>*/', '' ) ),
		
		initialize: function() {
			this.listenTo( this.model, 'destroy', this.remove );	
		},
		
		render: function() {
			//console.log('All Result Search - Rendering item ' + this.model.get('title'));
			if ( '' !== this.model.get( 'card' ) ) {
				this.setElement( this.model.get( 'card' ) );
			} else {
				this.$el.html( this.template( this.model.toJSON() ) );
			}
			
			return this;
		}
			
	});
	
	wp_psearch_results.views.ResultContainer = Backbone.View.extend({
		
		cached: {},
		
		addCache: function(search_in, ps_lang, page, value) {
			this.cached[search_in+'-'+ps_lang+'-'+page] = value;
		},
		
		flushCached: function() {
			this.cached = {};
		},
		
		events: {
			'click .ps_navigation' : 'initRouter'
		},
		
		initRouter: function( event ) {
			event.preventDefault();
			
			var target = $(event.target);
			var href = target.data('href');
			Backbone.history.navigate( href, {trigger: true});
		},
		
		footerTpl: _.template( $('#wp_psearch_result_footerTpl').html().replace( '/*<![CDATA[*/', '' ).replace( '/*]]>*/', '' ) ),
		
		initialize: function() {
			//console.log('All Result Search - init');
			this.total_items = 0;
			this.search_in = wpps_results_vars.search_in;
			this.ps_lang = wpps_results_vars.ps_lang;
			this.next_page_number = 1;
			this.is_first_load = true;
			this.listenTo( this.collection, 'add', this.addItem );
			
			this.items_container = this.$('#ps_items_container');
			this.footer = this.$('#ps_footer_container')
			this.ps_more_check = this.$('#ps_more_check');
			this.ps_more_result_popup = this.$('#ps_more_result_popup');
			this.ps_no_more_result_popup = this.$('#ps_no_more_result_popup');
			this.ps_fetching_result_popup = this.$('#ps_fetching_result_popup');
			this.ps_no_result_popup = this.$('#ps_no_result_popup');

			this.perPage = this.$('#ps_items_container').data('perpage');
			this.endless_loading = false;
			$(window).on('scroll', function() {
				if ( this.next_page_number > 1 ) {
					this.endlessScrollLoad();
				}
			}.bind( this ));
		},
		
		render: function() {
			//console.log('All Result Search - Rendering footer');
			this.footer.html( this.footerTpl({ next_page_number: this.next_page_number, first_load: this.is_first_load, total_items: this.total_items }) );
			
			return this;
		},
		
		addItem: function( itemModel ) {
			//console.log('All Result Search - Added item ' + itemModel.get('title') );
			var itemView = new wp_psearch_results.views.Item({ model: itemModel });
			this.items_container.append( itemView.render().el );
			if ( '' == itemModel.get( 'card' ) && 'plugin' == wpps_results_vars.template_type && 'list' == wpps_results_vars.display_type ) {
				this.items_container.append( '<div style="clear:both"></div>' );
			}
		},
		
		clearAll: function() {
			_.invoke( this.collection.where({status: true}), 'destroy');
			this.items_container = this.$( '#ps_items_container' );
			this.items_container.html('');
			return false;	
		},

		prepareContainer: function() {

			if ( 'block' == wpps_results_vars.content_type ) {
				return;
			}

			// Apply Grid for Taxonomy when Template Type is set to Theme
			if ( 'theme' == wpps_results_vars.template_type ) {
				if ( $.inArray( this.search_in, wpps_results_vars.taxonomies_support ) !== -1 ) {
					this.items_container.addClass( 'ps_grid_container' );
				} else {
					this.items_container.removeClass( 'ps_grid_container' );
				}
			}

			// Apply child container to object want to show correct for Theme template
			if ( 'theme' == wpps_results_vars.template_type && ! $.isEmptyObject( wpps_results_vars.child_container ) ) {
				if ( this.search_in in wpps_results_vars.child_container ) {
					var child_container = $( wpps_results_vars.child_container[this.search_in] );
					if ( ! validatePSThemeContainer( child_container, 'ps_theme_items_container' ) ) {
						addClassToEndChildren( child_container, 'ps_theme_items_container' );
					}
					this.items_container.html('').append( child_container );
					this.items_container = this.$('.ps_theme_items_container');
				}
			}

		},
		
		routeSearchIn: function() {
			// reset vars for new Search In
			this.total_items = 0;
			this.next_page_number = 1;
			this.is_first_load = true;
			this.endless_loading = false;
			this.clearAll();
			this.prepareContainer();
			this.getItems();
		},
		
		getItems: function() {
			// Check if have cached
			if ( this.cached[this.search_in+'-'+this.ps_lang+'-'+this.next_page_number] ) {
					item_list = this.cached[this.search_in+'-'+this.ps_lang+'-'+this.next_page_number];
					this.addItems(item_list);
			} else {
				if ( this.is_first_load ) { 
					this.ps_fetching_result_popup.fadeIn('fast');
				} else {
					this.ps_more_result_popup.fadeIn('fast');	
				}
				
				$.get( wpps_legacy_results_api_url, { search_in: this.search_in, ps_lang: this.ps_lang, psp: this.next_page_number, perpage: this.perPage }, function( item_list ) {
					
					// Add to Cached
					this.addCache(this.search_in, this.ps_lang, this.next_page_number, item_list );
					this.addItems(item_list);
					
					if ( this.is_first_load ) {
						this.ps_fetching_result_popup.fadeOut('normal');
					} else {
						this.ps_more_result_popup.fadeOut('normal');
					}
					if ( item_list['total'] == 0 ) {
						if ( this.is_first_load ) {
							this.ps_no_result_popup.fadeIn('normal').fadeOut(1000);
						} else {
							this.ps_no_more_result_popup.fadeIn('normal').fadeOut(1000)
						}
					}
				}.bind( this ));
			}
		},
		
		addItems: function(item_list) {
			this.$('.ps_heading_search_in_name').html(item_list['search_in_name']);
			if ( item_list['total'] > 0 ) {
				this.total_items += item_list['items'].length;
				$.each( item_list['items'], function ( index, data ) {
					if ( typeof data['card'] !== 'undefined' ) {
						data['card'] = data['card'].replace(/<\!--.*?-->/g, "");
					}
					this.collection.add( data );
				}.bind( this ));
				if ( item_list['total'] > item_list['items'].length ) {
					this.next_page_number++;
					this.render();
					this.endless_loading = false;
				} else {
					this.next_page_number = 0;
					this.render();
				}

				this.compatibilityScript();
			} else {
				this.next_page_number = 0;
				this.render();
			}
		},
		
		endlessScrollLoad: function() {
			if ( this.endless_loading == false ) {
				var visibleAtTop = $('#ps_more_check').offset().top + $('#ps_more_check').height() >= $(window).scrollTop();
				var visibleAtBottom = $('#ps_more_check').offset().top <= $(window).scrollTop() + $(window).height();
				if ( visibleAtTop && visibleAtBottom ) {
					this.endless_loading = true;
					this.is_first_load = false;
					this.getItems();
				}
			}
		},

		compatibilityScript: function() {

			// For Animation
			this.items_container.find('.animateMe').each(function() {
				var element = $(this),
					osAnimationClass = element.data("animation");

				element.addClass("animated").addClass(osAnimationClass);
			});

			// For Masonry
			if ( typeof this.items_container.masonry === 'function' || this.items_container.find('.masonry').length > 0 ) {
				if ( this.items_container.find( '.masonry' ).length < 1 ) {
					setTimeout( function() {
						this.items_container.masonry();
						this.items_container.masonry('reload');
					}, 500 );
				} else {
					setTimeout( function() {
						this.items_container.find( '.masonry' ).masonry();
						this.items_container.find( '.masonry' ).masonry('reload');
					}, 500 );
				}
			}
		}
		
	});
	
	wp_psearch_results.apps.App = Backbone.Router.extend({
		routes: {
			"?:query_parameters": "getResults_QueryString",
			"keyword/:s_k/search-in/:s_in": "getResults",
			"keyword/:s_k/search-in/:s_in/cat-in/:c_in": "getResults",
			"keyword/:s_k/search-in/:s_in/search-other/:s_other": "getResults",
			"keyword/:s_k/search-in/:s_in/in-taxonomy/:in_t": "getResults",
			"keyword/:s_k/search-in/:s_in/cat-in/:c_in/in-taxonomy/:int_t": "getResults",
			"keyword/:s_k/search-in/:s_in/in-taxonomy/:int_t/cat-in/:c_in": "getResults",
			"keyword/:s_k/search-in/:s_in/cat-in/:c_in/search-other/:s_other": "getResults",
			"keyword/:s_k/search-in/:s_in/search-other/:s_other/cat-in/:c_in": "getResults",
			"keyword/:s_k/search-in/:s_in/search-other/:s_other/in-taxonomy/:int_t": "getResults",
			"keyword/:s_k/search-in/:s_in/in-taxonomy/:int_t/search-other/:s_other": "getResults",
			"keyword/:s_k/search-in/:s_in/cat-in/:c_in/in-taxonomy/:int_t/search-other/:s_other": "getResults",
			"keyword/:s_k/search-in/:s_in/cat-in/:c_in/search-other/:s_other/in-taxonomy/:int_t": "getResults",
			"keyword/:s_k/search-in/:s_in/search-other/:s_other/cat-in/:c_in/in-taxonomy/:int_t": "getResults",
			"keyword/:s_k/search-in/:s_in/search-other/:s_other/in-taxonomy/:int_t/cat-in/:c_in": "getResults",
			"keyword/:s_k/search-in/:s_in/in-taxonomy/:int_t/cat-in/:c_in/search-other/:s_other": "getResults",
			"keyword/:s_k/search-in/:s_in/in-taxonomy/:int_t/search-other/:s_other/cat-in/:c_in": "getResults"
		},
		
		initialize: function() {
			this.collection = new wp_psearch_results.collections.Items;
			this.resultCointainerView = new wp_psearch_results.views.ResultContainer( { collection: this.collection, el : $('#ps_results_container') } );
			if (Backbone.history){
				Backbone.history.start({pushState: true, root: wpps_results_vars.search_page_path });
			}
			Backbone.history.navigate( wpps_results_vars.default_navigate, {trigger: true});
		},
		
		getResults: function( keyword, search_in ) {
			this.resultCointainerView.search_in = search_in;
			this.resultCointainerView.$('.ps_navigation' ).parent('.rs_result_other_item').removeClass('rs_result_other_item_activated');
			this.resultCointainerView.$('.ps_navigation' + search_in ).parent('.rs_result_other_item').addClass('rs_result_other_item_activated');
			this.resultCointainerView.routeSearchIn();
		},
		
		getResults_QueryString: function( queryString ) {
			if ( wpps_legacy_results_permalink_structure == '' ) {
				var params = this.parseQueryString(queryString);
				this.resultCointainerView.search_in = params.search_in;
				this.resultCointainerView.$('.ps_navigation' ).parent('.rs_result_other_item').removeClass('rs_result_other_item_activated');
				this.resultCointainerView.$('.ps_navigation' + params.search_in ).parent('.rs_result_other_item').addClass('rs_result_other_item_activated');
				this.resultCointainerView.routeSearchIn();
			}
		},
		
		parseQueryString: function(queryString) {
			var params = {};
			if(queryString){
				_.each(
					_.map(decodeURI(queryString).split(/&/g),function(el,i){
						var aux = el.split('='), o = {};
						if(aux.length >= 1){
							var val = undefined;
							if(aux.length == 2)
								val = aux[1];
							o[aux[0]] = val;
						}
						return o;
					}),
					function(o){
						_.extend(params,o);
					}
				);
			}
			return params;
		}
	});
				
	wpps_app.addInitializer(function(){
		var wp_psearch_results_app = new wp_psearch_results.apps.App;
	});
	
});
})(jQuery);