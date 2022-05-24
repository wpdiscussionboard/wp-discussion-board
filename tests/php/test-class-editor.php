<?php
/**
 * Test the editor class.
 *
 * @since 2.5
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Tests;

use WPDiscussionBoard\Editor;

/**
 * Test class for Editor.
 *
 * @since 2.5
 */
class Test_Editor extends \WP_UnitTestCase {

	/**
	 * Instance under test.
	 *
	 * @since 2.5
	 */
	protected $instance;

	/**
	 * Setup the tests.
	 *
	 * @since 2.5
	 */
	protected function setUp() {
		$this->instance = new Editor();

		parent::setUp();
	}

	/**
	 * Test the init method
	 *
	 * @since 2.5
	 *
	 * @covers Editor::init()
	 */
	public function test_init() {
		$this->instance->init();

		$this->assertEquals( 10, has_action( 'wp_enqueue_scripts', array( $this->instance, 'enqueue_editor_scripts' ) ) );
	}

	/**
	 * Test the enqueue of editor scripts.
	 *
	 * @sine 2.5
	 *
	 * @covers Editor::enqueue_editor_scripts
	 */
	public function test_enqueue_editor_scripts() {
		$this->instance->enqueue_editor_scripts();

		$this->assertTrue( wp_script_is( Editor::SCRIPT_HANDLE, 'enqueued' ) );
	}
}
