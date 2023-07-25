<?php
/**
 * Form for generating a free license.
 *
 * @since 2.4
 */

namespace WPDiscussionBoard\Admin;

?>
<div class="ctdb-about-section cta">
	<form method="post">
		<h3><?php esc_html_e( 'Free License', 'wp-discussion-board' ); ?></h3>
		<p><?php esc_html_e( "You're currently using the free version of WP Discussion Board. To register a free license for the plugin, please fill in your email below. This is not required but helps us support you better.", 'wp-discussion-board' ); ?></p>
		<input type="text" name="email" placeholder="<?php esc_attr_e( 'Email Address', 'wp-discussion-board' ); ?>" />
		<?php wp_nonce_field( Admin_License::NONCE_ACTION, Admin_License::NONCE_NAME ); ?>
		<input type="submit" name="free_license_activator" value="Register Free License" class="button button-primary" />
		<p><small>* <?php esc_html_e( 'Your email is secure with us! We will only send you a single email with helpful resources to get you started.', 'wp-discussion-board' ); ?></small></p>
	</form>
</div>
