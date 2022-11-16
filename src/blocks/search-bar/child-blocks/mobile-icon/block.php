<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

namespace A3Rev\WPPredictiveSearch\Blocks\Search_Form\Search_Bar;

use A3Rev\WPPredictiveSearch\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mobile_Icon extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/mobile-icon';
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

		$blockID = $attributes['blockID'];

		$id = 'wpps_mobile_icon-'.$blockID;

		$classname = array(
			'wpps_mobile_icon',
			'wpps_mobile_icon-' . $blockID
		);
		if ( isset( $attributes['className'] ) ) $classname[] = $attributes['className'];
		$classname = implode( ' ', $classname );

		$content = $this->format_svg_icon( $content );
		$content .= $this->render_inline_css( $attributes, '' );
		$content = '<div id="'. esc_attr( $id ) .'" class="'. esc_attr( $classname ) .'">' . $content . '</div>';

		return $content;
	}

	public function render_inline_css( $attributes, $content ) {
        if ( is_admin() ) {
            return $content;
        }

        if ( isset( $attributes['blockID'] ) ) {
            $content = $this->block_render_inline_css( $this->block_name, $attributes, $content );
        }

		return $content;
	}

	public function render_inline_css_head( $blockName, $attributes ) {
        $this->block_inline_css_head( $this->block_name, $blockName, $attributes );
	}

	public function inline_css( $attributes ) {
		$blockID = $attributes['blockID'];

		$stylecss = '';

		if ( isset( $attributes['normalIconColor'] ) ) {
			$stylecss .= '
#wpps_mobile_icon-'.$blockID.' {
    color: '.$attributes['normalIconColor'].'!important;
}';
		}

		if ( isset( $attributes['hoverIconColor'] ) ) {
			$stylecss .= '
#wpps_mobile_icon-'.$blockID.' svg:hover {
		color: '.$attributes['hoverIconColor'].'!important;
}
';
		}

		if ( isset( $attributes['activeIconColor'] ) ) {
			$stylecss .= '
#wpps_mobile_icon-'.$blockID.'.active svg {
		color: '.$attributes['activeIconColor'].'!important;
	}
';
		}

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
