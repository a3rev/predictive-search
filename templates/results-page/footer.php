<?php
/**
 * The Template for Predictive Search plugin
 *
 * Override this template by copying it to yourtheme/ps/results-page/footer.php
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/template" id="wp_psearch_result_footerTpl"><div style="clear:both"></div>
	{{ if ( next_page_number > 1 ) { }}
	<div id="ps_more_check"></div>
	{{ } else if ( total_items == 0 && first_load ) { }}
	<p style="text-align:center"><?php wpps_ict_t_e( 'No Result Text', __('Nothing Found! Please refine your search and try again.', 'wp-predictive-search' ) ); ?></p>
	{{ } }}
</script>