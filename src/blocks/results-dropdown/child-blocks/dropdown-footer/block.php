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

class Footer extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/dropdown-footer';
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

		$moreText = 'See more search results for \'%s\' in:';
		if ( isset( $attributes['moreText'] ) && ! empty( $attributes['moreText'] ) ) {
			$moreText = $attributes['moreText'];
		}

		$inline_css = $this->render_inline_css( $attributes, '' );

		ob_start();
		?>
		<script type="text/template" id="wp_psearch_footerCustomTpl_<?php echo esc_attr( $attributes['rootID'] ); ?>"><div rel="more_result" class="more_result">
				<span><?php echo sprintf( wpps_ict_t__( 'More result Text - Custom ' . esc_html( $attributes['parentID'] ), $moreText ), '{{= title }}' ); ?></span>
				{{ if ( description != null && description != '' ) { }}{{= description }}{{ } }}
		</div></script>
		<?php
		$template_script = ob_get_clean();

		return sprintf(
	        '%1$s
	        %2$s
	        %3$s',
	        $inline_css,
	        $template_script,
	        $content
	    );
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
			$typo_key = array();

			if ( isset( $attributes[ 'enableCustomMoreTextFont' ] ) && $attributes[ 'enableCustomMoreTextFont' ] ) {
				$typo_key[] = $this->field_name .'moreTextTypo';
			}

			if ( isset( $attributes[ 'enableCustomMoreLinkFont' ] ) && $attributes[ 'enableCustomMoreLinkFont' ] ) {
				$typo_key[] = $this->field_name .'moreLinkTypo';
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
#predictive_results-'.$parentID.' .ac_odd .more_result {
	' . $wpps_blocks_styles->spacing_styles( $this->field_name, $attributes ) . '
	'. ( isset( $backgroundColor ) ? 'background-color: '.$backgroundColor.'!important;' : '' ) .'
}';

		if ( isset( $moreTextColor ) || ( isset( $enableCustomMoreTextFont ) && $enableCustomMoreTextFont ) ) {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .more_result span {
	'. ( isset( $moreTextColor ) ? 'color: '.$moreTextColor.'!important;' : '' ) .'
	'. ( isset( $enableCustomMoreTextFont ) && $enableCustomMoreTextFont ? $wpps_blocks_styles->typography_styles( $this->field_name.'moreTextTypo', $attributes ) : '' ) .'
}';
		}

		if ( isset( $moreLinkColor ) || ( isset( $enableCustomMoreLinkFont ) && $enableCustomMoreLinkFont ) ) {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .more_result a {
	'. ( isset( $moreLinkColor ) ? 'color: '.$moreLinkColor.'!important;' : '' ) .'
	'. ( isset( $enableCustomMoreLinkFont ) && $enableCustomMoreLinkFont ? $wpps_blocks_styles->typography_styles( $this->field_name.'moreLinkTypo', $attributes ) : '' ) .'
}';
		}

		if ( isset( $moreIconColor ) ) {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .more_result .see_more_arrow,
#predictive_results-'.$parentID.' .ac_odd .more_result .see_more_arrow * {
	color: '.$moreIconColor.'!important;
}';
		}

		if ( isset( $moreIconSize ) ) {
			$stylecss .= '
#predictive_results-'.$parentID.' .ac_odd .more_result .see_more_arrow svg{
	width: '.$moreIconSize.'px!important;
	height: '.$moreIconSize.'px!important;
}';
		}

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
