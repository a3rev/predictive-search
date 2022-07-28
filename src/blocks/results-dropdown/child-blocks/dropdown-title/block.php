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

class Title extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/dropdown-title';
    protected $field_name = '';

	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'predictive_search_blocks_frontend_google_fonts', array( $this, 'add_google_fonts' ), 10, 3 );
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

	public function add_google_fonts( $google_fonts, $blockName, $attributes ) {
        if ( $blockName === $this->block_name && isset( $attributes['parentID'] ) ) {
			$typo_key = $this->field_name . 'typo';

            if ( isset( $attributes[ 'enableCustomFont' ] ) && $attributes[ 'enableCustomFont' ] ) {
                $block_gfonts = $this->get_block_google_fonts( $typo_key, $attributes );
                if ( $block_gfonts ) {
                	if ( ! is_array( $google_fonts ) ) {
						$google_fonts = array();
					}
					
                    $google_fonts = array_merge( $block_gfonts, $google_fonts );
                }
            }
        }

		return $google_fonts;
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
#predictive_results-'.$attributes['parentID'].' .ac_odd .ajax_search_content_title {
	' . $wpps_blocks_styles->spacing_styles( $this->field_name, $attributes ) . '
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_styles( $this->field_name, $attributes, true ) : '' ) .'
	'. ( isset( $attributes['enableCustomFont'] ) && $attributes['enableCustomFont'] ? $wpps_blocks_styles->typography_styles( $this->field_name.'typo', $attributes ) : '' ) .'
	'. ( isset( $attributes['textColor'] ) ? 'color: '.$attributes['textColor'].'!important;' : '' ) .'
	'. ( isset( $attributes['backgroundColor'] ) ? 'background-color: '.$attributes['backgroundColor'].'!important;' : '' ) .'
}';

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
