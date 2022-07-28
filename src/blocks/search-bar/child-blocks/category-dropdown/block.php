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

class Category_Dropdown extends Blocks\Frontend {

	protected $block_name = 'wp-predictive-search/category-dropdown';
	protected $field_name = '';
	public $got_post_categories = false;
	public $post_categories = false;

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

	public function dropdown_status_is_hidden( $attributes ) {
		$hide_category_dropdown = false;

		if ( isset( $attributes['enable'] ) && ! $attributes['enable'] ) {
			return true;
		}

		if ( ! $this->got_post_categories ) {
			$this->got_post_categories = true;

			$taxonomy = 'category';
			if ( isset( $attributes['taxonomy'] ) && ! empty( $attributes['taxonomy'] ) ) {
				$taxonomy = $attributes['taxonomy'];
			}
			$this->post_categories = wpps_get_categories( $taxonomy );
		}

		if ( false === $this->post_categories ) {
			$hide_category_dropdown = true;
		}

		return $hide_category_dropdown;
	}

	public function render( $attributes, $content ) {
		if ( is_admin() ) {
            return $content;
        }

		$blockID = $attributes['blockID'];

		$in_taxonomy = 'category';
		if ( isset( $attributes['taxonomy'] ) && ! empty( $attributes['taxonomy'] ) ) {
			$in_taxonomy = $attributes['taxonomy'];
		}

		if ( $this->dropdown_status_is_hidden( $attributes ) ) {
			return '<input type="hidden" class="wpps_category_selector" name="cat_in" value="" data-ps-cat_max_wide="'. ( isset( $attributes['maxWidth'] ) ? esc_attr( $attributes['maxWidth'] ) : 30 ) .'" data-ps-taxonomy="'. esc_attr( $in_taxonomy ) .'" />';
		}

		$id = 'wpps_cat_dropdown-'.$blockID;

		$classname = array(
			'wpps_cat_dropdown',
			'wpps_cat_dropdown-' . $blockID,
			'wpps_nav_' . ( isset( $attributes['position'] ) ? $attributes['position'] : 'left' ),
		);
		if ( isset( $attributes['className'] ) ) $classname[] = $attributes['className'];
		if ( isset( $attributes['enable'] ) && ! $attributes['enable'] ) $classname[] = 'disabled'; // default is true
		$classname = implode( ' ', $classname );

		$default_cat       = '';
		$default_cat_label = wpps_ict_t__( 'All', __( 'All', 'wp-predictive-search' ) );
		if ( isset( $attributes['defaultCategory'] ) && ! empty( $attributes['defaultCategory'] ) ) {
			foreach ( $this->post_categories as $category_data ) {
				if ( $attributes['defaultCategory'] == $category_data['slug'] ) {
					$default_cat       = $category_data['slug'];
					$default_cat_label = esc_html( str_replace( '&nbsp;', '', $category_data['name'] ) );
					break;
				}
			}
		}

		ob_start();
			echo $this->render_inline_css( $attributes, '' );
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classname ); ?>">
			<div class="wpps_nav_scope">
				<div class="wpps_nav_facade">
					<?php echo $this->format_svg_icon( $content ); ?>
					<span class="wpps_nav_facade_label"><?php echo esc_html( $default_cat_label ); ?></span>
				</div>
				<select aria-label="<?php _e( 'Select Category', 'wp-predictive-search' ); ?>" class="wpps_category_selector" name="cat_in" data-ps-cat_max_wide="<?php echo isset( $attributes['maxWidth'] ) ? esc_attr( $attributes['maxWidth'] ) : 30; ?>" data-ps-taxonomy="<?php echo esc_attr( $in_taxonomy ); ?>">
					<option value="" selected="selected"><?php wpps_ict_t_e( 'All', __( 'All', 'wp-predictive-search' ) ); ?></option>
				<?php foreach ( $this->post_categories as $category_data ) { ?>
					<option <?php selected( $default_cat, $category_data['slug'], true ); ?> data-href="<?php echo esc_url( $category_data['url'] ); ?>" value="<?php echo esc_attr( $category_data['slug'] ); ?>"><?php echo esc_html( $category_data['name'] ); ?></option>
				<?php } ?>
				</select>
			</div>
		</div>
		<?php

