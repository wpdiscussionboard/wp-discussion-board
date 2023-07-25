<?php
/**
 * Getting Started admin page.
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

// Get the page IDs.
$options        = get_option( 'ctdb_options_settings' );
$new_topic_page = $options['new_topic_page'];
$topics_page    = $options['discussion_topics_page'];
$login_page     = $options['frontend_login_page'];
$license_key    = get_option( Admin_License::FREE_LICENSE_OPTION_KEY );
?>

<div class="wrap about-wrap">
	<?php printf( '<h1>%s</h1>', esc_html__( 'Welcome to Discussion Board', 'wp-discussion-board' ) ); ?>
	<div class="ctdb-outer-wrap">
		<div class="ctdb-inner-wrap">
			<div class="ctdb-about-section">
				<?php printf( '<h3 class="ctdb-about">%s</h3>', esc_html__( 'Thanks for installing Discussion Board. This page is intended to help you get started as smoothly as possible.', 'wp-discussion-board' ) ); ?>
			</div>

			<?php if ( ! defined( 'DB_PRO_VERSION' ) && empty( $license_key ) ) : ?>
				<?php include WPDBD_PLUGIN_DIR . '/templates/admin/free-license-form.php'; ?>
			<?php endif; ?>

			<div class="ctdb-about-section">
				<?php printf( '<h3>%s</h3>', esc_html__( 'Getting started', 'wp-discussion-board' ) ); ?>
				<?php
				printf(
					'<p>%s <em>%s</em></p>',
					esc_html__( 'Discussion Board has automatically created the three essential pages you need. You can click the links below to view these pages if you like - but please note that because you are already logged in, you won\'t see the same content that a new user would.', 'wp-discussion-board' ),
					esc_html__( 'To see the pages from the point of view of a new user, try viewing them in an incognito browser window.', 'wp-discussion-board' )
				);
				?>
				<?php
				printf(
					'<ul><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li></ul>',
					esc_url( get_permalink( (int) $topics_page ) ),
					esc_html__( 'Topics', 'wp-discussion-board' ),
					esc_url( get_permalink( (int) $new_topic_page ) ),
					esc_html__( 'New Topic Form', 'wp-discussion-board' ),
					esc_url( get_permalink( (int) $login_page ) ),
					esc_html__( 'Log In', 'wp-discussion-board' )
				);
				?>

				<?php printf( '<p>%s</p>', esc_html__( 'These pages are all you need for your forum to function - probably the only thing you need to do now is add the pages to your menu so that users can access them.', 'wp-discussion-board' ) ); ?>
				<?php printf( '<p>%s</p>', esc_html__( 'You can add any content you like to these pages but don\'t delete the shortcodes that have been added already - the shortcodes create functional content, like the log-in form.', 'wp-discussion-board' ) ); ?>
				<?php printf( '<h4>%s</h4>', esc_html__( 'Support link', 'wp-discussion-board' ) ); ?>
				<?php
				printf(
					'<ul class="ctdb-support-links"><li><a href="%s" target="_blank">%s</a></li></ul>',
					'https://wpdiscussionboard.com/docs/',
					esc_html__( 'Getting started support article', 'wp-discussion-board' )
				);
				?>
			</div>

			<div class="ctdb-about-section">
				<?php printf( '<h3>%s</h3>', esc_html__( 'FAQs', 'wp-discussion-board' ) ); ?>
				<?php printf( '<p>%s</p>', esc_html__( 'There are a couple of questions that you might need the answer to when you get started:', 'wp-discussion-board' ) ); ?>
				<?php
				printf(
					'<p><strong>%s</strong><br>%s</p><p><strong>%s</strong><br>%s</p>',
					esc_html__( 'I see an error message about invalid post type. What\'s that?', 'wp-discussion-board' ),
					esc_html__( 'In your dashboard, just go to Settings > Permalinks. This will clear the permalinks and validate the topics post type - then everything should be fine.', 'wp-discussion-board' ),
					esc_html__( 'Help, I can\'t seem to log out. Why?', 'wp-discussion-board' ),
					esc_html__( 'We\'ve included a helpful log-in / log-out shortcode to display a log-in / log-out link. Use a plugin like Shortcode Widget and add the [discussion_board_log_in_out] shortcode to your sidebar or other widget area of your choice. The plugin also automatically displays a log-out link under the new topic form.', 'wp-discussion-board' )
				);
				?>

			</div>
			<div class="ctdb-about-section">
				<?php printf( '<h3>%s</h3>', esc_html__( 'Settings', 'wp-discussion-board' ) ); ?>
				<?php printf( '<p>%s</p>', esc_html__( 'You can change the settings by clicking on the Settings link in the Discussion Board menu. There is more information on the links below:', 'wp-discussion-board' ) ); ?>
				<?php
				printf(
					'<ul class="ctdb-support-links"><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li></ul>',
					'https://wpdiscussionboard.com/docs/#general-tab',
					esc_html__( 'General settings', 'wp-discussion-board' ),
					'https://wpdiscussionboard.com/docs/#design-tab',
					esc_html__( 'Design settings', 'wp-discussion-board' ),
					'https://wpdiscussionboard.com/docs/#user-tab',
					esc_html__( 'User settings', 'wp-discussion-board' )
				);
				?>

			</div>
			<div class="ctdb-about-section">
				<?php printf( '<h3>%s</h3>', esc_html__( 'Further links', 'wp-discussion-board' ) ); ?>
				<?php printf( '<p>%s</p>', esc_html__( 'If you have any questions, please ask them via the support forum. If you find this plugin useful, please leave it a 5 star review - links below:', 'wp-discussion-board' ) ); ?>
				<?php
				printf(
					'<ul><li><a href="%s" target="_blank">%s</a></li><li><a href="%s" target="_blank">%s</a></li></ul>',
					'https://wordpress.org/support/plugin/wp-discussion-board',
					esc_html__( 'Support forum', 'wp-discussion-board' ),
					'https://wordpress.org/support/plugin/wp-discussion-board/reviews/',
					esc_html__( 'Leave a review', 'wp-discussion-board' )
				);
				?>
			</div>
		</div><!-- .ctdb-inner-wrap -->
		<div class="ctdb-banners">
			<?php if ( ! defined( 'DB_PRO_VERSION' ) ) : ?>
			<div class="ctdb-banner">
				<a target="_blank" href="https://wpdiscussionboard.com/?utm_source=wp_plugin&utm_medium=banner&utm_content=sidebar&utm_campaign=upgrade">
					<img src="<?php echo esc_url( WPDBD_PLUGIN_URL . 'assets/images/discussion-board-banner-ad.png' ); ?>"  alt="">
				</a>
			</div>
			<?php endif; ?>
			<div class="ctdb-banner">
				<a target="_blank" href="https://singularitytheme.com/?utm_source=wp_plugin&utm_medium=banner&utm_content=sidebar&utm_campaign=singularity">
					<img src="<?php echo esc_html( WPDBD_PLUGIN_URL . 'assets/images/singularity-banner-ad.png' ); ?>" alt="">
				</a>
			</div>
		</div>
	</div><!-- .ctdb-outer-wrap -->
</div><!-- .wrap -->
