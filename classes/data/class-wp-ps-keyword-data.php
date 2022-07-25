<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\Data;

use A3Rev\WPPredictiveSearch;

class Keyword
{
	public function install_database() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if( ! empty($wpdb->charset ) ) $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			if( ! empty($wpdb->collate ) ) $collate .= " COLLATE $wpdb->collate";
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_ps_keyword = $wpdb->prefix. "ps_keyword";

		if ($wpdb->get_var("SHOW TABLES LIKE '$table_ps_keyword'") != $table_ps_keyword) {
			$sql = "CREATE TABLE IF NOT EXISTS `{$table_ps_keyword}` (
					post_id bigint(20) NOT NULL,
					keyword text NULL,
					PRIMARY KEY  (post_id)
				) $collate; ";

			$wpdb->query($sql);
		}

	}

	/**
	 * Predictive Search Focus Keyword Table - set table name
	 *
	 * @return void
	 */
	public function set_table_wpdbfix() {
		global $wpdb;
		$meta_name = 'ps_keyword';

		$wpdb->ps_keyword = $wpdb->prefix . $meta_name;

		$wpdb->tables[] = 'ps_keyword';
	}

	/**
	 * Predictive Search Focus Keyword Table - return sql
	 *
	 * @return void
	 */
	public function get_sql( $search_keyword = '', $search_keyword_nospecial = '' ) {
		if ( '' == $search_keyword && '' == $search_keyword_nospecial ) {
			return false;
		}

		global $wpdb;

		$sql   = array();
		$join  = array();
		$where = array();

		$join[]      = " LEFT JOIN {$wpdb->ps_keyword} AS pk ON (pp.post_id=pk.post_id) ";

		$sql['join'] = $join;

		$where_ps_keyword = ' ( ';
		$where_ps_keyword .= WPPredictiveSearch\Functions::remove_special_characters_in_mysql( 'pk.keyword', $search_keyword );
		if ( '' != $search_keyword_nospecial ) {
			$where_ps_keyword .= " OR ". WPPredictiveSearch\Functions::remove_special_characters_in_mysql( 'pk.keyword', $search_keyword_nospecial );
		}
		$where_ps_keyword .= ' ) ';
		$where[]                = " OR ( " . $where_ps_keyword . " )";

		$sql['where']           = array();
		$sql['where']['search'] = $where;

		return $sql;
	}

	/**
	 * Insert Predictive Search Focus Keyword
	 */
	public function insert_item( $post_id, $keyword = '' ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->ps_keyword} VALUES(%d, %s)", $post_id, stripslashes( $keyword ) ) );
	}

	/**
	 * Update Predictive Search Focus Keyword
	 */
	public function update_item( $post_id, $keyword = '' ) {
		global $wpdb;

		$value = $this->is_item_existed( $post_id );
		if ( '0' == $value ) {
			return $this->insert_item( $post_id, $keyword );
		} else {
			return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ps_keyword} SET keyword = %s WHERE post_id = %d ", stripslashes( $keyword ), $post_id ) );
		}
	}

	/**
	 * Get Predictive Search Focus Keyword
	 */
	public function get_item( $post_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT keyword FROM {$wpdb->ps_keyword} WHERE post_id = %d  LIMIT 0,1", $post_id ) );
	}

	/**
	 * Check Predictive Search Focus Keyword Existed
	 */
	public function is_item_existed( $post_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS( SELECT 1 FROM {$wpdb->ps_keyword} WHERE post_id = %d LIMIT 0,1 )", $post_id ) );
	}

	/**
	 * Delete Predictive Search Focus Keyword
	 */
	public function delete_item( $post_id ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->ps_keyword} WHERE post_id = %d ", $post_id ) );
	}

	/**
	 * Empty Predictive Search Focus Keywords
	 */
	public function empty_table() {
		global $wpdb;
		return $wpdb->query( "TRUNCATE {$wpdb->ps_keyword}" );
	}
}
