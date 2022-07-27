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
function wpps_render_block_item_title( $attributes, $content, $block ) {
	global $psobject;

	if ( empty( $psobject ) ) {
		return '';
	}

	$title     = isset( $psobject->title ) ? $psobject->title : '';
	$item_link = '#';

	switch ( $psobject->object ) {
		case 'post' :
			$title = ! empty( $title ) ? $title : get_the_title( $psobject->id );
			$item_link = get_the_permalink( $psobject->id );
			break;
		case 'taxonomy' :
			$title = ! empty( $title ) ? $title : get_term_name( $psobject->id, $psobject->type );
			$item_link = get_term_link( (int)$psobject->id, $psobject->type );
			break;
		default :
			list ( $list, $item_link) = apply_filters( 'wpps_block_item_title', array( $title, $item_link ), $psobject, $attributes );
			break;
	}

	if ( empty( $title ) ) {
		return '';
	}

	$character_count = ! empty( $attributes['charactersCount'] ) ? $attributes['charactersCount'] : 100;
	$title = \A3Rev\WPPredictiveSearch\Functions::woops_limit_words( strip_tags( \A3Rev\WPPredictiveSearch\Functions::strip_shortcodes( strip_shortcodes ( $title ) ) ), $character_count );

	$tag_name         = 'h2';
	$align_class_name = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";
	// $align_class_name .= ' wp-block-post-title';

	if ( isset( $attributes['level'] ) ) {
		$tag_name = 0 === $attributes['level'] ? 'p' : 'h' . $attributes['level'];
	}

	if ( isset( $attributes['isLink'] ) && $attributes['isLink'] ) {
		$title = sprintf( '<a href="%1$s" target="%2$s" rel="%3$s">%4$s</a>', $item_link, esc_attr( $attributes['linkTarget'] ), esc_attr( $attributes['rel'] ), $title );
	}
	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $align_class_name ) );

	return sprintf(
		'<%1$s %2$s>%3$s</%1$s>',
		$tag_name,
		$wrapper_attributes,
		$title
	);
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_item_title() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_item_title',
		)
	);
}
add_action( 'init', 'wpps_register_block_item_title' );
