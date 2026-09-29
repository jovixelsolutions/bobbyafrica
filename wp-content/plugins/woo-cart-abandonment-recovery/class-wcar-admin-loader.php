<?php
/**
 * CartFlows CA Admin Loader.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WCAR\Admin\Ajax\Ajax_Init;
use WCAR\Admin\Api\ApiInit;
use WCAR\Admin\Inc\Meta_Options;
use WCAR\Admin\Inc\Wcar_Admin;
use WCAR\Admin\Inc\Wcar_Onboarding;

/**
 * Class Admin_Loader.
 */
class WCAR_Admin_Loader {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		spl_autoload_register( [ $this, 'autoload' ] );

		$this->define_constants();
		$this->setup_classes();
	}

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 */
	public function autoload( $class ): void {

		// Check if the class belongs to our namespace.
		if ( 0 !== strpos( $class, 'WCAR\\' ) ) {
			return;
		}

		$class_to_load = $class;

		if ( ! class_exists( $class_to_load ) ) {
			$filename = strtolower(
				preg_replace(
					[ '/^WCAR\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
					[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
					$class_to_load
				)
			);

			$file = CARTFLOWS_CA_DIR . $filename . '.php';

			// if the file is readable, include it.
			if ( is_readable( $file ) ) {
				include $file;
			}
		}
	}

	/**
	 * Define constants.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function define_constants(): void {
		define( 'CARTFLOWS_CA_ADMIN_DIR', CARTFLOWS_CA_DIR . 'admin/' );
		define( 'CARTFLOWS_CA_ADMIN_URL', CARTFLOWS_CA_URL . 'admin/' );
	}

	/**
	 * Setup classes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setup_classes(): void {
		// Load admin classes.
		ApiInit::get_instance();
		Ajax_Init::get_instance();
		Meta_Options::get_instance();
		Wcar_Onboarding::get_instance();

		if ( is_admin() ) {
			Wcar_Admin::get_instance();
		}
	}
}

WCAR_Admin_Loader::get_instance();
