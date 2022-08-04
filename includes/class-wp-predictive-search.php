<?php
/**
 * WordPress Predictive Search
 *
 * Table Of Contents
 *
 * install_databases()
 * set_tables_wpdbfix()
 */

namespace A3Rev\WPPredictiveSearch;

class Main
{

	public function __construct() {
		// Set Predictive Search Tables
		add_action( 'plugins_loaded', array( $this, 'set_tables_wpdbfix' ), 0 );
		add_action( 'switch_blog', array( $this, 'set_tables_wpdbfix' ), 0 );
	}

	public function install_databases() {
		global $wpps_keyword_data;
		global $wpps_postmeta_data;
		global $wpps_posts_data;
		global $wpps_exclude_data;
		global $wpps_term_relationships_data;
		global $wpps_taxonomy_data;

		$wpps_posts_data->install_database();
		$wpps_keyword_data->install_database();
		$wpps_postmeta_data->install_database();
		$wpps_exclude_data->install_database();
		$wpps_term_relationships_data->install_database();
		$wpps_taxonomy_data->install_database();
	}

	public function set_tables_wpdbfix() {
		global $wpps_keyword_data;
		global $wpps_postmeta_data;
		global $wpps_posts_data;
		global $wpps_exclude_data;
		global $wpps_term_relationships_data;
		global $wpps_taxonomy_data;

		$wpps_posts_data->set_table_wpdbfix();
		$wpps_keyword_data->set_table_wpdbfix();
		$wpps_postmeta_data->set_table_wpdbfix();
		$wpps_exclude_data->set_table_wpdbfix();
		$wpps_term_relationships_data->set_table_wpdbfix();
		$wpps_taxonomy_data->set_table_wpdbfix();
	}

	public function taxonomies_support() {
		$taxonomies = array (
			'category' => array ( 
					'name'  => 'category',
					'label' => __( 'Post Categories', 'wp-predictive-search' ),
					'color' => '#7ad03a',
				),
			'post_tag' => array ( 
					'name'  => 'post_tag',
					'label' => __( 'Post Tags', 'wp-predictive-search' ),
					'color' => '#7ad03a',
				),
		);

		return apply_filters( 'wpps_taxonomies_support', $taxonomies );
	}

	public function taxonomies_slug_support() {
		$taxonomies_support = $this->taxonomies_support();

		$taxonomies_slug = array( 'category', 'post_tag' );
		if ( ! empty( $taxonomies_support ) ) {
			$taxonomies_slug = array_keys( $taxonomies_support );
		}

		return $taxonomies_slug;
	}

	public function posttypes_support() {
		$posttypes = array (
			'post' => array ( 
					'name'  => 'post',
					'label' => __( 'Post', 'wp-predictive-search' ),
					'color' => '#7ad03a',
				),
			'page' => array ( 
					'name'  => 'page',
					'label' => __( 'Page', 'wp-predictive-search' ),
					'color' => '#0073aa',
				),
		);

		return apply_filters( 'wpps_posttypes_support', $posttypes );
	}

	public function posttypes_slug_support() {
		$posttypes_support = $this->posttypes_support();

		$posttypes_slug = array( 'post', 'page' );
		if ( ! empty( $posttypes_support ) ) {
			$posttypes_slug = array_keys( $posttypes_support );
		}

		return $posttypes_slug;
	}

	public function custom_types_support() {
		$custom_types_support = apply_filters( 'wpps_custom_types_support', array() );

		return $custom_types_support;
	}

	public function get_items_search() {
		$posttypes_support = $this->posttypes_support();
		$taxonomies_support = $this->taxonomies_support();

		if ( ! empty( $posttypes_support ) ) {
			foreach ( $posttypes_support as $posttype ) {
				$items_search[$posttype['name']] = array( 'number' => 6, 'name' => $posttype['label'] );
			}
		} else {
			$items_search = array(
				'post'     => array( 'number' => 6, 'name' => wpps_ict_t__( 'Posts', __('Posts', 'wp-predictive-search' ) ) ),
				'page'     => array( 'number' => 6, 'name' => wpps_ict_t__( 'Pages', __('Pages', 'wp-predictive-search' ) ) ),
			);
		}

		$items_search = apply_filters( 'wpps_custom_types_items_search', $items_search );

		if ( ! empty( $taxonomies_support ) ) {
			foreach ( $taxonomies_support as $taxonomy ) {
				$items_search[$taxonomy['name']] = array( 'number' => 0, 'name' => $taxonomy['label'] );
			}
		}
			
		return apply_filters( 'wpps_get_items_search', $items_search );
	}

