<?php
/**
 * CartFlows Ajax Initialize.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminLegacyCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Init.
 */
class AjaxInit {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 1.0.0
	 */
	private static $instance;

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
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->initialize_hooks();
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function initialize_hooks() {

		$this->register_all_ajax_events();
	}

	/**
	 * Register API routes.
	 */
	public function register_all_ajax_events() {

		$controllers = array(
			'CartflowsAdmin\AdminLegacyCore\Ajax\Flows',
			'CartflowsAdmin\AdminLegacyCore\Ajax\CommonSettings',
			'CartflowsAdmin\AdminLegacyCore\Ajax\Importer',
			'CartflowsAdmin\AdminLegacyCore\Ajax\Steps',
			'CartflowsAdmin\AdminLegacyCore\Ajax\MetaData',
			'CartflowsAdmin\AdminLegacyCore\Ajax\FlowsStats',
			'CartflowsAdmin\AdminLegacyCore\Ajax\AbSteps',
			'CartflowsAdmin\AdminLegacyCore\Ajax\Debugger',
			'CartflowsAdmin\AdminLegacyCore\Ajax\Learn',
		);

		foreach ( $controllers as $controller ) {
			$controller::get_instance()->register_ajax_events();
		}
	}
}

AjaxInit::get_instance();
