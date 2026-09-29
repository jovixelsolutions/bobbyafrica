<?php
/**
 * CartFlows Admin Menu.
 *
 * @package CartFlows
 */

namespace WCAR\Admin\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ApiInit.
 */
class ApiInit {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.0.0
	 */
	private static $instance;

	/**
	 * Instance
	 *
	 * @access private
	 * @var string Class object.
	 * @since 2.0.0
	 */
	private $menu_slug;

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->menu_slug = 'cartflows-ca';
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
	 * Init Hooks.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function initialize_hooks(): void {

		// REST API extensions init.
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Register API routes.
	 */
	public function register_routes(): void {

		$controllers = [
			'WCAR\Admin\Api\Dashboard',
			'WCAR\Admin\Api\FollowUpEmails',
			'WCAR\Admin\Api\FollowUp',
			'WCAR\Admin\Api\DetailedReport',
		];

		foreach ( $controllers as $controller ) {
			$controller::get_instance()->register_routes();
		}
	}
}

ApiInit::get_instance();
