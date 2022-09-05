<?php

use A3Rev\WPPredictiveSearch;

/**
 * Register Activation Hook
 */
function wp_predictive_search_install(){
	global $wpdb;
	$wpps_search_page_id = WPPredictiveSearch\Functions::create_page( _x('wordpress-search', 'page_slug', 'wp-predictive-search' ), 'wpps_search_page_id', __('WP Predictive Search', 'wp-predictive-search' ), '[wpps_search]' );
	WPPredictiveSearch\Functions::auto_create_page_for_wpml( $wpps_search_page_id, _x('wordpress-search', 'page_slug', 'wp-predictive-search' ), __('WP Predictive Search', 'wp-predictive-search' ), '[wpps_search]' );

	global $wp_predictive_search;
	$wp_predictive_search->install_databases();

	delete_option('wpps_search_lite_clean_on_deletion');

	update_option('wp_predictive_search_version', WPPS_VERSION );

	delete_metadata( 'user', 0, $GLOBALS[WPPS_PREFIX.'admin_init']->plugin_name . '-' . 'plugin_framework_global_box' . '-' . 'opened', '', true );

	delete_transient( $GLOBALS[WPPS_PREFIX.'admin_init']->version_transient );
	flush_rewrite_rules();

	update_option( 'wp_predictive_search_had_sync_posts_data', 0 );

	update_option('wp_predictive_search_just_installed', true);

	// registered event for auto preload data cache
	$enable_cache_value = get_option( 'predictive_search_category_cache', 'yes' );
	if ( 'yes' == $enable_cache_value ) {

		// Automatic preload category cache
		global $wpps_cache;
		$wpps_cache->preload_category_dropdown_cache();

		if ( ! wp_next_scheduled( 'wp_predictive_search_auto_preload_cache_event' ) ) {
			wp_schedule_event( time() + 120, 'hourly', 'wp_predictive_search_auto_preload_cache_event' );
		}
	}
}

function wp_predictive_search_packages_init() {

	global $wp_predictive_search;
	$posttypes_support = $wp_predictive_search->posttypes_support();

	/* For Bulk Quick Editions */
	if ( ! empty( $posttypes_support ) ) {
		foreach ( $posttypes_support as $posttype ) {
			add_filter( 'manage_'.$posttype['name'].'_posts_columns', array( '\A3Rev\WPPredictiveSearch\Bulk_Quick_Editions', 'column_heading' ), 11 );
			add_action( 'manage_'.$posttype['name'].'_posts_custom_column', array( '\A3Rev\WPPredictiveSearch\Bulk_Quick_Editions', 'column_content' ), 10, 2 );
		}
	}

	add_action( 'bulk_edit_custom_box',  array( '\A3Rev\WPPredictiveSearch\Bulk_Quick_Editions', 'admin_bulk_edit' ), 10, 2);
	add_action( 'save_post', array( '\A3Rev\WPPredictiveSearch\Bulk_Quick_Editions', 'admin_bulk_edit_save' ), 10, 2 );

	add_action( 'quick_edit_custom_box',  array( '\A3Rev\WPPredictiveSearch\Bulk_Quick_Editions', 'quick_edit' ), 10, 2 );
	add_action( 'admin_enqueue_scripts', array( '\A3Rev\WPPredictiveSearch\Bulk_Quick_Editions', 'quick_edit_scripts' ), 10 );
	add_action( 'save_post', array( '\A3Rev\WPPredictiveSearch\Bulk_Quick_Editions', 'quick_edit_save' ), 10, 2 );


}
wp_predictive_search_packages_init();

function woops_init() {
	if ( get_option('wp_predictive_search_just_installed') ) {
		delete_option('wp_predictive_search_just_installed');

		// Set Settings Default from Admin Init
		$GLOBALS[WPPS_PREFIX.'admin_init']->set_default_settings();

		// Build sass
		$GLOBALS[WPPS_PREFIX.'less']->plugin_build_sass();

		update_option( 'wp_predictive_search_just_confirm', 1 );
	}

	wp_predictive_search_plugin_textdomain();
}

// Add language
add_action('init', 'woops_init');

// Add custom style to dashboard
add_action( 'admin_enqueue_scripts', array( '\A3Rev\WPPredictiveSearch\Hook_Filter', 'a3_wp_admin' ) );

add_action( 'init', array( '\A3Rev\WPPredictiveSearch\Hook_Filter', 'plugins_init' ) );
add_action( 'wp_loaded', array( '\A3Rev\WPPredictiveSearch\Hook_Filter', 'wpml_search_page_id' ), 0 );

// Add text on right of Visit the plugin on Plugin manager page
add_filter( 'plugin_row_meta', array('\A3Rev\WPPredictiveSearch\Hook_Filter', 'plugin_extra_links'), 10, 2 );

// Add extra link on left of Deactivate link on Plugin manager page
add_action('plugin_action_links_' . WPPS_NAME, array( '\A3Rev\WPPredictiveSearch\Hook_Filter', 'settings_plugin_links' ) );

