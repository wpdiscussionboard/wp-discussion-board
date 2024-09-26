<?php
/*
 * Discussion Board Front End class
*/
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}
/**
 * Plugin public class
 **/
if (!class_exists('CT_DB_Front_End')) {

	class CT_DB_Front_End
	{

		public $user_can_view = false;
		public $user_can_post = false;
		public $access_restricted_message = '';

		public function __construct()
		{
			//
		}

		/*
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		 */
		public function init()
		{

			add_action('wp', array($this, 'check_user_permission'));

			add_action('wp_head', array($this, 'add_styles'));

			add_filter('the_content', array($this, 'filter_single_content'));

			// If the user can view but can't post, filter off the comment fields
			add_filter('get_comment_text', array($this, 'oembed_comments_filter'), 1, 1);
			add_filter('comments_template', array($this, 'filter_comments'));
			add_filter('comment_form_defaults', array($this, 'remove_comment_form_defaults'));
			add_filter('comment_form_default_fields', array($this, 'remove_comment_form_fields'));
			add_filter('comment_reply_link', array($this, 'custom_reply_link'));
			add_filter('ctdb_filter_archive_content_end', array($this, 'display_categories_standard_layout'), 30);
			add_filter('ctdb_topics_form_before_submit', array($this, 'check_and_add_recaptcha'), 20, 1);

			add_shortcode('discussion_board_form', array($this, 'display_new_topic_form'));
			add_shortcode('discussion_topics', array($this, 'display_all_topics'), 10, 2);
			add_shortcode('recent_discussion_topics', array($this, 'display_recent_topics'));
			add_shortcode('discussion_board_log_in_out', array($this, 'return_logout_url'), 10, 2);
			add_shortcode('is_logged_in', array($this, 'is_logged_in_shortcode'), 10, 2);
			add_shortcode('not_logged_in', array($this, 'not_logged_in_shortcode'), 10, 2);
			add_shortcode('new_topic_button', array($this, 'new_topic_button_shortcode'));
		}

		public function check_user_permission()
		{
			$this->user_can_view = ctdb_is_user_permitted();
			$this->user_can_post = ctdb_is_posting_permitted();
			$this->access_restricted_message = $this->return_access_restricted_message();
		}

		/*
		 * Add Customizer styles into the header
		 * @since 2.1.0
		 */
		public function add_styles()
		{
			$settings = wpdbd_customizer_settings();
			$styles = array();

			$options = get_option('ctdb_design_settings');
			// Iterate through design settings that can be updated in Customizer
			if (!empty($settings)) {
				foreach ($settings as $setting) {
					// If the setting has a value and it has a style declaration
					if (isset($options[$setting['id']]) && isset($setting['declaration'])) {
						// Create a new rule for the element
						$newrule = $setting['element'] . ' {';
						$newrule .= $setting['declaration'] . ':' . $options[$setting['id']];
						$newrule .= '}';
						$styles[] = $newrule;
					}
				}
			}

			if (!empty($styles)) {
				echo '<style type="text/css" id="ctdb-custom-styles">';
				echo join(' ', $styles);
				echo '</style>';
			}
		}

		/*
		 * Create shortcode for new topic form
		 * Inserts new topic post
		 * @since 1.0.0
		 */
		public function display_new_topic_form()
		{
			$has_duplicate = false;
			// Check if the form was submitted
			if ('POST' == $_SERVER['REQUEST_METHOD'] && !empty($_POST['insert_post'])) {
				// Verify the nonce
				if (!wp_verify_nonce($_POST['new_topic'], 'display_new_topic_form')) {
					return;
				}

				//before we check from third party filter for recaptcha
				if (isset($_POST['g-recaptcha-response'])) {
					if (empty($_POST['g-recaptcha-response'])) {
						$output = '<div class="ctdb-errors">';
						$output .= '<ul>';
						$output .= '<li>Your new topic post failed the reCaptcha check.</li>';
						$output .= '</ul></div>';
						$output .= $this->new_topic_form_content();
						return $output;
					}

					$is_valid = apply_filters('discussion_board_validate_recaptcha', true, $_POST['g-recaptcha-response']);
					if (!$is_valid) {
						// reCaptcha failed
						// Maybe write a kind note here?
						$output = '<div class="ctdb-errors">';
						$output .= '<ul>';
						$output .= '<li>Your new topic post failed the reCaptcha check.</li>';
						$output .= '</ul></div>';
						$output .= $this->new_topic_form_content();
						return $output;
					}
				}

				// Prevent duplication
				$args = array(
					'post_type'				=> 'discussion-topics',
					'posts_per_page'	=> 9999,
					'meta_key'				=> 'ctdb_uniqid',
					'meta_value'			=> sanitize_text_field($_POST['ctdb_uniqid']),
					'fields'					=> 'ids'
				);
				$duplicates = new WP_Query($args);
				if ($duplicates->posts) {
					// Duplicate found
					$has_duplicate = true;
					$dupes = $duplicates->posts;
					$my_post_id = $dupes[0];
				}

				// Just in case there are any errors
				$errors = array();

				$error_params = array(
					'topic_title'	=> array(
						'id'			=> 'topic_title', // The form element we're evaluating
						'check'			=> '', // The value that will throw the error
						'response'		=> __('Please add a title.', 'wp-discussion-board'), // The error message if the element is empty
						'topic_element'	=> 'title' // The element of the $post_args to be populated with this element's value
					),
					'topic_content'	=> array(
						'id'			=> 'topic_content', // The form element we're evaluating
						'check'			=> '', // The value that will throw the error
						'response'		=> __('Please add some content to the topic.', 'wp-discussion-board'), // The error message if the element is empty
						'topic_element'	=> 'content' // The element of the $post_args to be populated with this element's value
					)
				);

				$error_params = apply_filters('ctdb_new_topic_form_validation', $error_params);

				// Add new content into this array once it's passed validation
				$new_content = array();

				foreach ($error_params as $error_param) {
					if (isset($_POST[$error_param['id']]) && $_POST[$error_param['id']] == $error_param['check']) {
						$errors[] = $error_param['response'];
					}
				}
				if (count($errors) != 0) {
					// If we've found errors, let the user know
					$intro = __('We found', 'wp-discussion-board');
					$problems = _n('an item missing:', 'some items missing:', count($errors), 'wp-discussion-board');
					$output = '<div class="ctdb-errors">' . $intro . ' ' . $problems;
					$output .= '<ul>';
					// We found an error so display the form
					foreach ($errors as $error) {
						$output .= '<li>' . $error . '</li>';
					}
					$output .= '</ul></div>';
					$output .= $this->new_topic_form_content();
				} else {
					$output = '';
					// No errors so create the new topic
					$options = get_option('ctdb_options_settings');
					if (isset($options['new_topic_status'])) {
						$status = 'publish';
					} else {
						$status = 'draft';
					}

					$allowed_tags = wp_kses_allowed_html('post');
					unset($allowed_tags['a']);

					$post_args = array(
						'post_title'		=> wp_strip_all_tags($_POST['topic_title']),
						'post_content'		=> strip_shortcodes(wp_kses($_POST['topic_content'], $allowed_tags)), //strip_shortcodes(sanitize_textarea_field(strip_tags($_POST['topic_content'], $allowed_tags))),
						'post_status'		=> $status,
						'post_type' 		=> 'discussion-topics',
						'comment_status'	=> 'open', // Forces the comment status to open - otherwise no one can reply
					);

					// Check for taxonomy
					$post_args = apply_filters('ctdb_post_args', $post_args);

					if (!$has_duplicate) {
						// Create the new topic
						$my_post_id = wp_insert_post($post_args);

						// Filter the new topic ID. Not sure we need this.
						$my_post_id = apply_filters('ctdb_post_id', $my_post_id);

						// Save the uniqid value to prevent duplicate topics
						update_post_meta($my_post_id, 'ctdb_uniqid', sanitize_text_field($_POST['ctdb_uniqid']));

						// Do stuff with the topic ID
						// @hooked CT_DB_Pro_Categories::create_new_term
						do_action('ctdb_new_topic_id', $my_post_id);
					}


					if ($my_post_id !== false) {
						// Display success message
						if ($status == 'publish') {
							$output .= '<p>' . stripslashes(wp_strip_all_tags($_POST['topic_title'])) . __(' successfully added. View it at:', 'wp-discussion-board') . '<br>';
							$url = get_permalink($my_post_id);
							$output .= '<a href="' . esc_url($url) . '">' . $url . '</a></p>';
						} else {
							$message = isset($options) && isset($options['new_topic_message_after_submission']) && !empty($options['new_topic_message_after_submission']) ? $options['new_topic_message_after_submission'] : 'Your topic has been received and is currently under review. We appreciate your contribution!';
							$output .= '<p>' . __(sanitize_text_field($message), 'wp-discussion-board') . '</p>';
						}
					}
				}
			} else {
				// Check for time limit to prevent multiple quick posts
				$options = get_option('ctdb_options_settings');
				$delay = $options['new_post_delay'];
				$user = wp_get_current_user();
				// Get the time of the user's last topic
				$last_post = get_posts(
					array(
						'author'		=> $user->ID,
						'orderby'		=> 'date',
						'post_type'		=> 'discussion-topics',
						'numberposts'	=> 1
					)
				);
				if ($last_post) {
					$last_post = $last_post[0];
					$last_post_time = get_post_time('U', true, $last_post->ID);
				} else {
					$last_post_time = 0;
				}
				$time_now = date('U');
				// We can set delay to 0 to allow quick reposting
				// Gets round time-based errors with dodgy plugins
				if (absint($time_now) - absint($last_post_time) < $delay && $delay != 0) {
					// User isn't permitted to post another topic yet
					if ($delay > 60) {
						$minutes = $delay / 60 . __(' minutes', 'wp-discussion-board');
					} else {
						$minutes = __('a minute', 'wp-discussion-board');;
					}
					$output = '<p>' . __('You can\'t post a new topic just yet. Please come back in ') . $minutes . '.</p>';
				} else if ($this->user_can_post) {
					// User is permitted to post another topic
					$output = $this->new_topic_form_content();
				} else {
					// User can't view
					$output = $this->access_restricted_message;
				}
			}

			$output = apply_filters('ctdb_topic_form', $output);

			return $output;
		}

		/*
		 * This is the HTML for the new topic submission form
		 * @since 1.0.0
		 */
		public function new_topic_form_content()
		{
			// Check if any content has already been entered
			$title = '';
			if (isset($_POST['topic_title'])) {
				$title = wp_strip_all_tags(stripslashes($_POST['topic_title']));
			}
			$content = '';
			if (isset($_POST['topic_content'])) {
				$content = strip_shortcodes(stripslashes($_POST['topic_content']));
			}
			// Check if include categories option is selected
			$term_select = '';
			$options = get_option('ctdb_options_settings');
			// Create the form
			$form = array();
			// Hook here to check role capabilities in CT_DB_Pro_Uploader
			do_action('ctdb_start_new_topic_form');

			$form['new_topic_message'] = ctdb_get_new_topic_message();

			$form['open_wrap'] = '<div id="ctdb-new-topic">';
			$form['open_form'] = '<form id="new-topic" name="new-topic" method="post" action="">';
			$form['fields'] = array();

			// Apply filter before title field
			$form = apply_filters('ctdb_topics_form_before_title', $form);

			$form['fields']['title'] = '<input class="required" type="text" id="topic_title" value="' . esc_attr($title) . '" tabindex="900" name="topic_title" placeholder="' . esc_attr(__('Topic Title', 'wp-discussion-board')) . '" />';
			$form['fields']['content'] = '<textarea id="topic_content" name="topic_content" cols="80" rows="20" tabindex="901" placeholder="' . esc_attr(__('Topic Content', 'wp-discussion-board')) . '">' . wp_kses_post($content) . '</textarea>';

			// Apply filter before submit button
			$form = apply_filters('ctdb_topics_form_before_submit', $form);

			$form['submit'] = '<input type="submit" value="' . __('Start Topic', 'wp-discussion-board') . '" tabindex="903" id="submit_topic" name="submit_topic" />';
			$form['insert_post'] = '<input type="hidden" name="insert_post" value="post" />';
			$form['nonce'] = wp_nonce_field("display_new_topic_form", "new_topic", true, false);
			$form['close'] = '<input type="hidden" name="ctdb_uniqid" id="ctdb_uniqid" value="' . uniqid() . '"></form></div>';
			$form['logout'] = $this->return_logout_url();
			$form = apply_filters('ctdb_topics_form', $form);
			// Assemble the form
			$form_output = $form['new_topic_message'];
			$form_output .= $form['open_wrap'];
			$form_output .= $form['open_form'];
			if (is_array($form['fields'])) {
				foreach ($form['fields'] as $field) {
					$form_output .= $field;
				}
			}

			if (isset($form['recaptcha'])) {
				$form_output .= $form['recaptcha'];
			}

			$form_output .= $form['submit'];
			$form_output .= $form['insert_post'];
			$form_output .= $form['nonce'];
			$form_output .= $form['close'];
			//$form_output .= $form['logout'];
			return $form_output;
		}


		/*
		 * Filter the content
		 * @since 1.0.0
		 */
		public function filter_single_content($content)
		{

			// If we're on a single page for the discussion-topic post type and we don't have access
			if ((is_single() || is_archive()) && 'discussion-topics' == get_post_type() && is_main_query()) {
				if (!$this->user_can_view) {
					$content = $this->access_restricted_message;
				} else {
					// Check to see if the information bar is included
					$options = get_option('ctdb_design_settings');
					$position = $options['information_bar'];
					$words = intval($options['number_words']);
					// Truncate content if archive page
					if ($words > 0 && is_archive()) {
						$content = '<p>' . wp_trim_words($content, $words) . '</p>';
						$content .= sprintf(
							'<p><a class="ctdb-view-topic" href="%s">%s</a></p>',
							get_permalink(),
							__('View Topic', 'discussion-tab')
						);
					}

					/*
					 * @hooked CT_DB_Skins::filter_single_content										Add meta data fields and author data	Priority 50
					 * @hooked CT_DB_Pro_Categories::display_categories_standard_layout 				Add categories							Priority 30
					 * @hooked CT_DB_Notifications::add_opt_out_field									Add author notification opt out field	Priority 20
					 * Higher priorities appear first as we're filtering the_content and adding additional content at the start of $content
					 */
					$content = apply_filters('ctdb_filter_single_content_end', $content, $position);
				}
			}

			return $content;
		}

		/**
		 * Enable OEmbeds in comments.
		 *
		 * @param string $comment_text The comment text.
		 *
		 * @credit https://gist.github.com/sheabunge/6018753
		 *
		 * @since 2.4.2
		 *
		 * @return string
		 */
		public function oembed_comments_filter($comment_text)
		{
			global $wp_embed;

			// Only for discussion topics, not all comments.
			if (!is_singular('discussion-topics')) {
				return $comment_text;
			}

			// Automatic discovery would be a security risk, safety first.
			add_filter('embed_oembed_discover', '__return_false', 999);

			$comment_text = $wp_embed->autoembed($comment_text);

			// Replace line breaks within HTML tags with placeholders
			$comment_text = wp_replace_in_html_tags($comment_text, array("\n" => '<!-- wp-line-break -->'));

			// Handle <span> tags with URLs using custom processing if needed
			if (preg_match('#<span([^>]*)>(https?://[^\s<>"]+)<\/span>#i', $comment_text)) {
				$comment_text = preg_replace_callback('|<span([^>]*)>(https?://[^\s<>"]+)<\/span>|i', array($this, 'autoembed_span_url_callback'), $comment_text);
			}

			if (has_shortcode($comment_text, 'embed')) {
				$comment_text = $GLOBALS['wp_embed']->run_shortcode($comment_text);
			}

			// Restore line breaks within HTML tags
			$comment_text = str_replace('<!-- wp-line-break -->', "\n", $comment_text);

			// But don't break your posts if you use it.
			remove_filter('embed_oembed_discover', '__return_false', 999);

			return $comment_text;
		}

		/*
		 * Hide comment form if user doesn't have access
		 * @since 1.0.0
		 */
		public function filter_comments($comment_template)
		{
			// If the user doesn't have access to view, return an empty file for the comments template
			if (is_single() && 'discussion-topics' == get_post_type() && !$this->user_can_view) {
				$comment_template = ctdb_get_comments_file_path();
			}
			return $comment_template;
		}

		/*
		 * Remove comment form title
		 * @since 1.0.0
		 */
		public function remove_comment_form_defaults($fields)
		{
			// If the user doesn't have access to post, remove the comment textarea
			if (is_single() && 'discussion-topics' == get_post_type() && !$this->user_can_post) {
				$fields = array(
					'comment_notes_before'		=> '',
					'title_reply'				=> '',
				);
			}
			return $fields;
		}

		/*
		 * Remove comment form fields if user can't post
		 * @since 1.0.0
		 */
		public function remove_comment_form_fields($fields)
		{

			// If the user doesn't have access to post, remove the comment textarea
			if (is_single() && 'discussion-topics' == get_post_type() && !$this->user_can_post) {

				$fields = array(
					'author'	=> '',
					'email'		=> '',
					'url'		=> ''
				);

				// Remove the comment textarea too
				add_filter('comment_form_field_comment', array($this, 'remove_comment_form_textarea'), 1);
				// Remove submit button
				add_filter('comment_form_submit_field', array($this, 'remove_comment_form_submit'));
			}
			return $fields;
		}

		/*
		 * Remove comment form textarea if user can't post
		 * @since 1.0.0
		 */
		public function custom_reply_link($content)
		{

			// If the user doesn't have access to post, remove the comment textarea
			if (is_single() && 'discussion-topics' == get_post_type() && !$this->user_can_post) {
				$content = '';
			}

			return $content;
		}

		/*
		 * Remove comment form textarea if user can't post
		 * @since 1.0.0
		 */
		public function remove_comment_form_textarea($comment_field)
		{

			// If the user doesn't have access to post, remove the comment textarea
			if (is_single() && 'discussion-topics' == get_post_type() && !$this->user_can_post) {

				$comment_field = '';
			}
			return $comment_field;
		}

		/*
		 * Remove comment form textarea if user can't post
		 * @since 1.0.0
		 */
		public function remove_comment_form_submit($submit_field)
		{

			// If the user doesn't have access to post, remove the submit button
			if (is_single() && 'discussion-topics' == get_post_type() && !$this->user_can_post) {

				apply_filters('comment_form_submit_button', '', array());
			}
		}

		/**
		 * Create shortcode to display all topics
		 * $class allows us to add class to ctdb-no-topics-message element
		 * @since 1.0.0
		 */
		public function display_all_topics($atts, $class = '')
		{

			$options = get_option('ctdb_design_settings');
			$topics_per_page = 10;
			if (isset($options['number_topics'])) {
				$topics_per_page = $options['number_topics'];
			}

			// We can filter our attributes here
			$atts = apply_filters('ctdb_discussion_topics_shortcode_atts', $atts);

			extract(shortcode_atts(array(
				'orderby'		=> 'date',
				'order'			=> 'DESC',
				'cols'			=> 'avatar,topic,replies,started',
				'number'		=> $topics_per_page,
				'show_nav'		=> true
			), $atts));

			$output = '';

			if (empty($cols)) {
				$cols = array('avatar', 'topic', 'replies', 'started');
			} else {
				$cols = explode(',', str_replace(' ', '', $cols));
			}

			global $CT_DB_Public;

			if (ctdb_is_user_permitted() == true) {

				// Only display topics if number of topics is greater than 0
				if ($number > 0) {
					// Get the layout style
					// Return standard layout
					// @updated 1.7.0 for new Style setting
					if (isset($options['layout'])) {
						$layout = $options['layout'];
					} else if (!isset($options['archive_layout']) || $options['archive_layout'] == 'standard') {
						$layout = 'standard';
					} else {
						$layout = $options['archive_layout'];
					}

					/*
					 * Updated @1.5.1
					 * Fixed pagination on static home page
					 */
					global $paged;

					if (get_query_var('paged')) {
						$paged = get_query_var('paged');
					} else if (get_query_var('page')) {
						$paged = get_query_var('page');
					} else {
						$paged = 1;
					}
					//	$page = get_query_var( 'page' ) ? get_query_var( 'page' ) : 1;
					$offset = ($paged - 1) * $topics_per_page;

					$args = array(
						'post_type'			=>	'discussion-topics',
						'paged'				=>	$paged,
						'posts_per_page'	=>	$number,
						'offset'			=>	$offset
					);

					if (isset($orderby)) {
						$permitted_params = array('date', 'title', 'author', 'modified', 'rand', 'comment_count');
						$permitted_params = apply_filters('ctdb_discussion_topics_shortcode_orderby', $permitted_params);
						if (in_array($orderby, $permitted_params)) {
							// Only specify this param if it's in our list of permitted params
							$args['orderby'] = esc_attr($orderby);
						}
					}

					if (isset($order)) {
						$args['order'] = esc_attr($order);
					}

					// We can filter our arguments here
					$args = apply_filters('ctdb_discussion_topics_shortcode_args', $args, $atts);

					$topics = new WP_Query($args);

					if ($topics->have_posts()) {

						if ($layout == 'standard') {
							$output = $this->return_standard_layout($topics);
						} else {
							$output = $this->return_table_layout($topics, $cols);
						}

						if (isset($show_nav) && $show_nav == true) {
							$output .= '<ul class="ctdb-pagination"><li class="prev">' . get_previous_posts_link('<< Previous', $topics->max_num_pages) . '</li><li class="next">' . get_next_posts_link('Next >>', $topics->max_num_pages) . '</li></ul>';
						}
					} else {

						// No topics found
						$output .= '<div class="ctdb-no-topics-message ' . $class . '">';
						$output .= __('There are no topics posted yet.', 'wp-discussion-board');
						$output .= '</div>';
					}

					wp_reset_query();
				}
			} else {

				// User can't view
				$output .= $this->access_restricted_message;
			}

			$output = apply_filters('ctdb_discussion_topics_end', $output);

			return $output;
		}

		/**
		 * Return topic content for standard shortcode layout
		 * @since 1.4.0
		 */
		public function return_standard_layout($topics)
		{
			$output = '';
			while ($topics->have_posts()) : $topics->the_post();
				$output .= '<div class="ctdb-topic">';
				$url = get_permalink();
				$output .= sprintf(
					'<h2><a href="%1$s">%2$s</a></h2>',
					esc_url($url),
					get_the_title()
				);

				// Excerpt
				$output .= '<div class="ctdb-excerpt">';
				$output .= '<p>' . self::get_custom_excerpt() . '</p>';
				$output .= '<p><a class="ctdb-button" href="' . esc_url($url) . '">' . __('Read More', 'wp-discussion-board') . '</a></p>';
				$output .= '</div><!-- .ctdb-excerpt -->';

				// Comments
				$output .= $this->display_information_bar();

				$output .= '</div><!-- .ctdb-topic -->';

			endwhile;

			return $output;
		}

		/**
		 * Return topic content for table shortcode layout
		 * @since 1.4.0
		 */
		public function return_table_layout($topics, $cols)
		{

			global $post;

			// These are the columns that will appear in our table
			if (empty($cols)) {
				$cols = array('avatar', 'topic', 'replies', 'started');
			}

			$colcount = 0;

			// Column titles: keys should match $cols elements above
			$titles = array(
				'avatar'		=> '&nbsp;',
				'topic'			=> __('Topic', 'wp-discussion-board'),
				'replies' 		=> __('Replies', 'wp-discussion-board'),
				'started'		=> __('Since', 'wp-discussion-board'),
				'posted-by'		=> __('Posted by: ', 'wp-discussion-board'),
				'voices'		=> __('Voices', 'wp-discussion-board')
			);

			// You can filter the titles here
			$titles = apply_filters('ctdb_topic_titles', $titles);

			// @since 1.6.1
			// Build the column titles with this array
			$title_fields = array();
			if (!empty($cols)) {
				$colcount = count($cols);
				foreach ($cols as $col) {
					// Check the col is valid
					if (isset($titles[$col])) {
						$title_fields[$col] = '<li class="ctdb-topic-table-item ctdb-topic-table-' . esc_attr($col) . '">' . $titles[$col] . '</li>';
					}
				}
			}

			// We can filter these fields if needed
			$title_fields = apply_filters('ctdb_topic_title_fields', $title_fields, $cols);

			// Filter the entire output before running code below
			// Allows us to build our own markup
			$output = '';
			$output = apply_filters('ctdb_filter_table_layout', $output, $topics, $titles, $cols);

			// If the output hasn't been pre-filtered, then build the markup
			if (empty($output)) {

				// The HTML for the column titles
				$output .= '<ul class="ctdb-topic-table ctdb-topic-table-col-' . intval($colcount) . '">';
				$output .= '<li class="ctdb-topic-table-header">';
				$output .= '<ul class="ctdb-topic-table-row ctdb-topic-table-header-row">';

				if (!empty($title_fields)) {
					$output .= join('', $title_fields);
				}

				$output .= '</ul><!-- ctdb-topic-table-row -->';
				$output .= '</li><!-- ctdb-topic-table-header -->';

				// Now build the table rows, i.e. the topics
				$output .= '<li class="ctdb-topic-table-body">';

				$body_fields = array();

				// Use this filter to insert your own body fields before the query runs
				// Avoids running the query twice
				$body_fields = apply_filters('ctdb_topic_body_fields_before_query', $body_fields, $topics, $titles, $cols);

				if (empty($body_fields)) {

					while ($topics->have_posts()) : $topics->the_post();

						$body_fields[$post->ID] = '<ul class="ctdb-topic-table-row">';

						if (!empty($cols)) {

							foreach ($cols as $col) {

								if (isset($titles[$col])) {

									$body_fields[$post->ID] .= '<li class="ctdb-topic-table-item ctdb-topic-table-' . esc_attr($col) . '">';
									// Get some metafields for the topic
									// Number of comments
									$comments_number = get_comments_number();
									// Start time
									$started_time = '<time class="comment-timeago" datetime="' . date('c', strtotime(get_the_date() . ' ' . get_the_time())) . '">' . get_the_date() . '</time>';
									// Voices
									$comments = get_comments($post);
									$CT_DB_Skins = new CT_DB_Skins();
									$voices = $CT_DB_Skins->count_voices($comments);

									if ($col == 'avatar') {
										$body_fields[$post->ID] .= get_avatar(get_the_author_meta('ID'), 48);
									} else if ($col == 'topic') {
										$url = get_permalink();
										$body_fields[$post->ID] .= sprintf(
											'<p><a href="%1$s">%2$s</a></p>',
											esc_url($url),
											get_the_title()
										);

										// Excerpt
										$body_fields[$post->ID] .= '<div class="ctdb-excerpt">';
										$body_fields[$post->ID] .= '<p>' . self::get_custom_excerpt() . '</p>';
										$body_fields[$post->ID] .= '<p><a class="ctdb-button" href="' . esc_url($url) . '">' . __('Read More', 'wp-discussion-board') . '</a></p>';
										$body_fields[$post->ID] .= '</div><!-- .ctdb-excerpt -->';

										$author = get_the_author();
										$author = apply_filters('ctdb_author_name', $author);
										$body_fields[$post->ID] .= '<span class="ctdb-topic-table-posted-by">' . $titles['posted-by'] . $author . '</span>';
										// We output hidden metafields here, displayed on mobile

										$body_fields[$post->ID] .= '<div class="ctdb-topic-mobile-metafields">';
										if (in_array('replies', $cols)) {
											$body_fields[$post->ID] .= '<div>';
											$body_fields[$post->ID] .= __('Replies', 'wp-discussion-board');
											$body_fields[$post->ID] .= ': ' . $comments_number;
											$body_fields[$post->ID] .= '</div>';
										}
										if (in_array('started', $cols)) {
											$body_fields[$post->ID] .= '<div>';
											$body_fields[$post->ID] .= __('Started', 'wp-discussion-board');
											$body_fields[$post->ID] .= ': ' . $started_time;
											$body_fields[$post->ID] .= '</div>';
										}
										if (in_array('voices', $cols)) {
											$body_fields[$post->ID] .= '<div>';
											$body_fields[$post->ID] .= __('Voices', 'wp-discussion-board');
											$body_fields[$post->ID] .= ': ' . absint($voices);
											$body_fields[$post->ID] .= '</div>';
										}
										$mobile_body_fields = '';
										$mobile_body_fields = apply_filters('ctdb_mobile_body_fields', $mobile_body_fields, $post->ID, $cols);
										$body_fields[$post->ID] .= $mobile_body_fields;
										$body_fields[$post->ID] .= '</div>';
									} else if ($col == 'replies') {
										$body_fields[$post->ID] .= $comments_number;
									} else if ($col == 'started') {
										$body_fields[$post->ID] .= $started_time;
									} else if ($col == 'voices') {
										$body_fields[$post->ID] .= absint($voices);
									}

									// Filter here for additional columns
									$body_fields = apply_filters('ctdb_topic_column', $body_fields, $post->ID, $col);

									$body_fields[$post->ID] .= '</li>';
								}
							}

							$body_fields[$post->ID] .= '</ul><!-- ctdb-topic-table-row -->';
						}

					endwhile;

					$body_fields = apply_filters('ctdb_topic_body_fields', $body_fields, $topics, $titles, $cols);

					if (!empty($body_fields)) {
						$output .= join('', $body_fields);
					}
				}

				$output .= '</li><!-- .ctdb-topic -->';
				$output .= '</ul><!-- ctdb-topic-table-body -->';
			}

			return $output;
		}

		/**
		 * Create shortcode to display recent topics
		 * @since 1.0.0
		 */
		public function display_recent_topics($atts)
		{

			extract(shortcode_atts(array(
				'number'		=>	5,
				'show_excerpt'	=>	false,
				'show_info'		=>	false,
			), $atts));

			$output = '';

			if ($this->user_can_view == true) {

				$options = get_option('ctdb_options_settings');

				// Create the query of topics
				$args = array(
					'post_type'			=>	'discussion-topics',
					'posts_per_page'	=>	$number
				);
				$topics = new WP_Query($args);

				if ($topics->have_posts()) {

					$output .= '<ul class="ctdb-recent-topics">';

					while ($topics->have_posts()) : $topics->the_post();

						$url = get_permalink();

						$output .= '<li>';

						$output .= sprintf(
							'<a href="%1$s">%2$s</a>',
							esc_url($url),
							get_the_title()
						);

						if ($show_excerpt) {
							// Excerpt
							$output .= '<div class="ctdb-excerpt">';
							$output .= '<p>' . get_the_excerpt() . '</p>';
							$output .= '</div><!-- .ctdb-excerpt -->';
						}

						if ($show_info) {
							$output .= $this->display_information_bar();
						}

						$output .= '</li>';

					endwhile;

					$output .= '</ul><!-- .ctdb-recent-topics -->';

					wp_reset_query();
				}
			} else {

				// User can't view
				// Don't display anything

			}

			return $output;
		}

		/**
		 * Display information about the topic - author, date, number comment
		 * @since 1.0.0
		 */
		public function display_information_bar()
		{

			// What layout should we use?
			$design_options = get_option('ctdb_design_settings');
			if (!isset($design_options['archive_layout']) || $design_options['archive_layout'] == 'standard') {
				$output = $this->info_bar_single();
			} else {
				$output = $this->info_bar_table();
			}

			return $output;
		}

		/**
		 * Display information about the topic - author, date, number comment
		 * @since 1.0.0
		 */
		public function info_bar_single()
		{

			global $post;

			// Use icons?
			$show_icons = ctdb_use_icons();

			// Comments
			$output = '<div class="ctdb-information-bar">';
			$output .= '<div class="ctdb-info-bar-row">';
			$output .= '<div class="ctdb-span-3">';
			if ($show_icons) $output .= '<span class="dashicons dashicons-format-chat"></span>';
			$output .= get_comments_number() . _n(' reply', ' replies', get_comments_number(), 'wp-discussion-board');
			$output .= '</div><!-- .ctdb-span-3 -->';

			// Topic author
			$output .= '<div class="ctdb-span-3 ctdb-border-left">';
			if ($show_icons) $output .= '<span class="dashicons dashicons-admin-users"></span>';
			$author = get_the_author();
			$author = apply_filters('ctdb_author_name', $author);
			$output .= $author;
			$output .= '</div><!-- .ctdb-span-3 -->';

			// Topic date
			$output .= '<div class="ctdb-span-3 ctdb-border-left">';
			if ($show_icons) $output .= '<span class="dashicons dashicons-calendar-alt"></span>';
			$output .= get_the_date();
			$output .= '</div><!-- .ctdb-span-3 -->';
			// Check to see if the information bar is included
			$output .= apply_filters('ctdb_filter_archive_content_end', ' ');
			$output .= '</div><!-- .ctdb-info-bar-row -->';
			$output .= '</div><!-- .ctdb-information-bar -->';



			return $output;
		}

		/**
		 * Display information about the topic in table layout
		 * @since 1.5.0
		 */
		public function info_bar_table()
		{

			global $post;

			// Use icons?
			$show_icons = ctdb_use_icons();

			// Comments
			$output = '<div class="ctdb-information-bar">';
			$output .= '<div class="ctdb-info-bar-row">';

			$author = get_the_author();

			$output .= '<div class="ctdb-info-avatar ctdb-info-title">';
			$output .= get_avatar(get_the_author_meta('ID'), 96);
			$output .= '</div><!-- .ctdb-info-avatar -->';

			$output .= '<div class="ctdb-info-meta-wrap">';

			// Topic author
			$output .= '<div class="ctdb-info-meta ctdb-info-author">';
			if ($show_icons) $output .= '<span class="dashicons dashicons-admin-users"></span>';
			$author = apply_filters('ctdb_author_name', $author);
			$output .= $author;
			$output .= '</div>';

			// Topic date
			$output .= '<div class="ctdb-info-meta comment-metadata">';
			if ($show_icons) $output .= '<span class="dashicons dashicons-calendar-alt"></span>';
			$output .= get_the_date() . ' ' . __('at', 'wp-discussion-board') . ' ' . get_the_time();
			$output .= '</div>';

			$output .= '</div><!-- .ctdb-info-meta-wrap -->';
			$output .= '</div><!-- .ctdb-info-bar-row -->';

			$output .= '<div class="ctdb-info-bar-row ctdb-info-has-border">';
			$output .= '<div class="ctdb-info-avatar ctdb-info-title">';
			if ($show_icons) $output .= '<span class="dashicons dashicons-format-chat"></span>';
			$output .= __('Replies', 'wp-discussion-board');
			$output .= '</div>';

			$output .= '<div class="ctdb-info-meta-wrap">';
			$output .= '<div class="ctdb-info-meta ctdb-info-author">';
			$output .= get_comments_number();
			$output .= '</div>';

			$output .= '</div><!-- .ctdb-info-meta-wrap -->';
			$output .= '</div><!-- .ctdb-info-bar-row -->';

			// Filter here to add additional meta information about the topic
			// @since 1.5.0
			$output = apply_filters('ctdb_info_meta_wrap_after_replies', $output);

			$output .= '</div><!-- .ctdb-information-bar -->';

			$output = apply_filters('ctdb_info_bar_table', $output);

			return $output;
		}

		/**
		 * Return restricted message
		 * @since 1.0.0
		 */
		public function return_access_restricted_message()
		{
			$options = get_option('ctdb_options_settings');
			$message = ctdb_get_restricted_message();
			//	if( isset( $options['restricted_message'] ) ) {
			$message = wp_kses(
				$message,
				array(
					'a'			=>	array(
						'href'	=> array(),
						'title'	=> array()
					),
					'p'			=> array(),
					'br'		=> array(),
					'em'		=> array(),
					'strong'	=> array()
				)
			);
			//	}
			if ($message == '') {
				$message = '<p class="ctdb-restricted-message">' . __('Sorry - you\'re not permitted to view this content.', 'wp-discussion-board') . '</p>';
			} else {
				$message = '<div class="ctdb-restricted-message">' . $message . '</div>';
			}

			// Append the log-in form if enabled and the user isn't already logged in
			// Don't display log-in form on archive page
			if (!is_user_logged_in() && !is_archive()) {
				// Show the form unless disabled
				// @since 1.1.0
				$CT_DB_Registration = new CT_DB_Registration();
				if (empty($options['hide_inline_form'])) {
					$message .= $CT_DB_Registration->return_login_registration_form('', '');
				}
			} else if (is_user_logged_in() && !$this->user_can_view) {
				// Return a message to a logged in user who doesn't have permissions
				$message = '<p class="ctdb-restricted-message">' . ctdb_get_restricted_user_message() . '</p>';
			}
			return $message;
		}

		/**
		 * Return the link to log out
		 * @since 1.0.0
		 */
		public function return_logout_url()
		{

			if (is_user_logged_in()) {

				$url = wp_logout_url(get_permalink());

				$logout = sprintf(
					'<p><a href="%1$s" title="%2$s">%3$s</a></p>',
					esc_url($url),
					__('Log out', 'wp-discussion-board'),
					__('Log out', 'wp-discussion-board')
				);

				return $logout;
			} else {

				// See if there's a front-end log-in page to display
				$options = get_option('ctdb_options_settings');

				if ($options['frontend_login_page']) {

					$login = sprintf(
						'<p><a href="%1$s" title="%2$s">%3$s</a></p>',
						esc_url(get_permalink($options['frontend_login_page'])),
						__('Log in', 'wp-discussion-board'),
						__('Log in', 'wp-discussion-board')
					);

					return $login;
				} else {

					// Return nothing
					return;
				}
			}
		}

		/**
		 * Display content to logged in users
		 * @since 1.3.1
		 */
		public function is_logged_in_shortcode($atts, $content = null)
		{

			$output = '';

			if (is_user_logged_in()) {

				$output = do_shortcode($content);
			}

			return $output;
		}

		/**
		 * Display content to users who are not logged in
		 * @since 1.3.1
		 */
		public function not_logged_in_shortcode($atts, $content = null)
		{

			$output = '';

			if (!is_user_logged_in()) {

				$output = do_shortcode($content);
			}

			return $output;
		}

		/**
		 * Add a link to the New Topic page
		 * @since 1.7.1
		 */
		public function new_topic_button_shortcode($atts)
		{

			extract(shortcode_atts(array(
				'class' => '',
				'text' 	=> __('Add new topic', 'wp-discussion-board')
			), $atts));

			$atts = apply_filters('ctdb_new_topic_button_atts', $atts);

			$output = '';

			// Get the page with the new topic form
			$options = get_option('ctdb_options_settings');
			if (isset($options['new_topic_page']) && $options['new_topic_page'] != '') {
				$new_topic_page = intval($options['new_topic_page']);
				// URL for the button
				$url = get_permalink($new_topic_page);
				$url = apply_filters('ctdb_new_topic_button_url', $url, $atts, $new_topic_page);
				$output .= sprintf(
					'<a class="ctdb-new-topic-button %s" href="%s">%s</a>',
					esc_attr($class),
					esc_url($url),
					esc_html($text)
				);
			} else {
				// Display a message to admins that there's no topic page set
				// Show admin a message to let them know
				if (current_user_can('update_plugins')) {
					return '<p class="ctdb-admin-message">' . __('Site Admin: please set the "New topic form page" field in Settings > Discussion Board > Options for the new_topic_button shortcode to work. This message will not be displayed to non-admins.', 'wp-discussion-board') . '</p>';
				} else {
					return;
				}
			}

			$output = apply_filters('ctdb_new_topic_button_filter', $output, $atts);

			return $output;
		}

		/*
		 * Display categories in standard layout on single.php
		 * @since 1.1.0
		*/
		public function display_categories_standard_layout($content = '')
		{
			/*
			 * Check the layout.
			 * Only display categories here if the layout is standard
			 * @since 1.2.0
			 */

			$design_options = get_option('ctdb_design_settings');
			if ((is_single() && isset($design_options['info_bar_layout']) && $design_options['info_bar_layout'] != 'standard') ||
				(is_archive() && isset($design_options['archive_layout']) && $design_options['archive_layout'] != 'standard')
			) {
				return $content;
			}

			$categories_enabled = true;
			$tags_enabled = true;
			$show_icons = ctdb_use_icons();
			$options = get_option('ctdb_categories_settings');
			if (!isset($options['allow_categories'])) {
				$categories_enabled = false;
			}
			if (!isset($options['allow_tags'])) {
				$tags_enabled = false;
			}

			// Categories position
			$options = get_option('ctdb_categories_settings');
			$position = is_array($options) && isset($options['categories_position']) ? $options['categories_position'] : '';
			if ($position == 'hide') {
				return $content;
			}

			// Use icons?
			if ($show_icons) {
				$heading_cat = '<span class="dashicons dashicons-category"></span>';
				$heading_tag = '<span class="dashicons dashicons-tag"></span>';
			} else {
				$heading_cat = __('Categorized: ', 'discussion-board-pro');
				$heading_tag = __('Tagged: ', 'discussion-board-pro');
			}
			$post_id = get_the_ID();

			$new_content = '';
			if ($categories_enabled && taxonomy_exists('topic-category')) {
				$new_content .= get_the_term_list($post_id, 'topic-category', '<div class="ctdb-span-3 ctdb-border-left">' . $heading_cat, ',&nbsp;', '</div>');
			}
			if ($tags_enabled && taxonomy_exists('topic-tag')) {
				$new_content .= get_the_term_list($post_id, 'topic-tag', '<div class="ctdb-span-3 ctdb-border-left">' . $heading_tag, ',&nbsp;', '</div>');
			}

			return $new_content;
		}

		public function use_icons()
		{
			return ctdb_use_icons();
		}

		private function get_custom_excerpt()
		{
			$options = get_option('ctdb_design_settings');
			$limit = !empty($options['number_words']) ? intval($options['number_words']) : 0;
			$content = explode(' ', strip_tags(get_the_content()), $limit);
			$content_length = count($content);
			if (count($content) >= $limit) {
				array_pop($content);
				$content = implode(" ", $content) . '...';
			} else {
				$content = implode(" ", $content);
			}

			if (defined('ELEMENTOR_PATH') && (class_exists('Elementor\Plugin') || defined('ELEMENTOR_PRO_PATH'))) {
				$content = explode('/*!', $content);
				if (count($content) > 1 && $content_length >= $limit) {
					$content = $content[0] . '...';
				} else {
					$content = implode(" ", $content);
				}
			}

			return $content;
		}

		public function check_and_add_recaptcha($form)
		{
			$CT_DB_Registration = new CT_DB_Registration();
			$recaptcha = $CT_DB_Registration->check_recaptcha();

			if ($recaptcha['add_recaptcha'] === true) {
				$form['recaptcha'] = '';
				if ($recaptcha['type'] !== 'invisible') {
					$form['recaptcha'] .= '<div id="ctdb_post_topic_submit" class="g-recaptcha" data-sitekey="' . $recaptcha['sitekey'] . '"></div><br />';
				}

				$form['recaptcha'] .= '<script src="https://www.google.com/recaptcha/api.js?onload=ctdb_render_recaptcha&render=explicit"></script>';
				$form['recaptcha'] .= '<script>';
				$form['recaptcha'] .= 'function ctdb_recaptcha_cb(token) {
						document.getElementById("ctdb_login_form").submit();
					}';

				$form['recaptcha'] .= 'function ctdb_render_recaptcha() {';
				if ($recaptcha['type'] === 'invisible') {
					$form['recaptcha'] .= 'window.ctdblogwidgetid = grecaptcha.render("submit_topic", {
								sitekey: "' . $recaptcha['sitekey'] . '",
								callback: ctdb_recaptcha_cb,
								action: "submit"
							});';
				} else {
					$form['recaptcha'] .= 'window.ctdblogwidgetid = grecaptcha.render("ctdb_post_topic_submit", {
								sitekey: "' . $recaptcha['sitekey'] . '",
								action: "submit"
							});';
				}
				$form['recaptcha'] .= '}
				</script>';
			}

			return $form;
		}

		// Callback function for handling <span> tags with URLs
		public function autoembed_span_url_callback($matches)
		{
			global $wp_embed;
			// $matches[0] contains the entire matched <span> tag with URL
			// $matches[1] contains any attributes within the <span> tag (if needed)
			// $matches[2] contains the URL

			// Sanitize and embed the URL using WordPress's autoembed function
			$embed_code = $wp_embed->autoembed(esc_url($matches[2]));

			// Escape output to prevent XSS vulnerabilities
			return $embed_code;
		}
	}
}
