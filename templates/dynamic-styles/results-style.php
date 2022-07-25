<?php

// Search Bar Sidebar Template
global $wpps_all_results_pages_settings;
extract( $wpps_all_results_pages_settings );
if ( 1 == $grid_card_column ) {
	$grid_card_column_gap = 0;
	$grid_card_wide = 100;
} else {
	$grid_card_wide = number_format( 100 / $grid_card_column, 1);
}
?>
@card_wide: ~"calc( <?php echo $grid_card_wide; ?>% - <?php echo (int) $grid_card_column_gap; ?>px )";
@grid_card_column_gap: <?php echo $grid_card_column_gap; ?>px;
@grid_card_row_gap: <?php echo $grid_card_row_gap; ?>px;
.grid_card_border() {
	<?php echo $GLOBALS[WPPS_PREFIX.'admin_interface']->generate_border_css( $grid_card_border ); ?>
}
.grid_card_shadow() {
	<?php echo $GLOBALS[WPPS_PREFIX.'admin_interface']->generate_shadow_css( $grid_card_shadow ); ?>
}

@list_image_size: <?php echo $list_image_size; ?>px;
@list_content_margin_left: <?php echo ( $list_image_size + 10 ); ?>px;
.list_divider() {
	<?php echo str_replace( 'border:', 'border-bottom:', $GLOBALS[WPPS_PREFIX.'admin_interface']->generate_border_style_css( $list_divider ) ) ; ?>
}
@list_divider_margin_top: <?php echo $list_divider_margin_top; ?>px;
@list_divider_margin_bottom: <?php echo $list_divider_margin_bottom; ?>px;

@content_bg_color: <?php echo $content_bg_color; ?>;
@content_padding: <?php echo $content_padding_top; ?>px <?php echo $content_padding_right; ?>px <?php echo $content_padding_bottom; ?>px <?php echo $content_padding_left; ?>px;
.title_font() {
	<?php echo $GLOBALS[WPPS_PREFIX.'fonts_face']->generate_font_css( $title_font ); ?>
}
@title_hover_color: <?php echo $title_hover_color; ?>;
@title_align: <?php echo $title_align; ?>;
@title_margin_top: <?php echo $title_margin_top; ?>px;
@title_margin_bottom: <?php echo $title_margin_bottom; ?>px;
.description_font() {
	<?php echo $GLOBALS[WPPS_PREFIX.'fonts_face']->generate_font_css( $description_font ); ?>
}
@description_align: <?php echo $description_align; ?>;
@description_margin_top: <?php echo $description_margin_top; ?>px;
@description_margin_bottom: <?php echo $description_margin_bottom; ?>px;
.category_font() {
	<?php echo $GLOBALS[WPPS_PREFIX.'fonts_face']->generate_font_css( $category_font ); ?>
}
@category_hover_color: <?php echo $category_hover_color; ?>;
.category_label_font() {
	<?php echo $GLOBALS[WPPS_PREFIX.'fonts_face']->generate_font_css( $category_label_font ); ?>
}
@category_align: <?php echo $category_align; ?>;
@category_margin_top: <?php echo $category_margin_top; ?>px;
@category_margin_bottom: <?php echo $category_margin_bottom; ?>px;

<style>
#ps_items_container.ps_grid_container {
	column-gap: @grid_card_column_gap;
	row-gap: @grid_card_row_gap;
}
#ps_items_container.ps_grid_container .rs_result_row {
	width: @card_wide;
	.grid_card_border();
	.grid_card_shadow();
}

#ps_items_container:not(.ps_grid_container) .rs_rs_avatar {
	width: @list_image_size;
}
#ps_items_container:not(.ps_grid_container) .rs_content{
	margin-left: @list_content_margin_left;
}
#ps_items_container:not(.ps_grid_container) .rs_result_row {
	.list_divider();
	padding-bottom: @list_divider_margin_top;
	margin-bottom: @list_divider_margin_bottom;
}

#ps_items_container .rs_result_row {
	background-color: @content_bg_color;
}
#ps_items_container .rs_content {
	padding: @content_padding;
}
#ps_items_container .rs_rs_name {
	.title_font();
	text-align: @title_align;
	margin-top: @title_margin_top;
	margin-bottom: @title_margin_bottom;
}
#ps_items_container .rs_rs_name:hover {
	color: @title_hover_color;
}
#ps_items_container .rs_rs_description {
	.description_font();
	text-align: @description_align;
	margin-top: @description_margin_top;
	margin-bottom: @description_margin_bottom;
}
#ps_items_container .rs_rs_meta {
	.category_label_font();
	text-align: @category_align;
	margin-top: @category_margin_top;
	margin-bottom: @category_margin_bottom;
}
#ps_items_container .rs_rs_meta a,
#ps_items_container .rs_rs_meta span {
	.category_font();
}
#ps_items_container .rs_rs_meta a:hover {
	color: @category_hover_color;
}
</style>
