<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\Data;

use A3Rev\WPPredictiveSearch;

class PostMeta
{
	public function install_database() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if( ! empty($wpdb->charset ) ) $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			if( ! empty($wpdb->collate ) ) $collate .= " COLLATE $wpdb->collate";
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_ps_postmeta = $wpdb->prefix. "ps_postmeta";

		if ($wpdb->get_var("SHOW TABLES LIKE '$table_ps_postmeta'") != $table_ps_postmeta) {
			$sql = "CREATE TABLE IF NOT EXISTS `{$table_ps_postmeta}` (
					meta_id bigint(20) NOT NULL auto_increment,
					ps_post_id bigint(20) NOT NULL,
					meta_key varchar(255) NULL,
					meta_value longtext NULL,
					PRIMARY KEY  (meta_id),
					KEY ps_post_id (ps_post_id),
					KEY meta_key (meta_key)
				) $collate; ";

			$wpdb->query($sql);
		}

	}

	/**
	 * Predictive Search Post Meta Table - set table name
	 *
	 * @return void
	 */
	public function set_table_wpdbfix() {
		global $wpdb;
		$meta_name = 'ps_postmeta';

		$wpdb->ps_postmeta = $wpdb->prefix . $meta_name;

		$wpdb->tables[] = 'ps_postmeta';
	}

	/**
	 * Predictive Search Post Meta Table - return sql
	 *
	 * @return void
	 */
	public function get_sql( $search_keyword = '', $search_keyword_nospecial = '', $meta_key_name = '' ) {
		if ( '' == $search_keyword && '' == $search_keyword_nospecial ) {
			return false;
		}

		if ( '' == $meta_key_name ) {
			return false;
		}

		global $wpdb;

		$sql   = array();
		$join  = array();
		$where = array();

		$join[]      = " LEFT JOIN {$wpdb->ps_postmeta} AS ppm ON (pp.post_id=ppm.ps_post_id) ";

		$sql['join'] = $join;

		$where_seo_keyword = ' ( ';
		$where_seo_keyword .= $wpdb->prepare( "ppm.meta_key = %s", $meta_key_name );
		$where_seo_keyword .= ' AND ';
		$where_seo_keyword .= ' ( ';
		$where_seo_keyword .= WPPredictiveSearch\Functions::remove_special_characters_in_mysql( 'CAST(ppm.meta_value AS CHAR)', $search_keyword );
		if ( '' != $search_keyword_nospecial ) {
			$where_seo_keyword .= " OR ". WPPredictiveSearch\Functions::remove_special_characters_in_mysql( 'CAST(ppm.meta_value AS CHAR)', $search_keyword_nospecial );
		}
		$where_seo_keyword .= ' ) ';
		$where_seo_keyword .= ' ) ';
		$where[]                = " OR ( " . $where_seo_keyword . " ) ";

		$sql['where']           = array();
		$sql['where']['search'] = $where;

		return $sql;
	}

	/**
	 * Add Predictive Search Post Meta
	 */
	public function add_item_meta( $object_id, $meta_key, $meta_value, $unique = true ) {
		return add_metadata( 'ps_post', $object_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update Predictive Search Post Meta
	 */
	public function update_item_meta( $object_id, $meta_key, $meta_value, $prev_value = '' ) {
		return update_metadata( 'ps_post', $object_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Get Predictive Search Post Meta
	 */
	public function get_item_meta( $object_id, $meta_key, $single = true ) {
		return get_metadata( 'ps_post', $object_id, $meta_key, $single );
	}

	/**
	 * Delete Predictive Search Post Meta
	 */
	public function delete_item_meta( $object_id, $meta_key, $meta_value = '', $delete_all = false ) {
		return delete_metadata( 'ps_post', $object_id, $meta_key, $meta_value, $delete_all );
	}

	/**
	 * Delete Predictive Search Post Metas
	 */
	public function delete_item_metas( $object_id ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->ps_postmeta} WHERE ps_post_id = %d ", $object_id ) );
	}

	/**
	 * Empty Predictive Search Post Metas
	 */
	public function empty_table() {
		global $wpdb;
		return $wpdb->query( "TRUNCATE {$wpdb->ps_postmeta}" );
	}
}
