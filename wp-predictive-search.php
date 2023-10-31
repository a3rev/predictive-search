<?php
/*
Plugin Name: Predictive Search
Description: Predictive Search - featuring "Smart Search" technology. Give your store customers the most awesome search experience on the web via widgets, shortcodes, Search results pages and the Predictive Search function.
Version: 1.2.5
Author: a3rev Software
Author URI: https://a3rev.com/
Requires at least: 5.6
Tested up to: 6.4
Text Domain: wp-predictive-search
Domain Path: /languages
License: GPLv2 or later

	Predictive Search.
	Copyright © 2011 A3 Revolution Software Development team

	A3 Revolution Software Development team
	admin@a3rev.com
	PO Box 1170
	Gympie 4570
	QLD Australia
*/
?>
<?php
define( 'WPPS_FILE_PATH', dirname(__FILE__) );
define( 'WPPS_DIR_NAME', basename(WPPS_FILE_PATH) );
define( 'WPPS_FOLDER', dirname(plugin_basename(__FILE__)) );
define( 'WPPS_NAME', plugin_basename(__FILE__) );
define( 'WPPS_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'WPPS_DIR', WP_PLUGIN_DIR . '/' . WPPS_FOLDER);
define( 'WPPS_JS_URL',  WPPS_URL . '/assets/js' );
define( 'WPPS_CSS_URL',  WPPS_URL . '/assets/css' );
define( 'WPPS_IMAGES_URL',  WPPS_URL . '/assets/images' );
define( 'WPPS_TEMPLATE_PATH', WPPS_FILE_PATH . '/templates' );

if(!defined("WPPS_DOCS_URI"))
    define("WPPS_DOCS_URI", "https://docs.a3rev.com/wordpress/predictive-search-for-wordpress/");

define( 'WPPS_KEY', 'wp_predictive_search' );
define( 'WPPS_PREFIX', 'wp_predictive_search_' );
define( 'WPPS_VERSION', '1.2.5' );
define( 'WPPS_G_FONTS', true );

function wpps_current_theme_is_fse_theme() {
	if ( function_exists( 'wp_is_block_theme' ) ) {
		return (bool) wp_is_block_theme();
	}
	if ( function_exists( 'gutenberg_is_fse_theme' ) ) {
		return (bool) gutenberg_is_fse_theme();
	}

	return false;
}

use \A3Rev\WPPredictiveSearch\FrameWork;

if ( version_compare( PHP_VERSION, '5.6.0', '>=' ) ) {
	require __DIR__ . '/vendor/autoload.php';

	// Predictive Search API
	global $wpps_legacy_api;
	$wpps_legacy_api = new \A3Rev\WPPredictiveSearch\Legacy_API();

	global $wpps_dashboard_ajax;
	$wpps_dashboard_ajax = new \A3Rev\WPPredictiveSearch\Dashboard_AJAX();

	// Predictive Cache
	global $wpps_cache;
	$wpps_cache = new \A3Rev\WPPredictiveSearch\Cache( 'category' );

	// Predictive WPML
	global $wpps_wpml;
	$wpps_wpml = new \A3Rev\WPPredictiveSearch\WPML_Functions();


	/**
	 * Plugin Framework init
	 */
	$GLOBALS[WPPS_PREFIX.'admin_interface'] = new FrameWork\Admin_Interface();

	do_action( 'wpps_before_include_admin_page' );

	global $wpps_admin_page;
	$wpps_admin_page = new FrameWork\Pages\Predictive_Search();

	do_action( 'wpps_after_include_admin_page' );

	$GLOBALS[WPPS_PREFIX.'admin_init'] = new FrameWork\Admin_Init();

	$GLOBALS[WPPS_PREFIX.'less'] = new FrameWork\Less_Sass();

	// End - Plugin Framework init


	// Predictive Datas
	global $wpps_keyword_data;
	$wpps_keyword_data = new \A3Rev\WPPredictiveSearch\Data\Keyword();

	global $wpps_postmeta_data;
	$wpps_postmeta_data = new \A3Rev\WPPredictiveSearch\Data\PostMeta();

	global $wpps_exclude_data;
	$wpps_exclude_data = new \A3Rev\WPPredictiveSearch\Data\Exclude();

	global $wpps_term_relationships_data;
	$wpps_term_relationships_data = new \A3Rev\WPPredictiveSearch\Data\Relationships();

	global $wpps_posts_data;
	$wpps_posts_data = new \A3Rev\WPPredictiveSearch\Data\Posts();

	global $wpps_taxonomy_data;
	$wpps_taxonomy_data = new \A3Rev\WPPredictiveSearch\Data\Taxonomy();

	// Predictive Main
	global $wp_predictive_search;
	$wp_predictive_search = new \A3Rev\WPPredictiveSearch\Main();

	// Predictive Error Logs
	global $wpps_errors_log;
	$wpps_errors_log = new \A3Rev\WPPredictiveSearch\Errors_Log();

	// Predictive Back Bone
	global $wpps_hook_backbone;
	$wpps_hook_backbone = new \A3Rev\WPPredictiveSearch\Hook_Backbone();

	// Predictive Schedule & Sync
	global $wpps_sync;
	$wpps_sync = new \A3Rev\WPPredictiveSearch\Sync();

	global $wpps_schedule;
	$wpps_schedule = new \A3Rev\WPPredictiveSearch\Schedule();


	global $wp_version;
	if ( version_compare( $wp_version, '5.5', '>=' ) ) {
		// Gutenberg Blocks init
		global $wpps_blocks;
		$wpps_blocks = new \A3Rev\WPPredictiveSearch\Blocks();
		new \A3Rev\WPPredictiveSearch\Blocks\Frontend();

		global $wpps_blocks_styles;
		$wpps_blocks_styles = new \A3Rev\WPPredictiveSearch\Blocks\Styles();

		// Call all blocks
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Search_Bar();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Search_Bar\Mobile_Icon();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Search_Bar\Category_Dropdown();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Search_Bar\Search_Icon();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Search_Bar\Search_Input();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Results_Dropdown();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Results_Dropdown\Close_Icon();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Results_Dropdown\Title();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Results_Dropdown\Footer();
		new \A3Rev\WPPredictiveSearch\Blocks\Search_Form\Results_Dropdown\Items();

		if ( wpps_current_theme_is_fse_theme() ) {
			require 'src/blocks/query-results/block.php';
			require 'src/blocks/results-heading/block.php';
			require 'src/blocks/results-filter-by/block.php';
			require 'src/blocks/item-title/block.php';
			require 'src/blocks/item-excerpt/block.php';
			require 'src/blocks/read-more/block.php';
			require 'src/blocks/item-featured-image/block.php';
			require 'src/blocks/item-terms/block.php';
			require 'src/blocks/item-template/block.php';

			// reigster templates and template parts
			new \A3Rev\WPPredictiveSearch\Blocks\BlockTemplatesController();
		}

		// register patterns
		new \A3Rev\WPPredictiveSearch\Blocks\Patterns();
	} else {
		add_action( 'admin_notices', 'wpps_blocks_unavailable', 8 );
	}

} else {
	return;
}

/**
 * Load Localisation files.
 *
 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
 *
 * Locales found in:
 * 		- WP_LANG_DIR/wp-predictive-search/wp-predictive-search-LOCALE.mo
 * 	 	- WP_LANG_DIR/plugins/wp-predictive-search-LOCALE.mo
 * 	 	- /wp-content/plugins/wp-predictive-search/languages/wp-predictive-search-LOCALE.mo (which if not found falls back to)
 */
function wp_predictive_search_plugin_textdomain() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'wp-predictive-search' );

	load_textdomain( 'wp-predictive-search', WP_LANG_DIR . '/wp-predictive-search/wp-predictive-search-' . $locale . '.mo' );
	load_plugin_textdomain( 'wp-predictive-search', false, WPPS_FOLDER . '/languages/' );
}

include 'includes/wp-predictive-search-template-functions.php';

include 'admin/wp-predictive-search-init.php';

/**
* Call when the plugin is activated
*/
register_activation_hook(__FILE__,'wp_predictive_search_install');
