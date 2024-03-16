<?php
/**
 * The Template for Predictive Search plugin
 *
 * Override this template by copying it to yourtheme/ps/popup/item.php
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/template" id="wp_psearch_itemTpl"><div class="ajax_search_content">
	<div class="result_row">
		{{ if ( image_url != null && image_url != '' ) { }}<span class="rs_avatar"><a href="{{= url }}" aria-label="{{= title }}"><img src="{{= image_url }}" /></a></span>{{ } }}
		<div class="rs_content_popup {{ if ( image_url == null || image_url == '' ) { }}no_image{{ } }}">
			<a href="{{= url }}" aria-label="{{= title }}">
				<span class="rs_name">{{= title }}</span>
			</a>
			<?php do_action( 'wpps_popup_tpl_item_meta' ); ?>
			<a href="{{= url }}" aria-label="{{= title }}">
				{{ if ( description != null && description != '' ) { }}<span class="rs_description">{{= description }}</span>{{ } }}
			</a>
			<?php do_action( 'wpps_popup_tpl_item_desc_after' ); ?>
			<?php do_action( 'wpps_popup_tpl_item_category_before' ); ?>
			{{ if ( categories.length > 0 ) { }}
				<span class="rs_cat posted_in">
					<?php wpps_ict_t_e( 'Category', __('Category', 'wp-predictive-search' ) ); ?>:
					{{ var number_cat = 0; }}
					{{ _.each( categories, function( cat_data ) { number_cat++; }}
						{{ if ( number_cat > 1 ) { }}, {{ } }}<a class="rs_cat_link" href="{{= cat_data.url }}">{{= cat_data.name }}</a>
					{{ }); }}
				</span>
			{{ } }}
			<?php do_action( 'wpps_popup_tpl_item_footer' ); ?>
		</div>
	</div>
</div></script>
