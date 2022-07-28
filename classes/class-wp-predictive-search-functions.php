<?php
/**
 * WordPress Predictive Search Functions
 *
 *
 * Table Of Contents
 *
 * woops_limit_words()
 * create_page()
 * create_page_wpml()
 * auto_create_page_for_wpml()
 * strip_shortcodes()
 */

namespace A3Rev\WPPredictiveSearch;

class Functions
{

	public static function symbol_entities() {
		$symbol_entities = array(
			"_" => "_",
			"(" => "&lpar;",
			")" => "&rpar;",
			"{" => "&lcub;",
			"}" => "&rcub;",
			"<" => "&lt;",
			">" => "&gt;",
			"«" => "&laquo;",
			"»" => "&raquo;",
			"‘" => "&lsquo;",
			"’" => "&rsquo;",
			"“" => "&ldquo;",
			"”" => "&rdquo;",
			"‐" => "&dash;",
			"-" => "-",
			"–" => "&ndash;",
			"—" => "&mdash;",
			"←" => "&larr;",
			"→" => "&rarr;",
			"↑" => "&uarr;",
			"↓" => "&darr;",
			"©" => "&copy;",
			"®" => "&reg;",
			"™" => "&trade;",
			"€" => "&euro;",
			"£" => "&pound;",
			"¥" => "&yen;",
			"¢" => "&cent;",
			"§" => "&sect;",
			"∑" => "&sum;",
			"µ" => "&micro;",
			"¶" => "&para;",
			"¿" => "&iquest;",
			"¡" => "&iexcl;",

		);

		return apply_filters( 'wpps_symbol_entities', $symbol_entities );
	}

	public static function get_argument_vars() {
		$argument_vars = array( 'keyword' , 'search-in', 'cat-in', 'in-taxonomy', 'search-other' );
		return $argument_vars;
	}

	public static function get_results_vars_values() {
		global $wp_query;

		$search_keyword = '';
		$search_in      = 'post';
		$search_other   = '';
		$cat_in         = 'all';
		$in_taxonomy    = 'category';

		if ( isset( $wp_query->query_vars['keyword'] ) ) $search_keyword = sanitize_text_field( wp_unslash( urldecode( $wp_query->query_vars['keyword'] ) ) );
		elseif ( isset( $_REQUEST['rs'] ) && trim( sanitize_text_field( wp_unslash( $_REQUEST['rs'] ) ) ) != '' ) $search_keyword = sanitize_text_field( wp_unslash( $_REQUEST['rs'] ) );

		if ( isset( $wp_query->query_vars['search-in'] ) ) $search_in = sanitize_key( wp_unslash( urldecode( $wp_query->query_vars['search-in'] ) ) );
		elseif ( isset( $_REQUEST['search_in'] ) && trim( sanitize_key( wp_unslash( $_REQUEST['search_in'] ) ) ) != '' ) $search_in = sanitize_key( wp_unslash( $_REQUEST['search_in'] ) );

		if ( isset( $wp_query->query_vars['search-other'] ) ) $search_other = sanitize_text_field( wp_unslash( urldecode( $wp_query->query_vars['search-other'] ) ) );
		elseif ( isset( $_REQUEST['search_other'] ) && trim( sanitize_text_field( wp_unslash( $_REQUEST['search_other'] ) ) ) != '' ) $search_other = sanitize_text_field( wp_unslash( $_REQUEST['search_other'] ) );

		if ( isset( $wp_query->query_vars['cat-in'] ) ) $cat_in = sanitize_text_field( wp_unslash( urldecode( $wp_query->query_vars['cat-in'] ) ) );
		elseif ( isset( $_REQUEST['cat_in'] ) && trim( sanitize_text_field( wp_unslash( $_REQUEST['cat_in'] ) ) ) != '' ) $cat_in = sanitize_text_field( wp_unslash( $_REQUEST['cat_in'] ) );

		if ( isset( $wp_query->query_vars['in-taxonomy'] ) ) $in_taxonomy = sanitize_key( wp_unslash( urldecode( $wp_query->query_vars['in-taxonomy'] ) ) );
		elseif ( isset( $_REQUEST['in_taxonomy'] ) && trim( sanitize_key( wp_unslash( $_REQUEST['in_taxonomy'] ) ) ) != '' ) $in_taxonomy = sanitize_key( wp_unslash( $_REQUEST['in_taxonomy'] ) );

		$vars_values = array(
			'search_keyword' => $search_keyword,
			'search_in'      => $search_in,
			'search_other'   => $search_other,
			'cat_in'         => $cat_in,
			'in_taxonomy'    => $in_taxonomy,
		);

		return apply_filters( 'wpps_get_results_vars_values', $vars_values );
	}

