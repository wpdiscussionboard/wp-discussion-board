<?php

/**
 * Discussion Board installation functions.
 *
 * @since 2.0.0
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Flush the permalinks.
 */
function wpdbd_flush_rewrites()
{
	global $wp_version;

	// Ensure post type is registered
	wpdbd_register_post_type();

	// Flush the permalinks
	flush_rewrite_rules();

	// Create our pages
	wpdbd_create_pages();

	if (empty(get_option('ctdb_install_date', '')))
		update_option('ctdb_install_date', wp_date('Y-m-d h:i:s'));

	$is_6_4_and_above = version_compare($wp_version, '6.4', '>=');
	if ($is_6_4_and_above) {
		wp_set_options_autoload(
			array('ctdb_options_settings', 'ctdb_design_settings', 'ctdb_user_settings', 'ctdb_categories_settings'),
			'yes'
		);
	}
}
register_activation_hook(WPDBD_PLUGIN_FILE, 'wpdbd_flush_rewrites');

/**
 * Remove the auto load options
 */
function wpdbd_deactivate()
{
	global $wp_version;

	$is_6_4_and_above = version_compare($wp_version, '6.4', '>=');
	if ($is_6_4_and_above) {
		wp_set_options_autoload(
			array('ctdb_options_settings', 'ctdb_design_settings', 'ctdb_user_settings', 'ctdb_categories_settings'),
			'no'
		);
	}
}
register_deactivation_hook(WPDBD_PLUGIN_FILE, 'wpdbd_deactivate');

/**
 * Register the discussion-topic post type and taxonomy.
 *
 * @since 1.0.0
 *
 * @todo Allow user to specify post type name
 * @todo Allow user to specify icon
 */
function wpdbd_register_post_type()
{
	$labels = array(
		'name'               => _x('Topics', 'post type general name', 'wp-discussion-board'),
		'singular_name'      => _x('Topic', 'post type singular name', 'wp-discussion-board'),
		'menu_name'          => _x('Discussion Board', 'admin menu', 'wp-discussion-board'),
		'name_admin_bar'     => _x('Topic', 'add new on admin bar', 'wp-discussion-board'),
		'add_new'            => _x('Add New', 'topic', 'wp-discussion-board'),
		'add_new_item'       => __('Add New Topic', 'wp-discussion-board'),
		'new_item'           => __('New Topic', 'wp-discussion-board'),
		'edit_item'          => __('Edit Topic', 'wp-discussion-board'),
		'view_item'          => __('View Topic', 'wp-discussion-board'),
		'all_items'          => __('All Topics', 'wp-discussion-board'),
		'search_items'       => __('Search Topics', 'wp-discussion-board'),
		'parent_item_colon'  => __('Parent Topic:', 'wp-discussion-board'),
		'not_found'          => __('No topics found.', 'wp-discussion-board'),
		'not_found_in_trash' => __('No topics found in Trash.', 'wp-discussion-board'),
	);

	$args = array(
		'labels'             => $labels,
		'description'        => __('Description.', 'wp-discussion-board'),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array('slug' => 'discussion-topics'),
		'capability_type'    => 'post',
		'menu_icon'          => 'dashicons-format-chat',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array('title', 'editor', 'author', 'comments', 'thumbnail', 'custom-fields'),
	);
	register_post_type('discussion-topics', $args);
}

/**
 * Create pages for Discussion Board.
 *
 * @since 2.0.0
 */
function wpdbd_create_pages()
{
	// Check if pages have already been set.
	$pages_done = get_option('ctdb_wizard_done');

	if (false === $pages_done) {
		// Discussion Topics Page.
		$topics_page = wp_insert_post(
			array(
				'post_title'     => __('Topics', 'wp-discussion-board'),
				'post_content'   => '[discussion_topics] [new_topic_button]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);

		// New Topic Page.
		$new_topic_page = wp_insert_post(
			array(
				'post_title'     => __('New Topic', 'wp-discussion-board'),
				'post_content'   => '[discussion_board_form]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $topics_page,
				'comment_status' => 'closed',
			)
		);

		// Log In Form Page.
		$login_page = wp_insert_post(
			array(
				'post_title'     => __('Log In', 'wp-discussion-board'),
				'post_content'   => '[discussion_board_login_form] [is_logged_in]' . __('You are already logged in', 'wp-discussion-board') . '[/is_logged_in]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'post_parent'    => $topics_page,
				'comment_status' => 'closed',
			)
		);

		// Get default options.
		$options = array(
			'defaults_set'                => 1, // We use this because we have already updated this option in install.php
			'archive_title'               => '',
			'new_topic_message'           => '',
			'restricted_title'            => '',
			'restricted_message'          => '',
			'hide_wp_login'               => 0,
			'hide_inline_form'            => 0,
			'prevent_wp_admin_access'     => 0,
			'check_human'                 => 1,
			'new_topic_status'            => 0,
			'new_post_delay'              => 30,
			'include_categories'          => 0,
			'notification_email'          => get_option('admin_email'),
			'enable_notification_opt_out' => 0,
			'wisdom_registered_setting'   => 1, // For plugin-usage-tracker
		);

		// Store our page IDs
		$options['new_topic_page']         = $new_topic_page;
		$options['discussion_topics_page'] = $topics_page;
		$options['frontend_login_page']    = $login_page;
		$options['redirect_to_page']       = $new_topic_page;

		update_option('ctdb_options_settings', $options);

		// Set the wizard to done
		update_option('ctdb_wizard_done', 1);
	}
}
