<?php
/**
 * Singleton trait to be used by classes.
 *
 * @since 2.4
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard\Traits;

/**
 * Trait Singleton
 *
 * @since 2.4
 *
 * @codeCoverageIgnore
 *
 * @package WPGeeks\Plugin\HidePrices\Traits
 */
trait Singleton {

	/**
	 * The object instance.
	 *
	 * @since 2.4
	 *
	 * @var object|null Instance of object.
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since 2.4
	 */
	protected function __construct() {}

	/**
	 * No cloning of the object.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since 2.4
	 */
	final protected function __clone() {}

	/**
	 * Get an instance of the object.
	 *
	 * @since 2.4
	 *
	 * @codeCoverageIgnore
	 *
	 * @return object
	 */
	final public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}