		$content = ob_get_clean();

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

	public function add_google_fonts( $google_fonts, $blockName, $attributes ) {
        if ( $blockName === $this->block_name && isset( $attributes['blockID'] ) ) {
			$typo_key = $this->field_name . 'typo';

            if ( ! $this->dropdown_status_is_hidden( $attributes ) && isset( $attributes[ 'enableCustomFont' ] ) && $attributes[ 'enableCustomFont' ] ) {
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

		if ( $this->dropdown_status_is_hidden( $attributes ) ) {
			return '';
		}

		global $wpps_blocks_styles;

		$blockID = $attributes['blockID'];

		$stylecss = '
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_scope {
	'. ( isset( $attributes['normalBackgroundColor'] ) ? 'background-color: '.$attributes['normalBackgroundColor'].';' : '' ) .'
	'. ( isset( $attributes['normalBorderColor'] ) ? 'border-color: '.$attributes['normalBorderColor'].';' : '' ) .'
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_styles( $this->field_name, $attributes, true ) : '' ) .'
	'. ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ? $wpps_blocks_styles->border_radius_styles( $this->field_name, $attributes, true ) : '' ) .'
}
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_facade_label {
	'. ( isset( $attributes['normalTextColor'] ) ? 'color: '.$attributes['normalTextColor'].';' : '' ) .'
	'. ( isset( $attributes['enableCustomFont'] ) && $attributes['enableCustomFont'] ? $wpps_blocks_styles->typography_styles( $this->field_name.'typo', $attributes ) : '' ) .'
}';

		if ( isset( $attributes['enableCustomBorder'] ) && $attributes['enableCustomBorder'] ) {
			$border_width = 0;
			if ( isset( $attributes[ $this->field_name . 'borderTop' ] ) ) {
				$border_width += $attributes[ $this->field_name . 'borderTop' ];
			}
			if ( isset( $attributes[ $this->field_name . 'borderBottom' ] ) ) {
				$border_width += $attributes[ $this->field_name . 'borderBottom' ];
			}
			if ( $border_width > 0 ) {
				$stylecss .= '
.wpps_container #wpps_cat_dropdown-'.$blockID.' .wpps_nav_facade_label {
	margin-top: -'. ($border_width / 2 ) .'px;
}';
			}
		}

		if ( isset( $attributes['hoverBackgroundColor'] ) || isset( $attributes['hoverBorderColor'] ) ) {
			$stylecss .= '
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_scope:hover {
	'. ( isset( $attributes['hoverBackgroundColor'] ) ? 'background-color: '.$attributes['hoverBackgroundColor'].'!important;' : '' ) .'
	'. ( isset( $attributes['hoverBorderColor'] ) ? 'border-color: '.$attributes['hoverBorderColor'].'!important;' : '' ) .'
}';
		}

		if ( isset( $attributes['hoverTextColor'] ) ) {
			$stylecss .= '
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_scope:hover .wpps_nav_facade_label {
	color: '.$attributes['hoverTextColor'].'!important;
}';
		}

		if ( isset( $attributes['normalIconColor'] ) ) {
			$stylecss .= '
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_scope svg,
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_scope svg * {
	color: '.$attributes['normalIconColor'].';
}';
		}

		if ( isset( $attributes['hoverIconColor'] ) ) {
			$stylecss .= '
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_scope:hover svg,
#wpps_cat_dropdown-'.$blockID.' .wpps_nav_scope:hover svg * {
	color: '.$attributes['hoverIconColor'].'!important;
}';
		}

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
