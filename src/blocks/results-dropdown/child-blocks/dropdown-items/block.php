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

class Items extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/dropdown-items';
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
		$content .= '<input type="hidden" name="show_img" value="'. ( ! isset( $attributes['showImg'] ) || $attributes['showImg'] ? 1 : 0 ) .'" />';
		$content .= '<input type="hidden" name="show_desc" value="'. ( ! isset( $attributes['showDesc'] ) || $attributes['showDesc'] ? 1 : 0 ) .'" />';
		$content .= '<input type="hidden" name="text_lenght" value="'. ( isset( $attributes['charactersDescCount'] ) ? $attributes['charactersDescCount'] : 100 ) .'" />';
		$content .= '<input type="hidden" name="show_in_cat" value="'. ( ! isset( $attributes['showCat'] ) || $attributes['showCat'] ? 1 : 0 ) .'" />';

		return $content;
	}

	public function render_inline_css( $attributes, $content ) {
        if ( is_admin() ) {
            return $content;
        }

        if ( isset( $attributes['parentID'] ) ) {
			$typo_key = array();

			if ( isset( $attributes[ 'enableCustomNameFont' ] ) && $attributes[ 'enableCustomNameFont' ] ) {
				$typo_key[] = $this->field_name .'nameTypo';
			}

			if ( ( ! isset( $attributes['showDesc'] ) || $attributes['showDesc'] ) && isset( $attributes[ 'enableCustomDescFont' ] ) && $attributes[ 'enableCustomDescFont' ] ) {
				$typo_key[] = $this->field_name .'descTypo';
			}

			if ( ( ! isset( $attributes['showCat'] ) || $attributes['showCat'] ) && isset( $attributes[ 'enableCustomCatFont' ] ) && $attributes[ 'enableCustomCatFont' ] ) {
				$typo_key[] = $this->field_name .'catTypo';
			}

            if ( ! empty( $typo_key ) ) {
                $block_gfonts = $this->get_block_google_fonts( $typo_key, $attributes );
                if ( $block_gfonts ) {
                    $content = $this->render_block_google_font( $block_gfonts ) . $content;
                }
			}

            $content = $this->block_render_inline_css( $this->block_name, $attributes, $content );
        }

		return $content;
	}

	public function add_google_fonts( $google_fonts, $blockName, $attributes ) {
        if ( $blockName === $this->block_name && isset( $attributes['parentID'] ) ) {
			$typo_key = array();

			if ( isset( $attributes[ 'enableCustomNameFont' ] ) && $attributes[ 'enableCustomNameFont' ] ) {
				$typo_key[] = $this->field_name .'nameTypo';
			}

			if ( ( ! isset( $attributes['showDesc'] ) || $attributes['showDesc'] ) && isset( $attributes[ 'enableCustomDescFont' ] ) && $attributes[ 'enableCustomDescFont' ] ) {
				$typo_key[] = $this->field_name .'descTypo';
			}

			if ( ( ! isset( $attributes['showCat'] ) || $attributes['showCat'] ) && isset( $attributes[ 'enableCustomCatFont' ] ) && $attributes[ 'enableCustomCatFont' ] ) {
				$typo_key[] = $this->field_name .'catTypo';
			}

            if ( ! empty( $typo_key ) ) {
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

		extract( $attributes );

		$stylecss = '
#predictive_results-'.$parentID.' .ac_odd .ajax_search_content {
	' . $wpps_blocks_styles->spacing_styles( $this->field_name, $attributes ) . '
	'. ( isset( $enableBorder ) && $enableBorder ? $wpps_blocks_styles->border_styles( $this->field_name, $attributes, true ) : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd:not(.ac_over) .ajax_search_content {
	'. ( isset( $enableBorder ) && $enableBorder && isset( $normalBorderColor ) ? 'border-color: '.$normalBorderColor.'!important;' : '' ) .'
	'. ( isset( $normalBackgroundColor ) ? 'background-color: '.$normalBackgroundColor.'!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd.ac_over .ajax_search_content {
	'. ( isset( $enableBorder ) && $enableBorder && isset( $hoverBorderColor ) ? 'border-color: '.$hoverBorderColor.'!important;' : '' ) .'
	'. ( isset( $hoverBackgroundColor ) ? 'background-color: '.$hoverBackgroundColor.'!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd .rs_content_popup .rs_name {
	'. ( isset( $enableCustomNameFont ) && $enableCustomNameFont ? $wpps_blocks_styles->typography_styles( $this->field_name.'nameTypo', $attributes, true ) : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd:not(.ac_over) .rs_content_popup .rs_name {
	'. ( isset( $normalNameColor ) ? 'color: '.$normalNameColor.'!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd.ac_over .rs_content_popup .rs_name {
	'. ( isset( $hoverNameColor ) ? 'color: '.$hoverNameColor.'!important;' : '' ) .'
}';

		if ( ! isset( $showImg ) || $showImg ) {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .rs_avatar {
	'. ( isset( $itemImgSize ) ? 'width: '.$itemImgSize.'px!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd .rs_content_popup {
	'. ( isset( $itemImgSize ) ? 'width: calc( 100% - '.( $itemImgSize + 10 ).'px )!important;' : '' ) .'
}';
		} else {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .rs_avatar {
	display: none;
}
#predictive_results-'.$parentID.' .ac_odd .rs_content_popup {
	width: 100%;
}';
		}

		if ( ! isset( $showDesc ) || $showDesc ) {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .rs_content_popup .rs_description {
	'. ( isset( $enableCustomDescFont ) && $enableCustomDescFont ? $wpps_blocks_styles->typography_styles( $this->field_name.'descTypo', $attributes ) : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd:not(.ac_over) .rs_content_popup .rs_description {
	'. ( isset( $normalDescColor ) ? 'color: '.$normalDescColor.'!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd.ac_over .rs_content_popup .rs_description {
	'. ( isset( $hoverDescColor ) ? 'color: '.$hoverDescColor.'!important;' : '' ) .'
}';
		} else {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .rs_content_popup .rs_description {
	display: none;
}';
		}

		if ( ! isset( $showCat ) || $showCat ) {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd:not(.ac_over) .rs_content_popup .rs_cat {
	'. ( isset( $normalCatWordColor ) ? 'color: '.$normalCatWordColor.'!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd.ac_over .rs_content_popup .rs_cat {
	'. ( isset( $hoverCatWordColor ) ? 'color: '.$hoverCatWordColor.'!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd .rs_content_popup .rs_cat,
#predictive_results-'.$parentID.' .ac_odd .rs_content_popup .rs_cat > a {
	'. ( isset( $enableCustomCatFont ) && $enableCustomCatFont ? $wpps_blocks_styles->typography_styles( $this->field_name.'catTypo', $attributes ) : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd:not(.ac_over) .rs_content_popup .rs_cat > a {
	'. ( isset( $normalCatColor ) ? 'color: '.$normalCatColor.'!important;' : '' ) .'
}
#predictive_results-'.$parentID.' .ac_odd.ac_over .rs_content_popup .rs_cat > a {
	'. ( isset( $hoverCatColor ) ? 'color: '.$hoverCatColor.'!important;' : '' ) .'
}';
		}

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
