<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */
/**
 * WordPress Predictive Search Legacy API Class
 *
 */

namespace A3Rev\WPPredictiveSearch;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Legacy_API {

	protected $namespace = 'wpps/v1';

	/**
	* Default contructor
	*/
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
	}

	public function rest_api_init() {

        register_rest_route( $this->namespace, '/get_result_popup', array(
            'methods'  => \WP_REST_Server::EDITABLE,
            'callback' => array( $this, 'get_result_popup' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( $this->namespace, '/get_all_results', array(
            'methods'  => \WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_all_results' ),
            'permission_callback' => '__return_true',
        ) );
    }

	public function get_legacy_api_url() {

		$legacy_api_url = rest_url( '/' . $this->namespace );
		$legacy_api_url = str_replace( array( 'https:', 'http:' ), '', $legacy_api_url );

		return apply_filters( 'wpps_legacy_api_url', $legacy_api_url );
	}

	public function get_result_popup() {
		if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
            @ini_set( 'display_errors', false ); // Turn off display_errors to prevent malformed JSON.
        }
        
		global $wpps_search_page_id;
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();
		$taxonomies_support = $wp_predictive_search->taxonomies_support();

		$current_lang = '';
		if ( class_exists('SitePress') ) {
			$current_lang = sanitize_text_field( $_REQUEST['ps_lang'] );
		}

		$rs_items = array();
		$row = 6;
		$text_lenght = 100;
		$show_in_cat = 0;
		$search_keyword = '';
		$last_found_search_term = '';
		$cat_in = 'all';
		$in_taxonomy = 'category';
		$widget_template = 'sidebar';
		$found_items = false;
		$items_search_default = $wp_predictive_search->get_items_search();
		$search_in_default = array();
		foreach ( $items_search_default as $key => $data ) {
			if ( $data['number'] > 0 ) {
				$search_in_default[$key] = $data['number'];
			}
		}
		if ( isset($_REQUEST['row']) && $_REQUEST['row'] > 0) $row = wp_unslash( wp_strip_all_tags( $_REQUEST['row'] ) );
		if ( isset($_REQUEST['text_lenght']) && $_REQUEST['text_lenght'] >= 0) $text_lenght = wp_unslash( wp_strip_all_tags( $_REQUEST['text_lenght'] ) );
		if ( isset($_REQUEST['show_in_cat']) && trim($_REQUEST['show_in_cat']) != '') $show_in_cat = wp_unslash( wp_strip_all_tags( $_REQUEST['show_in_cat'] ) );
		if ( $show_in_cat == 1 ) $show_in_cat = true; else $show_in_cat = false;
		if ( isset($_REQUEST['q']) && trim($_REQUEST['q']) != '') $search_keyword = wp_unslash( wp_strip_all_tags( $_REQUEST['q'] ) );
		if ( isset($_REQUEST['cat_in']) && trim($_REQUEST['cat_in']) != '') $cat_in = wp_unslash( wp_strip_all_tags( $_REQUEST['cat_in'] ) );
		if ( isset($_REQUEST['in_taxonomy']) && trim($_REQUEST['in_taxonomy']) != '') $in_taxonomy = wp_unslash( wp_strip_all_tags( $_REQUEST['in_taxonomy'] ) );
		if ( isset($_REQUEST['search_in']) && trim($_REQUEST['search_in']) != '') $search_in = json_decode( wp_unslash( wp_strip_all_tags( $_REQUEST['search_in'] ) ), true );
		if ( ! is_array($search_in) || count($search_in) < 1 || array_sum($search_in) < 1) $search_in = $search_in_default;
		if ( isset($_REQUEST['widget_template']) && trim($_REQUEST['widget_template']) != '' ) $widget_template = wp_unslash( wp_strip_all_tags( $_REQUEST['widget_template'] ) );

		if ( isset($_REQUEST['last_search_term']) && trim($_REQUEST['last_search_term']) != '') $last_found_search_term = wp_unslash( wp_strip_all_tags( $_REQUEST['last_search_term'] ) );

		if ( $search_keyword != '' ) {
			$search_list = array();
			foreach ($search_in as $key => $number) {
				if ( ! isset( $items_search_default[$key] ) ) continue;
				if ($number > 0)
					$search_list[$key] = $key;
			}

			$wpps_search_focus_enable = get_option( 'wpps_search_focus_enable' );
			$wpps_search_focus_plugin = get_option( 'wpps_search_focus_plugin' );

			$all_items = array();
			$post_list = array();
			$taxonomy_list = array();

			$permalink_structure = get_option( 'permalink_structure' );

			$post_term_id = 0;

			if ( ! empty( $posttypes_support ) ) {
				if ( ! empty( $cat_in ) && 'all' != $cat_in ) {
					$term_data = get_term_by( 'slug', $cat_in, $in_taxonomy );
					if ( $term_data ) {
						$post_term_id = (int) $term_data->term_id;
					}
				}

				foreach ( $posttypes_support as $posttype ) {
					if ( isset( $search_in[$posttype['name']] ) && $search_in[$posttype['name']] > 0 ) {
						$header_text = $posttype['label'];
						$post_list = $wp_predictive_search->get_post_results( $search_keyword, $search_in[$posttype['name']], 0, $wpps_search_focus_enable, $wpps_search_focus_plugin, $post_term_id, $text_lenght, $current_lang, $posttype['name'], true, $show_in_cat );
						${'total_'.$posttype['name']} = $post_list['total'];
						if ( ${'total_'.$posttype['name']} > 0 ) {
							$found_items = true;
							$rs_items[$posttype['name']] = $post_list['items'];
						}
					}
				}
			}

			if ( ! empty( $taxonomies_support ) ) {
				foreach ( $taxonomies_support as $taxonomy ) {
					if ( isset( $search_in[$taxonomy['name']] ) && $search_in[$taxonomy['name']] > 0 ) {
						$header_text = $taxonomy['label'];
						$taxonomy_list = $wp_predictive_search->get_taxonomy_results( $search_keyword, $search_in[$taxonomy['name']], 0, $text_lenght, $taxonomy['name'], $taxonomy['name'], $header_text, $current_lang );
						${'total_'.$taxonomy['name']} = $taxonomy_list['total'];
						if ( ${'total_'.$taxonomy['name']} > 0 ) {
							$found_items = true;
							$rs_items[$taxonomy['name']] = $taxonomy_list['items'];
						}
					}
				}
			}

			$rs_items = apply_filters( 'wpps_api_popup_get_result_items', $rs_items, $search_keyword, $search_in, $post_term_id, $current_lang, $show_in_cat );
			$found_items = apply_filters( 'wpps_api_popup_found_items', $found_items, $search_keyword, $search_in, $post_term_id, $current_lang, $show_in_cat );

			if ( $found_items === false ) {
				if ( 0 == $post_term_id ) {
					$nothing_title = sprintf( wpps_ict_t__( 'Nothing found', __('Nothing found for "%s".', 'wp-predictive-search' ) ), $search_keyword );
				} else {
					$nothing_title = sprintf( wpps_ict_t__( 'Nothing found in Category', __('Nothing found for "%s" in "%s" Category. Try selecting All from the dropdown and search again.', 'wp-predictive-search' ) ), $search_keyword, $term_data->name );
				}

				if ( '' != $last_found_search_term && $last_found_search_term != $search_keyword ) {
					$nothing_title .= ' ' . sprintf( wpps_ict_t__( 'Last Found', __('Showing results for last found search term "%s".', 'wp-predictive-search' ) ), $last_found_search_term );
				}
				$all_items[] = array(
					'title' 	=> $nothing_title,
					'keyword'	=> $search_keyword,
					'type'		=> 'nothing'
				);
			} else {
				foreach ( $search_in as $key => $number ) {
					if ( $number > 0 ) {
						if ( isset( $rs_items[$key] ) ) $all_items = array_merge( $all_items, $rs_items[$key] );
					}
				}

				$search_other = $search_list;

				if ( ! empty( $posttypes_support ) ) {
					foreach ( $posttypes_support as $posttype  ) {
						if ( ! isset( ${'total_'.$posttype['name']} ) || ${'total_'.$posttype['name']} < 1 ) { 
							unset($search_list[$posttype['name']]);
							unset($search_other[$posttype['name']]);
						} elseif (${'total_'.$posttype['name']} <= $search_in[$posttype['name']]) {
							unset($search_list[$posttype['name']]);
						}
					}
				}

				if ( ! empty( $taxonomies_support ) ) {
					foreach ( $taxonomies_support as $taxonomy  ) {
						if ( ! isset( ${'total_'.$taxonomy['name']} ) || ${'total_'.$taxonomy['name']} < 1 ) {
							unset($search_list[$taxonomy['name']]);
							unset($search_other[$taxonomy['name']]);
						} elseif (${'total_'.$taxonomy['name']} <= $search_in[$taxonomy['name']]) {
							unset($search_list[$taxonomy['name']]);
						}
					}
				}

				$search_list = apply_filters( 'wpps_api_popup_search_list', $search_list, $search_keyword, $search_in, $post_term_id, $current_lang, $show_in_cat );
				$search_other = apply_filters( 'wpps_api_popup_search_other', $search_other, $search_keyword, $search_in, $post_term_id, $current_lang, $show_in_cat );

				if ( count( $search_list ) > 0 ) {
					$rs_footer_html = '';
					foreach ($search_list as $other_rs) {
						if ( $permalink_structure == '')
							$search_in_parameter = '&search_in='.$other_rs;
						else
							$search_in_parameter = '/search-in/'.$other_rs;
						if ( $permalink_structure == '')
							$link_search = get_permalink( $wpps_search_page_id ).'&rs='. urlencode($search_keyword) .$search_in_parameter.'&search_other='.implode(",", $search_other).'&cat_in='.$cat_in.'&in_taxonomy='.$in_taxonomy;
						else
							$link_search = rtrim( get_permalink( $wpps_search_page_id ), '/' ).'/keyword/'. urlencode($search_keyword) .$search_in_parameter.'/cat-in/'.$cat_in.'/in-taxonomy/'.$in_taxonomy.'/search-other/'.implode(",", $search_other);
						$rs_item = '<a href="'.$link_search.'">'.$items_search_default[$other_rs]['name'].'<div class="see_more_arrow" aria-label="'.__( 'View More', 'wp-predictive-search' ).'"><svg viewBox="0 0 256 512" height="12" width="12" fill="currentColor" xmlns="http://www.w3.org/2000/svg" style="display: inline-block; vertical-align: middle;"><path d="M224.3 273l-136 136c-9.4 9.4-24.6 9.4-33.9 0l-22.6-22.6c-9.4-9.4-9.4-24.6 0-33.9l96.4-96.4-96.4-96.4c-9.4-9.4-9.4-24.6 0-33.9L54.3 103c9.4-9.4 24.6-9.4 33.9 0l136 136c9.5 9.4 9.5 24.6.1 34z"></path></svg></div></a>';
						$rs_footer_html .= "$rs_item";
					}

					$templateID = '';
					$footertype = 'footerSidebar';
					if ( 'header' == $widget_template ) {
						$footertype = 'footerHeader';
					} elseif ( 'custom' == $widget_template ) {
						$footertype = 'footerCustom';
						$templateID = isset($_REQUEST['templateID']) && trim($_REQUEST['templateID']) != '' ? wp_unslash( wp_strip_all_tags( $_REQUEST['templateID'] ) ) : '';
					}
					$all_items[] = array(
						'title' 	=> $search_keyword,
						'keyword'	=> $search_keyword,
						'description'	=> $rs_footer_html,
						'type'		=> $footertype,
						'templateID' => $templateID
					);
				}
			}

			header( 'Content-Type: application/json', true, 200 );
			die( json_encode( $all_items ) );
		} else {
			header( 'Content-Type: application/json', true, 200 );
			die( json_encode( array() ) );
		}

	}

	public function get_all_results() {
		if ( ! WP_DEBUG || ( WP_DEBUG && ! WP_DEBUG_DISPLAY ) ) {
            @ini_set( 'display_errors', false ); // Turn off display_errors to prevent malformed JSON.
        }

		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();
		$taxonomies_support = $wp_predictive_search->taxonomies_support();

		$current_lang = '';
		if ( class_exists('SitePress') ) {
			$current_lang = sanitize_text_field( $_REQUEST['ps_lang'] );
		}

		$psp = 1;
		$row = 12;
		$search_keyword = '';
		$cat_in = 'all';
		$in_taxonomy = 'category';
		$search_in = 'post';

		$wpps_all_results_pages_settings = get_option( 'wpps_all_results_pages_settings' );

		if ( isset( $_REQUEST['perpage'] ) && $_REQUEST['perpage'] > 0 ) {
			$row = absint( $_REQUEST['perpage'] );
		} elseif ( isset( $wpps_all_results_pages_settings['result_items'] ) && $wpps_all_results_pages_settings['result_items'] > 0  ) {
			$row = $wpps_all_results_pages_settings['result_items'];
		}

		if ( isset( $_REQUEST['psp'] ) && $_REQUEST['psp'] > 0 ) $psp = wp_unslash( wp_strip_all_tags( $_REQUEST['psp'] ) );
		if ( isset( $_REQUEST['q'] ) && trim( $_REQUEST['q'] ) != '' ) $search_keyword = wp_unslash( wp_strip_all_tags( $_REQUEST['q'] ) );
		if ( isset( $_REQUEST['cat_in'] ) && trim( $_REQUEST['cat_in'] ) != '' ) $cat_in = wp_unslash( wp_strip_all_tags( $_REQUEST['cat_in'] ) );
		if ( isset($_REQUEST['in_taxonomy']) && trim($_REQUEST['in_taxonomy']) != '') $in_taxonomy = wp_unslash( wp_strip_all_tags( $_REQUEST['in_taxonomy'] ) );
		if ( isset( $_REQUEST['search_in'] ) && trim( $_REQUEST['search_in'] ) != '' ) $search_in = wp_unslash( wp_strip_all_tags( $_REQUEST['search_in'] ) );

		$item_list = array( 'total' => 0, 'items' => array() );

		if ( $search_keyword != '' && $search_in != '') {
			$show_categories = isset( $wpps_all_results_pages_settings['categories_enable'] ) && 'yes' != $wpps_all_results_pages_settings['categories_enable'] ? false : true;
			$show_tags = isset( $wpps_all_results_pages_settings['tags_enable'] ) && 'yes' != $wpps_all_results_pages_settings['tags_enable'] ? false : true;

			$text_lenght = isset( $wpps_all_results_pages_settings['text_lenght'] ) ? $wpps_all_results_pages_settings['text_lenght'] : 0;

			$post_term_id = 0;

			$start = ( $psp - 1) * $row;

			$wpps_search_focus_enable = get_option('wpps_search_focus_enable');
			$wpps_search_focus_plugin = get_option('wpps_search_focus_plugin');

			if ( ! empty( $posttypes_support ) && isset( $posttypes_support[$search_in] ) ) {
				if ( ! empty( $cat_in ) && 'all' != $cat_in ) {
					$term_data = get_term_by( 'slug', $cat_in, $in_taxonomy );
					if ( $term_data ) {
						$post_term_id = (int) $term_data->term_id;
					}
				}

				$header_text = $posttypes_support[$search_in]['label'];
				$item_list = $wp_predictive_search->get_post_results( $search_keyword, $row, $start, $wpps_search_focus_enable, $wpps_search_focus_plugin, $post_term_id, $text_lenght, $current_lang, $search_in, false , $show_categories, $show_tags, true );
			} elseif ( ! empty( $taxonomies_support ) && isset( $taxonomies_support[$search_in] ) ) {
				$header_text = $taxonomies_support[$search_in]['label'];
				$item_list = $wp_predictive_search->get_taxonomy_results( $search_keyword, $row, $start, $text_lenght, $search_in, $search_in, $header_text, $current_lang, false, true );
			} else {
				$item_list = apply_filters( 'wpps_api_all_results_get_custom_types_list', $item_list, $search_keyword, $search_in, $row, $start, $post_term_id );
			}
		}

		$item_list = apply_filters( 'wpps_api_get_all_results', $item_list, $search_keyword, $search_in, $row, $start, $post_term_id );

		header( 'Content-Type: application/json', true, 200 );
		die( json_encode( $item_list ) );
	}

}
