<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

namespace A3Rev\WPPredictiveSearch\Blocks;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Search_Form extends Frontend {

	protected $block_name = 'wp-predictive-search/form';
    protected $field_name = '';

	public function __construct() {
		add_action( 'init', array( $this, 'register_block' ), 20 );
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

		$id = 'wpps-'.$blockID;

		$classname = array(
			'wpps_shortcode_container',
			'wpps_block',
			'wpps_block-' . $blockID,
		);
		if ( isset( $attributes['className'] ) ) $classname[] = $attributes['className'];
		if ( isset( $attributes['align'] ) ) $classname[] = 'align-' . $attributes['align'];
		$classname = implode( ' ', $classname );

		$bar_classname = array(
			'wpps_bar',
			'wpps_bar-' . $blockID,
		);
		if ( isset( $attributes['mIconEnable'] ) && $attributes['mIconEnable'] ) $bar_classname[] = 'search_icon_desktop_only';
		if ( isset( $attributes['tabletmIconEnable'] ) && $attributes['tabletmIconEnable'] ) $bar_classname[] = 'search_icon_tablet_only';
		if ( ! isset( $attributes['mobilemIconEnable'] ) || $attributes['mobilemIconEnable'] ) $bar_classname[] = 'search_icon_only'; // default is true
		$bar_classname = implode( ' ', $bar_classname );

		$row          = 0;
		$search_list  = array();
		$number_items = array();
		$items_search = array();
		$order_items  = array();

		if ( isset( $attributes['numberPostTypes'] ) && ! empty( $attributes['numberPostTypes'] ) ) {
			foreach ( $attributes['numberPostTypes'] as $posttype_slug => $posttype_items ) {
				if ( ! empty( $posttype_items ) && $posttype_items > 0 ) {
					$items_search[$posttype_slug] = $posttype_items;
					$order_items[$posttype_slug] = 0;
				}
			}
		} else {
			$items_search['post'] = 6;
			$items_search['page'] = 6;
			$order_items['post'] = 0;
			$order_items['page'] = 0;
		}

		if ( isset( $attributes['numberCustomTypes'] ) && ! empty( $attributes['numberCustomTypes'] ) ) {
			foreach ( $attributes['numberCustomTypes'] as $custom_key => $custom_items ) {
				if ( ! empty( $custom_items ) && $custom_items > 0 ) {
					$items_search[$custom_key] = $custom_items;
					$order_items[$custom_key] = 0;
				}
			}
		}

		if ( isset( $attributes['numberTaxonomies'] ) && ! empty( $attributes['numberTaxonomies'] ) ) {
			foreach ( $attributes['numberTaxonomies'] as $taxonomy => $taxonomy_items ) {
				if ( ! empty( $taxonomy_items ) && $taxonomy_items > 0 ) {
					$items_search[$taxonomy] = $taxonomy_items;
					$order_items[$taxonomy] = 0;
				}
			}
		} else {
			$items_search['category'] = 6;
			$items_search['post_tag'] = 6;
			$order_items['category'] = 0;
			$order_items['post_tag'] = 0;
		}

		if ( empty( $items_search ) ) {
			$items_search = array( 'post' => 6, 'page' => 6, 'category' => 6, 'post_tag' => 6 );
		}

		if ( isset( $attributes['orderItems'] ) && ! empty( $attributes['orderItems'] ) ) {
			$order_items = array_merge( $order_items, $attributes['orderItems'] );
		}

		// sort based order
		arsort( $order_items, SORT_NUMERIC );

		// remove different with items_search
		$diff_items = array_diff_key( $order_items, $items_search );
		if ( ! empty( $diff_items ) ) {
			foreach ( $diff_items as $key => $order ) {
				unset( $order_items[$key] );
			}
		}

		// update items_search with new order
		$items_search = array_merge( $order_items, $items_search );
		
		foreach ( $items_search as $key => $number ) {
			$number_items[$key] = $number;
			$row += $number;
			$row++;
			$search_list[] = $key;
		}

		$search_in = json_encode($number_items);

		ob_start();

		echo $this->render_inline_css( $attributes, '' );
		?>
		<div id="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( $classname ); ?>">
			<div class="<?php echo esc_attr( $bar_classname ); ?>"
				data-ps-id="<?php echo esc_attr( $blockID ); ?>"
				data-ps-row="<?php echo esc_attr( $row ); ?>"

				<?php if ( count( $search_list ) > 0 ) { ?>
				data-ps-search_in="<?php echo esc_attr( $search_list[0] ); ?>"
				data-ps-search_other="<?php echo esc_attr( implode( ',', $search_list ) ); ?>"
				<?php } ?>

				<?php if ( $search_in != '' ) { ?>
				data-ps-popup_search_in="<?php echo esc_attr( $search_in ); ?>"
				<?php } ?>

				<?php if ( class_exists('SitePress') ) { ?>
				data-ps-lang="<?php echo ICL_LANGUAGE_CODE; ?>"
				<?php } ?>

				data-ps-widget_template="custom"
			>
				<?php echo $content; ?>
			</div>
			<div style="clear: both;"></div>
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

    public function render_inline_css_head( $blockName, $attributes ) {
        $this->block_inline_css_head( $this->block_name, $blockName, $attributes );
	}

	public function inline_css( $attributes ) {
		global $wpps_blocks_styles;

		$blockID = $attributes['blockID'];

		// Validate Desktop Width
		if ( ! isset( $attributes['widthUnit'] ) ) {
            $widthUnit = '%';
        } else {
        	$widthUnit = $attributes['widthUnit'];
        }
        if ( ! isset( $attributes['width'] ) ) {
            $width = 100;
        } else {
        	$width = $attributes['width'];
        }
		if ( 'px' === $widthUnit && $width < 200 ) {
			$width = 200;
		} else if ( 'px' !== $widthUnit && $width > 100 ) {
			$width = 100;
		}

		// Validate Tablet Width
		if ( ! isset( $attributes['tabletwidthUnit'] ) ) {
            $tabletwidthUnit = '%';
        } else {
        	$tabletwidthUnit = $attributes['tabletwidthUnit'];
        }
        if ( ! isset( $attributes['tabletwidth'] ) ) {
            $tabletwidth = 100;
        } else {
        	$tabletwidth = $attributes['tabletwidth'];
        }
		if ( 'px' === $tabletwidthUnit && $tabletwidth < 200 ) {
			$tabletwidth = 200;
		} else if ( 'px' !== $tabletwidthUnit && $tabletwidth > 100 ) {
			$tabletwidth = 100;
		}

		if ( ! isset( $attributes['align'] ) ) {
            $align = 'none';
        } else {
        	$align = $attributes['align'];
        }

        $stylecss = '
#wpps-'.$blockID.' {
    ' . $wpps_blocks_styles->spacing_styles( $this->field_name, $attributes ) . '
}';

		if ( isset( $attributes['mIconEnable'] ) && $attributes['mIconEnable'] ) {
			$stylecss .= '
@media only screen and (min-width: 1025px) {
	.wpps_mobile_popup .wpps_container-'.$blockID.' {
		width: '.$width.$widthUnit.'!important;
		max-width: none !important;
	}
}
';
		} else {
			$stylecss .= '
@media only screen and (min-width: 1025px) {
	#wpps-'.$blockID.' .wpps_bar {
		max-width: '.$width.$widthUnit.';
	}
}
';
		}

