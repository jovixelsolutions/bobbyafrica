<?php
/**
 * Ability: wcar/get-setting — Read a single plugin setting by key or name.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Inc\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Wcar_Ability_Get_Setting.
 *
 * Returns a single cart abandonment setting, looked up either by its option
 * key (e.g. 'wcf_ca_status') or by its human-readable label (e.g. 'Enable Tracking').
 */
class Wcar_Ability_Get_Setting extends Wcar_Abstract_Ability {

	/**
	 * Configure ability properties.
	 *
	 * @return void
	 */
	protected function configure(): void {
		$this->id          = 'wcar/get-setting';
		$this->label       = __( 'Get Setting', 'woo-cart-abandonment-recovery' );
		$this->description = __( 'Returns the current value and metadata for one specific setting, looked up by option key or human-readable label. Use before updating to confirm a setting exists.', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * Plain-text guidance for the AI on when and how to use this ability.
	 *
	 * @return string
	 */
	public function get_instructions(): string {
		return __( 'Use when you need the current value of one specific setting. If you do not know the exact key or label, call wcar/get-settings first to discover available options. Always call this before wcar/update-setting to confirm the setting exists and record the current value.', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * JSON Schema for input parameters.
	 *
	 * @return array
	 */
	public function get_input_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'key'  => [
					'type'        => 'string',
					'description' => __( 'Option key to look up (e.g. "wcf_ca_status").', 'woo-cart-abandonment-recovery' ),
				],
				'name' => [
					'type'        => 'string',
					'description' => __( 'Human-readable label to search for (case-insensitive, e.g. "Enable Tracking").', 'woo-cart-abandonment-recovery' ),
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
				'key'         => [ 'type' => 'string' ],
				'label'       => [ 'type' => 'string' ],
				'value'       => [],
				'type'        => [ 'type' => 'string' ],
				'description' => [ 'type' => 'string' ],
				'tab'         => [ 'type' => 'string' ],
				'tab_title'   => [ 'type' => 'string' ],
				'section'     => [ 'type' => 'string' ],
			],
		];
	}

	/**
	 * Execute: find and return the requested setting.
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

		$field = self::resolve_setting( $key, $name );

		if ( null === $field ) {
			return $this->error(
				__( 'Setting not found. Please check the key or name and try again.', 'woo-cart-abandonment-recovery' )
			);
		}

		return $this->success(
			[
				'key'         => $field['key'],
				'label'       => $field['label'],
				'value'       => wcf_ca()->utils->wcar_get_option( $field['key'] ),
				'type'        => $field['type'],
				'description' => $field['description'],
				'tab'         => $field['tab'],
				'tab_title'   => $field['tab_title'],
				'section'     => $field['section'],
			]
		);
	}
}
