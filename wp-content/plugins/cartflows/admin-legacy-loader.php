<?php
/**
 * CartFlows Legacy Admin Loader.
 *
 * Bootstraps the pre-3.0 admin UI (preserved under admin-legacy-core/) when
 * the user has opted into the legacy interface via the cartflows-legacy-admin
 * option. Mirrors admin-loader.php but binds CARTFLOWS_ADMIN_CORE_DIR/URL to
 * the admin-legacy-core/ directory and registers the legacy Wizard.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin;

use CartflowsAdmin\AdminLegacyCore\Api\ApiInit;
use CartflowsAdmin\AdminLegacyCore\Ajax\AjaxInit;
use CartflowsAdmin\AdminLegacyCore\Inc\AdminMenu;
use CartflowsAdmin\AdminLegacyCore\Inc\StoreCheckout;
use CartflowsAdmin\Wizard\Inc\WizardCore;
use CartflowsAdmin\Wizard\Ajax\WizardAjaxInit;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Legacy_Loader.
 */
class Admin_Legacy_Loader {

	/**
	 * Instance
	 *
	 * @var object
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @return object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Autoload classes — kebab-cases the FQN under CartflowsAdmin\* and resolves
	 * it to a file path relative to CARTFLOWS_DIR. Identical mapping as
	 * admin-loader.php so AdminLegacyCore\* resolves to admin-legacy-core/*.
	 *
	 * @param string $class class name.
	 */
	public function autoload( $class ) {

		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		if ( ! class_exists( $class ) ) {
			$filename = strtolower(
				preg_replace(
					array( '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ),
					array( '', '$1-$2', '-', DIRECTORY_SEPARATOR ),
					$class
				)
			);

			$file = CARTFLOWS_DIR . $filename . '.php';

			if ( is_readable( $file ) ) {
				include $file;
			}
		}
	}

	/**
	 * Constructor.
	 */
	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->define_constants();
		$this->setup_classes();
	}

	/**
	 * Define legacy admin core paths. Reuses the same constant names as
	 * admin-loader.php so legacy files require no source changes — only the
	 * resolved path differs.
	 */
	public function define_constants() {
		define( 'CARTFLOWS_ADMIN_CORE_DIR', CARTFLOWS_DIR . 'admin-legacy-core/' );
		define( 'CARTFLOWS_ADMIN_CORE_URL', CARTFLOWS_URL . 'admin-legacy-core/' );
	}

	/**
	 * External code (importer, abilities runtime, SureTriggers compat) imports
	 * CartflowsAdmin\AdminCore\Inc\* classes by FQN. Those classes don't exist
	 * in legacy mode — autoload would miss and fatal on first reference. Alias
	 * the legacy classes under the AdminCore FQN so external callers resolve
	 * transparently regardless of which UI is active.
	 */
	public function alias_admin_core_classes() {

		$aliases = array(
			'CartflowsAdmin\\AdminLegacyCore\\Inc\\AdminHelper' => 'CartflowsAdmin\\AdminCore\\Inc\\AdminHelper',
			'CartflowsAdmin\\AdminLegacyCore\\Inc\\AdminMenu'   => 'CartflowsAdmin\\AdminCore\\Inc\\AdminMenu',
		);

		foreach ( $aliases as $source => $target ) {
			if ( ! class_exists( $target, false ) ) {
				class_alias( $source, $target );
			}
		}
	}

	/**
	 * Boot legacy admin classes.
	 */
	public function setup_classes() {

		$this->alias_admin_core_classes();

		ApiInit::get_instance();

		if ( is_admin() ) {
			StoreCheckout::get_instance();
			AdminMenu::get_instance();
			AjaxInit::get_instance();

			WizardAjaxInit::get_instance();
			WizardCore::get_instance();
		}
	}
}

Admin_Legacy_Loader::get_instance();
