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

class Search_Icon extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/search-icon';
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
				'uses_context' => array( 'wpps/layout' ),
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	public function render( $attributes, $content, $block ) {
		if ( is_admin() ) {
            return $content;
        }

		$blockID = $attributes['blockID'];

		$id = 'wpps_search_submit-'.$blockID;

		$classname = array(
			'wpps_search_submit',
			'wpps_search_submit-' . $blockID,
			'wpps_nav_' . ( isset( $block->context['wpps/layout'] ) && 'submit-cat' === $block->context['wpps/layout'] ? 'left' : 'right' ),
		);
		if ( isset( $attributes['className'] ) ) $classname[] = $attributes['className'];
		$classname = implode( ' ', $classname );

		$content = $this->format_svg_icon( $content );
		$content .= $this->render_inline_css( $attributes, '' );
		$content = '<div id="'. esc_attr( $id ) .'" class="'. esc_attr( $classname ) .'" aria-label="'.__( 'Search Now', 'wp-predictive-search' ).'"><div class="wpps_nav_submit">' . $content . '<input class="wpps_nav_submit_bt" type="button" value="'.__( 'Go', 'wp-predictive-search' ).'" aria-label="'.__( 'Go', 'wp-predictive-search' ).'"></div></div>';

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
		global $wpps_blocks_styles;

		$blockID = $attributes['blockID'];

        $stylecss = '
#wpps_search_submit-'.$blockID.' .wpps_nav_submit {
	'. ( isset( $attributes['normalBackgroundColor'] ) ? 'background-color: '.$attributes['normalBackgroundColor'].';' : '' ) .'
	'. ( isset( $attributes['normalBorderColor'] ) ? 'border-color: '.$attributes['normalBorderColor'].';' : '' ) .'
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_styles( $this->field_name, $attributes, true ) : '' ) .'
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_radius_styles( $this->field_name, $attributes, true ) : '' ) .'
}';

		if ( isset( $attributes['hoverBackgroundColor'] ) || isset( $attributes['hoverBorderColor'] ) ) {
			$stylecss .= '
#wpps_search_submit-'.$blockID.' .wpps_nav_submit:hover {
	'. ( isset( $attributes['hoverBackgroundColor'] ) ? 'background-color: '.$attributes['hoverBackgroundColor'].'!important;' : '' ) .'
	'. ( isset( $attributes['hoverBorderColor'] ) ? 'border-color: '.$attributes['hoverBorderColor'].'!important;' : '' ) .'
}';
		}

		if ( isset( $attributes['normalIconColor'] ) ) {
			$stylecss .= '
#wpps_search_submit-'.$blockID.' .wpps_nav_submit svg,
#wpps_search_submit-'.$blockID.' .wpps_nav_submit svg * {
	color: '.$attributes['normalIconColor'].'!important;
}';
		}

		if ( isset( $attributes['hoverIconColor'] ) ) {
			$stylecss .= '
#wpps_search_submit-'.$blockID.' .wpps_nav_submit:hover svg,
#wpps_search_submit-'.$blockID.' .wpps_nav_submit:hover svg * {
	color: '.$attributes['hoverIconColor'].'!important;
}';
		}


		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
