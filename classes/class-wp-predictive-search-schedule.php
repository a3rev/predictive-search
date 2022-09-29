<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

class Schedule
{
	public $error_id = 'auto_sync';

	public function __construct() {

		// Register the schedule
		add_action( 'init', array( $this, 'register_schedule' ) );
	}

	public function register_schedule() {
		$allow_auto_sync_data = get_option( 'wpps_search_allow_auto_sync_data', 'yes' );

		if ( 'yes' == $allow_auto_sync_data ) {
			if ( ! wp_next_scheduled( 'wp_predictive_search_sync_data_scheduled_jobs' ) ) {
				$next_day = date( 'Y-m-d', strtotime('+1 day') );
				$next_time = strtotime( $next_day . ' 00:00:00' );
				$next_time = get_option( 'gmt_offset' ) > 0 ? $next_time - ( 60 * 60 * get_option( 'gmt_offset' ) ) : $next_time +
( 60 * 60 * get_option( 'gmt_offset' ) );

				wp_schedule_event( $next_time, 'daily', 'wp_predictive_search_sync_data_scheduled_jobs' );
			}

			// Hook for run daily
			add_action( 'wp_predictive_search_sync_data_scheduled_jobs', array( $this, 'auto_sync_search_data' ) );

			// Hook for single events
			add_action( 'wp_predictive_search_auto_sync_posts', array( $this, 'auto_sync_posts' ) );
			add_action( 'wp_predictive_search_auto_sync_custom_types', array( $this, 'auto_sync_custom_types' ) );
			add_action( 'wp_predictive_search_auto_sync_taxonomy', array( $this, 'auto_sync_taxonomy' ) );
			add_action( 'wp_predictive_search_auto_sync_relationships', array( $this, 'auto_sync_relationships' ) );
			add_action( 'wp_predictive_search_auto_end_sync', array( $this, 'auto_end_sync' ) );

			// Detect if auto sync is ERROR
			add_action( 'wp_predictive_search_auto_sync_detect_error', array( $this, 'auto_sync_detect_error' ) );
		} else {
			wp_clear_scheduled_hook( 'wp_predictive_search_sync_data_scheduled_jobs' );
		}
	}

	public function auto_sync_search_data() {
		global $wpps_sync;

		$wpps_sync->wp_predictive_search_start_sync( $this->error_id, 'auto' );

		// Set status of auto synced is 'run' for when cron job start process
		update_option( 'wp_predictive_search_auto_synced_full_data_successed', 0 );

		wp_schedule_single_event( time() + 5, 'wp_predictive_search_auto_sync_posts' );
	}

	public function auto_sync_posts() {
		global $wpps_sync;
		global $wp_predictive_search;
		$posttypes = $wp_predictive_search->posttypes_slug_support();

		$is_starting_manual_sync = get_transient( 'wp_predictive_search_starting_manual_sync' );
		if ( false !== $is_starting_manual_sync && (int) $is_starting_manual_sync > time() ) {
			return;
		}

		$is_posts_synced_successed = get_option( 'wp_predictive_search_auto_synced_posts_successed', 0 );
		if ( 1 == $is_posts_synced_successed ) {
			update_option( 'wp_predictive_search_auto_synced_posts_successed', 0 );
		} else {
			add_option( 'wp_predictive_search_auto_synced_posts_successed', 0 );
		}

		/*
		 * Get current post type need to sync
		 */
		$current_post_type_is_syncing = get_option( 'wp_predictive_search_current_post_type_is_syncing' );
		if ( empty( $current_post_type_is_syncing ) || ! in_array( $current_post_type_is_syncing, $posttypes ) ) {
			$current_post_type_is_syncing = $posttypes[0];
		}

		/*
		 * Add single event after 60 minutes when cron job start process for
		 * to send ERROR email to admin if status of auto synced still is not completed
		 */
		wp_schedule_single_event( time() + ( 60 * 5 ), 'wp_predictive_search_auto_sync_detect_error', array( $current_post_type_is_syncing ) );

		$result = $wpps_sync->wp_predictive_search_sync_posts( $current_post_type_is_syncing, $this->error_id, 'auto' );

		// Remove the event send ERROR email if don't get limited execute time or php error
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', array( $current_post_type_is_syncing ) );

		// If status is continue then register sync current post type again for continue
		if ( isset( $result['status'] ) && 'continue' == $result['status'] ) {
			update_option( 'wp_predictive_search_current_post_type_is_syncing', $current_post_type_is_syncing );
			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_posts' );
		} else {

			// Check if current post type is end of posttypes array
			if ( $current_post_type_is_syncing != end( $posttypes ) ) {
				$current_index = array_search( $current_post_type_is_syncing, $posttypes );
				$next_post_type_is_syncing = $posttypes[ $current_index + 1 ];

				// If it not end then register sync next post type
				update_option( 'wp_predictive_search_current_post_type_is_syncing', $next_post_type_is_syncing );
				wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_posts' );
			} else {
				// Register sync taxonomy
				// Set status as successed and delete current post is syncing before sync next object
				update_option( 'wp_predictive_search_auto_synced_posts_successed', 1 );
				delete_option( 'wp_predictive_search_current_post_type_is_syncing' );

				wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_custom_types' );
			}
		}
	}

