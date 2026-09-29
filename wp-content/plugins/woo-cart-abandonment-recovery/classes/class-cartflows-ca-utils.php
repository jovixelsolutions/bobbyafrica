<?php
/**
 * Utils.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Ca_Utils.
 */
class Cartflows_Ca_Utils {
	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Common zapier data
	 *
	 * @var zapier
	 */
	private static $zapier = null;

	/**
	 * Common zapier data
	 *
	 * @var zapier
	 */
	private static $cart_abandonment_settings = null;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Check if cart abandonment tracking is enabled.
	 *
	 * @return bool
	 */
	public function is_cart_abandonment_tracking_enabled() {

		$wcf_ca_status = $this->wcar_get_option( 'wcf_ca_status' );

		// Check if abandonment cart tracking is disabled or zapier webhook is empty.
		if ( isset( $wcf_ca_status ) && 'on' === $wcf_ca_status ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if cart abandonment tracking is enabled.
	 *
	 * @return bool
	 */
	public function is_zapier_trigger_enabled() {

		$wcf_ca_zapier_tracking_status = $this->wcar_get_option( 'wcf_ca_zapier_tracking_status' );

		// Check if zapier tracking is disabled or zapier webhook is empty.
		if ( isset( $wcf_ca_zapier_tracking_status ) && 'on' === $wcf_ca_zapier_tracking_status ) {
			return true;
		}

		return false;
	}

	/**
	 * Get cart abandonment tracking cutoff time.
	 *
	 * @param  bool $in_seconds get cutoff time in seconds if true.
	 * @return bool
	 */
	public function get_cart_abandonment_tracking_cut_off_time( $in_seconds = false ) {

		$cart_abandoned_time = apply_filters( 'cartflows_ca_cart_abandonment_cut_off_time', WCF_DEFAULT_CUT_OFF_TIME );
		return $in_seconds ? $cart_abandoned_time * MINUTE_IN_SECONDS : $cart_abandoned_time;
	}

	/**
	 * Check if GDPR is enabled.
	 *
	 * @return bool
	 */
	public function is_gdpr_enabled() {

		$wcf_ca_gdpr_status = $this->wcar_get_option( 'wcf_ca_gdpr_status' );

		// Check if abandonment cart tracking is disabled or zapier webhook is empty.
		if ( isset( $wcf_ca_gdpr_status ) && 'on' === $wcf_ca_gdpr_status ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the value of a Cart Abandonment option.
	 *
	 * @param string $option  The name of the option to retrieve.
	 * @param mixed  $default The default value to return if the option is not set.
	 * @return mixed The value of the option, or the default value if not set.
	 * @since 2.0.0
	 */
	public function wcar_get_option( $option, $default = false ) {

		
		$value = get_option( $option );

		if ( ! $value ) {
			if ( false !== $default ) {
				$value = $default;
			} else {
				$default_options = wcf_ca()->options->get_default_settings();
				/**
				 * Filter the options array for Cart Abandonment Settings.
				 *
				 * @since  2.0.0
				 * @var Array
				 */
				$default_options = apply_filters( 'wcar_get_option_array', $default_options, $option, $default );
				$value           = isset( $default_options[ $option ] ) ? $default_options[ $option ] : $default;
			}
		}

		

		/**
		 * Dynamic filter wcar_get_option_$option.
		 * $option is the name of the Cart Abandonment Setting
		 *
		 * @since  2.0.0
		 * @var Mixed.
		 */
		return apply_filters( "wcar_get_option_{$option}", $value, $option, $default );
	}
}
