<?php
/**
 * WordPress Predictive Search Cache
 *
 */

namespace A3Rev\WPPredictiveSearch;

class Cache
{
	private $taxonomy;
	private $need_rebuild_key;
	private $have_cache_key;
	private $time_cache_key;
	private $transient_name;
	public $error_id = 'build_category_cache';

	public function __construct( $taxonomy = 'category' ) {

		$this->taxonomy = $taxonomy;
		$this->need_rebuild_key = 'predictive_search_need_rebuild_cache_' . $taxonomy;
		$this->have_cache_key = 'predictive_search_have_cache_' . $taxonomy;
		$this->time_cache_key = 'predictive_search_time_built_cache_' . $taxonomy;
		$this->transient_name = 'ps_dropdown_' . $taxonomy;

		add_action( 'admin_init', array( $this, 'handle_events' ) );

		/**
		 * On the scheduled action hook, run the function.
		 */
		add_action( 'wp_predictive_search_auto_preload_cache_event', array( $this, 'auto_check_preload_cache' ) );

		// Auto Rebuild Cache for Category
		add_action( 'created_' . $taxonomy, array( $this, 'auto_rebuild_category_dropdown_cache' ), 10, 0 );
		add_action( 'edited_' . $taxonomy, array( $this, 'auto_rebuild_category_dropdown_cache' ), 10, 0 );
		add_action( 'delete_' . $taxonomy, array( $this, 'auto_rebuild_category_dropdown_cache' ), 10, 0 );

		// Manual Rebuild Cache notify
		add_action( 'admin_notices', array( $this, 'rebuild_cache_notice' ), 11 );
		add_action( 'admin_footer', array( $this, 'include_custom_script' ) );

		if ( is_admin() ) {
			// AJAX refresh cache
			add_action('wp_ajax_wp_predictive_search_refresh_cache_' . $taxonomy, array( $this, 'wp_predictive_search_refresh_cache_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_refresh_cache_' . $taxonomy, array( $this, 'wp_predictive_search_refresh_cache_ajax' ) );

			add_action('wp_ajax_wp_predictive_search_rebuild_cache_' . $taxonomy, array( $this, 'wp_predictive_search_rebuild_cat_cache_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_rebuild_cache_' . $taxonomy, array( $this, 'wp_predictive_search_rebuild_cat_cache_ajax' ) );

			add_action('wp_ajax_wp_predictive_search_build_cache_error_' . $taxonomy, array( $this, 'wp_predictive_search_build_category_cache_error_ajax' ) );
			add_action('wp_ajax_nopriv_wp_predictive_search_build_cache_error_' . $taxonomy, array( $this, 'wp_predictive_search_build_category_cache_error_ajax' ) );
		}
	}

	public function enable_cat_cache() {
		$enable_cat_cache = get_option( 'predictive_search_category_cache', 'yes' );
		if ( 'no' == $enable_cat_cache ) return false;

		return true;
	}

	public function cat_cache_is_built() {

		$current_lang = '';
		if ( class_exists('SitePress') ) {
			$current_lang = apply_filters( 'wpml_current_language', NULL );
		}
		if ( '' != trim( $current_lang ) ) {
			$current_lang = '_' . $current_lang;
		}
		$have_cat_cache = get_option( $this->have_cache_key . $current_lang, 'no' );

		if ( 'yes' == $have_cat_cache ) return true;

		return false;
	}

	public function cat_cache_built_time() {

		$current_lang = '';
		if ( class_exists('SitePress') ) {
			$current_lang = apply_filters( 'wpml_current_language', NULL );
		}
		if ( '' != trim( $current_lang ) ) {
			$current_lang = '_' . $current_lang;
		}
		$cache_time = get_option( $this->time_cache_key . $current_lang, false );

		if ( false !== $cache_time ) return $cache_time;

		return false;
	}

	public function handle_events() {
		if ( ! $this->enable_cat_cache() ) return;

		if ( isset( $_POST['predicitve-search-generate-cat-cache'] ) || ( isset( $_GET['wpps-cat-rebuild-cache'] ) && 'yes' == sanitize_key( wp_unslash( $_GET['wpps-cat-rebuild-cache'] ) ) ) ) {

			$this->preload_category_dropdown_cache();

			echo '<div class="updated"><p>' . __( '<strong>SUCCESS</strong>! Your Category Data Cache has been successfully preloaded.', 'wp-predictive-search' ) . '</p></div>';
		}
	}

	public function wp_predictive_search_rebuild_cat_cache_ajax() {
		check_ajax_referer( WPPS_KEY . '_a3_admin_ui_event', 'security' );

		$this->preload_category_dropdown_cache();

		echo json_encode( array( 'status' => 'success', 'date' => date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ) ) ) );

		die();
	}

