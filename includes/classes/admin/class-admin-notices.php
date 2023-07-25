<?php

/**
 * Discussion Board Notices class.
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Admin_Notices
 *
 * @package WPDiscussionBoard
 */
class Admin_Notices extends Admin
{
	/**
	 * Initialize the class and start calling our hooks and filters.
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		add_action('admin_notices', array($this, 'review_plugin_notice'));
		add_action('admin_notices', array($this, 'pro_notice'));
		add_action('admin_notices', array($this, 'review_request'));
		add_action('wp_ajax_dismiss_notice', array($this, 'dismiss_notice_callback'));
	}

	/**
	 * Request a review.
	 *
	 * @since 1.6.0
	 */
	public function review_request()
	{
		// Check if notice has already been dismissed.
		$dismissed = get_option('ctdb_review_notice_dismissed');

		if (false === $dismissed) {
			// If the notice hasn't already been dismissed, check whether to show it.
			$count_topics = wp_count_posts('discussion-topics')->publish;

			if ($count_topics > 25) : ?>
				<div class="notice notice-info is-dismissible" data-notice="review_notice">
					<?php printf('<h4>%s</h4>', esc_html__('Discussion Board', 'wp-discussion-board')); ?>
					<?php printf('<p>%s</p>', esc_html__('Hi. It looks like you have been using Discussion Board for a while now. If you are finding it useful I\'d really appreciate it if you could give it a 5 star rating on the WordPress repository. Positive reviews and feedback help me to continue developing the plugin.', 'wp-discussion-board')); ?>
					<?php
					printf(
						'<p>%s</p>',
						sprintf(
							// translators: 1. URL to support page.
							wp_kses_post(__('Don\'t forget: if you have any questions or problems regarding the plugin, please <a href="%s" target="_blank">contact me directly <span class="dashicons dashicons-external"></span></a> and I will get straight back to you.', 'wp-discussion-board')),
							'https://wpdiscussionboard.com/support/'
						)
					);
					?>
					<?php printf('<p>%s</p>', esc_html__('Many thanks for your support.', 'wp-discussion-board')); ?>
					<?php printf('<p><em>%s</em></p>', esc_html__('Matt, Discussion Board Maintainer.', 'wp-discussion-board')); ?>

					<?php
					printf(
						'<p><a class="button button-primary" href="%s">%s</a> <a class="dismiss-text button button-secondary" data-notice="review_notice" href="#">%s</a></p>',
						'https://wordpress.org/support/plugin/wp-discussion-board/reviews/',
						esc_html__('Leave a review', 'wp-discussion-board'),
						esc_html__('No thanks', 'wp-discussion-board')
					);
					?>
				</div>
				<script>
					jQuery(document).ready(function($) {
						$('body').on('click', '.notice-dismiss', function() {
							var notice = $(this).parent().attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': notice,
								'security': <?php echo wp_json_encode(wp_create_nonce('dismiss_notice')); ?>
							}
							$.post(ajaxurl, data);
						});
						$('body').on('click', '.dismiss-text', function() {
							var notice = $(this).attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': notice,
								'security': <?php echo wp_json_encode(wp_create_nonce('dismiss_notice')); ?>
							}
							$.post(ajaxurl, data);
							$(this).parent().parent().fadeOut();
						});
					});
				</script>
			<?php
			endif;
		}
	}

	/**
	 * Admin Pro notices.
	 *
	 * @since 1.1.0
	 */
	public function pro_notice()
	{
		if (defined('DB_PRO_VERSION')) {
			return;
		}

		// Check if notice has already been dismissed.
		$dismissed = get_option('ctdb_pro_notice_dismissed');

		if (false === $dismissed) {
			// If the notice hasn't already been dismissed, check whether to show it.
			$count_topics = wp_count_posts('discussion-topics')->publish;

			if ($count_topics > 50) :
			?>
				<div class="notice notice-info is-dismissible" data-notice="pro_notice">
					<?php printf('<h4>%s</h4>', esc_html__('Discussion Board Pro - Save 20%', 'wp-discussion-board')); ?>
					<?php printf('<p>%s</p>', esc_html__('Hi. Thanks for using Discussion Board - I hope it\'s helping you run a successful forum. I just wanted to remind you about the Pro version, which features multiple boards for creating sub-forums, categories and tags, topic following, user profiles, WYSIWYG editing, image uploads, and more.', 'wp-discussion-board')); ?>
					<?php printf('<p>%s</p>', esc_html__('You can get 20% off the cost of the Pro version - just use the discount code UPGRADE on the checkout page. Click the button below to find out more.', 'wp-discussion-board')); ?>
					<?php printf('<p>%s</p>', esc_html__('Thanks for using my plugin.', 'wp-discussion-board')); ?>
					<?php printf('<p><em>%s</em></p>', esc_html__('Matt, Discussion Board Maintainer.', 'wp-discussion-board')); ?>
					<?php
					printf(
						'<p><a class="button button-primary" href="%s">%s</a> <a class="dismiss-text button button-secondary" data-notice="pro_notice" href="#">%s</a></p>',
						'https://wpdiscussionboard.com/?utm_source=wp_plugin&utm_medium=notice&utm_content=usage_topics&utm_campaign=upgrade',
						esc_html__('Find out more', 'wp-discussion-board'),
						esc_html__('No thanks', 'wp-discussion-board')
					);
					?>
				</div>
				<script>
					jQuery(document).ready(function($) {
						$('body').on('click', '.notice-dismiss', function() {
							var notice = $(this).parent().attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': 'pro_notice',
								'security': <?php echo wp_json_encode(wp_create_nonce('dismiss_notice')); ?>
							}
							$.post(ajaxurl, data);
						});
						$('body').on('click', '.dismiss-text', function() {
							var notice = $(this).attr('data-notice');
							var data = {
								'action': 'dismiss_notice',
								'notice': 'pro_notice',
								'security': <?php echo wp_json_encode(wp_create_nonce('dismiss_notice')); ?>
							}
							$.post(ajaxurl, data);
							$(this).parent().parent().fadeOut();
						});
					});
				</script>
		<?php
			endif;
		}
	}

	/**
	 * Dismiss notices.
	 *
	 * @since 1.1.0
	 */
	public function dismiss_notice_callback()
	{
		check_ajax_referer('dismiss_notice', 'security');
		$notice = sanitize_text_field(wp_unslash($_POST['notice']));
		$option = 'ctdb_' . $notice . '_dismissed';
		update_option($option, 1);
	}

	/**
	 * Display one-time admin notice to review plugin at least 7 days after installation
	 */
	public function review_plugin_notice()
	{
		if (!current_user_can('manage_options')) return;

		// Check if notice has already been dismissed.
		$dismissed = get_option('ctdb_review_plugin_notice_dismissed');

		if ($dismissed == 1) return;

		$install_date = get_option('ctdb_install_date', '');

		if (empty($install_date)) return;

		$diff = round((time() - strtotime($install_date)) / 24 / 60 / 60);

		if ($diff < 7) return;

		$review_url = 'https://wordpress.org/support/plugin/wp-discussion-board/reviews/?filter=5#new-post';

		$dismiss_url = "javascript:void(0);"; //esc_url_raw(add_query_arg('wpdb_admin_action', 'dismiss_leave_review_forever'));

		$notice = sprintf(
			__('Hey, I noticed you have been using Discussion Board for a while now - that\'s awesome! Could you please do me a BIG favor and give it a %1$s5-star rating on WordPress?%2$s This will help us spread the word and boost our motivation - thanks!', 'wp-discussion-board'),
			'<a href="' . $review_url . '" target="_blank">',
			'</a>'
		);
		$label  = __('Sure! I\'d love to give a review', 'wp-discussion-board');

		$dismiss_label = __('Dismiss', 'wp-discussion-board');

		$notice .= "<div style=\"margin:10px 0 0;\"><a href=\"$review_url\" target='_blank' class=\"button-primary\">$label</a></div>";
		$notice .= "<div style=\"margin:10px 0 0;\"><a class=\"dismiss-text\" href=\"$dismiss_url\">$dismiss_label</a></div>";

		echo '<div data-dismissible="wpdb-review-plugin-notice-forever" class="update-nag notice notice-warning is-dismissible">';
		echo "<p>$notice</p>";
		echo '</div>';
		?>
		<script>
			jQuery(document).ready(function($) {
				$('body').on('click', '.notice-dismiss', function() {
					var notice = $(this).parent().attr('data-notice');
					var data = {
						'action': 'dismiss_notice',
						'notice': 'review_plugin_notice',
						'security': <?php echo wp_json_encode(wp_create_nonce('dismiss_notice')); ?>
					}
					$.post(ajaxurl, data);
				});
				$('body').on('click', '.dismiss-text', function() {
					var notice = $(this).attr('data-notice');
					var data = {
						'action': 'dismiss_notice',
						'notice': 'review_plugin_notice',
						'security': <?php echo wp_json_encode(wp_create_nonce('dismiss_notice')); ?>
					}
					$.post(ajaxurl, data);
					$(this).parent().parent().fadeOut();
				});
			});
		</script>
<?php
	}
}
