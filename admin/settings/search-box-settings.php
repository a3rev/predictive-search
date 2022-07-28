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

class Search_Box extends FrameWork\Admin_UI
{
	
	/**
	 * @var string
	 */
	private $parent_tab = 'search-box-settings';
	
	/**
	 * @var array
	 */
	private $subtab_data;
	
	/**
	 * @var string
	 * You must change to correct option name that you are working
	 */
	public $option_name = 'wp_predictive_search_input_box_settings';
	
	/**
	 * @var string
	 * You must change to correct form key that you are working
	 */
	public $form_key = 'wp_predictive_search_input_box_settings';
	
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
				'success_message'	=> __( 'Search Box Settings successfully saved.', 'wp-predictive-search' ),
				'error_message'		=> __( 'Error: Search Box Settings can not save.', 'wp-predictive-search' ),
				'reset_message'		=> __( 'Search Box Settings successfully reseted.', 'wp-predictive-search' ),
			);
		
		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'include_script' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_end', array( $this, 'error_logs_container' ) );

		add_action( $this->plugin_name . '_set_default_settings' , array( $this, 'set_default_settings' ) );

		add_action( $this->plugin_name . '_settings_' . 'predictive_search_searchbox_text' . '_start', array( $this, 'predictive_search_searchbox_text' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_before_settings_save', array( $this, 'before_save_settings' ) );

		add_action( $this->plugin_name . '-' . $this->form_key . '_settings_init' , array( $this, 'after_save_settings' ) );

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
	/* before_save_settings()
	/*
	/*-----------------------------------------------------------------------------------*/
	public function before_save_settings() {
		$old_enable_cache_value = get_option( 'predictive_search_category_cache', 'yes' );
		$new_enable_cache_value = 'yes';
		if ( ! isset( $_POST['predictive_search_category_cache'] ) || 'yes' != sanitize_key( wp_unslash( $_POST['predictive_search_category_cache'] ) ) ) {
			$new_enable_cache_value = 'no';
		}

		if ( 'no' != $new_enable_cache_value && $old_enable_cache_value != $new_enable_cache_value ) {

			/*
			* registered event for auto preload data cache
			* if have change on enable cache to 'yes' and old data is different with new data
			*/
			wp_clear_scheduled_hook( 'wp_predictive_search_auto_preload_cache_event' );
			wp_schedule_event( time() + 120, 'hourly', 'wp_predictive_search_auto_preload_cache_event' );

		} elseif ( 'no' == $new_enable_cache_value && $old_enable_cache_value != $new_enable_cache_value ) {

			/*
			* deregistered event for auto preload data cache
			* if have change on enable cache to 'no' and old data is different with new data
			*/
			wp_clear_scheduled_hook( 'wp_predictive_search_auto_preload_cache_event' );
		}
	}

	/*-----------------------------------------------------------------------------------*/
	/* after_save_settings()
	/* Process when clean on deletion option is un selected */
	/*-----------------------------------------------------------------------------------*/
	public function after_save_settings() {

		if ( isset( $_REQUEST['wpps_search_box_text']) ) {
			update_option('wpps_search_box_text', is_array( $_REQUEST['wpps_search_box_text'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_REQUEST['wpps_search_box_text'] ) ) : sanitize_text_field( wp_unslash( $_REQUEST['wpps_search_box_text'] ) ) );
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
			'name'				=> 'search-box-settings',
			'label'				=> __( 'Search Box', 'wp-predictive-search' ),
			'callback_function'	=> 'wp_predictive_search_input_box_settings_form',
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
		global $wpps_cache;

		$cat_cache_time = $wpps_cache->cat_cache_built_time();
		if ( false !== $cat_cache_time ) {
			$cat_cache_time = date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), $cat_cache_time );
		} else {
			$cat_cache_time = '';
		}

		global $ps_enable_cat_cache;
		if ( is_admin() ) {
			if ( isset( $_POST['wpps_search_category_cache_timeout'] ) )  {
				if ( isset( $_POST['predictive_search_category_cache'] ) )  {
					$ps_enable_cat_cache = true;
				} else {
					$ps_enable_cat_cache = false;
				}
			} else {
				$ps_enable_cat_cache = $wpps_cache->enable_cat_cache();
			}
		}

		$effects = $this->get_effect_list();

  		// Define settings			
     	$this->form_fields = apply_filters( $this->option_name . '_settings_fields', array(

     		array(
            	'name' 		=> __( 'Global Search Box Text', 'wp-predictive-search' ),
                'type' 		=> 'heading',
				'id'		=> 'predictive_search_searchbox_text',
				'is_box'	=> true,
           	),

     		array(
            	'name' 		=> __( 'Dropdown Results Animation', 'wp-predictive-search' ),
            	'desc'		=> __( 'Add CSS animation to the loading of Results in the Search box dropdown. IMPORTANT! On settings tab turn ON Results NO-CACHE and when you check the animation on front end clear your browser cache so you can see the new animation effect that you have set.', 'wp-predictive-search' ),
                'type' 		=> 'heading',
                'id'		=> 'predictive_search_animiation_box',
                'is_box'	=> true,
           	),
			array(
				'name' 		=> __( 'Results Animation', 'wp-predictive-search' ),
				'class'		=> 'allow_result_effect',
				'id' 		=> 'allow_result_effect',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
			),

			array(
                'type' 		=> 'heading',
				'class'		=> 'allow_result_effect_container',
           	),
           	array(
				'name' 		=> __( "Animation", 'wp-predictive-search' ),
				'id' 		=> 'show_effect',
				'type' 		=> 'select',
				'default'	=> 'fadeInUpBig',
				'options'	=> $effects,
			),

			array(
            	'name' 		=> __( 'Search In Category Feature', 'wp-predictive-search' ),
            	'id'		=> 'predictive_search_category_cache_box',
                'type' 		=> 'heading',
				'is_box'	=> true,
				'is_active'	=> ( $ps_enable_cat_cache && $wpps_cache->cat_cache_is_built() ) ? true : false,
           	),
           	array(
				'name' 		=> __( 'Search In Category', 'wp-predictive-search' ),
				'class'		=> 'predictive_search_category_cache',
				'id' 		=> 'predictive_search_category_cache',
				'type' 		=> 'onoff_checkbox',
				'default'	=> 'yes',
				'checked_value'		=> 'yes',
				'unchecked_value'	=> 'no',
				'checked_label'		=> __( 'ON', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'OFF', 'wp-predictive-search' ),
				'separate_option'   => true,
			),
			array(
            	'name' 		=> '',
            	'class'		=> 'predictive_search_category_cache_container',
            	'id'		=> 'predictive_search_category_cache_container',
                'type' 		=> 'heading',
           	),
           	array(
				'name'             => __( 'Post Category Cache', 'wp-predictive-search' ),
				'id'               => 'wpps_search_category_cache_rebuid',
				'type'             => 'ajax_submit',
				'submit_data' => array(
					'ajax_url'  => admin_url( 'admin-ajax.php', 'relative' ),
					'ajax_type' => 'POST',
					'data'      => array(
						'action'   => 'wp_predictive_search_rebuild_cache_category',
					),
				),
				'separate_option'   => true,
				'desc'              => __( 'Category Cache enables loading of your full post category tree on the search box with just 1 query which is a massive saving on server resources used.', 'wp-predictive-search' ),
				'custom_attributes' => array( 'data-cache-is-built' => ( $ps_enable_cat_cache && $wpps_cache->cat_cache_is_built() ) ? 'yes' : 'no' ),
				'button_name'       => ( $ps_enable_cat_cache && $wpps_cache->cat_cache_is_built() ) ? __( 'Refresh Cache', 'wp-predictive-search' ) : __( 'Build Cache', 'wp-predictive-search' ),
				'progressing_text'  => __( 'Buiding Cache...', 'wp-predictive-search' ),
				'completed_text'    => __( 'Completed', 'wp-predictive-search' ),
				'successed_text'    => sprintf( __( 'Cache Build %s', 'wp-predictive-search' ), $cat_cache_time ),
				'errors_text'       => '<span style="color:#f00;font-style:normal; display: block;">' .__( 'ERROR: Latest build post category cache has failed to complete', 'wp-predictive-search' ) . ' - <a data-toggle="modal" href="#build_category_cache-modal">'. __( 'View Error Log', 'wp-predictive-search' ) .'</a></span>',
			),
           	array(
				'name' 		=> __( 'Editing / Refresh Type', 'wp-predictive-search' ),
				'desc'		=> '</span><span class="description predictive_search_cat_cache_auto">' . __( 'Post Categories cache will be auto refreshed when add / edit / delete a Category', 'wp-predictive-search' ) . '</span>'
				. '<span class="description predictive_search_cat_cache_manual">' . __( 'You will be prompted to manually refresh Cache when add / edit / delete a Post Category', 'wp-predictive-search' ) . '</span><span>',
				'class'		=> 'predictive_search_rebuild_cat_cache',
				'id' 		=> 'predictive_search_rebuild_cat_cache',
				'type' 		=> 'switcher_checkbox',
				'default'	=> 'auto',
				'checked_value'		=> 'auto',
				'unchecked_value'	=> 'manual',
				'checked_label'		=> __( 'AUTO', 'wp-predictive-search' ),
				'unchecked_label' 	=> __( 'MANUAL', 'wp-predictive-search' ),
				'separate_option'   => true,
			),
           	array(
				'name' 		=> __( 'Auto Refresh Every', 'wp-predictive-search' ),
				'desc'		=> __( "If your Post Categories Don't change very often set this long example '12 months'", 'wp-predictive-search' ),
				'id' 		=> 'wpps_search_category_cache_timeout',
				'type' 		=> 'select',
				'default'	=> 72,
				'options'	=> array(
					'1'    => __( '1 hour', 'wp-predictive-search' ),
					'6'    => __( '6 hour', 'wp-predictive-search' ),
					'12'   => __( '12 hour', 'wp-predictive-search' ),
					'24'   => __( '1 day', 'wp-predictive-search' ),
					'72'   => __( '3 days', 'wp-predictive-search' ),
					'144'  => __( '6 days', 'wp-predictive-search' ),
					'288'  => __( '12 days', 'wp-predictive-search' ),
					'576'  => __( '24 days', 'wp-predictive-search' ),
					'720'  => __( '1 month', 'wp-predictive-search' ),
					'1440' => __( '2 months', 'wp-predictive-search' ),
					'2160' => __( '3 months', 'wp-predictive-search' ),
					'4320' => __( '6 months', 'wp-predictive-search' ),
					'8640' => __( '12 months', 'wp-predictive-search' ),
				),
				'separate_option'   => true,
			),
        ));
	}

	function predictive_search_searchbox_text() {
		if ( class_exists('SitePress') ) {
			$wpps_search_box_text = get_option('wpps_search_box_text', array() );
			if ( !is_array( $wpps_search_box_text) ) $wpps_search_box_text = array();

			global $sitepress;
			$active_languages = $sitepress->get_active_languages();
			if ( is_array($active_languages)  && count($active_languages) > 0 ) {
	?>
    		<tr valign="top" class="">
				<td class="forminp" colspan="2">
                <?php _e("Enter the translated search box text for each language for WPML to show it correct on the front end.", 'wp-predictive-search' ); ?>
				</td>
			</tr>
    <?php
				foreach ( $active_languages as $language ) {
	?>
    		<tr valign="top" class="">
				<th class="titledesc" scope="row"><label for="wpps_search_box_text_<?php echo $language['code']; ?>"><?php _e('Text to Show', 'wp-predictive-search' );?> (<?php echo $language['display_name']; ?>)</label></th>
				<td class="forminp">
                	<input type="text" class="" value="<?php if (isset($wpps_search_box_text[$language['code']]) ) esc_attr_e( stripslashes( $wpps_search_box_text[$language['code']] ) ); ?>" style="min-width:300px;" id="wpps_search_box_text_<?php echo $language['code']; ?>" name="wpps_search_box_text[<?php echo $language['code']; ?>]" /> <span class="description"><?php _e('&lt;empty&gt; shows nothing', 'wp-predictive-search' ); ?></span>
				</td>
			</tr>
    <?php
				}
			}

		} else {
			$wpps_search_box_text = get_option('wpps_search_box_text', '' );
			if ( is_array( $wpps_search_box_text) ) $wpps_search_box_text = '';
	?>
            <tr valign="top" class="">
				<th class="titledesc" scope="row"><label for="wpps_search_box_text"><?php _e('Text to Show', 'wp-predictive-search' );?></label></th>
				<td class="forminp">
                	<input type="text" class="" value="<?php esc_attr_e( stripslashes( $wpps_search_box_text ) ); ?>" style="min-width:300px;" id="wpps_search_box_text" name="wpps_search_box_text" /> <span class="description"><?php _e('&lt;empty&gt; shows nothing', 'wp-predictive-search' ); ?></span>
				</td>
			</tr>
    <?php }
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
		$build_category_cache_error_log = trim( $wpps_errors_log->get_error( 'build_category_cache' ) );

		echo '<div class="build_category_cache_error_container">';
		echo $wpps_errors_log->error_modal( 'build_category_cache', $build_category_cache_error_log );
		echo '</div>';
?>
<style type="text/css">
	.a3rev_panel_container .a3rev-ui-ajax_submit-control .a3rev-ui-ajax_submit-errors {
		<?php if ( '' != $build_category_cache_error_log ) { ?>
		display: inline;
		<?php } ?>
	}
</style>
<script>
(function($) {

	$(document).ready(function() {

		$(document).on( 'a3rev-ui-ajax_submit-errors', '#wp_predictive_search_input_box_settings_wpps_search_category_cache_rebuid', function( event, bt_ajax_submit, data ) {
			setTimeout( function(){
				$('.a3rev-ui-ajax_submit-errors').show();
			}, 3500 );

			$.ajax({
				type: 'POST',
				url: '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>',
				data: { action: 'wp_predictive_search_build_cache_error_category' },
				success: function ( response ) {
					$('.build_category_cache_error_container').html( response );
				}
			});
		});

	});

})(jQuery);
</script>
<?php
	}

	public function include_script() {
		global $wpps_cache;
		global $ps_enable_cat_cache;
	?>
	<style type="text/css">
		<?php if ( $ps_enable_cat_cache && $wpps_cache->cat_cache_is_built() ) {
		echo '#predictive_search_category_cache_container .a3rev-ui-ajax_submit-successed { display: inline; }';
		} ?>
	</style>
<script>
(function($) {

	$(document).ready(function() {

		if ( $("input.allow_result_effect:checked").val() != 'yes') {
			$('.allow_result_effect_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.allow_result_effect', function( event, value, status ) {
			$('.allow_result_effect_container').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".allow_result_effect_container").slideDown();
			} else {
				$(".allow_result_effect_container").slideUp();
			}
		});

		if ( $("input.predictive_search_category_cache:checked").val() != 'yes') {
			$('.predictive_search_category_cache_container').css( {'visibility': 'hidden', 'height' : '0px', 'overflow' : 'hidden', 'margin-bottom' : '0px' } );
		}

		if ( $("input.predictive_search_rebuild_cat_cache:checked").val() != 'auto') {
			$('.predictive_search_cat_cache_auto').hide();
		} else {
			$('.predictive_search_cat_cache_manual').hide();
		}

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.predictive_search_category_cache', function( event, value, status ) {
			$('.predictive_search_category_cache_container').attr('style','display:none;');
			if ( status == 'true' ) {
				$(".predictive_search_category_cache_container").slideDown();
			} else {
				$(".predictive_search_category_cache_container").slideUp();
			}
		});

		$(document).on( "a3rev-ui-onoff_checkbox-switch", '.predictive_search_rebuild_cat_cache', function( event, value, status ) {
			if ( status == 'true' ) {
				$(".predictive_search_cat_cache_auto").attr('style','display: inline;');
				$(".predictive_search_cat_cache_manual").attr('style','display: none;');
			} else {
				$(".predictive_search_cat_cache_auto").attr('style','display: none;');
				$(".predictive_search_cat_cache_manual").attr('style','display: inline;');
			}
		});

		$(document).on( 'a3rev-ui-ajax_submit-completed', '#wp_predictive_search_input_box_settings_wpps_search_category_cache_rebuid', function( event, object, data ) {
			$(this).siblings('.a3rev-ui-ajax_submit-errors').hide();
			$(this).siblings('.a3rev-ui-ajax_submit-successed').html( '<?php _e( 'Cache Build', 'wp-predictive-search' ); ?> ' + data.date);
			$(this).data('cache-is-built', 'yes');
			$(this).html("<?php echo __( 'Refresh Cache', 'wp-predictive-search' ); ?>");
			$('#predictive_search_category_cache_box').find('.a3rev_panel_box_handle').addClass('box_active');
		});

		$(document).on('click', 'input[name="bt_save_settings"]', function() {
			var cat_cache_enable = $('.predictive_search_category_cache:checked').val();
			if ( 'yes' == cat_cache_enable ) {
				var cache_is_built = $('#wp_predictive_search_input_box_settings_wpps_search_category_cache_rebuid').data('cache-is-built');

				if ( 'yes' != cache_is_built ) {
					$('#predictive_search_category_cache_box').find('.a3rev-ui-ajax_submit-successed').hide();
					$('#predictive_search_category_cache_box').find('.a3rev-ui-ajax_submit-errors').html('<?php echo __( 'Action failed. You have not created a post category cache. Search in Post Categories is ON but no Categories cache was detected. Please build your categories cache and Save Changes again', 'wp-predictive-search' ); ?>').slideDown();
					$('#predictive_search_category_cache_box').find('.a3rev_panel_box_handle h3').addClass('box_open');
					$('#predictive_search_category_cache_box_box_inside').addClass('box_open').slideDown();

					return false;
				}
			}

			return true;
		});

	});

})(jQuery);
</script>
    <?php
	}

	public function get_effect_list() {
		$effects = array(
			'Attention Seekers'  => array(
				'bounce'             => 'bounce',
				'flash'              => 'flash',
				'pulse'              => 'pulse',
				'rubberBand'         => 'rubberBand',
				'shake'              => 'shake',
				'swing'              => 'swing',
				'tada'               => 'tada',
				'wobble'             => 'wobble',
				'jello'              => 'jello',
			),
			'Bouncing Entrances' => array(
				'bounceIn'           => 'bounceIn',
				'bounceInDown'       => 'bounceInDown',
				'bounceInLeft'       => 'bounceInLeft',
				'bounceInRight'      => 'bounceInRight',
				'bounceInUp'         => 'bounceInUp',
			),
			'Fading Entrances'   => array(
				'fadeIn'             => 'fadeIn',
				'fadeInDown'         => 'fadeInDown',
				'fadeInDownBig'      => 'fadeInDownBig',
				'fadeInLeft'         => 'fadeInLeft',
				'fadeInLeftBig'      => 'fadeInLeftBig',
				'fadeInRight'        => 'fadeInRight',
				'fadeInRightBig'     => 'fadeInRightBig',
				'fadeInUp'           => 'fadeInUp',
				'fadeInUpBig'        => 'fadeInUpBig',
			),
			'Flippers'           => array(
				'flip'               => 'flip',
				'flipInX'            => 'flipInX',
				'flipInY'            => 'flipInY',
				'flipOutX'           => 'flipOutX',
				'flipOutY'           => 'flipOutY',
			),
			'Lightspeed'         => array(
				'lightSpeedIn'       => 'lightSpeedIn',
				'lightSpeedOut'      => 'lightSpeedOut',
			),
			'Rotating Entrances' => array(
				'rotateIn'           => 'rotateIn',
				'rotateInDownLeft'   => 'rotateInDownLeft',
				'rotateInDownRight'  => 'rotateInDownRight',
				'rotateInUpLeft'     => 'rotateInUpLeft',
				'rotateInUpRight'    => 'rotateInUpRight',
			),
			'Sliding Entrances'  => array(
				'slideInUp'          => 'slideInUp',
				'slideInDown'        => 'slideInDown',
				'slideInLeft'        => 'slideInLeft',
				'slideInRight'       => 'slideInRight',
			),
			'Zoom Entrances'     => array(
				'zoomIn'             => 'zoomIn',
				'zoomInDown'         => 'zoomInDown',
				'zoomInLeft'         => 'zoomInLeft',
				'zoomInRight'        => 'zoomInRight',
				'zoomInUp'           => 'zoomInUp',
			),
			'Specials'           => array(
				'hinge'              => 'hinge',
				'rollIn'             => 'rollIn',
				'rollOut'            => 'rollOut',
			),
		);

		return $effects;
	}
}

}

// global code
namespace {

/** 
 * wp_predictive_search_performance_settings_form()
 * Define the callback function to show subtab content
 */
function wp_predictive_search_input_box_settings_form() {
	global $wp_predictive_search_input_box_settings_panel;
	$wp_predictive_search_input_box_settings_panel->settings_form();
}

}
