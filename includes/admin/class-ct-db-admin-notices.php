<?php

/*
 * Discussion Board Notices class
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CT_DB_Admin_Notices extends CT_DB_Admin {

	public function __construct() {
		//
	}

	/*
	 * Initialize the class and start calling our hooks and filters
	 * @since 1.0.0
	 */

	public function init() {
		add_action( 'admin_notices', array( $this, 'pro_notice' ) );
		add_action( 'admin_notices', array( $this, 'review_request' ) );
		add_action( 'wp_ajax_dismiss_notice', array( $this, 'dismiss_notice_callback' ) );
	}
	
	/**
	 * Request a review
	 * @since 1.6.0
	*/
	public function review_request() {
		// Check if notice has already been dismissed
		$dismissed = get_option( 'ctdb_review_notice_dismissed' );
		
		if( false === $dismissed ) {
			// If the notice hasn't already been dismissed, check whether to show it
			// At 25 topics
			$count_topics = wp_count_posts( 'discussion-topics' )->publish;
			
			if( $count_topics > 25 ) { ?>
				<div class="notice notice-info is-dismissible" data-notice="review_notice">
					<?php printf( '<h4>%s</h4>', __( 'Discussion Board', 'wp-discussion-board' ) ); ?>
					<?php printf( '<p>%s</p>', __( 'Hi. It looks like you have been using Discussion Board for a while now. If you are finding it useful I\'d really appreciate it if you could give it a 5 star rating on the WordPress repository. Positive reviews and feedback help me to continue developing the plugin.', 'wp-discussion-board' ) ); ?>
					<?php printf( 
						'<p>%s</p>',
						sprintf( 
							__( 'Don\'t forget: if you have any questions or problems regarding the plugin, please <a href="%s" target="_blank">contact me directly <span class="dashicons dashicons-external"></span></a> and I will get straight back to you.', 'wp-discussion-board' ),
						'https:/catapultthemes.com/contact/'
						 )
					); ?>
					<?php printf( '<p>%s</p>', __( 'Many thanks for your support.', 'wp-discussion-board' ) ); ?>
					<?php printf( '<p><em>%s</em></p>', __( 'Gareth, Catapult Themes.', 'wp-discussion-board' ) ); ?>
				
					<?php printf(
						'<p><a class="button button-primary" href="%s">%s</a> <a class="dismiss-text button button-secondary" data-notice="review_notice" href="#">%s</a></p>',
						'https://wordpress.org/support/plugin/wp-discussion-board/reviews/',
						__( 'Leave a review', 'wp-discussion-board' ),
						__( 'No thanks', 'wp-discussion-board' )
					 ); ?>
					
				</div>
				<script>
					jQuery(document).ready(function($){
						$('body').on('click','.notice-dismiss',function(){
							var notice = $(this).parent().attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': notice,
								'security': "<?php echo wp_create_nonce( 'dismiss_notice' ); ?>",
							}
							$.post(ajaxurl,data);
						});
						$('body').on('click','.dismiss-text',function(){
							var notice = $(this).attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': notice,
								'security': "<?php echo wp_create_nonce( 'dismiss_notice' ); ?>",
							}
							$.post(ajaxurl,data);
							$(this).parent().parent().fadeOut();
						});
					});
				</script>
			<?php }
		}
		
	}
	
	/**
	 * Admin notices
	 * @since 1.1.0
	*/
	public function pro_notice() {
		
		if ( class_exists ( 'CT_DB_Pro_Public' ) ) {
			return;
		}
		// Check if notice has already been dismissed
		$dismissed = get_option( 'ctdb_pro_notice_dismissed' );
		
		if( false === $dismissed ) {
			// If the notice hasn't already been dismissed, check whether to show it
			// At 50 topics
			$count_topics = wp_count_posts( 'discussion-topics' )->publish;
			
			if( $count_topics > 50 ) { ?>
				<div class="notice notice-info is-dismissible" data-notice="pro_notice">
					<?php printf( '<h4>%s</h4>', __( 'Discussion Board Pro - Save 20%', 'wp-discussion-board' ) ); ?>
					<?php printf( '<p>%s</p>', __( 'Hi. Thanks for using Discussion Board - I hope it\'s helping you run a successful forum. I just wanted to remind you about the Pro version, which features multiple boards for creating sub-forums, categories and tags, topic following, user profiles, WYSIWYG editing, image uploads, and more.', 'wp-discussion-board' ) ); ?>
					<?php printf( '<p>%s</p>', __( 'You can get 20% off the cost of the Pro version - just use the discount code UPGRADE on the checkout page. Click the button below to find out more.', 'wp-discussion-board' ) ); ?>
					<?php printf( '<p>%s</p>', __( 'Thanks for using my plugin.', 'wp-discussion-board' ) ); ?>
					<?php printf( '<p><em>%s</em></p>', __( 'Gareth, Catapult Themes.', 'wp-discussion-board' ) ); ?>
					
					<?php printf(
						'<p><a class="button button-primary" href="%s">%s</a> <a class="dismiss-text button button-secondary" data-notice="pro_notice" href="#">%s</a></p>',
						'https://catapultthemes.com/downloads/discussion-board-pro/?utm_source=pro_notice&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=dbpro',
						__( 'Find out more', 'wp-discussion-board' ),
						__( 'No thanks', 'wp-discussion-board' )
					 ); ?>
					
					
				</div>
				<script>
					jQuery(document).ready(function($){
						$('body').on('click','.notice-dismiss',function(){
							var notice = $(this).parent().attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': 'pro_notice',
								'security': "<?php echo wp_create_nonce( 'dismiss_notice' ); ?>",
							}
							$.post(ajaxurl,data);
						});
						$('body').on('click','.dismiss-text',function(){
							var notice = $(this).attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': 'pro_notice',
								'security': "<?php echo wp_create_nonce( 'dismiss_notice' ); ?>",
							}
							$.post(ajaxurl,data);
							$(this).parent().parent().fadeOut();
						});
					});
				</script>
			<?php }
		}
	}
	
	/**
	 * Dismiss notices
	 * @since 1.1.0
	*/
	public function dismiss_notice_callback() {
		check_ajax_referer( 'dismiss_notice', 'security' );
		$notice = sanitize_text_field( $_POST['notice'] );
		$option = 'ctdb_' . $notice . '_dismissed';
		update_option( $option, 1 );
	}

}

global $CT_DB_Admin_Notices;
$CT_DB_Admin_Notices = new CT_DB_Admin_Notices();
$CT_DB_Admin_Notices -> init();