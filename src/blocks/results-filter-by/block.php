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
function wpps_render_block_results_filter_by( $attributes, $content, $block ) {
    list( 'search_keyword' => $search_keyword, 'search_in' => $search_in, 'search_other' => $search_other, 'cat_in' => $cat_in, 'in_taxonomy' => $in_taxonomy ) = \A3Rev\WPPredictiveSearch\Functions::get_results_vars_values();

    if ( $search_keyword == '' ) {
        return '';
    }

    $blockID = $attributes['blockID'];
    $enableDivider = isset( $attributes['enableDivider'] ) ? $attributes['enableDivider'] : true;

    global $wp_predictive_search;
    global $wpps_search_page_id, $ps_search_list, $ps_current_search_in;

    $items_search_default = $wp_predictive_search->get_items_search();
    $permalink_structure  = get_option( 'permalink_structure' );

    if ( $permalink_structure == '')
        $other_link_search = get_permalink( $wpps_search_page_id ).'&rs='. urlencode( $search_keyword );
    else
        $other_link_search = rtrim( get_permalink( $wpps_search_page_id ), '/' ).'/keyword/'. urlencode( $search_keyword );

    $line_vertical = '';

    $container_class_name = empty( $attributes['textAlign'] ) ? '' : "has-text-align-{$attributes['textAlign']}";
    $container_class_name .= ' wpps-result-filter-by-container';
    $container_class_name .= ' wpps-result-filter-by-container-'.$blockID;

    $filter_class_name = 'rs_result_other_item';
    $wrapper_attributes = get_block_wrapper_attributes( array( 'class' => $filter_class_name ) );

    ob_start();

    $inline_css = wpps_block_results_filter_by_inline_css( $attributes );

    echo $content;

    if (  ! empty( $inline_css ) ) {
        echo '<style>'. esc_html( $inline_css ).'</style>';
    }
?>
    <div class="<?php echo $container_class_name; ?>">
<?php
    foreach ( $ps_search_list as $search_other_item ) {
        if ( ! isset( $items_search_default[ $search_other_item ] ) ) continue;

        if ( $permalink_structure == '' ) {
?>
        <?php echo wp_kses_post( $line_vertical ); ?>
        <span <?php echo $wrapper_attributes; ?>>
            <a class="ps_navigation ps_navigation<?php echo esc_attr( $search_other_item ); ?>" href="<?php echo esc_url( $other_link_search . '&search_in=' . $search_other_item . '&cat_in=' . $cat_in . '&in_taxonomy=' . $in_taxonomy . '&search_other=' . $search_other ); ?>" data-href="?page_id=<?php echo esc_attr( $wpps_search_page_id ); ?>&rs=<?php echo esc_attr( urlencode($search_keyword) ); ?>&search_in=<?php echo esc_attr( $search_other_item ); ?>&cat_in=<?php echo esc_attr( $cat_in ); ?>&in_taxonomy=<?php echo esc_attr( $in_taxonomy ); ?>&search_other=<?php echo esc_attr( $search_other ); ?>" alt=""><?php echo esc_html( $items_search_default[$search_other_item]['name'] ); ?></a>
        </span>
<?php
        } else {
?>
        <?php echo wp_kses_post( $line_vertical ); ?>
        <span <?php echo $wrapper_attributes; ?>>
            <a class="ps_navigation ps_navigation<?php echo esc_attr( $search_other_item ); ?>" href="<?php echo esc_url( $other_link_search . '/search-in/' . $search_other_item . '/cat-in/' . $cat_in . '/in-taxonomy/' . $in_taxonomy . '/search-other/' . $search_other ); ?>" data-href="keyword/<?php echo esc_attr( urlencode($search_keyword) ); ?>/search-in/<?php echo esc_attr( $search_other_item ); ?>/cat-in/<?php echo esc_attr( $cat_in ); ?>/in-taxonomy/<?php echo esc_attr( $in_taxonomy ); ?>/search-other/<?php echo esc_attr( $search_other ); ?>" alt=""><?php echo esc_html( $items_search_default[$search_other_item]['name'] ); ?></a>
        </span>
<?php
        }

        if ( $enableDivider ) {
            $line_vertical = '<span class="ps_navigation_divider"></span>';
        }
    }
?>
    </div>
<?php
    $output = ob_get_clean();

    return $output;
}

function wpps_block_results_filter_by_inline_css( $attributes ) {
    $blockID = $attributes['blockID'];
    $enableDivider = isset( $attributes['enableDivider'] ) ? $attributes['enableDivider'] : true;

    $stylecss = '';

    if ( $enableDivider ) {
        $stylecss .= '
.wpps-result-filter-by-container-'.$blockID.' .ps_navigation_divider {
    '. ( isset( $attributes['dividerSize'] ) ? 'border-left-width:'.$attributes['dividerSize'].'px;' : '' ) .'
    '. ( isset( $attributes['dividerColor'] ) ? 'border-left-color:'.$attributes['dividerColor'].';' : '' ) .'
    '. ( isset( $attributes['dividerSpace'] ) ? 'margin:0 '.$attributes['dividerSpace'].'px;' : '' ) .'
}';
    }

    if ( isset( $attributes['itemActiveBgColor'] ) ) {
        $stylecss .= '
.wpps-result-filter-by-container-'.$blockID.' .rs_result_other_item_activated,
.wpps-result-filter-by-container-'.$blockID.' .rs_result_other_item:hover {
    background-color: '.$attributes['itemActiveBgColor'].'!important;
}
';
    }

    if ( isset( $attributes['itemActiveColor'] ) ) {
        $stylecss .= '
.wpps-result-filter-by-container-'.$blockID.' .rs_result_other_item_activated a,
.wpps-result-filter-by-container-'.$blockID.' .rs_result_other_item:hover a {
    color: '.$attributes['itemActiveColor'].'!important;
}
';
    }

    return $stylecss; /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */
}

/**
 * Registers the `core/post-title` block on the server.
 */
function wpps_register_block_results_filter_by() {
	register_block_type(
		__DIR__ . '/block.json',
		array(
			'render_callback' => 'wpps_render_block_results_filter_by',
		)
	);
}
add_action( 'init', 'wpps_register_block_results_filter_by' );
