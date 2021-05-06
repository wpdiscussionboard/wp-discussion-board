<?php
/**
 * Bootstraps the plugin
 *
 * @since 0.1
 *
 * @package WPGeeks\Plugin\HidePrices
 */

namespace WPGeeks\Plugin\HidePrices;

use WPGeeks\Plugin\HidePrices\Traits\Singleton;

/**
 * Class Bootstrap
 *
 * Gets the plugin started and holds plugin objects.
 *
 * @since 0.1
 */
class Bootstrap {

	use Singleton;

	/**
	 * A container to hold objects.
	 *
	 * @since 0.1
	 *
	 * @var array Plugin objects.
	 */
	protected $container = [];

	/**
	 * Loads the different parts of the plugin and intializes the objects. Also
	 * stores the object in a container.
	 *
	 * @since 0.1
	 */
	public function load() {
		$components_path = HIDE_PRICES_DIR . 'includes/config/components.php';

		if ( file_exists( $components_path ) ) {
			require_once $components_path;
		}

		// Load Core components.
		if ( ! empty( $components ) && is_array( $components ) ) {
			foreach ( $components as $class ) {
				$this->load_component( $class );
			}
		}

		// Load Pro components if they exist.
		$components_pro_path = HIDE_PRICES_DIR . 'includes/config/components-pro.php';

		if ( file_exists( $components_pro_path ) ) {
			require_once $components_pro_path;

			if ( ! empty( $components_pro ) && is_array( $components_pro ) ) {
				foreach ( $components_pro as $class ) {
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
	 * @since 0.1
	 *
	 * @param string $class The class to instantiate.
	 */
	protected function load_component( $class ) {
		if ( class_exists( $class ) ) {
			$key = str_replace( 'WPGeeks\Plugin\HidePrices\\', '', $class );

			// Add component to container.
			$this->container[ $key ] = new $class;
		}
	}

	/**
	 * Takes an object and call the hooks method if it is available.
	 *
	 * @since 0.1
	 *
	 * @param object $object The object to initiate.
	 */
	protected function maybe_call_hooks( $object ) {
		if ( is_callable( [ $object, 'hooks' ] ) ) {
			$object->hooks();
		}
	}

	/**
	 * Return the object container.
	 *
	 * @since 0.1
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

}
