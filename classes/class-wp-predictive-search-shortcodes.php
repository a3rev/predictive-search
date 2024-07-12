<?php
/**
 * WC Predictive Search Hook Filter
 *
 *
 * Table Of Contents
 *
 * parse_shortcode_search_widget()
 * add_search_widget_icon()
 * add_search_widget_mce_popup()
 * parse_shortcode_search_result()
 * display_search()
 */

namespace A3Rev\WPPredictiveSearch;

class Shortcodes 
{
	public static function parse_shortcode_search_widget($attributes) {
		// Don't show content for shortcode on Dashboard, still support for admin ajax
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) return;

		if ( ! is_array( $attributes ) ) {
			$attributes = array();
		}

		$attr = array_merge( array(
			'widget_template'  => 'sidebar',
			'show_catdropdown' => 1,
			'default_cat'      => '',
			'in_taxonomy'	   => 'category',
			'show_image'       => 1,
			'show_desc'        => 1,
			'show_in_cat'      => 1,
			'character_max'    => 100,
			'style'            => '',
			'wrap'             => 'false',
			'search_box_text'  => '',
        ), $attributes );

        // WPCS: XSS ok.
		$widget_template  = esc_attr( $attr['widget_template'] );
		$show_catdropdown = intval( $attr['show_catdropdown'] );
		$in_taxonomy      = esc_attr( $attr['in_taxonomy'] );
		$default_cat      = esc_attr( $attr['default_cat'] );
		$show_image       = intval( $attr['show_image'] );
		$show_desc        = intval( $attr['show_desc'] );
		$show_in_cat      = intval( $attr['show_in_cat'] );
		$character_max    = intval( $attr['character_max'] );
		$style            = esc_attr( $attr['style'] );
		$wrap             = esc_attr( $attr['wrap'] );
		$search_box_text  = esc_attr( $attr['search_box_text'] );

		$text_lenght = $character_max;

		$break_div = '<div style="clear:both;"></div>';
		if ($wrap == 'true') $break_div = '';

		if ( trim($search_box_text) == '' ) {
			if ( class_exists('SitePress') ) {
				$current_lang = ICL_LANGUAGE_CODE;
				$search_box_texts = get_option('wpps_search_box_text', array() );
				if ( is_array($search_box_texts) && isset($search_box_texts[$current_lang]) ) $search_box_text = esc_attr( stripslashes( trim( $search_box_texts[$current_lang] ) ) );
				else $search_box_text = '';
			} else {
				$search_box_text = get_option('wpps_search_box_text', '' );
				if ( is_array($search_box_text) ) $search_box_text = '';
			}
		}

		global $wp_predictive_search;

		$ps_id = rand(100, 10000);

		$row                  = 0;
		$search_list          = array();
		$number_items         = array();
		$items_search_default = $wp_predictive_search->get_items_search();

		foreach ($items_search_default as $key => $data) {
			$item_key = $key.'_items';
			if ( isset($attr[$item_key]) ) {
				if ( $attr[$item_key] > 0 ) {
					$number_items[$key] = $attr[$item_key];
					$row += $attr[$item_key];
					$row++;
					$search_list[] = $key;
				}
			} elseif ( $data['number'] > 0 ) {
				$number_items[$key] = $data['number'];
				$row += $data['number'];
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
			'default_cat'      => $default_cat,
			'widget_template'  => $widget_template,
			'show_image'       => $show_image,
			'show_desc'        => $show_desc,
			'show_in_cat'      => $show_in_cat,
			'search_in'        => $search_in,
			'search_list'      => $search_list,
		);
		$search_form = wpps_search_form( $ps_id, $widget_template, $ps_args );

		$search_form_html = '<div class="wpps_shortcode_container" style="max-width: 100%; '.$style.'">' . $search_form . '</div>' . $break_div;

		return $search_form_html;
	}

	public static function add_search_widget_icon($context){
		$image_btn = WPPS_IMAGES_URL . "/ps_icon.png";
		$out = '<a href="#TB_inline?width=670&height=500&modal=false&inlineId=wpps_search_widget_shortcode" class="thickbox" title="'.__('Insert WordPress Predictive Search Shortcode', 'wp-predictive-search' ).'"><img class="search_widget_shortcode_icon" src="'.$image_btn.'" alt="'.__('Insert WordPress Predictive Search Shortcode', 'wp-predictive-search' ).'" /></a>';
		return $context . $out;
	}
	
