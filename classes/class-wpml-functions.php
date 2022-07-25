<?php
/**
 * WC Predictive Search WPML Functions
 *
 * Table Of Contents
 *
 * plugins_loaded()
 * wpml_register_string()
 */

namespace A3Rev\WPPredictiveSearch;

class WPML_Functions
{	
	public $plugin_wpml_name = 'WordPress Predictive Search';
	
	public function __construct() {
		
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		
		$this->wpml_ict_t();
		
	}
	
	/** 
	 * Register WPML String when plugin loaded
	 */
	public function plugins_loaded() {
		$this->wpml_register_dynamic_string();
		$this->wpml_register_static_string();
	}
	
	/** 
	 * Get WPML String when plugin loaded
	 */
	public function wpml_ict_t() {
		
		$plugin_name = WPPS_KEY;

		add_filter( $plugin_name . '_' . 'wp_predictive_search_sidebar_template_settings' . '_get_settings', array( $this, 'ict_t_sidebar_template_settings' ) );
		add_filter( $plugin_name . '_' . 'wp_predictive_search_header_template_settings' . '_get_settings', array( $this, 'ict_t_header_template_settings' ) );
		
	}
	
	// Registry Dynamic String for WPML
	public function wpml_register_dynamic_string() {

		$wp_predictive_search_sidebar_template_settings = array_map( array( $GLOBALS[WPPS_PREFIX.'admin_interface'], 'admin_stripslashes' ), get_option( 'wp_predictive_search_sidebar_template_settings', array() ) );
		$wp_predictive_search_header_template_settings = array_map( array( $GLOBALS[WPPS_PREFIX.'admin_interface'], 'admin_stripslashes' ), get_option( 'wp_predictive_search_header_template_settings', array() ) );
		
		if ( function_exists('icl_register_string') ) {
			icl_register_string($this->plugin_wpml_name, 'More result Text - Sidebar', $wp_predictive_search_sidebar_template_settings['sidebar_popup_seemore_text'] );
			icl_register_string($this->plugin_wpml_name, 'More result Text - Header', $wp_predictive_search_header_template_settings['header_popup_seemore_text'] );
		}
	}
	
	// Registry Static String for WPML
	public function wpml_register_static_string() {
		if ( function_exists('icl_register_string') ) {
			
			// Default Form
			icl_register_string( $this->plugin_wpml_name, 'Item Name', __( 'Item Name', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Item Categories', __( 'Item Categories', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Item Tags', __( 'Item Tags', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Posts', __( 'Posts', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Pages', __( 'Pages', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'All', __( 'All', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Category', __( 'Category', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Tags', __( 'Tags', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Nothing found', __( 'Nothing found for "%s".', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Nothing found in Category', __( 'Nothing found for "%s" in "%s" Category. Try selecting All from the dropdown and search again.', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Last Found', __( 'Showing results for last found search term "%s".', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Viewing all', __( 'Viewing all', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Search Result Text', __( 'search results for your search query', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Sort Text', __( 'Sort Search Results by', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Loading Text', __( 'Loading More Results...', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'No More Result Text', __( 'No More Results to Show', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'Fetching Text', __( 'Fetching search results...', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'No Fetching Result Text', __( 'No Results to Show', 'wp-predictive-search' ) );
			icl_register_string( $this->plugin_wpml_name, 'No Result Text', __( 'Nothing Found! Please refine your search and try again.', 'wp-predictive-search' ) );
		}
	}

	public function ict_t_sidebar_template_settings( $current_settings = array() ) {
		if ( is_array( $current_settings ) && isset( $current_settings['sidebar_popup_seemore_text'] ) ) 
			$current_settings['sidebar_popup_seemore_text'] = ( function_exists('icl_t') ? icl_t( $this->plugin_wpml_name, 'More result Text - Sidebar', $current_settings['sidebar_popup_seemore_text'] ) : $current_settings['sidebar_popup_seemore_text'] );

		return $current_settings;
	}

	public function ict_t_header_template_settings( $current_settings = array() ) {
		if ( is_array( $current_settings ) && isset( $current_settings['header_popup_seemore_text'] ) ) 
			$current_settings['header_popup_seemore_text'] = ( function_exists('icl_t') ? icl_t( $this->plugin_wpml_name, 'More result Text - Header', $current_settings['header_popup_seemore_text'] ) : $current_settings['header_popup_seemore_text'] );

		return $current_settings;
	}

}
