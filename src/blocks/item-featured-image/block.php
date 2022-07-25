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
function wpps_render_block_item_featured_image( $attributes, $content, $block ) {
	global $psobject;

	if ( empty( $psobject ) ) {
		return '';
	}

	$item_link      = '#';
	$alt            = '';
	$featured_image = apply_filters( 'wpps_block_item_featured_image_placeholder', '<image src="' . WPPS_URL . '/src/blocks/item-featured-image/placeholder.png" />' );

	$is_link        = isset( $attributes['isLink'] ) && $attributes['isLink'];
	$size_slug      = isset( $attributes['sizeSlug'] ) ? $attributes['sizeSlug'] : 'post-thumbnail';

	switch ( $psobject->object ) {
		case 'post' :
			$item_link      = get_the_permalink( $psobject->id );
			$alt            = trim( strip_tags( get_the_title( $psobject->id ) ) );
			$attr           = $is_link ? array( 'alt' => $alt ) : array();
			$featured_image = has_post_thumbnail( $psobject->id ) ? get_the_post_thumbnail( $psobject->id, $size_slug, $attr ) : $featured_image;
			break;
		case 'taxonomy' :
			$featured_image = '';
			break;
		default :
			list ( $featured_image, $item_link ) = apply_filters( 'wpps_block_item_featured_image', array( $featured_image, $item_link ), $psobject, $attributes );
			break;
	}

	if ( empty( $featured_image ) ) {
		return '';
	}

	$classes = 'wp-block-post-featured-image';
	
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classes ) );
	if ( $is_link ) {
		$featured_image = sprintf( 
			'<a href="%1s" target="%2s">%3s</a>',
			$item_link,
			esc_attr( $attributes['linkTarget'] ),
			$featured_image,
		);
	}

	$has_width  = ! empty( $attributes['width'] );
	$has_height = ! empty( $attributes['height'] );
	if ( ! $has_height && ! $has_width ) {
		return "<figure $wrapper_attributes>$featured_image</figure>";
	}

	if ( $has_width ) {
		$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classes, 'style' => "width:{$attributes['width']};" ) );
	}

	if ( $has_height ) {
		$image_styles = "height:{$attributes['height']};";
		if ( ! empty( $attributes['scale'] ) ) {
			$image_styles .= "object-fit:{$attributes['scale']};";
		}
		$featured_image = str_replace( 'src=', 'style="' . esc_attr( $image_styles ) . '" src=', $featured_image );
	}

	return "<figure $wrapper_attributes>$featured_image</figure>";
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_item_featured_image() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_item_featured_image',
		)
	);
}
add_action( 'init', 'wpps_register_block_item_featured_image' );
