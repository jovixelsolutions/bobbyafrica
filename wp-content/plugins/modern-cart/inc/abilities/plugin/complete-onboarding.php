<?php
/**
 * Complete Onboarding Ability
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Abilities\Plugin;

use ModernCart\Inc\Abilities\Abstract_Ability;
use ModernCart\Inc\Abilities\Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Complete_Onboarding
 *
 * Marks the Modern Cart onboarding wizard as complete.
 */
class Complete_Onboarding extends Abstract_Ability {

	/**
	 * Configure the ability.
	 *
	 * @return void
	 */
	public function configure() {
		$this->id           = 'moderncart/complete-onboarding';
		$this->category     = 'moderncart';
		$this->label        = __( 'Complete Onboarding', 'modern-cart' );
		$this->description  = __( 'Marks the Modern Cart onboarding wizard as complete. Call this after the initial plugin setup has been performed via update-settings. This operation is idempotent — safe to call even if onboarding was already completed.', 'modern-cart' );
		$this->capability   = 'manage_options';
		$this->instructions = __( 'Call this only after applying initial plugin configuration via update-settings. It is safe to call even if onboarding was already completed — check the was_already_complete field in the response to know whether this was the first completion or a redundant call.', 'modern-cart' );
	}

	/**
	 * Get input schema.
	 *
	 * @return array<string, mixed>
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(),
			'required'   => array(),
		);
	}

	/**
	 * Get annotations override.
	 *
	 * Complete-onboarding is a write operation but is idempotent and non-destructive.
	 *
	 * @return array<string, bool|float>
	 */
	public function get_annotations() {
		return array(
			'priority'        => 3.0,
			'readOnlyHint'    => false,
			'destructiveHint' => false,
			'idempotentHint'  => true,
			'openWorldHint'   => false,
		);
	}

	/**
	 * Execute the ability.
	 *
	 * @param array<string, mixed> $args Input arguments (none required).
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $args ) {
		$was_already_complete = 'yes' === get_option( 'moderncart_is_onboarding_complete', 'no' );

		if ( ! $was_already_complete ) {
			update_option( 'moderncart_is_onboarding_complete', 'yes' );
		}

		return Response::success(
			array(
				'success'              => true,
				'was_already_complete' => $was_already_complete,
			)
		);
	}
}