	public static function special_characters_list() {
		$special_characters = array();
		foreach ( self::symbol_entities() as $symbol => $entity ) {
			$special_characters[$symbol] = $symbol;
		}

		return apply_filters( 'wpps_special_characters', $special_characters );
	}

	public static function is_enable_special_characters () {
		$enable_special_characters = true;

		$wpps_search_remove_special_character = get_option( 'wpps_search_remove_special_character', 'no' );
		if ( 'no' == $wpps_search_remove_special_character ) {
			$enable_special_characters = false;
		}

		$wpps_search_special_characters = get_option( 'wpps_search_special_characters', array() );
		if ( !is_array( $wpps_search_special_characters ) || count( $wpps_search_special_characters ) < 1 ) {
			$enable_special_characters = false;
		}

		return $enable_special_characters;
	}

	public static function replace_mysql_command( $field_name, $special_symbol, $replace_special_character = 'ignore' ) {
		if ( 'ignore' == $replace_special_character ) {
			$field_name = 'REPLACE( '.$field_name.', " '.$special_symbol.' ", "")';
			$field_name = 'REPLACE( '.$field_name.', " '.$special_symbol.'", "")';
			$field_name = 'REPLACE( '.$field_name.', "'.$special_symbol.' ", "")';
			$field_name = 'REPLACE( '.$field_name.', "'.$special_symbol.'", "")';
		} else {
			$field_name = 'REPLACE( '.$field_name.', " '.$special_symbol.' ", " ")';
			$field_name = 'REPLACE( '.$field_name.', " '.$special_symbol.'", " ")';
			$field_name = 'REPLACE( '.$field_name.', "'.$special_symbol.' ", " ")';
			$field_name = 'REPLACE( '.$field_name.', "'.$special_symbol.'", " ")';
		}

		return $field_name;
	}

