<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard_AJAX
{

	public function __construct() {
		$this->add_ajax_events();
	}

	/**
	 * Hook in methods - uses WordPress ajax handlers (admin-ajax).
	 */

	public function add_ajax_events() {
		$ajax_events = array(
			'get_exclude_options' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_wpps_' . $ajax_event, array( $this, $ajax_event . '_ajax' ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_wpps_' . $ajax_event, array( $this, $ajax_event . '_ajax' ) );
			}
		}
	}



	/*
	 * Main AJAX handle
	 */

	public function get_exclude_options_ajax() {

		global $wpdb;

		$keyword = isset( $_GET['keyword']) ? sanitize_text_field( $_GET['keyword'] ) : '';
		$from    = isset( $_GET['from'] ) ? sanitize_key( $_GET['from'] ) : 'post';
		$type    = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : 'post';

		if ( empty( $keyword ) ) {
			wp_send_json( array() );
			die();
		}

		$options_data = array();

		switch ( $from ) {
			case 'post':
				$search_results = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, post_title FROM ".$wpdb->prefix."ps_posts WHERE post_type = %s AND post_title LIKE '%s' ORDER BY post_title ASC", $type, '%'. $keyword . '%' ) );
				if ( $search_results ) {
					foreach( $search_results as $item_data ) {
						$options_data[] = array( 'value' => $item_data->post_id, 'caption' => $item_data->post_title );
					}
				}
				break;

			case 'taxonomy':
				$search_results = $wpdb->get_results( $wpdb->prepare( "SELECT term_id, name FROM ".$wpdb->prefix."ps_taxonomy WHERE name LIKE '%s' AND taxonomy = %s ORDER BY name ASC", '%'. $keyword . '%', $type ) );
				if ( $search_results ) {
					foreach( $search_results as $item_data ) {
						$options_data[] = array( 'value' => $item_data->term_id, 'caption' => $item_data->name );
					}
				}
				break;
			default:
				$options_data = apply_filters( 'wpps_get_exclude_options', $options_data, $type, $keyword );
		}
		
		wp_send_json( $options_data );

		die();
	}

}
