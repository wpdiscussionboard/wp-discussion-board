<?php

/**
 * Functions and data for the admin. Includes our settings.
 *
 * @since 2.1.0
 *
 * @todo transition all settings to arrays
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Returns an array of settings for the General tab, Pages section.
 *
 * @since 2.1.0
 *
 * @returns array
 *
 * @todo add remaining settings to this array
 */
if (!function_exists('wpdbd_general_page_settings')) {
	function wpdbd_general_page_settings()
	{
		$settings = array(
			'options_page_settings'  => array(
				'id'       => 'options_page_settings',
				'label'    => '<h3>' . __('Pages', 'wp-discussion-board') . '</h3>',
				'callback' => 'page_header_callback',
				'page'     => 'ctdb_options',
				'section'  => 'ctdb_options_settings',
			),
			'new_topic_page'         => array(
				'id'          => 'new_topic_page',
				'label'       => __('New topic form page', 'wp-discussion-board'),
				'callback'    => 'pages_select_callback',
				'description' => __('The page where your New Topic form is displayed. The [discussion_board_form] shortcode should be on this page.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'discussion_topics_page' => array(
				'id'          => 'discussion_topics_page',
				'label'       => __('Discussion topics page', 'wp-discussion-board'),
				'callback'    => 'pages_select_callback',
				'description' => __('The page where your Discussion Topics are displayed. The [discussion_topics] shortcode should be on this page.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'frontend_login_page'    => array(
				'id'          => 'frontend_login_page',
				'label'       => __('Log-in form page', 'wp-discussion-board'),
				'callback'    => 'pages_select_callback',
				'description' => __('The page that displays your log-in form. The [discussion_board_login_form] shortcode should be on this page.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'redirect_to_page'       => array(
				'id'          => 'redirect_to_page',
				'label'       => __('Redirect on log-in', 'wp-discussion-board'),
				'callback'    => 'pages_select_callback',
				'description' => __('After logging in, the user will be redirected to this page.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
		);
		$settings = apply_filters('wpdbd_general_page_settings', $settings);

		$messages_settings = array(
			'options_messages_settings' => array(
				'id'       => 'options_messages_settings',
				'label'    => '<h3>' . __('Messages', 'wp-discussion-board') . '</h3>',
				'callback' => 'page_header_callback',
				'page'     => 'ctdb_options',
				'section'  => 'ctdb_options_settings',
			),
			'archive_title'             => array(
				'id'          => 'archive_title',
				'label'       => __('Archive title', 'wp-discussion-board'),
				'description' => __('You can enter an alternative archive title here if you wish.', 'wp-discussion-board'),
				'callback'    => 'text_callback',
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'archive_description'             => array(
				'id'          => 'archive_description',
				'label'       => __('Archive description', 'wp-discussion-board'),
				'description' => __('You can enter an alternative archive description here if you wish.', 'wp-discussion-board'),
				'callback'    => 'text_callback',
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'new_topic_message'         => array(
				'id'          => 'new_topic_message',
				'label'       => __('New topic message', 'wp-discussion-board'),
				'description' => __('You can enter an alternative message on the New Topic form here if you wish.', 'wp-discussion-board'),
				'callback'    => 'wysiwyg_callback',
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'restricted_message'        => array(
				'id'          => 'restricted_message',
				'label'       => __('Restricted message', 'wp-discussion-board'),
				'description' => __('You can enter an alternative message to users who don\'t have permissions.', 'wp-discussion-board'),
				'callback'    => 'wysiwyg_callback',
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'new_topic_message_after_submission' => array(
				'id'          => 'new_topic_message_after_submission',
				'label'       => __('New topic message after submission', 'wp-discussion-board'),
				'description' => __('You can enter an alternative message to users after they submit a new topic.', 'wp-discussion-board'),
				'callback'    => 'wysiwyg_callback',
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
		);
		$messages_settings = apply_filters('wpdbd_general_messages_settings', $messages_settings);

		$login_settings = array(
			'options_login_settings'  => array(
				'id'       => 'options_login_settings',
				'label'    => '<h3>' . __('Log In and Registration', 'wp-discussion-board') . '</h3>',
				'callback' => 'page_header_callback',
				'page'     => 'ctdb_options',
				'section'  => 'ctdb_options_settings',
			),
			'hide_wp_login'           => array(
				'id'          => 'hide_wp_login',
				'label'       => __('Hide WP Login?', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('This will prevent users logging in via wp-login.php and force them to use the Discussion Board log-in form. You must specify a front-end page containing your log-in form in the Log-in form page field above.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'hide_inline_form'        => array(
				'id'          => 'hide_inline_form',
				'label'       => __('Hide log-in form with restricted content?', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('Hide the log-in/registration form that is automatically displayed when the user is not logged in.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'prevent_wp_admin_access' => array(
				'id'          => 'prevent_wp_admin_access',
				'label'       => __('Prevent wp-admin access?', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('Hide the Admin bar and prevent wp-admin access to users registered on the Discussion Board. By default, this will apply to users with the Subscriber role.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'hide_registration_tab'   => array(
				'id'          => 'hide_registration_tab',
				'label'       => __('Hide registration tab?', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('Hide the Registration tab on the log-in form.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'check_human'             => array(
				'id'          => 'check_human',
				'label'       => __('Add check to registration form to prevent spam', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('Check this to include a radio button option on the registration form to reduce the number of spam registrations', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
		);
		$login_settings = apply_filters('wpdbd_general_login_settings', $login_settings);

		$moderation_settings = array(
			'options_moderation_settings' => array(
				'id'       => 'options_moderation_settings',
				'label'    => '<h3>' . __('Posting', 'wp-discussion-board') . '</h3>',
				'callback' => 'page_header_callback',
				'page'     => 'ctdb_options',
				'section'  => 'ctdb_options_settings',
			),
			'new_topic_status'            => array(
				'id'          => 'new_topic_status',
				'label'       => __('Publish new topic without moderation', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('Select this option to publish all new topics without moderation.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'new_post_delay'              => array(
				'id'          => 'new_post_delay',
				'label'       => __('Prevent user re-posting within (seconds)', 'wp-discussion-board'),
				'callback'    => 'select_callback',
				'choices'     => array(
					'0'   => '0',
					'15'  => '15',
					'30'  => '30',
					'45'  => '45',
					'60'  => '60',
					'120' => '120',
					'180' => '180',
					'240' => '300',
				),
				'description' => __('The number of seconds before a user can post a new topic. This prevents misuse.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
		);
		$moderation_settings = apply_filters('wpdbd_general_moderation_settings', $moderation_settings);

		$notification_settings = array(
			'options_notification_settings' => array(
				'id'       => 'options_notification_settings',
				'label'    => '<h3>' . __('Notifications', 'wp-discussion-board') . '</h3>',
				'callback' => 'page_header_callback',
				'page'     => 'ctdb_options',
				'section'  => 'ctdb_options_settings',
			),
			'notification_email'            => array(
				'id'          => 'notification_email',
				'label'       => __('Email address for notifications', 'wp-discussion-board'),
				'callback'    => 'email_callback',
				'description' => __('Define an email address here to send notifications to.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'enable_notification_opt_out'   => array(
				'id'          => 'enable_notification_opt_out',
				'label'       => __('Enable poster notification opt-out', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('Allow topic posters to opt out of receiving notifications when a comment is left on their topic.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
			'global_notification_opt_out'   => array(
				'id'          => 'global_notification_opt_out',
				'label'       => __('Global poster notification opt-out', 'wp-discussion-board'),
				'callback'    => 'checkbox_callback',
				'description' => __('Don\'t send any notifications of new comments to topic posters. Check this option to prevent duplicate notifications.', 'wp-discussion-board'),
				'page'        => 'ctdb_options',
				'section'     => 'ctdb_options_settings',
			),
		);

		$notification_settings = apply_filters('wpdbd_general_notification_settings', $notification_settings);

		return array_merge($settings, $login_settings, $moderation_settings, $notification_settings, $messages_settings);
	}
}


/**
 * Returns an array of settings for the User tab
 *
 * @since 2.1.12
 *
 * @returns array
 */
if (!function_exists('wpdbd_get_user_settings')) {
	function wpdbd_get_user_settings()
	{
		$settings = array(
			'discussion_board_minimum_role' => array(
				'id'       => 'discussion_board_minimum_role',
				'label'    => __('Permitted viewer roles', 'wp-discussion-board'),
				'callback' => 'discussion_board_minimum_roles_render',
				'page'     => 'ctdb_user',
				'section'  => 'ctdb_user_settings',
			),
			'minimum_user_roles'            => array(
				'id'       => 'minimum_user_roles',
				'label'    => __('Permitted poster roles', 'wp-discussion-board'),
				'callback' => 'minimum_user_roles_render',
				'page'     => 'ctdb_user',
				'section'  => 'ctdb_user_settings',
			),
			'new_user_role'                 => array(
				'id'       => 'new_user_role',
				'label'    => __('Register new user as', 'wp-discussion-board'),
				'callback' => 'new_user_role_render',
				'page'     => 'ctdb_user',
				'section'  => 'ctdb_user_settings',
			),
			'display_user_name'             => array(
				'id'       => 'display_user_name',
				'label'    => __('Display user name as', 'wp-discussion-board'),
				'callback' => 'display_user_name_render',
				'page'     => 'ctdb_user',
				'section'  => 'ctdb_user_settings',
			),
			'require_activation'            => array(
				'id'          => 'require_activation',
				'label'       => __('Require account activation', 'wp-discussion-board'),
				'callback'    => 'select_callback',
				'description' => __('Select "User must activate" to require new users to click an activation link before they are fully registered. This will significantly reduce spam registrations.', 'wp-discussion-board'),
				'choices'     => array(
					'none' => __('None', 'discussion-board-pro'),
					'user' => __('User must activate', 'discussion-board-pro'),
				),
				'page'        => 'ctdb_user',
				'section'     => 'ctdb_user_settings',
			),
			'email_blacklist'               => array(
				'id'       => 'email_blacklist',
				'label'    => __('Email blacklist', 'wp-discussion-board'),
				'callback' => 'email_blacklist_render',
				'page'     => 'ctdb_user',
				'section'  => 'ctdb_user_settings',
			),
		);

		return apply_filters('wpdbd_filter_user_settings', $settings);
	}
}
