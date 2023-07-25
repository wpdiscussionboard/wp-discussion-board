<?php
/*
 * Functions for user registration
 * @since 2.2.12
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns log in page link
 * @since 2.2.12
 */
if ( ! function_exists ( 'ctdb_get_login_page_id' ) ) {
	function ctdb_get_login_page_id() {
		$options = get_option( 'ctdb_options_settings' );
		if( $options['frontend_login_page'] ) {
			return absint( $options['frontend_login_page'] );
		}
		return false;
	}
}



/**
 * Returns activation setting
 * @since 2.2.12
 */
if ( ! function_exists ( 'ctdb_get_activation_setting' ) ) {
	function ctdb_get_activation_setting() {
		$user_options = get_option( 'ctdb_user_settings' );
		$require_activation = $user_options['require_activation'];
		return $require_activation;
	}
}

/**
 * Returns activation setting
 * @param $new_user_id User ID
 * @param $require_activation Activation setting
 * @since 2.2.12
 */
if ( ! function_exists ( 'ctdb_send_admin_email_new_registration' ) ) {
	function ctdb_send_admin_email_new_registration( $new_user_id, $require_activation ) {
		wp_new_user_notification( $new_user_id );
	}
}
add_action( 'ctdb_email_admin_new_registration', 'ctdb_send_admin_email_new_registration', 10, 2 );

/**
 * Send user email to activate their account
 * @param $new_user_id User ID
 * @param $user_email User email
 * @param $require_activation Activation setting
 * @since 2.2.12
 */
if ( ! function_exists ( 'ctdb_email_user_after_registration' ) ) {
	function ctdb_email_user_after_registration( $new_user_id, $user_email, $require_activation ) {

		if( $require_activation != 'none' ) {

			// Add an activation key as usermeta
			$key = substr( md5( time() . rand() ), 0, 16 );
			add_user_meta( $new_user_id, 'activate_key', sanitize_text_field( $key ) );

			// Email the user
			// Set HTML content type
			add_filter( 'wp_mail_content_type', 'ctdb_set_html_content_type' );

			$to = $user_email;
			$subject = apply_filters( 'ctdb_filter_activation_email_subject', get_bloginfo( 'name' ) . ': ' . __( 'Activate your account', 'wp-discussion-board' ) );
			$message = '<p>' . __( 'Thank you for registering. Please activate your account by clicking the link below:', 'wp-discussion-board' ) . '</p>';

			// Get the current URL and append the activation code and user ID
			$protocol = ( is_ssl() ) ? 'https://' : 'http://';

			// Ensure we remove any parameters from the current URL
			$uri = $_SERVER["REQUEST_URI"];
			$uri = explode( '?', $uri );
			$uri = $uri[0];

			$url = $protocol . $_SERVER["HTTP_HOST"] . $uri . '?activate_code=' . $key . '&user_id=' . $new_user_id;

			$message .= '<p><a href="' . $url . '">' . $url . '</a></p>';

			// Set HTML content type
			add_filter( 'wp_mail_content_type', 'ctdb_set_html_content_type' );

			wp_mail( $to, $subject, $message );

			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', 'ctdb_set_html_content_type' );

		}
	}
}
add_action( 'ctdb_email_user_activation_key', 'ctdb_email_user_after_registration', 10, 3 );

/**
 * Set HTML content type for our emails
 * @since 2.2.12
 */
if ( ! function_exists ( 'ctdb_set_html_content_type' ) ) {
	function ctdb_set_html_content_type() {
		return 'text/html';
	}
}

/**
 * Get additional user meta fields
 * @return Array
 * @since 2.3.0
 */
if ( ! function_exists( 'ctdb_get_extra_fields' ) ) {
	function ctdb_get_extra_fields() {
		$registration_fields = ctdb_registration_form_fields();
		$protected_fields    = ctdb_get_protected_registration_fields();
		if ( ! empty( $protected_fields ) && is_array( $protected_fields ) ) {
			foreach ( $protected_fields as $key ) {
				// Unset the protected fields
				unset( $registration_fields[ $key ] );
			}
		}

		// Only the extra fields are left
		return $registration_fields;
	}
}

/**
 * Save additional user meta field values.
 *
 * @param $user_id int    The User ID.
 * @param $post_obj array The $_POST array.
 *
 * @since 2.3.0
 */
if ( ! function_exists( 'ctdb_register_extra_fields' ) ) {
	function ctdb_register_extra_fields( $user_id, $post_obj ) {
		$extra_fields = ctdb_get_extra_fields();

		if ( $extra_fields ) {
			foreach ( $extra_fields as $field ) {
				$meta_key = ! empty( $field['meta_key'] ) ? $field['meta_key'] : $field['id'];

				if ( isset( $post_obj[ $field['id'] ] ) ) {
					update_user_meta( $user_id, $meta_key, sanitize_text_field( wp_unslash( $post_obj[ $field['id'] ] ) ) );
				} elseif ( ! empty( $field['type'] ) && 'checkbox' === $field['type'] && empty( $post_obj[ $field['id'] ] ) ) {
					update_user_meta( $user_id, $meta_key, 'no' );
				}
			}
		}
	}
}

/**
 * These fields aren't included as new registration fields
 * @since 2.3.0
 */
if ( ! function_exists ( 'ctdb_get_protected_registration_fields' ) ) {
	function ctdb_get_protected_registration_fields() {
		return array(
			'ctdb_user_login',
			'ctdb_user_email',
			'ctdb_user_first',
			'ctdb_user_last',
			'ctdb_user_pass',
			'ctdb_user_pass_confirm'
		);
	}
}


/**
 * Return the standard registration form fields
 * @since 2.3.0
 */
if ( ! function_exists ( 'ctdb_registration_form_fields' ) ) {
	function ctdb_registration_form_fields() {
		$form = array(
			'ctdb_user_login'	=> array(
				'id'		=> 'ctdb_user_login',
				'label'		=> __( 'Username', 'wp-discussion-board' ),
				'field'		=> 'input',
				'type'		=> 'text',
				'class'		=> 'required'
			),
			'ctdb_user_email'	=> array(
				'id'		=> 'ctdb_user_email',
				'label'		=> __( 'Email', 'wp-discussion-board' ),
				'field'		=> 'input',
				'type'		=> 'email',
				'class'		=> 'required'
			),
			'ctdb_user_first'	=> array(
				'id'		=> 'ctdb_user_first',
				'label'		=> __( 'First Name', 'wp-discussion-board' ),
				'field'		=> 'input',
				'type'		=> 'text',
				'class'		=> 'required'
			),
			'ctdb_user_last'	=> array(
				'id'		=> 'ctdb_user_last',
				'label'		=> __( 'Last Name', 'wp-discussion-board' ),
				'field'		=> 'input',
				'type'		=> 'text',
				'class'		=> 'required'
			),
			'ctdb_user_pass'	=> array(
				'id'		=> 'ctdb_user_pass',
				'label'		=> __( 'Password', 'wp-discussion-board' ),
				'field'		=> 'input',
				'type'		=> 'password',
				'class'		=> 'required'
			),
			'ctdb_user_pass_confirm'	=> array(
				'id'		=> 'ctdb_user_pass_confirm',
				'label'		=> __( 'Password again', 'wp-discussion-board' ),
				'field'		=> 'input',
				'type'		=> 'password',
				'class'		=> 'required'
			)
		);
		// See ctdb_get_protected_registration_fields() for protected fields
		$form = apply_filters( 'ctdb_registration_form_fields', $form );

		return $form;
	}
}
