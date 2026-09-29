<?php
/**
 * Third-party plugins initialization
 *
 * This file handles initialization of all third-party plugin integrations.
 *
 * @package surerank
 * @since 1.5.0
 */

namespace SureRank\Inc\ThirdPartyIntegrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\ThirdPartyIntegrations\Multilingual\Init as Multilingual;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Third-party plugins initialization class
 *
 * Manages loading of all integrations with third-party plugins.
 *
 * @since 1.5.0
 */
class Init {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.5.0
	 */
	public function __construct() {
		if ( is_admin() ) {
			$this->load_admin_integrations();
		}
		$this->load_frontend_integrations();
	}

	/**
	 * Load admin-specific integrations
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function load_admin_integrations(): void {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			Elementor::get_instance();
		}

		Avada_Fusion_Builder::get_instance();
	}

	/**
	 * Load frontend-specific integrations
	 *
	 * Only loads integration classes when the corresponding plugin is active,
	 * avoiding unnecessary class autoloading and constructor overhead.
	 *
	 * @since 1.5.0
	 * @return void
	 */
	public function load_frontend_integrations(): void {
		if ( defined( 'BRICKS_VERSION' ) ) {
			Bricks::get_instance();
		}

		if ( class_exists( 'WooCommerce' ) ) {
			Woocommerce::get_instance();
		}

		if ( defined( 'ANGIE_VERSION' ) ) {
			Angie::get_instance();
		}

		if ( defined( 'CARTFLOWS_VER' ) ) {
			CartFlows::get_instance();
		}

		if ( defined( 'ET_BUILDER_VERSION' ) ) {
			Divi::get_instance();
		}

		if ( defined( '__BREAKDANCE_VERSION' ) ) {
			Breakdance::get_instance();
		}

		Multilingual::get_instance();
	}
}
