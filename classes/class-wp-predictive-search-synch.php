<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

class Sync
{

	public $error_id = 'manual_sync';

	public function __construct() {

		// Synch for post
		add_action( 'init', array( $this, 'sync_process_post' ), 1 );

		// Sync for Taxonomy
		add_action( 'init', array( $this, 'synch_taxonomies' ) );

		// Synch for Term Relationships
		add_action( 'delete_term', array( $this, 'synch_delete_term_relationships' ), 10, 4 );

		add_action( 'admin_notices', array( $this, 'start_sync_data_notice' ), 11 );

		/*
		 *
		 * Synch for custom mysql query from 3rd party plugin
		 * Call below code on 3rd party plugin when create post by mysql query
		 * do_action( 'mysql_inserted_post', $post_id );
		 */
		add_action( 'mysql_inserted_post', array( $this, 'synch_mysql_inserted_post' ) );

		if ( is_admin() ) {
			// AJAX sync data
			add_action('wp_ajax_wp_predictive_search_start_sync', array( $this, 'wp_predictive_search_start_sync_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_start_sync', array( $this, 'wp_predictive_search_start_sync_ajax' ) );

			add_action('wp_ajax_wp_predictive_search_sync_posttype', array( $this, 'wp_predictive_search_sync_posts_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_sync_posttype', array( $this, 'wp_predictive_search_sync_posts_ajax' ) );

			add_action('wp_ajax_wp_predictive_search_sync_taxonomy', array( $this, 'wp_predictive_search_sync_taxonomy_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_sync_taxonomy', array( $this, 'wp_predictive_search_sync_taxonomy_ajax' ) );

			add_action('wp_ajax_wp_predictive_search_sync_relationships', array( $this, 'wp_predictive_search_sync_relationships_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_sync_relationships', array( $this, 'wp_predictive_search_sync_relationships_ajax' ) );

			add_action('wp_ajax_wp_predictive_search_sync_end', array( $this, 'wp_predictive_search_sync_end_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_sync_end', array( $this, 'wp_predictive_search_sync_end_ajax' ) );

			add_action('wp_ajax_wp_predictive_search_manual_sync_error', array( $this, 'wp_predictive_search_manual_sync_error_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_manual_sync_error', array( $this, 'wp_predictive_search_manual_sync_error_ajax' ) );
		}
	}

	public function synch_taxonomies() {
		global $wp_predictive_search;
		$taxonomies_support = $wp_predictive_search->taxonomies_support();
		if ( ! empty ( $taxonomies_support ) ) {
			foreach ( $taxonomies_support as $taxonomy ) {
				add_action( 'created_' . $taxonomy['name'], array( $this, 'synch_save_taxonomy' ), 10, 2 );
				add_action( 'edited_' . $taxonomy['name'], array( $this, 'synch_save_taxonomy' ), 10, 2 );
				add_action( 'delete_' . $taxonomy['name'], array( $this, 'synch_delete_taxonomy' ), 10, 3 );
			}
		}
	}

	public function start_sync_data_notice() {
		$had_sync_posts_data = get_option( 'wp_predictive_search_had_sync_posts_data', 0 );
		$is_upgraded_new_sync_data = get_option( 'wpps_upgraded_to_new_sync_data', 0 );
		$is_upgrade_from_free_version = get_option( 'wp_predictive_search_lite_version', false );

		if ( 0 != $had_sync_posts_data && 0 != $is_upgraded_new_sync_data ) return;

		if ( 0 == $is_upgraded_new_sync_data ) {
			$heading_text = __( 'Thanks for upgrading to latest version of WordPress Predictive Search' , 'wp-predictive-search' );
			$warning_text = __( 'The setup is almost done. Just one more step and you are ready to go. Please run database Sync to populate your Search engine database.' , 'wp-predictive-search' );
		} elseif ( false === $is_upgrade_from_free_version ) {
			$heading_text = __( 'Thanks for installing WordPress Predictive Search' , 'wp-predictive-search' );
			$warning_text = __( 'The setup is almost done. Just one more step and you are ready to go. Please run database Sync to populate your Search engine database.' , 'wp-predictive-search' );
		} else {
			$heading_text = __( 'Thanks for upgrading to WordPress Predictive Search Premium' , 'wp-predictive-search' );
			$warning_text = __( 'Now you need to run a full database sync to complete the upgrade.' , 'wp-predictive-search' );
		}

		$sync_data_url = admin_url( 'admin.php?page=wp-predictive-search&tab=performance-settings&box_open=predictive_search_synch_data#predictive_search_manual_sync_heading', 'relative' );
	?>
		<div class="message error wpps_sync_data_warning">
    		<p>
    			<strong><?php echo esc_html( $heading_text ); ?></strong>
    			- <?php echo esc_html( $warning_text ); ?>
    		</p>
    		<p>
    			<a class="button button-primary" href="<?php echo esc_url( $sync_data_url ); ?>" target="_parent"><?php echo __( 'Sync Now' , 'wp-predictive-search' ); ?></a>
    		</p>
    	</div>
	<?php
	}
 
	public function get_sync_posts_statistic( $post_type = 'post' ) {
		$status = 'completed';

		global $wpps_posts_data;
		$current_items = $wpps_posts_data->get_total_items_synched( $post_type );

		$all_items      = wp_count_posts( $post_type );
		$total_items    = isset( $all_items->publish ) ? $all_items->publish : 0;

		if ( $total_items > $current_items ) {
			$status = 'continue';
		}

		return array( 'status' => $status, 'current_items' => $current_items, 'total_items' => $total_items );
	}

	public function get_sync_taxonomy_statistic( $taxonomies = array( 'category', 'post_tag' ) ) {
		$status = 'completed';

		global $wpps_taxonomy_data;
		$current_items = $wpps_taxonomy_data->get_total_items_synched( $taxonomies );

		$total_items = $wpps_taxonomy_data->get_total_items_need_sync( $taxonomies );
		$total_items = ! empty( $total_items ) ? $total_items : 0;

		if ( $total_items > $current_items ) {
			$status = 'continue';
		}

		return array( 'status' => $status, 'current_items' => $current_items, 'total_items' => $total_items );
	}

	public function get_sync_relationships_statistic() {
		$status = 'completed';

		global $wp_predictive_search;
		$taxonomies = $wp_predictive_search->taxonomies_slug_support();

		global $wpps_term_relationships_data;
		$current_items = $wpps_term_relationships_data->get_total_items_synched();
		$total_items   = $wpps_term_relationships_data->get_total_items_need_sync( $taxonomies );

		if ( $total_items > $current_items ) {
			$status = 'continue';
		}

		return array( 'status' => $status, 'current_items' => $current_items, 'total_items' => $total_items );
	}

	public function wp_predictive_search_start_sync( $error_id = '', $sync_type = 'manual' ) {
		global $wpps_errors_log;
		$wpps_errors_log->delete_error( $error_id );

		if ( 'auto' !== $sync_type ) {
			// Stop child schedule of Auto Sync if have run Manual Sync for data is not conflicted
			global $wpps_schedule;
			$wpps_schedule->stop_child_schedule_events_auto_sync();
		}

		$status = 'completed';

		return array( 'status' => $status, 'current_items' => 1, 'total_items' => 1 );
	}

	public function wp_predictive_search_sync_posts( $post_type = 'post', $error_id = '', $sync_type = 'manual' ) {

		// Log Errors 
		global $wpps_errors_log;

		
		$error_type = sprintf( __( '%s Failed', 'wp-predictive-search' ), wpps_convert_key_to_label( $post_type ) );

		$wpps_errors_log->log_errors( $error_id, $error_type );

		$end_time = time() + 16;

		$this->migrate_posts( $post_type, $end_time, $sync_type );

		return $this->get_sync_posts_statistic( $post_type );
	}

	public function wp_predictive_search_sync_taxonomy( $error_id = '', $taxonomies = array( 'category', 'post_tag' ) , $sync_type = 'manual' ) {

		// Log Errors 
		global $wpps_errors_log;

		$error_type = sprintf( __( '%s Failed', 'wp-predictive-search' ), wpps_convert_key_to_label( $taxonomies[0] ) );

		$wpps_errors_log->log_errors( $error_id, $error_type );

		$end_time = time() + 16;

		$this->migrate_taxonomy( $taxonomies, $end_time, $sync_type );

		return $this->get_sync_taxonomy_statistic( $taxonomies );
	}

	public function wp_predictive_search_sync_relationships( $error_id = '', $sync_type = 'manual' ) {

		// Log Errors 
		global $wpps_errors_log;

		$error_type = __( 'Relationships Failed', 'wp-predictive-search' );

		$wpps_errors_log->log_errors( $error_id, $error_type );

		$end_time = time() + 16;

		$this->migrate_term_relationships( $end_time, $sync_type );

		return $this->get_sync_relationships_statistic();
	}

	public function wp_predictive_search_start_sync_ajax() {
		$result = $this->wp_predictive_search_start_sync( $this->error_id );

		echo json_encode( $result );

		die();
	}

	public function wp_predictive_search_sync_taxonomy_ajax() {
		if ( isset( $_POST['taxonomy'] ) ) {
			$taxonomy = is_array( $_POST['taxonomy'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['taxonomy'] ) ) : array( sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) );
		} else {
			$taxonomy = array( 'category' );
		}

		$result = $this->wp_predictive_search_sync_taxonomy( $this->error_id, $taxonomy );

		echo json_encode( $result );

		die();
	}

	public function wp_predictive_search_sync_relationships_ajax() {
		$result = $this->wp_predictive_search_sync_relationships( $this->error_id );

		echo json_encode( $result );

		die();
	}

	public function wp_predictive_search_sync_posts_ajax() {
		if ( isset( $_POST['posttype'] ) ) {
			$posttype = sanitize_key( wp_unslash( $_POST['posttype'] ) );
		} else {
			$posttype = 'post';
		}

		$result = $this->wp_predictive_search_sync_posts( $posttype, $this->error_id );

		echo json_encode( $result );

		die();
	}

	public function wp_predictive_search_manual_sync_error_ajax() {
		global $wpps_errors_log;

		$manual_synced_error_log = trim( $wpps_errors_log->get_error( 'manual_sync' ) );

		$wpps_errors_log->error_modal( 'manual_sync', $manual_synced_error_log );
	}

	public function wp_predictive_search_sync_end_ajax() {
		update_option( 'wp_predictive_search_synced_posts_data', 1 );
		update_option( 'wp_predictive_search_manual_synced_completed_time', current_time( 'timestamp' ) );

		wp_send_json( array( 'status' => 'OK', 'date' => date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ) ) ) );

		die();
	}

	public function sync_process_post() {
		add_action( 'save_post', array( $this, 'synch_save_post' ), 12, 2 );
		add_action( 'delete_post', array( $this, 'synch_delete_post' ) );
	}

	public function empty_full_data() {
		global $wpps_posts_data;
		global $wpps_postmeta_data;
		global $wpps_taxonomy_data;
		global $wpps_term_relationships_data;

		// Empty all tables
		$wpps_posts_data->empty_table();
		$wpps_postmeta_data->empty_table();
		$wpps_taxonomy_data->empty_table();
		$wpps_term_relationships_data->empty_table();

		do_action( 'wpps_empty_full_data' );

		update_option( 'wp_predictive_search_synced_posts_data', 0 );
	}

	public function update_sync_status() {
		update_option( 'wp_predictive_search_had_sync_posts_data', 1 );
		delete_option( 'wp_predictive_search_lite_version' );
		update_option( 'wpps_upgraded_to_new_sync_data', 1 );
	}

	public function migrate_posts( $post_types = array(), $end_time = 0, $sync_type = 'manual' ) {
		global $wpdb;
		global $wpps_posts_data;
		global $wpps_postmeta_data;
		global $wp_predictive_search;

		$this->update_sync_status();

		if ( ! is_array( $post_types ) ) {
			$post_types = array( $post_types );
		}

		if ( empty( $post_types ) ) {
			$post_types = $wp_predictive_search->posttypes_slug_support();
		}

		$post_status = $wp_predictive_search->post_status_support();

		// Check if synch data is stopped at latest run then continue synch without empty all the tables
		$synced_data = get_option( 'wp_predictive_search_synced_posts_data', 0 );

		if ( 0 == $synced_data || 'auto' === $sync_type ) {
			// continue synch data from stopped post ID
			$stopped_ID = $wpps_posts_data->get_latest_post_id( $post_types );
			if ( empty( $stopped_ID ) || is_null( $stopped_ID ) ) {
				$stopped_ID = 0;
			}
		} else {
			$this->empty_full_data();
			$stopped_ID = 0;
		}

		// If it's newest ID then fetch missed old ID to sync
		if ( $wpps_posts_data->is_newest_id( $post_types ) ) {
			$all_posts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.ID, p.post_title, p.post_type FROM {$wpdb->posts} AS p WHERE p.post_status IN ('". implode( "','", $post_status ) ."') AND p.post_type IN ('". implode("','", $post_types ) ."') AND NOT EXISTS ( SELECT 1 FROM {$wpdb->ps_posts} AS pp WHERE p.ID = pp.post_id ) ORDER BY p.ID ASC LIMIT %d, %d" ,
					0,
					500
				)
			);

		// Or continue sync based latest ID have synced
		} else {
			$all_posts = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT p.ID, p.post_title, p.post_type FROM {$wpdb->posts} AS p WHERE p.ID > %d AND p.post_status IN ('". implode( "','", $post_status ) ."') AND p.post_type IN ('". implode("','", $post_types ) ."') ORDER BY p.ID ASC LIMIT 0, 500" ,
					$stopped_ID
				)
			);
		}

