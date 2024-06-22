<?php
/*
 * Functions for template layouts
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/*
 * What theme are we using?
 * Helper to automatically add correct wrapper tags in depending on your theme
 * @since 2.0.0
 */
if (!function_exists('ctdb_theme_wrappers')) {
	function ctdb_theme_wrappers()
	{
		$theme = wp_get_theme();
		if ($theme->exists()) {
			$theme_name = $theme->get('Name');
			$theme_wrappers = array(
				'Twenty Ten' => array(
					'open' 	=> esc_html('<div id="container"><div id="content" role="main">'),
					'close'	=> esc_html('</div></div>')
				),
				'Twenty Eleven' => array(
					'open' 	=> esc_html('<div id="primary"><div id="content" role="main">'),
					'close'	=> esc_html('</div></div>')
				),
				'Twenty Twelve' => array(
					'open' 	=> esc_html('<div id="primary" class="site-content"><div id="content" role="main">'),
					'close'	=> esc_html('</div></div>')
				),
				'Twenty Thirteen' => array(
					'open' 	=> esc_html('<div id="primary" class="content-area"><div id="content" class="site-content" role="main">'),
					'close'	=> esc_html('</div></div>')
				),
				'Twenty Fourteen' => array(
					'open' 	=> esc_html('<div id="primary" class="content-area"><div id="content" class="site-content" role="main">'),
					'close'	=> esc_html('</div></div>')
				),
				'Twenty Fifteen' => array(
					'open' 	=> esc_html('<div id="primary" class="content-area"><main id="main" class="site-main" role="main">'),
					'close'	=> esc_html('</main></div>')
				),
				'Twenty Sixteen' => array(
					'open' 	=> esc_html('<div id="primary" class="content-area"><main id="main" class="site-main" role="main">'),
					'close'	=> esc_html('</main></div>')
				),
				'Twenty Sixteen for Discussion Board' => array(
					'open' 	=> esc_html('<div id="primary" class="content-area"><main id="main" class="site-main" role="main">'),
					'close'	=> esc_html('</main></div>')
				),
				'Twenty Seventeen' => array(
					'open' 	=> esc_html('<div id="primary" class="content-area"><main id="main" class="site-main" role="main">'),
					'close'	=> esc_html('</main></div>')
				),
				'Make' => array(
					'open' 	=> esc_html('<main id="site-main" class="site-main" role="main">'),
					'close'	=> esc_html('</main>')
				),
				'default' => array(
					'open' 	=> esc_html('<div class="ctdb-content-area">'),
					'close'	=> esc_html('</div>')
				)
			);
			if (isset($theme_wrappers[$theme_name])) {
				return $theme_wrappers[$theme_name];
			} else {
				return $theme_wrappers['default'];
			}
		}
	}
}

/*
 * The opening tags for the single-discussion-topics.php template
 * @since 1.0.0
 */
if (!function_exists('ctdb_open_wrapper_single')) {
	function ctdb_open_wrapper_single()
	{
		$wrappers = ctdb_theme_wrappers();
		echo html_entity_decode($wrappers['open']);
	}
	add_action('ctdb_open_wrapper_single', 'ctdb_open_wrapper_single');
}

/*
 * The closing tags for the single-discussion-topics.php template
 * @since 1.0.0
 */
if (!function_exists('ctdb_close_wrapper_single')) {
	function ctdb_close_wrapper_single()
	{
		$wrappers = ctdb_theme_wrappers();
		echo html_entity_decode($wrappers['close']);
	}
	add_action('ctdb_close_wrapper_single', 'ctdb_close_wrapper_single');
}

/*
 * The opening tags for the archive-discussion-topics.php template
 * Matches the Twenty Fifteen theme
 * @since 1.0.0
 */
if (!function_exists('ctdb_open_wrapper_archive')) {
	function ctdb_open_wrapper_archive()
	{
		$wrappers = ctdb_theme_wrappers();
		echo html_entity_decode($wrappers['open']);
	}
	add_action('ctdb_open_wrapper_archive', 'ctdb_open_wrapper_archive');
}

/*
 * The closing tags for the archive-discussion-topics.php template
 * Matches the Twenty Fifteen theme
 * @since 1.0.0
 */
if (!function_exists('ctdb_close_wrapper_archive')) {
	function ctdb_close_wrapper_archive()
	{
		$wrappers = ctdb_theme_wrappers();
		echo html_entity_decode($wrappers['close']);
	}
	add_action('ctdb_close_wrapper_archive', 'ctdb_close_wrapper_archive');
}

/**
 * Returns the Discussion Topics archive title
 * @since 1.0.0
 */
if (!function_exists('ctdb_the_archive_title')) {
	function ctdb_the_archive_title($before = '', $after = '')
	{
		//	$options = get_option ( 'ctdb_options_settings' );
		$title = ctdb_get_archive_title();

		if (!empty($title)) {
			echo $before . esc_html($title) . $after;
		}
	}
}

/**
 * Returns the Discussion Topics archive description
 * @since 2.5.2
 */
if (!function_exists('ctdb_the_archive_description')) {
	function ctdb_the_archive_description($before = '', $after = '')
	{
		//	$options = get_option ( 'ctdb_options_settings' );
		$description = ctdb_get_archive_description();

		if (!empty($description)) {
			echo $before . esc_html($description) . $after;
		} else {
			$description = get_the_archive_description();
			if ($description) {
				echo $before . $description . $after;
			}
		}
	}
}

/*
 * Returns if we are using icons or not
 * @since 1.0.0
 */
if (!function_exists('ctdb_use_icons')) {
	function ctdb_use_icons()
	{
		$options = get_option('ctdb_design_settings');
		return isset($options['enqueue_dashicons']);
	}
}

/*
 * Returns the path to the empty comment file
 * @since 1.0.0
 */
if (!function_exists('ctdb_get_comments_file_path')) {
	function ctdb_get_comments_file_path()
	{
		return apply_filters('ctdb_filter_comments_file_path', WPDBD_PLUGIN_DIR . '/templates/empty-comments-file.php');
	}
}
