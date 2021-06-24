<?php
/**
 * Admin settings.
 *
 * @since 3.0
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns an array of settings.
 *
 * @since 3.0
 *
 * @returns array
 */
if ( ! function_exists( 'wpdbd_get_settings' ) ) {
	function wpdbd_get_settings() {
		$settings = array(
			'new_topic_page'      => array(
				'id'          => 'discussion_board_page',
				'label'       => __( 'Discussion board page', 'wp-discussion-board' ),
				'type'        => 'select_pages',
				'description' => __( 'The page where your discussion board is displayed. The [discussion_board] shortcode should be on this page.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'general',
			),
			'frontend_login_page' => array(
				'id'          => 'frontend_login_page',
				'label'       => __( 'Log-in page', 'wp-discussion-board' ),
				'type'        => 'select_pages',
				'description' => __( 'The page that displays your log-in form. The [discussion_board_login_form] shortcode should be on this page.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'general',
			),
			'redirect_to_page'    => array(
				'id'          => 'redirect_to_page',
				'label'       => __( 'Redirect on log-in', 'wp-discussion-board' ),
				'type'        => 'select_pages',
				'description' => __( 'After logging in, the user will be redirected to this page.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'general',
			),
		);
		$settings = apply_filters( 'wpdbd_general_settings', $settings );

		$moderation_settings = array(
			'new_topic_status' => array(
				'id'          => 'new_topic_status',
				'label'       => __( 'Publish new topic without moderation', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Publish all new topics without moderation.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'moderation',
			),
			'new_post_delay'   => array(
				'id'          => 'new_post_delay',
				'label'       => __( 'Prevent user re-posting within (seconds)', 'wp-discussion-board' ),
				'type'        => 'select',
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
				'description' => __( 'The number of seconds before a user can post a new topic. This prevents misuse.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'moderation',
			),
		);
		$moderation_settings = apply_filters( 'wpdbd_general_moderation_settings', $moderation_settings );

		$messages_settings = array(
			'archive_title'      => array(
				'id'          => 'archive_title',
				'label'       => __( 'Archive title', 'wp-discussion-board' ),
				'description' => __( 'An alternative archive title.', 'wp-discussion-board' ),
				'type'        => 'text',
				'page'        => 'wpdbd_general',
				'section'     => 'messages',
			),
			'new_topic_message'  => array(
				'id'          => 'new_topic_message',
				'label'       => __( 'New topic message', 'wp-discussion-board' ),
				'description' => __( 'An alternative message on the New Topic form.', 'wp-discussion-board' ),
				'type'        => 'wysiwyg',
				'page'        => 'wpdbd_general',
				'section'     => 'messages',
			),
			'restricted_message' => array(
				'id'          => 'restricted_message',
				'label'       => __( 'Restricted message', 'wp-discussion-board' ),
				'description' => __( 'An alternative message to users who don\'t have permissions.', 'wp-discussion-board' ),
				'type'        => 'wysiwyg',
				'page'        => 'wpdbd_general',
				'section'     => 'messages',
			),
		);
		$messages_settings = apply_filters( 'wpdbd_general_messages_settings', $messages_settings );

		$notification_settings = array(
			'notification_email'          => array(
				'id'          => 'notification_email',
				'label'       => __( 'Email address for notifications', 'wp-discussion-board' ),
				'type'        => 'email',
				'description' => __( 'Email address to send admin notifications to.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'notifications',
			),
			'enable_notification_opt_out' => array(
				'id'          => 'enable_notification_opt_out',
				'label'       => __( 'Enable poster notification opt-out', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Allow topic posters to opt out of receiving notifications when a comment is left on their topic.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'notifications',
			),
			'global_notification_opt_out' => array(
				'id'          => 'global_notification_opt_out',
				'label'       => __( 'Global poster notification opt-out', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Don\'t send any notifications of new comments to topic posters. Check this option to prevent duplicate notifications.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_general',
				'section'     => 'notifications',
			),
		);

		$notification_settings = apply_filters( 'wpdbd_general_notification_settings', $notification_settings );

		$login_settings = array(
			'hide_wp_login'           => array(
				'id'          => 'hide_wp_login',
				'label'       => __( 'Hide WP Login?', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Prevent users logging in via wp-login.php and force them to use the Discussion Board log-in form. You must specify a front-end page containing your log-in form in the Log-in form page field above.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
			'hide_inline_form'        => array(
				'id'          => 'hide_inline_form',
				'label'       => __( 'Hide log-in form with restricted content?', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Hide the log-in/registration form that is automatically displayed when the user is not logged in.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
			'prevent_wp_admin_access' => array(
				'id'          => 'prevent_wp_admin_access',
				'label'       => __( 'Prevent wp-admin access?', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Hide the Admin bar and prevent wp-admin access to users registered on the Discussion Board. By default, this will apply to users with the Subscriber role.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
			'hide_registration_tab'   => array(
				'id'          => 'hide_registration_tab',
				'label'       => __( 'Hide registration tab?', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Hide the Registration tab on the log-in form.', 'wp-discussion-board' ),
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
			'display_user_name'       => array(
				'id'          => 'display_user_name',
				'label'       => __( 'Display user name as', 'wp-discussion-board' ),
				'description' => __( 'How to display user name.', 'wp-discussion-board' ),
				'type'        => 'select',
				'choices'     => array(
					'display_name' => __( 'Display Name', 'wp-discussion-board' ),
					'user_login'   => __( 'Username', 'wp-discussion-board' ),
					'nickname'     => __( 'Nickname', 'wp-discussion-board' ),
				),
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
			'require_activation'      => array(
				'id'          => 'require_activation',
				'label'       => __( 'Require account activation', 'wp-discussion-board' ),
				'type'        => 'select',
				'description' => __( 'Require new users to click an activation link before they are fully registered. This will significantly reduce spam registrations.', 'wp-discussion-board' ),
				'choices'     => array(
					'none' => __( 'None', 'discussion-board-pro' ),
					'user' => __( 'User must activate', 'discussion-board-pro' ),
				),
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
			'check_human'             => array(
				'id'          => 'check_human',
				'label'       => __( 'Add check to registration form to prevent spam', 'wp-discussion-board' ),
				'type'        => 'checkbox',
				'description' => __( 'Include a radio button option on the registration form to reduce the number of spam registrations', 'wp-discussion-board' ),
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
			'email_blacklist'         => array(
				'id'          => 'email_blacklist',
				'label'       => __( 'Email blacklist', 'wp-discussion-board' ),
				'description' => __( 'Block specific email addresses(e.g. nasty@spammer.com) or entire email domains(e.g. @spammer.com) from registering. Add one address per line.', 'wp-discussion-board' ),
				'type'        => 'textarea',
				'page'        => 'wpdbd_users',
				'section'     => 'login-registration',
			),
		);
		$login_settings = apply_filters( 'wpdbd_general_login_settings', $login_settings );

		$user_role_settings = array(
			'minimum_user_roles' => array(
				'id'      => 'minimum_user_roles',
				'label'   => __( 'Permitted poster roles', 'wp-discussion-board' ),
				'type'    => 'multiselect',
				'choices' => array_map(
					function ( $role ) {
						return $role['name'];
					},
					get_editable_roles()
				),
				'page'    => 'wpdbd_users',
				'section' => 'roles-permissions',
			),
			'new_user_role'      => array(
				'id'      => 'new_user_role',
				'label'   => __( 'Register new user as', 'wp-discussion-board' ),
				'type'    => 'multiselect',
				'choices' => array_map(
					function ( $role ) {
						return $role['name'];
					},
					get_editable_roles()
				),
				'page'    => 'wpdbd_users',
				'section' => 'roles-permissions',
			),
		);

		return array_merge( $settings, $login_settings, $moderation_settings, $notification_settings, $messages_settings, $user_role_settings );
	}
}