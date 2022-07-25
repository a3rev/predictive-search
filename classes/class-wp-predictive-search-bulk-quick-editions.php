<?php
/**
 * Predictive Search Bulk Quick Editions
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

class Bulk_Quick_Editions
{
	/**
	 * Column for Post, Page
	 *
	 * @access public
	 * @param mixed $columns
	 * @return array
	 */
	public static function column_heading( $existing_columns ) {
	
		if ( empty( $existing_columns ) && ! is_array( $existing_columns ) )
			$existing_columns = array();
	
		$our_columns = array();
		$our_columns["predictive_search_focuskw"] = __( 'PS Focus KW', 'wp-predictive-search' );
	
		return array_merge( $existing_columns, $our_columns );
	}
	
	
	
	
	/**
	 * Custom Columns for Post, Page
	 *
	 * @access public
	 * @param mixed $column
	 * @return void
	 */
	public static function column_content( $column_name, $post_id  ) {
		if ( $column_name == 'predictive_search_focuskw' ) {
			global $wpps_keyword_data;
			global $wpps_exclude_data;

			$ps_focuskw = $wpps_keyword_data->get_item( $post_id );
			esc_attr_e( $ps_focuskw );

			$exclude_items = array();
			$ps_exclude_item = 'no';
			if ( $wpps_exclude_data->get_item( $post_id, get_post_type( $post_id ) ) > 0 ) {
				$ps_exclude_item = 'yes';
			}
			echo '<div class="hidden" style="display:none" id="wp_predictive_search_inline_'.$post_id.'"><div class="predictive_search_focuskw">'.esc_html( $ps_focuskw ).'</div><div class="ps_exclude_item">'.$ps_exclude_item.'</div></div>';
		}
	}

	/**
	 * Custom bulk edit - form
	 *
	 * @access public
	 * @param mixed $column_name
	 * @param mixed $post_type
	 * @return void
	 */
	public static function admin_bulk_edit( $column_name, $post_type ) {
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();

		if ( $column_name != 'predictive_search_focuskw' || empty( $posttypes_support ) || ! isset( $posttypes_support[ $post_type ] ) ) return;
		?>
		<fieldset class="inline-edit-col-right inline-edit-predictive-search">
			<div id="wp-predictive-search-fields-bulk" class="inline-edit-col">
				<h4><?php _e( 'Predictive Search', 'wp-predictive-search' ); ?></h4>
                <div class="">
                    <label class="inline-edit-tags">
                        <span class="title" style="width:100px;"><?php _e( 'Focus Keywords', 'wp-predictive-search' ); ?></span> &nbsp;&nbsp;&nbsp;
                        <span class="">
                            <select class="change_ps_keyword change_to" name="change_ps_keyword">
                            <?php
                                $options = array(
                                    '' 	=> __( '- No Change -', 'wp-predictive-search' ),
                                    '1' => __( 'Change to:', 'wp-predictive-search' ),
                                );
                                foreach ($options as $key => $value) {
                                    echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
                                }
                            ?>
                            </select>
                        </span>
                    </label>
                    <label class="wp-predictive-search-keyword-value">
                        <textarea class="predictive_search_focuskw" name="_predictive_search_focuskw" rows="1" cols="22" autocomplete="off" placeholder="<?php _e( 'Enter Focus keywords', 'wp-predictive-search' ); ?>"></textarea>
                    </label>
                </div>
                <div class="inline-edit-group"></div>
                <div class="">
                    <label class="inline-edit-tags">
                        <span class="title" style="width:100px;"><?php _e( 'Show / Hide', 'wp-predictive-search' ); ?></span> &nbsp;&nbsp;&nbsp;
                        <span class="">
                            <select class="ps_exclude_item" name="ps_exclude_item">
                            <?php
                                $options = array(
                                    '' 	=> __( '- No Change -', 'wp-predictive-search' ),
                                    '1' => __( 'Hide from Predictive Search results', 'wp-predictive-search' ),
									'2' => __( 'Show in Predictive Search results', 'wp-predictive-search' ),
                                );
                                foreach ($options as $key => $value) {
                                    echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
                                }
                            ?>
                            </select>
                        </span>
                    </label>
                </div>
				
				<input type="hidden" name="predictive_search_bulk_edit_nonce" value="<?php echo wp_create_nonce( 'predictive_search_bulk_edit_nonce' ); ?>" />
			</div>
		</fieldset>
		<?php
	}
	
	
	/**
	 * Custom bulk edit - save
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public static function admin_bulk_edit_save( $post_id, $post ) {
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();

		if ( empty( $posttypes_support ) || ! isset( $posttypes_support[ $post->post_type ] ) ) return $post_id;
	
		if ( is_int( wp_is_post_revision( $post_id ) ) ) return;
		if ( is_int( wp_is_post_autosave( $post_id ) ) ) return;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;
		if ( ! isset( $_REQUEST['predictive_search_bulk_edit_nonce'] ) || ! wp_verify_nonce( $_REQUEST['predictive_search_bulk_edit_nonce'], 'predictive_search_bulk_edit_nonce' ) ) return $post_id;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
		
		// Save fields
		if ( ! empty( $_REQUEST['change_ps_keyword'] ) && isset( $_REQUEST['_predictive_search_focuskw'] ) ) {
			global $wpps_keyword_data;
			$predictive_search_focuskw = trim( sanitize_text_field( $_REQUEST['_predictive_search_focuskw'] ) );
			if ( '' != $predictive_search_focuskw ) {
				$wpps_keyword_data->update_item( $post_id, $predictive_search_focuskw );
			} else {
				$wpps_keyword_data->delete_item( $post_id );
			}
		}
			
		if ( ! empty( $_REQUEST['ps_exclude_item'] ) ) {

			global $wpps_exclude_data;

			if ( isset( $_REQUEST['ps_exclude_item'] ) && $_REQUEST['ps_exclude_item'] == 1 ) {
				$wpps_exclude_data->insert_item( $post_id , $post->post_type );
			} else {
				$wpps_exclude_data->delete_item( $post_id, $post->post_type );
			}
		}
	
	}
	
	/**
	 * Custom quick edit - form
	 *
	 * @access public
	 * @param mixed $column_name
	 * @param mixed $post_type
	 * @return void
	 */
	public static function quick_edit( $column_name, $post_type ) {
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();

		if ( $column_name != 'predictive_search_focuskw' || empty( $posttypes_support ) || ! isset( $posttypes_support[ $post_type ] ) ) return;
		?>
		<fieldset class="inline-edit-col-right">
			<div id="wp-predictive-search-fields-quick" class="inline-edit-col">
				<h4><?php _e( 'Predictive Search', 'wp-predictive-search' ); ?></h4>
				<div>
					<label class="">
						<span class="title"><?php _e( 'Focus Keywords', 'wp-predictive-search' ); ?></span>
                        <textarea class="_predictive_search_focuskw" name="_predictive_search_focuskw" rows="1" cols="22" autocomplete="off" placeholder="<?php _e( 'Enter Focus keywords', 'wp-predictive-search' ); ?>"></textarea>
					</label>
				</div>
                <div class="inline-edit-group">
					<label class="alignleft">
                        <input type="checkbox" value="1" name="ps_exclude_item" />
                        <span class="checkbox-title"><?php _e('Hide from Predictive Search results.', 'wp-predictive-search' ); ?></span>
                    </label>
				</div>
				<input type="hidden" name="predictive_search_quick_edit_nonce" value="<?php echo wp_create_nonce( 'predictive_search_quick_edit_nonce' ); ?>" />
			</div>
		</fieldset>
		<?php
	}
	
	
	/**
	 * Custom quick edit - script
	 *
	 * @access public
	 * @param mixed $hook
	 * @return void
	 */
	public static function quick_edit_scripts( $hook ) {
		global $post_type;
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();
	
		if ( $hook == 'edit.php' && ! empty( $posttypes_support ) && isset( $posttypes_support[ $post_type ] ) )
			wp_enqueue_script( 'predictive_search_quick-edit', WPPS_JS_URL . '/quick-edit.js', array('jquery') );
	}
	
	
	/**
	 * Custom quick edit - save
	 *
	 * @access public
	 * @param mixed $post_id
	 * @param mixed $post
	 * @return void
	 */
	public static function quick_edit_save( $post_id, $post ) {
		global $wp_predictive_search;
		$posttypes_support = $wp_predictive_search->posttypes_support();

		if ( empty( $posttypes_support ) || ! isset( $posttypes_support[ $post->post_type ] ) ) return $post_id;
	
		if ( ! $_POST || is_int( wp_is_post_revision( $post_id ) ) || is_int( wp_is_post_autosave( $post_id ) ) ) return $post_id;
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
		if ( ! isset( $_POST['predictive_search_quick_edit_nonce'] ) || ! wp_verify_nonce( $_POST['predictive_search_quick_edit_nonce'], 'predictive_search_quick_edit_nonce' ) ) return $post_id;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return $post_id;
	
		global $wpdb;

		// Save fields
		if ( isset( $_POST['_predictive_search_focuskw'] ) && trim( $_POST['_predictive_search_focuskw'] ) != '' ) {
			global $wpps_keyword_data;
			$predictive_search_focuskw = trim( sanitize_text_field( $_REQUEST['_predictive_search_focuskw'] ) );
			if ( '' != $predictive_search_focuskw ) {
				$wpps_keyword_data->update_item( $post_id, $predictive_search_focuskw );
			} else {
				$wpps_keyword_data->delete_item( $post_id );
			}
		}


		global $wpps_exclude_data;
		if ( isset( $_POST['ps_exclude_item'] ) && $_POST['ps_exclude_item'] == 1 ) {
			$wpps_exclude_data->insert_item( $post_id , $post->post_type );
		} else {
			$wpps_exclude_data->delete_item( $post_id, $post->post_type );
		}
	}

}
