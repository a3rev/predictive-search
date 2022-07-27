<?php
/**
 * Server-side rendering of the `core/post-title` block.
 *
 * @package WordPress
 */

/**
 * Renders the `core/post-title` block on the server.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 *
 * @return string Returns the filtered post title for the current post wrapped inside "h1" tags.
 */
function wpps_render_block_item_excerpt( $attributes, $content, $block ) {
	global $psobject;

	if ( empty( $psobject ) ) {
		return '';
	}

	$excerpt   = '';
	$item_link = '';

	switch ( $psobject->object ) {
		case 'post' :
			$excerpt = get_the_excerpt( $psobject->id );
			$item_link = get_the_permalink( $psobject->id );
			break;
		case 'taxonomy' :
			$item_link = get_term_link( (int)$psobject->id, $psobject->type );
			break;
		default :
			list ( $excerpt, $item_link) = apply_filters( 'wpps_block_item_excerpt', array( $excerpt, $item_link ), $psobject, $attributes );
			break;
	}

	$more_text           = ! empty( $attributes['moreText'] ) ? '<a class="wp-block-wpps-result-item-excerpt__more-link" href="' . esc_url( $item_link ) . '" target="' . esc_attr( $attributes['linkTarget'] ) . '" >' . wp_kses_post( $attributes['moreText'] ) . '</a>' : '';
	$filter_excerpt_more = function( $more ) use ( $more_text ) {
		return empty( $more_text ) ? $more : '';
	};

	add_filter( 'excerpt_more', $filter_excerpt_more );

	if ( empty( $excerpt ) && empty( $more_text ) ) {
		return '';
	}

	$character_count = ! empty( $attributes['charactersCount'] ) ? $attributes['charactersCount'] : 100;
	if ( ! empty( $excerpt ) ) {
		$excerpt = \A3Rev\WPPredictiveSearch\Functions::woops_limit_words( strip_tags( \A3Rev\WPPredictiveSearch\Functions::strip_shortcodes( strip_shortcodes ( $excerpt ) ) ), $character_count );
	}

	$classes = '';
	if ( isset( $attributes['textAlign'] ) ) {
		$classes .= "has-text-align-{$attributes['textAlign']}";
	}
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classes ) );

	$content               = '<div class="wp-block-wpps-result-item-excerpt__excerpt">' . $excerpt;
	$show_more_on_new_line = ! isset( $attributes['showMoreOnNewLine'] ) || $attributes['showMoreOnNewLine'];
	if ( $show_more_on_new_line && ! empty( $more_text ) ) {
		$content .= '</div><div class="wp-block-wpps-result-item-excerpt__more-text">' . $more_text . '</div>';
	} else {
		$content .= " $more_text</div>";
	}

	remove_filter( 'excerpt_more', $filter_excerpt_more );
	return sprintf( '<div %1$s>%2$s</div>', $wrapper_attributes, $content );
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_item_excerpt() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_item_excerpt',
		)
	);
}
add_action( 'init', 'wpps_register_block_item_excerpt' );
