<?php
/**
 * Bootstraps the plugin.
 *
 * @since 2.4
 *
 * @package WPDiscussionBoard
 */

namespace WPDiscussionBoard;

use WPDiscussionBoard\Traits\Singleton;

/**
 * Class Bootstrap
 *
 * Gets the plugin started and holds plugin objects.
 *
 * @since 2.4
 */
class Bootstrap {

	use Singleton;

	/**
	 * A container to hold objects.
	 *
	 * @since 2.4
	 *
	 * @var array Plugin objects.
	 */
	protected $container = array();

	/**
	 * Init.
	 *
	 * @since 2.4
	 */
	public function init() {
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Loads the different parts of the plugin and intializes the objects. Also
	 * stores the object in a container.
	 *
	 * @since 2.4
	 */
	public function load() {
		$components_path = WPDBD_PLUGIN_DIR . '/includes/config/components.php';

		if ( file_exists( $components_path ) ) {
			require_once $components_path;
		}

		// Load Core components.
		if ( ! empty( $components ) && is_array( $components ) ) {
			foreach ( $components as $class ) {
				$this->load_component( $class );
			}
		}

		if ( is_admin() ) {
			$admin_components_path = WPDBD_PLUGIN_DIR . '/includes/config/components-admin.php';

			if ( file_exists( $admin_components_path ) ) {
				require_once $admin_components_path;
			}

			// Load Core components.
			if ( ! empty( $admin_components ) && is_array( $admin_components ) ) {
				foreach ( $admin_components as $class ) {
					$this->load_component( $class );
				}
			}
		}

		// Init container objects.
		foreach ( $this->container as $object ) {
			$this->maybe_call_hooks( $object );
		}
	}

	/**
	 * Takes a component class name, creates an object and adds it
	 * to the container.
	 *
	 * @since 2.4
	 *
	 * @param string $class The class to instantiate.
	 */
	protected function load_component( $class ) {
		if ( class_exists( $class ) ) {
			$key = str_replace( 'WPDiscussionBoard\\', '', $class );

			// Add component to container.
			$this->container[ $key ] = new $class();
		}
	}

	/**
	 * Takes an object and call the hooks method if it is available.
	 *
	 * @since 2.4
	 *
	 * @param object $object The object to initiate.
	 */
	protected function maybe_call_hooks( $object ) {
		if ( is_callable( array( $object, 'init' ) ) ) {
			$object->init();
		}
	}

	/**
	 * Return the object container.
	 *
	 * @since 2.4
	 *
	 * @param string|bool|void $item The item identifier of the object to fetch.
	 *
	 * @return array|bool
	 */
	public function get_container( $item = false ) {
		if ( ! empty( $item ) ) {
			if ( ! empty( $this->container[ $item ] ) ) {
				return $this->container[ $item ];
			}

			return false;
		}

		return $this->container;
	}

	/**
	 * Load translation files.
	 *
	 * @since 2.4
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-discussion-board', false, basename( WPDBD_PLUGIN_DIR ) . '/languages' );
	}
}
