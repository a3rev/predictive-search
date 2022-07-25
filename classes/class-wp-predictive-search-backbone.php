<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
/**
 * WordPress Predictive Search Hook Backbone
 *
 * Table Of Contents
 *
 * register_admin_screen()
 */

namespace A3Rev\WPPredictiveSearch;

class Hook_Backbone
{
	public function __construct() {

		add_action( 'wp', array( $this, 'all_results_init' ) );

		// Add script into footer to hanlde the event from widget, popup
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_scripts' ), 11 );
		add_action( 'wp_enqueue_scripts', array( $this, 'include_result_shortcode_script' ), 12 );

		// Include google fonts into header
		add_action( 'wp_enqueue_scripts', array( $this, 'add_google_fonts'), 9 );
	}

	public function add_google_fonts() {

		$google_fonts = array();

		global $wp_predictive_search_sidebar_template_settings;
		global $wp_predictive_search_header_template_settings;
		global $wpps_all_results_pages_settings;

		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_category_dropdown_font']['face'];
		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_input_font']['face'];
		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_popup_heading_font']['face'];
		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_popup_item_name_font']['face'];
		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_popup_item_desc_font']['face'];
		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_popup_item_category_font']['face'];
		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_popup_seemore_font']['face'];
		$google_fonts[] = $wp_predictive_search_sidebar_template_settings['sidebar_popup_more_link_font']['face'];

		$google_fonts[] = $wp_predictive_search_header_template_settings['header_category_dropdown_font']['face'];
		$google_fonts[] = $wp_predictive_search_header_template_settings['header_input_font']['face'];
		$google_fonts[] = $wp_predictive_search_header_template_settings['header_popup_heading_font']['face'];
		$google_fonts[] = $wp_predictive_search_header_template_settings['header_popup_item_name_font']['face'];
		$google_fonts[] = $wp_predictive_search_header_template_settings['header_popup_item_desc_font']['face'];
		$google_fonts[] = $wp_predictive_search_header_template_settings['header_popup_item_category_font']['face'];
		$google_fonts[] = $wp_predictive_search_header_template_settings['header_popup_seemore_font']['face'];
		$google_fonts[] = $wp_predictive_search_header_template_settings['header_popup_more_link_font']['face'];

		$google_fonts[] = $wpps_all_results_pages_settings['title_font']['face'];
		$google_fonts[] = $wpps_all_results_pages_settings['description_font']['face'];
		$google_fonts[] = $wpps_all_results_pages_settings['category_font']['face'];
		$google_fonts[] = $wpps_all_results_pages_settings['category_label_font']['face'];

		$google_fonts = apply_filters( 'wpps_google_fonts', $google_fonts );

		$GLOBALS[WPPS_PREFIX.'fonts_face']->generate_google_webfonts( $google_fonts );
	}

