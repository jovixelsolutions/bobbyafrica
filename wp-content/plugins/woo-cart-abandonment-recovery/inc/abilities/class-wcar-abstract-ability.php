<?php
/**
 * Abstract Ability base class for WCAR MCP Abilities.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Inc\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract class Wcar_Abstract_Ability.
 *
 * Base class for all WCAR abilities that integrate with the WordPress
 * Abilities API and the MCP adapter.
 */
abstract class Wcar_Abstract_Ability {

	/**
	 * Ability ID (e.g. 'wcar/get-settings').
	 *
	 * @var string
	 */
	protected string $id = '';

	/**
	 * Ability category slug.
	 *
	 * @var string
	 */
	protected string $category = 'wcar';

	/**
	 * Human-readable ability label.
	 *
	 * @var string
	 */
	protected string $label = '';

	/**
	 * Ability description.
	 *
	 * @var string
	 */
	protected string $description = '';

	/**
	 * WordPress capability required to execute this ability.
	 *
	 * @var string
	 */
	protected string $capability = 'manage_woocommerce';

	/**
	 * Whether this ability only reads data and never mutates state.
	 *
	 * @var bool
	 */
	protected bool $is_readonly = true;

	/**
	 * Whether this ability makes destructive changes.
	 *
	 * @var bool
	 */
	protected bool $is_destructive = false;

	/**
	 * Whether calling this ability repeatedly with the same input produces the same result.
	 *
	 * @var bool
	 */
	protected bool $is_idempotent = true;

	/**
	 * Configure the ability properties (id, label, description, etc.).
	 * Called automatically during construction.
	 *
	 * @return void
	 */
	abstract protected function configure(): void;

	/**
	 * Return the JSON Schema for the ability's input parameters.
	 *
	 * @return array
	 */
	abstract public function get_input_schema(): array;

	/**
	 * Return the JSON Schema for the ability's output (return value).
	 *
	 * @return array
	 */
	abstract public function get_output_schema(): array;

	/**
	 * Return plain-text guidance for the AI on when and how to use this ability.
	 *
	 * @return string
	 */
	abstract public function get_instructions(): string;

	/**
	 * Execute the ability logic.
	 *
	 * @param array $args Validated input arguments.
	 * @return array Response array from success() or error().
	 */
	abstract public function execute( array $args ): array;

	/**
	 * Constructor — calls configure() so subclasses set their properties.
	 */
	public function __construct() {
		$this->configure();
	}

	/**
	 * Register this ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			$this->id,
			[
				'category'            => $this->category,
				'label'               => $this->label,
				'description'         => $this->description,
				'permission_callback' => function () {
					return current_user_can( $this->capability );
				},
				'input_schema'        => $this->get_input_schema(),
				'output_schema'       => $this->get_output_schema(),
				'meta'                => [
					'show_in_rest' => true,
					'annotations'  => [
						'readonly'     => $this->is_readonly,
						'destructive'  => $this->is_destructive,
						'idempotent'   => $this->is_idempotent,
						'instructions' => $this->get_instructions(),
					],
					'mcp'          => [ 'public' => true ],
				],
				'execute_callback'    => [ $this, 'handle_execute' ],
			]
		);
	}

	/**
	 * Public callback registered with the Abilities API.
	 * Checks permissions, handles dry_run, and delegates to execute().
	 *
	 * @param array $args Input arguments provided by the API caller.
	 * @return array
	 */
	public function handle_execute( array $args ): array {
		if ( ! $this->check_permission() ) {
			return $this->error(
				__( 'You do not have permission to perform this action.', 'woo-cart-abandonment-recovery' )
			);
		}

		if ( ! empty( $args['dry_run'] ) ) {
			return $this->success(
				[
					'dry_run' => true,
					'message' => __( 'Dry run — no changes made.', 'woo-cart-abandonment-recovery' ),
				]
			);
		}

		try {
			return $this->execute( $args );
		} catch ( \Exception $e ) {
			return $this->error( $e->getMessage() );
		}
	}

	/**
	 * Check if the current user has the required capability.
	 *
	 * @return bool
	 */
	protected function check_permission(): bool {
		return current_user_can( $this->capability );
	}

	/**
	 * Build a success response.
	 *
	 * @param array $data Payload to return to the caller.
	 * @return array
	 */
	protected function success( array $data ): array {
		return [
			'success' => true,
			'data'    => $data,
		];
	}

	/**
	 * Build an error response.
	 *
	 * @param string $message Human-readable error message.
	 * @param array  $data    Optional additional context.
	 * @return array
	 */
	protected function error( string $message, array $data = [] ): array {
		return [
			'success' => false,
			'message' => $message,
			'data'    => $data,
		];
	}

