<?php
/*
 * Functions for notifications
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Return new 'From' name for DB notifications
 */
if ( ! function_exists ( 'ctdb_filter_mail_sender_name' ) ) {
	function ctdb_filter_mail_sender_name( $email_sender ) {
		return apply_filters( 'ctdb_filter_mail_sender_name', $email_sender );
	}
}
