<?php
/**
 * Ability: wcar/get-settings — Read all plugin settings organized by tab.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Inc\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Wcar_Ability_Get_Settings.
 *
 * Returns all cart abandonment settings grouped by tab, with current values.
 * Optionally filtered to a single tab by slug.
 */
class Wcar_Ability_Get_Settings extends Wcar_Abstract_Ability {

	/**
	 * Configure ability properties.
	 *
	 * @return void
	 */
	protected function configure(): void {
		$this->id          = 'wcar/get-settings';
		$this->label       = __( 'Get All Settings', 'woo-cart-abandonment-recovery' );
		$this->description = __( 'Returns all plugin settings grouped by tab with current values. Use for a full configuration overview or to discover option keys before making targeted changes.', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * Plain-text guidance for the AI on when and how to use this ability.
	 *
	 * @return string
	 */
	public function get_instructions(): string {
		return __( 'Call when the user asks about current plugin configuration or wants to see all settings. Use the tab parameter to narrow results when you know the relevant section (e.g. "general-settings", "email-settings", "webhook-integration"). Call this before wcar/get-setting if you do not know the exact option key.', 'woo-cart-abandonment-recovery' );
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
				'tab' => [
					'type'        => 'string',
					'description' => __( 'Optional tab slug to filter results (e.g. "general-settings", "email-settings", "webhook-integration").', 'woo-cart-abandonment-recovery' ),
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
				'tabs' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'slug'     => [ 'type' => 'string' ],
							'title'    => [ 'type' => 'string' ],
							'section'  => [ 'type' => 'string' ],
							'settings' => [
								'type'  => 'array',
								'items' => [
									'type'       => 'object',
									'properties' => [
										'key'         => [ 'type' => 'string' ],
										'label'       => [ 'type' => 'string' ],
										'value'       => [],
										'type'        => [ 'type' => 'string' ],
										'description' => [ 'type' => 'string' ],
									],
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Execute: return settings grouped by tab.
	 *
	 * @param array $args Input arguments.
	 * @return array
	 */
	public function execute( array $args ): array {
		$filter_tab = isset( $args['tab'] ) ? sanitize_text_field( $args['tab'] ) : '';
		$tabs       = [];

		if ( class_exists( 'WCAR\\Admin\\Inc\\Meta_Options' ) ) {
			// ── Settings tabs ──────────────────────────────────────────────────
			$setting_tabs = \WCAR\Admin\Inc\Meta_Options::get_setting_fields();
			foreach ( $setting_tabs as $tab_slug => $tab ) {
				if ( ! empty( $filter_tab ) && $filter_tab !== $tab_slug ) {
					continue;
				}
				$tab_entry = $this->build_tab_entry( $tab_slug, $tab, 'settings' );
				if ( ! empty( $tab_entry['settings'] ) ) {
					$tabs[] = $tab_entry;
				}
			}

			// ── Integration tabs ───────────────────────────────────────────────
			$integration_tabs = \WCAR\Admin\Inc\Meta_Options::get_integration_fields();
			foreach ( $integration_tabs as $tab_slug => $tab ) {
				if ( ! empty( $filter_tab ) && $filter_tab !== $tab_slug ) {
					continue;
				}
				$tab_entry = $this->build_tab_entry( $tab_slug, $tab, 'integrations' );
				if ( ! empty( $tab_entry['settings'] ) ) {
					$tabs[] = $tab_entry;
				}
			}
		}

		return $this->success( [ 'tabs' => $tabs ] );
	}

	/**
	 * Build a single tab entry array from a Meta_Options tab definition.
	 *
	 * @param string $tab_slug Tab slug.
	 * @param array  $tab      Tab definition from Meta_Options.
	 * @param string $section  'settings' or 'integrations'.
	 * @return array
	 */
	private function build_tab_entry( string $tab_slug, array $tab, string $section ): array {
		$settings  = [];
		$tab_title = isset( $tab['title'] ) ? $tab['title'] : $tab_slug;

		if ( empty( $tab['fields'] ) || ! is_array( $tab['fields'] ) ) {
			return [
				'slug'     => $tab_slug,
				'title'    => $tab_title,
				'section'  => $section,
				'settings' => [],
			];
		}

		foreach ( $tab['fields'] as $field ) {
			// Compound 'time' field — expand to sub-fields.
			if ( 'time' === ( isset( $field['type'] ) ? $field['type'] : '' ) && ! empty( $field['fields'] ) ) {
				$parent_label = isset( $field['label'] ) ? $field['label'] : '';
				$parent_desc  = isset( $field['desc'] ) ? $field['desc'] : '';
				foreach ( $field['fields'] as $sub_field ) {
					if ( empty( $sub_field['name'] ) ) {
						continue;
					}
					$sub_label  = isset( $sub_field['label'] ) ? $sub_field['label'] : '';
					$settings[] = [
						'key'         => $sub_field['name'],
						'label'       => trim( $parent_label . ' ' . $sub_label ),
						'value'       => wcf_ca()->utils->wcar_get_option( $sub_field['name'] ),
						'type'        => isset( $sub_field['type'] ) ? $sub_field['type'] : 'text',
						'description' => $parent_desc,
					];
				}
				continue;
			}

			if ( empty( $field['name'] ) ) {
				continue;
			}

			// Skip action-only types.
			if ( isset( $field['type'] ) && in_array( $field['type'], [ 'button', 'rollback', 'ui_switch' ], true ) ) {
				continue;
			}

			$settings[] = [
				'key'         => $field['name'],
				'label'       => isset( $field['label'] ) ? $field['label'] : $field['name'],
				'value'       => isset( $field['value'] ) ? $field['value'] : wcf_ca()->utils->wcar_get_option( $field['name'] ),
				'type'        => isset( $field['type'] ) ? $field['type'] : 'text',
				'description' => isset( $field['desc'] ) ? $field['desc'] : '',
			];
		}

		return [
			'slug'     => $tab_slug,
			'title'    => $tab_title,
			'section'  => $section,
			'settings' => $settings,
		];
	}
}
