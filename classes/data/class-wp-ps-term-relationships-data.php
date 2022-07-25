<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\Data;

class Relationships
{
	public function install_database() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if( ! empty($wpdb->charset ) ) $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			if( ! empty($wpdb->collate ) ) $collate .= " COLLATE $wpdb->collate";
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_ps_term_relationships = $wpdb->prefix. "ps_term_relationships";

		if ($wpdb->get_var("SHOW TABLES LIKE '$table_ps_term_relationships'") != $table_ps_term_relationships) {
			$sql = "CREATE TABLE IF NOT EXISTS `{$table_ps_term_relationships}` (
					object_id bigint(20) NOT NULL,
					term_id bigint(20) NOT NULL,
					PRIMARY KEY (object_id,term_id),
					KEY term_id (term_id)
				) $collate; ";

			$wpdb->query($sql);
		}

	}

	/**
	 * Predictive Search Term Relationships Table - set table name
	 *
	 * @return void
	 */
	public function set_table_wpdbfix() {
		global $wpdb;
		$meta_name = 'ps_term_relationships';

		$wpdb->ps_term_relationships = $wpdb->prefix . $meta_name;

		$wpdb->tables[] = 'ps_term_relationships';
	}

	/**
	 * Predictive Search Term Relationships Table - return sql
	 *
	 * @return void
	 */
	public function get_sql( $term_id, $field_post_id = 'post_id', $sencond_field = '', $related_fields = 'OR' ) {

		global $wpdb;

		$sql   = array();
		$where = array();

		$items_include = $this->get_array_objects( $term_id );

		if ( is_array( $items_include ) && count( $items_include ) > 0 ) {
			$ids_include    = implode( ',', $items_include );

			$where_line = " AND ";

			if ( ! empty( $sencond_field ) ) {
				$where_line .= " ( pp.{$field_post_id} IN ({$ids_include}) " . $related_fields . " pp.{$sencond_field} IN ({$ids_include}) ) ";
			} else {
				$where_line .= " pp.{$field_post_id} IN ({$ids_include}) ";
			}

			$where[] = $where_line;

			$sql['where'] = $where;
		}

		return $sql;
	}

	/**
	 * Insert Predictive Search Term Relationships
	 */
	public function insert_item( $object_id, $term_id, $check_existed = true ) {
		global $wpdb;

		if ( ! $check_existed || $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS( SELECT 1 FROM {$wpdb->ps_term_relationships} WHERE object_id = %d AND term_id = %d LIMIT 0, 1 )", $object_id, $term_id ) ) != '1' ) {
			return $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->ps_term_relationships} VALUES(%d, %d)", $object_id, $term_id ) );
		} else {
			return false;
		}
	}

	/**
	 * Get Predictive Search Term Relationships
	 */
	public function get_terms( $object_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT term_id FROM {$wpdb->ps_term_relationships} WHERE object_id = %d ", $object_id ) );
	}

	/**
	 * Get Predictive Search Array Term Relationships
	 */
	public function get_array_terms( $object_id ) {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare( "SELECT term_id FROM {$wpdb->ps_term_relationships} WHERE object_id = %d ", $object_id ) );
	}

	/**
	 * Get Predictive Search Term Relationships
	 */
	public function get_objects( $term_id ) {
		global $wpdb;
		return $wpdb->get_results( $wpdb->prepare( "SELECT object_id FROM {$wpdb->ps_term_relationships} WHERE term_id = %d ", $term_id ) );
	}

	/**
	 * Get Predictive Search Array Term Relationships
	 */
	public function get_array_objects( $term_id ) {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->ps_term_relationships} WHERE term_id = %d ", $term_id ) );
	}

	/**
	 * Get Predictive Search Latest Object ID
	 */
	public function get_latest_post_id() {
		global $wpdb;

		return $wpdb->get_row( "SELECT object_id, term_id FROM {$wpdb->ps_term_relationships} ORDER BY object_id DESC, term_id DESC LIMIT 0,1" );
	}

	/**
	 * Check Latest Post ID is newest from WP database
	 */
	public function is_newest_id( $taxonomies = array( 'category', 'post_tag' ) ) {
		global $wpdb;

		$latest_data = $this->get_latest_post_id();
		if ( ! empty( $latest_data ) && ! is_null( $latest_data ) ) {
			$latest_id      = $latest_data->object_id;
			$latest_term_ID = $latest_data->term_id;
		} else {
			$latest_id      = 0;
			$latest_term_ID = 0;
		}

		$where = '';

		$taxonomies = wpps_esc_sql_array_s( $taxonomies );
		if ( ! empty( $taxonomies ) ) {
			$where = " AND tt.taxonomy IN ( ".implode( ',', $taxonomies )." )";
		}

		$is_not_newest = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT EXISTS( SELECT 1 FROM {$wpdb->term_relationships} AS tr INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE ( ( tr.object_id = %d AND tt.term_id > %d ) OR tr.object_id > %d ) {$where} LIMIT 0,1 )",
				$latest_id,
				$latest_term_ID,
				$latest_id
			)
		);

		if ( '1' != $is_not_newest ) {
			return true;
		}

		return false;
	}

	/**
	 * Get Total Items Synched
	 */
	public function get_total_items_synched() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(object_id) FROM {$wpdb->ps_term_relationships} " );
	}

	public function get_total_items_need_sync( $taxonomies = array( 'category', 'post_tag' ) ) {
		global $wpdb;

		$where = '';

		$taxonomies = wpps_esc_sql_array_s( $taxonomies );
		if ( ! empty( $taxonomies ) ) {
			$where = "WHERE tt.taxonomy IN ( ".implode( ',', $taxonomies )." )";
		}

		return $wpdb->get_var( "SELECT COUNT( tr.object_id ) FROM {$wpdb->term_relationships} AS tr INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) {$where} " );
	}

	/**
	 * Delete Predictive Search Term Relationships
	 */
	public function delete_object( $object_id ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->ps_term_relationships} WHERE object_id = %d ", $object_id ) );
	}

	/**
	 * Delete Predictive Search Term Relationships
	 */
	public function delete_term( $term_id ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->ps_term_relationships} WHERE term_id = %d ", $term_id ) );
	}

	/**
	 * Empty Predictive Search Term Relationships
	 */
	public function empty_table() {
		global $wpdb;
		return $wpdb->query( "TRUNCATE {$wpdb->ps_term_relationships}" );
	}
}
