<?php
/**
 * Discussion Board admin class
*/

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin admin class
 **/
if( ! class_exists( 'CT_DB_Admin_About' ) ) { // Don't initialise if there's already a Discussion Board activated

	class CT_DB_Admin_About {

		public function __construct() {
			//
		}

		/**
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		 */
		public function init() {
			add_action( 'admin_menu', array( $this, 'add_about_submenu' ), 99 );
			add_action( 'admin_init', array( $this, 'redirect_to_about_page' ), 11 );
		}

		function redirect_to_about_page() {

			$done_redirect = get_option( 'ctdb_done_redirect' );

			if( false === $done_redirect ) {
				$redirect_to = admin_url( 'edit.php' );
				$redirect_to = add_query_arg(
					array(
						'post_type'	=> 'discussion-topics',
						'page'		=> 'ctdb_about'
					),
					$redirect_to
				);
				update_option( 'ctdb_done_redirect', 1 );
				wp_safe_redirect( $redirect_to ); exit;

			}

		}

		// Add the submenu item
		public function add_about_submenu() {
			add_submenu_page( 'edit.php?post_type=discussion-topics', __( 'About', 'wp-discussion-board' ), __( 'About', 'wp-discussion-board' ), 'manage_options', 'ctdb_about', array( $this, 'about_page' ) );
		}


		public function about_page() {
			// Get the page IDs
			$options = get_option( 'ctdb_options_settings' );
			$new_topic_page = $options['new_topic_page'];
			$topics_page = $options['discussion_topics_page'];
			$login_page = $options['frontend_login_page']; ?>

			<div class="wrap about-wrap">
				<?php printf( '<h1>%s</h1>', __( 'Welcome to Discussion Board', 'wp-discussion-board' ) ); ?>
				<div class="ctdb-outer-wrap">
					<div class="ctdb-inner-wrap">
						<div class="ctdb-about-section">
							<?php printf( '<h3 class="ctdb-about">%s</h3>', __( 'Thanks for installing Discussion Board. This page is intended to help you get started as smoothly as possible.', 'wp-discussion-board' ) ); ?>

						</div>
						<div class="ctdb-about-section">
							<?php printf( '<h3>%s</h3>', __( 'Getting started', 'wp-discussion-board' ) ); ?>
							<?php printf(
								'<p>%s <em>%s</em></p>',
								__( 'Discussion Board has automatically created the three essential pages you need. You can click the links below to view these pages if you like - but please note that because you are already logged in, you won\'t see the same content that a new user would.', 'wp-discussion-board' ),
								__( 'To see the pages from the point of view of a new user, try viewing them in an incognito browser window.', 'wp-discussion-board')
							); ?>
							<?php printf(
								'<ul><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li></ul>',
								esc_url( get_permalink( ( int ) $topics_page ) ),
								__( 'Topics', 'wp-discussion-board' ),
								esc_url( get_permalink( ( int ) $new_topic_page ) ),
								__( 'New Topic Form', 'wp-discussion-board' ),
								esc_url( get_permalink( ( int ) $login_page ) ),
								__( 'Log In', 'wp-discussion-board' )
							); ?>

							<?php printf( '<p>%s</p>', __( 'These pages are all you need for your forum to function - probably the only thing you need to do now is add the pages to your menu so that users can access them.', 'wp-discussion-board' ) ); ?>
							<?php printf( '<p>%s</p>', __( 'You can add any content you like to these pages but don\'t delete the shortcodes that have been added already - the shortcodes create functional content, like the log-in form.', 'wp-discussion-board' ) ); ?>
							<?php printf( '<h4>%s</h4>', __( 'Support link', 'wp-discussion-board' ) ); ?>
							<?php printf(
								'<ul class="ctdb-support-links"><li><a href="%s" target="_blank">%s</a></li></ul>',
								'https://discussionboard.pro/documentation/',
								__( 'Getting started support article', 'wp-discussion-board' )
							); ?>
						</div>

						<div class="ctdb-about-section">
							<?php printf( '<h3>%s</h3>', __( 'FAQs', 'wp-discussion-board' ) ); ?>
							<?php printf( '<p>%s</p>', __( 'There are a couple of questions that you might need the answer to when you get started:', 'wp-discussion-board' ) ); ?>
							<?php printf(
								'<p><strong>%s</strong><br>%s</p><p><strong>%s</strong><br>%s</p>',
								__( 'I see an error message about invalid post type. What\'s that?', 'wp-discussion-board' ),
								__( 'In your dashboard, just go to Settings > Permalinks. This will clear the permalinks and validate the topics post type - then everything should be fine.', 'wp-discussion-board' ),
								__( 'Help, I can\'t seem to log out. Why?', 'wp-discussion-board' ),
								__( 'We\'ve included a helpful log-in / log-out shortcode to display a log-in / log-out link. Use a plugin like Shortcode Widget and add the [discussion_board_log_in_out] shortcode to your sidebar or other widget area of your choice. The plugin also automatically displays a log-out link under the new topic form.', 'wp-discussion-board' )
							); ?>

						</div>
						<div class="ctdb-about-section">
							<?php printf( '<h3>%s</h3>', __( 'Settings', 'wp-discussion-board' ) ); ?>
							<?php printf( '<p>%s</p>', __( 'You can change the settings by clicking on the Settings link in the Discussion Board menu. There is more information on the links below:', 'wp-discussion-board' ) ); ?>
							<?php printf(
								'<ul class="ctdb-support-links"><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li></ul>',
								'https://discussionboard.pro/documentation/#general-tab',
								__( 'General settings', 'wp-discussion-board' ),
								'https://discussionboard.pro/documentation/#design-tab',
								__( 'Design settings', 'wp-discussion-board' ),
								'https://discussionboard.pro/documentation/#user-tab',
								__( 'User settings', 'wp-discussion-board' )
							); ?>

						</div>
						<div class="ctdb-about-section">
							<?php printf( '<h3>%s</h3>', __( 'Further links', 'wp-discussion-board' ) ); ?>
							<?php printf( '<p>%s</p>', __( 'If you have any questions, please ask them via the support forum. If you find this plugin useful, please leave it a 5 star review - links below:', 'wp-discussion-board' ) ); ?>
							<?php printf(
								'<ul><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li></ul>',
								'https://wordpress.org/support/plugin/wp-discussion-board',
								__( 'Support forum', 'wp-discussion-board' ),
								'https://wordpress.org/support/plugin/wp-discussion-board/reviews/',
								__( 'Leave a review', 'wp-discussion-board' )
							); ?>
						</div>
					</div><!-- .ctdb-inner-wrap -->
					<div class="ctdb-banners">
						<div class="ctdb-banner hide-dbpro">
							<a target="_blank" href="https://discussionboard.pro/?utm_source=plugin_ad&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=dbpro">
								<img src="<?php echo DB_PLUGIN_URL . 'assets/images/discussion-board-banner-ad.png'; ?>" alt="" >
							</a>
						</div>
						<div class="ctdb-banner">
							<a target="_blank" href="https://catapultthemes.com/downloads/showcase/?utm_source=plugin_ad&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=showcase"><img src="<?php echo DB_PLUGIN_URL . 'assets/images/showcase-featured-image.jpg'; ?>" alt="" ></a>
						</div>
						<div class="ctdb-banner">
							<a target="_blank" href="https://catapultthemes.com/downloads/bookings-for-woocommerce/?utm_source=plugin_ad&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=bookings"><img src="<?php echo DB_PLUGIN_URL . 'assets/images/bookings-for-woocommerce-banner-ad.jpg'; ?>" alt="" ></a>
						</div>
						<div class="ctdb-banner">
							<a target="_blank" href="http://superheroslider.catapultthemes.com/?utm_source=plugin_ad&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=superhero"><img src="<?php echo DB_PLUGIN_URL . 'assets/images/shs-banner-ad.png'; ?>" alt="" ></a>
						</div>
						<div class="ctdb-banner">
							<a target="_blank" href="https://singularitytheme.com/?utm_source=plugin_ad&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=singularity"><img src="<?php echo DB_PLUGIN_URL . 'assets/images/singularity-banner-ad.png'; ?>" alt="" ></a>
						</div>
					</div>
				</div><!-- .ctdb-outer-wrap -->
			</div><!-- .wrap -->
			<?php
		}

	}

}