	public function general_sql( $main_sql ) {

		$select_sql = '';
		if ( is_array( $main_sql['select'] ) && count( $main_sql['select'] ) > 0 ) {
			$select_sql = implode( ', ', $main_sql['select'] );
		} elseif ( ! is_array( $main_sql['select'] ) ) {
			$select_sql = $main_sql['select'];
		}

		$from_sql = '';
		if ( is_array( $main_sql['from'] ) && count( $main_sql['from'] ) > 0 ) {
			$from_sql = implode( ', ', $main_sql['from'] );
		} elseif ( ! is_array( $main_sql['from'] ) ) {
			$from_sql = $main_sql['from'];
		}

		$join_sql = '';
		if ( is_array( $main_sql['join'] ) && count( $main_sql['join'] ) > 0 ) {
			$join_sql = implode( ' ', $main_sql['join'] );
		} elseif ( ! is_array( $main_sql['join'] ) ) {
			$join_sql = $main_sql['join'];
		}

		$where_sql = '';
		$where_search_sql = '';
		if ( is_array( $main_sql['where'] ) && count( $main_sql['where'] ) > 0 ) {
			if ( isset( $main_sql['where']['search'] ) ) {
				$where_search = $main_sql['where']['search'];
				unset( $main_sql['where']['search'] );
				if ( is_array( $where_search ) && count( $where_search ) > 0 ) {
					$where_search_sql = implode( ' ', $where_search );
				} elseif ( ! is_array( $where_search ) ) {
					$where_search_sql = $where_search;
				}
			}
			$where_sql = implode( ' ', $main_sql['where'] );
		} elseif ( ! is_array( $main_sql['where'] ) ) {
			$where_sql = $main_sql['where'];
		}

		$groupby_sql = '';
		if ( is_array( $main_sql['groupby'] ) && count( $main_sql['groupby'] ) > 0 ) {
			$groupby_sql = implode( ', ', $main_sql['groupby'] );
		} elseif ( ! is_array( $main_sql['groupby'] ) ) {
			$groupby_sql = $main_sql['groupby'];
		}

		$orderby_sql = '';
		if ( is_array( $main_sql['orderby'] ) && count( $main_sql['orderby'] ) > 0 ) {
			$orderby_sql = implode( ', ', $main_sql['orderby'] );
		} elseif ( ! is_array( $main_sql['orderby'] ) ) {
			$orderby_sql = $main_sql['orderby'];
		}

		$limit_sql = $main_sql['limit'];

		$sql = 'SELECT ';
		if ( '' != trim( $select_sql ) ) {
			$sql .= $select_sql;
		}

		$sql .= ' FROM ';
		if ( '' != trim( $from_sql ) ) {
			$sql .= $from_sql . ' ';
		}

		if ( '' != trim( $join_sql ) ) {
			$sql .= $join_sql . ' ';
		}

		if ( '' != trim( $where_sql ) || '' != trim( $where_search_sql ) ) {
			$sql .= ' WHERE ';
			$sql .= $where_sql . ' ';

			if ( '' != trim( $where_search_sql ) ) {
				if ( '' != trim( $where_sql ) ) {
					$sql .= ' AND ( ' . $where_search_sql . ' ) ';
				} else {
					$sql .= $where_search_sql;
				}
			}
		}

		if ( '' != trim( $groupby_sql ) ) {
			$sql .= ' GROUP BY ';
			$sql .= $groupby_sql . ' ';
		}

		if ( '' != trim( $orderby_sql ) ) {
			$sql .= ' ORDER BY ';
			$sql .= $orderby_sql . ' ';
		}

		if ( '' != trim( $limit_sql ) ) {
			$sql .= ' LIMIT ';
			$sql .= $limit_sql . ' ';
		}

		return $sql;
	}

