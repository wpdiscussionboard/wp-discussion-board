<?php
/**
 * PHPUnit bootstrap file
 *
 * @package WPDiscussionBoard
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Include Polyfills.
if ( class_exists( '\Yoast\PHPUnitPolyfills\Autoload' ) === false ) {
	require_once 'vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run `composer setup-tests`?" . PHP_EOL; // WPCS: XSS ok.
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require_once dirname( __FILE__ ) . '/../wp-discussion-board.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
