<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\Data;

use A3Rev\WPPredictiveSearch;

class Taxonomy
{
	public function install_database() {
		global $wpdb;
		$collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if( ! empty($wpdb->charset ) ) $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			if( ! empty($wpdb->collate ) ) $collate .= " COLLATE $wpdb->collate";
		}

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_ps_taxonomy = $wpdb->prefix. "ps_taxonomy";

		if ($wpdb->get_var("SHOW TABLES LIKE '$table_ps_taxonomy'") != $table_ps_taxonomy) {
			$sql = "CREATE TABLE IF NOT EXISTS `{$table_ps_taxonomy}` (
					term_id bigint(20) NOT NULL,
					term_taxonomy_id bigint(20) NOT NULL,
					name varchar(200) NOT NULL,
					taxonomy varchar(32) NOT NULL,
					PRIMARY KEY  (term_id),
					KEY term_taxonomy_id (term_taxonomy_id),
					KEY name (name)
				) $collate; ";

			$wpdb->query($sql);
		}

	}

	/**
	 * Predictive Search Taxonomy Table - set table name
	 *
	 * @return void
	 */
	public function set_table_wpdbfix() {
		global $wpdb;
		$meta_name = 'ps_taxonomy';

		$wpdb->ps_taxonomy = $wpdb->prefix . $meta_name;

		$wpdb->tables[] = 'ps_taxonomy';
	}

	/**
	 * Predictive Search Taxonomy Table - return sql
	 *
	 * @return void
	 */
	public function get_sql( $search_keyword = '', $search_keyword_nospecial = '', $number_row = 6, $start = 0, $check_existed = false, $taxonomy = 'category' ) {
		if ( '' == $search_keyword && '' == $search_keyword_nospecial ) {
			return false;
		}

		global $wpdb;
		global $wpps_exclude_data;

		$sql     = array();
		$join    = array();
		$where   = array();
		$groupby = array();
		$orderby = array();

		$items_excluded = apply_filters( 'wpps_items_excluded', $wpps_exclude_data->get_array_items( $taxonomy ), $taxonomy );
		
		$id_excluded    = '';
		if ( ! empty( $items_excluded ) ) {
			$id_excluded = implode( ',', $items_excluded );
		}

		$sql['select']   = array();
		if ( $check_existed ) {
			$sql['select'][] = " 1 ";
		} else {
			$sql['select'][] = " ppc.* ";
		}

		$sql['from']   = array();
		$sql['from'][] = " {$wpdb->ps_taxonomy} AS ppc ";

		$sql['join']   = $join;

		$where[] = " 1=1 ";

		$where[] = $wpdb->prepare( " AND ppc.taxonomy = %s", $taxonomy );

		if ( '' != trim( $id_excluded ) ) {
			$where[] = " AND ppc.term_id NOT IN ({$id_excluded}) ";
		}

		$where_title = ' ( ';
		$where_title .= WPPredictiveSearch\Functions::remove_special_characters_in_mysql( 'ppc.name', $search_keyword );
		if ( '' != $search_keyword_nospecial ) {
			$where_title .= " OR ". WPPredictiveSearch\Functions::remove_special_characters_in_mysql( 'ppc.name', $search_keyword_nospecial );
		}
		$search_keyword_no_s_letter = WPPredictiveSearch\Functions::remove_s_letter_at_end_word( $search_keyword );
		if ( $search_keyword_no_s_letter != false ) {
			$where_title .= " OR ". WPPredictiveSearch\Functions::remove_special_characters_in_mysql( 'ppc.name', $search_keyword_no_s_letter );
		}
		$where_title .= ' ) ';

		$where['search']   = array();
		$where['search'][] = ' ( ' . $where_title . ' ) ';

		$sql['where']      = $where;

		$sql['groupby']    = array();
		$sql['groupby'][]  = ' ppc.term_id ';

		$sql['orderby']    = array();
		if ( $check_existed ) {
			$sql['limit']      = " 0 , 1 ";
		} else {
			global $predictive_search_mode;

			$multi_keywords = explode( ' ', trim( $search_keyword ) );
			if ( 'broad' != $predictive_search_mode ) {
				$sql['orderby'][]  = $wpdb->prepare( " ppc.name NOT LIKE '%s' ASC, ppc.name ASC ", $search_keyword.'%' );
				foreach ( $multi_keywords as $single_keyword ) {
					$sql['orderby'][]  = $wpdb->prepare( " ppc.name NOT LIKE '%s' ASC, ppc.name ASC ", $single_keyword.'%' );
				}
			} else {
				$sql['orderby'][]  = $wpdb->prepare( " ppc.name NOT LIKE '%s' ASC, ppc.name NOT LIKE '%s' ASC, ppc.name ASC ", $search_keyword.'%', '% '.$search_keyword.'%' );
				foreach ( $multi_keywords as $single_keyword ) {
					$sql['orderby'][]  = $wpdb->prepare( " ppc.name NOT LIKE '%s' ASC, ppc.name NOT LIKE '%s' ASC, ppc.name ASC ", $single_keyword.'%', '% '.$single_keyword.'%' );
				}
			}

			$sql['limit']      = " {$start} , {$number_row} ";
		}

		return $sql;
	}

	/**
	 * Insert Predictive Search Taxonomy
	 */
	public function insert_item( $term_id, $term_taxonomy_id, $name = '', $taxonomy = 'category' ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "INSERT INTO {$wpdb->ps_taxonomy} VALUES(%d, %d, %s, %s)", $term_id, $term_taxonomy_id, stripslashes( $name ), stripslashes( $taxonomy ) ) );
	}

	/**
	 * Update Predictive Search Taxonomy
	 */
	public function update_item( $term_id, $term_taxonomy_id, $name = '', $taxonomy = 'category' ) {
		global $wpdb;

		$value = $this->is_item_existed( $term_id );
		if ( '0' == $value ) {
			return $this->insert_item( $term_id, $term_taxonomy_id, $name, $taxonomy );
		} else {
			return $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->ps_taxonomy} SET name = %s WHERE term_id = %d ", stripslashes( $name ), $term_id ) );
		}
	}

	/**
	 * Get Predictive Search Taxonomy
	 */
	public function get_item( $term_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$wpdb->ps_taxonomy} WHERE term_id = %d LIMIT 0,1 ", $term_id ) );
	}

	/**
	 * Check Predictive Search Taxonomy Existed
	 */
	public function is_item_existed( $term_id ) {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT EXISTS( SELECT 1 FROM {$wpdb->ps_taxonomy} WHERE term_id = %d LIMIT 0,1 )", $term_id ) );
	}

	/**
	 * Get Predictive Search Latest Post ID
	 */
	public function get_latest_post_id( $taxonomies = array( 'category', 'post_tag' ) ) {
		global $wpdb;

		$where = '';

		$taxonomies = wpps_esc_sql_array_s( $taxonomies );
		if ( ! empty( $taxonomies ) ) {
			$where = "WHERE taxonomy IN ( ".implode( ',', $taxonomies )." )";
		}

		return $wpdb->get_var( "SELECT term_id FROM {$wpdb->ps_taxonomy} {$where} ORDER BY term_id DESC LIMIT 0,1" );
	}

	/**
	 * Check Latest Post ID is newest from WP database
	 */
	public function is_newest_id( $taxonomies = array( 'category', 'post_tag' ) ) {
		global $wpdb;

		$latest_id = $this->get_latest_post_id( $taxonomies );
		if ( empty( $latest_id ) || is_null( $latest_id ) ) {
			$latest_id = 0;
		}

		$where = '';

		$taxonomies = wpps_esc_sql_array_s( $taxonomies );
		if ( ! empty( $taxonomies ) ) {
			$where = " AND tt.taxonomy IN ( ".implode( ',', $taxonomies )." )";
		}

		$is_not_newest = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT EXISTS( SELECT 1 FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON (t.term_id = tt.term_id) WHERE t.term_id > %d {$where} LIMIT 0,1 )",
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
	public function get_total_items_synched( $taxonomies = array( 'category', 'post_tag' ) ) {
		global $wpdb;

		$where = '';

		$taxonomies = wpps_esc_sql_array_s( $taxonomies );
		if ( ! empty( $taxonomies ) ) {
			$where = "WHERE taxonomy IN ( ".implode( ',', $taxonomies )." )";
		}

		return $wpdb->get_var( "SELECT COUNT(term_id) FROM {$wpdb->ps_taxonomy} {$where} " );
	}

	/**
	 * Get Total Items Need to Sync
	 */
	public function get_total_items_need_sync( $taxonomies = array( 'category', 'post_tag' ) ) {
		global $wpdb;

		$where = '';

		$taxonomies = wpps_esc_sql_array_s( $taxonomies );
		if ( ! empty( $taxonomies ) ) {
			$where = "WHERE tt.taxonomy IN ( ".implode( ',', $taxonomies )." )";
		}

		return $wpdb->get_var( "SELECT COUNT(t.term_id) FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON (t.term_id = tt.term_id) {$where} ");
	}

	/**
	 * Delete Predictive Search Taxonomy
	 */
	public function delete_item( $term_id ) {
		global $wpdb;
		return $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->ps_taxonomy} WHERE term_id = %d ", $term_id ) );
	}

	/**
	 * Empty Predictive Search Taxonomy
	 */
	public function empty_table() {
		global $wpdb;
		return $wpdb->query( "TRUNCATE {$wpdb->ps_taxonomy}" );
	}
}
