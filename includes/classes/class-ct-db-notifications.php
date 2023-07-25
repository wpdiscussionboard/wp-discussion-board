<?php
/**
 * Discussion Board Notifications class
*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin notifications class
 **/
if( ! class_exists( 'CT_DB_Notifications' ) ) {

	class CT_DB_Notifications {

		public function __construct() {

		}

		/**
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		 */
		public function init() {

			add_action( 'transition_post_status', array( $this, 'email_notification' ), 10, 3 );
			// Disabled 1.5.0 to avoid duplicate notifications
			// add_action( 'publish_discussion-topics', array( $this, 'email_notification_on_publish' ), 10, 2 );

			add_filter( 'ctdb_filter_single_content_end', array( $this, 'add_opt_out_field' ), 20 ); // We can also add this to the new topic form
			add_action( 'wp_ajax_update_optout_status', array( $this, 'update_optout_status' ) );

			add_action( 'comment_post', array( $this, 'new_comment_posted' ), 10, 2 );
			add_action( 'transition_comment_status', array( $this, 'comment_approved' ), 10, 3 );

		}

		/**
		 * Add checkbox for topic poster to opt in/out of notifications
		 * @since 1.5.0
		 */
		public function add_opt_out_field( $content ) {

			global $post;

			// Check if user is logged in and is topic author
			if( ! is_user_logged_in() || $post->post_author != get_current_user_id() ) {
				return $content;
			}
			$options = get_option( 'ctdb_options_settings' );

			if( empty( $options['enable_notification_opt_out'] ) ) {
				return $content;
			}

			$optout_content = '';

			// If we're on a single page for the discussion-topic post type
			if( is_single() && 'discussion-topics' == get_post_type() ) {

				$postid = get_the_ID();

				// Check the author has opted out
				$opted_out = get_post_meta( $postid, 'ctdb_author_opted_out', true );

				$checked = '';
				if( ! empty( $opted_out ) ) {
					$checked = 'checked="checked"';
				}

				// Get the message
				$message_label = __( 'Opt out of receiving notifications for this topic.', 'wp-discussion-board' );
				$message_label = apply_filters( 'ctdb_opt_out_message_label', $message_label );
				//$options = get_option( 'ctdb_followers_settings' );
				//$message = $options['follow_message'];
				$optout_content .= wp_nonce_field( 'optout_update_nonce', 'optout_update_nonce' );
				$optout_content .= '<p class="ctdb-optout-box">';
				$optout_content .= '<input type="checkbox" class="ctdb-optout-checkbox" id="ctdb-optout-checkbox" name="ctdb-optout-checkbox" ' . $checked . '>';
				$optout_content .= '<label for="ctdb-optout-checkbox">' . esc_html( $message_label ) . '</label>';

				$optout_content .= '</p>';
				$optout_content .= '<script>
				// Follower AJAX
				jQuery(document).ready(function($){
					$("#ctdb-optout-checkbox").on("change",function(){
						var ischecked = $(this).is(":checked");
						$.ajax({
							type: "POST",
							url: "' . admin_url( 'admin-ajax.php' ) . '",
							data: {
								postid: ' . $postid . ',
								checked: ischecked,
								action: "update_optout_status",
								security: $("#optout_update_nonce").val()
							},
							success: function(response){
							}
						})
					});
				});
				</script>';

			}
			$optout_content = apply_filters( 'ctdb_output_content', $optout_content );
			return $optout_content . $content;
		}

		/**
		 * Update the post meta
		 * @since 1.4.0
		 */
		public function update_optout_status() {
			check_ajax_referer( 'optout_update_nonce', 'security' );
			$return = 'fail';
			if ( ! empty( $_REQUEST['postid'] ) ) {

				$postid = absint( $_REQUEST['postid'] );
				$checked = stripslashes( strip_tags( $_REQUEST['checked'] ) );

				// We need to update the post meta field
				if( $checked == 'true' ) {
					$return = 'ok';
					update_post_meta( $postid, 'ctdb_author_opted_out', $checked );
				} else {
					$return = 'delete';
					delete_post_meta( $postid, 'ctdb_author_opted_out' );
				}

			}
			echo $return;
			die();
		}

		/**
		 * Notify the topic author when a new comment is posted
		 * Only notify author when there is a topic for moderation
		 * @since 1.0.0
		 */
		public function new_comment_posted( $comment_ID, $comment_approved ) {

			if( $comment_approved === 1 ) {
				// Comment is approved
				// Removed @since 1.5.0
				 $comment_object = get_comment( $comment_ID );
				 $this->notify_author_new_comment( $comment_object );
			} else {
				// Comment is for moderation
				$comment_object = get_comment( $comment_ID );
				$this->notify_admin_moderated_comment( $comment_object );
			}

		}

		/**
		 * Notify the topic author when a comment is approved
		 * @since 1.0.0
		 */
		public function comment_approved( $new_status, $old_status, $comment ) {

			if( $new_status != $old_status && $new_status == 'approved' ) {
				$this->notify_author_new_comment( $comment );
			}

		}

		/**
		 * Notify the topic author when a new comment is posted
		 * @since 1.0.0
		 */
		public function notify_author_new_comment( $comment ) {

			$options = get_option( 'ctdb_options_settings' );

			// If we've enabled the global opt out then don't send a notification
			if( ! empty( $options['global_notification_opt_out'] ) ) {
				return;
			}

			global $post;

			$post_type = get_post_type( $comment->comment_post_ID );
			// Don't send notification on any other post type
			if( 'discussion-topics' != $post_type ) {
				return;
			}

			// Check if the author has opted out
			$opted_out = get_post_meta( $comment->comment_post_ID, 'ctdb_author_opted_out', true );

			// If opt out is enabled and user has opted out, don't send notification
			if( isset( $options['enable_notification_opt_out'] ) && ! empty( $opted_out ) ) {
				return;
			}

			// Grab some information about the comment
			$author = $comment->comment_author;
			$comment_post_ID = $comment->comment_post_ID;

			// Find the topic author's email
			$post = get_post( $comment_post_ID );
			$author_id = $post->post_author;
			$title = $post->post_title;
			$post_author = get_the_author_meta( 'user_email', $author_id );

			// If the topic author and the commenter are the same, no need to send an email
			// I.e. the topic author has left a comment on his own topic
			$commenter_email = $comment->comment_author_email;
			if( $post_author == $commenter_email ) {
				return;
			}

			$subject = apply_filters( 'ctdb_filter_new_comment_subject', get_bloginfo( 'name' ) . ': ' . __( 'New comment on your topic', 'wp-discussion-board' ) );
			$content = '<p>' . __( 'A new comment has been left on your topic: ', 'wp-discussion-board' );
			$content .= $title . '</p>';

			// Add comment content
			$content .= '<p>' . esc_html( $comment->comment_content ) . '</p>';

			$content .= esc_url( get_permalink( $post->ID ) . '#comment-' . $comment->comment_ID );

			// Set HTML content type
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

			// Send mail
			wp_mail( $post_author, $subject, $content );

			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		}

		/**
		 * Notify the admin when a new comment is posted
		 * @since 1.0.0
		 */
		public function notify_admin_moderated_comment( $comment ) {

			global $post;

			// Grab some information about the comment
			$author = $comment->comment_author;
			$comment_post_ID = $comment->comment_post_ID;

			// Send to the admin
			$post = get_post( $comment_post_ID );
			// $author_id = $post->post_author;
			$title = $post->post_title;
			// $post_author = get_the_author_meta( 'user_email', $author_id );

			$to = $this->admin_email();

			$subject = apply_filters( 'ctdb_filter_moderate_new_comment_subject', get_bloginfo( 'name' ) . ': ' . __( 'New comment - please moderate', 'wp-discussion-board' ) );
			$content = '<p>' . __( 'Please moderate the following comment on the topic: ', 'wp-discussion-board' );
			$content .= $title . '</p>';

			$content .= get_permalink( $post->ID );

			// Set HTML content type
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

			// Send mail
			wp_mail( $to, $subject, $content );

			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		}

		/**
		 * Send email notification when topic is created
		 * @since 1.0.0
		 */
		public function email_notification( $new_status, $old_status, $post ) {
			$message = '';
			if( 'discussion-topics' == $post->post_type ) {
				// Only send a mail if the status has changed and an email address is specified
				if( $new_status != $old_status && $new_status != 'trash' ) {

					do_action( 'ctdb_email_notification_new_topic', $post );

					// Set HTML content type
					add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

					$to = $this->admin_email();

					$subject = apply_filters( 'ctdb_filter_new_topic_subject', __( 'New Topic Posted', 'wp-discussion-board' ) );
					$message = '<p>' . __( 'A new topic has been created:', 'wp-discussion-board' ) . '<br>';
					$message .= $post->post_title . '</p>';
					$message .=  '<p><strong>' . __( 'Topic status: ', 'wp-discussion-board' ) . $new_status . '</strong></p>';
					if( $new_status == 'draft' ) {
						$message .= '<p>' . __( 'This topic is waiting for you to moderate and publish it.', 'wp-discussion-board' ) . '</p>';
						$message .= '<p>' . stripslashes( strip_tags( $post->post_content ) ) . '</p>';
						$url = admin_url() . 'edit.php?post_type=discussion-topics';
						$message .= '<p><a href="' . $url . '">' . $url . '</a></p>';
					} else if( $new_status == 'publish' ) {
						do_action( 'ctdb_email_notification_publish_topic', $post );
						$message .= '<p>' . __( 'This topic has been published and is live.', 'wp-discussion-board' ) . '</p>';
						$message .= '<p>' . stripslashes( strip_tags( $post->post_content ) ) . '</p>';
						$url = esc_url( get_permalink( $post->ID ) );
						$message .= '<p><a href="' . $url . '">' . $url . '</a></p>';
					}

					// Set HTML content type
					add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

					wp_mail( $to, $subject, $message );

					// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
					remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

				}
			}
		}

		/**
		 * Send email notification when topic is published
		 * Disabled in 1.5.0
		 * @since 1.0.6
		 */
		public function email_notification_on_publish( $ID, $post ) {

			$message = '';

			$to = $this->admin_email();

			// Set HTML content type
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

			$subject = apply_filters( 'ctdb_email_notification_on_publish_subject', __( 'New Topic Posted', 'wp-discussion-board' ), $post );
			$message = __( '<p>A new topic has been created:<br>', 'wp-discussion-board' ) .
			$message .= $post->post_title . '</p>';

			$message .= '<p>' . __( 'This topic has been published and is live.', 'wp-discussion-board' ) . '</p>';

			$url = admin_url() . 'edit.php?post_type=discussion-topics';
			$message .= '<p><a href="' . $url . '">' . $url . '</a></p>';

			$message = apply_filters( 'ctdb_email_notification_on_publish_message', $message, $post, $url );

			// Set HTML content type
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

			wp_mail( $to, $subject, $message );

			// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
			remove_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

		}

		public function set_html_content_type() {
			return 'text/html';
		}

		/**
		 * Returns the admin email address if alternative address not set
		 * @since 1.3.0
		 */
		public function admin_email() {
			// Set HTML content type
			add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
			$email = ctdb_get_admin_email();
			return $email;
		}

	}

}
