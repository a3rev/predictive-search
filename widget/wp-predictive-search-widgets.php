<?php
/**
 * WordPress Predictive Search Widget
 *
 * Table Of Contents
 *
 * get_items_search()
 * __construct()
 * widget()
 * woops_results_search_form()
 * update()
 * form()
 */

namespace A3Rev\WPPredictiveSearch;

class Widgets extends \WP_Widget 
{

	function __construct() {
		$widget_ops = array(
			'classname' => 'widget_wp_predictive_search',
			'description' => __( "User sees search results as they type in a dropdown - links through to 'All Search Results Page' that features endless scroll.", 'wp-predictive-search' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct('wp_predictive_search', __('WordPress Predictive Search', 'wp-predictive-search' ), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
		$number_items = isset( $instance['number_items'] ) ? $instance['number_items'] : array();
		if (!is_array($number_items) || count($number_items) < 1 ) $number_items = array();
		if(!isset($instance['text_lenght']) || $instance['text_lenght'] < 0) $text_lenght = 100; 
		else $text_lenght = $instance['text_lenght'];
		$show_catdropdown = !isset( $instance['show_catdropdown'] ) || empty($instance['show_catdropdown']) ? 0 : $instance['show_catdropdown'];
		$widget_template = !isset( $instance['widget_template'] ) || empty($instance['widget_template']) ? 'sidebar' : $instance['widget_template'];

		$in_taxonomy = !isset( $instance['in_taxonomy'] ) || empty($instance['in_taxonomy']) ? 'category' : $instance['in_taxonomy'];

		$show_image = !isset( $instance['show_image'] ) || empty($instance['show_image']) ? 0 : $instance['show_image'];
		$show_desc = !isset( $instance['show_desc'] ) || empty($instance['show_desc']) ? 0 : $instance['show_desc'];
		$show_in_cat = !isset( $instance['show_in_cat'] ) || empty($instance['show_in_cat']) ? 0 : $instance['show_in_cat'];

		if ( class_exists('SitePress') ) {
			$current_lang = ICL_LANGUAGE_CODE;
			$search_box_texts = ( isset($instance['search_box_text']) ? $instance['search_box_text'] : array() );
			if ( !is_array($search_box_texts) ) $search_box_texts = get_option('wpps_search_box_text', array() );
			if ( is_array($search_box_texts) && isset($search_box_texts[$current_lang]) ) $search_box_text = esc_attr( stripslashes( trim( $search_box_texts[$current_lang] ) ) );
			else $search_box_text = '';
		} else {
			$search_box_text = ( isset($instance['search_box_text']) ? $instance['search_box_text'] : '' );
			if ( is_array($search_box_text) || trim($search_box_text) == '' ) $search_box_text = get_option('wpps_search_box_text', '' );
			if ( is_array($search_box_text) ) $search_box_text = '';
		}

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
		echo $this->woops_results_search_form($widget_id, $number_items, $text_lenght, $search_box_text, $show_catdropdown, $show_image, $show_desc, $show_in_cat, $in_taxonomy, $widget_template );
		echo $after_widget;
	}
	
	public static function woops_results_search_form($widget_id, $number_items=array(), $text_lenght=100, $search_box_text = '', $show_catdropdown = 1, $show_image = 1, $show_desc = 1, $show_in_cat = 1, $in_taxonomy = 'category', $widget_template = 'sidebar' ) {
		
		global $wpps_search_page_id;
		global $wp_predictive_search;
		
		$ps_id = str_replace('wp_predictive_search-','',$widget_id);

		$row = 0;
		if (!is_array($number_items) || count($number_items) < 1 || array_sum($number_items) < 1) {
			$items_search_default = $wp_predictive_search->get_items_search();
			$number_items_default = array();
			foreach ($items_search_default as $key => $data) {
				if ($data['number'] > 0) {
					$number_items_default[$key] = $data['number'];
				}
			}
			$number_items = $number_items_default;
		}

		$common = '';
		$search_list = array();
		foreach ($number_items as $key => $number) {
			if ($number > 0) {
				$row += $number;
				$row++;
				$search_list[] = $key;
			}
		}
		$search_in = json_encode($number_items);

		global $wpps_cache;
		if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
			$show_catdropdown = 0;
		}

		$ps_args = array(
			'search_box_text'  => $search_box_text,
			'row'              => $row,
			'text_lenght'      => $text_lenght,
			'show_catdropdown' => $show_catdropdown,
			'in_taxonomy'	   => $in_taxonomy,
			'widget_template'  => $widget_template,
			'show_image'       => $show_image,
			'show_desc'        => $show_desc,
			'show_in_cat'      => $show_in_cat,
			'search_in'        => $search_in,
			'search_list'      => $search_list,
		);

		$search_form = wpps_search_form( $ps_id, $widget_template, $ps_args, false );

		return $search_form . '<div style="clear:both;"></div>';
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number_items'] = $new_instance['number_items'];
		$instance['text_lenght'] = strip_tags($new_instance['text_lenght']);
		$instance['show_catdropdown'] = $new_instance['show_catdropdown'];
		$instance['in_taxonomy'] = $new_instance['in_taxonomy'];
		$instance['search_box_text'] = $new_instance['search_box_text'];
		$instance['widget_template'] = $new_instance['widget_template'];
		$instance['show_image'] = !isset( $new_instance['show_image'] ) ? 0 : $new_instance['show_image'];
		$instance['show_desc'] = !isset( $new_instance['show_desc'] ) ? 0 : $new_instance['show_desc'];
		$instance['show_in_cat'] = !isset( $new_instance['show_in_cat'] ) ? 0 : $new_instance['show_in_cat'];
		return $instance;
	}

	function form( $instance ) {
		global $wp_predictive_search;

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );

		$global_search_box_text = get_option('wpps_search_box_text');
		$items_search_default = $wp_predictive_search->get_items_search();
		$number_items_default = array();
		foreach ($items_search_default as $key => $data) {
			$number_items_default[$key] = $data['number'];
		}
		unset($key);
		unset($data);
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'number_items' => $number_items_default, 'text_lenght' => 100, 'show_catdropdown' => 1, 'in_taxonomy' => 'category' , 'show_image' => 1, 'show_desc' => 1, 'show_in_cat' => 1, 'widget_template' => 'sidebar', 'search_box_text' => $global_search_box_text ) );
		$title = strip_tags($instance['title']);
		$number_items = $instance['number_items'];