	public function get_post_search_sql( $search_keyword, $row, $start = 0, $wpps_search_focus_enable = '', $wpps_search_focus_plugin = '', $post_type = 'post', $term_id = 0, $current_lang = '', $check_exsited = false ) {
		global $wpdb;

		$row += 1;

		$search_keyword_nospecial = preg_replace( "/[^a-zA-Z0-9_.\s]/", " ", $search_keyword );
		if ( $search_keyword == $search_keyword_nospecial ) {
			$search_keyword_nospecial = '';
		} else {
			$search_keyword_nospecial = $wpdb->esc_like( trim( $search_keyword_nospecial ) );
		}

		$search_keyword	= $wpdb->esc_like( trim( $search_keyword ) );

		$main_sql               = array();
		$term_relationships_sql = array();
		$ps_keyword_sql         = array();
		$postmeta_sql           = array();
		$wpml_sql               = array();

		global $wpps_posts_data;
		$main_sql = $wpps_posts_data->get_sql( $search_keyword, $search_keyword_nospecial, $post_type, $row, $start, $check_exsited );

		if ( $term_id > 0 ) {
			global $wpps_term_relationships_data;
			$term_relationships_sql = $wpps_term_relationships_data->get_sql( $term_id );
		}

		if ( empty( $wpps_search_focus_enable ) || $wpps_search_focus_enable == 'yes' ) {
			global $wpps_keyword_data;
			$ps_keyword_sql = $wpps_keyword_data->get_sql( $search_keyword, $search_keyword_nospecial );

			if ( in_array( $wpps_search_focus_plugin, array( 'yoast_seo_plugin', 'all_in_one_seo_plugin' ) ) ) {
				global $wpps_postmeta_data;
				$meta_key_name = '_aioseop_keywords';
				if ( 'yoast_seo_plugin' == $wpps_search_focus_plugin ) {
					$meta_key_name = '_yoast_wpseo_focuskw';
				}

				$postmeta_sql = $wpps_postmeta_data->get_sql( $search_keyword, $search_keyword_nospecial, $meta_key_name );
			}
		}

		if ( class_exists('SitePress') && '' != $current_lang ) {
			$wpml_sql['join']    = " INNER JOIN ".$wpdb->prefix."icl_translations AS ic ON (ic.element_id = pp.post_id) ";
			$wpml_sql['where'][] = " AND ic.language_code = '".$current_lang."' AND ic.element_type = 'post_{$post_type}' ";
		}

		$main_sql = array_merge_recursive( $main_sql, $term_relationships_sql );
		$main_sql = array_merge_recursive( $main_sql, $wpml_sql );
		$main_sql = array_merge_recursive( $main_sql, $ps_keyword_sql );
		$main_sql = array_merge_recursive( $main_sql, $postmeta_sql );

		$sql = $this->general_sql( $main_sql );

		return $sql;
	}

