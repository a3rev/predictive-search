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
function wpps_render_block_read_more( $attributes, $content, $block ) {
	global $psobject;

	if ( empty( $psobject ) ) {
		return '';
	}

	$item_link = '';

	switch ( $psobject->object ) {
		case 'post' :
			$item_link = get_the_permalink( $psobject->id );
			break;
		case 'taxonomy' :
			$item_link = get_term_link( (int)$psobject->id, $psobject->type );
			break;
		default :
			$item_link = apply_filters( 'wpps_block_item_readmore', $item_link, $psobject, $attributes );
			break;
	}

	if ( empty( $item_link ) ) {
		return '';
	}

	$classes = '';
	if ( isset( $attributes['textAlign'] ) ) {
		$classes .= "has-text-align-{$attributes['textAlign']}";
	}
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classes ) );
	$more_text          = ! empty( $attributes['content'] ) ? wp_kses_post( $attributes['content'] ) : __( 'Read more' );
	return sprintf(
		'<a %1s href="%2s" target="%3s">%4s</a>',
		$wrapper_attributes,
		$item_link,
		esc_attr( $attributes['linkTarget'] ),
		$more_text
	);
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_read_more() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_read_more',
		)
	);
}
add_action( 'init', 'wpps_register_block_read_more' );
