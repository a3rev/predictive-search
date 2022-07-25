<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\FrameWork\Pages {

use A3Rev\WPPredictiveSearch\FrameWork;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit; 

/*-----------------------------------------------------------------------------------
WC Predictive Search Page

TABLE OF CONTENTS

- var menu_slug
- var page_data

- __construct()
- page_init()
- page_data()
- add_admin_menu()
- tabs_include()
- admin_settings_page()

-----------------------------------------------------------------------------------*/

class Predictive_Search extends FrameWork\Admin_UI
{	
	/**
	 * @var string
	 */
	private $menu_slug = 'wp-predictive-search';
	
	/**
	 * @var array
	 */
	private $page_data;
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {
		$this->page_init();
		$this->tabs_include();
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* page_init() */
	/* Page Init */
	/*-----------------------------------------------------------------------------------*/
	public function page_init() {
		
		add_filter( $this->plugin_name . '_add_admin_menu', array( $this, 'add_admin_menu' ) );
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* page_data() */
	/* Get Page Data */
	/*-----------------------------------------------------------------------------------*/
	public function page_data() {
		
		$page_data = array(
			'type'				=> 'menu',
			'page_title'		=> __( 'Predictive Search', 'wp-predictive-search' ),
			'menu_title'		=> __( 'Predictive Search', 'wp-predictive-search' ),
			'capability'		=> 'manage_options',
			'menu_slug'			=> $this->menu_slug,
			'function'			=> 'wpps_admin_page_show',
			'icon_url'			=> '',
			'position'			=> '30.1245',
			'admin_url'			=> 'admin.php',
			'callback_function' => '',
			'script_function' 	=> '',
			'view_doc'			=> '',
		);
		
		if ( $this->page_data ) return $this->page_data;
		return $this->page_data = $page_data;
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* add_admin_menu() */
	/* Add This page to menu on left sidebar */
	/*-----------------------------------------------------------------------------------*/
	public function add_admin_menu( $admin_menu ) {
		
		if ( ! is_array( $admin_menu ) ) $admin_menu = array();
		$admin_menu[] = $this->page_data();
		
		return $admin_menu;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* tabs_include() */
	/* Include all tabs into this page
	/*-----------------------------------------------------------------------------------*/
	public function tabs_include() {

		global $wp_predictive_search_global_settings_tab;
		$wp_predictive_search_global_settings_tab = new FrameWork\Tabs\Global_Settings();

		global $wp_predictive_search_input_box_settings_tab;
		$wp_predictive_search_input_box_settings_tab = new FrameWork\Tabs\Search_Box();

		global $wp_predictive_search_performance_settings_tab;
		$wp_predictive_search_performance_settings_tab = new FrameWork\Tabs\Performance();

		global $wp_predictive_search_sidebar_template_settings_tab;
		$wp_predictive_search_sidebar_template_settings_tab = new FrameWork\Tabs\Sidebar_Template();

		global $wp_predictive_search_header_template_settings_tab;
		$wp_predictive_search_header_template_settings_tab = new FrameWork\Tabs\Header_Template();

		global $wpps_all_results_page_tab;
		$wpps_all_results_page_tab = new FrameWork\Tabs\All_Results_Pages();

		global $wpps_search_function_tab;
		$wpps_search_function_tab = new FrameWork\Tabs\Search_Function();
		
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* admin_settings_page() */
	/* Show Settings Page */
	/*-----------------------------------------------------------------------------------*/
	public function admin_settings_page() {		
		$GLOBALS[$this->plugin_prefix.'admin_init']->admin_settings_page( $this->page_data() );
	}
	
}

}

// global code
namespace {

/** 
 * wpps_admin_page_show()
 * Define the callback function to show page content
 */
function wpps_admin_page_show() {
	global $wpps_admin_page;
	$wpps_admin_page->admin_settings_page();
}

}
