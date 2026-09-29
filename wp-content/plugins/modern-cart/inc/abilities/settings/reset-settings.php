<?php
/**
 * Reset Settings Ability
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Abilities\Settings;

use ModernCart\Inc\Abilities\Abstract_Ability;
use ModernCart\Inc\Abilities\Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Reset_Settings
 *
 * Resets one or more Modern Cart setting groups back to factory defaults.
 */
class Reset_Settings extends Abstract_Ability {

	/**
	 * All known setting group option keys.
	 *
	 * @var array<string>
	 */
	private $valid_groups = array(
		'moderncart_setting',
		'moderncart_cart',
		'moderncart_floating',
		'moderncart_appearance',
	);

	/**
	 * Configure the ability.
	 *
	 * @return void
	 */
	public function configure() {
		$this->id             = 'moderncart/reset-settings';
		$this->category       = 'moderncart';
		$this->label          = __( 'Reset Settings to Defaults', 'modern-cart' );
		$this->description    = __( 'Permanently deletes stored values for one or more Modern Cart setting groups, reverting them to factory defaults. This is irreversible — use get-settings first to save a backup if needed. Supports dry_run to preview which groups would be reset without applying changes.', 'modern-cart' );
		$this->capability     = 'manage_options';
		$this->is_destructive = true;
		$this->instructions   = __( 'This operation is irreversible. Always call get-settings first and show the user their current values. Run with dry_run: true and show the user which groups will be reset. Require explicit user confirmation before running without dry_run.', 'modern-cart' );
	}

	/**
	 * Get input schema.
	 *
	 * @return array<string, mixed>
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'groups' => array(
					'type'        => 'array',
					'description' => __( 'Setting groups to reset. Defaults to all four groups if not provided.', 'modern-cart' ),
					'items'       => array(
						'type' => 'string',
						'enum' => array( 'moderncart_setting', 'moderncart_cart', 'moderncart_floating', 'moderncart_appearance' ),
					),
				),
			),
			'required'   => array(),
		);
	}

	/**
	 * Dry run: return which groups would be reset without applying changes.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>
	 */
	protected function dry_run( $args ) {
		$groups_to_reset = $this->resolve_groups( $args );

		return array(
			'dry_run'         => true,
			'groups_to_reset' => $groups_to_reset,
			'reset_count'     => count( $groups_to_reset ),
		);
	}

	/**
	 * Execute the ability.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $args ) {
		$groups_to_reset = $this->resolve_groups( $args );

		if ( empty( $groups_to_reset ) ) {
			return Response::error(
				__( 'No valid setting groups specified. Valid groups: moderncart_setting, moderncart_cart, moderncart_floating, moderncart_appearance.', 'modern-cart' ),
				'moderncart_invalid_groups'
			);
		}

		$reset_groups = array();
		foreach ( $groups_to_reset as $group ) {
			delete_option( $group );
			$reset_groups[] = $group;
		}

		return Response::success(
			array(
				'reset_groups' => $reset_groups,
				'reset_count'  => count( $reset_groups ),
				'message'      => sprintf(
					/* translators: %d: number of groups reset */
					_n(
						'%d setting group reset to factory defaults.',
						'%d setting groups reset to factory defaults.',
						count( $reset_groups ),
						'modern-cart'
					),
					count( $reset_groups )
				),
			)
		);
	}

	/**
	 * Resolve which groups to reset from input args.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string>
	 */
	private function resolve_groups( $args ) {
		if ( ! empty( $args['groups'] ) && is_array( $args['groups'] ) ) {
			return array_values( array_intersect( $args['groups'], $this->valid_groups ) );
		}

		return $this->valid_groups;
	}
}