	public function auto_sync_custom_types() {
		global $wpps_sync;

		$is_starting_manual_sync = get_transient( 'wp_predictive_search_starting_manual_sync' );
		if ( false !== $is_starting_manual_sync && (int) $is_starting_manual_sync > time() ) {
			return;
		}

		$is_synced_successed = get_option( 'wp_predictive_search_auto_synced_custom_types_successed', 0 );
		if ( 1 == $is_synced_successed ) {
			do_action( 'wpps_auto_sync_custom_types_successed' );
			update_option( 'wp_predictive_search_auto_synced_custom_types_successed', 0 );
		} else {
			add_option( 'wp_predictive_search_auto_synced_custom_types_successed', 0 );
		}

		/*
		 * Add single event after 60 minutes when cron job start process for
		 * to send ERROR email to admin if status of auto synced still is not completed
		 */
		wp_schedule_single_event( time() + ( 60 * 5 ), 'wp_predictive_search_auto_sync_detect_error', array( 'custom_types' ) );

		$result = apply_filters( 'wpps_auto_sync_custom_types_result', array( 'status' => 'complete' ) );

		// Remove the event send ERROR email if don't get limited execute time or php error
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', array( 'custom_types' ) );

		// If status is continue then register sync custom types again for continue
		// If status is complete then register sync taxonomy
		if ( isset( $result['status'] ) && 'continue' == $result['status'] ) {
			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_custom_types' );
		} else {
			// Register sync taxonomy
			// Set status as successed before sync next object
			update_option( 'wp_predictive_search_auto_synced_custom_types_successed', 1 );

			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_taxonomy' );
		}
	}

	public function auto_sync_taxonomy() {
		global $wpps_sync;
		global $wp_predictive_search;
		$taxonomies = $wp_predictive_search->taxonomies_slug_support();

		if ( empty( $taxonomies ) ) {
			// Set status as successed before sync next object
			update_option( 'wp_predictive_search_auto_synced_taxonomy_successed', 1 );

			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_relationships' );
		}

		$is_starting_manual_sync = get_transient( 'wp_predictive_search_starting_manual_sync' );
		if ( false !== $is_starting_manual_sync && (int) $is_starting_manual_sync > time() ) {
			return;
		}

		$is_synced_successed = get_option( 'wp_predictive_search_auto_synced_taxonomy_successed', 0 );

		if ( 1 == $is_synced_successed ) {
			global $wpps_taxonomy_data;

			$wpps_taxonomy_data->empty_table();

			update_option( 'wp_predictive_search_auto_synced_taxonomy_successed', 0 );
		} else {
			add_option( 'wp_predictive_search_auto_synced_taxonomy_successed', 0 );
		}

		/*
		 * Add single event after 60 minutes when cron job start process for
		 * to send ERROR email to admin if status of auto synced still is not completed
		 */
		wp_schedule_single_event( time() + ( 60 * 5 ), 'wp_predictive_search_auto_sync_detect_error', $taxonomies );

		$result = $wpps_sync->wp_predictive_search_sync_taxonomy( $this->error_id, $taxonomies, 'auto' );

		// Remove the event send ERROR email if don't get limited execute time or php error
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', $taxonomies );

		// If status is continue then register sync categories again for continue
		// If status is complete then register sync tags
		if ( isset( $result['status'] ) && 'continue' == $result['status'] ) {
			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_taxonomy' );
		} else {
			// Set status as successed before sync next object
			update_option( 'wp_predictive_search_auto_synced_taxonomy_successed', 1 );

			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_relationships' );
		}
	}

