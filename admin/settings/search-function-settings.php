<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\FrameWork\Settings {

use A3Rev\WPPredictiveSearch\FrameWork;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------
WC Predictive Search Exclude Content Settings

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

class Search_Function extends FrameWork\Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'search-function';
	
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
	public $form_key = 'wpps_search_function_settings';
	
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
				'success_message'	=> __( 'Search Function Settings successfully saved.', 'wp-predictive-search' ),
				'error_message'		=> __( 'Error: Search Function Settings can not save.', 'wp-predictive-search' ),
				'reset_message'		=> __( 'Search Function Settings successfully reseted.', 'wp-predictive-search' ),
			);

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );
		
		add_action( $this->plugin_name . '_settings_' . 'predictive_search_code' . '_start', array( $this, 'predictive_search_code_start' ) );
		
		//add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
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
			'name'				=> 'search-function',
			'label'				=> __( 'Search Function', 'wp-predictive-search' ),
			'callback_function'	=> 'wpps_search_function_settings_form',
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

		$disabled_cat_dropdown = false;
		if ( is_admin() ) {
			global $wpps_cache;
			if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
				$disabled_cat_dropdown = true;
			}
		}

		$posttypes_number_settings = array();
		if ( ! empty( $posttypes_support ) ) {
			foreach ( $posttypes_support as $posttype ) {
				$posttypes_number_settings[] = array(  
					'name' 		=> $posttype['label'],
					'desc' 		=> sprintf( __('Number of %s to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'wp-predictive-search' ), $posttype['label'] ),
					'id' 		=> 'wpps_search_'.$posttype['name'].'_items',
					'type' 		=> 'text',
					'css' 		=> 'width:40px;',
				);
			}
		}

		$custom_types_number_settings = apply_filters( 'wpps_custom_types_number_customize_function_settings', array() );

		$taxonomies_number_settings = array();
		if ( ! empty( $taxonomies_support ) ) {
			foreach ( $taxonomies_support as $taxonomy ) {
				$taxonomies_number_settings[] = array(  
					'name' 		=> $taxonomy['label'],
					'desc' 		=> sprintf( __('Number of %s to show in search field drop-down. Leave &lt;empty&gt; for not activated', 'wp-predictive-search' ), $taxonomy['label'] ),
					'id' 		=> 'wpps_search_'.$taxonomy['name'].'_items',
					'type' 		=> 'text',
					'css' 		=> 'width:40px;',
				);
			}
		}

  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array_merge( array(
		
			array(
            	'name' 		=> __( 'Predictive Search Function', 'wp-predictive-search' ),
                'type' 		=> 'heading',
          		'id' 		=> 'predictive_search_code',
          		'is_box'	=> true,
           	),
			
			array(
            	'name' 		=> __( 'Customize Search Function values :', 'wp-predictive-search' ),
				'desc'		=> __("The values you set here will be shown when you add the global search function to your header.php file. After adding the global function to your header.php file you can change the values here and 'Update' and they will be auto updated in the function.", 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_customize_function_box',
                'is_box'	=> true,
           	),
		),
		$posttypes_number_settings,
		$custom_types_number_settings,
		$taxonomies_number_settings,
		array(
			array(
				'name' 		=> __( 'Select Template', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_widget_template',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'sidebar',
				'checked_value'		=> 'sidebar',
				'unchecked_value'	=> 'header',
				'checked_label'		=> __( 'WIDGET', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'HEADER', 'wp-predictive-search' ),
			),
			array(
				'name' 		=> __( 'Category Dropdown', 'wp-predictive-search' ),
				'desc' 		=> __('On to search in Post Category.', 'wp-predictive-search' )
				. ( ( $disabled_cat_dropdown ) ? '</span><div style="clear: both;">'.sprintf( __( 'Activate and build <a href="%s">Category Cache</a> to activate this feature', 'wp-predictive-search' ), admin_url( 'admin.php?page=wp-predictive-search&tab=search-box-settings&box_open=predictive_search_category_cache_box#predictive_search_category_cache_box', 'relative' ) ).'</div><span>' : '' ),
				'id' 		=> 'wpps_search_show_catdropdown',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
				'custom_attributes' => ( $disabled_cat_dropdown ) ? array( 'disabled' => 'disabled' ) : array(),
			),
			array(
				'name' 		=> __( 'Select Taxonomy', 'wp-predictive-search' ),
				'desc'		=> __( "Select a taxonomy for Category Dropdown", 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_in_taxonomy',
				'type' 		=> 'select',
				'default'	=> 'category',
				'options'	=> wpps_taxonomies_dropdown(),
			),
			array(  
				'name' 		=> __( 'Image', 'wp-predictive-search' ),
				'desc' 		=> __('On to show Results Images', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_show_image',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),
			array(  
				'name' 		=> __( 'Description', 'wp-predictive-search' ),
				'desc' 		=> __('On to show Results Description', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_show_desc',
				'class'		=> 'wpps_search_show_desc',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),
			array(
            	'name' 		=> '',
                'type' 		=> 'heading',
                'class'		=> 'wpps_search_show_desc_container',
           	),
			array(  
				'name' 		=> __( 'Character Count', 'wp-predictive-search' ),
				'desc' 		=> __('Number of characters from results description to show in search field drop-down. Default value is "100".', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_character_max',
				'type' 		=> 'text',
				'css' 		=> 'width:40px;',
			),

			array(
            	'name' 		=> '',
                'type' 		=> 'heading',
           	),
			array(  
				'name' 		=> __( 'Post Categories', 'wp-predictive-search' ),
				'desc' 		=> __('On to show Categories that Post assigned to', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_show_in_cat',
				'class'		=> 'wpps_search_show_in_cat',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),
		)
		
        ));
	}
	
	public function predictive_search_code_start() {
		echo '<tr valign="top"><td class="forminp" colspan="2">';
		?>
        <?php _e('Copy and paste this global function into your themes header.php file to replace any existing search function. (Be sure to delete the existing WordPress or Theme search function)', 'wp-predictive-search' );?>
            <br /><code>&lt;?php<br />
            $ps_echo = true ; <br /> 
            if ( function_exists( 'wp_predictive_search_widget' ) ) wp_predictive_search_widget( $ps_echo ); <br /> 
            ?&gt;</code>
		<?php echo '</td></tr>';
	}

	public function include_script() {
	?>
<script>
(function($) {

	$(document).ready(function() {

		if ( $("input.wpps_search_show_desc:checked").val() != 'yes') {
			$('.wpps_search_show_desc_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_show_desc', function( event, value, status ) {
			$('.wpps_search_show_desc_container').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".wpps_search_show_desc_container").slideDown();
			} else {
				$(".wpps_search_show_desc_container").slideUp();
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
 * wpps_search_function_settings_form()
 * Define the callback function to show subtab content
 */
function wpps_search_function_settings_form() {
	global $wpps_search_function_settings;
	$wpps_search_function_settings->settings_form();
}

}