	public function wp_predictive_search_refresh_cache_ajax() {
		if ( ! $this->enable_cat_cache() ) die();

		check_ajax_referer( 'wp-predictive-search-refresh-cache', 'security' );

		$this->preload_category_dropdown_cache();

		die();
	}

	public function wp_predictive_search_build_category_cache_error_ajax() {
		check_ajax_referer( 'wp_predictive_search_build_cache_error_category', 'security' );

		global $wpps_errors_log;

		$build_category_cache_error_log = trim( $wpps_errors_log->get_error( $this->error_id ) );

		$wpps_errors_log->error_modal( $this->error_id, $build_category_cache_error_log );
	}

	public function rebuild_cache_notice() {
		if ( ! $this->enable_cat_cache() ) return;

		$show_warning = false;

		if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'edit-tags.php', 'term.php' ) ) ) {
			if ( isset( $_REQUEST['taxonomy'] ) && in_array( $_REQUEST['taxonomy'], array( $this->taxonomy ) ) ) {
				$show_warning = true;
			}
		}

		if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post.php' ) ) ) {
			$show_warning = true;
		}

		if ( ! $show_warning ) return;

		$rebuild_cache    = get_option( 'predictive_search_rebuild_cat_cache', 'auto' );

		// Detect for show the warning when rebuild cache is set as manually and had change on categories
		if ( 'auto' != $rebuild_cache ) {
			$need_rebuild_now = get_option( $this->need_rebuild_key, 'no' );
			$warning_display = 'display: none;';

			if ( 'yes' == $need_rebuild_now ) {
				$warning_display = '';
			}

			// Just for show this warning 1 time after have change on categories
			update_option( $this->need_rebuild_key, 'no' );

			$rebuild_cache_url = add_query_arg( array(
				'wpps-cat-rebuild-cache' => 'yes',
			) );
	?>
		<div class="message error wpps_cache_warning" style="<?php echo esc_attr( $warning_display ); ?>">
    		<p>
    			<?php echo sprintf( __( 'Refresh Predictive Search in Categories cache <a class="button button-primary wp_predictive_search_refresh_cache_bt" href="%s" target="_parent">Refresh Cache</a>' , 'wp-predictive-search' ), esc_url( $rebuild_cache_url ) ); ?>
    			<img class="wpps_cache_loading" src="<?php echo esc_url( WPPS_IMAGES_URL ); ?>/indicator.gif" style="display: none; width: auto; height: auto;" >
    			<span class="wpps_cache_loaded" style="display: none; color: green; font-weight: 600;"><?php esc_html_e( 'Cache Refreshed', 'wp-predictive-search' ); ?></span>
    		</p>
    	</div>
	<?php
		}
	}

	public function include_custom_script() {
		$include_script = false;

		if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'edit-tags.php', 'term.php' ) ) ) {
			if ( isset( $_REQUEST['taxonomy'] ) && in_array( $_REQUEST['taxonomy'], array( $this->taxonomy ) ) ) {
				$include_script = true;
			}
		}

		if ( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post.php' ) ) ) {
			$include_script = true;
		}

		if ( ! $include_script ) return;

		$wpps_cache_security = wp_create_nonce("wp-predictive-search-refresh-cache");
	?>
    	<script type="text/javascript">
		(function($) {

			$(document).ready(function() {

				$( '.save', '#inline-edit' ).on('click', function() {
					$('.wpps_cache_warning').show('slow');
				});

				$( '#the-list' ).on( 'click', '.delete-tag', function() {
					$('.wpps_cache_warning').show('slow');
				});

				$( '#<?php echo esc_js( $this->taxonomy ); ?>-add-submit').on( 'click', function() {
					$('.wpps_cache_warning').show('slow');
				});

				$(document).on( "click", '.wp_predictive_search_refresh_cache_bt', function( event, value, status ) {
					var refresh_bt = $(this);
					refresh_bt.siblings('.wpps_cache_loading').show();

					var data = {
						action: 'wp_predictive_search_refresh_cache_<?php echo esc_js( $this->taxonomy ); ?>',
						security: '<?php echo esc_js( $wpps_cache_security ); ?>'
					};

					$.post(ajaxurl, data, function(response) {
						refresh_bt.siblings('.wpps_cache_loading').hide();
						refresh_bt.siblings('.wpps_cache_loaded').show();

						setTimeout( function(){
							$('.wpps_cache_warning').slideUp('slow');
							refresh_bt.siblings('.wpps_cache_loaded').hide();
						}, 2000 )
					});

					return false;

				});
			});

		})(jQuery);
		</script>
    <?php
	}

	public function auto_check_preload_cache() {
		if ( ! $this->enable_cat_cache() ) return;

		$this->preload_category_dropdown_cache( false );
	}

	public function auto_rebuild_category_dropdown_cache() {
		if ( ! $this->enable_cat_cache() ) return;

		$rebuild_cache = get_option( 'predictive_search_rebuild_cat_cache', 'auto' );
		if ( 'auto' == $rebuild_cache ) {
			$this->preload_category_dropdown_cache();
		} else {
			update_option( $this->need_rebuild_key, 'yes' );
		}
	}

	public function preload_category_dropdown_cache( $clear_cached = true ) {

		// Log Errors 
		global $wpps_errors_log;

		$wpps_errors_log->delete_error( $this->error_id );

		$error_type = __( 'Build Category Cache Failed', 'wp-predictive-search' );

		$wpps_errors_log->log_errors( $this->error_id, $error_type );

		if ( $clear_cached ) {
			update_option( $this->need_rebuild_key, 'no' );
			$this->flush_categories_dropdown_cache();
		}

		if ( class_exists('SitePress') ) {
			$current_lang = apply_filters( 'wpml_current_language', NULL );

			$active_languages = apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
			if ( !empty( $active_languages ) ) {
				foreach( $active_languages as $l ) {
					// Switch to different language to get correct categories
					do_action( 'wpml_switch_language', $l['language_code'] );
					$this->generate_categories_dropdown_cache();
				}

				// Reset language to current language
				do_action( 'wpml_switch_language', $current_lang );
			}
		} else {
			$this->generate_categories_dropdown_cache();
		}
	}

	public function generate_categories_dropdown_cache() {
		$categories_list       = false;
		$append_transient_name = '';

		if ( class_exists('SitePress') ) {
			$current_lang = apply_filters( 'wpml_current_language', NULL );
			$append_transient_name = $current_lang;
		}

		$categories_list = $this->get_categories_dropdown_cache( $append_transient_name );

		if ( false === $categories_list ) {

			@set_time_limit(86400);
			@ini_set("memory_limit","1000M");

			global $wp_predictive_search;
			$categories_list = $wp_predictive_search->get_categories_nested( $this->taxonomy );

			$cache_timeout_hours = get_option( 'wpps_search_category_cache_timeout', 48 );
			$cache_timeout       = $cache_timeout_hours * 60 * 60;
			$this->set_categories_dropdown_cache( $append_transient_name, $categories_list, $cache_timeout );
		}

		return $categories_list;
	}

	public function get_categories_dropdown_cache( $append_transient_name = '' ) {

		// Generate transient name
		$transient_name = $this->transient_name;
		if ( '' != trim( $append_transient_name ) ) {
			$append_transient_name = '_' . trim( $append_transient_name );
		}
		$transient_name .= $append_transient_name;

		// Get cached
		$data_cached = get_transient( $transient_name );

		return $data_cached;
	}

	public function set_categories_dropdown_cache( $append_transient_name, $data_cache, $timeout = 259200 ) {

		// Generate transient name
		$transient_name = $this->transient_name;
		if ( '' != trim( $append_transient_name ) ) {
			$append_transient_name = '_' . trim( $append_transient_name );
		}
		$transient_name .= $append_transient_name;

		// Get timeout
		if ( (int) $timeout < 3600 ) {
			$timeout = 259200;
		}

		// Set cached
		set_transient( $transient_name, $data_cache, (int) $timeout );
		update_option( $this->have_cache_key . $append_transient_name, 'yes' );
		update_option( $this->time_cache_key . $append_transient_name, current_time( 'timestamp' ) );
	}

	public function flush_categories_dropdown_cache() {
		global $wpdb;

		$transient_name = $this->transient_name;
		$wpdb->query( $wpdb->prepare( 'DELETE FROM '. $wpdb->options . ' WHERE option_name LIKE %s', '%'.$transient_name.'%' ) );
	}
}
