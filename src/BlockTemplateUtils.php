<?php
namespace A3Rev\WPPredictiveSearch\Blocks;

/**
 * BlockTemplateUtils class used for serving block templates from Woo Blocks.
 * IMPORTANT: These methods have been duplicated from Gutenberg/lib/full-site-editing/block-templates.php as those functions are not for public usage.
 */
class BlockTemplateUtils
{
    /**
     * Directory names for block templates
     *
     * Directory names conventions for block templates have changed with Gutenberg 12.1.0,
     * however, for backwards-compatibility, we also keep the older conventions, prefixed
     * with `DEPRECATED_`.
     *
     * @var array {
     *     @var string DEPRECATED_TEMPLATES  Old directory name of the block templates directory.
     *     @var string DEPRECATED_TEMPLATE_PARTS  Old directory name of the block template parts directory.
     *     @var string TEMPLATES_DIR_NAME  Directory name of the block templates directory.
     *     @var string TEMPLATE_PARTS_DIR_NAME  Directory name of the block template parts directory.
     * }
     */
    const DIRECTORY_NAMES = array(
        'DEPRECATED_TEMPLATES'      => 'block-templates',
        'DEPRECATED_TEMPLATE_PARTS' => 'block-template-parts',
        'TEMPLATES'                 => 'templates',
        'TEMPLATE_PARTS'            => 'parts',
    );

    /**
     * Returns an array containing the references of
     * the passed blocks and their inner blocks.
     *
     * @param array $blocks array of blocks.
     *
     * @return array block references to the passed blocks and their inner blocks.
     */
    public static function gutenberg_flatten_blocks(&$blocks)
    {
        $all_blocks = array();
        $queue      = array();
        foreach ($blocks as &$block) {
            $queue[] = &$block;
        }
        $queue_count = count($queue);

        while ($queue_count > 0) {
            $block = &$queue[0];
            array_shift($queue);
            $all_blocks[] = &$block;

            if (! empty($block['innerBlocks'])) {
                foreach ($block['innerBlocks'] as &$inner_block) {
                    $queue[] = &$inner_block;
                }
            }

            $queue_count = count($queue);
        }

        return $all_blocks;
    }

    /**
     * Parses wp_template content and injects the current theme's
     * stylesheet as a theme attribute into each wp_template_part
     *
     * @param string $template_content serialized wp_template content.
     *
     * @return string Updated wp_template content.
     */
    public static function inject_theme_attribute_in_content($template_content)
    {
        $has_updated_content = false;
        $new_content         = '';
        $template_blocks     = parse_blocks($template_content);

        $blocks = self::gutenberg_flatten_blocks($template_blocks);
        foreach ($blocks as &$block) {
            if ('core/template-part' === $block['blockName'] &&
                ! isset($block['attrs']['theme'])
            ) {
                $block['attrs']['theme'] = wp_get_theme()->get_stylesheet();
                $has_updated_content     = true;
            }
        }

        if ($has_updated_content) {
            foreach ($template_blocks as &$block) {
                $new_content .= serialize_block($block);
            }

            return $new_content;
        }

        return $template_content;
    }

    /**
     * Build a unified template object based a post Object.
     *
     * @param \WP_Post $post Template post.
     *
     * @return \WP_Block_Template|\WP_Error Template.
     */
    public static function build_template_result_from_post($post)
    {
        $terms = get_the_terms($post, 'wp_theme');

        if (is_wp_error($terms)) {
            return $terms;
        }

        if (! $terms) {
            return new \WP_Error( 'template_missing_theme', __( 'No theme is defined for this template.' ));
        }

        $theme          = $terms[0]->name;
        if ('wp_template_part' === $post->post_type) {
            $theme = wp_get_theme()->get_stylesheet();
        }

        $has_theme_file = true;

        $template                 = new \WP_Block_Template();
        $template->wp_id          = $post->ID;
        $template->id             = $theme . '//' . $post->post_name;
        $template->theme          = $theme;
        $template->content        = $post->post_content;
        $template->slug           = $post->post_name;
        $template->source         = 'custom';
        $template->type           = $post->post_type;
        $template->description    = $post->post_excerpt;
        $template->title          = $post->post_title;
        $template->status         = $post->post_status;
        $template->has_theme_file = $has_theme_file;
        $template->is_custom      = true;
        $template->post_types     = array( 'page' ); // Don't appear in any Edit Post template selector dropdown.

        if ('wp_template_part' === $post->post_type) {
            $type_terms = get_the_terms($post, 'wp_template_part_area');
            if (! is_wp_error($type_terms) && false !== $type_terms) {
                $template->area = $type_terms[0]->name;
            }
        }

        if ('wp-predictive-search' === $theme) {
            $template->origin = 'plugin';
        }

        return $template;
    }

