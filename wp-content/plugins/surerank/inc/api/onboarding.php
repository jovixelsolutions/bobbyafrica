<?php
/**
 * Onboarding class
 *
 * Handles onboarding related REST API endpoints for the SureRank plugin.
 *
 * @package SureRank\Inc\API
 */

namespace SureRank\Inc\API;

use SureRank\Inc\Admin\Helper;
use SureRank\Inc\Admin\Update_Timestamp;
use SureRank\Inc\Functions\Requests;
use SureRank\Inc\Functions\Send_Json;
use SureRank\Inc\Functions\Settings;
use SureRank\Inc\Functions\Update;
use SureRank\Inc\Traits\Get_Instance;
use WP_REST_Request;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Onboarding
 *
 * Handles onboarding related REST API endpoints.
 */
class Onboarding extends Api_Base {
	use Get_Instance;

	/**
	 * Default user details.
	 */
	public const DEFAULT_USER_DETAILS = [
		'first_name' => '',
		'last_name'  => '',
		'email'      => '',
		'skip'       => 'no',
		'lead'       => false,
	];

	/**
	 * Route Onboarding
	 */
	protected const ONBOARDING = '/onboarding';

	/**
	 * Route Improve Description
	 */
	protected const IMPROVE_DESCRIPTION = '/onboarding/improve-description';

	/**
	 * Register API routes.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->get_api_namespace(),
			self::ONBOARDING,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_website_details' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'role_capability'     => 'global_setting',
				'args'                => [
					'website_type'         => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Type of the website.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'website_name'         => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Name of the website.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'business_description' => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Business description of the website.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'website_owner_name'   => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Name of the website owner.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'website_owner_phone'  => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Phone number of the website owner.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'organization_type'    => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Type of the organization.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'about_page'           => [
						'type'              => 'integer',
						'required'          => false,
						'sanitize_callback' => 'absint',

					],
					'contact_page'         => [
						'type'              => 'integer',
						'required'          => false,
						'sanitize_callback' => 'absint',
					],
					'social_profiles'      => [
						'type'        => 'object',
						'required'    => false,
						'description' => __( 'Social profiles URLs.', 'surerank' ),
						'properties'  => array_fill_keys(
							self::get_social_profile_keys(),
							[
								'type'              => 'string',
								'sanitize_callback' => 'esc_url_raw',
								'validate_callback' => static function( $value ) {
									return filter_var( $value, FILTER_VALIDATE_URL ) !== false;
								},
							]
						),
					],
					'website_logo'         => [
						'type'     => 'string',
						'required' => false,
					],
					'first_name'           => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'First name of the user.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'last_name'            => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Last name of the user.', 'surerank' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function( $value ) {
							return is_string( $value );
						},
					],
					'email'                => [
						'type'              => 'string',
						'required'          => false,
						'description'       => __( 'Email address of the user.', 'surerank' ),
						'sanitize_callback' => 'sanitize_email',
						'validate_callback' => static function( $value ) {
							if ( empty( $value ) ) {
								return true;
							}
								return is_email( $value );
						},
					],
					'agree_to_terms'       => [
						'type'              => 'boolean',
						'required'          => false,
						'description'       => __( 'Whether the user opted into usage updates.', 'surerank' ),
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
				],
			]
		);

		register_rest_route(
			$this->get_api_namespace(),
			self::IMPROVE_DESCRIPTION,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'improve_description' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => [
					'business_name'     => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'business_desc'     => [
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					],
					'business_category' => [
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					],
					'language'          => [
						'type'              => 'string',
						'required'          => false,
						'default'           => 'en',
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);
	}

	/**
	 * Update common onboarding data.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Onboarding data.
	 * @return bool
	 */
	public static function update_common_onboarding_data( $data ) {
		if ( empty( $data ) ) {
			return false;
		}

		$instance = self::get_instance();

		$auth_data    = get_option( 'surerank_auth', [] );
		$current_user = wp_get_current_user();

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
			'social_profiles'      => [],
			'email'                => $auth_data['user_email'] ?? ( $current_user->user_email ?? '' ),
			'first_name'           => $current_user->first_name ?? '',
			'last_name'            => $current_user->last_name ?? '',
		];

		$data = wp_parse_args(
			$data,
			$defaults
		);

		$settings = Settings::get();

		$instance->set_onboarding_data( $data, $settings );
		$updated_onboarding = $instance->process_onboarding_data( $data, $settings );
		$updated_settings   = Update::option( SURERANK_SETTINGS, $settings );

		if ( $updated_onboarding && $updated_settings ) {
			$instance->set_schemas_pages( $data, $settings );
			Update_Timestamp::timestamp_option();
			return true;
		}

