<?php
/*
Plugin Name: Discussion Board
Plugin URI: https://wpdiscussionboard.com
Description: Provide a simple discussion board for your site
Version: 2.3.17
Author: WP Discussion Board
Author URI: https://wpdiscussionboard.com
Text Domain: wp-discussion-board
Domain Path: /languages
*/

namespace WPDiscussionBoard;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin root file.
if ( ! defined( 'DB_PLUGIN_FILE' ) ) {
	define( 'DB_PLUGIN_FILE', __FILE__ );
}

// Load config.
require_once 'includes/config/config.php';

// Load text domain.
function ctdb_load_plugin_textdomain() {
	load_plugin_textdomain( 'wp-discussion-board', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'WPDiscussionBoard\ctdb_load_plugin_textdomain' );

// Load plugin.
require_once DB_PLUGIN_DIR . '/includes/install.php';
require_once DB_PLUGIN_DIR . '/includes/customizer.php';

if ( is_admin() ) {
	require_once DB_PLUGIN_DIR . '/includes/admin/admin-settings.php';
	require_once DB_PLUGIN_DIR . '/includes/admin/class-ct-db-admin.php';
	require_once DB_PLUGIN_DIR . '/includes/admin/class-admin-getting-started.php';
	require_once DB_PLUGIN_DIR . '/includes/admin/class-ct-db-admin-notices.php';
	require_once DB_PLUGIN_DIR . '/includes/admin/class-ct-db-admin-upgrades.php';
	$admin = new Admin_Getting_Started();
	$admin->init();
}

require_once DB_PLUGIN_DIR . '/includes/classes/class-ct-db-public.php';
require_once DB_PLUGIN_DIR . '/includes/classes/class-ct-db-front-end.php';
require_once DB_PLUGIN_DIR . '/includes/classes/class-ct-db-notifications.php';
require_once DB_PLUGIN_DIR . '/includes/classes/class-ct-db-template-loader.php';
require_once DB_PLUGIN_DIR . '/includes/classes/class-ct-db-registration.php';
require_once DB_PLUGIN_DIR . '/includes/classes/class-ct-db-skins.php';
require_once DB_PLUGIN_DIR . '/includes/classes/class-ct-db-user.php';
require_once DB_PLUGIN_DIR . '/includes/functions/functions-layout.php';
require_once DB_PLUGIN_DIR . '/includes/functions/functions-messages.php';
require_once DB_PLUGIN_DIR . '/includes/functions/functions-notifications.php';
require_once DB_PLUGIN_DIR . '/includes/functions/functions-registration.php';
require_once DB_PLUGIN_DIR . '/includes/functions/functions-skins.php';
require_once DB_PLUGIN_DIR . '/includes/functions/functions-user.php';

function ctdb_public_init() {
	global $CT_DB_Public;
	$CT_DB_Public = new CT_DB_Public();
	$CT_DB_Public->init();

	// Make this global
	global $CT_DB_Skins;
	$CT_DB_Skins = new CT_DB_Skins();
	$CT_DB_Skins->init();
	do_action( 'ct_db_public_init' );
}
add_action( 'plugins_loaded', 'ctdb_public_init' );

function ctdb_plugin_update_message( $data, $response ) {
	if ( isset( $data['upgrade_notice'] ) ) {
		printf(
			'<div class="ctdb-update-message">%s</div>',
			wp_kses_post( wpautop( $data['upgrade_notice'] ) )
		);
	}
}
add_action( 'in_plugin_update_message-wp-discussion-board/wp-discussion-board.php', 'ctdb_plugin_update_message', 10, 2 );

function ctdb_ms_plugin_update_message( $file, $plugin ) {
	if ( is_multisite() && version_compare( $plugin['Version'], $plugin['new_version'], '<' ) ) {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		printf(
			'<tr class="plugin-update-tr"><td colspan="%s" class="plugin-update update-message notice inline notice-warning notice-alt"><div class="update-message"><h4 style="margin: 0; font-size: 14px;">%s</h4>%s</div></td></tr>',
			(int) $wp_list_table->get_column_count(),
			esc_html( $plugin['Name'] ),
			wp_kses_post( wpautop( $plugin['upgrade_notice'] ) )
		);
	}
}
add_action( 'after_plugin_row_wp-discussion-board/wp-discussion-board.php', 'ctdb_ms_plugin_update_message', 10, 2 );
