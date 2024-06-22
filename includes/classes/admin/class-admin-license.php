<?php
/**
 * Activate a free license.
 *
 * @since 2.4
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't initialise if there's already a Discussion Board activated.
if ( ! class_exists( 'WPDiscussionBoard\Admin\Admin_License' ) ) {
	/**
	 * Plugin admin class.
	 **/
	class Admin_License {

		/**
		 * Option key to hold the free license.
		 *
		 * @since 2.4
		 */
		const FREE_LICENSE_OPTION_KEY = 'wpdbd_free_license';

		/**
		 * Endpoint to generate free license.
		 *
		 * @since 2.4
		 */
		const LICENSE_ENDPOINT = 'https://wpdiscussionboard.com/wp-json/license/v1/free';

		/**
		 * Nonce action.
		 *
		 * @since 2,4
		 */
		const NONCE_ACTION = 'wpdbd_free_license';

		/**
		 * Nonce name.
		 *
		 * @since 2.4
		 */
		const NONCE_NAME = 'wpdbd_nonce';

		/**
		 * Init.
		 *
		 * @since 2.4
		 */
		public function init() {
			add_action( 'admin_init', array( $this, 'activate_license' ) );
		}

		/**
		 * Send the request to activate the free license.
		 *
		 * @since 2.4
		 */
		public function activate_license() {
			if ( ! empty( $_POST['free_license_activator'] ) ) {
				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
					return;
				}

				$email = sanitize_email( wp_unslash( $_POST['email'] ) );

				if ( is_email( $email ) ) {
					$user       = get_user_by( 'email', $email );
					$first_name = '';
					$last_name  = '';
					$url        = rawurlencode( home_url() );

					if ( is_a( $user, 'WP_User' ) ) {
						$first_name = $user->first_name;
						$last_name  = $user->last_name;
					}

					// Make request, save key.
					$request = wp_remote_post(
						self::LICENSE_ENDPOINT,
						array(
							'headers' => array(
								'Content-Type' => 'application/json',
							),
							'body'    => wp_json_encode(
								array(
									'email_address' => $email,
									'first_name'    => $first_name,
									'last_name'     => $last_name,
									'url'           => $url,
								)
							),
						)
					);


					// BREVO
					if ( !empty( $_POST['wo_free_license_subscribe'] ) ) {
						$fl = wp_remote_post(
							'https://wpdiscussionboard.com/wp-admin/admin-ajax.php',
							array(
								'body'    =>
									array(
										'action' => 'wpdb_free_license',
										'email_address' => $email,
										'fname'    => $first_name,
										'lname'     => $last_name,
										'url'           => $url,
									)
							)
						);
					}

					if ( ! is_wp_error( $request ) ) {
						$license = json_decode( $request['body'] );

						if ( ! empty( $license ) ) {
							update_option( self::FREE_LICENSE_OPTION_KEY, sanitize_text_field( $license ) );

							add_action(
								'admin_notices',
								function() {
									?>
									<div class="notice notice-success">
										<p><?php esc_html_e( 'Free license activated!', 'wp-discussion-board' ); ?></p>
									</div>
									<?php
								}
							);
						}
					} else {
						add_action(
							'admin_notices',
							function() {
								?>
								<div class="notice notice-error">
									<p><?php esc_html_e( 'Something went wrong! Try again later.', 'wp-discussion-board' ); ?></p>
								</div>
								<?php
							}
						);
					}
				} else {
					add_action(
						'admin_notices',
						function() {
							?>
							<div class="notice notice-error">
								<p><?php esc_html_e( 'Invalid email address!', 'wp-discussion-board' ); ?></p>
							</div>
							<?php
						}
					);
				}
			}
		}
	}
}
