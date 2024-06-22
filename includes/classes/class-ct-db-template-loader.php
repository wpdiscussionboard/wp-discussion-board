<?php
/*
 * Template Loader class
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Plugin public class
 **/
if (!class_exists('CT_DB_Template_Loader')) {

	class CT_DB_Template_Loader
	{

		public function __construct()
		{
			//
		}

		/*
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		 */
		public function init()
		{
			add_filter('single_template', array($this, 'filter_single_template'));
			add_filter('archive_template', array($this, 'filter_archive_template'));
			add_filter('get_the_archive_title', array($this, 'filter_archive_theme_template_title'), 99, 3);
			add_filter('get_the_archive_description', array($this, 'filter_archive_theme_template_description'), 99, 1);
		}

		/*
		 * Filters the single template
		 * @since 1.0.0
		 * @reference https://codex.wordpress.org/Plugin_API/Filter_Reference/single_template
		 */
		public function filter_single_template($single_template)
		{

			global $post;

			// Check we're using the plugin template
			$options = get_option('ctdb_design_settings');

			if ('discussion-topics' == $post->post_type && !isset($options['use_theme_templates'])) {

				// The hierarchy of where to find the template

				// Check child theme first
				if (file_exists(trailingslashit(get_stylesheet_directory()) . 'single-discussion-topics.php')) {
					$single_template = trailingslashit(get_stylesheet_directory()) . 'single-discussion-topics.php';

					// Check parent theme next
				} else if (file_exists(trailingslashit(get_template_directory()) . 'single-discussion-topics.php')) {
					$single_template = trailingslashit(get_template_directory()) . 'single-discussion-topics.php';

					// Check plugin compatibility last
				} else if (file_exists(WPDBD_PLUGIN_DIR . '/templates/single-discussion-topics.php')) {
					$single_template = WPDBD_PLUGIN_DIR . '/templates/single-discussion-topics.php';
				}
			}

			return $single_template;
		}

		/*
		 * Filters the archive template
		 * @since 1.0.0
		 * @reference https://codex.wordpress.org/Plugin_API/Filter_Reference/archive_template
		 */
		public function filter_archive_template($archive_template)
		{

			global $post;

			// Check we're using the plugin template
			$options = get_option('ctdb_design_settings');

			if (is_post_type_archive('discussion-topics') && !isset($options['use_theme_templates'])) {

				// The hierarchy of where to find the template
				// Check child theme first
				if (file_exists(trailingslashit(get_stylesheet_directory()) . 'archive-discussion-topics.php')) {
					$archive_template = trailingslashit(get_stylesheet_directory()) . 'archive-discussion-topics.php';

					// Check parent theme next
				} elseif (file_exists(trailingslashit(get_template_directory()) . 'archive-discussion-topics.php')) {
					$archive_template = trailingslashit(get_template_directory()) . 'archive-discussion-topics.php';

					// Check theme compatibility last
				} elseif (file_exists(WPDBD_PLUGIN_URL . 'templates/archive-discussion-topics.php')) {
					$archive_template = WPDBD_PLUGIN_URL . 'templates/archive-discussion-topics.php';
				} elseif (file_exists(WPDBD_PLUGIN_DIR . '/templates/archive-discussion-topics.php')) {
					$archive_template = WPDBD_PLUGIN_DIR . '/templates/archive-discussion-topics.php';
				}
			}

			return $archive_template;
		}

		/*
		 * Filters the archive theme template title
		 * @since 2.5.2
		 */
		public function filter_archive_theme_template_title($title, $original_title, $prefix)
		{
			if (is_post_type_archive('discussion-topics')) {
				// Check we're using the plugin template
				$options = get_option('ctdb_design_settings');
				if (isset($options['use_theme_templates'])) {
					$title = ctdb_get_archive_title();
				}
			}

			return $title;
		}

		/*
		 * Filters the archive theme template description
		 * @since 2.5.2
		 */
		public function filter_archive_theme_template_description($description)
		{
			if (is_post_type_archive('discussion-topics')) {
				// Check we're using the plugin template
				$options = get_option('ctdb_design_settings');
				if (isset($options['use_theme_templates'])) {
					$description = ctdb_get_archive_description();
				}
			}

			return $description;
		}
	}

	$CT_DB_Template_Loader = new CT_DB_Template_Loader();
	$CT_DB_Template_Loader->init();
}