	public static function remove_special_characters_in_mysql( $field_name, $search_keyword = '' ) {
		global $wpdb;

		$sql_after = '';

		if ( '' == trim( $field_name ) || '' == trim( $search_keyword ) ) {
			return $sql_after;
		}

		global $predictive_search_mode;

		$multi_keywords = explode( ' ', trim( $search_keyword ) );

		// This is original query
		if ( 'broad' != $predictive_search_mode ) {

			$sql_after .= " ( ";
			$combine = '';
			foreach ( $multi_keywords as $single_keyword ) {
				$sql_after .= $combine . " ( " . $wpdb->prepare( $field_name . " LIKE %s OR " . $field_name . " LIKE %s ", $single_keyword.'%', '% '.$single_keyword.'%' ) . " ) ";
				$combine = " AND ";
			}
			$sql_after .= " ) ";

		} else {

			$sql_after .= " ( ";
			$combine = '';
			foreach ( $multi_keywords as $single_keyword ) {
				$sql_after .= $combine . $wpdb->prepare( $field_name . " LIKE %s ", '%'.$single_keyword.'%' );
				$combine = " AND ";
			}
			$sql_after .= " ) ";

		}

		if ( ! self::is_enable_special_characters() ) {
			return $sql_after;
		}

		$replace_special_character             = get_option( 'wpps_search_replace_special_character', 'remove' );
		$wpps_search_special_characters = get_option( 'wpps_search_special_characters', array() );

		foreach ( $wpps_search_special_characters as $special_symbol ) {

			if ( 'both' == $replace_special_character ) {
				if ( 'broad' != $predictive_search_mode ) {

					$sql_after .= " OR ( ";
					$combine = '';
					foreach ( $multi_keywords as $single_keyword ) {
						$sql_after .= $combine . " ( " .  $wpdb->prepare( self::replace_mysql_command( $field_name, $special_symbol, 'ignore' ) . " LIKE %s OR " . self::replace_mysql_command( $field_name, $special_symbol, 'ignore' ) . " LIKE %s ", $single_keyword.'%', '% '.$single_keyword.'%' ) . " ) ";

						$combine = " AND ";
					}
					$sql_after .= " ) ";

					$sql_after .= " OR ( ";
					$combine = '';
					foreach ( $multi_keywords as $single_keyword ) {
						$sql_after .= $combine . " ( " . $wpdb->prepare( self::replace_mysql_command( $field_name, $special_symbol, 'remove' ) . " LIKE %s OR " . self::replace_mysql_command( $field_name, $special_symbol, 'remove' ) . " LIKE %s ", $single_keyword.'%', '% '.$single_keyword.'%' ) . " ) ";

						$combine = " AND ";
					}
					$sql_after .= " ) ";

				} else {

					$sql_after .= " OR ( ";
					$combine = '';
					foreach ( $multi_keywords as $single_keyword ) {
						$sql_after .= $combine . $wpdb->prepare( self::replace_mysql_command( $field_name, $special_symbol, 'ignore' ) . " LIKE %s ", '%'.$single_keyword.'%' );

						$combine = " AND ";
					}
					$sql_after .= " ) ";

					$sql_after .= " OR ( ";
					$combine = '';
					foreach ( $multi_keywords as $single_keyword ) {
						$sql_after .= $combine . $wpdb->prepare( self::replace_mysql_command( $field_name, $special_symbol, 'remove' ) . " LIKE %s ", '%'.$single_keyword.'%' );

						$combine = " AND ";
					}
					$sql_after .= " ) ";
				}
			} else {
				if ( 'broad' != $predictive_search_mode ) {

					$sql_after .= " OR ( ";
					$combine = '';
					foreach ( $multi_keywords as $single_keyword ) {
						$sql_after .= $combine . " ( " . $wpdb->prepare( self::replace_mysql_command( $field_name, $special_symbol, $replace_special_character ) . " LIKE %s OR " . self::replace_mysql_command( $field_name, $special_symbol, $replace_special_character ) . " LIKE %s ", $single_keyword.'%', '% '.$single_keyword.'%' ). " ) ";

						$combine = " AND ";
					}
					$sql_after .= " ) ";

				} else {

					$sql_after .= " OR ( ";
					$combine = '';
					foreach ( $multi_keywords as $single_keyword ) {
						$sql_after .= $combine . $wpdb->prepare( self::replace_mysql_command( $field_name, $special_symbol, $replace_special_character ) . " LIKE %s ", '%'.$single_keyword.'%' );

						$combine = " AND ";
					}
					$sql_after .= " ) ";
				}
			}

		}

		return $sql_after;
	}

	public static function remove_s_letter_at_end_word( $search_keyword ) {
		$search_keyword_new = '';
		$search_keyword_new_a = array();
		$search_keyword_split = explode( " ", trim( $search_keyword ) );
		if ( is_array( $search_keyword_split ) && count( $search_keyword_split ) > 0 ) {
			foreach ( $search_keyword_split as $search_keyword_element ) {
				if ( strlen( $search_keyword_element ) > 2 ) {
					$search_keyword_new_a[] = rtrim( $search_keyword_element, 's' );
				} else {
					$search_keyword_new_a[] = $search_keyword_element;
				}
			}
			$search_keyword_new = implode(" ", $search_keyword_new_a);
		}

		if ( '' != $search_keyword && $search_keyword_new != $search_keyword ) {
			return $search_keyword_new;
		} else {
			return false;
		}
	}

