<?php
/**
 * Deprecated classes, functions and global variables.
 *
 * @since 2.4
 *
 * @package WPDiscussionBoard
 */

// Class admin deprecations.
if ( is_admin() ) {
	/**
	 * Class: CT_DB_Admin
	 */
	class CT_DB_Admin extends \WPDiscussionBoard\Admin\Admin {
		public function __construct() {
			trigger_error( // phpcs:ignore
				/* translators: 1: %s PHP class name. */
				sprintf( esc_html__( '%s class has been deprecated since version 2.4. Use \WPDiscussionBoard\Admin\Admin instead', 'wp-discussion-board' ), __CLASS__ ),
				E_USER_DEPRECATED
			);
		}
	}

	/**
	 * Class: CT_DB_Admin_About
	 */
	class CT_DB_Admin_About extends \WPDiscussionBoard\Admin\Admin_Getting_Started {
		public function __construct() {
			trigger_error( // phpcs:ignore
				/* translators: 1: %s PHP class name. */
				sprintf( esc_html__( '%s class has been deprecated since version 2.4. Use \WPDiscussionBoard\Admin\Admin_Getting_Started instead', 'wp-discussion-board' ), __CLASS__ ),
				E_USER_DEPRECATED
			);
		}
	}

	/**
	 * Class: CT_DB_Admin_Notices
	 */
	class CT_DB_Admin_Notices extends \WPDiscussionBoard\Admin\Admin_Notices { // phpcs:ignore
		public function __construct() {
			trigger_error( // phpcs:ignore
				/* translators: 1: %s PHP class name. */
				sprintf( esc_html__( '%s class has been deprecated since version 2.4. Use \WPDiscussionBoard\Admin\Admin_Notices instead', 'wp-discussion-board' ), __CLASS__ ),
				E_USER_DEPRECATED
			);
		}
	}

	/**
	 * Class: CT_DB_Admin_Upgrades
	 */
	class CT_DB_Admin_Upgrades extends \WPDiscussionBoard\Admin\Admin_Upgrades { // phpcs:ignore
		public function __construct() {
			trigger_error( // phpcs:ignore
				/* translators: 1: %s PHP class name. */
				sprintf( esc_html__( '%s class has been deprecated since version 2.4. Use \WPDiscussionBoard\Admin\Admin_Upgrades instead', 'wp-discussion-board' ), __CLASS__ ),
				E_USER_DEPRECATED
			);
		}
	}
}

// Functions.
function ctdb_general_page_settings() {
	trigger_error( // phpcs:ignore
		/* translators: 1: %s PHP function name. */
		sprintf( esc_html__( '%s function has been deprecated since version 2.4. Use wpdbd_general_page_settings() instead', 'wp-discussion-board' ), __FUNCTION__ ),
		E_USER_DEPRECATED
	);

	return wpdbd_general_page_settings();
}

function ctdb_get_user_settings() {
	trigger_error( // phpcs:ignore
	/* translators: 1: %s PHP function name. */
		sprintf( esc_html__( '%s function has been deprecated since version 2.4. Use ctdb_get_user_settings() instead', 'wp-discussion-board' ), __FUNCTION__ ),
		E_USER_DEPRECATED
	);

	return wpdbd_get_user_settings();
}

// Old Globals.
$bootstrap = \WPDiscussionBoard\Bootstrap::get_instance();

if ( is_admin() ) {
	global $CT_DB_Admin_Notices;
	$CT_DB_Admin_Notices = $bootstrap->get_container( 'Admin\Admin_Notices' );
}

// Actions.
add_action(
	'wpdbd_init',
	function() {
		do_action_deprecated( 'ct_db_init', array(), '2.4', 'wpdbd_init' );
	}
);

// Filters.
add_filter(
	'wpdbd_general_page_settings',
	function( $settings ) {
		return apply_filters_deprecated( 'ctdb_general_page_settings', array( $settings ), '2.4', 'wpdbd_general_page_settings' );
	},
	0
);

add_filter(
	'wpdbd_general_login_settings',
	function( $settings ) {
		return apply_filters_deprecated( 'ctdb_general_login_settings', array( $settings ), '2.4', 'wpdbd_general_login_settings' );
	},
	0
);

add_filter(
	'wpdbd_general_moderation_settings',
	function( $settings ) {
		return apply_filters_deprecated( 'ctdb_general_moderation_settings', array( $settings ), '2.4', 'wpdbd_general_moderation_settings' );
	},
	0
);

add_filter(
	'wpdbd_general_notification_settings',
	function( $settings ) {
		return apply_filters_deprecated( 'ctdb_general_notification_settings', array( $settings ), '2.4', 'wpdbd_general_notification_settings' );
	},
	0
);

add_filter(
	'wpdbd_filter_user_settings',
	function( $settings ) {
		return apply_filters_deprecated( 'ctdb_filter_user_settings', array( $settings ), '2.4', 'wpdbd_filter_user_settings' );
	},
	0
);

// Legacy constants.
if ( ! defined( 'DB_PLUGIN_URL' ) ) {
	define( 'DB_PLUGIN_URL', WPDBD_PLUGIN_URL );
}

if ( ! defined( 'DB_PLUGIN_DIR' ) ) {
	define( 'DB_PLUGIN_DIR', WPDBD_PLUGIN_DIR );
}

if ( ! defined( 'DB_PLUGIN_VERSION' ) ) {
	define( 'DB_PLUGIN_VERSION', WPDBD_PLUGIN_VERSION );
}
