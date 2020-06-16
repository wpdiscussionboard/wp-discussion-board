<?php

/*
 * Discussion Board Admin upgrades class
 * @since 1.7.0
 * Handle any work between versions of the plugin
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CT_DB_Admin_Upgrades extends CT_DB_Admin {

	public function __construct() {
		//
	}

	/*
	 * Initialize the class and start calling our hooks and filters
	 * @since 1.0.0
	 */

	public function init() {
	//	add_action( 'init', array( $this, 'update_plugin_version' ) );
	//	add_action( 'init', array( $this, 'migrate_redirect_page_setting' ) );
	}
	
	/**
	 * Update the plugin version and check for any important changes
	 * @since 1.7.0
	*/
	public function update_plugin_version() {
		
		$version = get_option( 'ctdb_database_version' );
		
		// Save the previous version as a setting
		update_option( 'ctdb_updated_from_version', sanitize_text_field( $version ) );
		
		if( false === $version ) {
			// User is not upgrading
		} else {
			// User is upgrading
			// Check for version 2.0.0
			$old_version = explode( '.', $version );
			$old_major_version = $old_version[0];
			$new_version = explode( '.', DB_PLUGIN_VERSION );
			$new_major_version = $new_version[0];
			if( $old_major_version == '1' && $new_major_version == '2' ) {
				// We're upgrading from 1.x.x to 2.x.x
				global $CT_DB_Admin_Notices;
				add_action( 'admin_notices', array( $CT_DB_Admin_Notices, 'major_upgrade' ) );
			}
		}
		
		// Save our new version
		update_option( 'ctdb_database_version', DB_PLUGIN_VERSION );
		
	}
	
	
	
	/**
	 * Check if we have upgraded and have a redirect_to_page setting in user_settings
	 * @since 2.0.0
	*/
	public function migrate_redirect_page_setting() {
		$upgrade = get_option( 'ctdb_upgrade_settings' );
		if( false === $upgrade ) {
			$upgrade = array();
		}
		if( ! isset( $upgrade['migrate_redirect_page_setting'] ) || $upgrade['migrate_redirect_page_setting'] != 'done' ) {
			// The migrate_redirect_page_setting has not been done, yet
			// @since 2.0.0
			
			$user_settings = get_option( 'ctdb_user_settings' );
			// Check if there's a value in the user_settings redirect_to_page
			if( isset( $user_settings['redirect_to_page'] ) ) {
				$old_value = $user_settings['redirect_to_page'];
				$options_settings = get_option( 'ctdb_options_settings' );
				if( ! isset( $options_settings['redirect_to_page'] ) ) {
					// If the field is not set in the general options tab then update it to the user_settings value
					$options_settings['redirect_to_page'] = esc_attr( $old_value );
					update_option( 'ctdb_options_settings', $options_settings );
					$upgrade['migrate_redirect_page_setting'] = 'done';
				}
			}
		}
		update_option( 'ctdb_upgrade_settings', $upgrade );
	}

}

$CT_DB_Admin_Upgrades = new CT_DB_Admin_Upgrades();
$CT_DB_Admin_Upgrades -> init();