    /**
     * Build a unified template object based on a theme file.
     *
     * @param array $template_file Theme file.
     * @param array $template_type wp_template or wp_template_part.
     *
     * @return \WP_Block_Template Template.
     */
    public static function build_template_result_from_file($template_file, $template_type)
    {
        $template_file = (object) $template_file;

        // If the theme has an archive-portfolios.html template but does not have portfolio taxonomy templates
        // then we will load in the archive-portfolio.html template from the theme to use for portfolio taxonomies on the frontend.
        $template_is_from_theme = 'theme' === $template_file->source;
        $theme_name             = 'wp-predictive-search';
        if ('wp_template_part' === $template_type || $template_is_from_theme ) {
            $theme_name = wp_get_theme()->get_stylesheet();
        }

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        $template_content  = file_get_contents($template_file->path);
        $template          = new \WP_Block_Template();
        $template->id      = $theme_name . '//' . $template_file->slug;
        $template->theme   = $theme_name;
        $template->content = self::inject_theme_attribute_in_content($template_content);
        // Plugin was agreed as a valid source value despite existing inline docs at the time of creating: https://github.com/WordPress/gutenberg/issues/36597#issuecomment-976232909.
        $template->source         = $template_file->source ? $template_file->source : 'plugin';
        $template->slug           = $template_file->slug;
        $template->type           = $template_type;
        $template->title          = ! empty( $template_file->title ) ? $template_file->title : self::get_block_template_title( $template_file->slug );
        $template->description    = ! empty( $template_file->description ) ? $template_file->description : self::get_block_template_description( $template_file->slug );
        $template->status         = 'publish';
        $template->has_theme_file = true;
        $template->origin         = $template_file->source;
        $template->is_custom      = true; // Templates loaded from the filesystem aren't custom, ones that have been edited and loaded from the DB are.
        $template->post_types     = array( 'page' ); // Don't appear in any Edit Post template selector dropdown.
        $template->area           = 'uncategorized';

        return $template;
    }

    /**
     * Build a new template object so that we can make Woo Blocks default templates available in the current theme should they not have any.
     *
     * @param string $template_file Block template file path.
     * @param string $template_type wp_template or wp_template_part.
     * @param string $template_slug Block template slug e.g. single-a3-portfolio.
     * @param bool   $template_is_from_theme If the block template file is being loaded from the current theme instead of Woo Blocks.
     *
     * @return object Block template object.
     */
    public static function create_new_block_template_object($template_file, $template_type, $template_slug, $template_is_from_theme = false)
    {
        $theme_name = 'wp-predictive-search';
        if ('wp_template_part' === $template_type || $template_is_from_theme ) {
            $theme_name = wp_get_theme()->get_stylesheet();
        }

        $new_template_item = array(
            'slug'        => $template_slug,
            'id'          => $theme_name . '//' . $template_slug,
            'path'        => $template_file,
            'type'        => $template_type,
            'theme'       => $theme_name,
            // Plugin was agreed as a valid source value despite existing inline docs at the time of creating: https://github.com/WordPress/gutenberg/issues/36597#issuecomment-976232909.
            'source'      => $template_is_from_theme ? 'theme' : 'plugin',
            'title'       => self::get_block_template_title( $template_slug ),
            'description' => self::get_block_template_description( $template_slug ),
            'is_custom'   => true,
            'post_types'  => array( 'page' ), // Don't appear in any Edit Post template selector dropdown.
        );

        return (object) $new_template_item;
    }

