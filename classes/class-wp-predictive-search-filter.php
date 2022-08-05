<?php
/**
 * WordPress Predictive Search Hook Filter
 *
 * Table Of Contents
 *
 * plugins_loaded()
 * a3_wp_admin()
 * yellow_message_dontshow()
 * yellow_message_dismiss()
 * plugin_extra_links()
 */

namespace A3Rev\WPPredictiveSearch;

class Hook_Filter
{

	public static function plugins_init() {
		global $wpps_search_page_id;
		global $wpps_search_page_content_type;
		global $predictive_search_mode;
		global $predictive_search_description_source;

		if ( ! defined( 'WPPS_TRAVIS' ) ) {
			$wpps_search_page_id = get_option( 'wpps_search_page_id' );
		}

		$predictive_search_mode               = get_option( 'predictive_search_mode', 'strict' );
		$predictive_search_description_source = get_option( 'predictive_search_description_source', 'content' );
		$wpps_search_page_content_type        = get_option( 'wpps_search_page_content_type' );
	}

	public static function wpml_search_page_id() {
		global $wpdb;
		global $wpps_search_page_id;

		// For WPML
		if ( class_exists('SitePress') ) {
			global $sitepress;
			$translation_page_data = null;
			$trid = $sitepress->get_element_trid( $wpps_search_page_id, 'post_page' );
			if ( $trid ) {
				$translation_page_data = $wpdb->get_row( $wpdb->prepare( "SELECT element_id FROM " . $wpdb->prefix . "icl_translations WHERE trid = %d AND element_type='post_page' AND language_code = %s LIMIT 1", $trid , $sitepress->get_current_language() ) );
				if ( $translation_page_data != null )
					$wpps_search_page_id = $translation_page_data->element_id;
			}
		}
	}

	public static function a3_wp_admin() {
		wp_enqueue_style( 'a3rev-wp-admin-style', WPPS_CSS_URL . '/a3_wp_admin.css' );
	}

	public static function admin_sidebar_menu_css() {
		wp_enqueue_style( 'a3rev-wpps-admin-sidebar-menu-style', WPPS_CSS_URL . '/admin_sidebar_menu.css' );
	}

	public static function yellow_message_dontshow() {
		check_ajax_referer( 'wpps_yellow_message_dontshow', 'security' );
		$option_name   = sanitize_text_field( wp_unslash( $_REQUEST['option_name'] ) );
		update_option( $option_name, 1 );
		die();
	}

	public static function yellow_message_dismiss() {
		check_ajax_referer( 'wpps_yellow_message_dismiss', 'security' );
		$session_name   = sanitize_key( wp_unslash( $_REQUEST['session_name'] ) );
		if ( !isset($_SESSION) ) { @session_start(); }
		$_SESSION[$session_name] = 1 ;
		die();
	}

	public static function plugin_extra_links($links, $plugin_name) {

		if ( $plugin_name != WPPS_NAME) {
			return $links;
		}
		
		$links[] = '<a href="' . esc_url( WPPS_DOCS_URI ).' " target="_blank">'.__('Documentation', 'wp-predictive-search').'</a>';
		$links[] = '<a href="'.$GLOBALS[WPPS_PREFIX.'admin_init']->support_url.'" target="_blank">'.__('Support', 'wp-predictive-search' ).'</a>';

		return $links;
	}

	public static function settings_plugin_links($actions) {
		$actions = array_merge( array( 'settings' => '<a href="admin.php?page=wp-predictive-search">' . __( 'Settings', 'wp-predictive-search' ) . '</a>' ), $actions );

		return $actions;
	}

	public static function plugin_extension_box( $boxes = array() ) {
		$support_box = '<a href="https://wordpress.org/support/plugin/predictive-search" target="_blank" alt="'.__('Go to Support Forum', 'wp-predictive-search' ).'"><img src="'.WPPS_IMAGES_URL.'/go-to-support-forum.png" /></a>';
		$boxes[] = array(
			'content' => $support_box,
			'css' => 'border: none; padding: 0; background: none;'
		);

		$first_box = '<a href="https://profiles.wordpress.org/a3rev/#content-plugins" target="_blank" alt="'.__('Free WordPress Plugins', 'wp-predictive-search' ).'"><img src="'.WPPS_IMAGES_URL.'/free-wordpress-plugins.png" /></a>';

		$boxes[] = array(
			'content' => $first_box,
			'css' => 'border: none; padding: 0; background: none;'
		);

        $third_box = '<div style="margin-bottom: 5px; font-size: 12px;"><strong>' . __('Is this plugin is just what you needed? If so', 'wp-predictive-search' ) . '</strong></div>';
        $third_box .= '<a href="https://wordpress.org/support/view/plugin-reviews/wp-predictive-search#postform" target="_blank" alt="'.__('Submit Review for Plugin on WordPress', 'wp-predictive-search' ).'"><img src="'.WPPS_IMAGES_URL.'/a-5-star-rating-would-be-appreciated.png" /></a>';

        $boxes[] = array(
            'content' => $third_box,
            'css' => 'border: none; padding: 0; background: none;'
        );

        $four_box = '<div style="margin-bottom: 5px;">' . __('Connect with us via','wp-predictive-search' ) . '</div>';
		$four_box .= '<a href="https://www.facebook.com/a3rev" target="_blank" alt="'.__('a3rev Facebook', 'wp-predictive-search' ).'" style="margin-right: 5px;"><img src="'.WPPS_IMAGES_URL.'/follow-facebook.png" /></a> ';
		$four_box .= '<a href="https://twitter.com/a3rev" target="_blank" alt="'.__('a3rev Twitter', 'wp-predictive-search' ).'"><img src="'.WPPS_IMAGES_URL.'/follow-twitter.png" /></a>';

		$boxes[] = array(
			'content' => $four_box,
			'css' => 'border-color: #3a5795;'
		);

		return $boxes;
	}
}
