<?php
/**
 * Admin Getting Started Page
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't initialise if there's already a Discussion Board activated.
if ( ! class_exists( 'WPDiscussionBoard\Admin\Admin_Getting_Started' ) ) {
	/**
	 * Class Admin_Getting_Started
	 *
	 * @since 2.4
	 */
	class Admin_Getting_Started {
		/**
		 * Slug for the page.
		 *
		 * @since 2.4
		 */
		const SLUG = 'getting_started';

		/**
		 * Initialize the class and start calling our hooks and filters
		 *
		 * @since 2.4
		 */
		public function init() {
			add_action( 'admin_menu', array( $this, 'add_about_submenu' ), 99 );
			add_action( 'admin_init', array( $this, 'redirect_to_getting_started_page' ), 11 );
		}

		/**
		 * Redirect to the topics page on first install.
		 *
		 * @since 2.4
		 */
		public function redirect_to_getting_started_page() {
			$done_redirect = get_option( 'ctdb_done_redirect' );

			if ( false === $done_redirect ) {
				$redirect_to = admin_url( 'edit.php' );
				$redirect_to = add_query_arg(
					array(
						'post_type' => 'discussion-topics',
						'page'      => self::SLUG,
					),
					$redirect_to
				);
				update_option( 'ctdb_done_redirect', 1 );
				wp_safe_redirect( $redirect_to );
				exit;
			}
		}

		/**
		 * Add the submenu item to the CPT menu in WordPress.
		 *
		 * @since 2.4
		 */
		public function add_about_submenu() {
			add_submenu_page(
				'edit.php?post_type=discussion-topics',
				__( 'Getting Started', 'wp-discussion-board' ),
				__( 'Getting Started', 'wp-discussion-board' ),
				'manage_options',
				self::SLUG,
				array( $this, 'render_getting_started' ),
				0
			);
		}

		/**
		 * Render the getting started page.
		 *
		 * @since 2.4
		 */
		public function render_getting_started() {
			include WPDBD_PLUGIN_DIR . '/templates/admin/getting-started.php';
		}
	}
}