    /**
     * Finds all nested template part file paths in a theme's directory.
     *
     * @param string $base_directory The theme's file path.
     * @return array $path_list A list of paths to all template part files.
     */
    public static function gutenberg_get_template_paths($base_directory)
    {
        $path_list = array();
        if (file_exists($base_directory)) {
            $nested_files      = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($base_directory));
            $nested_html_files = new \RegexIterator($nested_files, '/^.+\.html$/i', \RecursiveRegexIterator::GET_MATCH);
            foreach ($nested_html_files as $path => $file) {
                $path_list[] = $path;
            }
        }
        return $path_list;
    }

    /**
     * Returns template titles.
     *
     * @param string $template_slug The templates slug (e.g. single-product).
     * @return string Human friendly title.
     */
    public static function get_block_template_title( $template_slug ) {
        $plugin_template_types = self::get_plugin_block_template_types();
        if ( isset( $plugin_template_types[ $template_slug ] ) ) {
            return $plugin_template_types[ $template_slug ]['title'];
        } else {
            // Human friendly title converted from the slug.
            return ucwords( preg_replace( '/[\-_]/', ' ', $template_slug ) );
        }
    }

    /**
     * Returns template descriptions.
     *
     * @param string $template_slug The templates slug (e.g. single-product).
     * @return string Template description.
     */
    public static function get_block_template_description( $template_slug ) {
        $plugin_template_types = self::get_plugin_block_template_types();
        if ( isset( $plugin_template_types[ $template_slug ] ) ) {
            return $plugin_template_types[ $template_slug ]['description'];
        }
        return '';
    }

    /**
     * Returns a filtered list of plugin template types, containing their
     * localized titles and descriptions.
     *
     * @return array The plugin template types.
     */
    public static function get_plugin_block_template_types() {
        $plugin_template_types = array(
            'ps-all-results'    => array(
                'title'       => _x( 'Predictive Search All Results Page', 'Template name', 'wp-predictive-search' ),
                'description' => __( 'Template used to display search results for WP Predictive Search.', 'wp-predictive-search' ),
            ),
            'ps-all-results-item'   => array(
                'title'       => _x( 'Predictive Search All Results Card', 'Template name', 'wp-predictive-search' ),
                'description' => __( 'Display item inside search results for WP Predictive Search.', 'wp-predictive-search' ),
            ),
        );

        return $plugin_template_types;
    }

    /**
     * Converts template paths into a slug
     *
     * @param string $path The template's path.
     * @param string $directory_name The template's directory name.
     * @return string slug
     */
    public static function generate_template_slug_from_path($path, $directory_name = 'templates')
    {
        return substr(
            $path,
            strpos($path, $directory_name . DIRECTORY_SEPARATOR) + 1 + strlen($directory_name),
            -5
        );
    }

    /**
     * Gets the first matching template part within themes directories
     *
     * Since [Gutenberg 12.1.0](https://github.com/WordPress/gutenberg/releases/tag/v12.1.0), the conventions for
     * block templates and parts directory has changed from `block-templates` and `block-templates-parts`
     * to `templates` and `parts` respectively.
     *
     * This function traverses all possible combinations of directory paths where a template or part
     * could be located and returns the first one which is readable, prioritizing the new convention
     * over the deprecated one, but maintaining that one for backwards compatibility.
     *
     * @param string $template_slug  The slug of the template (i.e. without the file extension).
     * @param string $template_type  Either `wp_template` or `wp_template_part`.
     *
     * @return string|null  The matched path or `null` if no match was found.
     */
    public static function get_theme_template_path($template_slug, $template_type = 'wp_template')
    {
        $template_filename      = $template_slug . '.html';
        $possible_templates_dir = 'wp_template' === $template_type ? array(
            self::DIRECTORY_NAMES['TEMPLATES'],
            self::DIRECTORY_NAMES['DEPRECATED_TEMPLATES'],
        ) : array(
            self::DIRECTORY_NAMES['TEMPLATE_PARTS'],
            self::DIRECTORY_NAMES['DEPRECATED_TEMPLATE_PARTS'],
        );

        // Combine the possible root directory names with either the template directory
        // or the stylesheet directory for child themes.
        $possible_paths = array_reduce(
            $possible_templates_dir,
            function ($carry, $item) use ($template_filename) {
                $filepath = DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . $template_filename;

                $carry[] = get_template_directory() . $filepath;
                $carry[] = get_stylesheet_directory() . $filepath;

                return $carry;
            },
            array()
        );

        // Return the first matching.
        foreach ($possible_paths as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Check if the theme has a template. So we know if to load our own in or not.
     *
     * @param string $template_name name of the template file without .html extension e.g. 'single-a3-portfolio'.
     * @return boolean
     */
    public static function theme_has_template($template_name)
    {
        return ! ! self::get_theme_template_path($template_name, 'wp_template');
    }

    /**
     * Check if the theme has a template. So we know if to load our own in or not.
     *
     * @param string $template_name name of the template file without .html extension e.g. 'single-a3-portfolio'.
     * @return boolean
     */
    public static function theme_has_template_part($template_name)
    {
        return ! ! self::get_theme_template_path($template_name, 'wp_template_part');
    }

    /**
     * Checks to see if they are using a compatible version of WP, or if not they have a compatible version of the Gutenberg plugin installed.
     *
     * @return boolean
     */
    public static function supports_block_templates()
    {
        if (( ! function_exists('wp_is_block_theme') || ! wp_is_block_theme() ) &&
            ( ! function_exists('gutenberg_supports_block_templates') || ! gutenberg_supports_block_templates() )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Sets the `has_theme_file` to `true` for templates with fallbacks
     *
     * There are cases (such as tags and categories) in which fallback templates
     * can be used; so, while *technically* the theme doesn't have a specific file
     * for them, it is important that we tell Gutenberg that we do, in fact,
     * have a theme file (i.e. the fallback one).
     *
     * **Note:** this function changes the array that has been passed.
     *
     * It returns `true` if anything was changed, `false` otherwise.
     *
     * @param array  $query_result Array of template objects.
     * @param object $template A specific template object which could have a fallback.
     *
     * @return boolean
     */
    public static function set_has_theme_file_if_fallback_is_available($query_result, $template)
    {
        foreach ($query_result as &$query_result_template) {
            if ($query_result_template->slug === $template->slug
                && $query_result_template->theme === $template->theme
            ) {

                return true;
            }
        }

        return false;
    }

    /**
     * Removes templates that were added to a theme's block-templates directory, but already had a customised version saved in the database.
     *
     * @param \WP_Block_Template[]|\stdClass[] $templates List of templates to run the filter on.
     *
     * @return array List of templates with duplicates removed. The customised alternative is preferred over the theme default.
     */
    public static function remove_theme_templates_with_custom_alternative( $templates ) {

        // Get the slugs of all templates that have been customised and saved in the database.
        $customised_template_slugs = array_map(
            function( $template ) {
                return $template->slug;
            },
            array_values(
                array_filter(
                    $templates,
                    function( $template ) {
                        // This template has been customised and saved as a post.
                        return 'custom' === $template->source;
                    }
                )
            )
        );

        // Remove theme (i.e. filesystem) templates that have the same slug as a customised one. We don't need to check
        // for `woocommerce` in $template->source here because woocommerce templates won't have been added to $templates
        // if a saved version was found in the db. This only affects saved templates that were saved BEFORE a theme
        // template with the same slug was added.
        return array_values(
            array_filter(
                $templates,
                function( $template ) use ( $customised_template_slugs ) {
                    // This template has been customised and saved as a post, so return it.
                    return ! ( 'theme' === $template->source && in_array( $template->slug, $customised_template_slugs, true ) );
                }
            )
        );
    }
}
