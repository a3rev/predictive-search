<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

namespace A3Rev\WPPredictiveSearch\Blocks\Search_Form\Results_Dropdown;

use A3Rev\WPPredictiveSearch\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Close_Icon extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/dropdown-close-icon';
    protected $field_name = '';

	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'predictive_search_blocks_frontend_inline_css', array( $this, 'render_inline_css_head' ), 10, 2 );
	}

	public function register_block() {
		// Only load if Gutenberg is available.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
        }

		register_block_type(
			$this->block_name,
			array(
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	public function render( $attributes, $content ) {
		if ( is_admin() ) {
            return $content;
        }

		$content = $this->render_inline_css( $attributes, $content );

		return $content;
	}

	public function render_inline_css( $attributes, $content ) {
        if ( is_admin() ) {
            return $content;
        }

        if ( isset( $attributes['parentID'] ) ) {
            $content = $this->block_render_inline_css( $this->block_name, $attributes, $content );
        }

		return $content;
	}

	public function render_inline_css_head( $blockName, $attributes ) {
        $this->block_inline_css_head( $this->block_name, $blockName, $attributes );
	}

	public function inline_css( $attributes ) {
		// Check if have parentID so that this block style based parentID
		if ( ! isset( $attributes['parentID'] ) ) {
			return '';
		}

		global $wpps_blocks_styles;

		$stylecss = '
#predictive_results-'.$attributes['parentID'].' .ps_close {
	' . $wpps_blocks_styles->spacing_styles( $this->field_name, $attributes ) . '
	'. ( isset( $attributes['iconSize'] ) ? 'font-size: '.$attributes['iconSize'].'px!important;' : '' ) .'
	'. ( isset( $attributes['iconColor'] ) ? 'color: '.$attributes['iconColor'].'!important;' : '' ) .'
}';

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