	public static function woops_limit_words($str='',$len=100,$more=true) {
		if (trim($len) == '' || $len < 0) $len = 100;
	   if ( $str=="" || $str==NULL ) return $str;
	   if ( is_array($str) ) return $str;
	   $str = trim($str);
	   $str = strip_tags(str_replace("\r\n", "", $str));
	   if ( strlen($str) <= $len ) return $str;
	   $str = substr($str,0,$len);
	   if ( $str != "" ) {
			if ( !substr_count($str," ") ) {
					  if ( $more ) $str .= " ...";
					return $str;
			}
			while( strlen($str) && ($str[strlen($str)-1] != " ") ) {
					$str = substr($str,0,-1);
			}
			$str = substr($str,0,-1);
			if ( $more ) $str .= " ...";
			}
			return $str;
	}

	public static function create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
		global $wpdb;

		$option_value = get_option($option);

		if ( $option_value > 0 && get_post( $option_value ) )
			return $option_value;

		$page_id = $wpdb->get_var( "SELECT ID FROM `" . $wpdb->posts . "` WHERE `post_content` LIKE '%$page_content%'  AND `post_type` = 'page' AND post_status = 'publish' ORDER BY ID ASC LIMIT 1" );

		if ( $page_id != NULL ) :
			if ( ! $option_value )
				update_option( $option, $page_id );
			return $page_id;
		endif;

		$page_data = array(
			'post_status' 		=> 'publish',
			'post_type' 		=> 'page',
			'post_author' 		=> 1,
			'post_name' 		=> $slug,
			'post_title' 		=> $page_title,
			'post_content' 		=> $page_content,
			'post_parent' 		=> $post_parent,
			'comment_status' 	=> 'closed'
		);
		$page_id = wp_insert_post( $page_data );

		if ( class_exists('SitePress') ) {
			global $sitepress;
			$source_lang_code = $sitepress->get_default_language();
			$trid = $sitepress->get_element_trid( $page_id, 'post_page' );
			if ( ! $trid ) {
				$wpdb->query( "UPDATE ".$wpdb->prefix . "icl_translations SET trid=".$page_id." WHERE element_id=".$page_id." AND language_code='".$source_lang_code."' AND element_type='post_page' " );
			}
		}

		update_option( $option, $page_id );

