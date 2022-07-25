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
function wpps_render_block_item_terms( $attributes, $content, $block ) {
	global $psobject;

	if ( empty( $psobject ) || ! isset( $attributes['term'] ) ) {
		return '';
	}

	$term = apply_filters( 'wpps_block_item_terms_' . $attributes['term'], $attributes['term'] );

	switch ( $psobject->object ) {

		case 'taxonomy' :
			$term = '';
			break;
		default :
			$term = apply_filters( 'wpps_block_item_title', $term, $psobject, $attributes );
			break;
	}

	if ( empty( $term ) ) {
		return '';
	}

	if ( ! is_taxonomy_viewable( $term ) ) {
		return '';
	}

	$post_terms = get_the_terms( $psobject->id, $term );
	if ( is_wp_error( $post_terms ) || empty( $post_terms ) ) {
		return '';
	}

	$classes = 'taxonomy-' . $term;
	$classes .= ' wp-block-post-terms';
	if ( isset( $attributes['textAlign'] ) ) {
		$classes .= ' has-text-align-' . $attributes['textAlign'];
	}

	$separator = empty( $attributes['separator'] ) ? ' ' : $attributes['separator'];

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classes ) );

	$prefix = "<div $wrapper_attributes>";
	if ( isset( $attributes['prefix'] ) && $attributes['prefix'] ) {
		$prefix .= '<span class="wp-block-post-terms__prefix wp-block-wpps-result-item-terms__prefix">' . $attributes['prefix'] . '</span>';
	}

	$suffix = '</div>';
	if ( isset( $attributes['suffix'] ) && $attributes['suffix'] ) {
		$suffix = '<span class="wp-block-post-terms__suffix wp-block-wpps-result-item-terms__suffix">' . $attributes['suffix'] . '</span>' . $suffix;
	}

	return get_the_term_list(
		$psobject->id,
		$term,
		wp_kses_post( $prefix ),
		'<span class="wp-block-post-terms__separator wp-block-wpps-result-item-terms__separator">' . esc_html( $separator ) . '</span>',
		wp_kses_post( $suffix )
	);
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_item_terms() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_item_terms',
		)
	);
}
add_action( 'init', 'wpps_register_block_item_terms' );