		return false;
	}

	/**
	 * Save Website Details
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return void
	 */
	public function save_website_details( $request ) {
		$data = $request->get_params();

		if ( empty( $data ) ) {
			Send_Json::error( [ 'message' => __( 'Invalid data provided', 'surerank' ) ] );
		}

		$this->process_usage_optin( $data );

		if ( self::update_common_onboarding_data( $data ) ) {
			Update::option( 'surerank_onboarding_completed', true );
			Send_Json::success( [ 'message' => __( 'Settings updated successfully', 'surerank' ) ] );
		}

		Send_Json::error( [ 'message' => __( 'Failed to update settings', 'surerank' ) ] );
	}

	/**
	 * Improve business description using AI
	 *
	 * @since 1.6.2
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return void
	 */
	public function improve_description( $request ) {
		$params = $request->get_params();

		$body = [
			'business_name'     => $params['business_name'] ?? '',
			'business_desc'     => $params['business_desc'] ?? '',
			'business_category' => $params['business_category'] ?? '',
			'language'          => $params['language'] ?? 'en',
		];

		$json_body = wp_json_encode( $body );

		if ( false === $json_body ) {
			Send_Json::error( [ 'message' => __( 'Failed to encode request data', 'surerank' ) ] );
		}

		$response = wp_safe_remote_post(
			'https://api.zipwp.com/api/v1/sites/suggest-description',
			[
				'headers' => [
					'Content-Type'     => 'application/json',
					'Accept'           => 'application/json, text/plain, */*',
					'X-Requested-With' => 'XMLHttpRequest',
				],
				'body'    => (string) $json_body,
				'timeout' => 30, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			]
		);

		if ( is_wp_error( $response ) ) {
			Send_Json::error(
				[
					'message' => __( 'Failed to improve description', 'surerank' ),
					'error'   => $response->get_error_message(),
				]
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );

		if ( $response_code !== 200 ) {
			Send_Json::error(
				[
					'message' => __( 'Failed to improve description', 'surerank' ),
					'code'    => $response_code,
				]
			);
		}

		$data = json_decode( $response_body, true );

		if ( isset( $data['description'] ) ) {
			Send_Json::success(
				[
					'message'     => __( 'Description improved successfully', 'surerank' ),
					'description' => sanitize_textarea_field( $data['description'] ),
				]
			);
		}

		Send_Json::error( [ 'message' => __( 'Invalid response from AI service', 'surerank' ) ] );
	}

	/**
	 * Set Schemas Pages
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return void
	 */
	public function set_schemas_pages( $data, $settings ) {
		$this->set_about_page( $data, $settings );
		$this->set_contact_page( $data, $settings );
	}

	/**
	 * Set About Page
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return bool|int
	 */
	public function set_about_page( $data, &$settings ) {
		$about_page = $data['about_page'] ?? 0;

		if ( ! $about_page ) {
			return false;
		}

		$default_schemas                               = Settings::prepare_schemas( $settings, 'page', $about_page, true );
		$schema                                        = $this->find_schema_by_type( $settings, 'WebPage' );
		$default_schemas[ $schema ]['fields']['@type'] = 'AboutPage';
		$default_schemas[ $schema ]['type']            = 'AboutPage';
		$schemas                                       = [
			'schemas' => $default_schemas,
		];

		return Update::post_meta( $about_page, 'surerank_settings_schemas', $schemas );
	}

	/**
	 * Set Contact Page
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return bool|int
	 */
	public function set_contact_page( $data, &$settings ) {
		$contact_page = $data['contact_page'] ?? 0;

		if ( ! $contact_page ) {
			return false;
		}

		$default_schemas                               = Settings::prepare_schemas( $settings, 'page', $contact_page, true );
		$schema                                        = $this->find_schema_by_type( $settings, 'WebPage' );
		$default_schemas[ $schema ]['fields']['@type'] = 'ContactPage';
		$default_schemas[ $schema ]['type']            = 'ContactPage';
		$schemas                                       = [
			'schemas' => $default_schemas,
		];

		return Update::post_meta( $contact_page, 'surerank_settings_schemas', $schemas );
	}

	/**
	 * Save Onboarding Data
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @return bool
	 */
	public function save_onboarding_data( $data ) {
		return Update::option( 'surerank_settings_onboarding', $data );
	}

	/**
	 * Set Website Details
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return void
	 */
	public function set_website_details( $data, &$settings ) {
		$sanitized_name = sanitize_text_field( $data['website_name'] ?? '' );

		// Only update the Organization schema name when a non-empty value is provided so we
		// don't overwrite the default (e.g., %site.title%) with an empty string during imports.
		if ( '' !== $sanitized_name ) {
			$this->update_schema_field( $settings, 'Organization', 'name', $data['website_name'] );
		}
		$this->update_schema_field( $settings, 'Organization', '@type', $data['organization_type'] );
		$this->update_schema_field( $settings, 'Organization', 'logo', $data['website_logo'] );
		$this->update_schema_field( $settings, 'Organization', 'telephone', $data['website_owner_phone'] );

		if ( ! empty( $data['email'] ) ) {
			$this->update_schema_field( $settings, 'Organization', 'email', $data['email'] );
		}

		$this->update_schema_field( $settings, 'Person', 'name', $data['website_owner_name'] );
		$this->update_schema_field( $settings, 'Person', 'image', $data['website_logo'] );
		if ( '' !== $sanitized_name ) {
			update_option( 'blogname', $sanitized_name );
		}
	}

	/**
	 * Update Schema Field
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Settings.
	 * @param string               $schema_type Schema type.
	 * @param string               $field Field name.
	 * @param mixed                $value Field value.
	 * @return void
	 */
	public function update_schema_field( &$settings, $schema_type, $field, $value ) {

		$schema_id = $this->find_schema_by_type( $settings, $schema_type );
		if ( is_null( $schema_id ) ) {
			return; // Bail early if schema is not found.
		}

		if ( ! isset( $settings['schemas'][ $schema_id ]['fields'] ) ) {
			$settings['schemas'][ $schema_id ]['fields'] = [];
		}

		$settings['schemas'][ $schema_id ]['fields'][ $field ] = $value;
	}

	/**
	 * Find Schema by Type
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $settings Settings array.
	 * @param string               $type Schema type to find.
	 * @return string|null Schema ID if found, null otherwise.
	 */
	public function find_schema_by_type( &$settings, $type ) {
		if ( isset( $settings['schemas'] ) && is_array( $settings['schemas'] ) ) {
			foreach ( $settings['schemas'] as $schema_id => $schema ) {
				if ( isset( $schema['type'] ) && $schema['type'] === $type ) {
					return $schema_id;
				}
			}
		}
		return null;
	}

	/**
	 * Person or Organization
	 *
	 * @since 1.0.0
	 * @param string $website_type Website type.
	 * @return string
	 */
	public function person_or_organization( $website_type ) {
		return in_array( $website_type, [ 'blog', 'personal' ] ) ? 'person' : 'organization';
	}

	/**
	 * Get Social Profile Keys
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>|array<int, array<string, mixed>>
	 */
	public static function get_social_profile_keys() {
		return array_column( self::social_profiles(), 'id' );
	}

	/**
	 * Set Social Profiles
	 *
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	public static function social_profiles() {
		return apply_filters(
			'surerank_social_profiles',
			[
				[
					'label'             => 'Facebook',
					'id'                => 'facebook',
					'placeholder'       => 'https://www.facebook.com/my-page-url',
					'show_in_dashboard' => false,
				],
				[
					'label'             => 'X',
					'id'                => 'twitter',
					'placeholder'       => 'https://x.com/myaccount',
					'show_in_dashboard' => false,
				],
				[
					'label'       => 'Instagram',
					'id'          => 'instagram',
					'placeholder' => 'https://www.instagram.com/my-page-url',
				],
				[
					'label'       => 'YouTube',
					'id'          => 'youtube',
					'placeholder' => 'https://www.youtube.com/my-channel-url',
				],
				[
					'label'       => 'LinkedIn',
					'id'          => 'linkedin',
					'placeholder' => 'https://www.linkedin.com/company/my-company',
				],
				[
					'label'       => 'TikTok',
					'id'          => 'tiktok',
					'placeholder' => 'https://www.tiktok.com/@username',
				],
				[
					'label'       => 'Pinterest',
					'id'          => 'pinterest',
					'placeholder' => 'https://www.pinterest.com/my-page-url',
				],
				[
					'label'       => 'WhatsApp',
					'id'          => 'whatsapp',
					'placeholder' => 'https://wa.me/number',
				],
				[
					'label'       => 'Telegram',
					'id'          => 'telegram',
					'placeholder' => 'https://t.me/username',
				],
				[
					'label'       => 'Yelp',
					'id'          => 'yelp',
					'placeholder' => 'https://www.yelp.com/biz/business-name-location',
				],
				[
					'label'       => 'BlueSky',
					'id'          => 'bluesky',
					'placeholder' => 'https://bsky.app/profile/username',
				],
			]
		);
	}

	/**
	 * Set Social Schema
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return void
	 */
	public function set_social_schema( $data, &$settings ) {
		$same_as_urls = array_filter(
			$data['social_profiles'] ?? [],
			static function( $url ) {
				return ! empty( $url );
			}
		);
		$this->update_schema_field( $settings, 'Organization', 'sameAs', $same_as_urls );
	}

	/**
	 * Get User Details
	 *
	 * @since 1.6.4
	 * @param string $key     Optional. Specific key to retrieve.
	 * @param mixed  $default Optional. Default value if key not found.
	 * @return mixed User details array or specific value.
	 */
	public function get_user_details( $key = '', $default = '' ) {
		$user_details = get_option( 'surerank_onboarding_user_details', self::DEFAULT_USER_DETAILS );

		if ( ! empty( $key ) ) {
			return $user_details[ $key ] ?? $default;
		}

		return $user_details;
	}

	/**
	 * Set User Details
	 *
	 * @since 1.6.4
	 * @param array<string, mixed> $data User details data.
	 * @return bool
	 */
	public function set_user_details( $data ) {
		$existing     = get_option( 'surerank_onboarding_user_details', self::DEFAULT_USER_DETAILS );
		$user_details = wp_parse_args( $data, $existing );
		return update_option( 'surerank_onboarding_user_details', $user_details );
	}

	/**
	 * Persist onboarding usage opt-in state.
	 *
	 * @param array<string, mixed> $data Request data.
	 * @return void
	 */
	private function process_usage_optin( $data ) {
		if ( ! isset( $data['agree_to_terms'] ) ) {
			return;
		}

		$has_opted_in = rest_sanitize_boolean( (bool) $data['agree_to_terms'] );
		Update::option( 'surerank_usage_optin', $has_opted_in ? 'yes' : 'no' );

		if ( ! $has_opted_in ) {
			$this->set_user_details( [ 'lead' => false ] );
		}
	}

	/**
	 * Set Onboarding Data
	 *
	 * We need to store the facebook page url and twitter profile username in the settings array as per the new requirement.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return void
	 */
	private function set_onboarding_data( $data, &$settings ) {
		$settings['facebook_page_url']        = $data['social_profiles']['facebook'] ?? '';
		$settings['twitter_profile_username'] = $data['social_profiles']['twitter'] ?? '';
	}

	/**
	 * Process Onboarding Data
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return bool
	 */
	private function process_onboarding_data( $data, &$settings ) {
		$this->set_website_details( $data, $settings );
		$this->set_social_schema( $data, $settings );
		$this->set_social_profiles( $data, $settings );
		$this->set_person_or_organization( $data, $settings );

		$lead = [
			'email'      => $data['email'] ?? '',
			'first_name' => $data['first_name'] ?? '',
			'last_name'  => $data['last_name'] ?? '',
		];
		$this->generate_lead( $lead );

		return $this->save_onboarding_data( $data );
	}

	/**
	 * Set Person or Organization.
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return void
	 */
	private function set_person_or_organization( &$data, &$settings ) {
		$type                           = $this->person_or_organization( $data['website_type'] );
		$data['person_or_organization'] = $type;

		/**
		 * Depending on the type, we need to set the publisher to the correct schema.
		 * We need to set the publisher for WebPage and Article.
		 */
		$schema = $type === 'organization' ? '%schemas.organization%' : '%schemas.person%';

		foreach ( [ 'WebPage', 'Article' ] as $context ) {
			$this->update_schema_field( $settings, $context, 'publisher', $schema );
		}
	}

	/**
	 * Set Social Profiles
	 *
	 * @since 1.0.0
	 * @param array<string, mixed> $data Data.
	 * @param array<string, mixed> $settings Settings.
	 * @return void
	 */
	private function set_social_profiles( &$data, &$settings ) {
		$settings['social_profiles'] = $data['social_profiles'];
		unset( $data['social_profiles'] );
	}

	/**
	 * Generate Lead
	 *
	 * @since 1.6.4
	 * @param array<string, mixed> $lead Lead data.
	 * @return void
	 */
	private function generate_lead( $lead ) {
		if ( empty( $lead['email'] ) ) {
			return;
		}

		$user_details = $this->get_user_details();

		if ( ! empty( $user_details['lead'] ) ) {
			return;
		}

		$this->set_user_details( $lead );

		$domain = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! is_string( $domain ) ) {
			$domain = '';
		}

		$response = Requests::post(
			'https://metrics.brainstormforce.com/wp-json/bsf-metrics-server/v1/subscribe/',
			[
				'headers' => [
					'Content-Type' => 'application/json',
				],
				'body'    => wp_json_encode(
					[
						'email'      => $lead['email'],
						'first_name' => $lead['first_name'],
						'last_name'  => $lead['last_name'],
						'domain'     => $domain,
						'source'     => 'surerank',
					]
				),
			]
		);

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$this->set_user_details( [ 'lead' => true ] );
		}
	}

}