	public function register_plugin_scripts() {
		global $wpps_search_page_id;

		$suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$ps_suffix   = '.min';
		$ps_is_debug = get_option( 'wpps_search_is_debug', 'yes' );
		if ( 'yes' == $ps_is_debug ) {
			$ps_suffix = '';
		}
	?>
    <!-- Predictive Search Widget Template Registered -->
    	<script type="text/template" id="wp_psearch_tempTpl">
    		<?php echo esc_js( "This's temp Template from Predictive Search" ); ?>
    	</script>
    <?php
    	wpps_get_popup_item_tpl();
    	wpps_get_popup_footer_sidebar_tpl();
    	wpps_get_popup_footer_header_tpl();
    ?>

    <?php
    	// If don't have any plugin or theme register font awesome style then register it from plugin framework
		if ( ! wp_style_is( 'font-awesome-styles', 'registered' ) ) {
			$GLOBALS[WPPS_PREFIX.'admin_interface']->register_fontawesome_style();
		}

    	wp_register_style( 'animate', WPPS_CSS_URL . '/animate.css', array(), '3.5.1', 'all' );
    	wp_register_style( 'wp-predictive-search-style', WPPS_CSS_URL . '/wp_predictive_search.css', array( 'dashicons', 'animate', 'font-awesome-styles' ), WPPS_VERSION, 'all' );

    	$_upload_dir = wp_upload_dir();
		$have_dynamic_style = false;
		if ( file_exists( $_upload_dir['basedir'] . '/sass/'.$GLOBALS[WPPS_PREFIX.'less']->css_file_name.'.min.css' ) ) {
			$have_dynamic_style = true;
    		wp_register_style( 'wp-predictive-search-dynamic-style', str_replace(array('http:','https:'), '', $_upload_dir['baseurl'] ) . '/sass/'.$GLOBALS[WPPS_PREFIX.'less']->css_file_name.'.min.css', array( 'wp-predictive-search-style' ), $GLOBALS[WPPS_PREFIX.'less']->get_css_file_version(), 'all' );
    	}

		wp_register_script( 'backbone.localStorage', WPPS_JS_URL . '/backbone.localStorage.js', array( 'jquery', 'underscore', 'backbone' ) , '1.1.9', true );
		wp_register_script( 'wp-predictive-search-autocomplete-script', WPPS_JS_URL . '/ajax-autocomplete/jquery.autocomplete.js', array( 'jquery', 'underscore', 'backbone', 'backbone.localStorage' ), WPPS_VERSION, true );
		wp_register_script( 'wp-predictive-search-backbone', WPPS_JS_URL . '/predictive-search.backbone.js', array( 'jquery', 'underscore', 'backbone' ), WPPS_VERSION, true );
		wp_register_script( 'wp-predictive-search-popup-backbone', WPPS_JS_URL . '/predictive-search-popup.backbone'.$ps_suffix.'.js', array( 'jquery', 'underscore', 'backbone', 'wp-predictive-search-autocomplete-script', 'wp-predictive-search-backbone' ), WPPS_VERSION, true );

		wp_enqueue_style( 'wp-predictive-search-style' );
		if ( $have_dynamic_style ) {
			wp_enqueue_style( 'wp-predictive-search-dynamic-style' );
		}
		wp_enqueue_script( 'wp-predictive-search-popup-backbone' );

		global $wpps_legacy_api;
		$legacy_api_url = $wpps_legacy_api->get_legacy_api_url() . '/get_result_popup/';
		$min_characters = get_option( 'wpps_search_min_characters', 1 );
		$delay_time     = get_option( 'wpps_search_delay_time', 600 );
		$cache_timeout  = get_option( 'wpps_search_cache_timeout', 24 );

		global $wp_predictive_search_input_box_settings;
		$allow_result_effect = $wp_predictive_search_input_box_settings['allow_result_effect'];
		$show_effect         = $wp_predictive_search_input_box_settings['show_effect'];

		wp_localize_script( 'wp-predictive-search-popup-backbone',
			'wpps_vars',
			apply_filters( 'wpps_vars', array(
				'minChars'            => $min_characters,
				'delay'               => $delay_time,
				'cache_timeout'       => $cache_timeout,
				'is_debug'            => $ps_is_debug,
				'legacy_api_url'      => $legacy_api_url,
				'search_page_url'     => get_permalink( $wpps_search_page_id ),
				'permalink_structure' => get_option('permalink_structure' ),
				'allow_result_effect' => $allow_result_effect,
				'show_effect'         => $show_effect,
				'is_rtl'			  => is_rtl() ? 'rtl' : '',
				'item_extra_data'	  => array()
			) )
		);
	}

	public function all_results_init() {
		global $post;
		global $wpps_search_page_id;

		if ( $post && $post->ID != $wpps_search_page_id ) return '';

		$current_lang = '';
		if ( class_exists('SitePress') ) {
			$current_lang = ICL_LANGUAGE_CODE;
		}

		list( 'search_keyword' => $search_keyword, 'search_in' => $search_in, 'search_other' => $search_other, 'cat_in' => $cat_in, 'in_taxonomy' => $in_taxonomy ) = Functions::get_results_vars_values();

		if ( $search_keyword == '' || $search_in == '' ) return;

		$term_id = 0;
		if ( ! empty( $cat_in ) && 'all' != $cat_in ) {
			$term_data = get_term_by( 'slug', $cat_in, $in_taxonomy );
			if ( $term_data ) {
				$term_id = (int) $term_data->term_id;
			}
		}

		$wpps_search_focus_enable = get_option('wpps_search_focus_enable');
		$wpps_search_focus_plugin = get_option('wpps_search_focus_plugin');

		$search_in_have_items = false;

		global $wp_predictive_search;
		$posttypes_support  = $wp_predictive_search->posttypes_support();
		$taxonomies_support = $wp_predictive_search->taxonomies_support();

		$search_other_list = explode(",", $search_other);
		if ( ! is_array( $search_other_list ) ) {
			$search_other_list = array();
		}

		global $ps_search_list, $ps_current_search_in;

		$ps_search_list = $search_all_list = $search_other_list;
		$ps_current_search_in = $search_in;

		// Remove current search in on search other list first
		$search_all_list = array_diff( $search_all_list, (array) $search_in );
		// Add current search in as first element from search other list
		$search_all_list = array_merge( (array) $search_in, $search_all_list );

		if ( count( $search_all_list ) > 0 ) {
			foreach ( $search_all_list as $search_item ) {
				if ( ! empty( $posttypes_support ) && isset( $posttypes_support[$search_item] ) ) {
					$have_post = $wp_predictive_search->check_post_exsited( $search_keyword, $wpps_search_focus_enable, $wpps_search_focus_plugin, $search_item, $term_id, $current_lang );
					if ( $have_post ) {
						if ( ! $search_in_have_items ) {
							$search_in_have_items = true;
							$ps_current_search_in = $search_item;
						}
					} else {
						$ps_search_list = array_diff( $ps_search_list, (array) $search_item );
					}
				} elseif ( ! empty( $taxonomies_support ) && isset( $taxonomies_support[$search_item] ) ) {
					$have_term = $wp_predictive_search->check_taxonomy_exsited( $search_keyword, $search_item, $current_lang );
					if ( $have_term ) {
						if ( ! $search_in_have_items ) {
							$search_in_have_items = true;
							$ps_current_search_in = $search_item;
						}
					} else {
						$ps_search_list = array_diff( $ps_search_list, (array) $search_item );
					}
				} else {
					if ( ! $search_in_have_items ) {
						$search_in_have_items = apply_filters( 'wpps_search_in_have_items', $search_in_have_items, $search_item );
						$ps_current_search_in = apply_filters( 'wpps_current_search_in', $search_item, $search_in_have_items );
					}
				}
			}
		}
	}

