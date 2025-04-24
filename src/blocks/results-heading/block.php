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
function wpps_render_block_results_heading( $attributes, $content, $block ) {
    list( 'search_keyword' => $search_keyword ) = \A3Rev\WPPredictiveSearch\Functions::get_results_vars_values();

    if ( $search_keyword == '' ) {
        return '';
    }

    global $wp_predictive_search;
    global $ps_current_search_in;
    $items_search_default = $wp_predictive_search->get_items_search();

	$search_object = '';
	if ( ! empty( $items_search_default ) && ! empty( $ps_current_search_in ) && isset( $items_search_default[ $ps_current_search_in ] ) ) {
		$search_object = '<span class="ps_heading_search_in_name">' . $items_search_default[ $ps_current_search_in ]['name'] . '</span>';
	}

    $content = str_replace( '%%object%%', $search_object, $content );
    $content = str_replace( '%%keyword%%', $search_keyword, $content );

    return $content;
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_results_heading() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_results_heading',
		)
	);
}
add_action( 'init', 'wpps_register_block_results_heading' );
