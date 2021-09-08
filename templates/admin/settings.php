<?php
/**
 * Settings page.
 *
 * @since 3.0
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

use WPDiscussionBoard\Bootstrap;

$admin         = Bootstrap::get_instance()->get_container( 'Admin\Admin' );
$settings_tabs = $admin->tabs;
$sections      = $admin->sections;
$current       = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore
$section       = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : key( $sections[ $current ] ); // phpcs:ignore
?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Discussion Board', 'wp-discussion-board' ); ?></h1>
	<?php settings_errors(); ?>
	<div class="ctdb-outer-wrap">
		<div class="ctdb-inner-wrap">
			<h2 class="nav-tab-wrapper">
				<?php
				foreach ( $settings_tabs as $setting_tab => $name ) :
					$class = ( $setting_tab === $current ) ? ' nav-tab-active' : '';
					?>
					<a class="nav-tab<?php echo esc_attr( $class ); ?>" href="?post_type=discussion-topics&page=discussion_board&tab=<?php echo esc_attr( $setting_tab ); ?>"><?php echo esc_html( $name ); ?></a>
					<?php
				endforeach;
				?>
			</h2>

			<?php if ( ! empty( $sections[ $current ] ) ) : ?>
			<div class="wp-clearfix">
				<ul class="subsubsub">
					<?php
					$count = 0;
					foreach ( $sections[ $current ] as $setting_section => $name ) :
						$count++;
						$class = ( $setting_section === $section ) ? 'current' : '';
						?>
					<li>
						<a class="<?php echo esc_attr( $class ); ?>" href="?post_type=discussion-topics&page=discussion_board&tab=<?php echo esc_attr( $current ); ?>&section=<?php echo esc_attr( $setting_section ); ?>">
							<?php echo esc_html( $name ); ?>
						</a>
						<?php if ( count( $sections[ $current ] ) !== $count ) : ?>
						|
						<?php endif; ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

			<form action="options.php" method="post">
				<?php settings_fields( 'wpdbd_' . strtolower( $current ) . '_' . strtolower( $section ) ); ?>
				<table class="form-table" role="presentation">
					<?php do_settings_fields( sprintf( 'wpdbd_%s', $current ), $section ); ?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- .ctdb-inner-wrap -->

		<?php //@todo: laod banners. ?>
	</div><!-- .ctdb-outer-wrap -->
</div><!-- .wrap -->
