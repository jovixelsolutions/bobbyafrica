<?php
/**
 * CartFlows Learn ajax actions.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Ajax\AjaxBase;

/**
 * Class Learn.
 */
class Learn extends AjaxBase {

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
	 * Register_ajax_events.
	 *
	 * @return void
	 */
	public function register_ajax_events() {

		if ( current_user_can( 'cartflows_manage_settings' ) ) {

			$ajax_events = array(
				'save_learn_completed',
			);
			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * AJAX handler – persist completed module IDs to option.
	 *
	 * @return void
	 */
	public function save_learn_completed() {
		check_ajax_referer( 'cartflows_save_learn_completed', 'nonce' );

		$module_ids = isset( $_POST['module_ids'] )
			? json_decode( sanitize_text_field( wp_unslash( $_POST['module_ids'] ) ), true )
			: array();

		if ( ! is_array( $module_ids ) ) {
			wp_send_json_error();
		}

		$module_ids = array_map( 'sanitize_text_field', $module_ids );

		update_option( 'wcf_learn_data', $module_ids );

		wp_send_json_success();
	}
}
