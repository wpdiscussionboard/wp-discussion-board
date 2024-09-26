<?php
/**
 * Configs used throughout the plugin.
 *
 * @since 2.4
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard;

if ( ! defined( 'WPDBD_PLUGIN_URL' ) ) {
	define( 'WPDBD_PLUGIN_URL', plugin_dir_url( WPDBD_PLUGIN_FILE ) );
}

if ( ! defined( 'WPDBD_PLUGIN_DIR' ) ) {
	define( 'WPDBD_PLUGIN_DIR', dirname( WPDBD_PLUGIN_FILE ) );
}

if ( ! defined( 'WPDBD_PLUGIN_VERSION' ) ) {
	define( 'WPDBD_PLUGIN_VERSION', '2.5.5' );
}
