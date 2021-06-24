<?php
/**
 * Discussion Board Admin class.
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPDiscussionBoard\Admin\Admin' ) ) {
	/**
	 * Plugin admin class.
	 **/
	class Admin {
		const SETTINGS_PAGE_SLUG = 'discussion_board';

		public $tabs;

		public $sections;

		public function __construct() {
			$this->tabs = array(
				'general' => __( 'General', 'wp-discussion-board' ),
				'themes'  => __( 'Themes', 'wp-discussion-board' ),
				'users'   => __( 'Users', 'wp-discussion-board' ),
			);
			$this->tabs = apply_filters( 'wpdbd_settings_tabs', $this->tabs );

			$this->sections = array(
				'general' => array(
					'general'       => __( 'General', 'wp-discussion-board' ),
					'moderation'    => __( 'Moderation', 'wp-discussion-board' ),
					'messages'      => __( 'Messages', 'wp-discussion-board' ),
					'notifications' => __( 'Notifications', 'wp-discussion-board' ),
				),
				'themes'  => array(
					'theme'    => __( 'Theme', 'wp-discussion-board' ),
					'settings' => __( 'Settings', 'wp-discussion-board' ),
				),
				'users'   => array(
					'roles-permissions'  => __( 'Roles & Permissions', 'wp-discussion-board' ),
					'login-registration' => __( 'Login & Registration', 'wp-discussion-board' ),
				),
			);
			$this->sections = apply_filters( 'wpdbd_settings_sections', $this->sections );
		}

		/**
		 * Initialize the class and start calling our hooks and filters.
		 *
		 * @since 3.0
		 */
		public function init() {
			add_action( 'admin_menu', array( $this, 'add_settings_submenu' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_init', array( $this, 'prevent_wp_admin_access' ), 100 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'admin_notices', array( $this, 'ctdb_admin_notices' ) );
			add_action( 'admin_footer', array( $this, 'user_registration_notice_script' ) );
			add_action( 'wp_ajax_ctdb_dismiss_notice', array( $this, 'ctdb_dismiss_notice' ) );
			add_action( 'show_user_profile', array( $this, 'ctdb_display_activation_key' ), 10, 1 );
			add_action( 'edit_user_profile', array( $this, 'ctdb_display_activation_key' ), 10, 1 );
			add_filter( 'plugin_action_links_wp-discussion-board/wp-discussion-board.php', array( $this, 'filter_action_links' ), 10, 1 );
		}

		/**
		 * Enqueue scripts.
         *
         * @since 3.0
		 */
		public function enqueue_scripts() {
			wp_enqueue_style( 'wpdbd-admin-style', WPDBD_PLUGIN_URL . 'assets/css/admin-style.css', array(), WPDBD_PLUGIN_VERSION );
		}

		/**
		 * Add the menu item.
         *
         * @since 3.0
		 */
		public function add_settings_submenu() {
			add_submenu_page(
				'edit.php?post_type=discussion-topics',
				__( 'Settings', 'wp-discussion-board' ),
				__( 'Settings', 'wp-discussion-board' ),
				'manage_options',
				self::SETTINGS_PAGE_SLUG,
				array( $this, 'render_settings_page' )
			);
		}

		public function register_settings() {
            foreach ( $this->sections as $setting => $sections ) {
                foreach ( $sections as $section => $name ) {
                    $key = sprintf( 'wpdbd_%s_%s', $setting, $section );
	                register_setting( $key, sprintf( 'wpdbd_%s_settings', $key ) );

	                add_settings_section(
		                $section,
		                $name,
		                '__return_empty_string',
		                sprintf( 'wpdbd_%s', $setting )
	                );
                }
            }

			$settings = wpdbd_get_settings();
			if ( ! empty( $settings ) ) {
				foreach ( $settings as $setting ) {
					add_settings_field(
						$setting['id'],
						$setting['label'],
						array( 'WPDiscussionBoard\Admin\Admin_Settings_Fields', $setting['type'] ),
						$setting['page'],
						$setting['section'],
						$setting
					);
				}
			}
        }

		/**
		 * Render settings page.
         *
         * @since 3.0
		 */
		public function render_settings_page() {
            require WPDBD_PLUGIN_DIR . '/templates/admin/settings.php';
		}

		/**
		 * Admin notices.
		 */
		public function ctdb_admin_notices() {
			$options = get_option( 'ctdb_options_settings' );

			// If the option to hide WP Login is selected with no frontend log-in page specified.
			if (
				( isset( $options['frontend_login_page'] ) &&
				! $options['frontend_login_page'] ) &&
				! empty( $options['hide_wp_login'] )
			) {
				?>
				<div class="notice error">
					<p><?php esc_html_e( 'You\'ve chosen to hide the WP Login page but you haven\'t specified a page on the front end with a login page. Until you specify that, the option to hide WP Login is disabled.', 'wp-discussion-board' ); ?></p>
				</div>
				<?php
				$options['hide_wp_login'] = 0;
				update_option( 'ctdb_options_settings', $options );
			}

		}

		/**
		 * Check that users can be registered.
		 */
		public function user_registration_notice_script() {
			if ( ! get_option( 'users_can_register' ) && 1 !== get_option( 'ctdb_nag_dismissed' ) ) :
				?>
				<script>
					jQuery(document).ready(function ($) {
						$('body').on('click', '.ctdb-registration-notice .notice-dismiss', function () {
							var data = {
								'action': 'ctdb_dismiss_notice'
							}
							jQuery.post(
								ajaxurl,
								data
							);
						});
					});
				</script>
				<?php
			endif;
		}

		/**
		 * Ajax call to dismiss notice.
		 */
		public function ctdb_dismiss_notice() {
			if ( get_option( 'ctdb_nag_dismissed' ) !== false ) {
				update_option( 'ctdb_nag_dismissed', 1 );
			} else {
				add_option( 'ctdb_nag_dismissed', 1 );
			}
			die();
		}

		/**
		 * Display activation key in user profile.
		 */
		public function ctdb_display_activation_key( $user ) {
			?>
			<table class="form-table">
				<tr>
					<td>
						<input type="hidden" value="<?php echo esc_attr( get_user_meta( $user->ID, 'activate_key', true ) ); ?>" class="regular-text" readonly="readonly" />
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Prevent admin access.
		 */
		public function prevent_wp_admin_access() {
			// Admins should always have access
			if ( current_user_can( 'manage_plugins' ) ) {
				return;
			}

			$options = get_option( 'ctdb_options_settings' );

			// Check that we've enabled the option
			if ( ! empty( $options['prevent_wp_admin_access'] ) ) {
				$user_options = get_option( 'ctdb_user_settings' );
				global $current_user;
				$user_roles = $current_user->roles;
				$user_role  = array_shift( $user_roles );

				// Check we're the correct role
				if ( $user_role === $user_options['new_user_role'] && ! defined( 'DOING_AJAX' ) ) {
					wp_safe_redirect( home_url() );
					exit;
				}
			}
		}

		/**
		 * Filter the action links to add Upgrade option.
		 *
		 * @since 2.2.4
		 */
		public function filter_action_links( $links ) {
			$links['settings'] = '<a href="' . admin_url( 'edit.php?post_type=discussion-topics&page=discussion_board' ) . '">' . __( 'Settings', 'wp-discussion-board' ) . '</a>';
			$links['support']  = '<a href="https://wpdiscussionboard.com/docs/">' . __( 'Support', 'wp-discussion-board' ) . '</a>';

			// Check to see if Pro version already installed
			if ( ! defined( 'DB_PRO_VERSION' ) ) {
				$links['upgrade'] = '<a href="https://wpdiscussionboard.com">' . __( 'Upgrade', 'wp-discussion-board' ) . '</a>';
			}

			return $links;
		}
	}
}
