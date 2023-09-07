<?php

/**
 * Discussion Board Admin class.
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}

// Don't initialise if there's already a Discussion Board activated.
if (!class_exists('WPDiscussionBoard\Admin\Admin')) {
	/**
	 * Plugin admin class.
	 **/
	class Admin
	{
		/**
		 * Initialize the class and start calling our hooks and filters.
		 *
		 * @since 1.0.0
		 */
		public function init()
		{
			add_action('admin_menu', array($this, 'add_settings_submenu'));
			add_action('admin_init', array($this, 'register_options_init'));
			add_action('admin_init', array($this, 'register_design_init'));
			add_action('admin_init', array($this, 'register_user_init'));
			add_action('admin_init', array($this, 'prevent_wp_admin_access'), 100);
			add_action('admin_init', array($this, 'save_tracker_settings'), 20);
			add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'), 10, 1);
			add_action('admin_notices', array($this, 'ctdb_admin_notices'));
			add_action('admin_footer', array($this, 'user_registration_notice_script'));
			add_action('wp_ajax_ctdb_dismiss_notice', array($this, 'ctdb_dismiss_notice'));
			add_action('show_user_profile', array($this, 'ctdb_display_activation_key'), 10, 1);
			add_action('edit_user_profile', array($this, 'ctdb_display_activation_key'), 10, 1);
			add_filter('plugin_action_links_wp-discussion-board/wp-discussion-board.php', array($this, 'filter_action_links'), 10, 1);

			add_action('wpdbd_load_assets', array($this, 'load_assets_for_screen'), 10, 1);
		}

		/**
		 * Loads assets for a particular screen.
		 *
		 * @since 2.4.3
		 */
		public function load_assets_for_screen($type)
		{
			switch ($type) {
				case 'settings':
					wp_enqueue_style('wpdbd-select2', WPDBD_PLUGIN_URL . 'assets/css/lib/select2.min.css', array(), WPDBD_PLUGIN_VERSION);
					wp_enqueue_script('wpdbd-select2', WPDBD_PLUGIN_URL . 'assets/js/lib/select2.min.js', array(), WPDBD_PLUGIN_VERSION);
					wp_enqueue_script('wpdbd-settings', WPDBD_PLUGIN_URL . 'assets/js/settings.js', array('wpdbd-select2'), WPDBD_PLUGIN_VERSION);
					wp_localize_script('wpdbd-settings', 'config', array());
					break;
			}
		}

		/**
		 * Enqueue scripts.
		 */
		public function enqueue_scripts($hook)
		{
			switch ($hook) {
				case 'discussion-topics_page_discussion_board':
					$this->load_assets_for_screen('settings');
					break;
			}

			wp_enqueue_style('ctdb-admin-style', WPDBD_PLUGIN_URL . 'assets/css/admin-style.css', array(), WPDBD_PLUGIN_VERSION);
		}

		/**
		 * We save this artificially to let the tracker know that
		 * we're allowed to export this option's data.
		 */
		public function save_tracker_settings()
		{
			$options = array(
				'ctdb_options_settings',
				'ctdb_design_settings',
				'ctdb_user_settings',
			);

			foreach ($options as $option) {
				$settings                              = get_option($option);
				$settings['wisdom_registered_setting'] = 1;
				update_option($option, $settings);
			}
		}

		/**
		 * Add the menu item.
		 */
		public function add_settings_submenu()
		{
			add_submenu_page(
				'edit.php?post_type=discussion-topics',
				__('Settings', 'wp-discussion-board'),
				__('Settings', 'wp-discussion-board'),
				'manage_options',
				'discussion_board',
				array(
					$this,
					'discussion_board_options_page',
				)
			);
		}

		/**
		 * Register options sections and fields.
		 */
		public function register_options_init()
		{
			register_setting('ctdb_options', 'ctdb_options_settings');

			add_settings_section(
				'ctdb_options_section',
				__('General Settings', 'wp-discussion-board'),
				array($this, 'discussion_board_settings_section_callback'),
				'ctdb_options'
			);

			add_settings_field(
				'options_page_settings',
				'<h3>' . __('Pages', 'wp-discussion-board') . '</h3>',
				array($this, 'page_header_render'),
				'ctdb_options',
				'ctdb_options_section'
			);

			$general_page_settings = wpdbd_general_page_settings();
			if (!empty($general_page_settings)) {
				foreach ($general_page_settings as $general_page_setting) {
					add_settings_field(
						$general_page_setting['id'],
						$general_page_setting['label'],
						array($this, $general_page_setting['callback']),
						'ctdb_options',
						'ctdb_options_section',
						$general_page_setting
					);
				}
			}
		}

		/**
		 * Set up the General tab in Settings. Defaults are set in install.php.
		 */
		public function register_design_init()
		{
			register_setting('ctdb_design', 'ctdb_design_settings');

			add_settings_section(
				'ctdb_design_section',
				__('Design Settings', 'wp-discussion-board'),
				array($this, 'discussion_board_design_settings_section_callback'),
				'ctdb_design'
			);

			add_settings_field(
				'enqueue_dashicons',
				__('Enqueue icons?', 'wp-discussion-board'),
				array($this, 'discussion_board_enqueue_dashicons_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'enqueue_styles',
				__('Enqueue styles?', 'wp-discussion-board'),
				array($this, 'discussion_board_enqueue_styles_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'use_theme_templates',
				__('Use theme templates', 'wp-discussion-board'),
				array($this, 'use_theme_templates_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'archive_layout',
				__('Archive layout', 'wp-discussion-board'),
				array($this, 'archive_layout_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'info_bar_layout',
				__('Single topic layout', 'wp-discussion-board'),
				array($this, 'info_bar_layout_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'meta_data_fields',
				__('Meta data fields', 'wp-discussion-board'),
				array($this, 'meta_data_fields_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'information_bar',
				__('Topic meta data position', 'wp-discussion-board'),
				array($this, 'discussion_board_information_bar_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'number_topics',
				__('Number of topics per page', 'wp-discussion-board'),
				array($this, 'discussion_board_number_topics_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			add_settings_field(
				'number_words',
				__('Number of words per excerpt', 'wp-discussion-board'),
				array($this, 'discussion_board_number_words_render'),
				'ctdb_design',
				'ctdb_design_section'
			);

			// Set default options
			$options = get_option('ctdb_design_settings');
			if (false === $options) {
				// Get defaults
				$defaults = $this->get_default_design_settings();
				update_option('ctdb_design_settings', $defaults);
			}
		}

		/**
		 * Return default design settings.
		 *
		 * @return array
		 */
		public function get_default_design_settings()
		{
			return array(
				'enqueue_dashicons'         => 1,
				'enqueue_styles'            => 1,
				'use_theme_templates'       => 1,
				'archive_layout'            => 'classic',
				'info_bar_layout'           => 'classic',
				'meta_data_fields'          => array(
					'replies' => 'replies',
					'voices'  => 'voices',
				),
				'information_bar'           => 'below-title',
				'number_topics'             => 5,
				'number_words'              => 35,
				'wisdom_registered_setting' => 1, // For plugin-usage-tracker.
			);
		}

		/**
		 * User sections and settings.
		 */
		public function register_user_init()
		{
			register_setting('ctdb_user', 'ctdb_user_settings');

			add_settings_section(
				'ctdb_user_section',
				__('User Settings', 'wp-discussion-board'),
				array($this, 'user_settings_section_callback'),
				'ctdb_user'
			);

			$options = get_option('ctdb_user_settings');
			if (isset($options['require_activation']) && 1 === $options['require_activation']) {
				// In previous versions, require_activation was a checkbox setting
				// If value equals 1, change value to 'user'
				$options['require_activation'] = 'user';
				update_option('ctdb_user_settings', $options);
			}

			$settings = wpdbd_get_user_settings();
			if (!empty($settings)) {
				foreach ($settings as $setting) {
					add_settings_field(
						$setting['id'],
						$setting['label'],
						array($this, $setting['callback']),
						'ctdb_user',
						'ctdb_user_section',
						$setting
					);
				}
			}

			// Set default options.
			if (false === $options) {
				// Get defaults.
				$defaults = $this->get_default_user_settings();
				update_option('ctdb_user_settings', $defaults);
			}
		}

		/**
		 * Return default user settings.
		 */
		public function get_default_user_settings()
		{
			return array(
				'redirect_to_page'              => '',
				'discussion_board_minimum_role' => array(), // Anyone can view
				'new_user_role'                 => 'subscriber',
				'minimum_user_roles'            => array('administrator', 'subscriber'), // These roles can post
				'display_user_name'             => 'display_name',
				'require_activation'            => 'user',
				'auto_log_in'                   => 0,
				'wisdom_registered_setting'     => 1, // For plugin-usage-tracker.
			);
		}

		/**
		 * Callback for header setting.
		 *
		 * @param $args array Callback arguments.
		 */
		public function page_header_callback($args)
		{
			$options = get_option($args['section']);
			$value   = '';
			if (isset($options[$args['id']])) {
				// Ensure value is prefixed with #
				$value = '#' . str_replace('#', '', $options[$args['id']]);
			}
		}

		/**
		 * Callback for pages select field.
		 *
		 * @param $args array Callback arguments.
		 */
		public function pages_select_callback($args)
		{
			$options = get_option($args['section']);
			$value   = '';
			if (isset($options[$args['id']])) {
				$value = $options[$args['id']];
			}
			// Get all pages
			$pages = get_pages();

			// Iterate through the pages
			if ($pages) :
?>
				<select name='<?php echo esc_attr($args['section']); ?>[<?php echo esc_attr($args['id']); ?>]'>
					<option></option>
					<?php foreach ($pages as $page) { ?>
						<option value='<?php echo esc_attr($page->ID); ?>' <?php selected($value, $page->ID); ?>><?php echo esc_html($page->post_title); ?></option>
					<?php } ?>
				</select>
			<?php
			endif;

			if (isset($args['description'])) :
			?>
				<p class="description"><?php echo esc_html($args['description']); ?></p>
			<?php
			endif;
		}

		public function page_header_render()
		{
		}

		/**
		 * Checkbox callback.
		 *
		 * @param $args array Callback arguments.
		 *
		 * @since 2.2.1
		 */
		public function checkbox_callback($args)
		{
			$options = get_option($args['section']);
			$value   = '';

			if (isset($options[$args['id']])) {
				$value = $options[$args['id']];
			}

			$checked = !empty($value) ? checked(1, $value, false) : '';
			?>
			<input type='checkbox' name="<?php echo esc_attr($args['section']); ?>[<?php echo esc_attr($args['id']); ?>]" <?php echo esc_html($checked); ?> value='1'>
			<?php
			if (isset($args['description'])) :
			?>
				<p class="description"><?php echo esc_html($args['description']); ?></p>
			<?php
			endif;
		}

		/**
		 * Text callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public function text_callback($args)
		{
			$options = get_option($args['section']);
			$value   = '';

			if (isset($options[$args['id']])) {
				$value = $options[$args['id']];
			}
			?>
			<input type='text' name="<?php echo esc_attr($args['section']); ?>[<?php echo esc_attr($args['id']); ?>]" value="<?php echo esc_attr($value); ?>" />
			<?php
			if (isset($args['description'])) :
			?>
				<p class="description"><?php echo esc_html($args['description']); ?></p>
			<?php
			endif;
		}

		/**
		 * WYSIWYG callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public function wysiwyg_callback($args)
		{
			$options = get_option($args['section']);
			$value   = '';

			if (isset($options[$args['id']])) {
				$value = $options[$args['id']];
			}

			$name = $args['section'] . '[' . $args['id'] . ']';

			wp_editor(
				$value,
				$args['id'],
				array(
					'textarea_name' => $name,
					'media_buttons' => false,
					'wpautop'       => false,
					'tinymce'       => true,
					'quicktags'     => true,
					'textarea_rows' => 5,
				)
			);

			if (isset($args['description'])) :
			?>
				<p class="description"><?php echo esc_html($args['description']); ?></p>
			<?php
			endif;
		}

		/**
		 * Select callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public function select_callback($args)
		{
			$options = get_option($args['section']);
			$setting = '';
			if (isset($options[$args['id']])) {
				$setting = $options[$args['id']];
			}
			?>
			<select name="<?php echo esc_attr($args['section']); ?>[<?php echo esc_attr($args['id']); ?>]">
				<?php foreach ($args['choices'] as $key => $value) { ?>
					<option value="<?php echo esc_attr($key); ?>" <?php selected($setting, $key); ?>><?php echo esc_html($value); ?></option>
				<?php } ?>
			</select>
			<?php
			if (isset($args['description'])) :
			?>
				<p class="description"><?php echo esc_html($args['description']); ?></p>
			<?php
			endif;
		}

		/**
		 * Email callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public function email_callback($args)
		{
			$options = get_option($args['section']);
			$value   = '';
			if (isset($options[$args['id']])) {
				$value = $options[$args['id']];
			}
			?>
			<input type="email" name="<?php echo esc_attr($args['section']); ?>[<?php echo esc_attr($args['id']); ?>]" value="<?php echo esc_attr($value); ?>" />
			<?php
		}

		/**
		 * Render minimum roles form.
		 */
		public function discussion_board_minimum_roles_render()
		{
			$options = get_option('ctdb_user_settings');
			if (isset($options['discussion_board_minimum_role'])) {
				$permitted_roles = $options['discussion_board_minimum_role'];
			} else {
				$permitted_roles = array();
			}

			// Let's check what roles are available.
			$roles = get_editable_roles();

			if (!empty($roles)) :
			?>
				<select multiple='multiple' name='ctdb_user_settings[discussion_board_minimum_role][]'>
					<?php
					foreach ($roles as $rolename => $role) :
						// Pending should never be able to post.
						// Admins will always be able to post.
						if ('pending' !== $rolename && 'administrator' !== $rolename) :
							$selected = '';

							//Check for selected values.
							if (count($permitted_roles) > 0) {
								$selected = in_array($rolename, $permitted_roles, true) ? 'selected' : '';
							}
					?>
							<option value="<?php echo esc_attr($rolename); ?>" <?php echo esc_attr($selected); ?>><?php echo esc_html($role['name']); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e('Select one or more user roles that are permitted to view Discussion Board topics. Administrators can always view.', 'wp-discussion-board'); ?></p>
			<?php
			endif;
		}

		/**
		 * Render maximum user roles form.
		 */
		public function minimum_user_roles_render()
		{
			$options = get_option('ctdb_user_settings');

			if (isset($options['minimum_user_roles'])) {
				$permitted_roles = $options['minimum_user_roles'];
			} else {
				$permitted_roles = array();
			}

			// Let's check what roles are available.
			$roles = get_editable_roles();

			if (!empty($roles)) :
			?>
				<select multiple='multiple' name='ctdb_user_settings[minimum_user_roles][]'>
					<?php
					foreach ($roles as $rolename => $role) :
						// Pending should never be able to post
						if ('pending' !== $rolename) :
							//Check for selected values
							$selected = '';
							if (count($permitted_roles) > 0) {
								$selected = in_array($rolename, $permitted_roles, true) ? 'selected' : '';
							}
					?>
							<option value="<?php echo esc_attr($rolename); ?>" <?php echo esc_html($selected); ?>><?php echo esc_html($role['name']); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e('Select one or more user roles that are permitted to post new Discussion Board topics', 'wp-discussion-board'); ?></p>
			<?php
			endif;
		}

		/**
		 * Render new user role form.
		 */
		public function new_user_role_render()
		{
			$options = get_option('ctdb_user_settings');

			// Let's check what roles are available
			$roles = get_editable_roles();

			// Only allow certain roles to be registered
			$disallowed_roles = array('administrator', 'editor', 'author');

			if (!empty($roles)) :
			?>
				<select name='ctdb_user_settings[new_user_role]'>
					<?php
					foreach ($roles as $rolename => $role) :
						// Exclude certain roles
						if (!in_array($rolename, $disallowed_roles, true)) :
							//Check for selected value
							$selected = ($rolename === $options['new_user_role']) ? 'selected' : '';
					?>
							<option value="<?php echo esc_attr($rolename); ?>" <?php echo esc_html($selected); ?>><?php echo esc_html($role['name']); ?></option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e('Newly registered users will be assigned this role', 'wp-discussion-board'); ?></p>
			<?php
			endif;
		}

		/**
		 * Automatically log user in option render.
		 */
		public function auto_log_in_render()
		{
			$options = get_option('ctdb_user_settings');
			?>
			<input type="checkbox" name="ctdb_user_settings[auto_log_in]" <?php checked(!empty($options['auto_log_in']), 1); ?> value="1">
			<p class="description"><?php esc_html_e('This will automatically log users in once they have registered.', 'wp-discussion-board'); ?></p>
		<?php
		}

		/**
		 * Design settings.
		 */
		public function discussion_board_enqueue_styles_render()
		{
			$options = get_option('ctdb_design_settings');
		?>
			<input type="checkbox" name="ctdb_design_settings[enqueue_styles]" <?php checked(!empty($options['enqueue_styles']), 1); ?> value="1">
		<?php
		}

		/**
		 * Dashicons form render.
		 */
		public function discussion_board_enqueue_dashicons_render()
		{
			$options = get_option('ctdb_design_settings');
		?>
			<input type="checkbox" name="ctdb_design_settings[enqueue_dashicons]" <?php checked(!empty($options['enqueue_dashicons']), 1); ?> value="1">
		<?php
		}

		/**
		 * Theme template form render.
		 */
		public function use_theme_templates_render()
		{
			$options = get_option('ctdb_design_settings');
		?>
			<input type="checkbox" name="ctdb_design_settings[use_theme_templates]" <?php checked(!empty($options['use_theme_templates']), 1); ?> value="1">
		<?php
		}

		/**
		 * Archive layout form render.
		 */
		public function archive_layout_render()
		{
			$options = get_option('ctdb_design_settings');

			if (!isset($options['archive_layout']) || 'classic' === $options['archive_layout']) {
				$layout = 'classic';
			} else {
				$layout = $options['archive_layout'];
			}
		?>
			<select name='ctdb_design_settings[archive_layout]'>
				<option value="standard" <?php selected($layout, 'standard'); ?>><?php esc_html_e('Archive', 'wp-discussion-board'); ?></option>
				<option value="classic" <?php selected($layout, 'classic'); ?>><?php esc_html_e('Classic Forum', 'wp-discussion-board'); ?></option>
				<option value="table" <?php selected($layout, 'table'); ?>><?php esc_html_e('Table', 'wp-discussion-board'); ?></option>
			</select>
			<p class="description"><?php esc_html_e('Set the layout of your topics archive if you are using the discussion_topics shortcode to display topics.', 'wp-discussion-board'); ?></p>
		<?php
		}

		/**
		 * Infobar layout form render.
		 */
		public function info_bar_layout_render()
		{
			$options = get_option('ctdb_design_settings');

			if (!isset($options['info_bar_layout']) || 'classic' === $options['info_bar_layout']) {
				$layout = 'classic';
			} else {
				$layout = $options['info_bar_layout'];
			}
		?>
			<select name="ctdb_design_settings[info_bar_layout]">
				<option value="standard" <?php selected($layout, 'standard'); ?>><?php esc_html_e('Archive', 'wp-discussion-board'); ?></option>
				<option value="classic" <?php selected($layout, 'classic'); ?>><?php esc_html_e('Classic Forum', 'wp-discussion-board'); ?></option>
				<option value="table" <?php selected($layout, 'table'); ?>><?php esc_html_e('Table', 'wp-discussion-board'); ?></option>
			</select>
			<p class="description"><?php esc_html_e('Set the layout of single topic pages.', 'wp-discussion-board'); ?></p>
			<?php
		}

		/**
		 * Meta data fields form render.
		 */
		public function meta_data_fields_render()
		{
			$options = get_option('ctdb_design_settings');
			$fields  = ctdb_meta_data_fields();

			if (!empty($fields)) :
				foreach ($fields as $key => $value) :
					$checked = '';
					if (!empty($options['meta_data_fields']) && in_array($key, $options['meta_data_fields'], true)) {
						$checked = 'checked';
					}
			?>
					<input type="checkbox" name="ctdb_design_settings[meta_data_fields][<?php echo esc_attr($key); ?>]" <?php echo esc_html($checked); ?> value="<?php echo esc_attr($key); ?>"><label><?php echo esc_html($value); ?></label><br>
			<?php
				endforeach;
			endif;
			?>
			<p class="description"><?php esc_html_e('Select which meta fields to display for topics.', 'wp-discussion-board'); ?></p>
		<?php
		}

		/**
		 * Information bar form render.
		 */
		public function discussion_board_information_bar_render()
		{
			$options = get_option('ctdb_design_settings');
		?>
			<select name="ctdb_design_settings[information_bar]">
				<option value="hide" <?php selected($options['information_bar'], 'hide'); ?>>
					<?php esc_html_e('Hide', 'wp-discussion-board'); ?>
				</option>
				<option value="below-title" <?php selected($options['information_bar'], 'below-title'); ?>>
					<?php esc_html_e('Below Title', 'wp-discussion-board'); ?>
				</option>
				<option value="below-content" <?php selected($options['information_bar'], 'below-content'); ?>>
					<?php esc_html_e('Below Content', 'wp-discussion-board'); ?>
				</option>
			</select>
			<p class="description"><?php esc_html_e('If you are using the Archive or Table layout style, you can position the meta data.', 'wp-discussion-board'); ?></p>
		<?php
		}

		/**
		 * Number of words form render.
		 */
		public function discussion_board_number_words_render()
		{
			$options = get_option('ctdb_design_settings');
		?>
			<input type="number" name="ctdb_design_settings[number_words]" min=0 value="<?php echo intval($options['number_words']); ?>" />
			<p class="description"><?php esc_html_e('Truncate the content to a set number of words when each topic is displayed in the archive. Leave at 0 to display all the content for each topic on archive pages.', 'wp-discussion-board'); ?></p>
		<?php
		}

		/**
		 * Number of topics form render.
		 */
		public function discussion_board_number_topics_render()
		{
			$options = get_option('ctdb_design_settings');

			if (!isset($options['number_topics'])) {
				$options['number_topics'] = 0;
			}
		?>
			<select name='ctdb_design_settings[number_topics]'>
				<option value="1" <?php selected($options['number_topics'], 1); ?>>1</option>
				<option value="2" <?php selected($options['number_topics'], 2); ?>>2</option>
				<option value="3" <?php selected($options['number_topics'], 3); ?>>3</option>
				<option value="4" <?php selected($options['number_topics'], 4); ?>>4</option>
				<option value="5" <?php selected($options['number_topics'], 5); ?>>5</option>
				<option value="6" <?php selected($options['number_topics'], 6); ?>>6</option>
				<option value="7" <?php selected($options['number_topics'], 7); ?>>7</option>
				<option value="8" <?php selected($options['number_topics'], 8); ?>>8</option>
				<option value="9" <?php selected($options['number_topics'], 9); ?>>9</option>
				<option value="10" <?php selected($options['number_topics'], 10); ?>>10</option>
				<option value="11" <?php selected($options['number_topics'], 11); ?>>11</option>
				<option value="12" <?php selected($options['number_topics'], 12); ?>>12</option>
				<option value="13" <?php selected($options['number_topics'], 13); ?>>13</option>
				<option value="14" <?php selected($options['number_topics'], 14); ?>>14</option>
				<option value="15" <?php selected($options['number_topics'], 15); ?>>15</option>
				<option value="16" <?php selected($options['number_topics'], 16); ?>>16</option>
				<option value="17" <?php selected($options['number_topics'], 17); ?>>17</option>
				<option value="18" <?php selected($options['number_topics'], 18); ?>>18</option>
				<option value="19" <?php selected($options['number_topics'], 19); ?>>19</option>
				<option value="20" <?php selected($options['number_topics'], 20); ?>>20</option>
			</select>
		<?php
		}

		/**
		 * Username form render.
		 */
		public function display_user_name_render()
		{
			$options = get_option('ctdb_user_settings');
			$value   = isset($options['display_user_name']) ? $options['display_user_name'] : '';
		?>
			<select name='ctdb_user_settings[display_user_name]'>
				<option value='display_name' <?php selected($value, 'display_name'); ?>><?php esc_html_e('Display Name', 'wp-discussion-board'); ?></option>
				<option value='user_login' <?php selected($value, 'user_login'); ?>><?php esc_html_e('Username', 'wp-discussion-board'); ?></option>
				<option value='nickname' <?php selected($value, 'nickname'); ?>><?php esc_html_e('Nickname', 'wp-discussion-board'); ?></option>
			</select>
			<p class="description"><?php esc_html_e('Decide how to display user name', 'wp-discussion-board'); ?></p>
		<?php
		}

		/**
		 * Render email blacklist form.
		 */
		public function email_blacklist_render()
		{
			$options = get_option('ctdb_user_settings');
			$value   = isset($options['email_blacklist']) ? $options['email_blacklist'] : '';
		?>
			<textarea rows="10" style="width: 300px;" name='ctdb_user_settings[email_blacklist]'><?php echo esc_attr($value); ?></textarea>
			<p class="description"><?php esc_html_e('Block specific email addresses(e.g. nasty@spammer.com) or entire email domains(e.g. @spammer.com) from registering. Add one address per line.', 'wp-discussion-board'); ?></p>
		<?php
		}

		/**
		 * Render color settings in Design
		 *
		 * @since 2.1.0
		 */
		public function color_setting_render($args)
		{
			$options = get_option('ctdb_design_settings');
			$value   = '';

			if (isset($options[$args['id']])) {
				// Ensure value is prefixed with #
				$value = '#' . str_replace('#', '', $options[$args['id']]);
			}
		?>
			<input type="text" name="ctdb_design_settings[$args['id']]" value="<?php echo esc_attr($value); ?>" />
		<?php
		}

		/**
		 * Settings callback.
		 */
		public function discussion_board_settings_section_callback()
		{
			echo '<p>' . esc_html__('This includes settings for the pages that are automatically created when Discussion Board is first activated, front-end messages, and options for posting new topics and for registration and log-in.', 'wp-discussion-board') . '</p>';
			echo '<p>' . wp_kses_post('Do you have a few seconds to <a target="_blank"  href="https://translate.wordpress.org/projects/wp-plugins/wp-discussion-board/stable">help with translating Discussion Board into other languages</a>? Even if you just translated a couple of words, that would really help.', 'wp-discussion-board') . '</p>';
		}

		/**
		 * Design settings callback.
		 */
		public function discussion_board_design_settings_section_callback()
		{
			echo '<p>' . esc_html__('Settings for the styles and layout.', 'wp-discussion-board') . '</p>';
			echo '<p>' . wp_kses_post('Do you have a few seconds to <a target="_blank"  href="https://translate.wordpress.org/projects/wp-plugins/wp-discussion-board/stable">help with translating Discussion Board into other languages</a>? Even if you just translated a couple of words, that would really help.', 'wp-discussion-board') . '</p>';
		}

		/**
		 * Settings section callback.
		 */
		public function user_settings_section_callback()
		{
			echo '<p>' . esc_html__('Settings for user options and permissions.', 'wp-discussion-board') . '</p>';
			echo '<p>' . wp_kses_post('Do you have a few seconds to <a target="_blank" href="https://translate.wordpress.org/projects/wp-plugins/wp-discussion-board/stable">help with translating Discussion Board into other languages</a>? Even if you just translated a couple of words, that would really help.', 'wp-discussion-board') . '</p>';
		}

		/**
		 * Render options page.
		 */
		public function discussion_board_options_page()
		{
			$current = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'options'; // phpcs:ignore
			$title   = __('Discussion Board', 'wp-discussion-board');
			$tabs    = array(
				'options' => __('General', 'wp-discussion-board'),
				'design'  => __('Design', 'wp-discussion-board'),
				'user'    => __('User', 'wp-discussion-board'),
			);
			$tabs    = apply_filters('ct_db_settings_tabs', $tabs);
		?>
			<div class="wrap">
				<h1><?php echo esc_html($title); ?></h1>
				<?php settings_errors(); ?>
				<div class="ctdb-outer-wrap">
					<div class="ctdb-inner-wrap">
						<h2 class="nav-tab-wrapper">
							<?php
							foreach ($tabs as $tab => $name) {
								$class = ($tab === $current) ? ' nav-tab-active' : '';
								echo wp_kses_post("<a class='nav-tab$class' href='?post_type=discussion-topics&page=discussion_board&tab=$tab'>$name</a>");
							}
							?>
						</h2>

						<form action='options.php' method='post'>
							<?php
							settings_fields('ctdb_' . strtolower($current));
							do_settings_sections('ctdb_' . strtolower($current));
							submit_button();
							?>
						</form>
					</div><!-- .ctdb-inner-wrap -->
					<div class="ctdb-banners">
						<?php if (!defined('DB_PRO_VERSION')) : ?>
							<?php
							$license_key = get_option(Admin_License::FREE_LICENSE_OPTION_KEY);
							if (empty($license_key)) :
							?>
								<div class="ctdb-banner">
									<?php include WPDBD_PLUGIN_DIR . '/templates/admin/free-license-form.php'; ?>
								</div>
							<?php endif; ?>

							<div class="ctdb-banner">
								<a target="_blank" href="https://wpdiscussionboard.com/?utm_source=wp_plugin&utm_medium=banner&utm_content=sidebar&utm_campaign=upgrade">
									<img src="<?php echo esc_url(WPDBD_PLUGIN_URL . 'assets/images/discussion-board-banner-ad.png'); ?>" alt="Discussion Board Pro">
								</a>
							</div>
						<?php endif; ?>

						<div id="postbox-container-1" class="postbox-container ctdb-sidebar ctdb-banner">
							<div class="meta-box-sortables">

								<div class="postbox">
									<div class="postbox-header">
										<h3 class="hndle is-non-sortable">
											<span>Recommended Plugins</span>
										</h3>
									</div>

									<div class="inside">
										<?php echo $this->sidebar_recommended_plugins(); ?>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div><!-- .ctdb-outer-wrap -->
			</div><!-- .wrap -->
			<?php
		}

		/**
		 * Admin notices.
		 */
		public function ctdb_admin_notices()
		{
			$options = get_option('ctdb_options_settings');

			// If the option to hide WP Login is selected with no frontend log-in page specified.
			if (
				(isset($options['frontend_login_page']) &&
					!$options['frontend_login_page']) &&
				!empty($options['hide_wp_login'])
			) {
			?>
				<div class="notice error">
					<p><?php esc_html_e('You\'ve chosen to hide the WP Login page but you haven\'t specified a page on the front end with a login page. Until you specify that, the option to hide WP Login is disabled.', 'wp-discussion-board'); ?></p>
				</div>
			<?php
				$options['hide_wp_login'] = 0;
				update_option('ctdb_options_settings', $options);
			}
		}

		/**
		 * Check that users can be registered.
		 */
		public function user_registration_notice_script()
		{
			if (!get_option('users_can_register') && 1 !== get_option('ctdb_nag_dismissed')) :
			?>
				<script>
					jQuery(document).ready(function($) {
						$('body').on('click', '.ctdb-registration-notice .notice-dismiss', function() {
							var data = {
								'action': 'ctdb_dismiss_notice'
							}
							jQuery.post(
								ajaxurl,
								data
							);
						});
					});
				</script>
			<?php
			endif;
		}

		/**
		 * Ajax call to dismiss notice.
		 */
		public function ctdb_dismiss_notice()
		{
			if (get_option('ctdb_nag_dismissed') !== false) {
				update_option('ctdb_nag_dismissed', 1);
			} else {
				add_option('ctdb_nag_dismissed', 1);
			}
			die();
		}

		/**
		 * Display activation key in user profile.
		 */
		public function ctdb_display_activation_key($user)
		{
			?>
			<table class="form-table">
				<tr>
					<td>
						<input type="hidden" value="<?php echo esc_attr(get_user_meta($user->ID, 'activate_key', true)); ?>" class="regular-text" readonly="readonly" />
					</td>
				</tr>
			</table>
<?php
		}

		/**
		 * Prevent admin access.
		 */
		public function prevent_wp_admin_access()
		{
			// Admins should always have access
			if (current_user_can('manage_plugins')) {
				return;
			}

			$options = get_option('ctdb_options_settings');

			// Check that we've enabled the option
			if (!empty($options['prevent_wp_admin_access'])) {
				$user_options = get_option('ctdb_user_settings');
				global $current_user;
				$user_roles = $current_user->roles;
				$user_role  = array_shift($user_roles);

				// Check we're the correct role
				if ($user_role === $user_options['new_user_role'] && !defined('DOING_AJAX')) {
					wp_safe_redirect(home_url());
					exit;
				}
			}
		}

		/**
		 * Filter the action links to add Upgrade option.
		 *
		 * @since 2.2.4
		 */
		public function filter_action_links($links)
		{
			$links['settings'] = '<a href="' . admin_url('edit.php?post_type=discussion-topics&page=discussion_board') . '">' . __('Settings', 'wp-discussion-board') . '</a>';
			$links['support']  = '<a href="https://wpdiscussionboard.com/docs/">' . __('Support', 'wp-discussion-board') . '</a>';

			// Check to see if Pro version already installed
			if (!defined('DB_PRO_VERSION')) {
				$links['upgrade'] = '<a href="https://wpdiscussionboard.com">' . __('Upgrade', 'wp-discussion-board') . '</a>';
			}

			return $links;
		}

		public function sidebar_recommended_plugins()
		{
			$link_icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="linkIcon"><path d="M18.2 17c0 .7-.6 1.2-1.2 1.2H7c-.7 0-1.2-.6-1.2-1.2V7c0-.7.6-1.2 1.2-1.2h3.2V4.2H7C5.5 4.2 4.2 5.5 4.2 7v10c0 1.5 1.2 2.8 2.8 2.8h10c1.5 0 2.8-1.2 2.8-2.8v-3.6h-1.5V17zM14.9 3v1.5h3.7l-6.4 6.4 1.1 1.1 6.4-6.4v3.7h1.5V3h-6.3z"></path></svg>';

			$content = '<p>';
			$content .= esc_html__('Check out some of our other plugins + a few extra that we love using!', 'peters-login-redirect');
			$content .= '</p>';

			$content .= '<p>';

			$widgetopts_url = 'https://widget-options.com/ref/113/';

			$content .= sprintf(
				esc_html__('%sWidget Options%s The #1 WordPress Widget Control Plugin. Easily show or hide widgets based on a ton of factors such as page name, user level, devices, days of the week and more!', 'peters-login-redirect'),
				'<a class="loginwp-link" href="' . $widgetopts_url . '" target="_blank" style="margin-bottom: 5px;">',
				$link_icon . '</a>'
			);

			$content .= '</p>';

			$content .= '<p>';

			$wpdb_url = 'https://loginwp.com/ref/10/';

			$content .= sprintf(
				esc_html__('%sLogin WP%s #1 WordPress User Redirection Plugin. Redirect users to different URLs after they log in, log out and register based on different conditions.', 'peters-login-redirect'),
				'<a class="loginwp-link" href="' . $wpdb_url . '" target="_blank" style="margin-bottom: 5px;">',
				$link_icon . '</a>'
			);

			$content .= '</p>';

			$content .= '<p>';

			$fusewp_url = 'https://fusewp.com/?sscid=81k7_pja98';

			$content .= sprintf(
				esc_html__('%sFuseWP%s Automatically Sync WordPress Users, customers, and members in ecommerce and membership plugins with your CRM and Email Marketing software.', 'peters-login-redirect'),
				'<a class="loginwp-link" href="' . $fusewp_url . '" target="_blank" style="margin-bottom: 5px;">',
				$link_icon . '</a>'
			);

			$content .= '</p>';

			return $content;
		}
	}
}