	/**
	 * Check post is exsited from search term
	 */
	public function check_post_exsited( $search_keyword, $wpps_search_focus_enable, $wpps_search_focus_plugin, $post_type = 'post', $term_id = 0, $current_lang = '' ) {
		global $wpdb;

		$sql = $this->get_post_search_sql( $search_keyword, 1, 0, $wpps_search_focus_enable, $wpps_search_focus_plugin, $post_type, $term_id, $current_lang, true );

		$sql = "SELECT EXISTS( " . $sql . ")";

		$have_item = $wpdb->get_var( $sql );
		if ( $have_item == '1' ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Get array post list
	 */
	public function get_post_results( $search_keyword, $row, $start = 0, $wpps_search_focus_enable = '', $wpps_search_focus_plugin = '', $post_term_id = 0, $text_lenght = 100, $current_lang = '', $post_type = 'post', $include_header = true , $show_categories = false, $show_tags = false, $in_results_page = false ) {
		global $wpdb;
		global $predictive_search_description_source;
		global $wpps_search_page_content_type;

		$posttypes_support = $this->posttypes_support();

		$total_post = 0;
		$have_post = $this->check_post_exsited( $search_keyword, $wpps_search_focus_enable, $wpps_search_focus_plugin, $post_type, $post_term_id, $current_lang );
		if ( ! $have_post ) {
			$item_list = array( 'total' => $total_post, 'search_in_name' => isset( $posttypes_support[ $post_type ] ) ? $posttypes_support[ $post_type ]['label'] : wpps_convert_key_to_label( $post_type ) );
			return $item_list;
		}

		$sql = $this->get_post_search_sql( $search_keyword, $row, $start, $wpps_search_focus_enable, $wpps_search_focus_plugin, $post_type, $post_term_id, $current_lang, false );

		$search_posts = $wpdb->get_results( $sql );

		$total_post = count( $search_posts );
		$item_list = array( 'total' => $total_post, 'search_in_name' => isset( $posttypes_support[ $post_type ] ) ? $posttypes_support[ $post_type ]['label'] : wpps_convert_key_to_label( $post_type ) );
		if ( $search_posts && $total_post > 0 ) {
			$item_list['items'] = array();

			if ( $include_header ) {
				$item_list['items'][] = array(
					'title' 	=> isset( $posttypes_support[ $post_type ] ) ? $posttypes_support[ $post_type ]['label'] : wpps_convert_key_to_label( $post_type ),
					'keyword'	=> $search_keyword,
					'type'		=> 'header'
				);
			}

			$wpps_all_results_pages_settings = get_option( 'wpps_all_results_pages_settings' );

			$template_type = isset( $wpps_all_results_pages_settings['template_type'] ) ? $wpps_all_results_pages_settings['template_type'] : 'plugin';

			if ( $in_results_page && 'block' === $wpps_search_page_content_type ) {

				foreach ( $search_posts as $item ) {
					global $psobject;

					$psobject         = new \stdClass();
					$psobject->id     = $item->post_id;
					$psobject->title  = $item->post_title;
					$psobject->object = 'post';
					$psobject->type   = $post_type;

					do_action( 'wpps_block_post_results', $post_type, $psobject, $search_keyword );

					$block_content = wpps_get_block_card_item();

					$card_html = '<div class="wp-block-post">' . $block_content . '</div>';

					$item_list['items'][] = array( 'card' => $card_html );

					$row-- ;
					if ( $row < 1 ) break;
				}

			} elseif ( $in_results_page && 'theme' === $template_type ) {

				foreach ( $search_posts as $item ) {
					$post_object = get_post( $item->post_id );

					setup_postdata( $GLOBALS['post'] =& $post_object );

					$card_html = apply_filters( 'wpps_post_item_card_html', '', $item->post_id, $post_type, $show_categories, $show_tags );

					if ( empty( $card_html ) ) {
						ob_start();
						get_template_part( 'content' );
						$card_html = ob_get_clean();
					}

					$item_list['items'][] = array( 'card' => $card_html );

					$row-- ;
					if ( $row < 1 ) break;
				}

			} else {

				$thumbnail_size_name = apply_filters( 'wpps_post_thumbnail_size_name', 'medium', $post_type );

				foreach ( $search_posts as $item ) {

					$post_data = get_post( $item->post_id );
					$item_content = Functions::woops_limit_words( strip_tags( Functions::strip_shortcodes( strip_shortcodes ( $post_data->post_content ) ) ), $text_lenght, '...' );
					
					$item_excerpt = Functions::woops_limit_words( strip_tags( Functions::strip_shortcodes( strip_shortcodes( $post_data->post_excerpt ) ) ), $text_lenght, '...' );

					$item_description = $item_content;
					if ( 'excerpt' == $predictive_search_description_source ) {
						$item_description = $item_excerpt;
					}

					if ( empty( $item_description ) && 'excerpt' == $predictive_search_description_source ) {
						$item_description = $item_content;
					} elseif ( empty( $item_description ) ) {
						$item_description = $item_excerpt;
					}

					$item_data = array(
						'title'       => $item->post_title,
						'keyword'     => $item->post_title,
						'url'         => get_permalink( $item->post_id ),
						'image_url'   => Functions::get_post_thumbnail_url( $item->post_id, 0, $thumbnail_size_name ),
						'description' => $item_description,
						'type'        => $post_type
					);

					if ( $show_categories ) $item_data['categories'] = Functions::get_terms_object( $item->post_id, 'category' );
					if ( $show_tags ) $item_data['tags']             = Functions::get_terms_object( $item->post_id, 'post_tag' );

					$item_data = apply_filters( 'wpps_post_item_data', $item_data, $item->post_id, $post_type, $show_categories, $show_tags, $in_results_page );

					$item_list['items'][] = $item_data;

					$row-- ;
					if ( $row < 1 ) break;
				}
			}
		}

		return $item_list;
	}

	public function get_taxonomy_search_sql( $search_keyword, $row, $start = 0, $taxonomy = 'category', $current_lang = '', $check_exsited = false ) {
		global $wpdb;

		$row += 1;

		$search_keyword_nospecial = preg_replace( "/[^a-zA-Z0-9_.\s]/", " ", $search_keyword );
		if ( $search_keyword == $search_keyword_nospecial ) {
			$search_keyword_nospecial = '';
		} else {
			$search_keyword_nospecial = $wpdb->esc_like( trim( $search_keyword_nospecial ) );
		}

		$search_keyword	= $wpdb->esc_like( trim( $search_keyword ) );

		$main_sql = array();
		$wpml_sql = array();

		global $wpps_taxonomy_data;
		$table_alias = 'ppc';
		$main_sql    = $wpps_taxonomy_data->get_sql( $search_keyword, $search_keyword_nospecial, $row, $start, $check_exsited, $taxonomy );

		if ( class_exists('SitePress') && '' != $current_lang ) {
			$wpml_sql['join']    = " INNER JOIN ".$wpdb->prefix."icl_translations AS ic ON (ic.element_id = {$table_alias}.term_taxonomy_id) ";
			$wpml_sql['where'][] = " AND ic.language_code = '".$current_lang."' AND ic.element_type = 'tax_{$taxonomy}' ";
		}

		$main_sql = array_merge_recursive( $main_sql, $wpml_sql );

		$sql = $this->general_sql( $main_sql );

		return $sql;
	}

	/**
	 * Check term is exsited from search term
	 */
	public function check_taxonomy_exsited( $search_keyword, $taxonomy = 'category', $current_lang = '' ) {
		global $wpdb;

		$sql = $this->get_taxonomy_search_sql( $search_keyword, 1, 0, $taxonomy, $current_lang, true );

		$sql = "SELECT EXISTS( " . $sql . ")";

		$have_item = $wpdb->get_var( $sql );
		if ( $have_item == '1' ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Get array taxonomy list
	 */
	public function get_taxonomy_results( $search_keyword, $row, $start = 0, $text_lenght = 100, $taxonomy = 'category', $item_type = 'category', $header_text = '', $current_lang = '', $include_header = true, $in_results_page = false ) {
		global $wpdb;
		global $wpps_search_page_content_type;

		$have_term = $this->check_taxonomy_exsited( $search_keyword, $taxonomy, $current_lang );
		if ( ! $have_term ) {
			$item_list = array( 'total' => 0, 'search_in_name' => $header_text );
			return $item_list;
		}

		$sql = $this->get_taxonomy_search_sql( $search_keyword, $row, $start, $taxonomy, $current_lang, false );

		$search_cats = $wpdb->get_results( $sql );

		$total_cat = count($search_cats);
		$item_list = array( 'total' => $total_cat, 'search_in_name' => $header_text );
		if ( $search_cats && $total_cat > 0 ) {
			$item_list['items'] = array();

			if ( $include_header ) {
				$item_list['items'][] = array(
					'title' 	=> $header_text,
					'keyword'	=> $search_keyword,
					'type'		=> 'header'
				);
			}

			if ( $in_results_page && 'block' === $wpps_search_page_content_type ) {

				foreach ( $search_cats as $item ) {
					global $psobject;

					$psobject         = new \stdClass();
					$psobject->id     = $item->term_id;
					$psobject->title  = $item->name;
					$psobject->object = 'taxonomy';
					$psobject->type   = $taxonomy;

					do_action( 'wpps_block_taxonomy_results', $taxonomy, $psobject, $search_keyword );

					$block_content = wpps_get_block_card_item();

					$card_html = '<div class="wp-block-post">' . $block_content . '</div>';

					$item_list['items'][] = array( 'card' => $card_html );

					$row-- ;
					if ( $row < 1 ) break;
				}

			} else {

				foreach ( $search_cats as $item ) {
					$term_description = $wpdb->get_var( $wpdb->prepare( "SELECT description FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = %s ", $item->term_id, $taxonomy ) );
					$item_description = Functions::woops_limit_words( strip_tags( Functions::strip_shortcodes( strip_shortcodes ( $term_description ) ) ), $text_lenght, '...' );

					$image_url = apply_filters( 'wpps_term_image', Functions::get_term_thumbnail( $item->term_id ), $item->term_id, $taxonomy );

					$item_data = array(
						'title'       => $item->name,
						'keyword'     => $item->name,
						'url'         => get_term_link( (int)$item->term_id, $taxonomy ),
						'image_url'   => $image_url,
						'description' => $item_description,
						'type'        => $item_type
					);

					$item_list['items'][] = apply_filters( 'wpps_term_item_data', $item_data, $item->term_id, $taxonomy );

					$row-- ;
					if ( $row < 1 ) break;
				}
			}
		}

		return $item_list;
	}

	/**
	 * Get categories dropdown
	 */
	public function get_categories_nested( $taxonomy = 'category', $parent = 0, $child_space = '', $cats_excluded = NULL ) {
		global $wp_version;

		$categories_list = array();

		if ( is_null( $cats_excluded ) ) {
			global $wpps_exclude_data;
			$cats_excluded = apply_filters( 'wpps_items_excluded', $wpps_exclude_data->get_array_items( $taxonomy ), $taxonomy );
		}

		if ( version_compare( $wp_version, '4.5.0', '<' ) ) {
			$top_categories = get_terms( $taxonomy, array(
				'hierarchical' => true,
				'exclude'      => $cats_excluded,
				'parent'       => 0,
			) );
		} else {
			$top_categories = get_terms( array(
				'taxonomy'     => $taxonomy,
				'hierarchical' => true,
				'exclude'      => $cats_excluded,
				'parent'       => 0,
			) );
		}

		if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) {

			foreach( $top_categories as $p_categories_data ) {
				if ( in_array( $p_categories_data->term_id, $cats_excluded ) ) continue;

				$child_space = '';

				$category_data = array(
					'name' => $child_space . $p_categories_data->name,
					'slug' => $p_categories_data->slug,
					'url'  => get_term_link( $p_categories_data->slug, $taxonomy )
				);

				$categories_list[$p_categories_data->term_id] = $category_data;

				$child_p_categories = get_terms( $taxonomy, array(
					'hierarchical' => true,
					'exclude'      => $cats_excluded,
					'child_of'     => $p_categories_data->term_id,
				) );

				if ( ! empty( $child_p_categories ) && ! is_wp_error( $child_p_categories ) ) {

					$current_top_cat = $p_categories_data->term_id;
					$current_parent_cat = $p_categories_data->term_id;

					$child_space = '&nbsp;&nbsp;&nbsp;';
					foreach( $child_p_categories as $p_categories_data ) {
						if ( in_array( $p_categories_data->term_id, $cats_excluded ) ) continue;

						if ( $current_top_cat == $p_categories_data->parent ) {
							$child_space = '&nbsp;&nbsp;&nbsp;';
						} elseif( $current_parent_cat == $p_categories_data->parent ) {
							$child_space .= '';
						} else {
							$child_space .= '&nbsp;&nbsp;&nbsp;';
						}

						$current_parent_cat = $p_categories_data->parent;

						$category_data = array(
							'name' => $child_space . $p_categories_data->name,
							'slug' => $p_categories_data->slug,
							'url'  => get_term_link( $p_categories_data->slug, $taxonomy )
						);

						$categories_list[$p_categories_data->term_id] = $category_data;
					}
				}
			}
		}

		return $categories_list;
	}

}
