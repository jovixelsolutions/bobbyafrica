<?php
/**
 * Onboarding Class
 *
 * Handles the onboarding process for the CAR plugin.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Inc;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Onboarding Class
 */
class Wcar_Onboarding {
	/**
	 * Member Variable.
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Onboarding completion setting.
	 *
	 * @var string
	 */
	private $onboarding_status_option = 'wcar_onboarding_completed';

	/**
	 * Constructor function that initializes required actions and hooks.
	 */
	public function __construct() {}

	/**
	 *  Initiator.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Set onboarding completion status.
	 *
	 * @since 0.0.1
	 * @param string $completed Whether the onboarding is completed.
	 * @return bool
	 */
	public function set_onboarding_status( $completed = false ) {
		return update_option( $this->onboarding_status_option, $completed );
	}

	/**
	 * Get onboarding completion status.
	 *
	 * @since 0.0.1
	 * @return bool
	 */
	public function get_onboarding_status() {
		return get_option( $this->onboarding_status_option, false );
	}
}
