<?php
/**
 * WC Predictive Search Results
 *
 */

namespace A3Rev\WPPredictiveSearch;

class Results 
{
	public static function inline_scripts() {
		global $wpps_search_page_id;
		
		$wpps_search_enable_google_analytic          = get_option( 'wpps_search_enable_google_analytic', 'no' ); 
		$wpps_search_google_analytic_id              = trim( get_option( 'wpps_search_google_analytic_id', '' ) ); 
		$wpps_search_google_analytic_query_parameter = trim( get_option( 'wpps_search_google_analytic_query_parameter', 'ps' ) ); 

		list( 'search_keyword' => $search_keyword ) = Functions::get_results_vars_values();

		if ( $search_keyword == '' ) {
			return '';
		}
	?>

	<?php if ( $wpps_search_enable_google_analytic == 'yes' && $wpps_search_google_analytic_id != '' ) { ?>
		<!-- Google Analytics -->
		<script>
	        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	        ga('create', '<?php echo esc_js( $wpps_search_google_analytic_id ); ?>', 'auto');
	        ga('send', 'pageview', {
			  'page': '/<?php echo esc_js( add_query_arg( array( $wpps_search_google_analytic_query_parameter => $search_keyword ) , get_page_uri( $wpps_search_page_id ) ) ); ?>',
			  'title': '<?php echo esc_js( get_the_title( $wpps_search_page_id ) ); ?>'
			});
        </script>
        <!-- End Google Analytics -->
    <?php } ?>

        <script type="text/javascript">
			(function($) {
				$(function(){
					wpps_app.start();
				});
			})(jQuery);
		</script>
    <?php
    }

    public static function include_header() {
    	global $wpps_search_page_id;

		list( 'search_keyword' => $search_keyword, 'search_in' => $search_in, 'search_other' => $search_other, 'cat_in' => $cat_in, 'in_taxonomy' => $in_taxonomy ) = Functions::get_results_vars_values();

		if ( $search_keyword == '' || $search_in == '' ) {
			return '';
		}

		global $wp_predictive_search;
		global $ps_search_list, $ps_current_search_in;
		$items_search_default = $wp_predictive_search->get_items_search();
		$permalink_structure  = get_option( 'permalink_structure' );

    	$tmp_args = array(
			'items_search_default' => $items_search_default,
			'ps_search_list'       => $ps_search_list,
			'ps_current_search_in' => $ps_current_search_in,
			'permalink_structure'  => $permalink_structure,
			'wpps_search_page_id'  => $wpps_search_page_id,
			'search_keyword'       => $search_keyword,
			'cat_in'               => $cat_in,
			'in_taxonomy'          => $in_taxonomy,
			'search_in'            => $search_in,
			'search_other'         => $search_other,
		);

		wpps_get_results_header_tpl( $tmp_args );
    }

    public static function items_container() {
    	list( 'search_in' => $search_in ) = Functions::get_results_vars_values();

    	$wpps_all_results_pages_settings = get_option( 'wpps_all_results_pages_settings' );

		$theme_container_class = '';
		$template_type = isset( $wpps_all_results_pages_settings['template_type'] ) ? $wpps_all_results_pages_settings['template_type'] : 'plugin';
		$results_display_type = isset( $wpps_all_results_pages_settings['display_type'] ) ? $wpps_all_results_pages_settings['display_type'] : 'grid';
		
		if ( 'theme' === $template_type ) {
			do_action( 'wpps_shortcode_theme_display' );
			$theme_container_class = isset( $wpps_all_results_pages_settings['theme_container_class'] ) ? $wpps_all_results_pages_settings['theme_container_class'] : '';
			$theme_container_class = apply_filters( 'wpps_search_result_theme_container_class', $theme_container_class, $search_in );
		} elseif ( 'grid' === $results_display_type ) {
			$theme_container_class = 'ps_grid_container';
		}

		$theme_container_class = str_replace( array( ', ', ',' ), ' ', $theme_container_class );
    	?>
    	<div id="ps_items_container" class="<?php esc_attr_e( $theme_container_class ); ?>"></div>
    	<?php
    }

    public static function more_results() {
    	?>
        <div style="clear:both"></div>
        <div class="ps_more_result" id="ps_more_result_popup">
            <img src="<?php echo esc_url( WPPS_IMAGES_URL ); ?>/more-results-loader.gif" />
            <div><em><?php wpps_ict_t_e( 'Loading Text', __('Loading More Results...', 'wp-predictive-search' ) ); ?></em></div>
        </div>
        <div class="ps_more_result" id="ps_no_more_result_popup"><em><?php wpps_ict_t_e( 'No More Result Text', __('No More Results to Show', 'wp-predictive-search' ) ); ?></em></div>
        <div class="ps_more_result" id="ps_fetching_result_popup">
            <img src="<?php echo esc_url( WPPS_IMAGES_URL ); ?>/more-results-loader.gif" />
            <div><em><?php wpps_ict_t_e( 'Fetching Text', __('Fetching search results...', 'wp-predictive-search' ) ); ?></em></div>
        </div>
        <div class="ps_more_result" id="ps_no_result_popup"><em><?php wpps_ict_t_e( 'No Fetching Result Text', __('No Results to Show', 'wp-predictive-search' ) ); ?></em></div>
        <div id="ps_footer_container"></div>
    	<?php
    }
						
	public static function display_search_results() {
		list( 'search_keyword' => $search_keyword, 'search_in' => $search_in ) = Functions::get_results_vars_values();
		
		if ( $search_keyword != '' && $search_in != '' ) {
			ob_start();
		?>
		<div id="ps_results_container" class="wpps">		
			<?php self::include_header(); ?>
			<?php self::items_container(); ?>
			<?php self::more_results(); ?>
		</div>
		<?php self::inline_scripts(); ?>
		<?php
			$output = ob_get_clean();
			
			return $output;
        }
	}	
}
