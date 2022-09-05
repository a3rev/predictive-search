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

class Search_Bar extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/search-bar';
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
				'provides_context' => array( 'wpps/layout' => 'layout' ),
				'editor_style' => 'predictive-search-block-editor',
				'style' => 'predictive-search-block-frontend',
				'render_callback' => array( $this, 'render' ),
			)
		);
	}

	public function render( $attributes, $content ) {
		if ( is_admin() ) {
            return $content;
        }

		$blockID = $attributes['blockID'];
		$rootID  = $attributes['rootID'];

		$id = 'wpps_container-'.$blockID;

		$classname = array(
			'wpps_container',
			'wpps_container-' . $blockID,
			'wpps_container-' . $rootID
		);
		if ( isset( $attributes['className'] ) ) $classname[] = $attributes['className'];
		if ( is_rtl() ) $classname[] = 'rtl';
		$classname = implode( ' ', $classname );

		global $wpps_search_page_id;
		$search_results_page = str_replace( array( 'http:', 'https:' ), '', get_permalink( $wpps_search_page_id ) );

		$inline_css = $this->render_inline_css( $attributes, '' );

		ob_start();
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classname ); ?>">
			<form
				class="wpps_form"
				autocomplete="off"
				action="<?php echo esc_url( $search_results_page ); ?>"
				method="get"
			>
				%1$s

				<?php if ( '' == get_option('permalink_structure') ) { ?>
				<input type="hidden" name="page_id" value="<?php echo esc_attr( $wpps_search_page_id ); ?>"  />

				<?php if ( class_exists('SitePress') ) { ?>
					<input type="hidden" name="lang" value="<?php echo esc_attr( ICL_LANGUAGE_CODE ); ?>"  />
				<?php } ?>

			<?php } ?>

				<input type="hidden" name="search_in" value=""  />
				<input type="hidden" name="search_other" value=""  />

				<?php do_action( 'wpps_search_form_inside' ); ?>
			</form>
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

	public function render_inline_css_head( $blockName, $attributes ) {
        $this->block_inline_css_head( $this->block_name, $blockName, $attributes );
	}

	public function inline_css( $attributes ) {
		global $wpps_blocks_styles;

		$blockID = $attributes['blockID'];

		$stylecss = '
#wpps_container-'.$blockID.' {
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_styles( '', $attributes, true ) : '' ) .'
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_radius_styles( '', $attributes, true ) : '' ) .'
	'. ( isset( $attributes['borderColor'] ) ? 'border-color: '.$attributes['borderColor'].';' : '' ) .'
	'. ( isset( $attributes['backgroundColor'] ) ? 'background-color: '.$attributes['backgroundColor'].';' : '' ) .'
	' . $wpps_blocks_styles->shadow_styles( 'shadow', $attributes ) . '
}';

		if ( isset( $attributes['height'] ) ) {
			$stylecss .= '
#wpps_container-'.$blockID.' .wpps_nav_left,
#wpps_container-'.$blockID.' .wpps_nav_right,
#wpps_container-'.$blockID.' .wpps_nav_fill,
#wpps_container-'.$blockID.' .wpps_nav_scope,
#wpps_container-'.$blockID.' .wpps_nav_submit,
#wpps_container-'.$blockID.' .wpps_nav_field,
#wpps_container-'.$blockID.' .wpps_search_keyword {
	height: '.$attributes['height'].'px!important;
}
#wpps_container-'.$blockID.' .wpps_nav_facade_label,
#wpps_container-'.$blockID.' .wpps_nav_down_icon,
#wpps_container-'.$blockID.' .wpps_category_selector,
#wpps_container-'.$blockID.' .wpps_nav_submit_icon,
#wpps_container-'.$blockID.' .wpps_searching_icon {
	line-height: '.$attributes['height'].'px!important;
}';
		}

		if ( isset( $attributes['borderFocusColor'] ) ) {
			$stylecss .= '
#wpps_container-'.$blockID.'.wpps_container_active {
	border-color: '.$attributes['borderFocusColor'].'!important;
}';
		}

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
