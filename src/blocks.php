<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

namespace A3Rev\WPPredictiveSearch;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Blocks {

	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );

		// Hook: Editor assets.
		add_action( 'enqueue_block_editor_assets', array( $this, 'cgb_editor_assets' ) );

		// Add all blocks to excerpt allowed
		add_filter( 'excerpt_allowed_blocks', array( $this, 'excerpt_allowed_blocks' ) );

		add_action( 'after_setup_theme', array( $this, 'a3_blockpress_after_setup_theme' ) );
	}

	public function a3_blockpress_after_setup_theme(  ) {
		// Add support for Block Styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Add support spcaing.
		add_theme_support( 'custom-spacing', array(
			"customMargin" => true,
			"customPadding"=> true,
			"margin" => true,
			"padding"=> true
		));

		// Add support align.
	    add_theme_support('align-wide');

	    if ( wpps_current_theme_is_fse_theme() ) {
	    	// Add support for Template Parts.
			add_theme_support( 'block-template-parts' );
		}
	}

	public function excerpt_allowed_blocks( $allowed_blocks ) {
		$allowed_blocks = array_merge( array(
			'wp-predictive-search/form'
		), $allowed_blocks );

		return $allowed_blocks;
	}

	public function create_blocks_section( $block_categories, $editor_context ) {
		$block_categories = array_merge(
			array(
				array(
					'slug' => 'wp-predictive-search-result-blocks',
					'title' => __( 'WP Predictive Search Result Blocks', 'wp-predictive-search' ),
					'icon' => '',
				),
			),
			$block_categories
		);

		return $block_categories;
	}

	public function register_block() {

		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}

		if ( wpps_current_theme_is_fse_theme() ) {
			add_filter( 'block_categories_all', array( $this, 'create_blocks_section' ), 10, 2 );
		}

		wp_register_style( 'wp-predictive-search-style', WPPS_CSS_URL . '/wp_predictive_search.css', array(), WPPS_VERSION, 'all' );

		// Blocks Frontend Style
		wp_register_style(
			'predictive-search-block-frontend', // Handle.
			plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
			array( 'wp-predictive-search-style' )
		);
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * @uses {wp-blocks} for block type registration & related functions.
	 * @uses {wp-element} for WP Element abstraction — structure of blocks.
	 * @uses {wp-i18n} to internationalize the block's text.
	 * @uses {wp-editor} for WP editor styles.
	 * @since 1.0.0
	 */
	function cgb_editor_assets() { // phpcs:ignore

		$js_deps = apply_filters( 'wpps_block_js_deps', array( 'wp-block-editor', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-data', 'wp-compose', 'wp-components' ) );

		$script_file_name = wpps_current_theme_is_fse_theme() ? 'blocks.build.js' : 'blocks.build.nofse.js';

		wp_enqueue_script(
			'predictive-search-block-js', // Handle.
			plugins_url( '/dist/' . $script_file_name, dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
			$js_deps, // Dependencies, defined above.
			// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: File modification time.
			true // Enqueue the script in the footer.
		);

		// Styles.
		wp_register_style(
			'predictive-search-block-editor', // Handle.
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
			array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
			// filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
		);

		global $wpps_cache;
		$disabled_cat_dropdown = 0;
		if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
			$disabled_cat_dropdown = 1;
			$post_categories = 0;
		} else {
			$category_list = wpps_get_categories();
			if ( $category_list ) {
				$post_categories = array();
				foreach ( $category_list as $category_data ) {
					$post_categories[] = array( 'label' => str_replace( '&nbsp;&nbsp;&nbsp;', ' - ', $category_data['name'] ), 'value' => $category_data['slug'] );
				}
			} else {
				$post_categories = 0;
			}
		}

		$taxonomy_terms_cache = array( 'category' => ( 0 !== $post_categories ? json_encode( $post_categories ) : 0 ) );

		wp_localize_script( 'predictive-search-block-js', 'predictive_search_vars', array( 
			'disabled_cat_dropdown' => $disabled_cat_dropdown, 
			'taxonomy_terms'       	=> apply_filters( 'wpps_taxonomy_terms_cache', $taxonomy_terms_cache ) ,
			'preview'               => WPPS_URL . '/src/blocks/search-form/preview.jpg',
			'image_sizes'			=> $this->get_image_sizes(),
			'placeholder'			=> WPPS_URL . '/src/blocks/item-featured-image/placeholder.png',
			'theme'					=> wp_get_theme()->get_stylesheet(),
		) );
	}

	public function get_block_card_item() {
		if ( isset( $_SESSION['ps_results_item_card'] ) ) {
			$block_instance = $_SESSION['ps_results_item_card'];
			$block_content = ( new \WP_Block( $block_instance ) )->render( array( 'dynamic' => false ) );
		} else {
			$block_template = '<!-- wp:template-part {"slug":"ps-all-results-item","theme":"'. wp_get_theme()->get_stylesheet() .'"} /-->';
			$block_content = do_blocks( $block_template );
		}

		return $block_content;
	}

	function get_image_sizes() {

		global $_wp_additional_image_sizes;

		$intermediate_image_sizes = get_intermediate_image_sizes();

		$image_sizes = array();
		foreach ( $intermediate_image_sizes as $size ) {
			if ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
				$image_sizes[ $size ] = array(
					'width'  => $_wp_additional_image_sizes[ $size ][ 'width' ],
					'height' => $_wp_additional_image_sizes[ $size ][ 'height' ]
				);
			} else {
				$image_sizes[ $size ] = array(
					'width'  => intval( get_option( "{$size}_size_w" ) ),
					'height' => intval( get_option( "{$size}_size_h" ) )
				);
			}
		}

		$sizes_arr = [];
		foreach ( $image_sizes as $key => $value ) {
			$temp_arr = [];
			$temp_arr[ 'value' ] = $key;
			$temp_arr[ 'label' ] = ucwords( strtolower( preg_replace( '/[-_]/', ' ', $key ) ) ) . " - {$value['width']} x {$value['height']}";
			$sizes_arr[] = $temp_arr;
		}

		$sizes_arr[] = array(
			'value' => 'full',
			'label' => __( 'Full Size', 'a3-blockpress' )
		);

		return $sizes_arr;
	}
}
