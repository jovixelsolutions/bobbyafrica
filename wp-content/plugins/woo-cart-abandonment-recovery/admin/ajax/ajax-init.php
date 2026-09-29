<?php
/**
 * Ajax initialization.
 *
 * @package WooCommerceCartAbandonmentRecovery
 */

namespace WCAR\Admin\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Ajax_Init
 */
class Ajax_Init {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.0.0
	 */
	private static $instance;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->initialize_hooks();
	}

	/**
	 * Initiator
	 *
	 * @since 2.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function initialize_hooks(): void {
		$this->register_ajax_events();
	}

	/**
	 * Register ajax events.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_ajax_events(): void {
		$controllers = [
			'WCAR\Admin\Ajax\Ajax_Setting',
			'WCAR\Admin\Ajax\Ajax_Follow_Up_Report',
			'WCAR\Admin\Ajax\Ajax_Detailed_Report',
			'WCAR\Admin\Ajax\Email_Templates',
		];

		foreach ( $controllers as $controller ) {
			$controller::get_instance()->register_ajax_events();
		}
	}
}

Ajax_Init::get_instance();
