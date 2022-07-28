<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get templates passing attributes and including the file.
 *
 * @access public
 * @param string $template_name
 * @param array $args (default: array())
 * @return void
 */
function wpps_get_template( $template_name, $args = array() ) {
	if ( $args && is_array( $args ) ) {
		extract( $args );
	}

	$template_file_path = wpps_get_template_file_path( $template_name );

	if ( ! file_exists( $template_file_path ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $template_file_path ), '1.0.0' );
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin
	$template_file_path = apply_filters( 'wpps_get_template', $template_file_path, $template_name, $args );

	do_action( 'wpps_before_template_part', $template_name, $template_file_path, $args );

	include( $template_file_path );

	do_action( 'wpps_after_template_part', $template_name, $template_file_path, $args );
}

/**
 * wpps_get_template_file_path( $file )
 *
 * This is the load order:
 *
 *		yourtheme					/	ps	/	$file
 *		yourtheme					/	$file
 *		WPPS_TEMPLATE_PATH			/	$file
 *
 * @access public
 * @param $file string filename
 * @return PATH to the file
 */
function wpps_get_template_file_path( $file = '' ) {
	// If we're not looking for a file, do not proceed
	if ( empty( $file ) )
		return;

	// Look for file in stylesheet
	if ( file_exists( get_stylesheet_directory() . '/ps/' . $file ) ) {
		$file_path = get_stylesheet_directory() . '/ps/' . $file;

	// Look for file in stylesheet
	} elseif ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
		$file_path = get_stylesheet_directory() . '/' . $file;

	// Look for file in template
	} elseif ( file_exists( get_template_directory() . '/ps/' . $file ) ) {
		$file_path = get_template_directory() . '/ps/' . $file;

	// Look for file in template
	} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
		$file_path = get_template_directory() . '/' . $file;

	// Get default template
	} else {
		$file_path = WPPS_TEMPLATE_PATH . '/' . $file;
	}

	// Return filtered result
	return apply_filters( 'wpps_get_template_file_path', $file_path, $file );
}

/**
 * wpps_search_form()
 *
 * @return void
 */
function wpps_search_form( $ps_id = '', $template = 'sidebar', $args = array(), $echo = false ) {

	$ps_id = str_replace( 'wp_predictive_search-', '', $ps_id );
	if ( empty( $ps_id ) ) {
		$ps_id = rand( 100, 10000 );
	}

	global $wpps_cache;
	if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
		$args['show_catdropdown'] = 0;
	}

	ob_start();

	// Custom Style for Individual Predictive Search Widget
	$custom_style = '';
	if ( isset( $args['show_image'] ) && 0 == $args['show_image'] ) {
		$custom_style .= '
.ac_results_' . $ps_id . ' .rs_avatar {
	display: none !important;
}
.predictive_results.ac_results_' . $ps_id . ' .rs_content_popup {
	width: 100% !important;
}';
	}
	if ( isset( $args['show_desc'] ) && 0 == $args['show_desc'] ) {
		$custom_style .= '
.ac_results_' . $ps_id . ' .rs_description {
	display: none !important;
}';
	}

	if ( '' != trim( $custom_style ) ) {
		echo '<style>' . esc_html( $custom_style ) . '</style>';
	}

	if ( 'header' == $template ) {
		wpps_search_form_header_tpl( $ps_id, $args );
	} else {
		wpps_search_form_sidebar_tpl( $ps_id, $args );
	}

	$search_form = ob_get_clean();

	if ( $echo ) {
		echo $search_form;
	} else {
		return $search_form;
	}
}

/**
 * wpps_search_form_sidebar_tpl()
 *
 * @return void
 */
function wpps_search_form_sidebar_tpl( $ps_id, $args = array() ) {
	global $wp_predictive_search_sidebar_template_settings;

	if ( ! is_array( $args ) ) {
		$args = array();
	}

	$args['popup_wide'] = $wp_predictive_search_sidebar_template_settings['popup_wide'];
	$args['cat_align'] = $wp_predictive_search_sidebar_template_settings['sidebar_category_dropdown_align'];
	$args['cat_max_wide'] = $wp_predictive_search_sidebar_template_settings['sidebar_category_dropdown_max_wide'];
	$args['search_icon_mobile'] = isset( $wp_predictive_search_sidebar_template_settings['search_icon_mobile'] ) ? $wp_predictive_search_sidebar_template_settings['search_icon_mobile'] : 'no';

	wpps_get_template( 'search-bar/predictive-search-form-sidebar.php',
		apply_filters( 'wpps_search_form_sidebar_tpl_args', array(
			'ps_id'              => $ps_id,
			'ps_widget_template' => 'sidebar',
			'ps_args'            => $args
		) )
	);
}

