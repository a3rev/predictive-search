<?php
/**
 * Pattern with Heading and Predictive Search
 *
 * @package gutenberg
 */

return array(
	'title'      => __( 'Heading + Search Bar', 'wp-predictive-search' ),
	'categories' => array( 'predictive-search' ),
	'content'    => '<!-- wp:group {"style":{"color":{"gradient":"linear-gradient(135deg,rgb(236,197,90) 0%,rgb(238,103,7) 100%)"}}} -->
	<div class="wp-block-group has-background" style="background:linear-gradient(135deg,rgb(236,197,90) 0%,rgb(238,103,7) 100%)"><div class="wp-block-group__inner-container"><!-- wp:heading {"style":{"typography":{"fontSize":20,"lineHeight":"1"}}} -->
	<h2 style="line-height:1;font-size:20px">Search Posts</h2>
	<!-- /wp:heading -->

	<!-- wp:wp-predictive-search/form {"blockID":"m5DwG"} -->
	<!-- wp:wp-predictive-search/mobile-icon {"blockID":"2n5irm"} -->
	<div style="display:inline-flex;justify-content:center;align-items:center" class="wp-block-wp-predictive-search-mobile-icon a3blockpress-svg-icon a3blockpress-svg-icon-fe_search"><svg style="display:inline-block;vertical-align:middle" viewbox="0 0 24 24" height="25" width="25" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></div>
	<!-- /wp:wp-predictive-search/mobile-icon -->

	<!-- wp:wp-predictive-search/search-bar {"blockID":"1AvWml","rootID":"m5DwG","enableCustomBorder":true,"borderColor":"#fff","borderFocusColor":"#dcdcdc","backgroundColor":"#fff","borderStyle":"solid","borderLeft":"2","borderTop":"2","borderRight":"2","borderBottom":"2","borderSync":true,"radiusTopLeft":"50","radiusTopRight":"50","radiusBottomRight":"50","radiusBottomLeft":"50","radiusSync":true} -->
	<!-- wp:wp-predictive-search/category-dropdown {"blockID":"Z21dhyW","enable":false} /-->

	<!-- wp:wp-predictive-search/search-icon {"blockID":"gmvUQ","iconSize":25,"normalIconColor":"#ff9704","normalBackgroundColor":"#fff","hoverBackgroundColor":"#fff","radiusTopLeft":"","radiusTopRight":"","radiusBottomRight":"","radiusBottomLeft":""} -->
	<div style="display:inline-flex;justify-content:center;align-items:center" class="wp-block-wp-predictive-search-search-icon a3blockpress-svg-icon a3blockpress-svg-icon-fe_search wpps_nav_submit_icon"><svg style="display:inline-block;vertical-align:middle" viewbox="0 0 24 24" height="25" width="25" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></div>
	<!-- /wp:wp-predictive-search/search-icon -->

	<!-- wp:wp-predictive-search/search-input {"blockID":"ZcPLdT","rootID":"m5DwG","placeholder":"Enter keyword here...","backgroundColor":"#fff","iconSize":14,"iconColor":"#fd9b3f"} -->
	<svg aria-hidden="true" viewbox="0 0 512 512" aria-label="Searching" class="wp-block-wp-predictive-search-search-input wpps_searching_icon"><path d="M288 39.056v16.659c0 10.804 7.281 20.159 17.686 23.066C383.204 100.434 440 171.518 440 256c0 101.689-82.295 184-184 184-101.689 0-184-82.295-184-184 0-84.47 56.786-155.564 134.312-177.219C216.719 75.874 224 66.517 224 55.712V39.064c0-15.709-14.834-27.153-30.046-23.234C86.603 43.482 7.394 141.206 8.003 257.332c.72 137.052 111.477 246.956 248.531 246.667C393.255 503.711 504 392.788 504 256c0-115.633-79.14-212.779-186.211-240.236C302.678 11.889 288 23.456 288 39.056z"></path></svg>
	<!-- /wp:wp-predictive-search/search-input -->
	<!-- /wp:wp-predictive-search/search-bar -->

	<!-- wp:wp-predictive-search/results-dropdown {"blockID":"Z1EKRAa","enableCustomBorder":true,"radiusTopLeft":"10","radiusTopRight":"10","radiusBottomRight":"10","radiusBottomLeft":"10","radiusSync":true} -->
	<!-- wp:wp-predictive-search/dropdown-close-icon {"blockID":"Geo9F","parentID":"Z1EKRAa"} /-->

	<!-- wp:wp-predictive-search/dropdown-title {"blockID":"ZOlxxe","parentID":"Z1EKRAa"} /-->

	<!-- wp:wp-predictive-search/dropdown-items {"blockID":"11QPfm","parentID":"Z1EKRAa"} /-->

	<!-- wp:wp-predictive-search/dropdown-footer {"blockID":"Z1HRFEF","rootID":"m5DwG","parentID":"Z1EKRAa"} /-->
	<!-- /wp:wp-predictive-search/results-dropdown -->
	<!-- /wp:wp-predictive-search/form --></div></div>
	<!-- /wp:group -->',
);
