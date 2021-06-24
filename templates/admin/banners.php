<div class="ctdb-banners">
	<?php if ( ! defined( 'DB_PRO_VERSION' ) ) : ?>
		<?php
		$license_key = get_option( Admin_License::FREE_LICENSE_OPTION_KEY );
		if ( empty( $license_key ) ) :
			?>
			<div class="ctdb-banner">
				<?php include WPDBD_PLUGIN_DIR . '/templates/admin/free-license-form.php'; ?>
			</div>
		<?php endif; ?>

		<div class="ctdb-banner">
			<a target="_blank" href="https://wpdiscussionboard.com/?utm_source=wp_plugin&utm_medium=banner&utm_content=sidebar&utm_campaign=upgrade">
				<img src="<?php echo esc_url( WPDBD_PLUGIN_URL . 'assets/images/discussion-board-banner-ad.png' ); ?>" alt="Discussion Board Pro">
			</a>
		</div>
	<?php endif; ?>
</div>