// Add admin sidebar menu css
add_action( 'admin_enqueue_scripts', array( '\A3Rev\WPPredictiveSearch\Hook_Filter', 'admin_sidebar_menu_css' ) );

function register_widget_woops_predictive_search() {
	register_widget('\A3Rev\WPPredictiveSearch\Widgets');
}

// Need to call Admin Init to show Admin UI
$GLOBALS[WPPS_PREFIX.'admin_init']->init();

// Add upgrade notice to Dashboard pages
add_filter( $GLOBALS[WPPS_PREFIX.'admin_init']->plugin_name . '_plugin_extension_boxes', array( '\A3Rev\WPPredictiveSearch\Hook_Filter', 'plugin_extension_box' ) );

// Custom Rewrite Rules
add_filter( 'query_vars', array( '\A3Rev\WPPredictiveSearch\Functions', 'add_query_vars' ) );
add_filter( 'rewrite_rules_array', array( '\A3Rev\WPPredictiveSearch\Functions', 'add_rewrite_rules' ) );

// Registry widget
add_action('widgets_init', 'register_widget_woops_predictive_search');

// AJAX hide yellow message dontshow
add_action('wp_ajax_wpps_yellow_message_dontshow', array('\A3Rev\WPPredictiveSearch\Hook_Filter', 'yellow_message_dontshow') );
add_action('wp_ajax_nopriv_wpps_yellow_message_dontshow', array('\A3Rev\WPPredictiveSearch\Hook_Filter', 'yellow_message_dontshow') );

// AJAX hide yellow message dismiss
add_action('wp_ajax_wpps_yellow_message_dismiss', array('\A3Rev\WPPredictiveSearch\Hook_Filter', 'yellow_message_dismiss') );
add_action('wp_ajax_nopriv_wpps_yellow_message_dismiss', array('\A3Rev\WPPredictiveSearch\Hook_Filter', 'yellow_message_dismiss') );

// Add shortcode [wpps_search]
add_shortcode('wpps_search', array('\A3Rev\WPPredictiveSearch\Shortcodes', 'parse_shortcode_search_result'));

// Add shortcode [wpps_search_widget]
add_shortcode('wpps_search_widget', array('\A3Rev\WPPredictiveSearch\Shortcodes', 'parse_shortcode_search_widget'));

// Add Predictive Search Meta Box to all post type
add_action( 'add_meta_boxes', array( '\A3Rev\WPPredictiveSearch\MetaBox', 'create_custombox'), 9 );

// Save Predictive Search Meta Box to all post type
if(in_array(basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php'))){
	add_action( 'save_post', array( '\A3Rev\WPPredictiveSearch\MetaBox', 'save_custombox' ), 11 );
}

// Add search widget icon to Page Editor
if (in_array (basename($_SERVER['PHP_SELF']), array('post.php', 'page.php', 'page-new.php', 'post-new.php') ) ) {
	add_action('media_buttons_context', array('\A3Rev\WPPredictiveSearch\Shortcodes', 'add_search_widget_icon') );
	add_action('admin_footer', array('\A3Rev\WPPredictiveSearch\Shortcodes', 'add_search_widget_mce_popup'));
}

function wp_predictive_search_widget() {
	global $wp_predictive_search;
	$posttypes_support = $wp_predictive_search->posttypes_support();
	$taxonomies_support = $wp_predictive_search->taxonomies_support();

	$widget_template  = get_option('wpps_search_widget_template', 'sidebar' );
	$show_catdropdown = get_option('wpps_search_show_catdropdown', 'yes' );
	$in_taxonomy      = get_option('wpps_search_in_taxonomy', 'category' );
	$show_image       = get_option('wpps_search_show_image', 'yes' );
	$show_desc        = get_option('wpps_search_show_desc', 'yes' );
	$text_lenght      = get_option('wpps_search_character_max', 100 );
	$show_in_cat      = get_option('wpps_search_show_in_cat', 'yes' );

	if ( ! empty( $posttypes_support ) ) {
		foreach ( $posttypes_support as $posttype ) {
			${$posttype['name'].'_items'}      = get_option('wpps_search_'.$posttype['name'].'_items', 0 );
		}
	}

	if ( ! empty( $taxonomies_support ) ) {
		foreach ( $taxonomies_support as $taxonomy ) {
			${$taxonomy['name'].'_items'}      = get_option('wpps_search_'.$taxonomy['name'].'_items', 0 );
		}
	}

	if ( class_exists('SitePress') ) {
		$current_lang = ICL_LANGUAGE_CODE;
		$search_box_texts = get_option('wpps_search_box_text', array() );
		if ( is_array($search_box_texts) && isset($search_box_texts[$current_lang]) ) $search_box_text = esc_attr( stripslashes( trim( $search_box_texts[$current_lang] ) ) );
		else $search_box_text = '';
	} else {
		$search_box_text = get_option('wpps_search_box_text', '' );
		if ( is_array($search_box_text) ) $search_box_text = '';
	}

	$ps_id = rand(100, 10000);

	if ( 'yes' == $show_image ) $show_image = 1;
	else $show_image = 0;

	if ( 'yes' == $show_desc ) $show_desc = 1;
	else $show_desc = 0;

	if ( 'yes' == $show_in_cat ) $show_in_cat = 1;
	else $show_in_cat = 0;

	if ( 'yes' == $show_catdropdown ) $show_catdropdown = 1;
	else $show_catdropdown = 0;

	global $wpps_cache;
	if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
		$show_catdropdown = 0;
	}

	$row                  = 0;
	$search_list          = array();
	$number_items         = array();
	$items_search_default = $wp_predictive_search->get_items_search();

	foreach ($items_search_default as $key => $data) {
		if ( isset(${$key.'_items'}) && ${$key.'_items'} > 0 ) {
			$number_items[$key] = ${$key.'_items'};
			$row += ${$key.'_items'};
			$row++;
			$search_list[] = $key;
		} elseif ( $number = apply_filters( 'wpps_customize_search_number', $data['number'], $key, $data ) > 0 ) {
			$number_items[$key] = $number;
			$row += $number;
			$row++;
			$search_list[] = $key;
		}
	}

	$search_in = json_encode($number_items);

	$ps_args = array(
		'search_box_text'  => $search_box_text,
		'row'              => $row,
		'text_lenght'      => $text_lenght,
		'show_catdropdown' => $show_catdropdown,
		'in_taxonomy'	   => $in_taxonomy,
		'widget_template'  => $widget_template,
		'show_image'       => $show_image,
		'show_desc'        => $show_desc,
		'show_in_cat'      => $show_in_cat,
		'search_in'        => $search_in,
		'search_list'      => $search_list,
	);
	$search_form = wpps_search_form( $ps_id, $widget_template, $ps_args );

	return $search_form;
}