	public function auto_sync_relationships() {
		global $wpps_sync;

		$is_starting_manual_sync = get_transient( 'wp_predictive_search_starting_manual_sync' );
		if ( false !== $is_starting_manual_sync && (int) $is_starting_manual_sync > time() ) {
			return;
		}

		$is_synced_successed = get_option( 'wp_predictive_search_auto_synced_relationships_successed', 0 );

		if ( 1 == $is_synced_successed ) {
			global $wpps_term_relationships_data;

			$wpps_term_relationships_data->empty_table();

			update_option( 'wp_predictive_search_auto_synced_relationships_successed', 0 );
		}

		/*
		 * Add single event after 60 minutes when cron job start process for
		 * to send ERROR email to admin if status of auto synced still is not completed
		 */
		wp_schedule_single_event( time() + ( 60 * 5 ), 'wp_predictive_search_auto_sync_detect_error', array( 'relationships' ) );

		$result = $wpps_sync->wp_predictive_search_sync_relationships( $this->error_id, 'auto' );

		// Remove the event send ERROR email if don't get limited execute time or php error
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', array( 'relationships' ) );

		// If status is continue then register sync relationships again for continue
		// If status is complete then register end sync
		if ( isset( $result['status'] ) && 'continue' == $result['status'] ) {
			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_relationships' );
		} else {
			// Set status as successed before sync next object
			update_option( 'wp_predictive_search_auto_synced_relationships_successed', 1 );

			wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_end_sync' );
		}
	}

	public function auto_end_sync() {
		update_option( 'wp_predictive_search_synced_posts_data', 1 );

		// Set status of auto sync is 'completed'
		update_option( 'wp_predictive_search_auto_synced_full_data_successed', 1 );
		update_option( 'wp_predictive_search_auto_synced_completed_time', current_time( 'timestamp' ) );

		// Send Success email to admin
		$this->auto_sync_success_email();
	}

	public function auto_sync_detect_error( $type = 'posts' ) {
		global $wpps_errors_log;

		$is_starting_manual_sync = get_transient( 'wp_predictive_search_starting_manual_sync' );
		if ( false !== $is_starting_manual_sync && (int) $is_starting_manual_sync > time() ) {
			return;
		}

		$auto_synced_completed = get_option( 'wp_predictive_search_auto_synced_full_data_successed', 1 );
		$auto_synced_error_log = trim( $wpps_errors_log->get_error( 'auto_sync' ) );

		// If status of auto sync still is not 'completed' then send Error email to admin
		if ( 0 == $auto_synced_completed ) {

			if ( ! empty( $auto_synced_error_log ) ) {
				$this->auto_sync_error_email( $auto_synced_error_log );
			} else {

				// Continue register child single event if don't have any error ( for cause it's stopped by upgrade theme or plugin or core WordPress )
				wp_schedule_single_event( time() - 5, 'wp_predictive_search_auto_sync_' . $type );
			}
			
		}
	}

	public function auto_sync_success_email() {
		$to_email = get_option( 'wpps_search_schedule_success_recipients', '' );

		// Don't send email if don't have any recipients
		if ( '' == trim( $to_email ) ) {
			return;
		}

		$from_email = get_option( 'admin_email' );
		$from_name = get_option( 'blogname' );

		$headers = array();
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset='. get_option('blog_charset');
		$headers[] = 'From: '.$from_name.' <'.$from_email.'>';

		$subject = sprintf( __( 'Predictive Search Database Synced Completed: %s', 'wp-predictive-search' ), home_url() );
		$content = '<p>' . __( 'Daily Predictive Search full Database sync has been successfully completed for the site', 'wp-predictive-search' );
		$content .= sprintf( '<br><a href="%s" target="_blank">%s</a>', home_url(), home_url() );
		$content .= '</p>';

		wp_mail( $to_email, $subject, $content, $headers, '' );
	}