	/**
	 * Resolve a setting entry by direct key or human-readable name.
	 *
	 * @param string|null $key  Option key (e.g. 'wcf_ca_status').
	 * @param string|null $name Human-readable label (case-insensitive).
	 * @return array|null Flat field info array, or null if not found.
	 */
	protected static function resolve_setting( ?string $key, ?string $name ): ?array {
		$fields = self::get_all_setting_fields();

		if ( ! empty( $key ) ) {
			foreach ( $fields as $field ) {
				if ( $key === $field['key'] ) {
					return $field;
				}
			}
			return null;
		}

		if ( ! empty( $name ) ) {
			$name_lower = strtolower( $name );
			foreach ( $fields as $field ) {
				if ( strtolower( $field['label'] ) === $name_lower ) {
					return $field;
				}
			}
		}

		return null;
	}

	/**
	 * Build a flat list of all configurable setting fields from Meta_Options.
	 *
	 * Iterates both get_setting_fields() and get_integration_fields(), flattening
	 * compound 'time' fields into their individual sub-fields.
	 *
	 * @return array Each entry: { key, label, type, description, tab, tab_title, section }.
	 */
	protected static function get_all_setting_fields(): array {
		if ( ! class_exists( 'WCAR\\Admin\\Inc\\Meta_Options' ) ) {
			return [];
		}

		$all = [];

		// ── Settings tabs ──────────────────────────────────────────────────────
		$setting_tabs = \WCAR\Admin\Inc\Meta_Options::get_setting_fields();
		foreach ( $setting_tabs as $tab_slug => $tab ) {
			if ( empty( $tab['fields'] ) || ! is_array( $tab['fields'] ) ) {
				continue;
			}
			$tab_title = isset( $tab['title'] ) ? $tab['title'] : $tab_slug;
			foreach ( $tab['fields'] as $field ) {
				if ( empty( $field['name'] ) ) {
					continue;
				}
				// Skip action-only types that are not stored options.
				if ( isset( $field['type'] ) && in_array( $field['type'], [ 'button', 'rollback', 'ui_switch' ], true ) ) {
					continue;
				}
				$all[] = [
					'key'         => $field['name'],
					'label'       => isset( $field['label'] ) ? $field['label'] : $field['name'],
					'type'        => isset( $field['type'] ) ? $field['type'] : 'text',
					'description' => isset( $field['desc'] ) ? $field['desc'] : '',
					'tab'         => $tab_slug,
					'tab_title'   => $tab_title,
					'section'     => 'settings',
				];
			}
		}

		// ── Integration tabs ───────────────────────────────────────────────────
		$integration_tabs = \WCAR\Admin\Inc\Meta_Options::get_integration_fields();
		foreach ( $integration_tabs as $tab_slug => $tab ) {
			if ( empty( $tab['fields'] ) || ! is_array( $tab['fields'] ) ) {
				continue;
			}
			$tab_title = isset( $tab['title'] ) ? $tab['title'] : $tab_slug;
			foreach ( $tab['fields'] as $field ) {
				// Compound 'time' field — expand to sub-fields.
				if ( 'time' === ( isset( $field['type'] ) ? $field['type'] : '' ) && ! empty( $field['fields'] ) ) {
					$parent_label = isset( $field['label'] ) ? $field['label'] : '';
					$parent_desc  = isset( $field['desc'] ) ? $field['desc'] : '';
					foreach ( $field['fields'] as $sub_field ) {
						if ( empty( $sub_field['name'] ) ) {
							continue;
						}
						$sub_label = isset( $sub_field['label'] ) ? $sub_field['label'] : '';
						$all[]     = [
							'key'         => $sub_field['name'],
							'label'       => trim( $parent_label . ' ' . $sub_label ),
							'type'        => isset( $sub_field['type'] ) ? $sub_field['type'] : 'text',
							'description' => $parent_desc,
							'tab'         => $tab_slug,
							'tab_title'   => $tab_title,
							'section'     => 'integrations',
						];
					}
					continue;
				}

				if ( empty( $field['name'] ) ) {
					continue;
				}
				$all[] = [
					'key'         => $field['name'],
					'label'       => isset( $field['label'] ) ? $field['label'] : $field['name'],
					'type'        => isset( $field['type'] ) ? $field['type'] : 'text',
					'description' => isset( $field['desc'] ) ? $field['desc'] : '',
					'tab'         => $tab_slug,
					'tab_title'   => $tab_title,
					'section'     => 'integrations',
				];
			}
		}

		return $all;
	}
}
