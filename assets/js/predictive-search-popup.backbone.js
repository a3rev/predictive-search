jQuery( document ).ready( function( $ ) {
	var wpps_legacy_api_url = wpps_vars.legacy_api_url;
	var wpps_permalink_structure = wpps_vars.permalink_structure;
	var wpps_search_page_url = wpps_vars.search_page_url;
	var wpps_minChars = wpps_vars.minChars;
	var wpps_delay = wpps_vars.delay;
	var wpps_cache_timeout = 24;
	var wpps_is_debug = true;
	var wpps_allow_result_effect = true;
	var wpps_show_effect = 'fadeInUpBig';

	if ( typeof wpps_vars.cache_timeout !== 'undefined' ) {
		wpps_cache_timeout = wpps_vars.cache_timeout;
	}
	if ( typeof wpps_vars.is_debug !== 'undefined' && wpps_vars.is_debug != 'yes' ) {
		wpps_is_debug = false;
	}
	if ( typeof wpps_vars.allow_result_effect !== 'undefined' && wpps_vars.allow_result_effect != 'yes' ) {
		wpps_allow_result_effect = false;
	}
	if ( typeof wpps_vars.show_effect !== 'undefined' ) {
		wpps_show_effect = wpps_vars.show_effect;
	}

	if ( wpps_is_debug ) {
		console.log( 'Predictive Search -- DEBUG' );
	}

	var wp_psearch_popup = { apps:{}, models:{}, collections:{}, views:{} };

	_.templateSettings = {
  		evaluate: /[<{][%{](.+?)[%}][}>]/g,
    	interpolate: /[<{][%{]=(.+?)[%}][}>]/g,
    	escape: /[<{][%{]-(.+?)[%}][}>]/g
	}

	wp_psearch_popup.models.Item = Backbone.Model.extend({
		defaults: {
			title: 'Empty Post',
			keyword: '',
			url: null,
			image_url: null,
			description: null,
			categories: [],
			type: 'post',
			status: true,
			...wpps_vars.item_extra_data
		}
	});

	wp_psearch_popup.collections.Items = Backbone.Collection.extend({
		model: 	wp_psearch_popup.models.Item,

		totalItems: function() {
			return this.where({ status: true }).length;
		},

		haveItems: function( item_type ) {
			return this.where({ type: item_type }).length;
		}
	});

	wp_psearch_popup.views.Item = Backbone.View.extend({
		tagName: 'li',
		className: function( model ) {
			switch( this.model.get('type') ) {
				case 'nothing':
					return 'ac_odd nothing';
				default:
					return 'ac_odd';
			}
		},

		itemTpl: 			_.template( $('#wp_psearch_itemTpl').html().replace( '/*<![CDATA[*/', '' ).replace( '/*]]>*/', '' ) ),
		footerSidebarTpl: 	_.template( $('#wp_psearch_footerSidebarTpl').html().replace( '/*<![CDATA[*/', '' ).replace( '/*]]>*/', '' ) ),
		footerHeaderTpl:	_.template( $('#wp_psearch_footerHeaderTpl').html().replace( '/*<![CDATA[*/', '' ).replace( '/*]]>*/', '' ) ),

		initialize: function() {
			this.listenTo( this.model, 'destroy', this.remove );
		},

		render: function() {
			switch( this.model.get('type') ) {
				case 'header':
					//console.log('Predictive Search Popup - Rendering ' + this.model.get('title') + ' header');
					this.$el.html( '<div class="ajax_search_content_title">' + this.model.get('title') + '</div>' );
				break;

				case 'footerSidebar':
					//console.log('Predictive Search Popup - Rendering footer Sidebar Template');
					this.$el.html( this.footerSidebarTpl( this.model.toJSON() ) );
				break;
				case 'footerHeader':
					//console.log('Predictive Search Popup - Rendering footer Header Template');
					this.$el.html( this.footerHeaderTpl( this.model.toJSON() ) );
				break;
				case 'footerCustom':
					//console.log('Predictive Search Popup - Rendering footer Header Template');
					this.$el.html( _.template( $('#wp_psearch_footerCustomTpl_' + this.model.get('templateID')).html().replace( '/*<![CDATA[*/', '' ).replace( '/*]]>*/', '' ) )( this.model.toJSON() ) );
				break;

				case 'nothing':
					//console.log('Predictive Search Popup - Rendering nothing');
					this.$el.html( '<div class="ajax_no_result">' + this.model.get('title') + '</div>' );
				break;

				default:
					//console.log('Predictive Search Popup - Rendering item ' + this.model.get('title') );
					if ( wpps_allow_result_effect ) {
						this.$el.html( this.itemTpl( this.model.toJSON() ) ).addClass('animated ' + wpps_show_effect).one('webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend', function(){
							$(this).removeClass('animated ' + wpps_show_effect);
						});
					} else {
						this.$el.html( this.itemTpl( this.model.toJSON() ) );
					}

				break;
			}

			return this;
		}

	});

	wp_psearch_popup.views.PopupResult = Backbone.View.extend({

		initialize: function() {
			//console.log('Predictive Search Popup - init');
			this.predictive_search_input = null;
			this.original_ps_search_other = '';
			this.delaytime = 0;
			this.prepend = false;

			this.listenTo( this.collection, 'add', this.addItem );

			this.list_items_container = this.$('.predictive_search_results');

			//this.collection.fetch();
		},

		createItems: function( itemsData, prepend ) {
			this.prepend = prepend;
			$.each( itemsData, function ( index, data ) {
				this.collection.add( data );
			}.bind( this ));

			var ps_id = $( this.predictive_search_input ).data('ps-id');
			var wpps_bar = $( '.wpps_bar-' + ps_id );

			if ( this.original_ps_search_other == '' ) {
				this.original_ps_search_other = wpps_bar.data('ps-search_other');
			}
			if ( this.original_ps_search_in == '' ) {
				this.original_ps_search_in = wpps_bar.data('ps-search_in');
			}
			ps_search_other = this.original_ps_search_other.split(',');

			new_ps_search_other = [];
			new_ps_search_in = '';
			$.each( ps_search_other, function( index, search_item ) {
				if ( this.collection.haveItems( search_item ) > 0 ) {
					new_ps_search_other.push( search_item );
					if ( new_ps_search_in == '' ) new_ps_search_in = search_item;
				}
			}.bind( this ));

			if ( new_ps_search_in != '' ) {
				wpps_bar.data('ps-search_in', new_ps_search_in );
				$( this.predictive_search_input ).parents('.wpps_form').find('input[name=search_in]').val( new_ps_search_in );
			}
			if ( new_ps_search_other.length == 0 ) {
				new_ps_search_other = [ wpps_bar.data('ps-search_in') ];
			}
			wpps_bar.data('ps-search_other', new_ps_search_other.join(',') );
			$( this.predictive_search_input ).parents('.wpps_form').find('input[name=search_other]').val( new_ps_search_other.join(',') );
		},

		addItem: function ( itemModel ) {
			//console.log('Predictive Search Popup - Added item ' + itemModel.get('title') );
			var itemView = new wp_psearch_popup.views.Item({ model: itemModel });
			var itemHtml = itemView.render().el;
			if ( this.prepend ) {
				this.list_items_container.prepend( itemHtml );
			} else {
				this.list_items_container.append( itemHtml );
			}

			$.data( itemHtml, "ac_data", itemModel.attributes );
		},

		clearAll: function() {
			_.invoke( this.collection.where({status: true}), 'destroy');
			return false;
		}

	});

	wp_psearch_popup.apps.App = {
		initialize: function() {

			$(document).on( 'click', '.wpps_nav_submit_bt', this.goToSearchResultPage );
			$('.wpps_form' ).on( 'keypress', function( e ){
				if ( e.keyCode == 13 ) {
					this.goToSearchResultPage( e );
					return false;
				}
			}.bind( this ));

			$('.wpps_category_selector').on('change', function() {
				$(this).parents('.wpps_container').find('.wpps_nav_facade_label').html( $(this).find('option:selected').text().trim() );
			}).on('focus', function() {
				$(this).parents('.wpps_container').addClass('wpps_container_active');
			}).on('blur', function() {
				$(this).parents('.wpps_container').removeClass('wpps_container_active');
			});

			$(document).on( 'click', '.wpps_mobile_icon', function() {
				var wpps_bar = $(this).parents('.wpps_bar');
				var ps_id = wpps_bar.data('ps-id');
				var x = 20;
				var y = $(this).offset().top + $(this).find('svg').outerHeight();
				var width = false;

				if ( window.innerWidth > 680 ) {
					x = wpps_bar.offset().left;
					width = wpps_bar.innerWidth();
				}

				if ( $(this).hasClass( 'active' ) ) {
					$(this).removeClass('active');
					wpps_bar.append( $('.wpps_container-' + ps_id) );
					$('#wpps_mobile_popup-' + ps_id).remove();
				} else {
					$(this).addClass('active');

					$('<div class="wpps_mobile_popup" id="wpps_mobile_popup-' + ps_id + '"></div>').appendTo( document.body );
					wpps_bar.find('.wpps_container').appendTo( $('#wpps_mobile_popup-' + ps_id) );
					$('#wpps_mobile_popup-' + ps_id).css({'transform': 'translate3d('+x+'px, '+y+'px, 0)'});

					if ( width ) {
						$('#wpps_mobile_popup-' + ps_id).css({'width': width+'px' });
						var ps_form = $('#wpps_mobile_popup-' + ps_id).find('.wpps_form');
						var cat_max_wide = ps_form.find('.wpps_category_selector').data('ps-cat_max_wide');
						var cat_max_wide_value = ps_form.innerWidth() * cat_max_wide / 100;
						ps_form.find('.wpps_nav_facade_label').css( 'max-width', cat_max_wide_value );
					}
				}

				$('.ac_input_' + ps_id ).trigger("ps_mobile_icon_click");
			});

			this.initPredictSearch();
		},

		initPredictSearch: function() {
			$(".wpps_search_keyword").each( function() {
				$(this).ps_autocomplete( wpps_legacy_api_url , {
					minChars: wpps_minChars,
					delay: wpps_delay,
					cacheTimeout: wpps_cache_timeout,
					isDebug: wpps_is_debug,
					scrollHeight: 2000,
					loadingClass: "predictive_loading",
					highlight : false
				}, wp_psearch_popup );

				var wpps_bar = $(this).parents('.wpps_bar');
				var search_in = wpps_bar.data('ps-search_in');
				var search_other = wpps_bar.data('ps-search_other');

				wpps_bar.find('input[name=search_in]').val( search_in );
				wpps_bar.find('input[name=search_other]').val( search_other );

				$(this).result( function( event, keyword, url ) {
					if ( keyword != '' ) {
						$( this ).val( keyword );
					}
					if ( url != '' && url != null && url != '#' ) window.location = url;
				});
			}).on('focus', function() {
				$(this).parents('.wpps_container').addClass('wpps_container_active');
			}).on('blur', function() {
				$(this).parents('.wpps_container').removeClass('wpps_container_active');
			});

		},

		goToSearchResultPage: function( event ) {
			var target = $(event.target);
			var wpps_container = target.parents('.wpps_container');
			predictive_search_input = wpps_container.find( '.wpps_search_keyword');
			cat_selected = wpps_container.find('.wpps_category_selector option:selected');
			if ( predictive_search_input.val() != '' && predictive_search_input.val() != predictive_search_input.data('ps-default_text') ) {
				wpps_container.find( '.wpps_form' ).trigger('submit');
			} else if ( '' !== cat_selected.val() ) {
				cat_href = cat_selected.data('href');
				window.location = cat_href;
			}
		}
	};

	var wp_psearch_popup_app = wp_psearch_popup.apps.App;
	wp_psearch_popup_app.initialize();

});
