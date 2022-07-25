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
		<strong><span class="ps_heading_search_in_name"><?php echo $items_search_default[$ps_current_search_in]['name']; ?></span></strong> 
		<?php wpps_ict_t_e( 'Search Result Text', __('search results for your search query', 'wp-predictive-search' ) ); ?> 
		<strong><?php echo $search_keyword; ?></strong>
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
		<?php echo $line_vertical; ?>
		<span class="rs_result_other_item">
			<a class="ps_navigation ps_navigation<?php echo $search_other_item; ?>" href="<?php echo $other_link_search; ?>&search_in=<?php echo $search_other_item; ?>&cat_in=<?php echo $cat_in; ?>&in_taxonomy=<?php echo $in_taxonomy; ?>&search_other=<?php echo $search_other; ?>" data-href="?page_id=<?php echo $wpps_search_page_id; ?>&rs=<?php echo urlencode($search_keyword); ?>&search_in=<?php echo $search_other_item; ?>&cat_in=<?php echo $cat_in; ?>&in_taxonomy=<?php echo $in_taxonomy; ?>&search_other=<?php echo $search_other; ?>" alt=""><?php echo $items_search_default[$search_other_item]['name']; ?></a>
		</span>
<?php
		} else {
?>
		<?php echo $line_vertical; ?>
		<span class="rs_result_other_item">
			<a class="ps_navigation ps_navigation<?php echo $search_other_item; ?>" href="<?php echo $other_link_search; ?>/search-in/<?php echo $search_other_item; ?>/cat-in/<?php echo $cat_in; ?>/in-taxonomy/<?php echo $in_taxonomy; ?>/search-other/<?php echo $search_other; ?>" data-href="keyword/<?php echo urlencode($search_keyword); ?>/search-in/<?php echo $search_other_item; ?>/cat-in/<?php echo $cat_in; ?>/in-taxonomy/<?php echo $in_taxonomy; ?>/search-other/<?php echo $search_other; ?>" alt=""><?php echo $items_search_default[$search_other_item]['name']; ?></a>
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