		if ( isset( $attributes['mIconEnable'] ) && $attributes['mIconEnable'] && 'right' === $align ) {
			$stylecss .= '
@media only screen and (min-width: 1025px) {
	.wpps_mobile_popup .wpps_container-'.$blockID.' {
		margin-left: calc( 100% - '.$width.$widthUnit.' )!important;
	}
}
';
		} else if ( isset( $attributes['mIconEnable'] ) && $attributes['mIconEnable'] && 'center' === $align ) {
			$stylecss .= '
@media only screen and (min-width: 1025px) {
	.wpps_mobile_popup .wpps_container-'.$blockID.' {
		margin-left: calc( 50% - ( '.$width.$widthUnit.' / 2 ) )!important;
	}
}
';
		}

		if ( isset( $attributes['tabletmIconEnable'] ) && $attributes['tabletmIconEnable'] ) {
			$stylecss .= '
@media only screen and (max-width: 1024px) and (min-width: 681px) {
	.wpps_mobile_popup .wpps_container-'.$blockID.' {
		width: '.$tabletwidth.$tabletwidthUnit.'!important;
		max-width: none !important;
	}
}
';
		} else {
			$stylecss .= '
@media only screen and (max-width: 1024px) and (min-width: 681px) {
	#wpps-'.$blockID.' .wpps_bar {
		max-width: '.$tabletwidth.$tabletwidthUnit.';
	}
}
';
		}

		if ( isset( $attributes['tabletmIconEnable'] ) && $attributes['tabletmIconEnable'] && 'right' === $align ) {
			$stylecss .= '
@media only screen and (max-width: 1024px) and (min-width: 681px) {
	.wpps_mobile_popup .wpps_container-'.$blockID.' {
		margin-left: calc( 100% - '.$tabletwidth.$tabletwidthUnit.' )!important;
	}
}
';
		} else if ( isset( $attributes['tabletmIconEnable'] ) && $attributes['tabletmIconEnable'] && 'center' === $align ) {
			$stylecss .= '
@media only screen and (max-width: 1024px) and (min-width: 681px) {
	.wpps_mobile_popup .wpps_container-'.$blockID.' {
		margin-left: calc( 50% - ( '.$tabletwidth.$tabletwidthUnit.' / 2 ) )!important;
	}
}
';
		}

		return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
	}
}
