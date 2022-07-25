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

    // do_blocks( '<!-- wp:template-part {"slug":"ps-all-results-item","theme":"'. wp_get_theme()->get_stylesheet() .'"} /-->' );
    ob_start();
    ?>
    <div id="ps_results_container" class="wpps">
        <<?php echo $tag_name; ?> <?php echo $wrapper_attributes; ?>>
            <?php echo $content; ?>
            <?php echo \A3Rev\WPPredictiveSearch\Results::more_results(); ?>
        </<?php echo $tag_name; ?>>
    </div>
    <?php echo \A3Rev\WPPredictiveSearch\Results::inline_scripts(); ?>
    <?php
    $content = ob_get_clean();

	return $content;
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
