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

class Search_Input extends Blocks\Frontend  {

	protected $block_name = 'wp-predictive-search/search-input';
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

		$blockID = $attributes['blockID'];

		$id = 'wpps_nav_fill-'.$blockID;

		$classname = array(
			'wpps_nav_fill',
			'wpps_nav_fill-' . $blockID,
		);
		if ( isset( $attributes['className'] ) ) $classname[] = $attributes['className'];
		$classname = implode( ' ', $classname );

		if ( ! isset( $attributes['placeholder'] ) ) {
			$attributes['placeholder'] = '';
		}

		$inline_css = $this->render_inline_css( $attributes, '' );

		ob_start();
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classname ); ?>">
			<div class="wpps_nav_field">
				<input type="text" name="rs" class="wpps_search_keyword" id="wpps_search_keyword_<?php echo isset( $attributes['rootID'] ) ? esc_attr( $attributes['rootID'] ) : ''; ?>"
					aria-label="<?php _e( 'Keyword Search', 'wp-predictive-search' ); ?>"
					onblur="if( this.value == '' ){ this.value = '<?php echo esc_js( $attributes['placeholder'] ); ?>'; }"
					onfocus="if( this.value == '<?php echo esc_js( $attributes['placeholder'] ); ?>' ){ this.value = ''; }"
					value="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
					data-ps-id="<?php echo isset( $attributes['rootID'] ) ? esc_attr( $attributes['rootID'] ) : ''; ?>"
					data-ps-default_text="<?php echo esc_attr( $attributes['placeholder'] ); ?>"
				/>
				%1$s
			</div>
		</div>
		<?php

		$output = ob_get_clean();

		return $inline_css . sprintf( $output, $content );
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

	public function add_google_fonts( $google_fonts, $blockName, $attributes ) {
        if ( $blockName === $this->block_name && isset( $attributes['blockID'] ) ) {
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
		global $wpps_blocks_styles;

		$blockID = $attributes['blockID'];

		$stylecss = '
#wpps_nav_fill-'.$blockID.' .wpps_search_keyword {
	'. ( isset( $attributes['textColor'] ) ? 'color: '.$attributes['textColor'].';' : '' ) .'
	' . $wpps_blocks_styles->spacing_styles( $this->field_name, $attributes ) . '
	'. ( isset( $attributes['enableCustomFont'] ) && $attributes['enableCustomFont'] ? $wpps_blocks_styles->typography_styles( $this->field_name.'typo', $attributes ) : '' ) .'
}';

		if ( isset( $attributes['backgroundColor'] ) ) {
			$stylecss .= '
#wpps_nav_fill-'.$blockID.' .wpps_nav_field {
	background-color: '.$attributes['backgroundColor'].'!important;
}';
		}

		if ( isset( $attributes['iconSize'] ) ) {
			$stylecss .= '
#wpps_nav_fill-'.$blockID.' .wpps_searching_icon {
	width: '.$attributes['iconSize'].'px!important;
}';
		}

		if ( isset( $attributes['iconColor'] ) ) {
			$stylecss .= '
#wpps_nav_fill-'.$blockID.' .wpps_searching_icon,
#wpps_nav_fill-'.$blockID.' .wpps_searching_icon * {
	color: '.$attributes['iconColor'].'!important;
	fill: '.$attributes['iconColor'].'!important;
}';
		}

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
