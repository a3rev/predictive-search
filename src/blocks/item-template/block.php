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
function wpps_render_block_item_template( $attributes, $content, $block ) {
	list( 'search_keyword' => $search_keyword ) = \A3Rev\WPPredictiveSearch\Functions::get_results_vars_values();

    if ( $search_keyword == '' ) {
        return '';
    }

    // Get an instance of the current Post Template block.
	$block_instance = $block->parsed_block;

	// Set the block name to one that does not correspond to an existing registered block.
	// This ensures that for the inner instances of the Post Template block, we do not render any block supports.
	$block_instance['blockName'] = 'core/null';

	$item_card = $block_instance;

	setcookie( 'ps_results_item_card', json_encode( $item_card ), time() + 3600, '/' );

	$classnames = 'wp-block-post-template';
	if ( isset( $block->context['columns'] ) && $block->context['columns'] > 1 ) {
		$classnames .= " is-flex-container columns-{$block->context['columns']}";
	}

	$perpage = 12;
	if ( isset( $block->context['perPage'] ) && $block->context['perPage'] >= 1 ) {
		$perpage = $block->context['perPage'];
	}

	$wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $classnames ) );

	return sprintf(
		'<div id="ps_items_container" %1$s data-perpage="%2$s"></div>',
		$wrapper_attributes,
		$perpage
	);
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_item_template() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_item_template',
		)
	);
}
add_action( 'init', 'wpps_register_block_item_template' );