		$number_items = ( array ) $number_items + $number_items_default;

		$text_lenght = strip_tags($instance['text_lenght']);
		$show_catdropdown = $instance['show_catdropdown'];
		$search_box_text = $instance['search_box_text'];
		$widget_template = $instance['widget_template'];
		$in_taxonomy = $instance['in_taxonomy'];

		$show_image = $instance['show_image'];
		$show_desc = $instance['show_desc'];
		$show_in_cat = $instance['show_in_cat'];

		global $wpps_cache;
		$disabled_cat_dropdown = false;
		if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
			$disabled_cat_dropdown = true;
		}
?>
		<style type="text/css">
		.item_heading{ width:130px; display:inline-block;}
		ul.predictive_search_item li{padding-left:15px; background:url(<?php echo WPPS_IMAGES_URL; ?>/sortable.gif) no-repeat left center; cursor:pointer;}
		ul.predictive_search_item li.ui-sortable-placeholder{border:1px dotted #111; visibility:visible !important; background:none;}
		ul.predictive_search_item li.ui-sortable-helper{background-color:#DDD;}
		</style>
			<p><label for="<?php echo esc_attr( $this->get_field_id('title') ); ?>"><?php _e('Title:', 'wp-predictive-search' ); ?></label> <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('title') ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<?php
		if ( class_exists('SitePress') ) {
			if ( !is_array($search_box_text) ) $search_box_text = array();
			global $sitepress;
			$active_languages = $sitepress->get_active_languages();
			if ( is_array($active_languages)  && count($active_languages) > 0 ) {
				foreach ( $active_languages as $language ) {
		?>
        	<p><label for="<?php echo esc_attr( $this->get_field_id('search_box_text') ); ?>_<?php echo esc_attr( $language['code'] ); ?>"><?php _e('Search box text message', 'wp-predictive-search' ); ?> (<?php echo esc_html( $language['display_name'] ); ?>)</label> <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('search_box_text') ); ?>_<?php echo esc_attr( $language['code'] ); ?>" name="<?php echo esc_attr( $this->get_field_name('search_box_text') ); ?>[<?php echo esc_attr( $language['code'] ); ?>]" type="text" value="<?php if ( isset( $search_box_text[$language['code'] ] ) ) esc_attr_e( $search_box_text[$language['code']] ); ?>" /></p>
        <?php
				}
			}
		} else {
			if ( is_array($search_box_text) ) $search_box_text = '';
		?>
            <p><label for="<?php echo esc_attr( $this->get_field_id('search_box_text') ); ?>"><?php _e('Search box text message:', 'wp-predictive-search' ); ?></label> <input class="widefat" id="<?php echo esc_attr( $this->get_field_id('search_box_text') ); ?>" name="<?php echo esc_attr( $this->get_field_name('search_box_text') ); ?>" type="text" value="<?php echo esc_attr($search_box_text); ?>" /></p>
		<?php } ?>
            <p><?php _e("Activate search 'types' for this widget by entering the number of results to show in the widget dropdown. &lt;empty&gt; = not activated. Sort order by drag and drop", 'wp-predictive-search' ); ?></p>
            <ul class="ui-sortable predictive_search_item">
            <?php foreach ($number_items as $key => $value) { ?>
            	<?php if ( isset( $items_search_default[$key] ) ) { ?>
            	<li><span class="item_heading"><label for="search_<?php echo esc_attr( $key ); ?>"><?php echo esc_attr( $items_search_default[$key]['name'] ); ?></label></span> <input id="search_<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $this->get_field_name('number_items') ); ?>[<?php echo esc_attr( $key ); ?>]" type="text" value="<?php echo esc_attr($value); ?>" style="width:50px;" /></li>
            	<?php } ?>
            <?php } ?>
            </ul>
            <p>
            	<label for="<?php echo esc_attr( $this->get_field_id('widget_template') ); ?>"><?php _e('Select Template:', 'wp-predictive-search' ); ?></label>
            	<select id="<?php echo esc_attr( $this->get_field_id('widget_template') ); ?>" name="<?php echo esc_attr( $this->get_field_name('widget_template') ); ?>">
					<option value="sidebar" selected="selected" ><?php _e('Widget', 'wp-predictive-search' ); ?></option>
					<option value="header" <?php selected( 'header', $widget_template, true ); ?>><?php _e('Header', 'wp-predictive-search' ); ?></option>
            	</select>
            </p>
            <p>
            	<label><input <?php echo ( $disabled_cat_dropdown ) ? 'disabled="disabled"' : 'name="' . esc_attr( $this->get_field_name('show_catdropdown') ).'"'; ?> type="checkbox" value="1" <?php checked( $show_catdropdown, 1 ); ?>  /> <?php _e('Search in Post Category Feature', 'wp-predictive-search' ); ?></label>
            	<?php if ( $disabled_cat_dropdown ) { ?>
            	<br>
            	<span><?php echo sprintf( __( 'Activate and build <a href="%s" target="_blank">Category Cache</a> to activate this feature', 'wp-predictive-search' ), admin_url( 'admin.php?page=wp-predictive-search&tab=search-box-settings&box_open=predictive_search_category_cache_box#predictive_search_category_cache_box', 'relative' ) ); ?></span>
				<input type="hidden" name="<?php echo esc_attr( $this->get_field_name('show_catdropdown') ); ?>" value="<?php echo esc_attr( $show_catdropdown ); ?>" />
            	<?php } ?>
            </p>
            <p>
            	<label for="<?php echo esc_attr( $this->get_field_id('in_taxonomy') ); ?>"><?php _e('Select Taxonomy', 'wp-predictive-search' ); ?></label>
            	<select <?php disabled( $disabled_cat_dropdown, true ); ?> id="<?php echo esc_attr( $this->get_field_id('in_taxonomy') ); ?>" name="<?php echo esc_attr( $this->get_field_name('in_taxonomy') ); ?>">
            		<?php foreach ( wpps_taxonomies_dropdown() as $taxonomy => $label ) { ?>
            			<option value="<?php esc_attr_e( $taxonomy ); ?>" <?php selected( $taxonomy, $in_taxonomy, true ); ?>><?php esc_html_e( $label ); ?></option>
            		<?php } ?>
            	</select>
            </p>
            <p>
            	<label><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_image') ); ?>" value="1" <?php checked( $show_image, 1 ); ?>  /> <?php _e('Show Results Images', 'wp-predictive-search' ); ?></label>
            </p>
            <p>
            	<label><input class="wpps_show_desc" type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_desc') ); ?>" value="1" <?php checked( $show_desc, 1 ); ?>  /> <?php _e('Show Results Description', 'wp-predictive-search' ); ?></label>
            </p>
            <p class="wpps_show_desc_container" style="<?php echo ( 0 == $show_desc ) ? 'display: none' : ''; ?>">
            	<label for="<?php echo esc_attr( $this->get_field_id('text_lenght') ); ?>"><?php _e('Character Count:', 'wp-predictive-search' ); ?></label> <input style="width:50px;" id="<?php echo esc_attr( $this->get_field_id('text_lenght') ); ?>" name="<?php echo esc_attr( $this->get_field_name('text_lenght') ); ?>" type="text" value="<?php echo esc_attr($text_lenght); ?>" />
            </p>
            <p>
            	<label><input type="checkbox" name="<?php echo esc_attr( $this->get_field_name('show_in_cat') ); ?>" value="1" <?php checked( $show_in_cat, 1 ); ?>  /> <?php _e('Results - Show Categories', 'wp-predictive-search' ); ?></label>
            </p>
		<script>
		jQuery(document).ready(function() {
        	jQuery(".predictive_search_item").sortable();
        	jQuery(document).on( 'change', ".wpps_show_desc", function(){
        		if ( jQuery(this).is(':checked') ) {
        			jQuery(this).parent('label').parent('p').siblings('.wpps_show_desc_container').show();
        		} else {
        			jQuery(this).parent('label').parent('p').siblings('.wpps_show_desc_container').hide();
        		}
        	});
		});
        </script>
<?php
	}
}
