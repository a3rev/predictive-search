<?php
/**
 * The Template for Predictive Search plugin
 *
 * Override this template by copying it to yourtheme/ps/popup/footer-sidebar.php
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/template" id="wp_psearch_footerSidebarTpl"><div rel="more_result" class="more_result">
		<span><?php echo sprintf( wpps_ict_t__( 'More result Text - Sidebar', $popup_seemore_text ), '{{= title }}' ); ?></span>
		{{ if ( description != null && description != '' ) { }}{{= description }}{{ } }}
</div></script>