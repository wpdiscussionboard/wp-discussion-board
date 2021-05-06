<?php
/**
 * Configs used throughout the plugin.
 *
 * @since 2.3.18
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard;

if ( ! defined( 'DB_PLUGIN_URL' ) ) {
	define( 'DB_PLUGIN_URL', plugin_dir_url( DB_PLUGIN_FILE ) );
}

if ( ! defined( 'DB_PLUGIN_DIR' ) ) {
	define( 'DB_PLUGIN_DIR', dirname( DB_PLUGIN_FILE ) );
}

if ( ! defined( 'DB_PLUGIN_VERSION' ) ) {
	define( 'DB_PLUGIN_VERSION', '2.3.18' );
}
