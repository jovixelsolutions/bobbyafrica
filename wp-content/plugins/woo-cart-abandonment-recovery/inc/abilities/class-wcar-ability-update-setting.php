<?php
/**
 * Ability: wcar/update-setting — Update a single plugin setting by key or name.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Inc\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Wcar_Ability_Update_Setting.
 *
 * Updates a single cart abandonment setting identified by its option key or
 * human-readable label. Returns the old and new values on success.
 */
class Wcar_Ability_Update_Setting extends Wcar_Abstract_Ability {

	/**
	 * Configure ability properties.
	 *
	 * @return void
	 */
	protected function configure(): void {
		$this->id             = 'wcar/update-setting';
		$this->label          = __( 'Update Setting', 'woo-cart-abandonment-recovery' );
		$this->description    = __( 'Writes a new value for one setting identified by key or label. Returns old and new values. Destructive and non-idempotent — the change persists immediately.', 'woo-cart-abandonment-recovery' );
		$this->is_readonly    = false;
		$this->is_destructive = true;
		$this->is_idempotent  = false;
	}

	/**
	 * Plain-text guidance for the AI on when and how to use this ability.
	 *
	 * @return string
	 */
	public function get_instructions(): string {
		return __( 'Use only after confirming intent with the user — this change is immediate and irreversible. Always read the current value with wcar/get-setting first. Provide either key or name and the new value. The response includes old_value and new_value so the change can be verified.', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * JSON Schema for input parameters.
	 *
	 * @return array
	 */
	public function get_input_schema(): array {
		return [
			'type'       => 'object',
			'required'   => [ 'value' ],
			'properties' => [
				'key'   => [
					'type'        => 'string',
					'description' => __( 'Option key to update (e.g. "wcf_ca_status").', 'woo-cart-abandonment-recovery' ),
				],
				'name'  => [
					'type'        => 'string',
					'description' => __( 'Human-readable label of the setting to update (case-insensitive).', 'woo-cart-abandonment-recovery' ),
				],
				'value' => [
					'description' => __( 'The new value for the setting.', 'woo-cart-abandonment-recovery' ),
				],
			],
		];
	}

	/**
	 * JSON Schema for the output returned by this ability.
	 *
	 * @return array
	 */
	public function get_output_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'key'       => [ 'type' => 'string' ],
				'label'     => [ 'type' => 'string' ],
				'old_value' => [],
				'new_value' => [],
			],
		];
	}

	/**
	 * Execute: validate, look up, and persist the new setting value.
	 *
	 * @param array $args Input arguments.
	 * @return array
	 */
	public function execute( array $args ): array {
		$key  = isset( $args['key'] ) ? sanitize_text_field( $args['key'] ) : null;
		$name = isset( $args['name'] ) ? sanitize_text_field( $args['name'] ) : null;

		if ( empty( $key ) && empty( $name ) ) {
			return $this->error(
				__( 'Please provide either a "key" or a "name" parameter.', 'woo-cart-abandonment-recovery' )
			);
		}

		if ( ! isset( $args['value'] ) ) {
			return $this->error(
				__( 'A "value" parameter is required.', 'woo-cart-abandonment-recovery' )
			);
		}

		$field = self::resolve_setting( $key, $name );

		if ( null === $field ) {
			return $this->error(
				__( 'Setting not found. Please check the key or name and try again.', 'woo-cart-abandonment-recovery' )
			);
		}

		$option_key = $field['key'];

		// Confirm the option is registered by the plugin.
		if ( ! wcf_ca()->options->plugin_option_exist( $option_key ) ) {
			return $this->error(
				sprintf(
					/* translators: %s: option key */
					__( 'The setting "%s" is not a writable plugin option.', 'woo-cart-abandonment-recovery' ),
					$option_key
				)
			);
		}

		$old_value = wcf_ca()->utils->wcar_get_option( $option_key );
		$saved     = wcf_ca()->helper->save_meta_fields( $option_key, $args['value'] );

		if ( false === $saved ) {
			return $this->error(
				sprintf(
					/* translators: %s: option key */
					__( 'Failed to save the setting "%s".', 'woo-cart-abandonment-recovery' ),
					$option_key
				)
			);
		}

		return $this->success(
			[
				'key'       => $option_key,
				'label'     => $field['label'],
				'old_value' => $old_value,
				'new_value' => wcf_ca()->utils->wcar_get_option( $option_key ),
			]
		);
	}
}
