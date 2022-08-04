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
function wpps_render_block_query_results( $attributes, $content, $block ) {
    list( 'search_keyword' => $search_keyword, 'search_in' => $search_in ) = \A3Rev\WPPredictiveSearch\Functions::get_results_vars_values();

    if ( $search_keyword == '' || $search_in == '' ) {
        return '';
    }

    $tag_name = 'div';
    $tag_name = empty( $attributes['tagName'] ) ? 'div' : $attributes['tagName'];

    $wrapper_attributes = get_block_wrapper_attributes();

    ob_start();
    \A3Rev\WPPredictiveSearch\Results::more_results();
    $more_results = ob_get_clean();

    ob_start();
    \A3Rev\WPPredictiveSearch\Results::inline_scripts();
    $inline_scripts = ob_get_clean();

    return sprintf(
        '<div id="ps_results_container" class="wpps">
            <%1$s %2$s>
                %3$s
                %4$s
            </%1$s>
        </div>%5$s',
        $tag_name,
        $wrapper_attributes,
        $content,
        $more_results,
        $inline_scripts
    );
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_query_results() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_query_results',
		)
	);
}
add_action( 'init', 'wpps_register_block_query_results' );