		if ( $all_posts && is_array( $all_posts ) && count( $all_posts ) > 0 ) {

			$wpps_search_focus_enable = get_option( 'wpps_search_focus_enable', 'no' );
			$wpps_search_focus_plugin = get_option( 'wpps_search_focus_plugin', 'none' );

			foreach ( $all_posts as $item ) {

				// Stop command after timeout is set
				if ( $end_time > 0 && $end_time <= time() ) {
					break;
				}

				$post_id       = $item->ID;

				$item_existed = $wpps_posts_data->is_item_existed( $post_id );
				if ( '0' == $item_existed ) {
					$post_title = apply_filters( 'wpps_migrate_post_title', $item->post_title, $post_id, $item->post_type );
					$wpps_posts_data->insert_item( $post_id, $post_title, $item->post_type );
				}

				if ( 'yes' == $wpps_search_focus_enable && 'none' != $wpps_search_focus_plugin ) {

					if ( 'yoast_seo_plugin' == $wpps_search_focus_plugin ) {
						$yoast_keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
						if ( ! empty( $yoast_keyword ) && '' != trim( $yoast_keyword ) ) {
							$wpps_postmeta_data->add_item_meta( $post_id, '_yoast_wpseo_focuskw', $yoast_keyword );
						}
					}

					if ( 'all_in_one_seo_plugin' == $wpps_search_focus_plugin ) {
						$wpseo_keyword = get_post_meta( $post_id, '_aioseop_keywords', true );
						if ( ! empty( $wpseo_keyword ) && '' != trim( $wpseo_keyword ) ) {
							$wpps_postmeta_data->add_item_meta( $post_id, '_aioseop_keywords', $wpseo_keyword );
						}
					}
				}
			}
		}
	}

	public function migrate_taxonomy( $taxonomies = array(), $end_time = 0, $sync_type = 'manual' ) {
		global $wpdb;
		global $wpps_taxonomy_data;

		$this->update_sync_status();

		// Check if synch data is stopped at latest run then continue synch without empty all the tables
		$synced_data = get_option( 'wp_predictive_search_synced_posts_data', 0 );

		if ( 0 == $synced_data || 'auto' === $sync_type ) {
			// continue synch data from stopped post ID
			$stopped_ID = $wpps_taxonomy_data->get_latest_post_id( $taxonomies );
			if ( empty( $stopped_ID ) || is_null( $stopped_ID ) ) {
				$stopped_ID = 0;
			}
		} else {
			$wpps_taxonomy_data->empty_table();
			$stopped_ID = 0;
		}

		$where = '';

		$taxonomies_esc = wpps_esc_sql_array_s( $taxonomies );
		if ( ! empty( $taxonomies_esc ) ) {
			$where = " AND tt.taxonomy IN ( ".implode( ',', $taxonomies_esc )." )";
		}

		// If it's newest ID then fetch missed old ID to sync
		if ( $wpps_taxonomy_data->is_newest_id( $taxonomies ) ) {
			$all_items = $wpdb->get_results(
					"SELECT t.term_id, t.name, tt.term_taxonomy_id, tt.taxonomy FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON (t.term_id = tt.term_id) WHERE 1=1 {$where} AND NOT EXISTS ( SELECT 1 FROM {$wpdb->ps_taxonomy} AS pc WHERE t.term_id = pc.term_id ) ORDER BY t.term_id ASC LIMIT 0, 100"
			);

		// Or continue sync based latest ID have synced
		} else {
			$all_items = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT t.term_id, t.name, tt.term_taxonomy_id, tt.taxonomy FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON (t.term_id = tt.term_id) WHERE t.term_id > %d {$where} ORDER BY t.term_id ASC LIMIT 0, 100",
					$stopped_ID
				)
			);
		}

		if ( $all_items && is_array( $all_items ) && count( $all_items ) > 0 ) {
			foreach ( $all_items as $item ) {

				// Stop command after timeout is set
				if ( $end_time > 0 && $end_time <= time() ) {
					break;
				}

				$item_existed = $wpps_taxonomy_data->is_item_existed( $item->term_id );
				if ( '0' == $item_existed ) {
					$wpps_taxonomy_data->insert_item( $item->term_id, $item->term_taxonomy_id, $item->name, $item->taxonomy );
				}
			}
		}
	}

	public function migrate_term_relationships( $end_time = 0, $sync_type = 'manual' ) {
		global $wpdb;
		global $wp_predictive_search;
		global $wpps_term_relationships_data;

		$taxonomies = $wp_predictive_search->taxonomies_slug_support();
		if ( ! empty( $taxonomies ) ) {
			$taxonomies = wpps_esc_sql_array_s( $taxonomies );
		} else {
			$taxonomies = array( 'category', 'post_tag' );
		}
		

		$this->update_sync_status();

		// Check if synch data is stopped at latest run then continue synch without empty all the tables
		$synced_data = get_option( 'wp_predictive_search_synced_posts_data', 0 );

		if ( 0 == $synced_data || 'auto' === $sync_type ) {
			// continue synch data from stopped post ID
			$latest_data = $wpps_term_relationships_data->get_latest_post_id();
			if ( ! empty( $latest_data ) && ! is_null( $latest_data ) ) {
				$stopped_ID      = $latest_data->object_id;
				$stopped_term_ID = $latest_data->term_id;
			} else {
				$stopped_ID      = 0;
				$stopped_term_ID = 0;
			}
		} else {
			// Empty table
			$wpps_term_relationships_data->empty_table();
			$stopped_ID      = 0;
			$stopped_term_ID = 0;
		}

		// If it's newest ID then fetch missed old ID to sync
		if ( $wpps_term_relationships_data->is_newest_id( $taxonomies ) ) {
			$all_relationships = $wpdb->get_results(
				"SELECT tr.object_id, tt.term_id FROM {$wpdb->term_relationships} AS tr INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.taxonomy IN ( ".implode( ',', $taxonomies )." ) AND NOT EXISTS ( SELECT 1 FROM {$wpdb->ps_term_relationships} AS ptr WHERE tr.object_id = ptr.object_id AND tt.term_id = ptr.term_id ) ORDER BY tr.object_id ASC, tt.term_id ASC LIMIT 0, 5000"
			);

		// Or continue sync based latest ID have synced
		} else {
			$all_relationships = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT tr.object_id, tt.term_id FROM {$wpdb->term_relationships} AS tr INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE ( ( tr.object_id = %d AND tt.term_id > %d ) OR tr.object_id > %d ) AND tt.taxonomy IN ( ".implode( ',', $taxonomies )." ) ORDER BY tr.object_id ASC, tt.term_id ASC LIMIT 0, 5000",
					$stopped_ID,
					$stopped_term_ID,
					$stopped_ID
				)
			);
		}

		if ( $all_relationships && is_array( $all_relationships ) && count( $all_relationships ) > 0 ) {
			foreach ( $all_relationships as $item ) {

				// Stop command after timeout is set
				if ( $end_time > 0 && $end_time <= time() ) {
					break;
				}

				$wpps_term_relationships_data->insert_item( $item->object_id, $item->term_id );
			}
		}
	}

	public function synch_full_database() {
		$this->migrate_posts();
		$this->migrate_taxonomy();
		$this->migrate_term_relationships();
	}

	public function delete_post_data( $post_id ) {
		global $wpps_posts_data;
		global $wpps_postmeta_data;

		$wpps_posts_data->delete_item( $post_id );
		$wpps_postmeta_data->delete_item_metas( $post_id );
	}

	public function synch_save_post( $post_id, $post ) {
		global $wpdb;
		global $wpps_posts_data;
		global $wpps_postmeta_data;
		global $wpps_term_relationships_data;

		$this->delete_post_data( $post_id );

		global $wp_predictive_search;
		$post_types  = $wp_predictive_search->posttypes_slug_support();
		$post_status = $wp_predictive_search->post_status_support();

		if ( in_array( $post->post_status, $post_status ) && in_array( $post->post_type, $post_types ) ) {
			$yoast_keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
			// For Yoast SEO need to check if $_POST['yoast_wpseo_focuskw_text_input'] is existed then use it instead of use post meta
			if ( isset( $_POST['yoast_wpseo_focuskw_text_input'] ) ) {
				$yoast_keyword = trim( sanitize_text_field( wp_unslash( $_POST['yoast_wpseo_focuskw_text_input'] ) ) );
			}
			$wpseo_keyword = get_post_meta( $post_id, '_aioseop_keywords', true );

			$wpps_posts_data->update_item( $post_id, $post->post_title, $post->post_type );

			if ( ! empty( $yoast_keyword ) && '' != trim( $yoast_keyword ) ) {
				$wpps_postmeta_data->update_item_meta( $post_id, '_yoast_wpseo_focuskw', $yoast_keyword );
			}

			if ( ! empty( $wpseo_keyword ) && '' != trim( $wpseo_keyword ) ) {
				$wpps_postmeta_data->update_item_meta( $post_id, '_aioseop_keywords', $wpseo_keyword );
			}

			$wpps_term_relationships_data->delete_object( $post_id );

			if ( 'post' == $post->post_type ) {
				$all_relationships = $wpdb->get_results( "SELECT tt.term_id FROM {$wpdb->term_relationships} AS tr INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.taxonomy IN ('category', 'post_tag') AND tr.object_id = {$post_id} ORDER BY tr.object_id ASC" );
				if ( is_array( $all_relationships)  && count( $all_relationships ) > 0 ) {
					foreach ( $all_relationships as $item ) {
						$wpps_term_relationships_data->insert_item( $post_id, $item->term_id );
					}
				}
			}

			if ( 'page' == $post->post_type ) {
				global $wpps_search_page_id;

				// flush rewrite rules if page is editing is WP Search Result page
				if ( $post_id == $wpps_search_page_id ) {
					flush_rewrite_rules();
				}
			}

		}
	}

	public function synch_delete_post( $post_id ) {
		global $wpps_keyword_data;
		global $wpps_exclude_data;
		global $wpps_term_relationships_data;

		$this->delete_post_data( $post_id );

		$post_type = get_post_type( $post_id );

		$wpps_keyword_data->delete_item( $post_id );
		$wpps_exclude_data->delete_item( $post_id, $post_type );

		$wpps_term_relationships_data->delete_object( $post_id );
	}

	public function synch_save_taxonomy( $term_id, $tt_id ) {
		global $wpdb;
		global $wpps_taxonomy_data;

		$taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND term_taxonomy_id = %d ", $term_id, $tt_id ) );

		$term = get_term( $term_id, $taxonomy );
		$wpps_taxonomy_data->update_item( $term_id, $tt_id, $term->name, $taxonomy );
	}

	public function synch_delete_taxonomy( $term_id, $tt_id, $deleted_term ) {
		global $wpdb;
		global $wpps_taxonomy_data;
		global $wpps_exclude_data;

		$taxonomy = $wpdb->get_var( $wpdb->prepare( "SELECT taxonomy FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND term_taxonomy_id = %d ", $term_id, $tt_id ) );

		$wpps_taxonomy_data->delete_item( $term_id );
		$wpps_exclude_data->delete_item( $term_id, $taxonomy );
	}

	public function synch_delete_term_relationships( $term_id, $tt_id, $taxonomy, $deleted_term ) {
		global $wpps_term_relationships_data;
		$wpps_term_relationships_data->delete_term( $term_id );
	}

	public function synch_mysql_inserted_post( $post_id = 0 ) {
		if ( $post_id < 1 ) return;

		global $wpdb;
		global $wp_predictive_search;
		$post_types  = $wp_predictive_search->posttypes_slug_support();
		$post_status = $wp_predictive_search->post_status_support();

		$item = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, post_title, post_type, post_parent FROM {$wpdb->posts} WHERE ID = %d AND post_status IN ('". implode( "','", $post_status ) ."') AND post_type IN ('". implode("','", $post_types ) ."')" ,
				$post_id
			)
		);

		if ( $item ) {
			global $wpps_posts_data;
			global $wpps_postmeta_data;

			$yoast_keyword = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
			$wpseo_keyword = get_post_meta( $post_id, '_aioseop_keywords', true );

			$item_existed = $wpps_posts_data->is_item_existed( $post_id );
			if ( '0' == $item_existed ) {
				$wpps_posts_data->insert_item( $post_id, $item->post_title, $item->post_type );
			}

			if ( ! empty( $yoast_keyword ) && '' != trim( $yoast_keyword ) ) {
				$wpps_postmeta_data->add_item_meta( $post_id, '_yoast_wpseo_focuskw', $yoast_keyword );
			}

			if ( ! empty( $wpseo_keyword ) && '' != trim( $wpseo_keyword ) ) {
				$wpps_postmeta_data->add_item_meta( $post_id, '_aioseop_keywords', $wpseo_keyword );
			}
		}
	}
}