	public function auto_sync_error_email( $error_log = '' ) {

		if ( empty( $error_log ) ) {
			return false;
		}

		$to_email = get_option( 'wpps_search_schedule_error_recipients', '' );

		// Don't send email if don't have any recipients
		if ( '' == trim( $to_email ) ) {
			$to_email = get_option( 'admin_email' );
		}

		$from_email = get_option( 'admin_email' );
		$from_name = get_option( 'blogname' );

		$headers = array();
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-type: text/html; charset='. get_option('blog_charset');
		$headers[] = 'From: '.$from_name.' <'.$from_email.'>';

		$subject = sprintf( __( 'Predictive Search Database Sync ERROR: %s', 'wp-predictive-search' ), home_url() );
		$content = '<p>'. __( 'There was a problem with the Scheduled WordPress Predictive Search Database sync. It did NOT COMPLETE on the site:', 'wp-predictive-search' );
		$content .= sprintf( '<br><a href="%s" target="_blank">%s</a>', home_url(), home_url() );
		$content .= '</p>';

		$content = '<p>'. __( 'Error log for Debugging:', 'wp-predictive-search' );
		$content .= '<br>'. $error_log;
		$content .= '</p>';

		$content .= '<p>'. __( 'Please login to the site and try running a manual sync', 'wp-predictive-search' );
		$content .= sprintf( '<br><a href="%s" target="_blank">%s</a>', admin_url( 'admin.php?page=wp-predictive-search&tab=performance-settings&box_open=predictive_search_synch_data#predictive_search_manual_sync_heading' ), admin_url( 'admin.php?page=wp-predictive-search&tab=performance-settings&box_open=predictive_search_synch_data#predictive_search_manual_sync_heading' ) );
		$content .= '</p>';

		$content .= '<p>'. __( "If the manual sync won't complete or it fails again tomorrow, please open a support ticket and copy and paste the error log into the ticket.", 'wp-predictive-search' );
		$content .= sprintf( '<br><a href="%s" target="_blank">%s</a>', $GLOBALS[WPPS_PREFIX.'admin_init']->support_url, $GLOBALS[WPPS_PREFIX.'admin_init']->support_url );
		$content .= '</p>';

		wp_mail( $to_email, $subject, $content, $headers, '' );
	}

	public function stop_child_schedule_events_auto_sync() {
		global $wp_predictive_search;
		$taxonomies = $wp_predictive_search->taxonomies_slug_support();
		$posttypes  = $wp_predictive_search->posttypes_slug_support();
 
		set_transient( 'wp_predictive_search_starting_manual_sync', time() + 60, 60 * 5 );

		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_posts' );
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_custom_types' );
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_taxonomy' );
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_relationships' );
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_end_sync' );

		if ( ! empty ( $posttypes ) ) {
			foreach ( $posttypes as $posttype ) {
				wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', array( $posttype ) );
			}
		}

		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', array( 'custom_types' ) );
		wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', array( 'relationships' ) );

		if ( ! empty( $taxonomies ) ) {
			wp_clear_scheduled_hook( 'wp_predictive_search_auto_sync_detect_error', $taxonomies );
		}

		delete_option( 'wp_predictive_search_current_post_type_is_syncing' );
		delete_option( 'wp_predictive_search_auto_synced_posts_successed' );
		delete_option( 'wp_predictive_search_auto_synced_custom_types_successed' );
		delete_option( 'wp_predictive_search_auto_synced_taxonomy_successed' );
		delete_option( 'wp_predictive_search_auto_synced_relationships_successed' );

		do_action( 'wpps_stop_child_schedule_events_auto_sync' );
	}

}
