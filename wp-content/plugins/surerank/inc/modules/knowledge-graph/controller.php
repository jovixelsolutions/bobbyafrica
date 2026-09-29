<?php
/**
 * Knowledge Graph Controller
 *
 * Main module controller for handling knowledge graph settings.
 *
 * @package SureRank\Inc\Modules\Knowledge_Graph
 * @since 1.6.6
 */

namespace SureRank\Inc\Modules\Knowledge_Graph;

use SureRank\Inc\Admin\Helper;
use SureRank\Inc\API\Onboarding;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Traits\Get_Instance;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Controller class
 *
 * Main module class for knowledge graph functionality.
 */
class Controller {

	use Get_Instance;

	/**
	 * Get knowledge graph settings.
	 *
	 * @since 1.6.6
	 * @return array<string, mixed> Knowledge graph settings.
	 */
	public function get_settings() {
		$settings = Get::option( 'surerank_settings_onboarding', [] );

		// Provide defaults if settings are empty.
		$defaults = [
			'website_type'         => '',
			'website_name'         => '',
			'business_description' => Helper::get_saved_business_details( 'business_description' ),
			'website_owner_name'   => '',
			'organization_type'    => 'Organization',
			'website_owner_phone'  => '',
			'website_logo'         => '',
			'about_page'           => 0,
			'contact_page'         => 0,
		];

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Update knowledge graph settings.
	 *
	 * @since 1.6.6
	 * @param array<string, mixed> $data Settings data to update.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public function update_settings( $data ) {
		if ( empty( $data ) ) {
			return new WP_Error( 'invalid_data', __( 'Invalid data provided', 'surerank' ) );
		}

		// Use the existing onboarding update method to ensure all schema updates happen.
		$result = Onboarding::update_common_onboarding_data( $data );

		if ( ! $result ) {
			return new WP_Error( 'update_failed', __( 'Failed to update settings', 'surerank' ) );
		}

		return true;
	}
}
