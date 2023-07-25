<?php
/*
 * Functions for user roles and permissions
 * @since 2.1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return admin email address
 * @since 2.2.12
 */
function ctdb_get_admin_email() {
	$options = get_option( 'ctdb_options_settings' );
	$notification_email = $options['notification_email'];
	if( ! empty( $notification_email ) || ! is_email( $notification_email ) ) {
		$to = $notification_email;
	} else {
		$to = get_option( 'admin_email' );
	}
	return apply_filters( 'ctdb_admin_email', $to );
}

/**
 * Return display_user_name setting
 * @since 2.2.1
 */
function ctdb_display_user_name() {
	$options = get_option( 'ctdb_user_settings' );
	$display_as = 'display_name'; // This is the default
	if( isset( $options['display_user_name'] ) ) {
		$display_as = esc_attr( $options['display_user_name'] );
	}
	return $display_as;
}

/**
 * Return the filtered author name
 * @since 2.2.9
 */
function ctdb_get_author() {
	$author = get_the_author();
	$author = apply_filters( 'ctdb_author_name', $author );
	return $author;
}

/**
 * Filter author name to respect display_user_name setting
 */
function ctdb_filter_author_name( $author ) {
	$display_as = ctdb_display_user_name();
	if( ! empty( $display_as ) ) {
		return get_the_author_meta( $display_as );
	}
	return $author;
}
add_filter( 'ctdb_author_name', 'ctdb_filter_author_name', 100 );

/**
 * Filter comment author name to respect display_user_name setting
 * @since 2.2.1
 */
function ctdb_filter_comment_author_name( $return, $author, $comment_id ) {
	$comment = get_comment( $comment_id );
	// Only filter comments on Topics
	if( 'discussion-topics' != get_post_type( $comment->comment_post_ID ) ) {
		return $return;
	}
	$display_as = ctdb_display_user_name();
	if( ! empty( $display_as ) ) {
		// Get comment author ID
		$user = get_userdata( $comment->user_id );
		if( $user ) {
			$url = $user->user_url;
			$url = apply_filters( 'ctdb_comment_author_url', $url, $user, $comment_id );
		}

		if( $user ) {
			$author = $user->$display_as;
			if( empty( $url ) || 'http://' == $url ) {
				$return = $author;
			} else {
				$return = "<a href='$url' rel='external nofollow' class='url'>$author</a>";
			}
		} else {
			return $author;
		}
	}
	return $return;
}
add_filter( 'get_comment_author_link', 'ctdb_filter_comment_author_name', 10, 3 );


/**
 * Return the filtered current user name
 * @since 2.2.9
 */
function ctdb_get_current_user() {
	if( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if( isset( $user->user_login ) ) {
			$user_login = $user->user_login;
		//	$user_login = apply_filters( 'ctdb_author_name', $user_login );
			return $user_login;
		}
	}
	return false;
}

/**
 * Return the filtered current user email address
 * @since 2.2.9
 */
function ctdb_get_current_user_email() {
	if( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if( isset( $user->user_email ) ) {
			$user_email = $user->user_email;
		//	$user_login = apply_filters( 'ctdb_author_name', $user_login );
			return $user_email;
		}
	}
	return false;
}

/**
 * Return the filtered current user bio
 * @since 2.2.9
 */
function ctdb_get_current_user_bio() {
	if( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if( isset( $user->user_description ) ) {
			$user_description = $user->user_description;
		//	$user_login = apply_filters( 'ctdb_author_name', $user_login );
			return $user_description;
		}
	}
	return false;
}

/**
 * Return the filtered current user avatar
 * @since 2.2.9
 */
function ctdb_get_current_user_avatar( $size=96 ) {
	if( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if( isset( $user->ID ) ) {
			$user_id = $user->ID;
			$avatar = get_avatar( $user_id, absint( $size ) );
			return $avatar;
		}
	}
	return false;
}

/**
 * Returns most recent commenter
 * @since 2.2.9
 * @return Mixed
 */