	//Action target that displays the popup to insert a form to a post/page
	public static function add_search_widget_mce_popup(){
		global $wpps_cache;
		$disabled_cat_dropdown = false;
		$post_categories = wpps_get_categories();
		if ( ! $wpps_cache->enable_cat_cache() || ! $wpps_cache->cat_cache_is_built() ) {
			$disabled_cat_dropdown = true;
			$post_categories = false;
		}

		global $wp_predictive_search;

		$items_search_default = $wp_predictive_search->get_items_search();
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#wpps_search_show_catdropdown').on('click', function(){
					if ( jQuery(this).is(':checked') ) {
						jQuery('.wpps_search_set_default_cat_container').show();
					} else {
						jQuery('.wpps_search_set_default_cat_container').hide();
					}
				});
			});

			function wpps_search_widget_add_shortcode(){
				var number_items = '';
				<?php foreach ($items_search_default as $key => $data) {?>
				var wpps_search_<?php echo esc_js( $key ); ?>_items = '<?php echo esc_js( $key ); ?>_items="' + jQuery("#wpps_search_<?php echo esc_js( $key ); ?>_items").val() + '" ';
				number_items += wpps_search_<?php echo esc_js( $key ); ?>_items;
				<?php } ?>
				var wpps_search_widget_template = jQuery("#wpps_search_widget_template").val();
				var wpps_search_set_default_cat = jQuery('#wpps_search_set_default_cat').val();
				var wpps_search_show_catdropdown = 0;
				if ( jQuery('#wpps_search_show_catdropdown').is(":checked") ) {
					wpps_search_show_catdropdown = 1;
				} else {
					wpps_search_set_default_cat = '';
				}
				var wpps_search_show_image = 0;
				if ( jQuery('#wpps_search_show_image').is(":checked") ) {
					wpps_search_show_image = 1;
				}
				var wpps_search_show_desc = 0;
				if ( jQuery('#wpps_search_show_desc').is(":checked") ) {
					wpps_search_show_desc = 1;
				}
				var wpps_search_show_in_cat = 0;
				if ( jQuery('#wpps_search_show_in_cat').is(":checked") ) {
					wpps_search_show_in_cat = 1;
				}
				var wpps_search_text_lenght = jQuery("#wpps_search_text_lenght").val();
				var wpps_search_align = jQuery("#wpps_search_align").val();
				var wpps_search_width = jQuery("#wpps_search_width").val();
				var wpps_search_padding_top = jQuery("#wpps_search_padding_top").val();
				var wpps_search_padding_bottom = jQuery("#wpps_search_padding_bottom").val();
				var wpps_search_padding_left = jQuery("#wpps_search_padding_left").val();
				var wpps_search_padding_right = jQuery("#wpps_search_padding_right").val();
				var wpps_search_box_text = jQuery("#wpps_search_box_text").val();
				var wpps_search_style = '';
				var wrap = '';
				if (wpps_search_align == 'center') wpps_search_style += 'float:none;margin:auto;display:table;';
				else if (wpps_search_align == 'left-wrap') wpps_search_style += 'float:left;';
				else if (wpps_search_align == 'right-wrap') wpps_search_style += 'float:right;';
				else wpps_search_style += 'float:'+wpps_search_align+';';
				
				if(wpps_search_align == 'left-wrap' || wpps_search_align == 'right-wrap') wrap = 'wrap="true"';
				
				if (parseInt(wpps_search_width) > 0) wpps_search_style += 'width:'+parseInt(wpps_search_width)+'px;';
				if (parseInt(wpps_search_padding_top) >= 0) wpps_search_style += 'padding-top:'+parseInt(wpps_search_padding_top)+'px;';
				if (parseInt(wpps_search_padding_bottom) >= 0) wpps_search_style += 'padding-bottom:'+parseInt(wpps_search_padding_bottom)+'px;';
				if (parseInt(wpps_search_padding_left) >= 0) wpps_search_style += 'padding-left:'+parseInt(wpps_search_padding_left)+'px;';
				if (parseInt(wpps_search_padding_right) >= 0) wpps_search_style += 'padding-right:'+parseInt(wpps_search_padding_right)+'px;';
				var win = window.dialogArguments || opener || parent || top;
				win.send_to_editor('[wpps_search_widget ' + number_items + ' widget_template="'+wpps_search_widget_template+'" show_catdropdown="'+wpps_search_show_catdropdown+'" in_taxonomy="category" default_cat="'+wpps_search_set_default_cat+'" show_image="'+wpps_search_show_image+'" show_desc="'+wpps_search_show_desc+'" show_in_cat="'+wpps_search_show_in_cat+'" character_max="'+wpps_search_text_lenght+'" style="'+wpps_search_style+'" '+wrap+' search_box_text="'+wpps_search_box_text+'" ]');
			}
			
			
		</script>
		<style type="text/css">
		#TB_ajaxContent{width:auto !important;}
		#TB_ajaxContent p {
			padding:2px 0;	
		}
		.field_content {
			padding:0 40px;
		}
		.field_content label{
			width:150px;
			float:left;
			text-align:left;
		}
		.a3-view-docs-button {
			background-color: #FFFFE0 !important;
			border: 1px solid #E6DB55 !important;
			border-radius: 3px;
			-webkit-border-radius: 3px;
			-moz-border-radius: 3px;
			color: #21759B !important;
			outline: 0 none;
			text-shadow:none !important;
			font-weight:normal !important;
			font-family: sans-serif;
			font-size: 12px;
			text-decoration: none;
			padding: 3px 8px;
			position: relative;
			margin-left: 4px;
			white-space:nowrap;
		}
		.a3-view-docs-button:hover {
			color: #D54E21 !important;
		}
		@media screen and ( max-width: 782px ) {
			#wpps_search_box_text {
				width:100% !important;	
			}
		}
		@media screen and ( max-width: 480px ) {
			.a3_ps_exclude_item {
				float:none !important;
				display:block;
			}
		}
		</style>
		<div id="wpps_search_widget_shortcode" style="display:none;">
		  <div style="height: 98%; overflow: auto;">
			<h3><?php _e('Customize the Predictive Search Shortcode', 'wp-predictive-search' ); ?> <a class="add-new-h2 a3-view-docs-button" target="_blank" href="<?php echo esc_url( WPPS_DOCS_URI ); ?>#section-16" ><?php _e('View Docs', 'wp-predictive-search' ); ?></a></h3>
			<div style="clear:both"></div>
			<div class="field_content">
                <?php foreach ($items_search_default as $key => $data) { ?>
                <p><label for="wpps_search_<?php echo esc_attr( $key ); ?>_items"><?php echo esc_html( $data['name'] ); ?>:</label> <input style="width:100px;" size="10" id="wpps_search_<?php echo esc_attr( $key ); ?>_items" name="wpps_search_<?php echo esc_attr( $key ); ?>_items" type="text" value="<?php echo esc_attr( $data['number'] ); ?>" /> <span class="description"><?php echo sprintf( __( 'Number of %s results to show in dropdown', 'wp-predictive-search' ), esc_html( $data['name'] ) ); ?></span></p> 
                <?php } ?>
                <p><label for="wpps_search_widget_template"><?php _e('Select Template', 'wp-predictive-search' ); ?>:</label> <select style="width:100px" id="wpps_search_widget_template" name="wpps_search_widget_template"><option value="sidebar" selected="selected"><?php _e('Widget', 'wp-predictive-search' ); ?></option><option value="header"><?php _e('Header', 'wp-predictive-search' ); ?></option></select></p>
                <p>
                	<label for="wpps_search_show_catdropdown"><?php _e('Category Dropdown', 'wp-predictive-search' ); ?>:</label> <input <?php echo ( $disabled_cat_dropdown ) ? 'disabled="disabled"' : ''; ?> type="checkbox" checked="checked" id="wpps_search_show_catdropdown" name="wpps_search_show_catdropdown" value="1" /> <span class="description"><?php _e('Search in Category Feature', 'wp-predictive-search' ); ?></span>
                	<?php if ( $disabled_cat_dropdown ) { ?>
                	<br>
            		<label>&nbsp;</label><span><?php echo sprintf( __( 'Activate and build <a href="%s" target="_blank">Category Cache</a> to activate this feature', 'wp-predictive-search' ), admin_url( 'admin.php?page=wp-predictive-search&tab=search-box-settings&box_open=predictive_search_category_cache_box#predictive_search_category_cache_box', 'relative' ) ); ?></span>
            		<?php } ?>
            	</p>

            	
            	<p class="wpps_search_set_default_cat_container" style="<?php if ( $disabled_cat_dropdown || false === $post_categories ) { ?>display: none;<?php } ?>">
            		<label for="wpps_search_set_default_cat"><?php _e('Default Category', 'wp-predictive-search' ); ?>:</label> 
            		<select style="width:100px" id="wpps_search_set_default_cat" name="wpps_search_set_default_cat">
            			<option value="" selected="selected"><?php _e('All', 'wp-predictive-search' ); ?></option>
            		<?php if ( $post_categories ) { ?>
						<?php foreach ( $post_categories as $category_data ) { ?>
						<option value="<?php echo esc_attr( $category_data['slug'] ); ?>"><?php echo esc_html( $category_data['name'] ); ?></option>
						<?php } ?>
            		<?php } ?>
            		</select> 
            		<span class="description"><?php _e('Set category as default selected category for Category Dropdown', 'wp-predictive-search' ); ?></span>
            	</p>

                <p><label for="wpps_search_show_image"><?php _e('Image', 'wp-predictive-search' ); ?>:</label> <input type="checkbox" checked="checked" id="wpps_search_show_image" name="wpps_search_show_image" value="1" /> <span class="description"><?php _e('Show Results Images', 'wp-predictive-search' ); ?></span></p>
            	<p><label for="wpps_search_show_desc"><?php _e('Description', 'wp-predictive-search' ); ?>:</label> <input type="checkbox" checked="checked" id="wpps_search_show_desc" name="wpps_search_show_desc" value="1" /> <span class="description"><?php _e('Show Results Description', 'wp-predictive-search' ); ?></span></p>
            	<p><label for="wpps_search_text_lenght"><?php _e('Characters Count', 'wp-predictive-search' ); ?>:</label> <input style="width:100px;" size="10" id="wpps_search_text_lenght" name="wpps_search_text_lenght" type="text" value="100" /> <span class="description"><?php _e('Number of results description characters', 'wp-predictive-search' ); ?></span></p>
            	<p><label for="wpps_search_show_in_cat"><?php _e('Post Categories', 'wp-predictive-search' ); ?>:</label> <input type="checkbox" checked="checked" id="wpps_search_show_in_cat" name="wpps_search_show_in_cat" value="1" /> <span class="description"><?php _e('Results - Show Categories', 'wp-predictive-search' ); ?></span></p>
                <p><label for="wpps_search_align"><?php _e('Alignment', 'wp-predictive-search' ); ?>:</label> <select style="width:100px" id="wpps_search_align" name="wpps_search_align"><option value="none" selected="selected"><?php _e('None', 'wp-predictive-search' ); ?></option><option value="left-wrap"><?php _e('Left - wrap', 'wp-predictive-search' ); ?></option><option value="left"><?php _e('Left - no wrap', 'wp-predictive-search' ); ?></option><option value="center"><?php _e('Center', 'wp-predictive-search' ); ?></option><option value="right-wrap"><?php _e('Right - wrap', 'wp-predictive-search' ); ?></option><option value="right"><?php _e('Right - no wrap', 'wp-predictive-search' ); ?></option></select> <span class="description"><?php _e('Horizontal aliginment of search box', 'wp-predictive-search' ); ?></span></p>
                <p><label for="wpps_search_width"><?php _e('Search box width', 'wp-predictive-search' ); ?>:</label> <input style="width:100px;" size="10" id="wpps_search_width" name="wpps_search_width" type="text" value="200" />px</p>
                <p><label for="wpps_search_box_text"><?php _e('Search box text message', 'wp-predictive-search' ); ?>:</label> <input style="width:300px;" size="10" id="wpps_search_box_text" name="wpps_search_box_text" type="text" value="" /></p>
                <p><label for="wpps_search_padding"><strong><?php _e('Padding', 'wp-predictive-search' ); ?></strong>:</label><br /> 
				<label for="wpps_search_padding_top" style="width:auto; float:none"><?php _e('Above', 'wp-predictive-search' ); ?>:</label><input style="width:50px;" size="10" id="wpps_search_padding_top" name="wpps_search_padding_top" type="text" value="10" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label for="wpps_search_padding_bottom" style="width:auto; float:none"><?php _e('Below', 'wp-predictive-search' ); ?>:</label> <input style="width:50px;" size="10" id="wpps_search_padding_bottom" name="wpps_search_padding_bottom" type="text" value="10" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label for="wpps_search_padding_left" style="width:auto; float:none"><?php _e('Left', 'wp-predictive-search' ); ?>:</label> <input style="width:50px;" size="10" id="wpps_search_padding_left" name="wpps_search_padding_left" type="text" value="0" />px &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <label for="wpps_search_padding_right" style="width:auto; float:none"><?php _e('Right', 'wp-predictive-search' ); ?>:</label> <input style="width:50px;" size="10" id="wpps_search_padding_right" name="wpps_search_padding_right" type="text" value="0" />px
                </p>
			</div>
            <p><input type="button" class="button-primary" value="<?php _e('Insert Shortcode', 'wp-predictive-search' ); ?>" onclick="wpps_search_widget_add_shortcode();"/>&nbsp;&nbsp;&nbsp;
            <a class="button" style="" href="#" onclick="tb_remove(); return false;"><?php _e('Cancel', 'wp-predictive-search' ); ?></a>
			</p>
            <div style="clear:both;"></div>
		  </div>
          <div style="clear:both;"></div>
		</div>
<?php
	}
	
	public static function parse_shortcode_search_result( $attributes = array() ) {
		// Don't show content for shortcode on Dashboard, still support for admin ajax
		if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) return;

		global $wpps_search_page_content_type;

		if ( 'block' == $wpps_search_page_content_type ) return;

		$search_results = Results::display_search_results();
		
    	return $search_results;	
    }
}
