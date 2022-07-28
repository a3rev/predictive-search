<?php
/**
 * Blocks Frontend
 *
 * Enqueue CSS/JS of all the blocks on frontend.
 *
 * @since   1.0.0
 * @package CGB
 */

namespace A3Rev\WPPredictiveSearch\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend {

	public static $header_google_fonts = array();
	public static $render_google_fonts = array();

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_inline_css' ), 20 );

		// Hook: Include google fonts on Frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_google_fonts' ), 100 );
	}

	/**
	 * FOR BLOCK EXTEND THIS CLASS : START
	 */

	/*
	 * Use to add inline css from block when extend this class
	 */
	public function enqueue_global_scripts_styles() {
		if ( ! wp_style_is( 'predictive-search-block-frontend', 'enqueued' ) ) {
			wp_enqueue_style( 'predictive-search-block-frontend' );
		}

		if ( ! wp_script_is( 'wp-predictive-search-popup-backbone', 'enqueued' ) ) {
			wp_enqueue_script( 'wp-predictive-search-popup-backbone' );
		}
	}

	/*
	 * Use to get google font from block when extend this class
	 */
	public function get_block_google_fonts( $typo_keys = array(), $attributes = array() ) {
		if ( ! is_array( $typo_keys ) ) {
			$typo_keys = array( $typo_keys );
		}

		$block_gfonts = array();
		foreach ( $typo_keys as $typo_key ) {
			if ( isset( $attributes[ $typo_key . 'Google' ] ) && $attributes[ $typo_key . 'Google' ] ) {
				if ( isset( $attributes[ $typo_key . 'FontFamily' ] ) ) {
					$block_gfonts[] = array(
						'FontFamily' => $attributes[ $typo_key . 'FontFamily' ],
						'Variant'    => isset( $attributes[ $typo_key . 'Variant' ] ) ? $attributes[ $typo_key . 'Variant' ] : '',
					);
				}
			}
		}

		if ( empty( $block_gfonts ) ) {
			return false;
		}

		return $block_gfonts;
	}

	/*
	 * Use to render google font of block when extend this class
	 */
	public function render_block_google_font( $block_google_fonts ) {
		if ( empty( $block_google_fonts ) ) {
			return;
		}

		$valid_gfonts = array();

		//var_dump( self::$header_google_fonts );
		//var_dump( self::$render_google_fonts );

		// Validate if font is rendering at header or callback_render of other block then stop
		$check_gfonts = array_merge( self::$header_google_fonts, self::$render_google_fonts );
		$combine_gfonts = $this->combine_google_fonts( $check_gfonts );
		self::$render_google_fonts = array_merge( $block_google_fonts, self::$render_google_fonts );

		if ( ! empty( $combine_gfonts ) ) {
			foreach ( $block_google_fonts as $font ) {

				if ( ! isset( $font['FontFamily'] ) ) {
					continue;
				}

				$font_family = $font['FontFamily'];
				$font_variant = $font['Variant'];

				if ( isset( $combine_gfonts[ $font_family ] ) ) {
					if ( ! in_array( $font_variant, $combine_gfonts[ $font_family ] ) ) {
						$valid_gfonts[] = $font;
					}
				} else {
					$valid_gfonts[] = $font;
				}
			}
		} else {
			$valid_gfonts = $block_google_fonts;
		}

		return $this->include_google_fonts( $valid_gfonts );
	}

	public function block_render_inline_css( $blockName, $attributes, $content = '' ) {
		if ( isset( $attributes['blockID'] ) ) {
			$this->enqueue_global_scripts_styles();

			$unique_id = $attributes['blockID'];
			$style_id = str_replace( '/', '-', $blockName ) . '-' . esc_attr( $unique_id );
			if ( ! wp_style_is( $style_id, 'enqueued' ) ) {
				$css = $this->inline_css( $attributes );
				if ( ! empty( $css ) ) {
					global $wpps_blocks_styles;
					$css = $wpps_blocks_styles->minimizeCSSsimple( $css );
					// if ( doing_filter( 'the_content' ) ) {
					// 	wp_register_style( $style_id, false );
					// 	wp_enqueue_style( $style_id );
					// 	$content = '<style id="' . $style_id . '-inline-css" type="text/css">' . $css . '</style>' . $content;
					// } else {
						$this->add_inline_css( $style_id, $css, true );
					// }
				}
			}
		}

		return $content;
	}

	public function block_inline_css_head( $blockName, $blockNameCheck, $attributes ) {
        if ( $blockName === $blockNameCheck && isset( $attributes['blockID'] ) ) {
            $this->enqueue_global_scripts_styles();

            $unique_id = $attributes['blockID'];
            $style_id = str_replace( '/', '-', $blockName ) . '-' . esc_attr( $unique_id );
            if ( ! wp_style_is( $style_id, 'enqueued' ) ) {
				$css = $this->inline_css( $attributes );
                if ( ! empty( $css ) ) {
					global $wpps_blocks_styles;
					$css = $wpps_blocks_styles->minimizeCSSsimple( $css );
                    $this->add_inline_css( $style_id, $css );
                }
            }
        }
	}

	public function inline_css( $attributes ) {}

	/*
	 * Use to add inline css from block when extend this class
	 */
	public function add_inline_css( $style_id, $css, $in_content = false ) {
		if ( ! is_admin() ) {
			wp_register_style( $style_id, false );
			wp_enqueue_style( $style_id );
			wp_add_inline_style( $style_id, $css );
			if ( 1 === did_action( 'wp_head' ) && $in_content ) {
				wp_print_styles( $style_id );
			}
		}
	}
	/**
	 * FOR BLOCK EXTEND THIS CLASS : END
	 */


	public function frontend_inline_css() {
		if ( function_exists( 'has_blocks' ) && has_blocks( get_the_ID() ) ) {
			global $post;
			if ( ! is_object( $post ) ) {
				return;
			}

			$blocks = parse_blocks( $post->post_content );
			$this->blocks_inline_css( $blocks );
		}
	}

	public function blocks_inline_css( $blocks ) {
		if ( ! is_array( $blocks ) || empty( $blocks ) ) {
			return;
		}

		foreach ( $blocks as $block ) {
			if ( ! is_object( $block ) && is_array( $block ) && isset( $block['blockName'] ) ) {
				if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
					do_action( 'predictive_search_blocks_frontend_inline_css', $block['blockName'], $block['attrs'] );
					self::$header_google_fonts = apply_filters( 'predictive_search_blocks_frontend_google_fonts', self::$header_google_fonts, $block['blockName'], $block['attrs'] );
				}

				if ( 'core/block' === $block['blockName'] ) {
					if ( isset( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
						$blockattr = $block['attrs'];
						if ( isset( $blockattr['ref'] ) ) {
							$reusable_block = get_post( $blockattr['ref'] );
							if ( $reusable_block && 'wp_block' == $reusable_block->post_type ) {
								$reuse_data_block = parse_blocks( $reusable_block->post_content );
								$this->blocks_inline_css( $reuse_data_block );
							}
						}
					}
				}

				if ( isset( $block['innerBlocks'] ) && ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$this->blocks_inline_css( $block['innerBlocks'] );
				}
			}
		}
	}

	public function frontend_google_fonts() {
		self::$header_google_fonts = apply_filters( 'predictive_search_blocks_frontend_global_google_fonts', self::$header_google_fonts );

		return $this->include_google_fonts( self::$header_google_fonts );
	}

	public function combine_google_fonts( $google_fonts = array() ) {
		$combine_gfonts = array();

		if ( empty( $google_fonts ) ) {
			return $combine_gfonts;
		}

		foreach ( $google_fonts as $font ) {

			if ( ! isset( $font['FontFamily'] ) ) {
				continue;
			}

			$font_family = $font['FontFamily'];
			$font_variant = $font['Variant'];

			if ( isset( $combine_gfonts[ $font_family ] ) ) {
				if ( ! in_array( $font_variant, $combine_gfonts[ $font_family ] ) ) {
					$combine_gfonts[ $font_family ][] = $font_variant;
				}
			} else {
				$combine_gfonts[ $font_family ] = array( $font_variant );
			}

		}

		return $combine_gfonts;
	}

	public function include_google_fonts( $google_fonts = array() ) {
		$allow_google_fonts = apply_filters( 'predictive_search_blocks_frontend_allow_google_fonts', true );
		if ( ! $allow_google_fonts ) {
			return;
		}

		if ( empty( $google_fonts ) ) {
			return;
		}

		$combine_gfonts = $this->combine_google_fonts( $google_fonts );

		if ( empty( $combine_gfonts ) ) {
			return;
		}

		$link = '';
		foreach ( $combine_gfonts as $font => $variant ) {

			if ( ! empty( $link ) ) {
				$link .= '%7C'; // Append a new font to the string.
			}

			$link .= $font;

			$variant = array_filter( $variant );
			if ( ! empty( $variant ) ) {
				$link .= ':';
				$link .= implode( ',', $variant );
			}
		}

		if ( ! empty( $link ) ) {
			wp_enqueue_style( 'wpps-block-gfonts-' . time(), '//fonts.googleapis.com/css?family=' . esc_attr( str_replace( '|', '%7C', $link ) ) );
		}
	}

	public function format_svg_icon( $content ) {
		$replaces = array(
			'strokewidth' => 'stroke-width',
			'strokelinecap' => 'stroke-linecap',
			'strokelinejoin' => 'stroke-linejoin',
		);

		foreach ( $replaces as $search => $replace ) {
			$content = str_replace( $search, $replace, $content );
		}

		return $content;
	}
}
