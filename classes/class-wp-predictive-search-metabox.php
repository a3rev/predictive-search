<?php
/**
 * Predictive Search Meta
 *
 * Class Function into WP e-Commerce plugin
 *
 * Table Of Contents
 *
 *
 * create_custombox()
 * a3_people_metabox()
 */

namespace A3Rev\WPPredictiveSearch;

class MetaBox
{
	public static function create_custombox() {
		global $post;
		global $wp_predictive_search;
		$posttypes_slug = $wp_predictive_search->posttypes_slug_support();

		add_action( 'post_submitbox_misc_actions', array( __CLASS__, 'hide_from_results_box' ) );

		add_meta_box( 'wp_predictive_search_metabox', __('Predictive Search Meta', 'wp-predictive-search' ) , array( __CLASS__, 'data_metabox' ), $posttypes_slug, 'side', 'high'
			, array( 
				'__block_editor_compatible_meta_box' => true,
				'__back_compat_meta_box' => false,
			) );
	}

	public static function hide_from_results_box() {
		global $post;
		global $wp_predictive_search;
		$posttypes_slug = $wp_predictive_search->posttypes_slug_support();

		if ( empty( $posttypes_slug ) || ! in_array( $post->post_type, $posttypes_slug ) ) {
			return;
		}

		global $wpps_exclude_data;

		$postid      = $post->ID;
		$is_excluded = false;

		if ( $wpps_exclude_data->get_item( $postid, get_post_type( $postid ) ) > 0 ) {
			$is_excluded = true;
		}
	?>
		<script type="text/javascript">
			jQuery(document).ready(function() {

				setTimeout( function(){
				<?php if ( $is_excluded ) { ?>
					jQuery('#wp_predictive_search_metabox').addClass('closed').slideUp();
				<?php } else { ?>
					jQuery('#wp_predictive_search_metabox').removeClass('closed').slideDown();
				<?php } ?>
				}, 1000);

				jQuery('input.a3_ps_exclude_item').on('change', function() {
					if( jQuery(this).is(":checked") ) {
						jQuery('#wp_predictive_search_metabox').addClass('closed').slideUp();
					} else {
						jQuery('#wp_predictive_search_metabox').removeClass('closed').slideDown();
					}
				});
			});
		</script>
		<div class="misc-pub-section">
			<label>
				<input type="checkbox" <?php checked( true, $is_excluded, true ); ?> value="1" name="ps_exclude_item" class="a3_ps_exclude_item" />
				<?php echo __( 'Hide from Predictive Search results', 'wp-predictive-search' ); ?>
			</label>
		</div>
	<?php
	}

	public static function data_metabox() {
		global $post;
		$postid = $post->ID;

		global $wpps_keyword_data;

		$ps_focuskw = $wpps_keyword_data->get_item( $postid );
	?>
		<div class="a3_ps_focus_keyword_container">
			<p><?php echo __( "To hide from Predictive Search Results, use this post/page quick editor function or do it from the plugins settings.", 'wp-predictive-search' ); ?></p>
			<div class="wide_div">
				<label for="_predictive_search_focuskw"><strong><?php _e('Focus Keywords', 'wp-predictive-search' ); ?></strong></label>
			</div>
			<div class="wide_div">
				<input type="text" value="<?php esc_attr_e( $ps_focuskw );?>" id="_predictive_search_focuskw" name="_predictive_search_focuskw" style="width:98%;" />
			</div>
			<span class="description"><?php echo __( 'Enter keywords by "," separating values. Example: iPhone, ios', 'wp-predictive-search' ); ?></span>
        </div>
        <?php
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'a3_ps_metabox_action', 'a3_ps_metabox_nonce_field' );
		?>
		<div style="clear: both;"></div>
	<?php

	}

	public static function save_custombox( $post_id = 0 ) {
		if ( $post_id < 1 ) {
			global $post;
			$post_id = $post->ID;
		}

		// Check if our nonce is set.
		if ( ! isset( $_POST['a3_ps_metabox_nonce_field'] ) || ! check_admin_referer( 'a3_ps_metabox_action', 'a3_ps_metabox_nonce_field' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
		// so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;

		if ( ! current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		$post_type = get_post_type( $post_id );

		global $wp_predictive_search;
		$posttypes_slug = $wp_predictive_search->posttypes_slug_support();

		if ( empty( $posttypes_slug ) || ! in_array( $post_type, $posttypes_slug ) )
			return $post_id;

		$post_status = get_post_status( $post_id );
		if ( $post_status == 'inherit' )
			return $post_id;

		if ( ! isset( $_REQUEST['_predictive_search_focuskw'] ) )
			return $post_id;

		global $wpps_keyword_data;
		global $wpps_exclude_data;

		$predictive_search_focuskw = trim( sanitize_text_field( wp_unslash( $_REQUEST['_predictive_search_focuskw'] ) ) );
		if ( '' != $predictive_search_focuskw ) {
			$wpps_keyword_data->update_item( $post_id, $predictive_search_focuskw );
		} else {
			$wpps_keyword_data->delete_item( $post_id );
		}

		if ( isset( $_REQUEST['ps_exclude_item'] ) && intval( $_REQUEST['ps_exclude_item'] ) == 1 ) {
			$wpps_exclude_data->insert_item( $post_id , $post_type );
		} else {
			$wpps_exclude_data->delete_item( $post_id, $post_type );
		}
	}
}