// Check upgrade functions
add_action( 'init', 'wp_predictive_search_upgrade_plugin' );
function wp_predictive_search_upgrade_plugin() {

	update_option('wp_predictive_search_version', WPPS_VERSION );
}

function wpps_blocks_unavailable() {

		$had_dismiss = get_option( 'wpps_blocks_unavailable_dismiss' );
		if ( ! empty( $had_dismiss ) ) {
			return;
		}
?>
		<div class="below-h2 a3-notification warning upgrade dismissible wpps_blocks_unavailable" style="display:block !important;">
			<div>
    			<?php echo __( 'WordPress Predictive Search block is not available with the WordPress version on this site. Please upgrade to WordPress version 5.5 or later to access and use Predictive Search Gutenberg block and Block Pattern.' , 'wp-predictive-search' ); ?>
    		</div>
			<div class="a3-notification-dismiss">
				<a href="javascript:void(0);" class="wpps_blocks_unavailable_bt" aria-label="Close"><?php echo __( 'Dismiss', 'wp-predictive-search' ); ?></a>
			</div>
			<script>
			(function($) {
			$(document).ready(function() {
				
				$(document).on( "click", ".wpps_blocks_unavailable_bt", function(){
					$(".wpps_blocks_unavailable").slideUp();
					var data = {
							action: 		"wpps_yellow_message_dontshow",
							option_name: 	"wpps_blocks_unavailable_dismiss",
							security: 		"<?php echo wp_create_nonce("wpps_yellow_message_dontshow"); ?>"
						};
					$.post( "<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>", data);
				});
			});
			})(jQuery);
			</script>
		</div>
<?php
}

function wpps_convert_key_to_label( $key ) {
	return ucwords( strtolower( str_replace( array( '_', '-' ), ' ', $key ) ) );
}

function wpps_esc_sql_array_s( $array_data ) {
	if ( empty( $array_data ) ) return $array_data;

	if ( ! is_array( $array_data ) ) $array_data = array( $array_data );

	$array_data = array_map( function( $value ) {
		global $wpdb;
		return $wpdb->prepare( '%s', $value );
	}, $array_data );

	return $array_data;
}

function wpps_esc_sql_array_d( $array_data ) {
	if ( empty( $array_data ) ) return $array_data;

	if ( ! is_array( $array_data ) ) $array_data = array( $array_data );

	$array_data = array_map( function( $value ) {
		global $wpdb;
		return $wpdb->prepare( '%d', $value );
	}, $array_data );

	return $array_data;
}

function wpps_ict_t_e( $name, $string ) {
	global $wpps_wpml;
	$string = ( function_exists('icl_t') ? icl_t( $wpps_wpml->plugin_wpml_name, $name, $string ) : $string );
	
	echo wp_kses_post( $string );
}

function wpps_ict_t__( $name, $string ) {
	global $wpps_wpml;
	$string = ( function_exists('icl_t') ? icl_t( $wpps_wpml->plugin_wpml_name, $name, $string ) : $string );
	
	return $string;
}

function wp_predictive_search_check_pin() {
	return true;
}
