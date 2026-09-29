<?php
/**
 * Set Modern Cart Settings Ability
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
 * Class Set_Settings
 *
 * Updates Modern Cart settings including general configuration, cart appearance,
 * styling, text labels, and floating cart options.
 */
class Set_Settings extends Abstract_Ability {

	/**
	 * Configure the ability.
	 *
	 * @return void
	 */
	public function configure() {
		$this->id             = 'moderncart/update-settings';
		$this->category       = 'moderncart';
		$this->label          = __( 'Update Modern Cart Settings', 'modern-cart' );
		$this->description    = __( 'Partially update Modern Cart settings across one or more groups. Only the keys you include are written — all other existing settings are preserved. Supports dry_run to preview changes without applying them.', 'modern-cart' );
		$this->capability     = 'manage_options';
		$this->is_destructive = true;
		$this->instructions   = __( 'Always call get-settings first to read current values. Only include the keys you intend to change. Never overwrite translatable text labels (main_title, coupon_title, etc.) unless the user explicitly provides new text. Call get-available-options to discover valid enum values before setting any dropdown field. Setting cart_style to "popup" requires the Pro plugin — check get-plugin-status first.', 'modern-cart' );
	}

	/**
	 * Get input schema.
	 *
	 * @return array<string, mixed>
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'required'   => array( 'settings' ),
			'properties' => array(
				'settings' => array(
					'type'                 => 'object',
					'description'          => __( 'Grouped Modern Cart settings. Keys must match Modern Cart setting groups. Settings use typed values: dropdown options as strings, toggles as booleans, measurements as numbers, labels as strings.', 'modern-cart' ),
					'additionalProperties' => false,
					'properties'           => array(
						'moderncart_setting'    => array(
							'type'                 => 'object',
							'description'          => __( 'Core plugin behavior and feature toggles. Includes: plugin activation scope (dropdown: all pages, WooCommerce pages only, disabled, or specific pages), powered-by attribution toggle, AJAX add-to-cart functionality, free shipping progress bar, express checkout gateway integration.', 'modern-cart' ),
							'additionalProperties' => array(
								'type' => array( 'string', 'number', 'boolean' ),
							),
							'value_hints'          => array(
								'enable_moderncart' => array(
									'type'    => 'string',
									'options' => array( 'all', 'wc_pages', 'disabled', 'specific' ),
									'note'    => __( 'Controls where Modern Cart appears', 'modern-cart' ),
								),
							),
						),
						'moderncart_cart'       => array(
							'type'                 => 'object',
							'description'          => __( 'Cart display and behavior configuration. Includes: cart display style (slideout vs popup), visual theme variants, product image sizing, coupon field visibility control (show/hide/minimize), spacing and padding values, animation timing, section accordion behavior, and all user-facing text labels (headings, buttons, placeholders, success messages, promotional text).', 'modern-cart' ),
							'additionalProperties' => array(
								'type' => array( 'string', 'number', 'boolean' ),
							),
							'value_hints'          => array(
								'cart_style'          => array(
									'type'    => 'string',
									'options' => array( 'slideout', 'popup' ),
									'note'    => __( 'Primary cart display mode', 'modern-cart' ),
								),
								'cart_theme_style'    => array(
									'type'    => 'string',
									'options' => array( 'style1', 'style2', 'style3' ),
									'note'    => __( 'Visual theme variation', 'modern-cart' ),
								),
								'enable_coupon_field' => array(
									'type'    => 'string',
									'options' => array( 'show', 'hide', 'minimize' ),
									'note'    => __( 'Coupon field visibility', 'modern-cart' ),
								),
								'section_styling'     => array(
									'type'    => 'string',
									'options' => array( 'accordion', 'default' ),
									'note'    => __( 'Section collapse behavior', 'modern-cart' ),
								),
							),
						),
						'moderncart_floating'   => array(
							'type'                 => 'object',
							'description'          => __( 'Floating cart icon positioning and styling. Controls: screen position (combination of top/bottom and left/right), and color scheme for floating cart icon and bubble including backgrounds, text colors, quantity indicators, and badge styling.', 'modern-cart' ),
							'additionalProperties' => array(
								'type' => array( 'string', 'number', 'boolean' ),
							),
							'value_hints'          => array(
								'floating_cart_position' => array(
									'type'    => 'string',
									'options' => array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' ),
									'note'    => __( 'Screen position for floating cart icon', 'modern-cart' ),
								),
							),
						),
						'moderncart_appearance' => array(
							'type'                 => 'object',
							'description'          => __( 'Global visual styling and color scheme. Controls: primary brand colors, typography colors (headings, body text), cart header alignment and sizing, comprehensive UI element colors including buttons, backgrounds, icons, quantity controls, and count badges. Color values stored as hex strings.', 'modern-cart' ),
							'additionalProperties' => array(
								'type' => array( 'string', 'number', 'boolean' ),
							),
							'value_hints'          => array(
								'cart_header_text_alignment' => array(
									'type'    => 'string',
									'options' => array( 'left', 'center', 'right' ),
									'note'    => __( 'Cart header text alignment', 'modern-cart' ),
								),
							),
						),
					),
				),
				'context'  => array(
					'type'                 => 'object',
					'description'          => __( 'Optional contextual metadata for the update operation.', 'modern-cart' ),
					'additionalProperties' => true,
				),
			),
		);
	}

	/**
	 * Execute the ability.
	 *
	 * Returns a plain data array on success, or WP_Error on failure —
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

		if ( ! isset( $args['settings'] ) || ! is_array( $args['settings'] ) || empty( $args['settings'] ) ) {
			return Response::error(
				__( 'Expected "settings" object with Modern Cart configuration values.', 'modern-cart' ),
				'moderncart_invalid_input'
			);
		}

		/**
		 * Extracts settings and context from arguments array.
		 *
		 * $args['settings'] is confirmed as an array by the guard above; annotation narrows the type.
		 *
		 * @var array<string, mixed> $settings
		 */
		$settings = $args['settings'];
		$context  = isset( $args['context'] ) && is_array( $args['context'] ) ? $args['context'] : array();

		$result = \MCW_ZipWP_Helper::update_settings( $settings, $context );

		if ( empty( $result['applied'] ) ) {
			$error_details = array();

			if ( ! empty( $result['rejected'] ) ) {
				$error_details[] = __( 'Rejected:', 'modern-cart' ) . ' ' . wp_json_encode( $result['rejected'] );
			}

			if ( ! empty( $result['skipped'] ) ) {
				$error_details[] = __( 'Skipped:', 'modern-cart' ) . ' ' . wp_json_encode( $result['skipped'] );
			}

			$result_messages = is_array( $result['messages'] ) ? $result['messages'] : array();
			$error_message   = implode( '; ', array_filter( array_merge( $result_messages, $error_details ) ) );
			return Response::error(
				$error_message ? $error_message : __( 'No settings were updated.', 'modern-cart' ),
				'moderncart_no_settings_updated'
			);
		}

		$result_applied = is_array( $result['applied'] ) ? $result['applied'] : array();
		$total_updated  = array_sum( array_map( 'count', $result_applied ) );

		return Response::success(
			array(
				'settings_updated' => array(
					'applied'     => $result_applied,
					'rejected'    => is_array( $result['rejected'] ) ? $result['rejected'] : array(),
					'skipped'     => is_array( $result['skipped'] ) ? $result['skipped'] : array(),
					'total_count' => $total_updated,
				),
			)
		);
	}
}