/**
 * wpps_search_form_header_tpl()
 *
 * @return void
 */
function wpps_search_form_header_tpl( $ps_id, $args = array() ) {
	global $wp_predictive_search_header_template_settings;

	if ( ! is_array( $args ) ) {
		$args = array();
	}

	$args['popup_wide'] = $wp_predictive_search_header_template_settings['popup_wide'];
	$args['cat_align'] = $wp_predictive_search_header_template_settings['header_category_dropdown_align'];
	$args['cat_max_wide'] = $wp_predictive_search_header_template_settings['header_category_dropdown_max_wide'];
	$args['search_icon_mobile'] = isset( $wp_predictive_search_header_template_settings['search_icon_mobile'] ) ? $wp_predictive_search_header_template_settings['search_icon_mobile'] : 'yes';

	wpps_get_template( 'search-bar/predictive-search-form-header.php',
		apply_filters( 'wpps_search_form_header_tpl_args', array(
			'ps_id'              => $ps_id,
			'ps_widget_template' => 'header',
			'ps_args'            => $args
		) )
	);
}

/**
 * wpps_get_popup_item_tpl()
 *
 * @return void
 */
function wpps_get_popup_item_tpl() {

	wpps_get_template( 'popup/item.php',
		apply_filters( 'wpps_popup_item_tpl_args', array() )
	);
}

/**
 * wpps_get_popup_footer_sidebar_tpl()
 *
 * @return void
 */
function wpps_get_popup_footer_sidebar_tpl() {
	global $wp_predictive_search_sidebar_template_settings;

	wpps_get_template( 'popup/footer-sidebar.php',
		apply_filters( 'wpps_popup_footer_sidebar_tpl_args', array(
			'popup_seemore_text' => $wp_predictive_search_sidebar_template_settings['sidebar_popup_seemore_text']
		) )
	);
}

/**
 * wpps_get_popup_footer_header_tpl()
 *
 * @return void
 */
function wpps_get_popup_footer_header_tpl() {
	global $wp_predictive_search_header_template_settings;

	wpps_get_template( 'popup/footer-header.php',
		apply_filters( 'wpps_popup_footer_header_tpl_args', array(
			'popup_seemore_text' => $wp_predictive_search_header_template_settings['header_popup_seemore_text']
		) )
	);
}

/**
 * wpps_get_results_item_tpl()
 *
 * @return void
 */
function wpps_get_results_item_tpl() {
	wpps_get_template( 'results-page/item.php',
		apply_filters( 'wpps_results_item_tpl_args', array() )
	);
}

/**
 * wpps_get_results_header_tpl()
 *
 * @return void
 */
function wpps_get_results_header_tpl( $args = array() ) {
	if ( ! is_array( $args ) ) {
		$args = array();
	}

	wpps_get_template( 'results-page/header.php',
		apply_filters( 'wpps_results_header_tpl_args', $args )
	);
}

/**
 * wpps_get_results_footer_tpl()
 *
 * @return void
 */
function wpps_get_results_footer_tpl() {
	wpps_get_template( 'results-page/footer.php',
		apply_filters( 'wpps_results_footer_tpl_args', array() )
	);
}

/**
 * wpps_error_modal_tpl()
 *
 * @return void
 */
function wpps_error_modal_tpl( $args = array() ) {

	wpps_get_template( 'admin/error-log-modal.php',
		apply_filters( 'wpps_error_modal_tpl_args', $args )
	);
}

function wpps_taxonomies_dropdown() {
	$taxonomies = array(
		'category' => __( 'Post Category', 'wp-predictive-search' )
	);

	$taxonomies = apply_filters( 'wpps_taxonomies_dropdown', $taxonomies );

	return $taxonomies;
}

function wpps_get_categories( $taxonomy = 'category' ) {
	global $wpps_cache;
	$categories_list       = false;
	$append_transient_name = '';

	if ( empty( $taxonomy ) ) {
		$taxonomy = 'category';
	}

	if ( $wpps_cache->enable_cat_cache() ) {
		if ( class_exists('SitePress') ) {
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			$append_transient_name = $current_lang;
		}

		if ( 'category' === $taxonomy ) {
			$categories_list = $wpps_cache->get_categories_dropdown_cache( $append_transient_name );
		} else {
			$categories_list = apply_filters( 'wpps_get_categories', $categories_list, $taxonomy, $append_transient_name );
		}

		if ( false === $categories_list ) {
			$language = trim( $append_transient_name );
			if ( '' != $language ) {
				$language = '_' . $language;
			}
			update_option( 'predictive_search_have_cat_cache' . $language, 'no' );
		}
	}

	return $categories_list;
}

function wpps_get_block_card_item() {
	global $wpps_blocks;
	return $wpps_blocks->get_block_card_item();
}
