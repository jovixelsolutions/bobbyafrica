<?php
/**
 * Ajax error helpers.
 *
 * @package WooCommerceCartAbandonmentRecovery
 */

namespace WCAR\Admin\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Ajax_Errors
 */
class Ajax_Errors {
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.0.0
	 */
	private static $instance;

	/**
	 * Errors
	 *
	 * @access private
	 * @var array Errors strings.
	 * @since 2.0.0
	 */
	private static $errors = [];

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		self::$errors = [
			'permission'   => __( 'Sorry, you are not allowed to do this operation.', 'woo-cart-abandonment-recovery' ),
			'nonce'        => __( 'Nonce validation failed', 'woo-cart-abandonment-recovery' ),
			'default'      => __( 'Sorry, something went wrong.', 'woo-cart-abandonment-recovery' ),
			'parameter'    => __( 'Required parameter is missing from the posted data.', 'woo-cart-abandonment-recovery' ),
			'option'       => __( 'Missing option name.', 'woo-cart-abandonment-recovery' ),
			'missing-data' => __( 'No post data found!', 'woo-cart-abandonment-recovery' ),
		];
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
	 * Get error message.
	 *
	 * @param string $type Message type.
	 * @return string
	 */
	public function get_error_msg( $type ) {

		if ( ! isset( self::$errors[ $type ] ) ) {
			$type = 'default';
		}

		return self::$errors[ $type ];
	}
}
