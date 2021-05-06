<?php
/**
 *
 */

// Get the page IDs
$options        = get_option( 'ctdb_options_settings' );
$new_topic_page = $options['new_topic_page'];
$topics_page    = $options['discussion_topics_page'];
$login_page     = $options['frontend_login_page'];
?>

<div class="wrap about-wrap">
	<?php printf( '<h1>%s</h1>', __( 'Welcome to Discussion Board', 'wp-discussion-board' ) ); ?>
	<div class="ctdb-outer-wrap">
		<div class="ctdb-inner-wrap">
			<div class="ctdb-about-section">
				<?php printf( '<h3 class="ctdb-about">%s</h3>', __( 'Thanks for installing Discussion Board. This page is intended to help you get started as smoothly as possible.', 'wp-discussion-board' ) ); ?>
			</div>

			<div class="ctdb-about-section cta">
				<h3><?php esc_html_e( 'Free License', 'wp-discussion-board' ); ?></h3>
				<p><?php esc_html_e( "You're currently using the free version of WP Discussion Board. To register a free license for the plugin, please fill in your email below. This is not required but helps us support you better.", 'wp-discussion-board' ); ?></p>
				<input type="text" name="email" placeholder="<?php esc_attr_e( 'Email Address', 'wp-discussion-board' ); ?>" />
				<input type="submit" value="Register Free License" class="button button-primary" />
				<p><?php esc_html_e( 'Your email is secure with us! We will only spend you a single email with helpful resources to get you started.', 'wp-discussion-board' ); ?></p>

			</div>

			<div class="ctdb-about-section">
				<?php printf( '<h3>%s</h3>', __( 'Getting started', 'wp-discussion-board' ) ); ?>
				<?php printf(
					'<p>%s <em>%s</em></p>',
					__( 'Discussion Board has automatically created the three essential pages you need. You can click the links below to view these pages if you like - but please note that because you are already logged in, you won\'t see the same content that a new user would.', 'wp-discussion-board' ),
					__( 'To see the pages from the point of view of a new user, try viewing them in an incognito browser window.', 'wp-discussion-board' )
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
					'https://wpdiscussionboard.com/docs/',
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
					'https://wpdiscussionboard.com/docs/#general-tab',
					__( 'General settings', 'wp-discussion-board' ),
					'https://wpdiscussionboard.com/docs/#design-tab',
					__( 'Design settings', 'wp-discussion-board' ),
					'https://wpdiscussionboard.com/docs/#user-tab',
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
				<a target="_blank"
				   href="https://wpdiscussionboard.com/?utm_source=plugin_ad&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=dbpro">
					<img src="<?php echo DB_PLUGIN_URL . 'assets/images/discussion-board-banner-ad.png'; ?>"
					     alt="">
				</a>
			</div>
			<div class="ctdb-banner">
				<a target="_blank"
				   href="https://singularitytheme.com/?utm_source=plugin_ad&utm_medium=wp_plugin&utm_content=ctdb&utm_campaign=singularity"><img
						src="<?php echo DB_PLUGIN_URL . 'assets/images/singularity-banner-ad.png'; ?>"
						alt=""></a>
			</div>
		</div>
	</div><!-- .ctdb-outer-wrap -->
</div><!-- .wrap -->