if ( ! function_exists ( 'ctdb_get_most_recent_commenter' ) ) {
	function ctdb_get_most_recent_commenter() {
		global $post;
		$args = array(
			'number'	=> '1',
			'post_id'	=> $post->ID
		);
		$comments = get_comments( $args );
		if( ! empty( $comments ) ) {
			$author = $comments[0]->comment_author;
			$author = apply_filters( 'get_comment_author_link', $author, $author, $comments[0]->comment_ID );
			return $author;
		}
		return false;
	}
}

/**
 * Returns whether current user is permitted to view content
 * @since 2.1.0
 */
if ( ! function_exists ( 'ctdb_is_user_permitted' ) ) {
	function ctdb_is_user_permitted() {

		// Array of user roles
		$roles = ctdb_get_role();
		// Array of permitted roles
		$permitted_roles = ctdb_get_permitted_viewer_roles();

		// Can the user view the content
		$user_can = false;
		if( count( $permitted_roles ) == 1 ) {
			// Permitted roles only contains 'administrator' so everyone can view
			$user_can = true;
		} else if( ! empty( $roles ) ) {
			foreach( $roles as $role ) {
				if( in_array( $role, $permitted_roles ) ) {
					$user_can = true;
					break;
				}
			}
		}

		return $user_can;

	}
}

/**
 * Returns the current user's role
 * @since 2.1.0
 */
if ( ! function_exists ( 'ctdb_get_role' ) ) {
	function ctdb_get_role() {

		if( ! is_user_logged_in() ) {
			// User not logged in
			$role = array();
		} else {
			$user = wp_get_current_user();
			if( empty( $user ) ) {
				$role = array();
			} else {
				$role = ( array ) $user -> roles;
			}
		}

		return $role;

	}
}



/**
 * Returns permitted user roles for viewing topics
 * @since 1.0.0
 */
if ( ! function_exists ( 'ctdb_get_permitted_viewer_roles' ) ) {
	function ctdb_get_permitted_viewer_roles() {

		$options = get_option( 'ctdb_user_settings' );
		if( isset( $options['discussion_board_minimum_role'] ) ) {
			$viewer_roles = $options['discussion_board_minimum_role'];
		} else {
			$viewer_roles = array();
		}

		// Admins can always view
		$viewer_roles[] = 'administrator';

		return apply_filters( 'ctdb_filter_permitted_viewer_roles', $viewer_roles );

	}
}

/**
 * Returns whether current user is permitted to post content
 * @since 1.0.0
 */
if ( ! function_exists ( 'ctdb_is_posting_permitted' ) ) {
	function ctdb_is_posting_permitted() {
		// Array of user roles
		$roles = ctdb_get_role();
		// Array of permitted roles
		$permitted_roles = ctdb_get_permitted_poster_roles();
		// Can the user post the content
		$user_do_post = false;
		if( empty( $permitted_roles ) ) {
			// Permitted roles is empty so everyone can post
			$user_do_post = true;
		} else if( ! empty( $roles ) ) {
			foreach( $roles as $role ) {
				if( in_array( $role, $permitted_roles ) ) {
					$user_do_post = true;
					break;
				}
			}
		}
		return apply_filters( 'ctdb_filter_is_posting_permitted', $user_do_post );
	}
}

/**
 * Returns permitted user roles for posting topics
 * @since 2.3.1
 */
if ( ! function_exists ( 'ctdb_get_unfiltered_permitted_poster_roles' ) ) {
	function ctdb_get_unfiltered_permitted_poster_roles() {
		$options = get_option( 'ctdb_user_settings' );
		if( isset( $options['minimum_user_roles'] ) ) {
			$poster_roles = $options['minimum_user_roles'];
		} else {
			$poster_roles = array();
		}
		return $poster_roles;
	}
}

/**
 * Returns permitted user roles for posting topics
 * @since 1.0.0
 */
if ( ! function_exists ( 'ctdb_get_permitted_poster_roles' ) ) {
	function ctdb_get_permitted_poster_roles() {
		$poster_roles = ctdb_get_unfiltered_permitted_poster_roles();
		return apply_filters( 'ctdb_filter_permitted_poster_roles', $poster_roles );
	}
}
