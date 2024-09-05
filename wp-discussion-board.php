<?php
/*
Plugin Name: Discussion Board
Plugin URI: https://wpdiscussionboard.com
Description: Discussion Board is a simple, effective way to add a forum or discussion board to your site, helping you build and engage an active community.
Version: 2.5.4
Author: WP Discussion Board
Author URI: https://wpdiscussionboard.com
Text Domain: wp-discussion-board
Domain Path: /languages
*/

namespace WPDiscussionBoard;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Define plugin root file.
if (!defined('WPDBD_PLUGIN_FILE')) {
	define('WPDBD_PLUGIN_FILE', __FILE__);
}

// Load config files.
require_once 'includes/config/config.php';

if (is_admin() || wp_doing_cron()) {
	require_once 'includes/config/settings.php';
}

// Load helpers.
require_once 'includes/helpers/autoloader.php';
require_once 'includes/helpers/install.php';
require_once 'includes/helpers/customizer.php';

// @todo: Move these to autoloader.
require_once 'includes/classes/class-ct-db-public.php';
require_once 'includes/classes/class-ct-db-front-end.php';
require_once 'includes/classes/class-ct-db-notifications.php';
require_once 'includes/classes/class-ct-db-template-loader.php';
require_once 'includes/classes/class-ct-db-registration.php';
require_once 'includes/classes/class-ct-db-skins.php';
require_once 'includes/classes/class-ct-db-user.php';
require_once 'includes/functions/functions-layout.php';
require_once 'includes/functions/functions-messages.php';
require_once 'includes/functions/functions-notifications.php';
require_once 'includes/functions/functions-registration.php';
require_once 'includes/functions/functions-skins.php';
require_once 'includes/functions/functions-user.php';

// Deprecated class, functions and variables.
require_once 'includes/helpers/deprecated.php';

add_action(
	'plugins_loaded',
	function () {
		$bootstrap = Bootstrap::get_instance();
		$bootstrap->init();
		$bootstrap->load();
		do_action('wpdbd_init');
	}
);

function ctdb_public_init()
{
	global $CT_DB_Public;
	$CT_DB_Public = new \CT_DB_Public();
	$CT_DB_Public->init();

	// Make this global
	global $CT_DB_Skins;
	$CT_DB_Skins = new \CT_DB_Skins();
	$CT_DB_Skins->init();
	do_action('ct_db_public_init');
}
add_action('plugins_loaded', '\WPDiscussionBoard\ctdb_public_init');

function ctdb_plugin_update_message($data, $response)
{
	if (isset($data['upgrade_notice'])) {
		printf(
			'<div class="ctdb-update-message">%s</div>',
			wp_kses_post(wpautop($data['upgrade_notice']))
		);
	}
}
add_action('in_plugin_update_message-wp-discussion-board/wp-discussion-board.php', '\WPDiscussionBoard\ctdb_plugin_update_message', 10, 2);

function ctdb_ms_plugin_update_message($file, $plugin)
{
	if (is_multisite() && version_compare($plugin['Version'], $plugin['new_version'], '<')) {
		$wp_list_table = _get_list_table('WP_Plugins_List_Table');
		printf(
			'<tr class="plugin-update-tr"><td colspan="%s" class="plugin-update update-message notice inline notice-warning notice-alt"><div class="update-message"><h4 style="margin: 0; font-size: 14px;">%s</h4>%s</div></td></tr>',
			(int) $wp_list_table->get_column_count(),
			esc_html($plugin['Name']),
			wp_kses_post(wpautop($plugin['upgrade_notice']))
		);
	}
}
add_action('after_plugin_row_wp-discussion-board/wp-discussion-board.php', '\WPDiscussionBoard\ctdb_ms_plugin_update_message', 10, 2);
