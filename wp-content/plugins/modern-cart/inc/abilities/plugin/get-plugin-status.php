<?php
/**
 * Get Plugin Status Ability
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Abilities\Plugin;

use ModernCart\Inc\Abilities\Abstract_Ability;
use ModernCart\Inc\Abilities\Response;
use ModernCart\Inc\Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Get_Plugin_Status
 *
 * Returns Modern Cart version, Pro plugin status, onboarding completion,
 * and site maintenance mode state.
 */
class Get_Plugin_Status extends Abstract_Ability {

	/**
	 * Configure the ability.
	 *
	 * @return void
	 */
	public function configure() {
		$this->id           = 'moderncart/get-plugin-status';
		$this->category     = 'moderncart';
		$this->label        = __( 'Get Plugin Status', 'modern-cart' );
		$this->description  = __( 'Returns Modern Cart plugin version, Pro plugin installation status (not-installed, inactive, or active), onboarding completion flag, and site maintenance mode state. Use this as the first call in any setup or diagnostic flow.', 'modern-cart' );
		$this->capability   = 'manage_options';
		$this->instructions = __( 'Call this as the first step in any setup or diagnostic flow. Check pro_status before attempting to set cart_style to "popup" — that value requires pro_status to be "active". If is_onboarding_complete is false, apply initial settings via update-settings then call complete-onboarding.', 'modern-cart' );
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
	 * Execute the ability.
	 *
	 * @param array<string, mixed> $args Input arguments (none required).
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $args ) {
		$version_option = get_option( 'moderncart_version', array() );

		$version_current  = '';
		$version_previous = '';

		if ( is_array( $version_option ) ) {
			$version_current  = isset( $version_option['current'] ) ? (string) $version_option['current'] : '';
			$version_previous = isset( $version_option['previous'] ) ? (string) $version_option['previous'] : '';
		}

		// Fall back to the constant if the option has not been written yet.
		if ( '' === $version_current && defined( 'MODERNCART_VER' ) ) {
			$version_current = MODERNCART_VER;
		}

		$is_onboarding_complete = 'yes' === get_option( 'moderncart_is_onboarding_complete', 'no' );
		$pro_status             = Helper::get_pro_status();
		$is_maintenance_mode    = Helper::is_maintenance_mode();

		return Response::success(
			array(
				'version'                => array(
					'current'  => $version_current,
					'previous' => $version_previous,
				),
				'pro_status'             => $pro_status,
				'is_onboarding_complete' => $is_onboarding_complete,
				'is_maintenance_mode'    => $is_maintenance_mode,
			)
		);
	}
}
