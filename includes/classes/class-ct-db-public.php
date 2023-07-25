<?php
/*
 * Discussion Board public class
*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin public class
 **/
if( ! class_exists( 'CT_DB_Public' ) ) { // Don't initialise if there's already a Discussion Board activated
	class CT_DB_Public {

		public $user_can_view = false;
		public $user_can_post = false;

		public function __construct() {

		}
		/*
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		 */
		public function init() {

			add_filter( 'body_class', array( $this, 'body_class_filter' ) );

			// Call this from install.php
			add_action( 'init', 'wpdbd_register_post_type' );

			add_action( 'init', array( $this, 'check_user_permission' ) );
			add_action( 'init', array( $this, 'hide_admin_bar' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_head', array( $this, 'add_ajaxurl' ) );

			global $CT_DB_Front_End;
			$CT_DB_Front_End = new CT_DB_Front_End();
			$CT_DB_Front_End->init();
			$CT_DB_Notifications = new CT_DB_Notifications();
			$CT_DB_Notifications->init();
			$CT_DB_Registration = new CT_DB_Registration();
			$CT_DB_Registration->init();
			$CT_DB_User = new CT_DB_User();
			$CT_DB_User->init();

		}

		public function add_ajaxurl() {
			echo '<script type="text/javascript">
           	 	var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         	</script>';
		}

		/*
		 * Add classes to body
		 * @since 1.5.0
		 */
		public function body_class_filter( $classes ) {

			$options = get_option( 'ctdb_design_settings' );
			if( isset( $options['info_bar_layout'] ) ) {
				// This is legacy
				$classes[] = 'ctdb-layout-' . esc_attr( $options['info_bar_layout'] );
				// Single layout
			//	$classes[] = 'ctdb-meta-layout-' . esc_attr( $options['info_bar_layout'] );
			}

			// Add a class depending on page type
			if( is_single() && 'discussion-topics' == get_post_type() ) {
				// If we're on a single-discussion-topics.php page
				if( isset( $options['info_bar_layout'] ) ) {
					// Single layout
					$classes[] = 'ctdb-single-layout-' . esc_attr( $options['info_bar_layout'] );
				}
			} else if( isset( $options['archive_layout'] ) ) {
				// Archive layout
				$classes[] = 'ctdb-archive-layout-' . esc_attr( $options['archive_layout'] );
			}

			// Add the theme name to the body classes
			$theme = wp_get_theme();
			if( $theme->exists()) {
				$theme_name = $theme->get( 'Name' );
				if( ! empty( $theme_name ) ) {
					$classes[] = 'ctdb-' . strtolower( str_replace( ' ', '-', $theme_name ) );
				}
			}

			// Add some classes relating to user permissions
			if( $this->user_can_view ) {
				$classes[] = 'ctdb-user-can-view';
			} else {
				$classes[] = 'ctdb-user-cannot-view';
			}
			if( $this->user_can_post ) {
				$classes[] = 'ctdb-user-can-post';
			} else {
				$classes[] = 'ctdb-user-cannot-post';
			}

			return $classes;

		}

		public function check_user_permission() {
			$this->user_can_view = ctdb_is_user_permitted();
			$this->user_can_post = ctdb_is_posting_permitted();
		}

		/*
		 * Enqueue styles and scripts
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			$options = get_option( 'ctdb_design_settings' );
			wp_enqueue_script( 'jquery' );
			if( isset( $options['enqueue_styles'] ) ) {
				wp_enqueue_style( 'ctdb-style', WPDBD_PLUGIN_URL . 'assets/css/style.css', array(), WPDBD_PLUGIN_VERSION );
			}
			if( isset( $options['enqueue_dashicons'] ) ) {
				wp_enqueue_style( 'dashicons' );
			}
		}

		/*
		 * Hide the admin bar to Discussion Topic users
		 * @since 1.0.0
		 */
		public function hide_admin_bar() {

			$options = get_option( 'ctdb_options_settings' );

			// Check that we've enabled the option
			if( ! empty( $options['prevent_wp_admin_access'] ) ) {

				$user_options = get_option( 'ctdb_user_settings' );
				global $current_user;
				$user_roles = $current_user -> roles;
				$user_role = array_shift( $user_roles );

				// Check we're the correct role
				if( $user_role == $user_options['new_user_role'] ) {
					add_filter( 'show_admin_bar', '__return_false' );
				}

			}

		}

		// Legacy
		public function is_posting_permitted() {
			return ctdb_is_posting_permitted();
		}

		public function is_user_permitted() {
			return ctdb_is_user_permitted();
		}
		public function get_permitted_poster_roles() {
			return ctdb_get_permitted_poster_roles();
		}
		public function get_permitted_viewer_roles() {
			return ctdb_get_permitted_viewer_roles();
		}
	}

}
