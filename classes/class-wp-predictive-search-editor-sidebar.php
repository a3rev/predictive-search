<?php
/**
 * Predictive Search - Block Editor Sidebar
 *
 * Replaces the legacy add_meta_box with a modern Plugin Document Setting Panel
 * for the Block Editor. Uses register_post_meta + REST API so it's compatible
 * with Real-Time Collaboration and future Block Editor features.
 *
 * Data continues to live in custom tables (ps_keyword, ps_exclude) via
 * get_post_metadata / update_post_metadata filters.
 */

namespace A3Rev\WPPredictiveSearch;

class EditorSidebar {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_meta_fields' ) );
		add_filter( 'get_post_metadata', array( __CLASS__, 'filter_get_metadata' ), 10, 4 );
		add_filter( 'update_post_metadata', array( __CLASS__, 'filter_update_metadata' ), 10, 5 );
		add_filter( 'add_post_metadata', array( __CLASS__, 'filter_add_metadata' ), 10, 5 );
		add_filter( 'delete_post_metadata', array( __CLASS__, 'filter_delete_metadata' ), 10, 5 );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_sidebar_assets' ) );
	}

	/**
	 * Register post meta fields so the REST API can read/write them.
	 * The actual data lives in custom tables; metadata filters bridge the gap.
	 */
	public static function register_meta_fields() {
		global $wp_predictive_search;

		if ( ! isset( $wp_predictive_search ) ) {
			return;
		}

		$posttypes_slug = $wp_predictive_search->posttypes_slug_support();

		if ( empty( $posttypes_slug ) ) {
			return;
		}

		foreach ( $posttypes_slug as $post_type ) {
			register_post_meta( $post_type, '_predictive_search_focuskw', array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => '',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			) );

			register_post_meta( $post_type, '_ps_exclude_item', array(
				'show_in_rest'  => true,
				'single'        => true,
				'type'          => 'string',
				'default'       => '',
				'auth_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			) );
		}
	}

	/**
	 * Intercept get_post_meta reads and serve data from custom tables.
	 */
	public static function filter_get_metadata( $value, $object_id, $meta_key, $single ) {
		if ( '_predictive_search_focuskw' === $meta_key ) {
			global $wpps_keyword_data;
			if ( ! isset( $wpps_keyword_data ) ) {
				return $value;
			}

			$keyword = $wpps_keyword_data->get_item( $object_id );
			$keyword = ( null === $keyword ) ? '' : $keyword;

			return $single ? $keyword : array( $keyword );
		}

		if ( '_ps_exclude_item' === $meta_key ) {
			global $wpps_exclude_data;
			if ( ! isset( $wpps_exclude_data ) ) {
				return $value;
			}

			$post_type   = get_post_type( $object_id );
			$is_excluded = $wpps_exclude_data->get_item( $object_id, $post_type ) > 0;
			$result      = $is_excluded ? '1' : '';

			return $single ? $result : array( $result );
		}

		return $value;
	}

	/**
	 * Intercept update_post_meta and write to custom tables instead of wp_postmeta.
	 * Returning a non-null value short-circuits the default update_metadata() logic.
	 */
	public static function filter_update_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		if ( '_predictive_search_focuskw' === $meta_key ) {
			global $wpps_keyword_data;
			if ( ! isset( $wpps_keyword_data ) ) {
				return $check;
			}

			$meta_value = sanitize_text_field( wp_unslash( $meta_value ) );

			if ( '' !== $meta_value ) {
				$wpps_keyword_data->update_item( $object_id, $meta_value );
			} else {
				$wpps_keyword_data->delete_item( $object_id );
			}

			return true;
		}

		if ( '_ps_exclude_item' === $meta_key ) {
			global $wpps_exclude_data;
			if ( ! isset( $wpps_exclude_data ) ) {
				return $check;
			}

			$post_type = get_post_type( $object_id );

			if ( '1' === $meta_value || 1 === $meta_value || true === $meta_value ) {
				$wpps_exclude_data->insert_item( $object_id, $post_type );
			} else {
				$wpps_exclude_data->delete_item( $object_id, $post_type );
			}

			return true;
		}

		return $check;
	}

	/**
	 * Intercept add_post_meta the same way as update.
	 */
	public static function filter_add_metadata( $check, $object_id, $meta_key, $meta_value, $unique ) {
		if ( '_predictive_search_focuskw' === $meta_key || '_ps_exclude_item' === $meta_key ) {
			return self::filter_update_metadata( $check, $object_id, $meta_key, $meta_value, '' );
		}

		return $check;
	}

	/**
	 * Intercept delete_post_meta and remove from custom tables.
	 */
	public static function filter_delete_metadata( $check, $object_id, $meta_key, $meta_value, $delete_all ) {
		if ( '_predictive_search_focuskw' === $meta_key ) {
			global $wpps_keyword_data;
			if ( ! isset( $wpps_keyword_data ) ) {
				return $check;
			}

			$wpps_keyword_data->delete_item( $object_id );

			return true;
		}

		if ( '_ps_exclude_item' === $meta_key ) {
			global $wpps_exclude_data;
			if ( ! isset( $wpps_exclude_data ) ) {
				return $check;
			}

			$post_type = get_post_type( $object_id );
			$wpps_exclude_data->delete_item( $object_id, $post_type );

			return true;
		}

		return $check;
	}

	/**
	 * Enqueue the sidebar JS for the Block Editor.
	 */
	public static function enqueue_sidebar_assets() {
		global $wp_predictive_search;

		if ( ! isset( $wp_predictive_search ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$posttypes_slug = $wp_predictive_search->posttypes_slug_support();
		if ( empty( $posttypes_slug ) || ! in_array( $screen->post_type, $posttypes_slug, true ) ) {
			return;
		}

		wp_enqueue_script(
			'wpps-editor-sidebar',
			WPPS_URL . '/admin/assets/js/wpps-editor-sidebar.js',
			array(
				'wp-plugins',
				'wp-edit-post',
				'wp-editor',
				'wp-components',
				'wp-data',
				'wp-element',
				'wp-i18n',
			),
			WPPS_VERSION,
			true
		);
	}
}
