<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\FrameWork\Settings {

use A3Rev\WPPredictiveSearch\FrameWork;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------
WC Predictive Search Global Settings

TABLE OF CONTENTS

- var parent_tab
- var subtab_data
- var option_name
- var form_key
- var position
- var form_fields
- var form_messages

- __construct()
- subtab_init()
- set_default_settings()
- get_settings()
- subtab_data()
- add_subtab()
- settings_form()
- init_form_fields()

-----------------------------------------------------------------------------------*/

class Global_Panel extends FrameWork\Admin_UI
{

	/**
	 * @var string
	 */
	private $parent_tab = 'global-settings';

	/**
	 * @var array
	 */
	private $subtab_data;

	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = '';

	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_predictive_search_global_settings';

	/**
	 * @var string
	 * You can change the order show of this sub tab in list sub tabs
	 */
	private $position = 1;

	/**
	 * @var array
	 */
	public $form_fields = array();

	/**
	 * @var array
	 */
	public $form_messages = array();

	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'init_form_fields' ), 1 );
		$this->subtab_init();

		$this->form_messages = array(
				'success_message'	=> __( 'Global Settings successfully saved.', 'wp-predictive-search' ),
				'error_message'		=> __( 'Error: Global Settings can not save.', 'wp-predictive-search' ),
				'reset_message'		=> __( 'Global Settings successfully reseted.', 'wp-predictive-search' ),
			);

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_modal_script' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );
		//add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );

		add_action( $this->plugin_name . '_settings_' . 'predictive_search_shortcode_box' . '_start', array( $this, 'predictive_search_shortcode_box' ) );
	}

	/*-----------------------------------------------------------------------------------*/
	/* subtab_init() */
	/* Sub Tab Init */
	/*-----------------------------------------------------------------------------------*/
	public function subtab_init() {

		add_filter( $this->plugin_name . '-' . $this->parent_tab . '_settings_subtabs_array', array( $this, 'add_subtab' ), $this->position );

	}

	/*-----------------------------------------------------------------------------------*/
	/* set_default_settings()
	/* Set default settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function set_default_settings() {
		$GLOBALS[$this->plugin_prefix.'admin_interface']->reset_settings( $this->form_fields, $this->option_name, false );
	}

	/*-----------------------------------------------------------------------------------*/
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();
		$taxonomies_support = $wp_predictive_search->taxonomies_support();

		if ( ( isset( $_POST['bt_save_settings'] ) || isset( $_POST['bt_reset_settings'] ) ) && get_option( $this->plugin_name . '_clean_on_deletion' ) == 'no' )  {
			$uninstallable_plugins = (array) get_option('uninstall_plugins');
			unset($uninstallable_plugins[$this->plugin_path]);
			update_option('uninstall_plugins', $uninstallable_plugins);
		}

		if ( isset( $_POST['bt_save_settings'] ) ) {
			flush_rewrite_rules();
		} elseif ( 1 == get_option( 'wp_predictive_search_just_confirm', 0 ) ) {
			delete_option( 'wp_predictive_search_just_confirm' );
			flush_rewrite_rules();
		}

		if ( ( isset( $_POST['bt_save_settings'] ) || isset( $_POST['bt_reset_settings'] ) ) )  {
			global $wpps_exclude_data;
			$wpps_exclude_data->empty_table();

			if ( ! empty( $posttypes_support ) ) {
				foreach ( $posttypes_support as $posttype ) {
					delete_option( 'wpps_search_exclude_'.$posttype['name'] );
				}
			}

			if ( ! empty( $taxonomies_support ) ) {
				foreach ( $taxonomies_support as $taxonomy ) {
					delete_option( 'wpps_search_exclude_'.$taxonomy['name'] );
				}
			}
		}
		if ( isset( $_POST['bt_save_settings'] ) )  {
			global $wpps_exclude_data;

			if ( ! empty( $posttypes_support ) ) {
				foreach ( $posttypes_support as $posttype ) {
					if ( isset( $_POST['wpps_search_exclude_'.$posttype['name']] ) && count( $_POST['wpps_search_exclude_'.$posttype['name']] ) > 0 ) {
						foreach ( $_POST['wpps_search_exclude_'.$posttype['name']] as $item_id ) {
							$wpps_exclude_data->insert_item( absint( $item_id ), $posttype['name'] );
						}
					}
				}
			}

			if ( ! empty( $taxonomies_support ) ) {
				foreach ( $taxonomies_support as $taxonomy ) {
					if ( isset( $_POST['wpps_search_exclude_'.$taxonomy['name']] ) && count( $_POST['wpps_search_exclude_'.$taxonomy['name']] ) > 0 ) {
						foreach ( $_POST['wpps_search_exclude_'.$taxonomy['name']] as $item_id ) {
							$wpps_exclude_data->insert_item( absint( $item_id ), $taxonomy['name'] );
						}
					}
				}
			}
		}
	}

	/*-----------------------------------------------------------------------------------*/
	/* get_settings()
	/* Get settings with function called from Admin Interface */
	/*-----------------------------------------------------------------------------------*/
	public function get_settings() {
		$GLOBALS[$this->plugin_prefix.'admin_interface']->get_settings( $this->form_fields, $this->option_name );
	}

	/**
	 * subtab_data()
	 * Get SubTab Data
	 * =============================================
	 * array (
	 *		'name'				=> 'my_subtab_name'				: (required) Enter your subtab name that you want to set for this subtab
	 *		'label'				=> 'My SubTab Name'				: (required) Enter the subtab label
	 * 		'callback_function'	=> 'my_callback_function'		: (required) The callback function is called to show content of this subtab
	 * )
	 *
	 */
	public function subtab_data() {

		$subtab_data = array(
			'name'				=> 'global-settings',
			'label'				=> __( 'Settings', 'wp-predictive-search' ),
			'callback_function'	=> 'wp_predictive_search_global_settings_form',
		);

		if ( $this->subtab_data ) return $this->subtab_data;
		return $this->subtab_data = $subtab_data;

	}

	/*-----------------------------------------------------------------------------------*/
	/* add_subtab() */
	/* Add Subtab to Admin Init
	/*-----------------------------------------------------------------------------------*/
	public function add_subtab( $subtabs_array ) {

		if ( ! is_array( $subtabs_array ) ) $subtabs_array = array();
		$subtabs_array[] = $this->subtab_data();

		return $subtabs_array;
	}

	/*-----------------------------------------------------------------------------------*/
	/* settings_form() */
	/* Call the form from Admin Interface
	/*-----------------------------------------------------------------------------------*/
	public function settings_form() {
		$output = '';
		$output .= $GLOBALS[$this->plugin_prefix.'admin_interface']->admin_forms( $this->form_fields, $this->form_key, $this->option_name, $this->form_messages );

		return $output;
	}

	/*-----------------------------------------------------------------------------------*/
	/* init_form_fields() */
	/* Init all fields of this form */
	/*-----------------------------------------------------------------------------------*/
	public function init_form_fields() {

		global $wpdb;
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();
		$taxonomies_support = $wp_predictive_search->taxonomies_support();

		$get_exclude_options_nonce = wp_create_nonce( 'wp_predictive_search_get_exclude_options' );
	
		$posttypes_exclude_settings = array();
		if ( ! empty( $posttypes_support ) ) {
			foreach ( $posttypes_support as $posttype ) {

				$all_items      = array();
				$items_excluded = array();

				if ( is_admin() && in_array (basename($_SERVER['PHP_SELF']), array('admin.php') ) && isset( $_GET['page'] ) && sanitize_key( wp_unslash( $_GET['page'] ) ) == 'wp-predictive-search' && ( ! isset( $_GET['tab'] ) || sanitize_key( wp_unslash( $_GET['tab'] ) ) == 'global-settings' ) ) {

					if ( isset( $_POST['bt_save_settings'] ) )  {
						if ( isset( $_POST['wpps_search_exclude_'.$posttype['name']] ) && is_array( $_POST['wpps_search_exclude_'.$posttype['name']] ) ) {
							$items_excluded = array_map( 'absint', $_POST['wpps_search_exclude_'.$posttype['name']] );
						}
					} else {
						$items_excluded = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->prefix}ps_exclude WHERE object_type = %s ", $posttype['name'] ) );
					}


					if ( ! empty( $items_excluded ) ) {
						$items_excluded = wpps_esc_sql_array_d( $items_excluded );
						$results = $wpdb->get_results("SELECT post_id, post_title FROM ".$wpdb->prefix."ps_posts WHERE post_id IN (" . implode(',', $items_excluded ) . ") ORDER BY post_title ASC");
						if ($results) {
							foreach($results as $item_data) {
								$all_items[$item_data->post_id] = $item_data->post_title;
							}
						}
					}
				}

				$posttypes_exclude_settings[] = array(  
					'name' 		=> sprintf( __( 'Exclude %s', 'wp-predictive-search' ), $posttype['label'] ),
					'id' 		=> 'wpps_search_exclude_'.$posttype['name'],
					'type' 		=> 'multiselect',
					'placeholder' => sprintf( __( 'Search %s', 'wp-predictive-search' ), $posttype['label'] ),
					'css'		=> 'width:600px; min-height:80px;',
					'options'	=> $all_items,
					'default'	=> $items_excluded,
					'options_url' => admin_url( 'admin-ajax.php?action=wpps_get_exclude_options&security='.$get_exclude_options_nonce.'&from=post&type='.$posttype['name'].'&keyword=', 'relative' ),
				);
			}
		}

		$taxonomies_exclude_settings = array();
		if ( ! empty( $taxonomies_support ) ) {
			foreach ( $taxonomies_support as $taxonomy ) {

				$all_items      = array();
				$items_excluded = array();

				if ( is_admin() && in_array (basename($_SERVER['PHP_SELF']), array('admin.php') ) && isset( $_GET['page'] ) && sanitize_key( wp_unslash( $_GET['page'] ) ) == 'wp-predictive-search' && ( ! isset( $_GET['tab'] ) || sanitize_key( wp_unslash( $_GET['tab'] ) ) == 'global-settings' ) ) {

					if ( isset( $_POST['bt_save_settings'] ) )  {
						if ( isset( $_POST['wpps_search_exclude_'.$taxonomy['name']] ) && is_array( $_POST['wpps_search_exclude_'.$taxonomy['name']] ) ) {
							$items_excluded = array_map( 'absint', $_POST['wpps_search_exclude_'.$taxonomy['name']] );
						}
					} else {
						$items_excluded = $wpdb->get_col( $wpdb->prepare( "SELECT object_id FROM {$wpdb->prefix}ps_exclude WHERE object_type = %s ", $taxonomy['name'] ) );
					}


					if ( ! empty( $items_excluded ) ) {
						$items_excluded = wpps_esc_sql_array_d( $items_excluded );
						$results = $wpdb->get_results("SELECT term_id, name FROM ".$wpdb->prefix."ps_taxonomy WHERE term_id IN (" . implode(',', $items_excluded ) . ") ORDER BY name ASC");
						if ($results) {
							foreach($results as $item_data) {
								$all_items[$item_data->term_id] = $item_data->name;
							}
						}
					}
				}

				$taxonomies_exclude_settings[] = array(  
					'name' 		=> sprintf( __( 'Exclude %s', 'wp-predictive-search' ), $taxonomy['label'] ),
					'id' 		=> 'wpps_search_exclude_'.$taxonomy['name'],
					'type' 		=> 'multiselect',
					'placeholder' => sprintf( __( 'Search %s', 'wp-predictive-search' ), $taxonomy['label'] ),
					'css'		=> 'width:600px; min-height:80px;',
					'options'	=> $all_items,
					'default'	=> $items_excluded,
					'options_url' => admin_url( 'admin-ajax.php?action=wpps_get_exclude_options&security='.$get_exclude_options_nonce.'&from=taxonomy&type='.$taxonomy['name'].'&keyword=', 'relative' ),
				);
			}
		}

  		// Define settings
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array_merge( array(

     		array(
            	'name' 		=> __( 'Plugin Framework Global Settings', 'wp-predictive-search' ),
            	'id'		=> 'plugin_framework_global_box',
                'type' 		=> 'heading',
                'first_open'=> true,
                'is_box'	=> true,
           	),

           	array(
           		'name'		=> __( 'Customize Admin Setting Box Display', 'wp-predictive-search' ),
           		'desc'		=> __( 'By default each admin panel will open with all Setting Boxes in the CLOSED position.', 'wp-predictive-search' ),
                'type' 		=> 'heading',
           	),
           	array(
				'type' 		=> 'onoff_toggle_box',
			),

           	array(
            	'name' 		=> __( 'House Keeping', 'wp-predictive-search' ),
                'type' 		=> 'heading',
            ),
			array(
				'name' 		=> __( 'Clean Up On Deletion', 'wp-predictive-search' ),
				'desc' 		=> __( 'On deletion (not deactivate) the plugin will completely remove all tables and data it created, leaving no trace it was ever here.', 'wp-predictive-search' ),
				'id' 		=> $this->plugin_name . '_clean_on_deletion',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'no',
				'separate_option'	=> true,
				'free_version'		=> true,
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),

     		array(
            	'name' 		=> __( 'Search Results No-Cache', 'wp-predictive-search' ),
            	'desc'		=> __( 'While testing different setting and the results in search box dropdown you need to switch ON Results No-Cache On. Search box dropdown results are cached in local store for frontend users for faster delivery on repeat searches. Be sure to turn this OFF when you are finished testing.', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_nocache_box',
				'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Results No-Cache', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_is_debug',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),

			array(
            	'name' 		=> __( 'Predictive Search Mode', 'wp-predictive-search' ),
            	'desc'		=> __( '<strong>IMPORTANT!</strong> Remember to turn ON the No-Cache option so that you see the difference between the 2 search modes when testing.', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_mode_box',
				'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Search Mode', 'wp-predictive-search' ),
				'desc'		=> '</span><span class="description predictive_search_mode_strict">' . __( "STRICT MODE will return exact match results. Example if user types 'out' the results will include all items that have 'out' at the start of a word such as 'outside', 'outsized' etc. This gives 100% relevant results every time but can lead to a lot of 'Nothing Found' results depending on how customers search your site.", 'wp-predictive-search' ) . '</span>'
				. '<span class="description predictive_search_mode_broad">' . __( "BROAD MODE just like Strict mode will return results that have the search term at the start but will also search within a word. Example if user types 'out' all items that have 'out' at the start will be returned plus all that have 'out' within a word such as 'fadeout', 'about' etc. Results are not as accurate as STRICT MODE but there will be less 'Nothing Found' results.", 'wp-predictive-search' ) . '</span><span>',
				'class'		=> 'predictive_search_mode',
				'id' 		=> 'predictive_search_mode',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'broad',
				'checked_value'		=> 'strict',
				'unchecked_value'	=> 'broad',
				'checked_label'		=> __( 'STRICT', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'BROAD', 'wp-predictive-search' ),
			),

			array(
            	'name' 		=> __( 'Default Results Description Source', 'wp-predictive-search' ),
            	'desc'		=> __( "Use the switch below to set where Predictive Search should source each found results description, if you have 'Show Results Description' activated in a PS Search Widget, Search Function, shortcode and on the All Results page. The 2 options are from the long description or short extract. If for any post the selected 'Default' source is empty PS will auto fallback to use the other source.", 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_description_source_box',
				'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Default Source', 'wp-predictive-search' ),
				'class'		=> 'predictive_search_description_source',
				'id' 		=> 'predictive_search_description_source',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'content',
				'checked_value'		=> 'content',
				'unchecked_value'	=> 'excerpt',
				'checked_label'		=> __( 'DESCRIPTION', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'EXTRACT', 'wp-predictive-search' ),
			),

			array(
            	'name' 		=> __( 'Predictive Search Focus Keywords', 'wp-predictive-search' ),
				'desc'		=> __( '<strong>Important!</strong> Do not turn this feature on unless you have or will be adding Focus Keywords to your posts. ON and Predictive search will query every post in searches checking for Focus Keywords. Increased and unnecessary queries ( if you have not set Focus Keywords ) can and on larger stores will degrade the search speed.', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_focus_keywords_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Predictive Search', 'wp-predictive-search' ),
				'class'		=> 'wpps_search_focus_enable',
				'id' 		=> 'wpps_search_focus_enable',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'no',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),

			array(
                'type' 		=> 'heading',
				'class'		=> 'wpps_search_focus_plugin_container',
           	),
			array(
				'name' 		=> __( "SEO Focus Keywords", 'wp-predictive-search' ),
				'desc' 		=> __("Supported plugins, WordPress SEO and ALL in ONE SEO Pack.", 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_focus_plugin',
				'type' 		=> 'select',
				'default'	=> 'none',
				'options'	=> array(
						'none'						=> __( 'Select SEO plugin', 'wp-predictive-search' ) ,
						'yoast_seo_plugin'			=> __( 'Yoast WordPress SEO', 'wp-predictive-search' ) ,
						'all_in_one_seo_plugin'		=> __( 'All in One SEO', 'wp-predictive-search' ) ,
					),
			),

			array(
            	'name' 		=> __( 'Special Characters', 'wp-predictive-search' ),
				'desc'		=> __( 'Select any special characters that are used on this site. Selecting a character will mean that results will be returned when user search input includes or excludes the special character. <strong>IMPORTANT!</strong> Do not turn this feature on unless needed. If ON - only select actual characters used in Post Titles, Category Names etc - each special character selected creates 1 extra query per search object, per post or page.', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_special_characters_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Special Character Function', 'wp-predictive-search' ),
				'class'		=> 'wpps_search_remove_special_character',
				'id' 		=> 'wpps_search_remove_special_character',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'no',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),

			array(
                'type' 		=> 'heading',
				'class'		=> 'wpps_search_remove_special_character_container',
           	),
           	array(
				'name' 		=> __( 'Character Syntax', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_replace_special_character',
				'type' 		=> 'onoff_radio',
				'default'	=> 'remove',
				'onoff_options' => array(
					array(
						'val' 				=> 'ignore',
						'text' 				=> __( 'IGNORE. ON to ignore or skip over special characters in the string.', 'wp-predictive-search' ),
						'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
						'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
					),
					array(
						'val' 				=> 'remove',
						'text' 				=> __( 'REMOVE. ON to remove or see special characters as a space.', 'wp-predictive-search' ).' <span class="description">('.__( 'recommended', 'wp-predictive-search' ).')</span>' ,
						'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
						'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
					),
					array(
						'val' 				=> 'both',
						'text' 				=> __( 'BOTH. On to use ignore and remove for special characters.', 'wp-predictive-search' ),
						'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
						'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
					),
				),
			),

			array(
				'name' 		=> __( "Select Characters", 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_special_characters',
				'type' 		=> 'multiselect',
				'css'		=> 'width:600px; min-height:80px;',
				'options'	=> \A3Rev\WPPredictiveSearch\Functions::special_characters_list(),
			),

			array(
            	'name' 		=> __( 'Exclude From Predictive Search', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_exclude_box',
                'is_box'	=> true,
           	),
        ),
		$posttypes_exclude_settings,
		$taxonomies_exclude_settings,
		array(
			array(
            	'name' 		=> __( 'Google Analytics Site Search Integration', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_google_analytics_box',
                'is_box'	=> true,
           	),
			array(  
				'name' 		=> __( 'Track Predictive Search Result with Google Analytics', 'wp-predictive-search' ),
				'class'		=> 'wpps_search_enable_google_analytic',
				'id' 		=> 'wpps_search_enable_google_analytic',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),
			
			array(
                'type' 		=> 'heading',
				'class'		=> 'wpps_search_enable_google_analytic_container',
           	),
			array(  
				'name' 		=> __( 'Google Analytics UID', 'wp-predictive-search' ),
				'desc' 		=> __('Example:', 'wp-predictive-search' ) . ' UA-3423237-10',
				'id' 		=> 'wpps_search_google_analytic_id',
				'type' 		=> 'text',
				'custom_attributes'	=> array( 'placeholder' => 'UA-XXXX-Y' ),
				'default'	=> ''
			),
			array(  
				'name' 		=> __( 'Query Parameter', 'wp-predictive-search' ),
				'desc' 		=> __( 'The parameter that is to be entered on the track Site Search config page on your Google Anayitics account. Default: [default_value]', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_google_analytic_query_parameter',
				'type' 		=> 'text',
				'default'	=> 'ps'
			),

			array(
            	'name' 		=> __( 'Shortcode', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_shortcode_box',
                'is_box'	=> true,
           	),
		)

        ));
	}

	public function predictive_search_shortcode_box() {
	?>
		<tr valign="top" class="">
			<td class="forminp" colspan="2">
            	<?php _e( 'You can add the Predictive Search Box by shortcode anywhere in a theme or page builder that accepts shortcodes.', 'wp-predictive-search' ); ?>
            	<br />
            	<div style="text-align: center; margin-top: 20px;"><a data-toggle="modal" href="#ps_generate_shortcode-modal" class="button button-primary"><?php _e( 'Create Shortcode', 'wp-predictive-search' ); ?></a></div>
			</td>
		</tr>
	<?php
	}

	public function include_modal_script() {
		global $wpps_cache;
		$disabled_cat_dropdown = false;
		$post_categories = wpps_get_categories();
		if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
			$disabled_cat_dropdown = true;
			$post_categories = false;
		}

		global $wp_predictive_search;

		$items_search_default = $wp_predictive_search->get_items_search();
	?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#wpps_search_show_catdropdown').on('click', function(){
					if ( jQuery(this).is(':checked') ) {
						jQuery('.wpps_search_set_default_cat_container').show();
					} else {
						jQuery('.wpps_search_set_default_cat_container').hide();
					}
				});
			});

			function wpps_search_widget_add_shortcode(){
				var number_items = '';
				<?php foreach ($items_search_default as $key => $data) {?>
				var wpps_search_<?php echo esc_js( $key ); ?>_items = '<?php echo esc_js( $key ); ?>_items="' + jQuery("#wpps_search_<?php echo esc_js( $key ); ?>_items").val() + '" ';
				number_items += wpps_search_<?php echo esc_js( $key ); ?>_items;
				<?php } ?>
				var wpps_search_widget_template = jQuery("#wpps_search_widget_template").val();
				var wpps_search_set_default_cat = jQuery('#wpps_search_set_default_cat').val();
				var wpps_search_show_catdropdown = 0;
				if ( jQuery('#wpps_search_show_catdropdown').is(":checked") ) {
					wpps_search_show_catdropdown = 1;
				} else {
					wpps_search_set_default_cat = '';
				}
				var wpps_search_show_image = 0;
				if ( jQuery('#wpps_search_show_image').is(":checked") ) {
					wpps_search_show_image = 1;
				}
				var wpps_search_show_desc = 0;
				if ( jQuery('#wpps_search_show_desc').is(":checked") ) {
					wpps_search_show_desc = 1;
				}
				var wpps_search_show_in_cat = 0;
				if ( jQuery('#wpps_search_show_in_cat').is(":checked") ) {
					wpps_search_show_in_cat = 1;
				}
				var wpps_search_text_lenght = jQuery("#wpps_search_text_lenght").val();
				var wpps_search_align = jQuery("#wpps_search_align").val();
				var wpps_search_width = jQuery("#wpps_search_width").val();
				var wpps_search_padding_top = jQuery("#wpps_search_padding_top").val();
				var wpps_search_padding_bottom = jQuery("#wpps_search_padding_bottom").val();
				var wpps_search_padding_left = jQuery("#wpps_search_padding_left").val();
				var wpps_search_padding_right = jQuery("#wpps_search_padding_right").val();
				var wpps_search_box_text = jQuery("#wpps_search_box_text").val();
				var wpps_search_style = '';
				var wrap = '';
				if (wpps_search_align == 'center') wpps_search_style += 'float:none;margin:auto;display:table;';
				else if (wpps_search_align == 'left-wrap') wpps_search_style += 'float:left;';
				else if (wpps_search_align == 'right-wrap') wpps_search_style += 'float:right;';
				else wpps_search_style += 'float:'+wpps_search_align+';';
				
				if(wpps_search_align == 'left-wrap' || wpps_search_align == 'right-wrap') wrap = 'wrap="true"';
				
				if (parseInt(wpps_search_width) > 0) wpps_search_style += 'width:'+parseInt(wpps_search_width)+'px;';
				if (parseInt(wpps_search_padding_top) >= 0) wpps_search_style += 'padding-top:'+parseInt(wpps_search_padding_top)+'px;';
				if (parseInt(wpps_search_padding_bottom) >= 0) wpps_search_style += 'padding-bottom:'+parseInt(wpps_search_padding_bottom)+'px;';
				if (parseInt(wpps_search_padding_left) >= 0) wpps_search_style += 'padding-left:'+parseInt(wpps_search_padding_left)+'px;';
				if (parseInt(wpps_search_padding_right) >= 0) wpps_search_style += 'padding-right:'+parseInt(wpps_search_padding_right)+'px;';
				var win = window.dialogArguments || opener || parent || top;
				var shortcode_output = '[wpps_search_widget ' + number_items + ' widget_template="'+wpps_search_widget_template+'" show_catdropdown="'+wpps_search_show_catdropdown+'" in_taxonomy="category" default_cat="'+wpps_search_set_default_cat+'" show_image="'+wpps_search_show_image+'" show_desc="'+wpps_search_show_desc+'" show_in_cat="'+wpps_search_show_in_cat+'" character_max="'+wpps_search_text_lenght+'" style="'+wpps_search_style+'" '+wrap+' search_box_text="'+wpps_search_box_text+'" ]';

				jQuery(".shortcode_container").html( shortcode_output );
			}
		</script>
		<style type="text/css">
			.field_content {
				padding:0 40px;
			}
			.field_content label{
				width:150px;
				float:left;
				text-align:left;
			}
			.field_content p {
				clear: both;
			}
			.shortcode_container {
				background: rgba(0, 0, 0, 0.07);
			    color: #fc2323;
			    padding: 30px 20px;
			    margin-top: 20px;
			}
			body.mobile.modal-open #wpwrap {
				position:  inherit;
			}
			label[for="woo_search_padding"] {
					width: 100%;
				}
			@media screen and ( max-width: 782px ) {
				#woo_search_box_text {
					width:100% !important;	
				}
			}
		</style>

    	<div class="modal fade wc-ps-modal" id="ps_generate_shortcode-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"><?php echo __( 'Generate Shortcode', 'wp-predictive-search' ); ?></h5>
					</div>
					<div class="modal-body m-3">
						<div class="field_content">
			                <?php foreach ($items_search_default as $key => $data) { ?>
			                <p><label for="wpps_search_<?php echo esc_attr( $key ); ?>_items"><?php echo esc_html( $data['name'] ); ?>:</label> <input style="width:100px;" size="10" id="wpps_search_<?php echo esc_attr( $key ); ?>_items" name="wpps_search_<?php echo esc_attr( $key ); ?>_items" type="text" value="<?php echo esc_attr( $data['number'] ); ?>" /> <span class="description"><?php echo sprintf( __( 'Number of %s results to show in dropdown', 'wp-predictive-search' ), esc_html( $data['name'] ) ); ?></span></p> 
			                <?php } ?>
			                <p><label for="wpps_search_widget_template"><?php _e('Select Template', 'wp-predictive-search' ); ?>:</label> <select style="width:100px" id="wpps_search_widget_template" name="wpps_search_widget_template"><option value="sidebar" selected="selected"><?php _e('Widget', 'wp-predictive-search' ); ?></option><option value="header"><?php _e('Header', 'wp-predictive-search' ); ?></option></select></p>
			                <p>
			                	<label for="wpps_search_show_catdropdown"><?php _e('Category Dropdown', 'wp-predictive-search' ); ?>:</label> <input <?php echo ( $disabled_cat_dropdown ) ? 'disabled="disabled"' : ''; ?> type="checkbox" checked="checked" id="wpps_search_show_catdropdown" name="wpps_search_show_catdropdown" value="1" /> <span class="description"><?php _e('Search in Category Feature', 'wp-predictive-search' ); ?></span>
			                	<?php if ( $disabled_cat_dropdown ) { ?>
			                	<br>
			            		<label>&nbsp;</label><span><?php echo sprintf( __( 'Activate and build <a href="%s" target="_blank">Category Cache</a> to activate this feature', 'wp-predictive-search' ), admin_url( 'admin.php?page=wp-predictive-search&tab=search-box-settings&box_open=predictive_search_category_cache_box#predictive_search_category_cache_box', 'relative' ) ); ?></span>
			            		<?php } ?>
			            	</p>

			            	
			            	<p class="wpps_search_set_default_cat_container" style="<?php if ( $disabled_cat_dropdown || false === $post_categories ) { ?>display: none;<?php } ?>">
			            		<label for="wpps_search_set_default_cat"><?php _e('Default Category', 'wp-predictive-search' ); ?>:</label> 
			            		<select style="width:100px" id="wpps_search_set_default_cat" name="wpps_search_set_default_cat">
			            			<option value="" selected="selected"><?php _e('All', 'wp-predictive-search' ); ?></option>
			            		<?php if ( $post_categories ) { ?>
									<?php foreach ( $post_categories as $category_data ) { ?>
									<option value="<?php echo esc_attr( $category_data['slug'] ); ?>"><?php echo esc_html( $category_data['name'] ); ?></option>
									<?php } ?>
			            		<?php } ?>
			            		</select> 
			            		<span class="description"><?php _e('Set category as default selected category for Category Dropdown', 'wp-predictive-search' ); ?></span>
			            	</p>

			                <p><label for="wpps_search_show_image"><?php _e('Image', 'wp-predictive-search' ); ?>:</label> <input type="checkbox" checked="checked" id="wpps_search_show_image" name="wpps_search_show_image" value="1" /> <span class="description"><?php _e('Show Results Images', 'wp-predictive-search' ); ?></span></p>
			            	<p><label for="wpps_search_show_desc"><?php _e('Description', 'wp-predictive-search' ); ?>:</label> <input type="checkbox" checked="checked" id="wpps_search_show_desc" name="wpps_search_show_desc" value="1" /> <span class="description"><?php _e('Show Results Description', 'wp-predictive-search' ); ?></span></p>
			            	<p><label for="wpps_search_text_lenght"><?php _e('Characters Count', 'wp-predictive-search' ); ?>:</label> <input style="width:100px;" size="10" id="wpps_search_text_lenght" name="wpps_search_text_lenght" type="text" value="100" /> <span class="description"><?php _e('Number of results description characters', 'wp-predictive-search' ); ?></span></p>
			            	<p><label for="wpps_search_show_in_cat"><?php _e('Post Categories', 'wp-predictive-search' ); ?>:</label> <input type="checkbox" checked="checked" id="wpps_search_show_in_cat" name="wpps_search_show_in_cat" value="1" /> <span class="description"><?php _e('Results - Show Categories', 'wp-predictive-search' ); ?></span></p>
			                <p><label for="wpps_search_align"><?php _e('Alignment', 'wp-predictive-search' ); ?>:</label> <select style="width:100px" id="wpps_search_align" name="wpps_search_align"><option value="none" selected="selected"><?php _e('None', 'wp-predictive-search' ); ?></option><option value="left-wrap"><?php _e('Left - wrap', 'wp-predictive-search' ); ?></option><option value="left"><?php _e('Left - no wrap', 'wp-predictive-search' ); ?></option><option value="center"><?php _e('Center', 'wp-predictive-search' ); ?></option><option value="right-wrap"><?php _e('Right - wrap', 'wp-predictive-search' ); ?></option><option value="right"><?php _e('Right - no wrap', 'wp-predictive-search' ); ?></option></select> <span class="description"><?php _e('Horizontal aliginment of search box', 'wp-predictive-search' ); ?></span></p>
			                <p><label for="wpps_search_width"><?php _e('Search box width', 'wp-predictive-search' ); ?>:</label> <input style="width:100px;" size="10" id="wpps_search_width" name="wpps_search_width" type="text" value="400" />px</p>
			                <p><label for="wpps_search_box_text"><?php _e('Search box text message', 'wp-predictive-search' ); ?>:</label> <input style="width:300px;" size="10" id="wpps_search_box_text" name="wpps_search_box_text" type="text" value="" /></p>
			                <p><label for="wpps_search_padding"><strong><?php _e('Padding', 'wp-predictive-search' ); ?></strong>:</label> 
							<label for="wpps_search_padding_top" style="width:auto; float:none"><?php _e('Above', 'wp-predictive-search' ); ?>:</label><input style="width:50px;" size="10" id="wpps_search_padding_top" name="wpps_search_padding_top" type="text" value="10" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			                <label for="wpps_search_padding_bottom" style="width:auto; float:none"><?php _e('Below', 'wp-predictive-search' ); ?>:</label> <input style="width:50px;" size="10" id="wpps_search_padding_bottom" name="wpps_search_padding_bottom" type="text" value="10" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			                <label for="wpps_search_padding_left" style="width:auto; float:none"><?php _e('Left', 'wp-predictive-search' ); ?>:</label> <input style="width:50px;" size="10" id="wpps_search_padding_left" name="wpps_search_padding_left" type="text" value="0" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			                <label for="wpps_search_padding_right" style="width:auto; float:none"><?php _e('Right', 'wp-predictive-search' ); ?>:</label> <input style="width:50px;" size="10" id="wpps_search_padding_right" name="wpps_search_padding_right" type="text" value="0" />px
			                </p>
						</div>
						<div class="shortcode_container"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" onclick="wpps_search_widget_add_shortcode();"><?php echo __( 'Get Shortcode', 'wp-predictive-search' ); ?></button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo __( 'Close', 'wp-predictive-search' ); ?></button>
					</div>
				</div>
			</div>
		</div>
	<?php
		if ( ! wp_script_is( 'bootstrap-modal', 'registered' ) 
			&& ! wp_script_is( 'bootstrap-modal', 'enqueued' ) ) {
			$GLOBALS[$this->plugin_prefix.'admin_interface']->register_modal_scripts();
		}

		wp_enqueue_style( 'bootstrap-modal' );

		// Don't include modal script if bootstrap is loaded by theme or plugins
		if ( wp_script_is( 'bootstrap', 'registered' ) 
			|| wp_script_is( 'bootstrap', 'enqueued' ) ) {
			
			wp_enqueue_script( 'bootstrap' );
			
			return;
		}

		wp_enqueue_script( 'bootstrap-modal' );
	}

	public function include_script() {
	?>
<script>
(function($) {

	$(document).ready(function() {

		if ( $("input.wpps_search_focus_enable:checked").val() != 'yes') {
			$('.wpps_search_focus_plugin_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		if ( $("input.wpps_search_remove_special_character:checked").val() != 'yes') {
			$('.wpps_search_remove_special_character_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		if ( $("input.predictive_search_mode:checked").val() != 'strict') {
			$('.predictive_search_mode_strict').hide();
		} else {
			$('.predictive_search_mode_broad').hide();
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_focus_enable', function( event, value, status ) {
			$('.wpps_search_focus_plugin_container').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".wpps_search_focus_plugin_container").slideDown();
			} else {
				$(".wpps_search_focus_plugin_container").slideUp();
			}
		});

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_remove_special_character', function( event, value, status ) {
			$('.wpps_search_remove_special_character_container').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".wpps_search_remove_special_character_container").slideDown();
			} else {
				$(".wpps_search_remove_special_character_container").slideUp();
			}
		});

		if ( $("input.wpps_search_enable_google_analytic:checked").val() == 'yes') {
			$(".wpps_search_enable_google_analytic_container").show();
		} else {
			$(".wpps_search_enable_google_analytic_container").hide();
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_enable_google_analytic', function( event, value, status ) {
			if ( status == 'true' ) {
				$(".wpps_search_enable_google_analytic_container").slideDown();
			} else {
				$(".wpps_search_enable_google_analytic_container").slideUp();
			}
		});

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.predictive_search_mode', function( event, value, status ) {
			if ( status == 'true' ) {
				$(".predictive_search_mode_strict").attr('style','display: inline;');
				$(".predictive_search_mode_broad").attr('style','display: none;');
			} else {
				$(".predictive_search_mode_strict").attr('style','display: none;');
				$(".predictive_search_mode_broad").attr('style','display: inline;');
			}
		});

	});

})(jQuery);
</script>
    <?php
	}
}

}

// global code
namespace {

/**
 * wp_predictive_search_global_settings_form()
 * Define the callback function to show subtab content
 */
function wp_predictive_search_global_settings_form() {
	global $wp_predictive_search_global_settings;
	$wp_predictive_search_global_settings->settings_form();
}

}