		return $page_id;
	}

	public static function create_page_wpml( $trid, $lang_code, $source_lang_code, $slug, $page_title = '', $page_content = '' ) {
		global $wpdb;

		$element_id = $wpdb->get_var( "SELECT ID FROM " . $wpdb->posts . " AS p INNER JOIN " . $wpdb->prefix . "icl_translations AS ic ON p.ID = ic.element_id WHERE p.post_content LIKE '%$page_content%' AND p.post_type = 'page' AND p.post_status = 'publish' AND ic.trid=".$trid." AND ic.language_code = '".$lang_code."' AND ic.element_type = 'post_page' ORDER BY p.ID ASC LIMIT 1" );

		if ( $element_id != NULL ) :
			return $element_id;
		endif;

		$page_data = array(
			'post_date'			=> gmdate( 'Y-m-d H:i:s' ),
			'post_modified'		=> gmdate( 'Y-m-d H:i:s' ),
			'post_status' 		=> 'publish',
			'post_type' 		=> 'page',
			'post_author' 		=> 1,
			'post_name' 		=> $slug,
			'post_title' 		=> $page_title,
			'post_content' 		=> $page_content,
			'comment_status' 	=> 'closed'
		);
		$wpdb->insert( $wpdb->posts , $page_data);
		$element_id = $wpdb->insert_id;

		//$element_id = wp_insert_post( $page_data );

		$wpdb->insert( $wpdb->prefix . "icl_translations", array(
				'element_type'			=> 'post_page',
				'element_id'			=> $element_id,
				'trid'					=> $trid,
				'language_code'			=> $lang_code,
				'source_language_code'	=> $source_lang_code,
			) );

		return $element_id;
	}

	public static function auto_create_page_for_wpml(  $original_id, $slug, $page_title = '', $page_content = '' ) {
		if ( class_exists('SitePress') ) {
			global $sitepress;
			$active_languages = $sitepress->get_active_languages();
			if ( is_array($active_languages)  && count($active_languages) > 0 ) {
				$source_lang_code = $sitepress->get_default_language();
				$trid = $sitepress->get_element_trid( $original_id, 'post_page' );
				foreach ( $active_languages as $language ) {
					if ( $language['code'] == $source_lang_code ) continue;
					self::create_page_wpml( $trid, $language['code'], $source_lang_code, $slug.'-'.$language['code'], $page_title.' '.$language['display_name'], $page_content );
				}
			}
		}
	}

	public static function add_query_vars( $aVars ) {
		$argument_vars = self::get_argument_vars();
		foreach ( $argument_vars as $avar ) {
			$aVars[] = $avar;
		}

		return $aVars;
	}

	public static function add_page_rewrite_rules( $aRules, $page_id ) {
		$search_page = get_page( $page_id );

		if ( ! empty( $search_page ) ) {

			$search_page_slug = $search_page->post_name;
			$argument_vars    = self::get_argument_vars();

			$rewrite_rule   = '';
			$original_url   = '';
			$number_matches = 0;
			foreach ( $argument_vars as $avar ) {
				$number_matches++;
				$rewrite_rule .= $avar.'/([^/]*)/';
				$original_url .= '&'.$avar.'=$matches['.$number_matches.']';
			}

			$aNewRules = array($search_page_slug.'/'.$rewrite_rule.'?$' => 'index.php?pagename='.$search_page_slug.$original_url);
			$aRules = $aNewRules + $aRules;

		}

		return $aRules;
	}

	public static function add_rewrite_rules( $aRules ) {
		global $wpdb;
		global $wpps_search_page_id;

		$shortcode   = 'wpps_search';
		$option_name = 'wpps_search_page_id';

		$page_id = $wpps_search_page_id;

		$aRules      = self::add_page_rewrite_rules( $aRules, $page_id );

		// For WPML
		if ( class_exists('SitePress') ) {
			global $sitepress;
			$translation_page_data = null;
			$trid = $sitepress->get_element_trid( $page_id, 'post_page' );
			if ( $trid ) {
				$translation_page_data = $wpdb->get_results( $wpdb->prepare( "SELECT element_id FROM " . $wpdb->prefix . "icl_translations WHERE trid = %d AND element_type='post_page' AND element_id != %d", $trid , $page_id ) );
				if ( is_array( $translation_page_data ) && count( $translation_page_data ) > 0 ) {
					foreach( $translation_page_data as $translation_page ) {
						$aRules = self::add_page_rewrite_rules( $aRules, $translation_page->element_id );
					}
				}
			}
		}

		return $aRules;
	}

	public static function strip_shortcodes ($content='') {
		$content = preg_replace( '|\[(.+?)\](.+?\[/\\1\])?|s', '', $content);

		return $content;
	}

	public static function get_terms_object( $object_id, $taxonomy = 'category', $post_parent = 0 ) {
		$terms_list = array();

		if ( (int) $post_parent > 0 ) {
			$object_id = (int) $post_parent;
		}

		$terms = get_the_terms( $object_id, $taxonomy );

		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $terms ) {
				$terms_list[] = array(
					'name'	=> $terms->name,
					'url'	=> get_term_link($terms->slug, $taxonomy )
				);
			}
		}

		return $terms_list;
	}

	/**
	 * Get post thumbnail url
	 */
	public static function get_post_thumbnail_url( $post_id, $post_parent = 0, $size = 'medium' ) {

		$mediumSRC = '';

		// Return Feature Image URL
		if ( has_post_thumbnail( $post_id ) ) {
			$thumbid = get_post_thumbnail_id( $post_id );
			$attachmentArray = wp_get_attachment_image_src( $thumbid, $size, false );
			if ( $attachmentArray ) {
				$mediumSRC = $attachmentArray[0];
				if ( trim( $mediumSRC ) != '' ) {
					return $mediumSRC;
				}
			}
		}

		// Return First Image URL in gallery of this post
		if ( $post_parent == 0 && trim( $mediumSRC ) == '' ) {
			$args = array( 'post_parent' => $post_id , 'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'ID', 'post_status' => null );
			$attachments = get_posts( $args );
			if ( $attachments ) {
				foreach ( $attachments as $attachment ) {
					$attachmentArray = wp_get_attachment_image_src( $attachment->ID, $size, false );
					if ( $attachmentArray ) {
						$mediumSRC = $attachmentArray[0];
						if ( trim( $mediumSRC ) != '' ) {
							return $mediumSRC;
						}
					}
				}
			}
		}

		// Ger Image URL of parent post
		if ( $post_parent > 0 && trim( $mediumSRC ) == '' ) {

			// Set ID of parent post if one exists
			$post_id = $post_parent;

			if ( has_post_thumbnail( $post_id ) ) {
				$thumbid = get_post_thumbnail_id( $post_id );
				$attachmentArray = wp_get_attachment_image_src( $thumbid, $size, false );
				if ( $attachmentArray ) {
					$mediumSRC = $attachmentArray[0];
					if ( trim( $mediumSRC ) != '' ) {
						return $mediumSRC;
					}
				}
			}

			if ( trim( $mediumSRC ) == '' ) {
				$args = array( 'post_parent' => $post_id , 'numberposts' => 1, 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'DESC', 'orderby' => 'ID', 'post_status' => null );
				$attachments = get_posts( $args );
				if ( $attachments ) {
					foreach ( $attachments as $attachment ) {
						$attachmentArray = wp_get_attachment_image_src( $attachment->ID, $size, false );
						if ( $attachmentArray ) {
							$mediumSRC = $attachmentArray[0];
							if ( trim( $mediumSRC ) != '' ) {
								return $mediumSRC;
							}
						}
					}
				}
			}
		}

		// Use place holder image of Woo
		if ( trim( $mediumSRC ) == '' ) {
			$wpps_all_results_pages_settings = get_option( 'wpps_all_results_pages_settings' );
			$results_display_type = isset( $wpps_all_results_pages_settings['display_type'] ) ? $wpps_all_results_pages_settings['display_type'] : 'grid';
			if ( 'grid' === $results_display_type ) {
				$placeholder_image = WPPS_IMAGES_URL . '/placeholder-grid.png';
			} else {
				$placeholder_image = WPPS_IMAGES_URL . '/placeholder.png';
			}

			$mediumSRC = apply_filters( 'wpps_post_placeholder_img_src', $placeholder_image );
		}

		return $mediumSRC;
	}

	public static function get_term_thumbnail( $term_id, $size = 'medium' ) {

		$image= '';

		$thumbnail_id  = get_term_meta( $term_id, 'thumbnail_id', true  );

		if ( $thumbnail_id ) {
			$image = wp_get_attachment_image_src( $thumbnail_id, $size  );
			$image = $image[0];
		}

		if ( trim( $image ) != '' ) {
			return $image;
		} else {
			return apply_filters( 'wpps_term_placeholder_img_src', '' );
		}
	}
}