	public function include_result_shortcode_script() {
		global $post;
		global $wpps_search_page_id;
		global $wpps_search_page_content_type;

		if ( $post && $post->ID != $wpps_search_page_id ) return '';

		$current_lang = '';
		if ( class_exists('SitePress') ) {
			$current_lang = ICL_LANGUAGE_CODE;
		}

		list( 'search_keyword' => $search_keyword, 'search_in' => $search_in, 'search_other' => $search_other, 'cat_in' => $cat_in, 'in_taxonomy' => $in_taxonomy ) = Functions::get_results_vars_values();

		$permalink_structure = get_option( 'permalink_structure' );

		if ( $search_keyword == '' || $search_in == '' ) return;

		$suffix      = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
		$ps_suffix   = '.min';
		$ps_is_debug = get_option( 'wpps_search_is_debug', 'yes' );
		if ( 'yes' == $ps_is_debug ) {
			$ps_suffix = '';
		}
	?>
    <!-- Predictive Search Results Template Registered -->
    <?php
    	wpps_get_results_item_tpl();
    	wpps_get_results_footer_tpl();
    ?>

    <?php
		wp_register_script( 'wp-predictive-search-results-backbone', WPPS_JS_URL . '/predictive-search-results.backbone'.$ps_suffix.'.js', array( 'jquery', 'underscore', 'backbone', 'wp-predictive-search-backbone' ), WPPS_VERSION, true );
		wp_enqueue_script( 'wp-predictive-search-results-backbone' );

		global $wpps_legacy_api;
		$legacy_api_url = $wpps_legacy_api->get_legacy_api_url() . '/get_all_results/';
		$legacy_api_url = add_query_arg( 'q', $search_keyword, $legacy_api_url );
		if (  ! empty( $cat_in ) ) $legacy_api_url .= '&cat_in=' . $cat_in;
		else $legacy_api_url .= '&cat_in=all';
		$legacy_api_url .= '&in_taxonomy=' . $in_taxonomy;

		global $wp_predictive_search;
		global $ps_current_search_in;

		$search_page_url = get_permalink( $wpps_search_page_id );
		$search_page_parsed = parse_url( $search_page_url );
		if ( $permalink_structure == '' ) {
			$search_page_path = $search_page_parsed['path'];
			$default_navigate = '?page_id='.$wpps_search_page_id.'&rs='.urlencode($search_keyword).'&search_in='.$ps_current_search_in.'&cat_in='.$cat_in.'&in_taxonomy='.$in_taxonomy.'&search_other='.$search_other;
		} else {
			$host_name = $search_page_parsed['host'];
			$search_page_exploded = explode( $host_name , $search_page_url );
			$search_page_path = $search_page_exploded[1];
			$default_navigate = 'keyword/'.urlencode($search_keyword).'/search-in/'.$ps_current_search_in.'/cat-in/'.$cat_in.'/in-taxonomy/'.$in_taxonomy.'/search-other/'.$search_other;
		}

		$wpps_all_results_pages_settings = get_option( 'wpps_all_results_pages_settings' );

		$template_type = isset( $wpps_all_results_pages_settings['template_type'] ) ? $wpps_all_results_pages_settings['template_type'] : 'plugin';
		$theme_container_class = isset( $wpps_all_results_pages_settings['theme_container_class'] ) ? $wpps_all_results_pages_settings['theme_container_class'] : '';
		$theme_container_class = apply_filters( 'wpps_search_result_theme_container_class', $theme_container_class, $ps_current_search_in );

		$theme_container_class = str_replace( array( ', ', ',' ), ' ', $theme_container_class );

		$results_display_type = isset( $wpps_all_results_pages_settings['display_type'] ) ? $wpps_all_results_pages_settings['display_type'] : 'grid';

		$child_container = apply_filters( 'wpps_search_result_theme_child_container', array(), $ps_current_search_in );

		wp_localize_script( 'wp-predictive-search-results-backbone', 'wpps_results_vars', 
			apply_filters( 'wpps_results_vars', 
				array(
					'content_type' => $wpps_search_page_content_type,
					'template_type' => $template_type,
					'display_type' => $results_display_type,
					'theme_container_class' => $theme_container_class,
					'default_navigate' => $default_navigate,
					'search_in' => $ps_current_search_in,
					'ps_lang' => $current_lang,
					'legacy_api_url' => $legacy_api_url,
					'search_page_path' => $search_page_path,
					'permalink_structure' => get_option('permalink_structure' ),
					'taxonomies_support' => $wp_predictive_search->taxonomies_slug_support(),
					'item_extra_data' => array(),
					'child_container' => $child_container
				)
			)
		);
	}
}
