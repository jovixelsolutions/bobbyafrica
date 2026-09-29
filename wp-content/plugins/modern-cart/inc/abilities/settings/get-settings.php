<?php
/**
 * Get Modern Cart Settings Ability
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
 * Class Get_Settings
 *
 * Retrieves all Modern Cart settings grouped by category.
 */
class Get_Settings extends Abstract_Ability {

	/**
	 * Configure the ability.
	 *
	 * @return void
	 */
	public function configure() {
		$this->id           = 'moderncart/get-settings';
		$this->category     = 'moderncart';
		$this->label        = __( 'Get Modern Cart Settings', 'modern-cart' );
		$this->description  = __( 'Read the current Modern Cart configuration before making changes. Returns all settings grouped by category (moderncart_setting, moderncart_cart, moderncart_floating, moderncart_appearance). Output is structured to be passed directly as input to update-settings.', 'modern-cart' );
		$this->capability   = 'manage_options';
		$this->instructions = __( 'Call this before update-settings to read the current configuration. Use include_groups to limit the response to only the groups you need to modify.', 'modern-cart' );
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
				'include_groups' => array(
					'type'        => 'array',
					'description' => 'Optional list of setting groups to include. Available groups: moderncart_setting (core features and activation), moderncart_cart (cart behavior, styling, and text labels), moderncart_floating (floating cart position and colors), moderncart_appearance (global colors and typography). Defaults to all groups if not specified.',
					'items'       => array(
						'type' => 'string',
						'enum' => array( 'moderncart_setting', 'moderncart_cart', 'moderncart_floating', 'moderncart_appearance' ),
					),
				),
				'format'         => array(
					'type'        => 'string',
					'description' => 'Response format. Returns settings in grouped structure matching SetSettings input format. Output can be directly reused as SetSettings input.',
					'enum'        => array( 'grouped' ),
					'default'     => 'grouped',
				),
			),
			'required'   => array(),
		);
	}

	/**
	 * Execute the ability.
	 *
	 * Returns the grouped settings data directly — no envelope wrapper —
	 * consistent with the WordPress Abilities API contract.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $args ) {
		if ( ! class_exists( 'MCW_ZipWP_Helper' ) ) {
			return Response::error(
				__( 'Modern Cart MCP helper not available. Ensure the Modern Cart plugin is installed and activated, then try again.', 'modern-cart' ),
				'moderncart_helper_unavailable'
			);
		}

		$settings       = \MCW_ZipWP_Helper::get_settings();
		$include_groups = ! empty( $args['include_groups'] ) && is_array( $args['include_groups'] )
			? $args['include_groups']
			: array( 'moderncart_setting', 'moderncart_cart', 'moderncart_floating', 'moderncart_appearance' );

		$grouped = array();
		foreach ( $include_groups as $group ) {
			$grouped[ $group ] = isset( $settings[ $group ] ) ? $settings[ $group ] : array();
		}

		return Response::success( $grouped );
	}
}
