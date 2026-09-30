<?php
/**
 * CartFlows Ajax Base.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminCore\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminCore\Ajax\AjaxErrors;

/**
 * Class Admin_Menu.
 */
abstract class AjaxBase {

	/**
	 * Ajax action prefix.
	 *
	 * @var string
	 */
	private $prefix = 'cartflows';

	/**
	 * Erros class instance.
	 *
	 * @var object
	 */
	public $errors = null;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->errors = AjaxErrors::get_instance();
	}

	/**
	 * Register ajax events.
	 *
	 * @param array $ajax_events Ajax events.
	 */
	public function init_ajax_events( $ajax_events ) {

		if ( ! empty( $ajax_events ) ) {

			foreach ( $ajax_events as $ajax_event ) {
				add_action( 'wp_ajax_' . $this->prefix . '_' . $ajax_event, array( $this, $ajax_event ) );

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
	public function localize_ajax_action_nonce( $action ) {

		if ( current_user_can( 'cartflows_manage_flows_steps' ) ) {

			add_filter(
				'cartflows_admin_localized_vars',
				function( $localize ) use ( $action ) {

					$localize[ $action . '_nonce' ] = wp_create_nonce( $this->prefix . '_' . $action );
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

	/**
	 * Verify the current user can edit the given flow post.
	 *
	 * Used as a per-flow ownership check in addition to the global
	 * `cartflows_manage_flows_steps` capability — defends against IDOR where
	 * an editor with access to one flow submits step IDs from another flow.
	 *
	 * @since 3.1.0
	 * @param int $flow_id Flow post ID.
	 * @return bool
	 */
	protected function user_can_edit_flow( $flow_id ) {

		$flow_id = (int) $flow_id;

		if ( $flow_id <= 0 ) {
			return false;
		}

		if ( CARTFLOWS_FLOW_POST_TYPE !== get_post_type( $flow_id ) ) {
			return false;
		}

		return current_user_can( 'cartflows_manage_flows_steps' )
			&& current_user_can( 'edit_post', $flow_id );
	}

	/**
	 * Verify a step belongs to the given flow.
	 *
	 * @since 3.1.0
	 * @param int $step_id Step post ID.
	 * @param int $flow_id Flow post ID.
	 * @return bool
	 */
	protected function is_step_in_flow( $step_id, $flow_id ) {

		$step_id = (int) $step_id;
		$flow_id = (int) $flow_id;

		if ( $step_id <= 0 || $flow_id <= 0 ) {
			return false;
		}

		if ( CARTFLOWS_STEP_POST_TYPE !== get_post_type( $step_id ) ) {
			return false;
		}

		$step_flow_id = wcf()->utils->get_flow_id_from_step_id( $step_id );

		return is_scalar( $step_flow_id ) && (int) $step_flow_id === $flow_id;
	}
}
