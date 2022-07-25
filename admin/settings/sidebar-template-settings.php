<?php
/* "Copyright 2012 A3 Revolution Web Design" This software is distributed under the terms of GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007 */

namespace A3Rev\WPPredictiveSearch\FrameWork\Settings {

use A3Rev\WPPredictiveSearch\FrameWork;

// File Security Check
if ( ! defined( 'ABSPATH' ) ) exit;

/*-----------------------------------------------------------------------------------
WC Predictive Search Input Box Settings

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

class Sidebar_Template extends FrameWork\Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'sidebar-template-settings';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wp_predictive_search_sidebar_template_settings';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_predictive_search_sidebar_template_settings';
	
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
				'success_message'	=> __( 'Widget Template Settings successfully saved.', 'wp-predictive-search' ),
				'error_message'		=> __( 'Error: Widget Template Settings can not save.', 'wp-predictive-search' ),
				'reset_message'		=> __( 'Widget Template Settings successfully reseted.', 'wp-predictive-search' ),
			);
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '_get_all_settings' , array( $this, 'get_settings' ) );
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
			'name'				=> 'sidebar-template-settings',
			'label'				=> __( 'Widget Template', 'wp-predictive-search' ),
			'callback_function'	=> 'wp_predictive_search_sidebar_template_settings_form',
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
     	$this->form_fields = apply_filters( $this->form_key . '_settings_fields', array(
     		array(
            	'name' 		=> __( 'Search Box Container', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_container_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Search Box Alignment', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_box_align',
				'desc'		=> __( 'Alignment within the widget area container', 'wp-predictive-search' ),
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
				'name' 		=> __( 'Search Box Width', 'wp-predictive-search' ),
				'desc'		=> '% ' . __( 'of width of widget area container', 'wp-predictive-search' ) ,
				'id' 		=> 'sidebar_search_box_wide',
				'type' 		=> 'slider',
				'default'	=> 100,
				'min'		=> 30,
				'max'		=> 100,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Search Box Height', 'wp-predictive-search' ),
				'desc'		=> 'px',
				'id' 		=> 'sidebar_search_box_height',
				'type' 		=> 'text',
				'css'		=> 'width:40px;',
				'default'	=> 35,
			),
			array(
				'name' 		=> __( 'Search Box Margin', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_box_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_search_box_margin_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 								array(  'id' 		=> 'sidebar_search_box_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_margin_left',
	 										'name' 		=> __( 'Left', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 								array(  'id' 		=> 'sidebar_search_box_margin_right',
	 										'name' 		=> __( 'Right', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 							)
			),
			array(
				'name' 		=> __( 'Search Box Margin (Mobiles)', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_box_mobile_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_left',
	 										'name' 		=> __( 'Left', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 								array(  'id' 		=> 'sidebar_search_box_mobile_margin_right',
	 										'name' 		=> __( 'Right', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
				'name' 		=> __( 'Search Box Border', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_box_border',
				'type' 		=> 'border',
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#cdcdcd', 'corner' => 'rounded' , 'top_left_corner' => 4 , 'top_right_corner' => 4 , 'bottom_left_corner' => 4 , 'bottom_right_corner' => 4 ),
			),
			array(
				'name' 		=> __( 'Search Box Border Focus', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_box_border_color_focus',
				'type' 		=> 'color',
				'default'	=> '#febd69',
			),
			array(
				'name' 		=> __( 'Border Shadow', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_box_shadow',
				'type' 		=> 'box_shadow',
				'default'	=> array( 'enable' => 0, 'h_shadow' => '0px' , 'v_shadow' => '1px', 'blur' => '0px' , 'spread' => '0px', 'color' => '#555555', 'inset' => 'inset' )
			),

			array(
            	'name' 		=> __( 'Search in Category Dropdown', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_category_dropdown_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Alignment', 'wp-predictive-search' ),
				'desc'		=> __( 'If set LEFT then Predictive Search Icon on RIGHT ', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_category_dropdown_align',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'left',
				'checked_value'		=> 'left',
				'unchecked_value'	=> 'right',
				'checked_label'		=> __( 'LEFT', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'RIGHT', 'wp-predictive-search' ),
			),
			array(
				'name' 		=> __( 'Maximum Width', 'wp-predictive-search' ),
				'desc'		=> __( '% width of Search Box', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_category_dropdown_max_wide',
				'type' 		=> 'slider',
				'default'	=> 30,
				'min'		=> 10,
				'max'		=> 50,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Category Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_category_dropdown_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#777777' )
			),
			array(
				'name' 		=> __( 'Down Icon Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_category_dropdown_icon_size',
				'type' 		=> 'slider',
				'default'	=> 12,
				'min'		=> 8,
				'max'		=> 30,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Down Icon Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_category_dropdown_icon_color',
				'type' 		=> 'color',
				'default'	=> '#555555'
			),
			array(
				'name' 		=> __( 'Background Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_category_dropdown_bg_color',
				'type' 		=> 'color',
				'default'	=> '#f3f3f3'
			),
			array(
				'name' 		=> __( 'Vertical Side Border', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_category_dropdown_side_border',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#cdcdcd' ),
			),

			array(
            	'name' 		=> __( 'Search Icon', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_button_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Search Icon Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_search_icon_size',
				'type' 		=> 'slider',
				'default'	=> 16,
				'min'		=> 8,
				'max'		=> 30,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Search Icon Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_icon_color',
				'type' 		=> 'color',
				'default'	=> '#555555'
			),
			array(
				'name' 		=> __( 'Search Icon Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_icon_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff'
			),
			array(
				'name' 		=> __( 'Background Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_icon_bg_color',
				'type' 		=> 'color',
				'default'	=> '#febd69'
			),
			array(
				'name' 		=> __( 'Background Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_icon_bg_hover_color',
				'type' 		=> 'color',
				'default'	=> '#f3a847'
			),
			array(
				'name' 		=> __( 'Vertical Side Border', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_search_icon_side_border',
				'type' 		=> 'border_styles',
				'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#febd69' ),
			),

			array(
            	'name' 		=> __( 'Search Input Box', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_input_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Input Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_input_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#111111' )
			),
			array(
				'name' 		=> __( 'Input Padding', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_input_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_input_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '6' ),
	 								array(  'id' 		=> 'sidebar_input_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
				'name' 		=> __( 'Input Background Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_input_bg_color',
				'type' 		=> 'bg_color',
				'default'	=> array( 'enable' => 1, 'color' => '#ffffff' )
			),
			array(
				'name' 		=> __( 'Loading Icon Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_loading_icon_size',
				'type' 		=> 'slider',
				'default'	=> 16,
				'min'		=> 8,
				'max'		=> 30,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Loading Icon Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_loading_icon_color',
				'type' 		=> 'color',
				'default'	=> '#555555'
			),

			array(
            	'name' 		=> __( 'Close Icon (mobile only)', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_close_icon_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Close Icon Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_close_icon_size',
				'type' 		=> 'slider',
				'default'	=> 20,
				'min'		=> 8,
				'max'		=> 30,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Close Icon Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_close_icon_color',
				'type' 		=> 'color',
				'default'	=> '#ff0606'
			),
			array(
				'name' 		=> __( 'Close Icon Margin', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_close_icon_margin',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_close_icon_margin_top',
	 										'name' 		=> __( 'Top', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '2' ),
	 								array(  'id' 		=> 'sidebar_close_icon_margin_bottom',
	 										'name' 		=> __( 'Bottom', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '2' ),
	 								array(  'id' 		=> 'sidebar_close_icon_margin_left',
	 										'name' 		=> __( 'Left', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '0' ),
	 								array(  'id' 		=> 'sidebar_close_icon_margin_right',
	 										'name' 		=> __( 'Right', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),

			array(
            	'name' 		=> __( 'Click Icon to Show Search Box (mobile only)', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_icon_mobile_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Search Icon On Mobile', 'wp-predictive-search' ),
				'id' 		=> 'search_icon_mobile',
				'class'		=> 'search_icon_mobile',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'no',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),
			array(
                'type' 		=> 'heading',
                'class'		=> 'search_icon_mobile_container',
           	),
           	array(
				'name' 		=> __( 'Search Icon Alignment', 'wp-predictive-search' ),
				'id' 		=> 'search_icon_mobile_align',
				'css' 		=> 'width:80px;',
				'type' 		=> 'select',
				'default'	=> 'center',
				'options'	=> array(
						'left'			=> __( 'Left', 'wp-predictive-search' ) ,
						'center'		=> __( 'Center', 'wp-predictive-search' ) ,
						'right'			=> __( 'Right', 'wp-predictive-search' ) ,
					),
			),
			array(
				'name' 		=> __( 'Search Icon Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'search_icon_mobile_size',
				'type' 		=> 'slider',
				'default'	=> 25,
				'min'		=> 8,
				'max'		=> 50,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Search Icon Colour', 'wp-predictive-search' ),
				'id' 		=> 'search_icon_mobile_color',
				'type' 		=> 'color',
				'default'	=> '#555555'
			),

			array(
            	'name' 		=> __( 'Results Dropdown Container', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Dropdown Wide', 'wp-predictive-search' ),
				'id' 		=> 'popup_wide',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'input_wide',
				'checked_value'		=> 'input_wide',
				'unchecked_value'	=> 'full_wide',
				'checked_label'		=> __( 'Input Wide', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'Full Wide', 'wp-predictive-search' ),
			),
           	array(
            	'name' 		=> __( 'Container Border', 'wp-predictive-search' ),
                'id' 		=> 'sidebar_popup_border',
				'type' 		=> 'border',
                'default'	=> array( 'width' => '1px', 'style' => 'solid', 'color' => '#c2c2c2', 'corner' => 'square' , 'top_left_corner' => 0 , 'top_right_corner' => 0 , 'bottom_left_corner' => 0 , 'bottom_right_corner' => 0 ),
           	),

           	array(
            	'name' 		=> __( 'Results Dropdown Section Titles', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_title_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Title Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_heading_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Title Padding', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_heading_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_popup_heading_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '3' ),
	 								array(  'id' 		=> 'sidebar_popup_heading_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
            	'name' 		=> __( 'Container Border Bottom', 'wp-predictive-search' ),
                'id' 		=> 'sidebar_popup_heading_border',
				'type' 		=> 'border_styles',
                'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#c2c2c2' ),
           	),
			array(
				'name' 		=> __( 'Container Background', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_heading_bg_color',
				'type' 		=> 'color',
				'default'	=> '#f2f2f2',
			),

			array(
            	'name' 		=> __( 'Results Dropdown Items', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_items_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Item Padding', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_popup_item_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '6' ),
	 								array(  'id' 		=> 'sidebar_popup_item_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
            	'name' 		=> __( 'Item Border Bottom', 'wp-predictive-search' ),
                'id' 		=> 'sidebar_popup_item_border',
				'type' 		=> 'border_styles',
                'default'	=> array( 'width' => '0px', 'style' => 'solid', 'color' => '#c2c2c2' ),
           	),
           	array(
            	'name' 		=> __( 'Item Border Bottom Hover Colour', 'wp-predictive-search' ),
                'id' 		=> 'sidebar_popup_item_border_hover_color',
				'type' 		=> 'color',
				'default'	=> '#6d84b4',
           	),
			array(
				'name' 		=> __( 'Item Background Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_bg_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
			array(
				'name' 		=> __( 'Item Background Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_bg_hover_color',
				'type' 		=> 'color',
				'default'	=> '#6d84b4',
			),
			array(
				'name' 		=> __( 'Item Image Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_popup_item_image_size',
				'type' 		=> 'slider',
				'default'	=> 64,
				'min'		=> 32,
				'max'		=> 96,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'Item Name Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_name_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#3b5998' ),
			),
			array(
				'name' 		=> __( 'Item Name Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_name_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
			array(
				'name' 		=> __( 'Item Description Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_desc_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Item Description Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_desc_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
			array(
				'name' 		=> __( 'Item Category Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_category_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'normal', 'color' => '#000000' ),
			),
			array(
				'name' 		=> __( 'Item Category Link Hover Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_category_link_hover_color',
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),
			array(
				'name' 		=> __( "Item 'Category' Colour", 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_category_color',
				'desc'		=> __( 'Category: word text colour', 'wp-predictive-search' ),
				'type' 		=> 'color',
				'default'	=> '#000000',
			),
			array(
				'name' 		=> __( "Item 'Category' Hover Colour", 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_item_category_hover_color',
				'desc'		=> __( 'Category: word text colour on hover', 'wp-predictive-search' ),
				'type' 		=> 'color',
				'default'	=> '#ffffff',
			),

			array(
            	'name' 		=> __( 'Results Dropdown Footer', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_popup_result_footer_box',
                'is_box'	=> true,
           	),
           	array(
				'name' 		=> __( 'Footer Padding', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_footer_padding',
				'type' 		=> 'array_textfields',
				'ids'		=> array(
	 								array(  'id' 		=> 'sidebar_popup_footer_padding_tb',
	 										'name' 		=> __( 'Top/Bottom', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '3' ),
	 								array(  'id' 		=> 'sidebar_popup_footer_padding_lr',
	 										'name' 		=> __( 'Left/Right', 'wp-predictive-search' ),
	 										'class' 	=> '',
	 										'css'		=> 'width:40px;',
	 										'default'	=> '10' ),
	 							)
			),
			array(
				'name' 		=> __( 'Footer Background Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_footer_bg_color',
				'type' 		=> 'color',
				'default'	=> '#f2f2f2',
			),
           	array(
				'name' 		=> __( 'See More Text', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_seemore_text',
				'type' 		=> 'text',
				'default'	=> __( "See more search results for '%s' in:", 'wp-predictive-search' ),
			),
			array(
				'name' 		=> __( 'See More Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_seemore_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '10px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#999999' ),
			),
			array(
				'name' 		=> __( 'More Link Font', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_more_link_font',
				'type' 		=> 'typography',
				'default'	=> array( 'size' => '12px', 'line_height' => '1.4em', 'face' => 'Arial, sans-serif', 'style' => 'bold', 'color' => '#3b5998' ),
			),
			array(
				'name' 		=> __( 'More Icon Size', 'wp-predictive-search' ),
				'desc' 		=> "px",
				'id' 		=> 'sidebar_popup_more_icon_size',
				'type' 		=> 'slider',
				'default'	=> 12,
				'min'		=> 8,
				'max'		=> 24,
				'increment'	=> 1,
			),
			array(
				'name' 		=> __( 'More Icon Colour', 'wp-predictive-search' ),
				'id' 		=> 'sidebar_popup_more_icon_color',
				'type' 		=> 'color',
				'default'	=> '#3b5998'
			),

        ));
	}

	public function include_script() {
	?>
<script>
(function($) {

	$(document).ready(function() {

		if ( $("input.search_icon_mobile:checked").val() != 'yes') {
			$('.search_icon_mobile_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.search_icon_mobile', function( event, value, status ) {
			$('.search_icon_mobile_container').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".search_icon_mobile_container").slideDown();
			} else {
				$(".search_icon_mobile_container").slideUp();
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
 * wp_predictive_search_sidebar_template_settings_form()
 * Define the callback function to show subtab content
 */
function wp_predictive_search_sidebar_template_settings_form() {
	global $wp_predictive_search_sidebar_template_settings_panel;
	$wp_predictive_search_sidebar_template_settings_panel->settings_form();
}

}
