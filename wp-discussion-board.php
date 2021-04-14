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

// Exit if accessed directly

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ctdb_load_plugin_textdomain() {
    load_plugin_textdomain( 'wp-discussion-board', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'ctdb_load_plugin_textdomain' );

/**
 * Define constants
 **/
if ( ! defined( 'DB_PLUGIN_URL' ) ) {
	define( 'DB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'DB_PLUGIN_DIR' ) ) {
	define( 'DB_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'DB_PLUGIN_VERSION' ) ) {
	define( 'DB_PLUGIN_VERSION', '2.3.15' );
}
// Plugin Root File.
if ( ! defined( 'DB_PLUGIN_FILE' ) ) {
	define( 'DB_PLUGIN_FILE', __FILE__ );
}

/**
 * Load her up.
 **/
require_once dirname( __FILE__ ) . '/includes/install.php';
require_once dirname( __FILE__ ) . '/includes/customizer.php';

if( is_admin() ) {
	require_once dirname( __FILE__ ) . '/includes/admin/admin-settings.php';
	require_once dirname( __FILE__ ) . '/includes/admin/class-ct-db-admin.php';
	require_once dirname( __FILE__ ) . '/includes/admin/class-ct-db-admin-about.php';
	require_once dirname( __FILE__ ) . '/includes/admin/class-ct-db-admin-notices.php';
	require_once dirname( __FILE__ ) . '/includes/admin/class-ct-db-admin-upgrades.php';
	$CT_DB_Admin_About = new CT_DB_Admin_About();
	$CT_DB_Admin_About->init();
}
require_once dirname( __FILE__ ) . '/includes/classes/class-ct-db-public.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-ct-db-front-end.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-ct-db-notifications.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-ct-db-template-loader.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-ct-db-registration.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-ct-db-skins.php';
require_once dirname( __FILE__ ) . '/includes/classes/class-ct-db-user.php';
require_once dirname( __FILE__ ) . '/includes/functions/functions-layout.php';
require_once dirname( __FILE__ ) . '/includes/functions/functions-messages.php';
require_once dirname( __FILE__ ) . '/includes/functions/functions-notifications.php';
require_once dirname( __FILE__ ) . '/includes/functions/functions-registration.php';
require_once dirname( __FILE__ ) . '/includes/functions/functions-skins.php';
require_once dirname( __FILE__ ) . '/includes/functions/functions-user.php';

function ctdb_public_init() {
	global $CT_DB_Public;
	$CT_DB_Public = new CT_DB_Public();
	$CT_DB_Public -> init();
	// Make this global
	global $CT_DB_Skins;
	$CT_DB_Skins = new CT_DB_Skins();
	$CT_DB_Skins->init();
	do_action( 'ct_db_public_init' );
}
add_action( 'plugins_loaded', 'ctdb_public_init' );


function ctdb_plugin_update_message( $data, $response ) {
	if( isset( $data['upgrade_notice'] ) ) {
		printf(
			'<div class="ctdb-update-message">%s</div>',
			wpautop( $data['upgrade_notice'] )
		);
	}
}
add_action( 'in_plugin_update_message-wp-discussion-board/wp-discussion-board.php', 'ctdb_plugin_update_message', 10, 2 );

function ctdb_ms_plugin_update_message( $file, $plugin ) {
	if( is_multisite() && version_compare( $plugin['Version'], $plugin['new_version'], '<') ) {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		printf(
			'<tr class="plugin-update-tr"><td colspan="%s" class="plugin-update update-message notice inline notice-warning notice-alt"><div class="update-message"><h4 style="margin: 0; font-size: 14px;">%s</h4>%s</div></td></tr>',
			$wp_list_table->get_column_count(),
			$plugin['Name'],
			wpautop( $plugin['upgrade_notice'] )
		);
	}
}
add_action( 'after_plugin_row_wp-discussion-board/wp-discussion-board.php', 'ctdb_ms_plugin_update_message', 10, 2 );
