<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\FrameWork\Settings {

use A3Rev\WPPredictiveSearch\FrameWork;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------
WC Predictive Search Performance Settings

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

class Performance extends FrameWork\Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'performance-settings';
	
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
	public $form_key = 'wp_predictive_search_performance_settings';
	
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
	
	public function custom_types() {
		$custom_type = array( 'min_characters_yellow_message', 'time_delay_yellow_message', 'cache_timeout_yellow_message' );
		
		return $custom_type;
	}
	
	/*-----------------------------------------------------------------------------------*/
	/* __construct() */
	/* Settings Constructor */
	/*-----------------------------------------------------------------------------------*/
	public function __construct() {

		// add custom type
		foreach ( $this->custom_types() as $custom_type ) {
			add_action( $this->plugin_name . '_admin_field_' . $custom_type, array( $this, $custom_type ) );
		}
		
		add_action( 'plugins_loaded', array( $this, 'init_form_fields' ), 1 );
		$this->subtab_init();
		
		$this->form_messages = array(
				'success_message'	=> __( 'Performance Settings successfully saved.', 'wp-predictive-search' ),
				'error_message'		=> __( 'Error: Performance Settings can not save.', 'wp-predictive-search' ),
				'reset_message'		=> __( 'Performance Settings successfully reseted.', 'wp-predictive-search' ),
			);

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'error_logs_container' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_before_settings_save', array( $this, 'before_save_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );
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
	/* before_save_settings()
	/*
	/*-----------------------------------------------------------------------------------*/
	public function before_save_settings() {
		$old_schedule_time = get_option( 'wpps_search_schedule_time_sync_data', '00:00' );
		$new_schedule_time = $old_schedule_time;
		if ( isset( $_POST['wpps_search_schedule_time_sync_data'] ) && '' != $_POST['wpps_search_schedule_time_sync_data'] ) {
			$new_schedule_time = date( 'H:i', strtotime( $_POST['wpps_search_schedule_time_sync_data'] ) );
		}

		$new_allow_auto_sync_data = 'yes';
		if ( ! isset( $_POST['wpps_search_allow_auto_sync_data'] ) || 'yes' != $_POST['wpps_search_allow_auto_sync_data'] ) {
			$new_allow_auto_sync_data = 'no';
		}

		if ( 'no' != $new_allow_auto_sync_data ) {

			/*
			* registered event
			* if Auto Sync Daily is set 'ON' and Schedule Time is changed
			*/
			if ( $old_schedule_time != $new_schedule_time || ! wp_next_scheduled( 'wp_predictive_search_sync_data_scheduled_jobs' ) ) {
				wp_clear_scheduled_hook( 'wp_predictive_search_sync_data_scheduled_jobs' );

				$next_day = date( 'Y-m-d', strtotime('+1 day') );
				$next_time = strtotime( $next_day . ' ' . $new_schedule_time .':00' );
				$next_time = get_option( 'gmt_offset' ) > 0 ? $next_time - ( 60 * 60 * get_option( 'gmt_offset' ) ) : $next_time +
( 60 * 60 * get_option( 'gmt_offset' ) );

				wp_schedule_event( $next_time, 'daily', 'wp_predictive_search_sync_data_scheduled_jobs' );
			}

		} else {

			/*
			* deregistered event
			* if Auto Sync Daily is set 'OFF'
			*/
			wp_clear_scheduled_hook( 'wp_predictive_search_sync_data_scheduled_jobs' );
		}
	}

	/*-----------------------------------------------------------------------------------*/
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {

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
			'name'				=> 'performance-settings',
			'label'				=> __( 'Performance', 'wp-predictive-search' ),
			'callback_function'	=> 'wp_predictive_search_performance_settings_form',
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

		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();
		$taxonomies_support = $wp_predictive_search->taxonomies_support();
		$taxonomies_slug_support = $wp_predictive_search->taxonomies_slug_support();

		$sync_button_text = __( 'Start Sync', 'wp-predictive-search' );
		$synced_full_data = false;
		if ( isset( $_GET['page'] ) && 'wp-predictive-search' == sanitize_key( $_GET['page'] ) && isset( $_GET['tab'] ) && $this->parent_tab == sanitize_key( $_GET['tab'] ) ) {
			if ( ! isset( $_SESSION ) ) {
				@session_start();
			}

			global $wpdb;

			$current_posttypes = 0;
			$total_posttypes   = 0;
			if ( ! empty( $posttypes_support ) ) {
				global $wpps_posts_data;
				foreach ( $posttypes_support as $posttype ) {
					${'current_'.$posttype['name']} = $wpps_posts_data->get_total_items_synched( $posttype['name'] );
					${'total_'.$posttype['name']}   = $wpps_posts_data->get_total_items_need_sync( $posttype['name'] );

					$current_posttypes += ${'current_'.$posttype['name']};
					$total_posttypes   += ${'total_'.$posttype['name']};
				}
			}

			global $wpps_term_relationships_data;
			$current_relationships = $wpps_term_relationships_data->get_total_items_synched();
			$total_relationships   = $wpps_term_relationships_data->get_total_items_need_sync( $taxonomies_slug_support );

			$current_taxonomies = 0;
			$total_taxonomies   = 0;
			if ( ! empty( $taxonomies_support ) ) {
				global $wpps_taxonomy_data;
				foreach ( $taxonomies_support as $taxonomy ) {
					${'current_'.$taxonomy['name']} = $wpps_taxonomy_data->get_total_items_synched( array( $taxonomy['name'] ) );
					${'total_'.$taxonomy['name']}   = $wpps_taxonomy_data->get_total_items_need_sync( array( $taxonomy['name'] )  );

					$current_taxonomies += ${'current_'.$taxonomy['name']};
					$total_taxonomies   += ${'total_'.$taxonomy['name']};
				}
			}

			$current_items = $current_relationships + $current_posttypes + $current_taxonomies;
			$total_items   = $total_relationships + $total_posttypes + $total_taxonomies;

			$current_items = apply_filters( 'wpps_current_items_performance_settings', $current_items );
			$total_items = apply_filters( 'wpps_total_items_performance_settings', $total_items );

			$had_sync_posts_data = get_option( 'wp_predictive_search_had_sync_posts_data', 0 );

			if ( 0 == $had_sync_posts_data ) {
				$synced_full_data = true;
				update_option( 'wp_predictive_search_synced_posts_data', 1 );
			} elseif ( $current_items > 0 && $current_items < $total_items ) {
				update_option( 'wp_predictive_search_synced_posts_data', 0 );
				$sync_button_text = __( 'Continue Sync', 'wp-predictive-search' );
			} elseif ( $current_items >= $total_items ) {
				$synced_full_data = true;
				update_option( 'wp_predictive_search_synced_posts_data', 1 );
				$sync_button_text = __( 'Re Sync', 'wp-predictive-search' );
			}
		}

		$auto_synced_completed_time = get_option( 'wp_predictive_search_auto_synced_completed_time', false );
		if ( false !== $auto_synced_completed_time ) {
			$auto_synced_completed_time = date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $auto_synced_completed_time );
		} else {
			$auto_synced_completed_time = '';
		}

		$manual_synced_completed_time = get_option( 'wp_predictive_search_manual_synced_completed_time', false );
		if ( false !== $manual_synced_completed_time ) {
			$manual_synced_completed_time = date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $manual_synced_completed_time );
		} else {
			$manual_synced_completed_time = '';
		}

		$have_confirm = false;

		$posttypes_settings = array();
		if ( ! empty( $posttypes_support ) ) {
			foreach ( $posttypes_support as $posttype ) { 
				$posttype_syncing_title = sprintf( __( 'Syncing %s ...', 'wp-predictive-search' ), $posttype['label'] );
				$posttype_synced_title  = sprintf( __( '%s Synced', 'wp-predictive-search' ), $posttype['label'] );

				$posttype_sync_title = $posttype_synced_title; 
				if ( 0 == get_option( 'wp_predictive_search_auto_synced_'.$posttype['name'].'_successed', 1 ) ) {
					$posttype_sync_title = $posttype_syncing_title;
					$have_confirm = true;
				}

				$posttypes_settings[] = array(
					'item_id'          => 'sync_' . $posttype['name'],
					'item_name'        => $posttype_sync_title,
					'current_items'    => ( ! empty( ${'current_'.$posttype['name']} ) ) ? (int) ${'current_'.$posttype['name']} : 0,
					'total_items'      => ( ! empty( ${'total_'.$posttype['name']} ) ) ? (int) ${'total_'.$posttype['name']} : 0,
					'progressing_text' => $posttype_syncing_title,
					'completed_text'   => $posttype_synced_title,
					'submit_data'      => array(
						'ajax_url'  => admin_url( 'admin-ajax.php', 'relative' ),
						'ajax_type' => 'POST',
						'data'      => array(
							'action'   => 'wp_predictive_search_sync_posttype',
							'posttype' => $posttype['name'],
						)
					),
					'show_statistic'       => true,
					'statistic_customizer' => array(
						'current_color' => isset( $posttype['color'] ) ? $posttype['color'] : '#7ad03a',
					),
				);
			}
		}

		$custom_types_settings = apply_filters( 'wpps_custom_types_performance_settings', array() );

		$taxonomies_settings = array();
		if ( ! empty( $taxonomies_support ) ) {
			foreach ( $taxonomies_support as $taxonomy ) { 
				$taxonomy_syncing_title = sprintf( __( 'Syncing %s ...', 'wp-predictive-search' ), $taxonomy['label'] );
				$taxonomy_synced_title  = sprintf( __( '%s Synced', 'wp-predictive-search' ), $taxonomy['label'] );

				$taxonomy_sync_title = $taxonomy_synced_title; 
				if ( 0 == get_option( 'wp_predictive_search_auto_synced_'.$taxonomy['name'].'_successed', 1 ) ) {
					$taxonomy_sync_title = $taxonomy_syncing_title;
					$have_confirm = true;
				}

				$taxonomies_settings[] = array(
					'item_id'          => 'sync_' . $taxonomy['name'],
					'item_name'        => $taxonomy_sync_title,
					'current_items'    => ( ! empty( ${'current_'.$taxonomy['name']} ) ) ? (int) ${'current_'.$taxonomy['name']} : 0,
					'total_items'      => ( ! empty( ${'total_'.$taxonomy['name']} ) ) ? (int) ${'total_'.$taxonomy['name']} : 0,
					'progressing_text' => $taxonomy_syncing_title,
					'completed_text'   => $taxonomy_synced_title,
					'submit_data'      => array(
						'ajax_url'  => admin_url( 'admin-ajax.php', 'relative' ),
						'ajax_type' => 'POST',
						'data'      => array(
							'action'   => 'wp_predictive_search_sync_taxonomy',
							'taxonomy' => array( $taxonomy['name'] ),
						)
					),
					'show_statistic'       => true,
					'statistic_customizer' => array(
						'current_color' => isset( $taxonomy['color'] ) ? $taxonomy['color'] : '#7ad03a',
					),
				);
			}
		}

		if ( 0 == get_option( 'wp_predictive_search_auto_synced_relationships_successed', 1 ) ) {
			$have_confirm = true;
		}

		// If have Error log on Sync
		global $wpps_errors_log;
		$auto_synced_error_log   = trim( $wpps_errors_log->get_error( 'auto_sync' ) );

  		// Define settings
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(

			array(
            	'name' 		=> __( 'Database Sync', 'wp-predictive-search' ),
            	'desc'		=> __( 'Predictive Search database is auto updated whenever a post is published or updated. Please run a Manual database sync if you upload post by csv or feel that Predictive Search results are showing old data.  Will sync the Predictive Search database with your current WordPress databases', 'wp-predictive-search' ),
            	'id'		=> 'predictive_search_synch_data',
                'type' 		=> 'heading',
				'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Auto Sync Daily', 'wp-predictive-search' ),
				'class'		=> 'wpps_search_allow_auto_sync_data',
				'id' 		=> 'wpps_search_allow_auto_sync_data',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
				'separate_option'   => true,
			),
			array(
                'type' 		=> 'heading',
				'class'		=> 'allow_auto_sync_data_container',
           	),
           	array(
				'name' 		=> __( 'Schedule Time', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_schedule_time_sync_data',
				'type' 		=> 'time_picker',
				'default'	=> '00:00',
				'desc'		=> ( '' != $auto_synced_completed_time ? '<span style="color:#46b450;font-style:normal;">' .__( 'Last Scheduled Full Database Sync completed', 'wp-predictive-search' ) . ' ' . $auto_synced_completed_time . '</span>' : '' ) . ( '' != $auto_synced_error_log ? '<span style="color:#f00;font-style:normal; display: block;">' .__( 'ERROR: Latest auto sync has failed to complete', 'wp-predictive-search' ) . ' - <a data-toggle="modal" href="#auto_sync-modal">'. __( 'View Error Log', 'wp-predictive-search' ) .'</a></span>' : '' ),
				'separate_option'   => true,
			),
			array(
                'type' 		=> 'heading',
                'name'		=> __( 'Email Notifications', 'wp-predictive-search' ),
				'class'		=> 'allow_auto_sync_data_container',
           	),
			array(
				'name' 		=> __( 'Error notification recipient(s)', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_schedule_error_recipients',
				'type' 		=> 'text',
				'desc'		=> sprintf( __( 'Blank for default: %s', 'wp-predictive-search' ), get_option( 'admin_email' ) ),
				'separate_option'   => true,
			),
			array(
				'name' 		=> __( 'Sync Complete recipients(s)', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_schedule_success_recipients',
				'type' 		=> 'text',
				'separate_option'   => true,
			),

           	array(
           		'name'		=> __( 'Manual Sync', 'wp-predictive-search' ),
           		'id'		=> 'predictive_search_manual_sync_heading',
                'type' 		=> 'heading',
           	),
           	array(
				'name'             => __( 'Manual Sync Search Data', 'wp-predictive-search' ),
				'id'               => 'wpps_search_sync_data',
				'type'             => 'ajax_multi_submit',
				'statistic_column' => 2,
				'multi_submit' => array_merge( 
					array(
						array(
							'item_id'          => 'start_sync',
							'item_name'        => __( 'Sync Initied', 'wp-predictive-search' ),
							'current_items'    => 0,
							'total_items'      => 1,
							'progressing_text' => __( 'Start Syncing...', 'wp-predictive-search' ),
							'completed_text'   => __( 'Sync Initied', 'wp-predictive-search' ),
							'submit_data'      => array(
								'ajax_url'  => admin_url( 'admin-ajax.php', 'relative' ),
								'ajax_type' => 'POST',
								'data'      => array(
									'action'   => 'wp_predictive_search_start_sync',
								)
							),
							'show_statistic'       => false,
							'statistic_customizer' => array(
								'current_color' => '#96587d',
							),
						),
					),
					$posttypes_settings,
					$custom_types_settings,
					$taxonomies_settings, 
					array(
						array(
							'item_id'          => 'sync_relationships',
							'item_name'        => __( 'Term Relationships Synced', 'wp-predictive-search' ),
							'current_items'    => ( ! empty( $current_relationships ) ) ? (int) $current_relationships : 0,
							'total_items'      => ( ! empty( $total_relationships ) ) ? (int) $total_relationships : 0,
							'progressing_text' => __( 'Syncing Term Relationships...', 'wp-predictive-search' ),
							'completed_text'   => __( 'Synced Term Relationships', 'wp-predictive-search' ),
							'submit_data'      => array(
								'ajax_url'  => admin_url( 'admin-ajax.php', 'relative' ),
								'ajax_type' => 'POST',
								'data'      => array(
									'action'   => 'wp_predictive_search_sync_relationships',
								)
							),
							'show_statistic'       => false,
							'statistic_customizer' => array(
								'current_color' => '#96587d',
							),
						),
					),
				),
				'separate_option'  => true,
				'button_name'      => $sync_button_text,
				'resubmit'         => $synced_full_data,
				'progressing_text' => __( 'Syncing Data...', 'wp-predictive-search' ),
				'completed_text'   => __( 'Synced Data', 'wp-predictive-search' ),
				'successed_text'   => sprintf( __( 'Last manual Full Database Sync completed %s', 'wp-predictive-search' ), $manual_synced_completed_time ),
				'errors_text'      => '<span style="color:#f00;font-style:normal; display: block;">' .__( 'ERROR: Latest manual sync has failed to complete', 'wp-predictive-search' ) . ' - <a data-toggle="modal" href="#manual_sync-modal">'. __( 'View Error Log', 'wp-predictive-search' ) .'</a></span>',
				'confirm_run' 	   => array(
					'allow'   => $have_confirm,
					'message' => __( 'WARNING! Auto sync is currently running. Starting a Manual Sync will cancel the Auto sync and Manual Sync will start from the beginning again. Are you Sure you want to do this?', 'wp-predictive-search' ),
				),
				'notice' 		   => __( 'You need to leave this page open for the sync to complete.', 'wp-predictive-search' ),
			),

			array(
            	'name' 		=> __( 'Search Performance Settings', 'wp-predictive-search' ),
                'type' 		=> 'heading',
				'desc'		=> __( "If you have a large site with 1,000's of posts or an underpowered server use the settings below to tweak the search performance.", 'wp-predictive-search' ),
				'id'		=> 'predictive_search_performance_settings',
				'is_box'	=> true,
           	),
			array(  
				'name' 		=> __( "Characters Before Query", 'wp-predictive-search' ),
				'desc' 		=> __("characters", 'wp-predictive-search' ). '. ' .__( 'Number of Characters min 1, max 6', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_min_characters',
				'type' 		=> 'slider',
				'default'	=> 1,
				'min'		=> 1,
				'max'		=> 6,
				'increment'	=> 1
			),
			
			array(
                'type' 		=> 'heading',
				'class'		=> 'yellow_message_container min_characters_yellow_message_container',
           	),
			array(
                'type' 		=> 'min_characters_yellow_message',
           	),
			
			array(
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Query Time Delay', 'wp-predictive-search' ),
				'desc' 		=> __( 'milli seconds', 'wp-predictive-search' ). '. ' .__( 'min 500, max 1,500', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_delay_time',
				'type' 		=> 'slider',
				'default'	=> 600,
				'min'		=> 500,
				'max'		=> 1500,
				'increment'	=> 100
			),
			
			array(
                'type' 		=> 'heading',
				'class'		=> 'yellow_message_container time_delay_yellow_message_container',
           	),
			array(
                'type' 		=> 'time_delay_yellow_message',
           	),

           	array(
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Cache Timeout', 'wp-predictive-search' ),
				'desc' 		=> __( 'hours', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_cache_timeout',
				'type' 		=> 'slider',
				'default'	=> 24,
				'min'		=> 1,
				'max'		=> 72,
				'increment'	=> 1
			),

			array(
                'type' 		=> 'heading',
				'class'		=> 'yellow_message_container cache_timeout_yellow_message_container',
           	),
			array(
                'type' 		=> 'cache_timeout_yellow_message',
           	),
		
        ));
	}

	public function error_logs_container() {
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

		global $wpps_errors_log;
		$auto_synced_error_log   = trim( $wpps_errors_log->get_error( 'auto_sync' ) );
		$manual_synced_error_log = trim( $wpps_errors_log->get_error( 'manual_sync' ) );

		if ( '' != $auto_synced_error_log ) {
			echo $wpps_errors_log->error_modal( 'auto_sync', $auto_synced_error_log );
		}

		echo '<div class="manual_sync_error_container">';
		echo $wpps_errors_log->error_modal( 'manual_sync', $manual_synced_error_log );
		echo '</div>';
?>
<style type="text/css">
	.a3rev_panel_container .a3rev-ui-ajax_multi_submit-control .a3rev-ui-ajax_multi_submit-errors {
		<?php if ( '' != $manual_synced_error_log ) { ?>
		display: inline;
		<?php } ?>
	}
</style>
<script>
(function($) {

	$(document).ready(function() {

		$(document).on( 'a3rev-ui-ajax_multi_submit-errors', '#wpps_search_sync_data', function( event, bt_ajax_submit, multi_ajax ) {
			$.ajax({
				type: 'POST',
				url: '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
				data: { action: 'wp_predictive_search_manual_sync_error' },
				success: function ( response ) {
					$('.manual_sync_error_container').html( response );
				}
			});
		});

	});

})(jQuery);
</script>
<?php
	}

	public function include_script() {
	?>
	<style type="text/css">
		.a3-ps-synched-posts {
			color: #7ad03a;
		}
		.a3-ps-synched-pages {
			color: #0073aa;
		}
		<?php 
		$manual_synced_completed_time = get_option( 'wp_predictive_search_manual_synced_completed_time', false );
		if ( false !== $manual_synced_completed_time ) {
		?>
		#predictive_search_synch_data .a3rev-ui-ajax_multi_submit-successed {
			display: inline;
		}
		<?php } ?>
	</style>
<script>
(function($) {

	$(document).ready(function() {

		if ( $("input.wpps_search_allow_auto_sync_data:checked").val() != 'yes') {
			$('.allow_auto_sync_data_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_allow_auto_sync_data', function( event, value, status ) {
			$('.allow_auto_sync_data_container').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".allow_auto_sync_data_container").slideDown();
			}
		});

		$(document).on( 'a3rev-ui-ajax_multi_submit-end', '#wpps_search_sync_data', function( event, bt_ajax_submit, multi_ajax ) {
			bt_ajax_submit.html('<?php echo __( 'Re Sync', 'wp-predictive-search' ); ?>');
			$('body').find('.wpps_sync_data_warning').slideUp('slow');
			$.ajax({
				type: 'POST',
				url: '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
				data: { action: 'wp_predictive_search_sync_end' },
				success: function ( response ) {
					$('#predictive_search_synch_data_box_inside').find('.a3rev-ui-ajax_multi_submit-successed').html( '<?php _e( 'Last manual Full Database Sync completed', 'wp-predictive-search' ); ?> ' + response.date );
				}
			});
		});

	});

})(jQuery);
</script>
    <?php
	}
		
	public function min_characters_yellow_message( $value ) {
	?>
    	<tr valign="top" class="min_characters_yellow_message_tr" style=" ">
			<th scope="row" class="titledesc">&nbsp;</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
            <?php 
				$min_characters_yellow_message = '<div>'. __( 'Number of characters that must be typed before the first search query. Setting 6 will decrease the number of queries  on your database by a factor of ~5 over a setting of 1.' , 'wp-predictive-search' ) .'</div><div>&nbsp;</div>
				<div style="clear:both"></div>
                <a class="min_characters_yellow_message_dontshow" style="float:left;" href="javascript:void(0);">'.__( "Don't show again", 'wp-predictive-search' ).'</a>
                <a class="min_characters_yellow_message_dismiss" style="float:right;" href="javascript:void(0);">'.__( "Dismiss", 'wp-predictive-search' ).'</a>
                <div style="clear:both"></div>';
            	echo $this->blue_message_box( $min_characters_yellow_message, '600px' ); 
			?>
<style>
.a3rev_panel_container .min_characters_yellow_message_container {
<?php if ( get_option( 'wpps_min_characters_message_dontshow', 0 ) == 1 ) echo 'display: none !important;'; ?>
<?php if ( !isset($_SESSION) ) { @session_start(); } if ( isset( $_SESSION['wpps_min_characters_message_dismiss'] ) ) echo 'display: none !important;'; ?>
}
</style>
<script>
(function($) {
$(document).ready(function() {
	
	$(document).on( "click", ".min_characters_yellow_message_dontshow", function(){
		$(".min_characters_yellow_message_tr").slideUp();
		$(".min_characters_yellow_message_container").slideUp();
		var data = {
				action: 		"wpps_yellow_message_dontshow",
				option_name: 	"wpps_min_characters_message_dontshow",
				security: 		"<?php echo wp_create_nonce("wpps_yellow_message_dontshow"); ?>"
			};
		$.post( "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>", data);
	});
	
	$(document).on( "click", ".min_characters_yellow_message_dismiss", function(){
		$(".min_characters_yellow_message_tr").slideUp();
		$(".min_characters_yellow_message_container").slideUp();
		var data = {
				action: 		"wpps_yellow_message_dismiss",
				session_name: 	"wpps_min_characters_message_dismiss",
				security: 		"<?php echo wp_create_nonce("wpps_yellow_message_dismiss"); ?>"
			};
		$.post( "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>", data);
	});
});
})(jQuery);
</script>
			</td>
		</tr>
    <?php
	
	}
	
	public function time_delay_yellow_message( $value ) {
	?>
    	<tr valign="top" class="time_delay_yellow_message_tr" style=" ">
			<th scope="row" class="titledesc">&nbsp;</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
            <?php 
				$time_delay_yellow_message = '<div>'. __( 'Time delay after a character is entered and query begins. Example setting 1,000 is 1 second after that last charcter is typed. If speed type a 10 letter word then first query is whole word not 1 query for each character. Reducing queries  to database by a factor of ~10.' , 'wp-predictive-search' ) .'</div><div>&nbsp;</div>
				<div style="clear:both"></div>
                <a class="time_delay_yellow_message_dontshow" style="float:left;" href="javascript:void(0);">'.__( "Don't show again", 'wp-predictive-search' ).'</a>
                <a class="time_delay_yellow_message_dismiss" style="float:right;" href="javascript:void(0);">'.__( "Dismiss", 'wp-predictive-search' ).'</a>
                <div style="clear:both"></div>';
            	echo $this->blue_message_box( $time_delay_yellow_message, '600px' ); 
			?>
<style>
.a3rev_panel_container .time_delay_yellow_message_container {
<?php if ( get_option( 'wpps_time_delay_message_dontshow', 0 ) == 1 ) echo 'display: none !important;'; ?>
<?php if ( !isset($_SESSION) ) { @session_start(); } if ( isset( $_SESSION['wpps_time_delay_message_dismiss'] ) ) echo 'display: none !important;'; ?>
}
</style>
<script>
(function($) {
$(document).ready(function() {
	
	$(document).on( "click", ".time_delay_yellow_message_dontshow", function(){
		$(".time_delay_yellow_message_tr").slideUp();
		$(".time_delay_yellow_message_container").slideUp();
		var data = {
				action: 		"wpps_yellow_message_dontshow",
				option_name: 	"wpps_time_delay_message_dontshow",
				security: 		"<?php echo wp_create_nonce("wpps_yellow_message_dontshow"); ?>"
			};
		$.post( "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>", data);
	});
	
	$(document).on( "click", ".time_delay_yellow_message_dismiss", function(){
		$(".time_delay_yellow_message_tr").slideUp();
		$(".time_delay_yellow_message_container").slideUp();
		var data = {
				action: 		"wpps_yellow_message_dismiss",
				session_name: 	"wpps_time_delay_message_dismiss",
				security: 		"<?php echo wp_create_nonce("wpps_yellow_message_dismiss"); ?>"
			};
		$.post( "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>", data);
	});
});
})(jQuery);
</script>
			</td>
		</tr>
    <?php
	
	}

	public function cache_timeout_yellow_message( $value ) {
	?>
    	<tr valign="top" class="cache_timeout_yellow_message_tr" style=" ">
			<th scope="row" class="titledesc">&nbsp;</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
            <?php 
				$cache_timeout_yellow_message = '<div>'. __( 'How long should cached popup result remain fresh? Use low value if your site have add or update many post daily. A good starting point is 24 hours.' , 'wp-predictive-search' ) .'</div><div>&nbsp;</div>
				<div style="clear:both"></div>
                <a class="cache_timeout_yellow_message_dontshow" style="float:left;" href="javascript:void(0);">'.__( "Don't show again", 'wp-predictive-search' ).'</a>
                <a class="cache_timeout_yellow_message_dismiss" style="float:right;" href="javascript:void(0);">'.__( "Dismiss", 'wp-predictive-search' ).'</a>
                <div style="clear:both"></div>';
            	echo $this->blue_message_box( $cache_timeout_yellow_message, '600px' ); 
			?>
<style>
.a3rev_panel_container .cache_timeout_yellow_message_container {
<?php if ( get_option( 'wpps_cache_timeout_message_dontshow', 0 ) == 1 ) echo 'display: none !important;'; ?>
<?php if ( !isset($_SESSION) ) { @session_start(); } if ( isset( $_SESSION['wpps_cache_timeout_message_dismiss'] ) ) echo 'display: none !important;'; ?>
}
</style>
<script>
(function($) {
$(document).ready(function() {
	
	$(document).on( "click", ".cache_timeout_yellow_message_dontshow", function(){
		$(".cache_timeout_yellow_message_tr").slideUp();
		$(".cache_timeout_yellow_message_container").slideUp();
		var data = {
				action: 		"wpps_yellow_message_dontshow",
				option_name: 	"wpps_cache_timeout_message_dontshow",
				security: 		"<?php echo wp_create_nonce("wpps_yellow_message_dontshow"); ?>"
			};
		$.post( "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>", data);
	});
	
	$(document).on( "click", ".cache_timeout_yellow_message_dismiss", function(){
		$(".cache_timeout_yellow_message_tr").slideUp();
		$(".cache_timeout_yellow_message_container").slideUp();
		var data = {
				action: 		"wpps_yellow_message_dismiss",
				session_name: 	"wpps_cache_timeout_message_dismiss",
				security: 		"<?php echo wp_create_nonce("wpps_yellow_message_dismiss"); ?>"
			};
		$.post( "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>", data);
	});
});
})(jQuery);
</script>
			</td>
		</tr>
    <?php
	
	}
}

}

// global code
namespace {

/** 
 * wp_predictive_search_performance_settings_form()
 * Define the callback function to show subtab content
 */
function wp_predictive_search_performance_settings_form() {
	global $wp_predictive_search_performance_settings;
	$wp_predictive_search_performance_settings->settings_form();
}

}
