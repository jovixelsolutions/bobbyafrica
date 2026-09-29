<?php
/**
 * Ajax Base utilities.
 *
 * @package WooCommerceCartAbandonmentRecovery
 */

namespace WCAR\Admin\Ajax;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Ajax_Base
 */
abstract class Ajax_Base {
	/**
	 * Erros class instance.
	 *
	 * @var object
	 */
	public $errors = null;

	/**
	 * Ajax action prefix.
	 *
	 * @var string
	 */
	private $prefix = 'wcar';

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->errors = Ajax_Errors::get_instance();
	}

	/**
	 * Register ajax events.
	 *
	 * @param array $ajax_events Ajax events.
	 */
	public function init_ajax_events( $ajax_events ): void {

		if ( ! empty( $ajax_events ) ) {

			foreach ( $ajax_events as $ajax_event ) {
				add_action( 'wp_ajax_' . $this->prefix . '_' . $ajax_event, [ $this, $ajax_event ] );

				$this->localize_ajax_action_nonce( $ajax_event );
			}
		}
	}

	/**
	 * Localize nonce for ajax call.
	 *
	 * @param string $action Action name.
	 * @return void
	 */
	public function localize_ajax_action_nonce( $action ): void {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$prefix = $this->prefix;
			add_filter(
				'cart_abandonment_admin_vars',
				static function( $localize ) use ( $action, $prefix ) {
					$localize[ $action . '_nonce' ] = wp_create_nonce( $prefix . '_' . $action );
					return $localize;
				}
			);
		}
	}

	/**
	 * Get ajax error message.
	 *
	 * @param string $type Message type.
	 * @return string
	 */
	public function get_error_msg( $type ) {

		return $this->errors->get_error_msg( $type );
	}
}
