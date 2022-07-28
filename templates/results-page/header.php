<?php
/**
 * The Template for Predictive Search plugin
 *
 * Override this template by copying it to yourtheme/ps/results-page/header.php
 *
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php if ( ! empty( $ps_search_list ) && count( $ps_search_list ) > 0 ) { ?>
	<p class="rs_result_heading">
		<?php wpps_ict_t_e( 'Viewing all', __('Viewing all', 'wp-predictive-search' ) ); ?> 
		<strong><span class="ps_heading_search_in_name"><?php echo esc_html( $items_search_default[$ps_current_search_in]['name'] ); ?></span></strong> 
		<?php wpps_ict_t_e( 'Search Result Text', __('search results for your search query', 'wp-predictive-search' ) ); ?> 
		<strong><?php echo esc_html( $search_keyword ); ?></strong>
	</p>
<?php } ?>

<?php
if ( ! empty( $ps_search_list ) && count( $ps_search_list ) > 1 ) {
	if ( $permalink_structure == '')
		$other_link_search = get_permalink( $wpps_search_page_id ).'&rs='. urlencode($search_keyword);
	else
		$other_link_search = rtrim( get_permalink( $wpps_search_page_id ), '/' ).'/keyword/'. urlencode($search_keyword);
	$line_vertical = '';
?>
	<div class="rs_result_others">
		<div class="rs_result_others_heading"><?php wpps_ict_t_e( 'Sort Text', __('Sort Search Results by', 'wp-predictive-search' ) ); ?></div>
<?php
	foreach ( $ps_search_list as $search_other_item ) {
		if ( ! isset( $items_search_default[$search_other_item] ) ) continue;

		if ( $permalink_structure == '' ) {
?>
		<?php echo esc_html( $line_vertical ); ?>
		<span class="rs_result_other_item">
			<a class="ps_navigation ps_navigation<?php echo esc_attr( $search_other_item ); ?>" href="<?php echo esc_url( $other_link_search . '&search_in=' . $search_other_item . '&cat_in=' . $cat_in . '&in_taxonomy=' . $in_taxonomy . '&search_other=' . $search_other ); ?>" data-href="?page_id=<?php echo esc_attr( $wpps_search_page_id ); ?>&rs=<?php echo esc_attr( urlencode($search_keyword) ); ?>&search_in=<?php echo esc_attr( $search_other_item ); ?>&cat_in=<?php echo esc_attr( $cat_in ); ?>&in_taxonomy=<?php echo esc_attr( $in_taxonomy ); ?>&search_other=<?php echo esc_attr( $search_other ); ?>" alt=""><?php echo esc_html( $items_search_default[$search_other_item]['name'] ); ?></a>
		</span>
<?php
		} else {
?>
		<?php echo esc_html( $line_vertical ); ?>
		<span class="rs_result_other_item">
			<a class="ps_navigation ps_navigation<?php echo esc_attr( $search_other_item ); ?>" href="<?php echo esc_url( $other_link_search . '/search-in/' . $search_other_item . '/cat-in/' . $cat_in . '/in-taxonomy/' . $in_taxonomy . '/search-other/' . $search_other ); ?>" data-href="keyword/<?php echo esc_attr( urlencode($search_keyword) ); ?>/search-in/<?php echo esc_attr( $search_other_item ); ?>/cat-in/<?php echo esc_attr( $cat_in ); ?>/in-taxonomy/<?php echo esc_attr( $in_taxonomy ); ?>/search-other/<?php echo esc_attr( $search_other ); ?>" alt=""><?php echo esc_html( $items_search_default[$search_other_item]['name'] ); ?></a>
		</span>
<?php
		}
		$line_vertical = ' | ';
	}
?>
	</div>
<?php
}
?>