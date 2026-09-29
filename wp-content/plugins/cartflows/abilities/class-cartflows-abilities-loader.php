<?php
/**
 * CartFlows Abilities Loader.
 *
 * Bootstraps the CartFlows Abilities API integration. Defines constants,
 * checks for WordPress 6.9+ Abilities API availability, requires the config
 * and runtime classes, and wires the registration hooks.
 *
 * @package CartFlows
 * @since   2.2.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cartflows_Abilities_Loader.
 */
class Cartflows_Abilities_Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.2.2
	 */
	private static $instance;

	/**
	 * Ability runtime instance.
	 *
	 * @var object|null
	 * @since 2.2.2
	 */
	private $runtime = null;

	/**
	 * Initiator
	 *
	 * @since 2.2.2
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
	 */
	public function __construct() {
		$this->define_constants();
		$this->init_hooks();
	}

	/**
	 * Define Abilities API constants.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function define_constants() {

		/**
		 * Abilities API integration is present in this build.
		 *
		 * @since 2.2.2
		 */
		if ( ! defined( 'CARTFLOWS_ABILITY_API' ) ) {
			define( 'CARTFLOWS_ABILITY_API', true );
		}

		/**
		 * Namespace prefix used for all CartFlows ability identifiers.
		 *
		 * Every ability registered by this plugin is named
		 * CARTFLOWS_ABILITY_API_NAMESPACE . '<slug>', e.g. 'cartflows/list-flows'.
		 *
		 * @since 2.2.2
		 */
		if ( ! defined( 'CARTFLOWS_ABILITY_API_NAMESPACE' ) ) {
			define( 'CARTFLOWS_ABILITY_API_NAMESPACE', 'cartflows/' );
		}
	}

	/**
	 * Load required ability class files.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function load_files() {
		require_once CARTFLOWS_DIR . 'abilities/class-cartflows-ability-config.php';
		require_once CARTFLOWS_DIR . 'abilities/class-cartflows-ability-runtime.php';
	}

	/**
	 * Init Hooks.
	 *
	 * Graceful degradation: only loads when the WordPress Abilities API
	 * (WordPress 6.9+) is available on this installation.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function init_hooks() {

		if ( ! function_exists( 'wp_register_ability' ) || ! class_exists( 'WP_Ability' ) ) {
			return;
		}

		$this->load_files();

		$this->runtime = new Cartflows_Ability_Runtime();

		/**
		 * Register CartFlows ability categories.
		 *
		 * Fired by the WordPress Abilities API after category registration opens.
		 *
		 * @since 2.2.2
		 */
		add_action( 'wp_abilities_api_categories_init', array( $this->runtime, 'register_categories' ) );

		/**
		 * Register CartFlows abilities.
		 *
		 * Fired by the WordPress Abilities API after ability registration opens.
		 *
		 * @since 2.2.2
		 */
		add_action( 'wp_abilities_api_init', array( $this->runtime, 'register' ) );
	}
}

Cartflows_Abilities_Loader::get_instance();
