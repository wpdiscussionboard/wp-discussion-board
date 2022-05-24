<?php
/**
 * Handles the new topic and reply editor.
 *
 * @since 2.5
 *
 * @package WPDiscusisonBoard
 */

namespace WPDiscussionBoard;

/**
 * Class Editor
 *
 * @since 2.5
 */
class Editor {
	/**
	 * Handle for the editor scripts.
	 *
	 * @since 2.5
	 */
	const SCRIPT_HANDLE = 'wpdbd-editor';

	/**
	 * Init
	 *
	 * @since 2.5
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_editor_styles' ) );
	}

	/**
	 * Enqueue the editor scripts.
	 *
	 * @since 2.5
	 *
	 * @return void
	 */
	public function enqueue_editor_scripts() {
		if ( ! is_singular( 'discussion-topics' ) ) {
			return;
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			WPDBD_PLUGIN_URL . '/assets/js/build/topic.js',
			array( 'wp-element' ),
			WPDBD_PLUGIN_VERSION,
			true
		);
	}

	public function enqueue_editor_styles() {
		if ( ! is_singular( 'discussion-topics' ) ) {
			return;
		}

		wp_enqueue_style(
			self::SCRIPT_HANDLE,
			WPDBD_PLUGIN_URL . '/assets/js/build/topic.css',
		);
	}
}
