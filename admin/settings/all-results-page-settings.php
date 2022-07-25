<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\FrameWork\Settings {

use A3Rev\WPPredictiveSearch\FrameWork;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------
WC Predictive Search All Results Page Settings

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

class All_Results_Pages extends FrameWork\Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'all-results-page';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wpps_all_results_pages_settings';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wpps_all_results_pages_settings';
	
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
		$this->init_form_fields();
		$this->subtab_init();
		
		$this->form_messages = array(
				'success_message'	=> __( 'All Results Pages successfully saved.', 'wp-predictive-search' ),
				'error_message'		=> __( 'Error: All Results Pages can not save.', 'wp-predictive-search' ),
				'reset_message'		=> __( 'All Results Pages successfully reseted.', 'wp-predictive-search' ),
			);

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );
		
		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );

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
			'name'				=> 'all-results-page',
			'label'				=> __( 'All Results Pages', 'wp-predictive-search' ),
			'callback_function'	=> 'wpps_all_results_page_settings_form',
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
		
  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(

     		array(
            	'name' 		=> __('Search Results Page Configuration', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'desc' 		=> ( class_exists('SitePress') ) ? __('Predictive Search has detected the WPML plugin. On install a search page was auto created for each language in use. Please use the WPML String Translations plugin to make translation for plugin text for each page. If adding another language after installing Predictive Search you have to manually create a search page for it.', 'wp-predictive-search' ) : __('A search results page needs to be selected so that WordPress Predictive Search knows where to show search results. This page should have been created upon installation of the plugin, if not you need to create it.', 'wp-predictive-search' ),
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Select Search Page', 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_page_id',
				'type' 		=> 'single_select_page',
				'separate_option'  => true,
			),
			array(
				'name' 		=> __( 'Search Page Content', 'wp-predictive-search' ),
				'desc'		=> __( "Create the Search Page content with the PS Shortcode or PS Gutenberg Blocks." ),
				'class'		=> 'wpps_search_page_content_type',
				'id' 		=> 'wpps_search_page_content_type',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'shortcode',
				'checked_value'		=> 'shortcode',
				'unchecked_value'	=> 'block',
				'checked_label'		=> __( 'SHORTCODE', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'PS BLOCK', 'wp-predictive-search' ),
				'separate_option'  => true,
			),
			array(
            	'desc' 		=> sprintf( __("The WordPress Predictive Search shortcode is %s. To use it you need to check that the Search Page you set above has that shortcode in the content section, otherwise the results can't show. You will see more setting show below for creating the Search Results template.", 'wp-predictive-search' ), ' [wpps_search]' ),
                'type' 		=> 'heading',
                'class'		=> 'wpps_search_page_shortcode',
           	),
           	array(
                'type' 		=> 'heading',
                'desc' 		=> __('With this selection you need edit the Search Page, remove the shortcode [wpps_search], and use WordPress Predictive Search Block to build your post and page List or Grid search results style and layout.', 'wp-predictive-search' ),
                'class'		=> 'wpps_search_page_block',
           	),
		
			array(
            	'name' 		=> __( 'Search Results Template', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'desc' 		=> __('You have chosen to use the WordPress Predictive Search Shortcode in your designated All Results Search Page. You now need to choose to apply the Themes template for the search results page display or use the Predictive Search built in template.', 'wp-predictive-search' ),
                'id'		=> 'predictive_search_results_settings_box',
                'class'		=> 'predictive_search_results_settings_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Select a Template', 'wp-predictive-search' ),
				'class'		=> 'wpps_search_result_template_type',
				'id' 		=> 'template_type',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'plugin',
				'checked_value'		=> 'plugin',
				'unchecked_value'	=> 'theme',
				'checked_label'		=> __( 'PLUGIN TEMPLATE', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'THEME TEMPLATE', 'wp-predictive-search' ),
			),

			array(
                'type' 		=> 'heading',
				'class'		=> 'wpps_search_result_content_type_shortcode wpps_search_result_template_type_container_theme',
				'desc' 		=> __('Predictive Search will attempt to get the themes archives template file in content.php. You may have to add additional class names if your results do not show correctly. You may need to ask your theme developer what these would be OR switch to the built in Predictive Search Search Page template.', 'wp-predictive-search' ),
           	),
           	array(  
				'name' 		=> __( 'Container Classes Additional', 'wp-predictive-search' ),
				'id' 		=> 'theme_container_class',
				'type' 		=> 'text',
				'default'	=> ''
			),

           	array(
                'type' 		=> 'heading',
				'class'		=> 'wpps_search_result_content_type_shortcode wpps_search_result_template_type_container_plugin',
				'desc' 		=> __('You are using the built-in Predictive Search Plugin Template. Use the settings below to create your results layout and style.', 'wp-predictive-search' ),
           	),
			array(
				'name' 		=> __( 'Display Type', 'wp-predictive-search' ),
				'id' 		=> 'display_type',
				'class'		=> 'wpps_search_result_display_type',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'grid',
				'checked_value'		=> 'grid',
				'unchecked_value'	=> 'list',
				'checked_label'		=> __( 'GRID VIEW', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'LIST VIEW', 'wp-predictive-search' ),
			),
			array(  
				'name' 		=> __( 'Description character count', 'wp-predictive-search' ),
				'desc' 		=> __('The number of characters from post descriptions that shows with each search result.', 'wp-predictive-search' ),
				'id' 		=> 'text_lenght',
				'type' 		=> 'text',
				'css' 		=> 'width:40px;',
				'default'	=> 100
			),
			array(  
				'name' 		=> __( 'Post Categories Meta', 'wp-predictive-search' ),
				'desc' 		=> __('On to show Categories Meta on Posts search results', 'wp-predictive-search' ),
				'id' 		=> 'categories_enable',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),
			array(  
				'name' 		=> __( 'Post Tags Meta', 'wp-predictive-search' ),
				'desc' 		=> __('On to show Tags Meta on Post search results', 'wp-predictive-search' ),
				'id' 		=> 'tags_enable',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),

			array(
            	'name' 		=> __( 'Search Results Page Endless Scroll', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'desc' 		=> __('The Search Results Page shows results from most relevant to least in each section and uses endless scroll to ensure the results load fast. This applies search page results created by the shortcode for both the Plugin Template or your Themes template.', 'wp-predictive-search' ),
                'id'		=> 'predictive_search_results_settings_box',
                'class'		=> 'predictive_search_results_settings_box',
                'is_box'	=> true,
           	),
			array(  
				'name' 		=> __( 'Number of Results', 'wp-predictive-search' ),
				'desc' 		=> __('The number of results that show per page before endless scroll loads the next lot of results.', 'wp-predictive-search' ),
				'id' 		=> 'result_items',
				'type' 		=> 'text',
				'css' 		=> 'width:40px;',
				'default'	=> 12
			),

			array(
            	'name' 		=> __( 'Grid Settings', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_grid_card_box',
                'class'		=> 'wpps_search_result_content_type_shortcode wpps_search_result_template_type_container_plugin wpps_search_result_display_type_container_grid',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Number of Column', 'wp-predictive-search' ),
				'desc'		=> 'columns',
				'id' 		=> 'grid_card_column',
				'type' 		=> 'slider',
				'default'	=> 3,
				'min'		=> 1,
				'max'		=> 6,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Card Space', 'wp-predictive-search' ),
				'id' 		=> 'grid_card_gap',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'grid_card_column_gap',
	 										'name' 		=> __( 'Column Gap', 'wp-predictive-search' ) . '(px)',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '20' ),
	 								array(  'id' 		=> 'grid_card_row_gap',
	 										'name' 		=> __( 'Row Gap', 'wp-predictive-search' ) . '(px)',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '20' ),
	 							)
			),
			array(
				'name' 		=> __( 'Card Border', 'wp-predictive-search' ),
				'id' 		=> 'grid_card_border',
				'type' 		=> 'border',
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#ddd', 'corner' => 'square' , 'top_left_corner' => 0 , 'top_right_corner' => 0 , 'bottom_left_corner' => 0 , 'bottom_right_corner' => 0 ),
			),
			array(
				'name' 		=> __( 'Card Border Shadow', 'wp-predictive-search' ),
				'id' 		=> 'grid_card_shadow',
				'type' 		=> 'box_shadow',
				'default'	=> array( 'enable' => 1, 'h_shadow' => '0px' , 'v_shadow' => '1px', 'blur' => '5px' , 'spread' => '2px', 'color' => '#dddddd', 'inset' => '' )
			),

			array(
            	'name' 		=> __( 'List Settings', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_list_box',
                'class'		=> 'wpps_search_result_content_type_shortcode wpps_search_result_template_type_container_plugin wpps_search_result_display_type_container_list',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Item Image Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'list_image_size',
				'type' 		=> 'slider',
				'default'	=> 64,
				'min'		=> 32,
				'max'		=> 200,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Divider', 'wp-predictive-search' ),
				'id' 		=> 'list_divider',
				'type' 		=> 'border_styles',
                'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#c2c2c2' ),
			),
			array(
				'name' 		=> __( 'Divider Margin', 'wp-predictive-search' ),
				'id' 		=> 'list_divider_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'list_divider_margin_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'list_divider_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),

			array(
            	'name' 		=> __( 'List/Grid Template', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_plugin_template_box',
                'class'		=> 'wpps_search_result_content_type_shortcode wpps_search_result_template_type_container_plugin ',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Content Container Background Colour', 'wp-predictive-search' ),
				'id' 		=> 'content_bg_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
           	array(
				'name' 		=> __( 'Content Container Padding', 'wp-predictive-search' ),
				'id' 		=> 'content_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'content_padding_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ) . '(px)',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'content_padding_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ) . '(px)',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'content_padding_left',
	 										'name' 		=> __( 'Left', 'wp-predictive-search' ) . '(px)',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'content_padding_right',
	 										'name' 		=> __( 'Right', 'wp-predictive-search' ) . '(px)',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
           	array(
				'name' 		=> __( 'Item Name Font', 'wp-predictive-search' ),
				'id' 		=> 'title_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#0000ff' ),
			),
			array(
				'name' 		=> __( 'Item Name Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'title_hover_color',
				'type' 		=> 'color',
				'default'	=> '#0000ff',
			),
			array(
				'name' 		=> __( 'Item Name Alignment', 'wp-predictive-search' ),
				'id' 		=> 'title_align',
				'css' 		=> 'width:80px;',
				'type' 		=> 'select',
				'default'	=> 'none',
				'options'	=> array(
						'none'			=> __( 'None', 'wp-predictive-search' ) ,
						'left'			=> __( 'Left', 'wp-predictive-search' ) ,
						'center'		=> __( 'Center', 'wp-predictive-search' ) ,
						'right'			=> __( 'Right', 'wp-predictive-search' ) ,
					),
			),
			array(
				'name' 		=> __( 'Item Name Margin', 'wp-predictive-search' ),
				'id' 		=> 'title_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'title_margin_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '' ),
	 								array(  'id' 		=> 'title_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '' ),
	 							)
			),
			array(
				'name' 		=> __( 'Item Description Font', 'wp-predictive-search' ),
				'id' 		=> 'description_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Item Description Alignment', 'wp-predictive-search' ),
				'id' 		=> 'description_align',
				'css' 		=> 'width:80px;',
				'type' 		=> 'select',
				'default'	=> 'none',
				'options'	=> array(
						'none'			=> __( 'None', 'wp-predictive-search' ) ,
						'left'			=> __( 'Left', 'wp-predictive-search' ) ,
						'center'		=> __( 'Center', 'wp-predictive-search' ) ,
						'right'			=> __( 'Right', 'wp-predictive-search' ) ,
					),
			),
			array(
				'name' 		=> __( 'Item Description Margin', 'wp-predictive-search' ),
				'id' 		=> 'description_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'description_margin_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '' ),
	 								array(  'id' 		=> 'description_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
				'name' 		=> __( 'Item Category Font', 'wp-predictive-search' ),
				'id' 		=> 'category_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#0000ff' ),
			),
			array(
				'name' 		=> __( 'Item Category Link Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'category_hover_color',
				'type' 		=> 'color',
				'default'	=> '#0000ff',
			),
			array(
				'name' 		=> __( "Item 'Category' Label Font", 'wp-predictive-search' ),
				'id' 		=> 'category_label_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Item Category Alignment', 'wp-predictive-search' ),
				'id' 		=> 'category_align',
				'css' 		=> 'width:80px;',
				'type' 		=> 'select',
				'default'	=> 'none',
				'options'	=> array(
						'none'			=> __( 'None', 'wp-predictive-search' ) ,
						'left'			=> __( 'Left', 'wp-predictive-search' ) ,
						'center'		=> __( 'Center', 'wp-predictive-search' ) ,
						'right'			=> __( 'Right', 'wp-predictive-search' ) ,
					),
			),
			array(
				'name' 		=> __( 'Item Category Margin', 'wp-predictive-search' ),
				'id' 		=> 'category_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'category_margin_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '' ),
	 								array(  'id' 		=> 'category_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ) . '(px)',
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '' ),
	 							)
			),
		
        ));
	}

	public function include_script() {
	?>
<script>
(function($) {

	$(document).ready(function() {

		if ( $("input.wpps_search_page_content_type:checked").val() != 'shortcode') {
			$('.wpps_search_page_shortcode').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
			$('.predictive_search_results_settings_box').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
			$('.wpps_search_result_content_type_shortcode').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		} else {
			$('.wpps_search_page_block').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		if ( $("input.wpps_search_result_template_type:checked").val() != 'plugin') {
			$('.wpps_search_result_template_type_container_plugin').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		} else {
			$('.wpps_search_result_template_type_container_theme').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		if ( $("input.wpps_search_result_display_type:checked").val() != 'grid') {
			$('.wpps_search_result_display_type_container_grid').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		} else {
			$('.wpps_search_result_display_type_container_list').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_page_content_type', function( event, value, status ) {
			$('.wpps_search_page_shortcode').attr('style','display:none;');
			$('.wpps_search_page_block').attr('style','display:none;');
			$('.predictive_search_results_settings_box').attr('style','display:none;');
			$('.wpps_search_result_content_type_shortcode').attr('style','display:none;');

			if ( status == 'true' ) {
				$(".wpps_search_page_block").slideUp();
				$(".wpps_search_page_shortcode").slideDown();
				$(".predictive_search_results_settings_box").slideDown();

				if ( $("input.wpps_search_result_template_type:checked").val() != 'plugin') {
					$(".wpps_search_result_template_type_container_theme").slideDown();
				} else {
					$(".wpps_search_result_template_type_container_plugin").slideDown();
				
					if ( $("input.wpps_search_result_display_type:checked").val() != 'grid') {
						$(".wpps_search_result_display_type_container_list").slideDown();
						$(".wpps_search_result_display_type_container_grid").slideUp();
					} else {
						$(".wpps_search_result_display_type_container_list").slideUp();
						$(".wpps_search_result_display_type_container_grid").slideDown();
					}
				}
			} else {
				$(".wpps_search_page_block").slideDown();
				$(".wpps_search_page_shortcode").slideUp();
				$(".predictive_search_results_settings_box").slideUp();
			}
		});

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_result_template_type', function( event, value, status ) {
			$('.wpps_search_result_template_type_container_theme').attr('style','display:none;');
			$('.wpps_search_result_template_type_container_plugin').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".wpps_search_result_template_type_container_theme").slideUp();
				$(".wpps_search_result_template_type_container_plugin").slideDown();

				if ( $("input.wpps_search_result_display_type:checked").val() != 'grid') {
					$(".wpps_search_result_display_type_container_grid").slideUp();
				} else {
					$(".wpps_search_result_display_type_container_list").slideUp();
				}
			} else {
				$(".wpps_search_result_template_type_container_theme").slideDown();
				$(".wpps_search_result_template_type_container_plugin").slideUp();
			}
		});

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.wpps_search_result_display_type', function( event, value, status ) {
			$('.wpps_search_result_display_type_container_grid').attr('style','display:none;');
			$('.wpps_search_result_display_type_container_list').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".wpps_search_result_display_type_container_grid").slideDown();
				$(".wpps_search_result_display_type_container_list").slideUp();
			} else {
				$(".wpps_search_result_display_type_container_grid").slideUp();
				$(".wpps_search_result_display_type_container_list").slideDown();

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
 * wpps_all_results_page_settings_form()
 * Define the callback function to show subtab content
 */
function wpps_all_results_page_settings_form() {
	global $wpps_all_results_page_settings;
	$wpps_all_results_page_settings->settings_form();
}

}
