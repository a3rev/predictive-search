<?php
/**
 * WC Predictive Search Uninstall
 *
 * Uninstalling deletes options, tables, and pages.
 *
 */
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

$plugin_key = 'wp_predictive_search';

// Delete Google Font
delete_option( $plugin_key . '_google_api_key' . '_enable' );
delete_transient( $plugin_key . '_google_api_key' . '_status' );
delete_option( $plugin_key . '_google_font_list' );

if ( get_option( $plugin_key . '_clean_on_deletion' ) == 'yes' ) {
	delete_option( $plugin_key . '_google_api_key' );
	delete_option( $plugin_key . '_toggle_box_open' );
	delete_option( $plugin_key . '-custom-boxes' );

	delete_metadata( 'user', 0,  $plugin_key . '-' . 'plugin_framework_global_box' . '-' . 'opened', '', true );

	delete_option('wpps_all_results_pages_settings');
	delete_option('wpps_search_box_text');
	delete_option('wpps_search_page_id');
	delete_option('wpps_search_page_content_type');
	delete_option('predictive_search_mode');

	delete_option('wpps_search_exclude_posts');
	delete_option('wpps_search_exclude_pages');
	delete_option('wpps_search_focus_enable');
	delete_option('wpps_search_focus_plugin');
	delete_option('wpps_search_post_items');
	delete_option('wpps_search_page_items');
	delete_option('wpps_search_character_max');
	delete_option('wpps_search_width');
	delete_option('wpps_search_padding_top');
	delete_option('wpps_search_padding_bottom');
	delete_option('wpps_search_padding_left');
	delete_option('wpps_search_padding_right');
	delete_option('wpps_search_custom_style');
	delete_option('wpps_search_global_search');

	delete_option('wpps_search_enable_google_analytic');
	delete_option('wpps_search_google_analytic_id');
	delete_option('wpps_search_google_analytic_query_parameter');

	delete_option('wpps_search_is_debug');

	delete_option('wpps_search_remove_special_character');
	delete_option('wpps_search_replace_special_character');
	delete_option('wpps_search_special_characters');

	delete_option('wp_predictive_search_had_sync_posts_data');
	delete_option('wp_predictive_search_synced_posts_data');
	delete_option('wp_predictive_search_synced_categories_data');
	delete_option('wp_predictive_search_synced_tags_data');

	delete_post_meta_by_key('_predictive_search_focuskw');

	wp_delete_post( get_option('wpps_search_page_id') , true );

	global $wpdb;

	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_posts');
	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_keyword');
	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_postmeta');
	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_term_relationships');
	$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ps_exclude');

	$string_ids = $wpdb->get_col("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context='WordPress Predictive Search' ");
	if ( is_array( $string_ids ) && count( $string_ids ) > 0 ) {
		$str = join(',', array_map('intval', $string_ids));
		$wpdb->query("
			DELETE s.*, t.* FROM {$wpdb->prefix}icl_strings s LEFT JOIN {$wpdb->prefix}icl_string_translations t ON s.id = t.string_id
			WHERE s.id IN ({$str})");
		$wpdb->query("DELETE FROM {$wpdb->prefix}icl_string_positions WHERE string_id IN ({$str})");
	}

	delete_option( $plugin_key . '_clean_on_deletion' );
}

// Delete the queries cached
global $wpdb;

$wpdb->query( $wpdb->prepare( 'DELETE FROM '. $wpdb->options . ' WHERE option_name LIKE %s', '%ps_cat_dropdown%' ) );

