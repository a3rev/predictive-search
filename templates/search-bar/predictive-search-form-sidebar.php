<?php
/**
 * The Template for Predictive Search plugin
 *
 * Override this template by copying it to yourtheme/ps/predictive-search-form-sidebar.php
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $wpps_search_page_id;

$search_results_page = str_replace( array( 'http:', 'https:' ), '', get_permalink( $wpps_search_page_id ) );
?>

<?php do_action( 'wpps_search_form_before' ); ?>

<div class="wpps_bar wpps_bar-<?php echo esc_attr( $ps_id ); ?> <?php echo ( 'yes' == sanitize_key( wp_unslash( $ps_args['search_icon_mobile'] ) ) ? 'search_icon_only' : '' ); ?>"
	data-ps-id="<?php echo esc_attr( $ps_id ); ?>"
	data-ps-row="<?php echo esc_attr( $ps_args['row'] ); ?>"

	<?php if ( count( $ps_args['search_list'] ) > 0 ) { ?>
	data-ps-search_in="<?php echo esc_attr( $ps_args['search_list'][0] ); ?>"
	data-ps-search_other="<?php echo esc_attr( implode( ',', $ps_args['search_list'] ) ); ?>"
	<?php } ?>

	<?php if ( $ps_args['search_in'] != '' ) { ?>
	data-ps-popup_search_in="<?php echo esc_attr( $ps_args['search_in'] ); ?>"
	<?php } ?>

	<?php if ( class_exists('SitePress') ) { ?>
	data-ps-lang="<?php echo esc_attr( ICL_LANGUAGE_CODE ); ?>"
	<?php } ?>

	data-ps-widget_template="<?php echo esc_attr( $ps_widget_template ); ?>"
>

	<div class="wpps_mobile_icon sidebar_temp" role="button" aria-label="<?php _e( 'Open Search', 'wp-predictive-search' ); ?>">
		<div style="display: inline-flex; justify-content: center; align-items: center;">
			<svg viewBox="0 0 24 24" height="25" width="25" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
		</div>
	</div>
	<div style="clear:both;"></div>

	<div class="wpps_container wpps_container-<?php echo esc_attr( $ps_id ); ?> wpps_sidebar_container <?php echo is_rtl() ? 'rtl' : ''; ?>" id="wpps_container-<?php echo esc_attr( $ps_id ); ?>">
		<form
			class="wpps_form"
			autocomplete="off"
			action="<?php echo esc_url( $search_results_page ); ?>"
			method="get"
		>

			<?php
			if ( 1 == $ps_args['show_catdropdown'] && false !== $post_categories = wpps_get_categories( $ps_args['in_taxonomy'] ) ) {
				$default_cat       = '';
				$default_cat_label = wpps_ict_t__( 'All', __('All', 'wp-predictive-search' ) );
				if ( isset( $ps_args['default_cat'] ) && ! empty( $ps_args['default_cat'] ) ) {
					foreach ( $post_categories as $category_data ) {
						if ( $ps_args['default_cat'] == $category_data['slug'] ) {
							$default_cat       = $category_data['slug'];
							$default_cat_label = esc_html( $category_data['name'] );
							break;
						}
					}
				}
			?>
			<div class="wpps_nav_<?php echo esc_attr( $ps_args['cat_align'] ); ?>">
				<div class="wpps_nav_scope">
					<div class="wpps_nav_facade">
						<div class="wpps_nav_down_icon">
							<svg viewBox="0 0 24 24" height="12" width="12" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;"><polyline points="6 9 12 15 18 9"></polyline></svg>
						</div>
						<span class="wpps_nav_facade_label"><?php echo esc_html( $default_cat_label ); ?></span>
					</div>
					<select aria-label="<?php _e( 'Select Category', 'wp-predictive-search' ); ?>" class="wpps_category_selector" name="cat_in" data-ps-cat_max_wide="<?php echo esc_attr( $ps_args['cat_max_wide'] ); ?>" data-ps-taxonomy="<?php echo esc_attr( $ps_args['in_taxonomy'] ); ?>">
						<option value="" selected="selected"><?php wpps_ict_t_e( 'All', __('All', 'wp-predictive-search' ) ); ?></option>
					<?php if ( $post_categories !== false ) { ?>
						<?php foreach ( $post_categories as $category_data ) { ?>
						<option <?php selected( $default_cat, $category_data['slug'], true ); ?> data-href="<?php echo esc_url( $category_data['url'] ); ?>" value="<?php echo esc_attr( $category_data['slug'] ); ?>"><?php echo esc_html( $category_data['name'] ); ?></option>
						<?php } ?>
					<?php } ?>
					</select>
				</div>
			</div>
			<?php } else { ?>
			<input type="hidden" class="wpps_category_selector" name="cat_in" value="" data-ps-cat_max_wide="<?php echo esc_attr( $ps_args['cat_max_wide'] ); ?>" data-ps-taxonomy="<?php echo esc_attr( $ps_args['in_taxonomy'] ); ?>" />
			<?php } ?>

			<div class="wpps_nav_<?php echo ( 'left' === sanitize_key( wp_unslash( $ps_args['cat_align'] ) ) ? 'right' : 'left' ); ?>" aria-label="<?php _e( 'Search Now', 'wp-predictive-search' ); ?>">
				<div class="wpps_nav_submit">
					<div class="wpps_nav_submit_icon">
						<svg viewBox="0 0 24 24" height="16" width="16" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
					</div>
					<input class="wpps_nav_submit_bt" type="button" value="<?php echo __( 'Go', 'wp-predictive-search' ); ?>" aria-label="<?php _e( 'Go', 'wp-predictive-search' ); ?>">
				</div>
			</div>

			<div class="wpps_nav_fill">
				<div class="wpps_nav_field">
					<input type="text" name="rs" class="wpps_search_keyword" id="wpps_search_keyword_<?php echo esc_attr( $ps_id ); ?>"
						aria-label="<?php _e( 'Keyword Search', 'wp-predictive-search' ); ?>"
						onblur="if( this.value == '' ){ this.value = '<?php echo esc_js( $ps_args['search_box_text'] ); ?>'; }"
						onfocus="if( this.value == '<?php echo esc_js( $ps_args['search_box_text'] ); ?>' ){ this.value = ''; }"
						value="<?php echo esc_attr( $ps_args['search_box_text'] ); ?>"
						data-ps-id="<?php echo esc_attr( $ps_id ); ?>"
						data-ps-default_text="<?php echo esc_attr( $ps_args['search_box_text'] ); ?>"
					/>
					<svg aria-hidden="true" viewBox="0 0 512 512" class="wpps_searching_icon" style="display: none;" aria-label="<?php _e( 'Searching', 'wp-predictive-search' ); ?>">
						<path d="M288 39.056v16.659c0 10.804 7.281 20.159 17.686 23.066C383.204 100.434 440 171.518 440 256c0 101.689-82.295 184-184 184-101.689 0-184-82.295-184-184 0-84.47 56.786-155.564 134.312-177.219C216.719 75.874 224 66.517 224 55.712V39.064c0-15.709-14.834-27.153-30.046-23.234C86.603 43.482 7.394 141.206 8.003 257.332c.72 137.052 111.477 246.956 248.531 246.667C393.255 503.711 504 392.788 504 256c0-115.633-79.14-212.779-186.211-240.236C302.678 11.889 288 23.456 288 39.056z"></path>
					</svg>
				</div>
			</div>

		<?php if ( '' == get_option('permalink_structure') ) { ?>
			<input type="hidden" name="page_id" value="<?php echo esc_attr( $wpps_search_page_id ); ?>"  />

			<?php if ( class_exists('SitePress') ) { ?>
				<input type="hidden" name="lang" value="<?php echo esc_attr( ICL_LANGUAGE_CODE ); ?>"  />
			<?php } ?>

		<?php } ?>

		<?php if ( count( $ps_args['search_list'] ) > 0 ) { ?>
			<input type="hidden" name="search_in" value="<?php echo esc_attr( $ps_args['search_list'][0] ); ?>"  />
			<input type="hidden" name="search_other" value="<?php echo esc_attr( implode( ',', $ps_args['search_list'] ) ); ?>"  />
		<?php } ?>

			<?php do_action( 'wpps_search_form_inside' ); ?>
		</form>
	</div>
	<div style="clear:both;"></div>

	<?php do_action( 'wpps_search_form_data_extra', $ps_args ); ?>
	<input type="hidden" name="show_in_cat" value="<?php echo esc_attr( $ps_args['show_in_cat'] ); ?>"  />
	<input type="hidden" name="text_lenght" value="<?php echo esc_attr( $ps_args['text_lenght'] ); ?>"  />
	<input type="hidden" name="popup_wide" value="<?php echo esc_attr( $ps_args['popup_wide'] ); ?>"  />

</div>

<?php do_action( 'wpps_search_form_after' ); ?>
