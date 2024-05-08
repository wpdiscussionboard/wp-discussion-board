<?php

/**
 * Discussion Board registration class
 * This handles user registration and logging in
 *
 * @since 2.1.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Registration public class
 **/
if (!class_exists('CT_DB_Registration')) { // Don't initialise if there's already a Discussion Board activated
	class CT_DB_Registration
	{

		public $user_can_view = false;
		public $user_can_post = false;

		public function __construct()
		{
		}
		/**
		 * Initialize the class and start calling our hooks and filters
		 * @since 1.0.0
		 */
		public function init()
		{

			add_action('init', array($this, 'check_user_permission'));
			add_action('init', array($this, 'login_user'));
			add_action('init', array($this, 'register_new_user'));
			add_action('init', array($this, 'activate_new_user'));
			add_action('init', array($this, 'redirect_to_login_page'));

			add_filter('the_content', array($this, 'get_error_messages'), 1);
			add_filter('discussion_board_validate_recaptcha', array($this, 'validate_recaptcha_response'), 1, 2);

			add_action('wp_login_failed', array($this, 'login_fail'));
			add_action('wp_ajax_ajax_validation', array($this, 'ajax_validation_callback'));
			add_action('wp_ajax_nopriv_ajax_validation', array($this, 'ajax_validation_callback'));

			add_shortcode('discussion_board_login_form', array($this, 'return_login_registration_form'), 10, 2);
			add_shortcode('discussion_board_login_only', array($this, 'return_login_form_only'), 10, 2);
			add_shortcode('discussion_board_registration_only', array($this, 'return_registration_form_only'), 10, 2);
		}

		public function check_user_permission()
		{
			$this->user_can_view = ctdb_is_user_permitted();
			$this->user_can_post = ctdb_is_posting_permitted();
		}

		/**
		 * Return log-in and registration form markup
		 * @since 1.0.0
		 */
		public function return_login_registration_form($atts, $content = '')
		{

			$successful_registration = false;
			if (!empty($_POST['ctdb_user_email'])) {
				$user_email = sanitize_email(wp_unslash($_POST['ctdb_user_email']));
				$user       = get_user_by('email', $user_email);
				if ($user) {
					$successful_registration = true;
				}
			}

			if (!is_user_logged_in() && !$successful_registration) {

				$message = '';
				$class   = '';

				// Check if styles are enqueued
				$options           = get_option('ctdb_design_settings');
				$general_options   = get_option('ctdb_options_settings');
				$hide_registration = !empty($general_options['hide_registration_tab']) ? true : false;

				// Use icons?
				$show_icons = ctdb_use_icons();

				// Log in tab
				$message .= '<div class="ctdb-login-form-wrapper">';
				if (isset($_POST['ctdb_page']) && $_POST['ctdb_page'] != 'register') {
					$class = 'active-header';
				}
				$message .= '<div class="ctdb-header ' . $class . '" data-form-id="ctdb-login-wrap"><h3 class="ctdb-h3">';
				if ($show_icons) {
					$message .= '<span class="dashicons dashicons-unlock"></span>';
				}
				$message .= __('Log in', 'wp-discussion-board') . '</h3></div>';

				// If styles are not enqueued we will display the log-in form fields directly after the heading
				if (empty($options['enqueue_styles'])) {
					$message .= $this->display_login_form(true);
				}

				// Registration tab
				if (!$hide_registration) {
					$class = '';
					if (isset($_POST['ctdb_page']) && $_POST['ctdb_page'] == 'register') {
						$class = 'active-header';
					}
					$message .= '<div class="ctdb-header ' . $class . '" data-form-id="ctdb-registration-wrap"><h3 class="ctdb-h3">';
					if ($show_icons) {
						$message .= '<span class="dashicons dashicons-edit"></span>';
					}
					$message .= __('Register', 'wp-discussion-board') . '</h3></div>';
				}

				// Get the forms HTML

				// If styles are enqueued we will display the log-in form fields after both headings
				if (!empty($options['enqueue_styles'])) {
					$message .= $this->display_login_form(!$hide_registration);
				}

				if (!$hide_registration) {
					$message .= $this->display_registration_form();
				}

				$message .= '</div><!-- .ctdb-login-form-wrapper -->';

				$message .= '<script>
					jQuery(document).ready(function($){
						$(".ctdb-header").on("click",function(){
							id = $(this).data("form-id");
							$(".ctdb-header").removeClass("active-header");
							$(this).addClass("active-header");
							$(".ctdb-form-section").removeClass("active-section");
							$("#"+id).addClass("active-section");
						});
					});
				</script>';
			} else {

				$message = do_shortcode($content);
			}

			return $message;
		}

		/**
		 * Return log-in form markup
		 * @since 1.3.1
		 */
		public function return_login_form_only($atts, $content = '')
		{

			if (!is_user_logged_in()) {

				$message = '';
				$class   = '';

				// Check if styles are enqueued
				$options = get_option('ctdb_design_settings');

				// Use icons?
				$show_icons = ctdb_use_icons();

				// Log in tab
				$message .= '<div class="ctdb-header active-header" data-form-id="ctdb-login-wrap"><h3 class="ctdb-h3">';
				if ($show_icons) {
					$message .= '<span class="dashicons dashicons-unlock"></span>';
				}
				$message .= __('Log in', 'wp-discussion-board') . '</h3></div>';

				// Fake page to show form
				$_POST['ctdb_page'] = 'login';

				// Get the forms HTML
				$message .= $this->display_login_form(false);
			} else {

				$message = do_shortcode($content);
			}

			return $message;
		}

		/**
		 * Return log-in form markup
		 * @since 1.3.1
		 */
		public function return_registration_form_only($atts, $content = '')
		{

			$successful_registration = false;
			if (!empty($_POST['ctdb_user_email'])) {
				$user_email = sanitize_email(wp_unslash($_POST['ctdb_user_email']));
				$user       = get_user_by('email', $user_email);
				if ($user) {
					$successful_registration = true;
				}
			}

			if (!is_user_logged_in()) {

				$message = '';
				$class   = '';

				// Check if styles are enqueued
				$options = get_option('ctdb_design_settings');

				// Use icons?
				$show_icons = ctdb_use_icons();

				// Registration tab
				$message .= '<div class="ctdb-header active-header" data-form-id="ctdb-registration-wrap"><h3 class="ctdb-h3">';
				if ($show_icons) {
					$message .= '<span class="dashicons dashicons-edit"></span>';
				}
				$message .= __('Register', 'wp-discussion-board') . '</h3></div>';

				// Fake page to show form
				$_POST['ctdb_page'] = 'register';

				// Get the forms HTML
				$message .= $this->display_registration_form();
			} else {

				$message = do_shortcode($content);
			}

			return $message;
		}


		/**
		 * Hide comment form if user doesn't have access
		 * @since 1.0.0
		 */
		public function return_access_restricted_title()
		{

			$options = get_option('ctdb_options_settings');
			$title   = ctdb_get_restricted_title();

			if ($title == '') {
				$title = __('Content not available', 'wp-discussion-board');
			}

			return $title;
		}

		/**
		 * Display registration form
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function display_registration_form()
		{

			// Only show the registration form to non-logged-in members
			if (!is_user_logged_in()) {

				$output = $this->registration_form_fields();
			} else {

				$output  = '<div id="ctdb-registration-wrap" class="ctdb-form-section">';
				$output .= '<p>' . __('You are already registered on this site.', 'wp-discussion-board') . '</p>';
				$output .= '</div>';
			}

			return $output;
		}

		/**
		 * Display log-in form
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function display_login_form($login_registration = true)
		{

			$output = $this->login_form_fields($login_registration);
			return $output;
		}

		/**
		 * Get the registration form fields
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function registration_form_fields()
		{

			$options = get_option('ctdb_options_settings');
			// Check if we need to add a humanity check to the form
			$require_humanity = isset($options['check_human']);
			$class            = '';

			//check if recaptcha is available
			$recaptcha = $this->check_recaptcha();

			ob_start();

			if (isset($_POST['ctdb_page']) && $_POST['ctdb_page'] == 'register') {
				$class = 'active-section';
			} ?>

			<div id="ctdb-registration-wrap" class="ctdb-form-section <?php echo $class; ?>">

				<?php
				// show any error messages after form submission
				$this->ctdb_show_error_messages();

				// Get the registration form fields
				$form = ctdb_registration_form_fields();
				?>

				<form id="ctdb_registration_form" class="ctdb-form" action="" method="POST">

					<fieldset>
						<?php
						if (!empty($form)) {
							foreach ($form as $field) {
								if (isset($_POST["{$field['id']}"])) { // phpcs:ignore
									$value = $_POST["{$field['id']}"]; // phpcs:ignore
								} else {
									$value = '';
								}

								/*
								 * Backwards compatible support for `textarea`. Previously, `field` could
								 * have been defined without `type` being defined. We've removed `field`
								 * now and only rely on `type`.
								 *
								 * @since 2.3.17
								 */
								if (!empty($field['field']) && 'textarea' === $field['field']) {
									$field['type'] = 'textarea';
								}

								switch ($field['type']) {
									case 'email':
									case 'hidden':
									case 'number':
									case 'password':
									case 'tel':
									case 'text':
									case 'url':
						?>
										<p>
											<label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_attr($field['label']); ?><span id="<?php echo esc_attr($field['id']); ?>-response" class="validation-response"></span></label>
											<input name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" class="<?php echo esc_attr($field['class']); ?> <?php echo esc_attr($field['id']); ?>" type="<?php echo esc_attr($field['type']); ?>" value="<?php echo esc_attr($value); ?>" />
										</p>
									<?php
										break;
									case 'checkbox':
									?>
										<p>
											<input name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" class="<?php echo esc_attr($field['class']); ?> <?php echo esc_attr($field['id']); ?>" type="checkbox" value="yes" <?php checked('yes', $value); ?> />
											<label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_attr($field['label']); ?><span id="<?php echo esc_attr($field['id']); ?>-response" class="validation-response"></span></label>
										</p>
									<?php
										break;
									case 'textarea':
									?>
										<p>
											<label for="<?php echo esc_attr($field['id']); ?>"><?php echo esc_attr($field['label']); ?><span id="<?php echo esc_attr($field['id']); ?>-response" class="validation-response"></span></label>
											<textarea name="<?php echo esc_attr($field['id']); ?>" id="<?php echo esc_attr($field['id']); ?>" class="<?php echo esc_attr($field['class']); ?> <?php echo esc_attr($field['id']); ?>"><?php echo esc_attr($value); ?></textarea>
										</p>
							<?php
										break;
								}
							}
						}

						if ($require_humanity) :
							?>
							<p class="ctdb-check-humanity-wrapper">
								<label><?php esc_html_e('Are you a human?', 'wp-discussion-board'); ?></label>
								<input name="ctdb_check_humanity" value="no" type="radio" /> <?php esc_html_e('No', 'wp-discussion-board'); ?><br />
								<input name="ctdb_check_humanity" value="yes" type="radio" /> <?php esc_html_e('Yes', 'wp-discussion-board'); ?>
							</p>
						<?php endif; ?>
						<p>
							<input type="hidden" name="ctdb_page" value="register" />
							<input type="hidden" name="ctdb_register_nonce" value="<?php echo esc_attr(wp_create_nonce('ctdb-register-nonce')); ?>" />

							<?php if ($recaptcha['add_recaptcha'] === true) { ?>
								<?php if ($recaptcha['type'] === 'invisible') { ?>
									<input id="ctdb_registration_form_btn" type="submit" value="<?php esc_html_e('Register Your Account', 'wp-discussion-board'); ?>" />
								<?php } else { ?>
						<div id="ctdb_registration_form_btn" class="g-recaptcha" data-sitekey="<?= $recaptcha['sitekey'] ?>"></div>
						<br />
						<input type="submit" value="<?php esc_html_e('Register Your Account', 'wp-discussion-board'); ?>" />
					<?php } ?>
					<script src="https://www.google.com/recaptcha/api.js?onload=ctdb_render_recaptcha&render=explicit"></script>
					<script>
						function ctdb_recaptcha_register_cb(token) {
							// document.getElementById("ctdb_registration_form").submit();
						}

						function ctdb_render_recaptcha() {
							let args_log = {
								sitekey: '<?= $recaptcha['sitekey'] ?>',
								callback: function(token) {
									// document.getElementById("ctdb_login_form").submit();
								},
								action: 'submit'
							};

							let args_reg = {
								sitekey: '<?= $recaptcha['sitekey'] ?>',
								callback: ctdb_recaptcha_register_cb,
								action: 'submit'
							};

							if (document.getElementById('ctdb_login_submit')) {
								window.ctdblogwidgetid = grecaptcha.render('ctdb_login_submit', args_reg);
							}

							window.ctdbregwidgetid = grecaptcha.render('ctdb_registration_form_btn', args_log);

							<?php if ($class == 'active-section') { ?>
								document.querySelector('#ctdb_registration_form .grecaptcha-badge').setAttribute('style', `
							width: 256px; height: 60px; display: block; transition: right 0.3s ease 0s; position: fixed; bottom: 14px; right: -186px; box-shadow: gray 0px 0px 5px; border-radius: 2px; overflow: hidden;
							`);
							<?php } ?>
						}

						function ctdb_recaptcha_reset(widget_id) {
							grecaptcha.reset(widget_id);
						}

						document.querySelector('.ctdb-header[data-form-id="ctdb-registration-wrap"]').addEventListener('click', function() {
							ctdb_recaptcha_reset(window.ctdbregwidgetid);
							document.querySelector('#ctdb_registration_form .grecaptcha-badge').setAttribute('style', `
							width: 256px; height: 60px; display: block; transition: right 0.3s ease 0s; position: fixed; bottom: 14px; right: -186px; box-shadow: gray 0px 0px 5px; border-radius: 2px; overflow: hidden;
							`);
						});

						if (document.querySelector('.ctdb-header[data-form-id="ctdb-login-wrap"]')) {
							document.querySelector('.ctdb-header[data-form-id="ctdb-login-wrap"]').addEventListener('click', function() {
								ctdb_recaptcha_reset(window.ctdblogwidgetid);
								document.querySelector('#ctdb_login_form .grecaptcha-badge').setAttribute('style', `
							width: 256px; height: 60px; display: block; transition: right 0.3s ease 0s; position: fixed; bottom: 14px; right: -186px; box-shadow: gray 0px 0px 5px; border-radius: 2px; overflow: hidden;
							`);
							});
						}
					</script>
				<?php } else { ?>
					<input type="submit" value="<?php esc_html_e('Register Your Account', 'wp-discussion-board'); ?>" />
				<?php } ?>

				</p>

				<p>
					<span id="ctdb_user_form-response" class="validation-response"><small><?php esc_html_e('Please make sure all required fields are filled out.', 'wp-discussion-board'); ?></small></span>
				</p>
					</fieldset>

					<script>
						// Do inline validation for username
						jQuery(document).ready(function($) {
							$(this).parents('form').find('#ctdb_user_form-response').hide();
							// On focusout so that we only evaluate inputs once completed
							// Send current so that we only evaluate current input
							$('#ctdb-registration-wrap input').on('focusout', function() {
								$(this).parents('form').find('#ctdb_user_form-response').hide();
								var required = 'false';
								if ($(this).hasClass('required')) {
									required = 'true';
								}
								// Clear the field validation each time
								var validateID = $(this).attr('id');
								$(this).removeClass('valid invalid');
								$('#' + validateID + '-response').html('');
								var data = {
									'action': 'ajax_validation',
									'current': $(this).attr('id'),
									'val': $(this).val(),
									'required': required,
									'login': $('.ctdb_user_login').val(),
									'email': $('#ctdb-registration-wrap #ctdb_user_email').val(),
									'pass': $('#ctdb-registration-wrap #ctdb_user_pass').val(),
									'confirm': $('#ctdb-registration-wrap #ctdb_user_pass_confirm').val(),
									'security': "<?php echo wp_create_nonce('validation_nonce'); ?>",
									'dataType': 'json'
								};
								$.post(ajaxurl, data, function(response) {
									response = JSON.parse(response);
									for (var i = 0; i < response.length; i++) {
										if (response[i]['status'] == 'error') {
											var id = response[i]['id'];
											$('.' + id).removeClass('valid');
											$('.' + id).addClass('invalid');
											$('#' + id + '-response').html('<small> - ' + response[i]['message'] + '</small>');
										} else if (response[i]['status'] == 'ok') {
											var id = response[i]['id'];
											$('.' + id).removeClass('invalid');
											$('.' + id).addClass('valid');
											if (response[i]['message']) {
												$('#' + id + '-response').html('<small> - ' + response[i]['message'] + '</small>');
											} else {
												$('#' + id + '-response').html('');
											}
										}
									}
								});
							});

							$('#ctdb_registration_form').submit(function(event) {
								if ($(this).find('input.invalid').length > 0) {
									$(this).find('#ctdb_user_form-response').show();
									event.preventDefault();
								}
							});

						});
					</script>

					<?php do_action('google_invre_render_widget_action'); ?>

				</form>
			</div><!-- .ctdb-form-section .-->

		<?php

			return ob_get_clean();
		}

		public function ajax_validation_callback()
		{
			check_ajax_referer('validation_nonce', 'security');
			$login_name  = sanitize_text_field($_POST['login']);
			$val         = $_POST['val'];
			$email       = $_POST['email'];
			$pass        = sanitize_text_field($_POST['pass']);
			$confirm     = sanitize_text_field($_POST['confirm']);
			$is_required = ($_POST['required']);
			$current     = isset($_POST['current']) ? $_POST['current'] : '';
			$response    = array();
			if ($current == 'ctdb_user_login') {
				if (username_exists($login_name) && !empty($login_name)) {
					$response[] = array(
						'id'      => 'ctdb_user_login',
						'status'  => 'error',
						'message' => __('That username already exists', 'wp-discussion-board'),
					);
				} elseif (!validate_username($login_name) && !empty($login_name)) {
					$response[] = array(
						'id'      => 'ctdb_user_login',
						'status'  => 'error',
						'message' => __('Only use lower case alphanumeric characters', 'wp-discussion-board'),
					);
				} elseif (empty($login_name) && $is_required == true) {
					$response[] = array(
						'id'      => $current,
						'status'  => 'error',
						'message' => __('This field is required', 'wp-discussion-board'),
					);
				} elseif ((!empty($login_name))) {
					$response[] = array(
						'id'      => 'ctdb_user_login',
						'status'  => 'ok',
						'message' => __('Looks good', 'wp-discussion-board'),
					);
				}
			} elseif ($current == 'ctdb_user_email') {
				if (!is_email($val) && !empty($val)) {
					$response[] = array(
						'id'      => 'ctdb_user_email',
						'status'  => 'error',
						'message' => __('That email doesn\'t look valid', 'wp-discussion-board'),
					);
				} elseif (email_exists($val)) {
					$response[] = array(
						'id'      => 'ctdb_user_email',
						'status'  => 'error',
						'message' => __('That email has already been used', 'wp-discussion-board'),
					);
				} elseif (empty($val) && $is_required == 'true') {
					$response[] = array(
						'id'      => $current,
						'status'  => 'error',
						'message' => __('This field is required', 'wp-discussion-board'),
					);
				} elseif (!empty($val)) {
					$response[] = array(
						'id'      => 'ctdb_user_email',
						'status'  => 'ok',
						'message' => __('That email looks good', 'wp-discussion-board'),
					);
				}
			} elseif ($current == 'ctdb_user_pass' || $current == 'ctdb_user_pass_confirm') {
				if ($confirm == $pass && !empty($confirm) && !empty($pass)) {
					$response[] = array(
						'id'      => 'ctdb_user_pass_confirm',
						'status'  => 'ok',
						'message' => __('Passwords match', 'wp-discussion-board'),
					);
					$response[] = array(
						'id'      => 'ctdb_user_pass',
						'status'  => 'ok',
						'message' => '',
					);
				}
				if ($current == 'ctdb_user_pass_confirm') {
					if ($confirm != $pass) {
						$response[] = array(
							'id'      => 'ctdb_user_pass_confirm',
							'status'  => 'error',
							'message' => __('The passwords don\'t match', 'wp-discussion-board'),
						);
					}
				}
				if (empty($val) && $is_required == 'true') {
					$response[] = array(
						'id'      => $current,
						'status'  => 'error',
						'message' => __('This field is required', 'wp-discussion-board'),
					);
				} else {
					$response[] = array(
						'id'      => $current,
						'status'  => '',
						'message' => '',
					);
				}
			} elseif (empty($val) && $is_required == 'true') {
				$response[] = array(
					'id'      => $current,
					'status'  => 'error',
					'message' => __('This field is required', 'wp-discussion-board'),
				);
			} elseif ($current != 'ctdb_user_pass' && !empty($val)) {
				$response[] = array(
					'id'      => $current,
					'status'  => 'ok',
					'message' => __('Looks good', 'wp-discussion-board'),
				);
			}

			echo wp_json_encode($response);

			wp_die();
		}

		/**
		 * Return the registration form fields
		 * @since 1.3.0
		 * @deprecated Use ctdb_registration_form_fields() instead
		 */
		public function registration_form_fields_array()
		{

			$form = array(
				'ctdb_user_login'        => array(
					'id'    => 'ctdb_user_login',
					'label' => __('Username', 'wp-discussion-board'),
					'field' => 'input',
					'type'  => 'text',
					'class' => 'required',
				),
				'ctdb_user_email'        => array(
					'id'    => 'ctdb_user_email',
					'label' => __('Email', 'wp-discussion-board'),
					'field' => 'input',
					'type'  => 'email',
					'class' => 'required',
				),
				'ctdb_user_first'        => array(
					'id'    => 'ctdb_user_first',
					'label' => __('First Name', 'wp-discussion-board'),
					'field' => 'input',
					'type'  => 'text',
					'class' => 'required',
				),
				'ctdb_user_last'         => array(
					'id'    => 'ctdb_user_last',
					'label' => __('Last Name', 'wp-discussion-board'),
					'field' => 'input',
					'type'  => 'text',
					'class' => 'required',
				),
				'ctdb_user_pass'         => array(
					'id'    => 'ctdb_user_pass',
					'label' => __('Password', 'wp-discussion-board'),
					'field' => 'input',
					'type'  => 'password',
					'class' => 'required',
				),
				'ctdb_user_pass_confirm' => array(
					'id'    => 'ctdb_user_pass_confirm',
					'label' => __('Password again', 'wp-discussion-board'),
					'field' => 'input',
					'type'  => 'password',
					'class' => 'required',
				),
			);

			// See ctdb_get_protected_registration_fields() for protected fields
			$form = apply_filters('ctdb_registration_form_fields', $form);

			return $form;
		}

		/**
		 * Get the login form fields
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function login_form_fields($login_registration = true)
		{

			$class = '';

			//check if recaptcha is available
			$recaptcha = $this->check_recaptcha();

			ob_start();

			if (empty($_POST['ctdb_page']) || $_POST['ctdb_page'] != 'register') {
				$class = 'active-section';
			}
		?>

			<div id="ctdb-login-wrap" class="ctdb-form-section <?php echo $class; ?>">

				<?php
				// Show any error messages after form submission
				$this->ctdb_show_error_messages();
				?>

				<?php // Password reset notification message 
				?>
				<?php // @todo better notification method 
				?>
				<?php if (isset($_GET['action']) && $_GET['action'] == 'passwordrequest') { ?>
					<p class="ctdb-success"><?php _e('Please check your inbox for an email containing a link to reset your password.', 'wp-discussion-board'); ?></p>
				<?php } ?>

				<form id="ctdb_login_form" class="ctdb-form" action="" method="post">

					<fieldset>
						<p>
							<label for="ctdb_user_login"><?php _e('Username', 'wp-discussion-board'); ?></label>
							<input name="ctdb_user_login" id="ctdb_user_login" class="required" type="text" />
						</p>
						<p>
							<label for="ctdb_user_pass"><?php _e('Password', 'wp-discussion-board'); ?></label>
							<input name="ctdb_user_pass" id="ctdb_user_pass" class="required" type="password" />
						</p>
						<p>
							<input type="hidden" name="ctdb_login_nonce" value="<?php echo wp_create_nonce('ctdb-login-nonce'); ?>" />

							<?php if ($recaptcha['add_recaptcha'] === true) { ?>
								<?php if ($recaptcha['type'] === 'invisible') { ?>
									<input id="ctdb_login_submit" type="submit" value="<?php _e('Log in', 'wp-discussion-board'); ?>" />
								<?php } else { ?>
						<div id="ctdb_login_submit" class="g-recaptcha" data-sitekey="<?= $recaptcha['sitekey'] ?>"></div>
						<br />
						<input type="submit" value="<?php _e('Log in', 'wp-discussion-board'); ?>" />
					<?php } ?>
					<?php if ($login_registration === false) { ?>
						<script src="https://www.google.com/recaptcha/api.js?onload=ctdb_render_recaptcha&render=explicit"></script>
						<script>
							function ctdb_recaptcha_cb(token) {
								// document.getElementById("ctdb_login_form").submit();
							}

							function ctdb_render_recaptcha() {
								<?php if ($recaptcha['type'] === 'invisible') { ?>
									window.ctdblogwidgetid = grecaptcha.render('ctdb_login_submit', {
										sitekey: '<?= $recaptcha['sitekey'] ?>',
										callback: ctdb_recaptcha_cb,
										action: 'submit'
									});
								<?php } else { ?>
									window.ctdblogwidgetid = grecaptcha.render('ctdb_login_submit', {
										sitekey: '<?= $recaptcha['sitekey'] ?>',
										action: 'submit'
									});
								<?php } ?>
							}
						</script>
					<?php } ?>

				<?php } else { ?>
					<input id="ctdb_login_submit" type="submit" value="<?php _e('Log in', 'wp-discussion-board'); ?>" />
				<?php } ?>
				</p>
					</fieldset>

				</form>

				<p><a href="<?php echo wp_lostpassword_url(get_permalink() . '?action=passwordrequest'); ?>"><?php _e('Lost password?', 'wp-discussion-board'); ?></a>
				<p>

			</div>

<?php
			return ob_get_clean();
		}

		/**
		 * Logs user in after submitting form
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function login_user()
		{
			if (isset($_POST['ctdb_user_login']) && isset($_POST['ctdb_login_nonce']) && wp_verify_nonce($_POST['ctdb_login_nonce'], 'ctdb-login-nonce')) {

				//before we check from third party filter for recaptcha
				if (isset($_POST['g-recaptcha-response'])) {
					if (empty($_POST['g-recaptcha-response'])) {
						$this->ctdb_errors()->add('reCaptcha', __('Your login failed the reCaptcha check.', 'wp-discussion-board'));
						$errors = $this->ctdb_errors()->get_error_messages();
						return;
					}

					$is_valid = apply_filters('discussion_board_validate_recaptcha', true, $_POST['g-recaptcha-response']);
					if (!$is_valid) {
						// reCaptcha failed
						// Maybe write a kind note here?
						$this->ctdb_errors()->add('reCaptcha', __('Your login failed the reCaptcha check.', 'wp-discussion-board'));
						$errors = $this->ctdb_errors()->get_error_messages();
						return;
					}
				}

				// This returns the user ID and other info from the user name.
				$user = $this->get_user(sanitize_text_field(wp_unslash($_POST['ctdb_user_login'])));

				if (!$user) {
					// if the user name doesn't exist
					$this->ctdb_errors()->add('login_error', __('You\'ve entered an invalid combination of username and password.', 'wp-discussion-board'));
					return;
				}

				$user_roles = $user->roles;

				if ($user_roles) {
					$user_role = array_shift($user_roles);
				}

				if ($user_role == 'pending') {
					// User hasn't activated their registration yet
					$this->ctdb_errors()->add('unactivated', __('You haven\'t activated your registration yet. Please check your email for an activation link', 'wp-discussion-board'));
				}

				if (empty($_POST['ctdb_user_pass'])) {
					// if no password was entered
					$this->ctdb_errors()->add('empty_password', __('Please enter a password', 'wp-discussion-board'));
				}

				// check the user's login with their password
				if (!wp_check_password($_POST['ctdb_user_pass'], $user->user_pass, $user->ID)) {
					// if the password is incorrect for the specified user
					$this->ctdb_errors()->add('login_error', __('You\'ve entered an invalid combination of username and password.', 'wp-discussion-board'));
				}

				// retrieve all error messages
				$errors = $this->ctdb_errors()->get_error_messages();

				// only log the user in if there are no errors
				if (empty($errors)) {
					$options = get_option('ctdb_options_settings');
					// Check what page has been set to redirect to
					$redirect_to = '';
					if (!empty($options['redirect_to_page'])) {
						$redirect_to = $options['redirect_to_page'];
					}

					wp_set_auth_cookie($user->ID, false);
					wp_set_current_user($user->ID, $_POST['ctdb_user_login']);
					do_action('wp_login', $_POST['ctdb_user_login'], $user);

					$this->check_user_permission();

					if (current_user_can('manage_plugins')) {

						// Redirect admins to the dashboard
						$url = get_admin_url();
					} elseif (!empty($redirect_to)) {
						// Redirect to the correct page if user is not an admin
						$url = esc_url_raw(add_query_arg('redirecting', 'true', get_permalink($redirect_to)));
					} else {
						// Just log in
						$url = esc_url_raw(add_query_arg('redirecting', 'true', get_permalink()));
					}
					wp_redirect($url);
					exit;
				}
			}
		}

		/**
		 * Get the user based on the login form entry.
		 *
		 * @since 2.4.4
		 *
		 * @param string $user_string Email or user name of the user.
		 */
		public function get_user($user_string)
		{
			if (is_email($user_string)) {
				return get_user_by('email', $user_string);
			}

			return get_user_by('login', $user_string);
		}

		/**
		 * Registers user after submitting form
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function register_new_user()
		{

			global $post;

			if (empty($_POST['ctdb_register_nonce'])) {
				return;
			}

			//before we check from third party filter for recaptcha
			if (isset($_POST['g-recaptcha-response'])) {
				if (empty($_POST['g-recaptcha-response'])) {
					$this->ctdb_errors()->add('reCaptcha', __('Your registration failed the reCaptcha check.', 'wp-discussion-board'));
					$errors = $this->ctdb_errors()->get_error_messages();
					return;
				}

				$is_valid = apply_filters('discussion_board_validate_recaptcha', true, $_POST['g-recaptcha-response']);
				if (!$is_valid) {
					// reCaptcha failed
					// Maybe write a kind note here?
					$this->ctdb_errors()->add('reCaptcha', __('Your registration failed the reCaptcha check.', 'wp-discussion-board'));
					$errors = $this->ctdb_errors()->get_error_messages();
					return;
				}
			}

			// Need to check if we're running reCaptcha
			if (class_exists('InvisibleReCaptcha')) {

				$is_valid = apply_filters('google_invre_is_valid_request_filter', true);
				if (!$is_valid) {
					// reCaptcha failed
					// Maybe write a kind note here?
					$this->ctdb_errors()->add('reCaptcha', __('Your registration failed the reCaptcha check.', 'wp-discussion-board'));
					$errors = $this->ctdb_errors()->get_error_messages();
					return;
				}
			}

			$options = get_option('ctdb_options_settings');

			// Check if we need to add a humanity check to the form
			if (isset($options['check_human'])) {
				$require_humanity = $options['check_human'];
			} else {
				$require_humanity = false;
			}

			if ($require_humanity && isset($_POST['ctdb_check_humanity']) && $_POST['ctdb_check_humanity'] == 'yes') {
				$is_human = true;
			} elseif ($require_humanity && (!isset($_POST['ctdb_check_humanity']) || $_POST['ctdb_check_humanity'] == 'no')) {
				$is_human = false;
			} else {
				$is_human = true;
			}

			if (isset($_POST['ctdb_user_login']) && $is_human && wp_verify_nonce($_POST['ctdb_register_nonce'], 'ctdb-register-nonce')) {

				$user_login = $_POST['ctdb_user_login'];
				$user_email = $_POST['ctdb_user_email'];
				$user_first = '';
				if (isset($_POST['ctdb_user_first'])) {
					$user_first = $_POST['ctdb_user_first'];
				}
				$user_last = '';
				if (isset($_POST['ctdb_user_last'])) {
					$user_last = $_POST['ctdb_user_last'];
				}
				$user_pass    = $_POST['ctdb_user_pass'];
				$pass_confirm = $_POST['ctdb_user_pass_confirm'];
				if (isset($_POST['ctdb_url'])) {
					$website = $_POST['ctdb_url'];
				} else {
					$website = '';
				}
				if (isset($_POST['ctdb_bio'])) {
					$bio = $_POST['ctdb_bio'];
				} else {
					$bio = '';
				}

				/**
				 * Check blacklisted email addresses and fail silently if found
				 * @since 2.2.0
				 */
				$user_options = get_option('ctdb_user_settings');
				if (isset($user_options['email_blacklist'])) {
					// Convert list to array
					$email_address = $_POST['ctdb_user_email'];
					if ($email_address) {
						// Get the email domain
						$email_split = explode('@', $email_address);
						$domain      = '@' . $email_split[1];

						// Split the textarea by line break
						$blacklisted = preg_split('/[\n\r]+/', ($user_options['email_blacklist']));
						if (!empty($blacklisted) && is_array($blacklisted)) {
							foreach ($blacklisted as $blacklisted_email) {
								// Look for wildcards, e.g. *.top
								if (strpos($blacklisted_email, '*') !== false) {
									$blacklisted_string = substr($blacklisted_email, strpos($blacklisted_email, '*') + 1);
									if (strpos($email_address, $blacklisted_string) !== false) {
										// If the email address contains the string we don't like, then bounce
										return;
									}
								}
								// Remove any accidental spaces
								$blacklisted_email = str_replace(' ', '', $blacklisted_email);
								if ($email_address == $blacklisted_email || $domain == $blacklisted_email) {
									// This email is blacklisted so don't allow registration
									return;
								}
							}
						}
					}
				}

				if (username_exists($user_login)) {
					// Username already registered
					$this->ctdb_errors()->add('username_unavailable', __('That username has already been taken. Please choose another.', 'wp-discussion-board'));
				}
				if (!validate_username($user_login)) {
					// invalid username
					$this->ctdb_errors()->add('username_invalid', __('The username you selected was not valid. Please only use lowercase alphanumeric characters.', 'wp-discussion-board'));
				}
				if ($user_login == '') {
					// empty username
					$this->ctdb_errors()->add('username_empty', __('Please enter your username.', 'wp-discussion-board'));
				}
				if (!is_email($user_email)) {
					//invalid email
					$this->ctdb_errors()->add('email_invalid', __('Please check you have entered a valid email address.', 'wp-discussion-board'));
				}
				if (email_exists($user_email)) {
					//Email address already registered
					$this->ctdb_errors()->add('email_used', __('That email address has already been registered.', 'wp-discussion-board'));
				}
				if ($user_pass == '') {
					// password is empty
					$this->ctdb_errors()->add('password_empty', __('Please enter a password.', 'wp-discussion-board'));
				}
				if ($user_pass != $pass_confirm) {
					// passwords do not match
					$this->ctdb_errors()->add('password_mismatch', __('Please check that the passwords match.', 'wp-discussion-board'));
				}

				// Check additional fields for errors
				$extra_fields = ctdb_get_extra_fields();

				if ($extra_fields) {
					foreach ($extra_fields as $field) {
						if ($field['class'] == 'required' && empty($_POST[$field['id']])) {
							$this->ctdb_errors()->add('field_empty', sprintf('%s %s', $field['label'], __('not set', 'wp-discussion-board')));
						}
					}
				}

				$errors = $this->ctdb_errors()->get_error_messages();

				// Only create the user if there are no errors
				if (empty($errors)) {

					$require_activation = ctdb_get_activation_setting();

					// Define role for new users
					if ($require_activation != 'none') {
						// Set the user role to pending if we require user activation
						$register_role = 'pending';
					} elseif (!empty($options['new_user_role'])) {
						$register_role = $options['new_user_role'];
					} else {
						$register_role = 'subscriber';
					}

					// Register user without activation
					// Sanitization is handled by wp_insert_user
					$new_user_id = wp_insert_user(
						array(
							'user_login'      => sanitize_user($user_login),
							'user_pass'       => $user_pass,
							'user_email'      => sanitize_email($user_email),
							'first_name'      => sanitize_text_field($user_first),
							'last_name'       => sanitize_text_field($user_last),
							'user_registered' => date('Y-m-d H:i:s'),
							'role'            => sanitize_text_field($register_role),
							'user_url'        => esc_url($website),
							'description'     => sanitize_text_field($bio),
						)
					);

					if (!is_wp_error($new_user_id)) {

						ctdb_register_extra_fields($new_user_id, $_POST);

						/**
						 * Send an email to the admin alerting them of the registration
						 * @hooked ctdb_get_activation_setting
						 */
						do_action('ctdb_email_admin_new_registration', $new_user_id, $require_activation);

						/**
						 * If we require new users to activate their accounts
						 * @hooked ctdb_email_user_after_registration
						 */
						do_action('ctdb_email_user_activation_key', $new_user_id, $user_email, $require_activation);

						add_filter('the_content', array($this, 'registration_success'));
					}
				}
			}
		}

		/**
		 * Checks for user activation
		 * @since 1.0.0
		 */
		public function activate_new_user()
		{

			if (!isset($_GET['activate_code']) || !isset($_GET['user_id'])) {
				return;
			}
			if ($_GET['activate_code'] && $_GET['user_id']) {

				// An activation link has been clicked
				$user_id = intval($_GET['user_id']);

				// Get the activation code of the user ID in the URL
				$user_activation = get_user_meta($user_id, 'activate_key', true);

				$is_pending = false;
				$user       = new WP_User($user_id);
				foreach ($user->roles as $role) {
					if ($role == 'pending') {
						$is_pending = true;
					}
				}

				// Only update if role is still pending
				if ($is_pending && $_GET['activate_code'] == $user_activation) {

					// The user ID and activation code match so we'll register this user
					$options = get_option('ctdb_user_settings');
					$role    = $options['new_user_role'];

					$user_update = wp_update_user(
						array(
							'ID'   => $user_id,
							'role' => $role,
						)
					);

					if (!is_wp_error($user_update)) {
						add_filter('the_content', array($this, 'activate_success'));
					}
				}
			}
		}

		/**
		 * Successful registration
		 * @since 1.0.0
		 */
		public function registration_success($content)
		{

			$require_activation = ctdb_get_activation_setting();

			// Additional text if activation is required
			if ($require_activation != 'none') {
				$success = __('Please check your inbox for an activation email - if you don\'t see an email from us, please remember to check your spam folder.', 'wp-discussion-board');
			} else {
				$success = __('Thank you for registering. You may now log in.', 'wp-discussion-board');
			}

			$success = apply_filters('ctdb_filter_registration_message', $success, $require_activation);

			// Place the message at the start of the content
			$content = sprintf('<p class="ctdb-success">%s</p>%s', $success, $content);

			return $content;
		}

		/**
		 * Successful activation from link
		 * @since 1.0.0
		 */
		public function activate_success($content)
		{
			$success = '<p class="ctdb-success">' . __('You have activated your account successfully. You can now log in.', 'wp-discussion-board') . '</p>';
			$content = $success . $content;
			return $content;
		}

		/**
		 * Redirect to log-in form
		 * @since 1.0.0
		 */
		public function redirect_to_login_page()
		{
			global $pagenow;
			$options = get_option('ctdb_options_settings');

			// Should we redirect this request to the login page?
			if (!$this->redirect_login_action()) {
				return;
			}

			// If we're heading towards wp-login.php and our settings are right
			if ('wp-login.php' === $pagenow && !empty($options['hide_wp_login']) && !empty($options['frontend_login_page'])) {
				$frontend_login_page = $options['frontend_login_page'];

				if ('publish' !== get_post_status($frontend_login_page)) {
					return;
				}

				$redirect_url = get_permalink($frontend_login_page);

				// Check that the redirect URL is a valid page.
				if (empty($redirect_url)) {
					return;
				}

				$redirect_url = esc_url_raw(add_query_arg('login', 'bounced', $redirect_url));
				wp_redirect($redirect_url);
				exit;
			}
		}

		/**
		 * Checks to see if a request for the login form should be redirected to our
		 * custom login form. The login page in WordPress performs other actions such
		 * as resetting passwords which we do not want to redirect so that WordPress
		 * can handle that request successfully.
		 *
		 * @since 2.4.4
		 *
		 * @return bool
		 */
		public function redirect_login_action()
		{
			$action = '';

			if (!empty($_GET['action'])) {
				$action = sanitize_text_field(wp_unslash($_GET['action']));
			}

			// Certain actions on the login form should not invoke a redirect.
			if (in_array($action, array('logout', 'lostpassword', 'rp', 'resetpass', 'postpass'))) {
				return false;
			}

			return true;
		}

		/**
		 * Redirect to front end log-in form if failed log-in
		 * @since 1.0.0
		 * @https://pippinsplugins.com/redirect-to-custom-login-page-on-failed-login/
		 */
		public function login_fail($username)
		{
			// If we've opted to hide the wp-login.php page and have an alternative front-end page set
			$options = get_option('ctdb_options_settings');
			if (isset($options['hide_wp_login']) && isset($options['frontend_login_page'])) {
				// Where did the post submission come from?
				$referrer = $_SERVER['HTTP_REFERER'];
				// If there's a valid referrer, and it's not the default log-in screen
				if (!empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
					$redirect_url = esc_url(add_query_arg('login', 'failed', get_permalink($options['frontend_login_page'])));
					wp_redirect($redirect_url);
					exit;
				}
			}
		}

		/**
		 * Tracks error messages
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function ctdb_errors()
		{
			static $wp_error;
			return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
		}

		/**
		 * Displays error messages
		 * @since 1.0.0
		 * @credit https://pippinsplugins.com/creating-custom-front-end-registration-and-login-forms/
		 */
		public function ctdb_show_error_messages()
		{

			if ($codes = $this->ctdb_errors()->get_error_codes()) {

				echo '<div class="ctdb-errors">';
				// Loop error codes and display errors
				foreach ($codes as $code) {
					$message = $this->ctdb_errors()->get_error_message($code);
					echo '<span class="error"><strong>' . __('Error', 'wp-discussion-board') . '</strong>: ' . $message . '</span><br/>';
				}
				echo '</div>';
			}
		}

		/**
		 * Filter the_content with error messages
		 * @since 2.3.0
		 */
		public function get_error_messages($content)
		{
			if ($codes = $this->ctdb_errors()->get_error_codes()) {
				$return = '<div class="ctdb-errors">';
				// Loop error codes and display errors
				foreach ($codes as $code) {
					$message = $this->ctdb_errors()->get_error_message($code);
					$return .= '<span class="error"><strong>' . __('Error', 'wp-discussion-board') . '</strong>: ' . $message . '</span><br/>';
				}
				$return .= '</div>';

				return $return . $content;
			}
			return $content;
		}

		public function set_html_content_type()
		{
			return 'text/html';
		}

		public function check_recaptcha()
		{
			$recaptcha_data = [
				'add_recaptcha' => false,
			];
			//check if pro version is available
			if (defined('DB_PRO_VERSION')) {
				//check if recaptcha is available
				$recaptcha = get_option('ctdb_recaptcha_settings');
				if ($recaptcha !== false) {
					if (
						isset($recaptcha['recaptcha_sitekey']) && !empty($recaptcha['recaptcha_sitekey']) &&
						isset($recaptcha['recaptcha_secretkey']) && !empty($recaptcha['recaptcha_secretkey'])
					) {
						$recaptcha_data['add_recaptcha'] = true;
						$recaptcha_data['type'] = $recaptcha['recaptcha_type'] ?? 'invisible';
						$recaptcha_data['sitekey'] = $recaptcha['recaptcha_sitekey'];
						$recaptcha_data['secretkey'] = $recaptcha['recaptcha_secretkey'];
					}
				}
			}

			return $recaptcha_data;
		}

		public function validate_recaptcha_response($is_valid, $token)
		{
			$recaptcha = get_option('ctdb_recaptcha_settings');

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, ['secret' => $recaptcha['recaptcha_secretkey'], 'response' => $token]);

			$resp = curl_exec($ch);
			if (curl_errno($ch) === 0) {
				$response = json_decode($resp);
				if ($response->success === true) {
					$is_valid = true;
				} else {
					$is_valid = false;
				}
			} else {
				$is_valid = false;
			}

			curl_close($ch);
			return $is_valid;
		}
	}
}
