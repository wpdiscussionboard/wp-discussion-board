<?php
/*
 * User class
 * Create new user roles
 * Registration and logging in are in CT_DB_Publi
 *
 * @since 2.1.0
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * User class
 */
if( ! class_exists( 'CT_DB_User' ) ) { // Don't initialise if there's already a Discussion Board activated
	
	class CT_DB_User {
		
		public function __construct() {
			
		}
		/*
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		 */
		public function init() {
			add_action( 'init', array( $this, 'create_pending_role' ) );
		}
		
		/*
		 * Create basic user role 'Pending' with no capabilities
		 * @since 1.0.0
		 * @reference https://codex.wordpress.org/Function_Reference/add_role
		 */
		public function create_pending_role() {
			add_role(
				'pending',
				__( 'Pending', 'wp-discussion-board' ),
				array(
					'read'	=>	true
				)
			);
		}

	}
	
}