<?php
/**
 * WordPress Test Config.
 *
 * @package Salespack
 */

define( 'ABSPATH', '/app/wp/' );

// Test with multisite enabled.
// Alternatively, use the tests/phpunit/multisite.xml configuration file.
/* define( 'WP_TESTS_MULTISITE', true ); */

// Force known bugs to be run.
// Tests with an associated Trac ticket that is still open are normally skipped.
/* define( 'WP_TESTS_FORCE_KNOWN_BUGS', true ); */

// Test with WordPress debug mode (default).
define( 'WP_DEBUG', true );

// ** MySQL settings ** //

// This configuration file will be used by the copy of WordPress being tested.
// wordpress/wp-config.php will be ignored.

// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.

define( 'DB_HOST', 'mysql_phpunit' );
define( 'DB_NAME', 'wordpress_test' );
define( 'DB_USER', 'wp' );
define( 'DB_PASSWORD', 'wp' );

define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );

define( 'WP_PHP_BINARY', 'php' );

define( 'WPLANG', '' );
