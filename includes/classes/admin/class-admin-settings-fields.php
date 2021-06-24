<?php
/**
 * Admin settings fields.
 *
 * @since 3.0
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPDiscussionBoard\Admin\Admin_Settings_Fields' ) ) {
	class Admin_Settings_Fields {
		/**
		 * Checkbox callback.
		 *
		 * @param $args array Callback arguments.
		 *
		 * @since 2.2.1
		 */
		public static function checkbox( $args ) {
			$options = get_option( $args['section'] );
			$value   = '';

			if ( isset( $options[ $args['id'] ] ) ) {
				$value = $options[ $args['id'] ];
			}

			$checked = ! empty( $value ) ? checked( 1, $value, false ) : '';
			?>
            <input type="checkbox" name="<?php echo esc_attr( $args['section'] ); ?>[<?php echo esc_attr( $args['id'] ); ?>]" <?php echo esc_html( $checked ); ?> value="1" />
			<?php
			if ( isset( $args['description'] ) ) :
				?>
                <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
			endif;
		}

		/**
		 * Text callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public static function text( $args ) {
			$options = get_option( $args['section'] );
			$value   = '';

			if ( isset( $options[ $args['id'] ] ) ) {
				$value = $options[ $args['id'] ];
			}
			?>
            <input type="text" class="regular-text" name="<?php echo esc_attr( $args['section'] ); ?>[<?php echo esc_attr( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>"/>
			<?php
			if ( isset( $args['description'] ) ) :
				?>
                <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
			endif;
		}

		/**
		 * Render email blacklist form.
		 */
		public static function textarea( $args ) {
			$options = get_option( $args['section'] );
			$value   = '';

			if ( isset( $options[ $args['id'] ] ) ) {
				$value = $options[ $args['id'] ];
			}
			?>
            <textarea rows="10" style="width: 300px;" name="<?php echo esc_attr( $args['section'] ); ?>[<?php echo esc_attr( $args['id'] ); ?>]"><?php echo esc_attr( $value ); ?></textarea>
            <p class="description"><?php esc_html_e( 'Block specific email addresses(e.g. nasty@spammer.com) or entire email domains(e.g. @spammer.com) from registering. Add one address per line.', 'wp-discussion-board' ); ?></p>
			<?php
		}

		/**
		 * Email callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public static function email( $args ) {
			$options = get_option( $args['section'] );
			$value   = '';
			if ( isset( $options[ $args['id'] ] ) ) {
				$value = $options[ $args['id'] ];
			}
			?>
            <input type="email" class="regular-text" name="<?php echo esc_attr( $args['section'] ); ?>[<?php echo esc_attr( $args['id'] ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
			<?php
			if ( isset( $args['description'] ) ) :
				?>
                <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
			endif;
		}

		/**
		 * WYSIWYG callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public static function wysiwyg( $args ) {
			$options = get_option( $args['section'] );
			$value   = '';

			if ( isset( $options[ $args['id'] ] ) ) {
				$value = $options[ $args['id'] ];
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

			if ( isset( $args['description'] ) ) :
				?>
                <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
			endif;
		}

		/**
		 * Select callback.
		 *
		 * @param $args array Callback arguments.
		 */
		public static function select( $args ) {
			$options = get_option( $args['section'] );
			$setting = '';

			if ( isset( $options[ $args['id'] ] ) ) {
				$setting = $options[ $args['id'] ];
			}
			?>
            <select name="<?php echo esc_attr( $args['section'] ); ?>[<?php echo esc_attr( $args['id'] ); ?>]">
				<?php foreach ( $args['choices'] as $key => $value ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $setting, $key ); ?>><?php echo esc_html( $value ); ?></option>
				<?php } ?>
            </select>
			<?php
			if ( isset( $args['description'] ) ) :
				?>
                <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
			<?php
			endif;
		}

		/**
		 * Callback for pages select field.
		 *
		 * @param $args array Callback arguments.
		 */
		public static function select_pages( $args ) {
			$options = get_option( $args['section'] );
			$value   = '';

			if ( isset( $options[ $args['id'] ] ) ) {
				$value = $options[ $args['id'] ];
			}

			// Get all pages.
			$pages = get_pages();

			// Iterate through the pages.
			if ( ! empty( $pages ) ) :
				?>
				<select name='<?php echo esc_attr( $args['section'] ); ?>[<?php echo esc_attr( $args['id'] ); ?>]'>
					<option></option>
					<?php foreach ( $pages as $page ) { ?>
						<option value='<?php echo esc_attr( $page->ID ); ?>' <?php selected( $value, $page->ID ); ?>><?php echo esc_html( $page->post_title ); ?></option>
					<?php } ?>
				</select>
				<?php
			endif;

			if ( isset( $args['description'] ) ) :
				?>
				<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
				<?php
			endif;
		}

		/**
		 * Multiselect form.
         *
         * @since 3.0
		 */
		public static function multiselect( $args ) {
			$options = get_option( $args['section'] );
			$values  = array();

			if ( isset( $options[ $args['id'] ] ) ) {
				$values = $options[ $args['id'] ];
			}

			if ( ! empty( $args['choices'] ) ) :
                foreach ( $args['choices'] as $value => $name ) :
	                $checked = in_array( $value, $values, true ) ? 1 : '';
				?>
                    <input type="checkbox" name="<?php echo esc_attr( $args['section'] ); ?>[<?php echo esc_attr( $args['id'] ); ?>][]" <?php echo esc_html( $checked ); ?> value="1" />
                    <label for="<?php echo esc_attr( $args['section'] ); ?>"><?php echo esc_html( $name ); ?></label>
                    <br />
                <?php
                endforeach;
				if ( isset( $args['description'] ) ) :
					?>
                    <p class="description"><?php echo esc_html( $args['description'] ); ?></p>
				<?php
				endif;
			endif;
		}
	}
}
