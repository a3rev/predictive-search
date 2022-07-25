<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

namespace A3Rev\WPPredictiveSearch\Blocks\Search_Form;

use A3Rev\WPPredictiveSearch\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Results_Dropdown extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/results-dropdown';
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

		$id = 'predictive_results-'.$attributes['blockID'];

		$content = $this->render_inline_css( $attributes, $content );
		$content .= '<input type="hidden" name="predictive_results_id" value="'. esc_attr( $id ).'" />';
		$content .= '<input type="hidden" name="popup_wide" value="'. ( isset( $attributes['containerWide'] ) ? esc_attr( $attributes['containerWide'] ) .'_wide' : 'full_wide' ) .'" />';

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

		$stylecss = '
#predictive_results-'.$attributes['blockID'].' {
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_styles( '', $attributes, true ) : '' ) .'
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_radius_styles( '', $attributes, true ) : '' ) .'
	'. ( isset( $attributes['borderColor'] ) ? 'border-color: '. $attributes['borderColor'].';' : '' ) .'
}';

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
