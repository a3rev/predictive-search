<?php
namespace A3Rev\WPPredictiveSearch\Blocks;

use A3Rev\WPPredictiveSearch\Blocks\BlockTemplateUtils;

/**
 * BlockTypesController class.
 *
 * @internal
 */
class BlockTemplatesController
{

    /**
     * Holds the path for the directory where the block templates will be kept.
     *
     * @var string
     */
    private $templates_directory;

    /**
     * Holds the path for the directory where the block template parts will be kept.
     *
     * @var string
     */
    private $template_parts_directory;

    /**
     * Directory name of the block template directory.
     *
     * @var string
     */
    const TEMPLATES_DIR_NAME = 'templates';

    /**
     * Directory name of the block template parts directory.
     *
     * @var string
     */
    const TEMPLATE_PARTS_DIR_NAME = 'parts';

    /**
     * Constructor.
     */
    public function __construct()
    {
            $this->templates_directory      = plugin_dir_path(__DIR__) . 'src/' . self::TEMPLATES_DIR_NAME;
            $this->template_parts_directory = plugin_dir_path(__DIR__) . 'src/' . self::TEMPLATE_PARTS_DIR_NAME;
            $this->init();
    }

    /**
     * Initialization method.
     */
    protected function init()
    {
        add_filter('pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3);
        add_filter('get_block_templates', array( $this, 'add_block_templates' ), 10, 3);
        add_filter('theme_file_path', array( $this, 'load_default_wp_template_part'), 10, 2);
    }

    /**
     * This function checks if there's a blocks template (ultimately it resolves either a saved blocks template from the
     * database or a template file in `wp-predictive-search/templates/block-templates/`)
     * to return to pre_get_posts short-circuiting the query in Gutenberg.
     *
     * @param \WP_Block_Template|null $template Return a block template object to short-circuit the default query,
     *                                               or null to allow WP to run its normal queries.
     * @param string                  $id Template unique identifier (example: theme_slug//template_slug).
     * @param array                   $template_type wp_template or wp_template_part.
     *
     * @return mixed|\WP_Block_Template|\WP_Error
     */
    public function maybe_return_blocks_template($template, $id, $template_type)
    {


        // 'get_block_template' was introduced in WP 5.9. We need to support
        // 'gutenberg_get_block_template' for previous versions of WP with
        // Gutenberg enabled.
        if (! function_exists('gutenberg_get_block_template') &&
            ! function_exists('get_block_template')
        ) {
            return $template;
        }
        $template_name_parts = explode('//', $id);
        if (count($template_name_parts) < 2) {
            return $template;
        }
        list( , $slug ) = $template_name_parts;

        // Remove the filter at this point because if we don't then this function will infinite loop.
        remove_filter('pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3);

        // Check if the theme has a saved version of this template before falling back to the portfolio one. Please note how
        // the slug has not been modified at this point, we're still using the default one passed to this hook.
        $maybe_template = function_exists('gutenberg_get_block_template') ?
            gutenberg_get_block_template($id, $template_type) :
            get_block_template($id, $template_type);

        if (null !== $maybe_template) {
            add_filter('pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3);
            return $maybe_template;
        }

        // Theme-based template didn't exist, try switching the theme to wp-predictive-search and try again. This function has
        // been unhooked so won't run again.
        add_filter('get_block_file_template', array( $this, 'get_single_block_template' ), 10, 3);

        $theme_name = 'wp-predictive-search';
        if ('wp_template_part' === $template_type ) {
            $theme_name = wp_get_theme()->get_stylesheet();
        }

        $maybe_template = function_exists('gutenberg_get_block_template') ?
            gutenberg_get_block_template( $theme_name . '//' . $slug, $template_type) :
            get_block_template( $theme_name . '//' . $slug, $template_type);

        // Re-hook this function, it was only unhooked to stop recursion.
        add_filter('pre_get_block_file_template', array( $this, 'maybe_return_blocks_template' ), 10, 3);
        remove_filter('get_block_file_template', array( $this, 'get_single_block_template' ), 10, 3);
        if (null !== $maybe_template) {
            return $maybe_template;
        }

        // At this point we haven't had any luck finding a template. Give up and let Gutenberg take control again.
        return $template;
    }

    /**
     * Runs on the get_block_template hook. If a template is already found and passed to this function, then return it
     * and don't run.
     * If a template is *not* passed, try to look for one that matches the ID in the database, if that's not found defer
     * to Blocks templates files. Priority goes: DB-Theme, DB-Blocks, Filesystem-Theme, Filesystem-Blocks.
     *
     * @param \WP_Block_Template $template The found block template.
     * @param string             $id Template unique identifier (example: theme_slug//template_slug).
     * @param array              $template_type wp_template or wp_template_part.
     *
     * @return mixed|null
     */
    public function get_single_block_template($template, $id, $template_type)
    {

        // The template was already found before the filter runs, just return it immediately.
        if (null !== $template) {
            return $template;
        }

        $template_name_parts = explode('//', $id);
        if (count($template_name_parts) < 2) {
            return $template;
        }
        list( , $slug ) = $template_name_parts;

        // If this blocks template doesn't exist then we should just skip the function and let Gutenberg handle it.
        if (! $this->block_template_is_available($slug, $template_type)) {
            return $template;
        }

        $available_templates = $this->get_block_templates(array( $slug ), $template_type);
        return ( is_array($available_templates) && count($available_templates) > 0 )
            ? BlockTemplateUtils::build_template_result_from_file($available_templates[0], $available_templates[0]->type)
            : $template;
    }

    /**
     * Add the block template objects to be used.
     *
     * @param array $query_result Array of template objects.
     * @param array $query Optional. Arguments to retrieve templates.
     * @param array $template_type wp_template or wp_template_part.
     * @return array
     */
    public function add_block_templates($query_result, $query, $template_type)
    {

        if (! BlockTemplateUtils::supports_block_templates()) {
            return $query_result;
        }

        $post_type      = isset($query['post_type']) ? $query['post_type'] : '';
        $slugs          = isset($query['slug__in']) ? $query['slug__in'] : array();

        $template_files = $this->get_block_templates($slugs, $template_type);

        // @todo: Add apply_filters to _gutenberg_get_template_files() in Gutenberg to prevent duplication of logic.
        foreach ($template_files as $template_file) {
            // If we have a template which is eligible for a fallback, we need to explicitly tell Gutenberg that
            // it has a theme file (because it is using the fallback template file). And then `continue` to avoid
            // adding duplicates.
            if (BlockTemplateUtils::set_has_theme_file_if_fallback_is_available($query_result, $template_file)) {
                continue;
            }

            // If the current $post_type is set (e.g. on an Edit Post screen), and isn't included in the available post_types
            // on the template file, then lets skip it so that it doesn't get added. This is typically used to hide templates
            // in the template dropdown on the Edit Post page.
            if ($post_type &&
                isset($template_file->post_types) &&
                ! in_array($post_type, $template_file->post_types, true)
            ) {
                continue;
            }

            // It would be custom if the template was modified in the editor, so if it's not custom we can load it from
            // the filesystem.
            if ('custom' !== $template_file->source) {
                $template = BlockTemplateUtils::build_template_result_from_file($template_file, $template_type);
            } else {
                $template_file->title       = BlockTemplateUtils::get_block_template_title( $template_file->slug );
                $template_file->description = BlockTemplateUtils::get_block_template_description( $template_file->slug );
                $query_result[]             = $template_file;
                continue;
            }

            $is_not_custom   = false === array_search(
                wp_get_theme()->get_stylesheet() . '//' . $template_file->slug,
                array_column($query_result, 'id'),
                true
            );
            $fits_slug_query =
                ! isset($query['slug__in']) || in_array($template_file->slug, $query['slug__in'], true);
            $fits_area_query =
                ! isset($query['area']) || $template_file->area === $query['area'];
            $should_include  = $is_not_custom && $fits_slug_query && $fits_area_query;
            if ($should_include) {
                $query_result[] = $template;
            }
        }

        // We need to remove theme (i.e. filesystem) templates that have the same slug as a customised one.
        // This only affects saved templates that were saved BEFORE a theme template with the same slug was added.
        $query_result = BlockTemplateUtils::remove_theme_templates_with_custom_alternative( $query_result );

        /**
         * WC templates from theme aren't included in `$this->get_block_templates()` but are handled by Gutenberg.
         * We need to do additional search through all templates file to update title and description for WC
         * templates that aren't listed in theme.json.
         */
        $query_result = array_map(
            function( $template ) {
                if ( 'theme' === $template->origin ) {
                    return $template;
                }
                if ( $template->title === $template->slug ) {
                    $template->title = BlockTemplateUtils::get_block_template_title( $template->slug );
                }
                if ( ! $template->description ) {
                    $template->description = BlockTemplateUtils::get_block_template_description( $template->slug );
                }
                return $template;
            },
            $query_result
        );

        return $query_result;
    }

    /**
     * Gets the templates saved in the database.
     *
     * @param array $slugs An array of slugs to retrieve templates for.
     * @param array $template_type wp_template or wp_template_part.
     *
     * @return int[]|\WP_Post[] An array of found templates.
     */
    public function get_block_templates_from_db($slugs = array(), $template_type = 'wp_template')
    {
        $check_query_args = array(
            'post_type'      => $template_type,
            'posts_per_page' => -1,
            'no_found_rows'  => true,
            'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                array(
                    'taxonomy' => 'wp_theme',
                    'field'    => 'name',
                    'terms'    => array( 'wp-predictive-search', get_stylesheet() ),
                ),
            ),
        );
        if (is_array($slugs) && count($slugs) > 0) {
            $check_query_args['post_name__in'] = $slugs;
        }
        $check_query         = new \WP_Query($check_query_args);
        $saved_templates = $check_query->posts;

        return array_map(
            function ($saved_template) {
                return BlockTemplateUtils::build_template_result_from_post($saved_template);
            },
            $saved_templates
        );
    }

    /**
     * Gets the templates from the WooCommerce blocks directory, skipping those for which a template already exists
     * in the theme directory.
     *
     * @param string[] $slugs An array of slugs to filter templates by. Templates whose slug does not match will not be returned.
     * @param array    $already_found_templates Templates that have already been found, these are customised templates that are loaded from the database.
     * @param string   $template_type wp_template or wp_template_part.
     *
     * @return array Templates from the WooCommerce blocks plugin directory.
     */
    public function get_block_templates_from_plugin($slugs, $already_found_templates, $template_type = 'wp_template')
    {
        $directory      = $this->get_templates_directory($template_type);
        $template_files = BlockTemplateUtils::gutenberg_get_template_paths($directory);
        $templates      = array();

        if ('wp_template_part' === $template_type) {
            $dir_name = self::TEMPLATE_PARTS_DIR_NAME;
        } else {
            $dir_name = self::TEMPLATES_DIR_NAME;
        }

        foreach ($template_files as $template_file) {
            $template_slug = BlockTemplateUtils::generate_template_slug_from_path($template_file, $dir_name);

            // This template does not have a slug we're looking for. Skip it.
            if (is_array($slugs) && count($slugs) > 0 && ! in_array($template_slug, $slugs, true)) {
                continue;
            }

            // If the theme already has a template, or the template is already in the list (i.e. it came from the
            // database) then we should not overwrite it with the one from the filesystem.
            if (BlockTemplateUtils::theme_has_template($template_slug) ||
                count(
                    array_filter(
                        $already_found_templates,
                        function ($template) use ($template_slug) {
                            $template_obj = (object) $template; //phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found
                            return $template_obj->slug === $template_slug;
                        }
                    )
                ) > 0) {
                continue;
            }

            // At this point the template only exists in the Blocks filesystem and has not been saved in the DB,
            // or superseded by the theme.
            $templates[] = BlockTemplateUtils::create_new_block_template_object($template_file, $template_type, $template_slug);
        }

        return $templates;
    }

    /**
     * Get and build the block template objects from the block template files.
     *
     * @param array $slugs An array of slugs to retrieve templates for.
     * @param array $template_type wp_template or wp_template_part.
     *
     * @return array
     */
    public function get_block_templates($slugs = array(), $template_type = 'wp_template')
    {
        $templates_from_db  = $this->get_block_templates_from_db($slugs, $template_type);
        $templates_from_plugin = $this->get_block_templates_from_plugin($slugs, $templates_from_db, $template_type);
        return array_merge($templates_from_db, $templates_from_plugin);
    }

    /**
     * Gets the directory where templates of a specific template type can be found.
     *
     * @param array $template_type wp_template or wp_template_part.
     *
     * @return string
     */
    protected function get_templates_directory($template_type = 'wp_template')
    {
        if ('wp_template_part' === $template_type) {
            return $this->template_parts_directory;
        }
        return $this->templates_directory;
    }

    /**
     * Checks whether a block template with that name exists in Woo Blocks
     *
     * @param string $template_name Template to check.
     * @param array  $template_type wp_template or wp_template_part.
     *
     * @return boolean
     */
    public function block_template_is_available($template_name, $template_type = 'wp_template')
    {
        if (! $template_name) {
            return false;
        }
        $directory = $this->get_templates_directory($template_type) . '/' . $template_name . '.html';

        return is_readable(
            $directory
        ) || $this->get_block_templates(array( $template_name ), $template_type);
    }

    public function load_default_wp_template_part( $path, $file ) {
        if ( ! file_exists( $path ) ) {
            if ( strstr( $path, 'ps-all-results-item' ) !== false ) {
                $path = $this->template_parts_directory . '/' . 'ps-all-results-item.html';
            }
        }

        return $path;
    }
}
