<?php
/**
 * The Template for Predictive Search plugin
 *
 * Override this template by copying it to yourtheme/ps/results-page/item.php
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<script type="text/template" id="wp_psearch_result_itemTpl">{{ if ( image_url != null && image_url != '' ) { }}<span class="rs_rs_avatar"><a href="{{= url }}" aria-label="{{= title }}"><img src="{{= image_url }}" /></a></span>{{ } }}
	<div class="rs_content {{ if ( image_url == null || image_url == '' ) { }}no_image{{ } }}">
		<a href="{{= url }}" class="rs_rs_name">{{= title }}</a>
		<?php do_action( 'wpps_all_results_tpl_item_title_after' ); ?>
		{{ if ( description != null && description != '' ) { }}<div class="rs_rs_description">{{= description }}</div>{{ } }}
		<?php do_action( 'wpps_all_results_tpl_item_desc_after' ); ?>
		{{ if ( categories.length > 0 ) { }}
			<div class="rs_rs_meta rs_rs_cat posted_in">
				<?php wpps_ict_t_e( 'Category', __('Category', 'wp-predictive-search' ) ); ?>:
				{{ var number_cat = 0; }}
				{{ _.each( categories, function( cat_data ) { number_cat++; }}
					{{ if ( number_cat > 1 ) { }}, {{ } }}<a href="{{= cat_data.url }}">{{= cat_data.name }}</a>
				{{ }); }}
			</div>
		{{ } }}
		{{ if ( tags.length > 0 ) { }}
			<div class="rs_rs_meta rs_rs_tag tagged_as">
				<?php wpps_ict_t_e( 'Tags', __('Tags', 'wp-predictive-search' ) ); ?>:
				{{ var number_tag = 0; }}
				{{ _.each( tags, function( tag_data ) { number_tag++; }}
					{{ if ( number_tag > 1 ) { }}, {{ } }}<a href="{{= tag_data.url }}">{{= tag_data.name }}</a>
				{{ }); }}
			</div>
		{{ } }}
		<?php do_action( 'wpps_all_results_tpl_item_footer' ); ?>
	</div>
</script>
