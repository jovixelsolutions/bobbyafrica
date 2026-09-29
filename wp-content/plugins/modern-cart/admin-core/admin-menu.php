<?php
/**
 * Admin menu.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Admin_Core;

use ModernCart\Admin_Core\Inc\Settings_Fields;
use ModernCart\Inc\Helper;
use ModernCart\Inc\Traits\Get_Instance;

/**
 * Admin menu
 *
 * @since 0.0.1
 */
class Admin_Menu {
	use Get_Instance;

	/**
	 * Tailwind assets base url
	 *
	 * @var string
	 * @since 0.0.1
	 */
	private $tailwind_assets = MODERNCART_URL . 'admin-core/assets/build/';

	/**
	 * Instance of Helper class
	 *
	 * @var Helper
	 * @since 0.0.1
	 */
	private $helper;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		$this->helper = new Helper();
		add_action( 'admin_menu', [ $this, 'settings_page' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'settings_page_scripts' ] );
		add_action( 'wp_ajax_moderncart_update_settings', [ $this, 'moderncart_update_settings' ] );
		add_action( 'wp_ajax_moderncart_fetch_whats_new', [ $this, 'fetch_whats_new' ] );

		add_action( 'wp_ajax_moderncart_complete_onboarding', [ $this, 'complete_onboarding' ] );
	}

	/**
	 * Adds admin menu for settings page
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function settings_page(): void {
		add_submenu_page(
			'woocommerce',
			esc_html__( 'Settings - Modern Cart Woo', 'modern-cart' ),
			esc_html__( 'Modern Cart', 'modern-cart' ),
			'manage_woocommerce',
			'moderncart_settings',
			[ $this, 'render' ],
			57
		);
	}

	/**
	 * Renders main div to implement tailwind UI
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function render(): void {
		?>
		<div class="moderncart-settings" id="moderncart-settings"></div>
		<?php
	}

	/**
	 * Enqueue settings page script and style
	 *
	 * @param string $hook Current page hook name.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function settings_page_scripts( $hook ): void {
		if ( 'woocommerce_page_moderncart_settings' !== $hook ) {
			return;
		}

		$is_onboarding = Helper::is_admin_onboarding_screen();

		$version           = MODERNCART_VER;
		$script_asset_path = MODERNCART_DIR . 'admin-core/assets/build/settings.asset.php';
		$script_info       = file_exists( $script_asset_path )
			? include $script_asset_path
			: [
				'dependencies' => [],
				'version'      => $version,
			];

		$script_dep = array_merge( $script_info['dependencies'], [ 'updates' ] );

		wp_register_script( 'moderncart_settings', $this->tailwind_assets . 'settings.js', $script_dep, $version, true );
		wp_enqueue_script( 'moderncart_settings' );
		wp_localize_script(
			'moderncart_settings',
			'moderncart_settings',
			apply_filters(
				'moderncart_settings_admin_localize_script',
				[
					'ajax_url'                     => admin_url( 'admin-ajax.php' ),
					'proStatus'                    => Helper::get_pro_status(),
					'update_nonce'                 => wp_create_nonce( 'moderncart_update_settings' ),
					MODERNCART_MAIN_SETTINGS       => $this->helper->get_option( MODERNCART_MAIN_SETTINGS ),
					MODERNCART_SETTINGS            => $this->helper->get_option( MODERNCART_SETTINGS ),
					MODERNCART_FLOATING_SETTINGS   => $this->helper->get_option( MODERNCART_FLOATING_SETTINGS ),
					MODERNCART_APPEARANCE_SETTINGS => $this->helper->get_option( MODERNCART_APPEARANCE_SETTINGS ),
					'onboarding'                   => [
						'inProgress' => $is_onboarding,
						'ajaxUrl'    => add_query_arg(
							[
								'action' => 'moderncart_complete_onboarding',
								'nonce'  => wp_create_nonce( 'moderncart_onboarding_nonce' ),
							],
							admin_url( 'admin-ajax.php' )
						),
						'defaults'   => $this->get_onboarding_defaults(),
					],
					'whats_new_rss_feed'           => $this->get_whats_new_rss_feeds_data(),
					'theme_colors'                 => Helper::get_compatible_colors(),
					'color_default_vars'           => Helper::is_astra_active() ? Helper::get_astra_color_vars() : $this->helper->get_defaults()[ MODERNCART_APPEARANCE_SETTINGS ],
					'moderncart_cart_icons'        => Helper::get_cart_icons(),
					'knowledge_base'               => Helper::get_knowledge_base(),
					'settings_tabs'                => Settings_Fields::get_tabs(),
					'settings_fields'              => Settings_Fields::get_fields(),
					'settings_icons'               => Settings_Fields::get_icon_svg(),
					'versionBadgeInfo'             => apply_filters(
						'moderncart_admin_version_badge_info',
						[
							'label' => 'Free',
							'title' => MODERNCART_VER,
						]
					),
				]
			)
		);

		wp_register_style( 'moderncart_settings', $this->tailwind_assets . 'settings.css', [], $version );
		wp_style_add_data( 'moderncart_settings', 'rtl', 'replace' );
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'moderncart_settings' );
		wp_enqueue_style( 'moderncart_font', 'https://fonts.googleapis.com/css2?family=Figtree:wght@300;400;500;600&display=swap', [], MODERNCART_VER );

		// Load translations strings used in the Javascript file.
		wp_set_script_translations( 'moderncart_settings', 'modern-cart', MODERNCART_DIR . 'languages' );

		// Enqueue WordPress media library for custom icon upload.
		wp_enqueue_media();

		$admin_inline_style = '
			.woocommerce_page_moderncart_settings #wpfooter {
				display: none !important;
			}
			.whats-new-rss-flyout.closed {
				visibility: hidden;
			}
		';

		if ( $is_onboarding ) {
			$admin_inline_style .= '
			html.wp-toolbar {
				padding: 0;
			}
			#wpcontent {
				margin: 0;
				padding: 0;
			}
			#wpadminbar, #adminmenumain {
				display:none;
			}';
		}

		wp_add_inline_style( 'moderncart_settings', $admin_inline_style );
	}

	/**
	 * Ajax handler for submit action on settings page.
	 * Updates settings data in database.
	 *
	 * @return void
	 * @since 0.0.1
	 */
	public function moderncart_update_settings(): void {
		check_ajax_referer( 'moderncart_update_settings', 'security' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'modern-cart' ) );
		}

		$keys = [];

		if ( ! empty( $_POST[ MODERNCART_MAIN_SETTINGS ] ) ) {
			$keys[] = MODERNCART_MAIN_SETTINGS;
		}

		if ( ! empty( $_POST[ MODERNCART_SETTINGS ] ) ) {
			$keys[] = MODERNCART_SETTINGS;
		}

		if ( ! empty( $_POST[ MODERNCART_FLOATING_SETTINGS ] ) ) {
			$keys[] = MODERNCART_FLOATING_SETTINGS;
		}

		if ( ! empty( $_POST[ MODERNCART_APPEARANCE_SETTINGS ] ) ) {
			$keys[] = MODERNCART_APPEARANCE_SETTINGS;
		}

		if ( empty( $keys ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'No valid setting keys found.', 'modern-cart' ) ] );
		}

		$succeded = 0;
		foreach ( $keys as $key ) {
			if ( ! isset( $_POST[ $key ] ) || ! is_string( $_POST[ $key ] ) ) {
				continue;
			}

			// We are sanitizing $_POST inside update_settings before saving data into database.
			$settings_data = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing --> Sanitized in next step below | Nonce Already verified | Already unslashed.
			if ( $this->update_settings( $settings_data, $key ) ) {
				$succeded++;
			}
		}

		if ( count( $keys ) === $succeded ) {
			// Set flag for first_settings_saved analytics event.
			if ( ! get_option( 'mcw_first_settings_saved', false ) ) {
				update_option( 'mcw_first_settings_saved', true, false );
			}

			// Clear the state-events transient so detect_state_events() re-runs on the next
			// admin page load. This ensures state-based events (free_shipping_bar_enabled,
			// express_checkout_enabled, etc.) are queued promptly after settings change,
			// rather than waiting up to 24 hours for the transient to expire naturally.
			delete_transient( 'mcw_state_events_checked' );

			wp_send_json_success( [ 'message' => esc_html__( 'Settings saved successfully.', 'modern-cart' ) ] );
		}

		wp_send_json_error( [ 'message' => esc_html__( 'Failed to save settings.', 'modern-cart' ) ] );
	}

	/**
	 * Update dettings data in database
	 *
	 * @param string $settings_data JSON String raw input data.
	 * @param string $key options key.
	 * @return bool
	 * @since 0.0.1
	 */
	public function update_settings( $settings_data, $key ) {
		// Sanitize and decode input JSON.
		$data = json_decode( $settings_data, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
			return false; // Invalid JSON input.
		}

		// Sanitize the decoded array.
		$data         = $this->sanitize_data( $key, $data );
		$default_data = $this->helper->get_option( $key );
		$data         = wp_parse_args( $data, $default_data );

		if ( MODERNCART_SETTINGS === $key && isset( $data['enable_usage_tracking'] ) ) {
			update_option( 'mcw_usage_optin', true === $data['enable_usage_tracking'] ? 'yes' : 'no' );
		}

		// update_option() returns false for both DB errors and "value unchanged" — we call it
		// for its side-effect but always return true here since the data was valid and processed
		// successfully. The AJAX handler must not treat an unchanged value as a failure.
		update_option( $key, $data );
		return true;
	}

	/**
	 * Sanitize data as per data type
	 *
	 * @param string        $key options key.
	 * @param array<string> $data raw input received from user.
	 * @return array<mixed>
	 * @since 0.0.1
	 */
	public function sanitize_data( $key, $data ) {
		$schema = $this->helper->get_defaults( true )[ $key ];

		$temp = [];

		foreach ( $data as $setting_key => $value ) {
			$sanitized_key = sanitize_key( $setting_key );

			if ( ! isset( $schema[ $sanitized_key ] ) || ! is_array( $schema[ $sanitized_key ] ) || ! isset( $schema[ $sanitized_key ]['type'] ) ) {
				continue;
			}
			$type = $schema[ $sanitized_key ]['type'];
			switch ( $type ) {
				case 'boolean':
					$sanitized_value = rest_sanitize_boolean( $value );
					break;
				case 'number':
					$sanitized_value = absint( $value );
					break;
				case 'hex':
					$sanitized_value = sanitize_hex_color( $value );
					break;
				default:
					$sanitized_value = sanitize_text_field( $value );
					break;
			}
			$temp[ $sanitized_key ] = $sanitized_value;
		}

		return $temp;
	}

	/**
	 * Prepare the array of RSS Feeds of Modern Cart for Whats New slide-out panel.
	 *
	 * @since 0.0.1
	 * @return array<string> The prepared array of RSS feeds.
	 */
	public function get_whats_new_rss_feeds_data() {
		return [
			'key'   => 'modern-cart',
			'label' => 'Modern Cart',
			'url'   => add_query_arg(
				[
					'action' => 'moderncart_fetch_whats_new',
					'nonce'  => wp_create_nonce( 'moderncart_fetch_whats_new' ),
				],
				admin_url( 'admin-ajax.php' )
			),
		];
	}

	/**
	 * Fetch the Whats New RSS feed from the URL.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function fetch_whats_new(): void {
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'moderncart_fetch_whats_new' ) ) {
			// Verify the nonce, if it fails, return an error.
			wp_send_json_error( [ 'message' => esc_html__( 'Nonce verification failed.', 'modern-cart' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'modern-cart' ) );
		}

		// Fetch the RSS feed from the URL. This saves us from the CORS issue.
		$response = wp_remote_get( 'https://cartflows.com/product/modern-cart/feed/' ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get -- Not a VIP environment; wp_remote_get is appropriate here.

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Failed to fetch RSS feed.', 'modern-cart' ) ] );
		}

		$body = wp_remote_retrieve_body( $response );

		// Output the raw RSS/XML body with the correct Content-Type header.
		// wp_kses() must NOT be used here: it is an HTML sanitizer that strips XML CDATA sections,
		// namespaced tags (content:encoded, dc:creator, atom:link), pubDate elements, and the XML
		// declaration — all of which are required by the whats-new-rss JS library for parsing.
		// The feed is fetched from cartflows.com (trusted first-party URL) and is only accessible
		// to users with the manage_options capability, so no further sanitization is needed.
		header( 'Content-Type: application/rss+xml; charset=UTF-8' );
		echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Proxying RSS/XML from trusted first-party URL (cartflows.com); restricted to manage_options users.
		exit;
	}

	/**
	 * Complete onboarding process.
	 */
	public function complete_onboarding(): void {
		if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'moderncart_onboarding_nonce' ) ) {
			// Verify the nonce, if it fails, return an error.
			wp_send_json_error( [ 'message' => esc_html__( 'Nonce verification failed.', 'modern-cart' ) ] );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'modern-cart' ) );
		}

		$raw_input       = file_get_contents( 'php://input' ); // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsRemoteFile
		$onboarding_data = is_string( $raw_input ) ? json_decode( $raw_input, true ) : null;

		if ( empty( $onboarding_data ) || ! is_array( $onboarding_data ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Invalid data. Onboarding data cannot be empty', 'modern-cart' ) ] );
		}

		$mapped_data = $this->helper->get_defaults();

		$installable_plugin_slugs = [];

		$user_details_data = [];

		// Detect skipped steps for analytics before processing.
		$skipped_steps = [];
		foreach ( $onboarding_data as $index => $data ) {
			if ( is_array( $data ) && ! empty( $data['hasSkipped'] ) ) {
				$skipped_steps[] = (string) $index;
			}
		}
		if ( ! empty( $skipped_steps ) ) {
			update_option( 'mcw_onboarding_skipped', true, false );
			update_option( 'mcw_onboarding_exit_step', implode( ',', $skipped_steps ), false );
		}

		foreach ( $onboarding_data as $index => $data ) {
			if ( ! is_array( $data ) ) {
				continue;
			}

			if ( isset( $data['hasSkipped'] ) ) {
				continue;
			}

			foreach ( $data as $key => $value ) {
				if ( ! is_string( $key ) ) {
					continue;
				}

				$mapped_key_value = $this->map_onboarding_key_to_original_key( $key, $value );

				if ( $mapped_key_value && is_scalar( $mapped_key_value['value'] ) ) {
					$mapped_data[ $mapped_key_value['option'] ][ $mapped_key_value['key'] ] = sanitize_text_field( wp_unslash( (string) $mapped_key_value['value'] ) );
				}

				if ( 2 === $index && is_scalar( $value ) ) {
					$user_details_data[ sanitize_key( $key ) ] = sanitize_text_field( wp_unslash( (string) $value ) );
				}

				if ( 3 === $index && (bool) $value ) {
					$allowed_slugs = array_keys( $this->get_onboarding_defaults()[3] ?? [] );
					if ( in_array( $key, $allowed_slugs, true ) ) {
						$installable_plugin_slugs[] = $key;
					}
				}
			}
		}

		if ( ! empty( $user_details_data ) ) {
			$encoded_body = wp_json_encode( $user_details_data );
			wp_remote_post(
				MODERNCART_ONBOARDING_USER_SUB_WORKFLOW_URL,
				[
					'body'    => $encoded_body ? $encoded_body : '',
					'headers' => [
						'Content-Type' => 'application/json',
					],
				]
			);
		}

		Helper::install_wordpress_plugins( $installable_plugin_slugs );

		if ( ! empty( $mapped_data ) ) {
			foreach ( $mapped_data as $option => $setting_data ) {
				$encoded_data = wp_json_encode( $setting_data );
				if ( is_string( $encoded_data ) ) {
					$this->update_settings( $encoded_data, $option );
				}
			}
		}

		update_option( 'moderncart_is_onboarding_complete', 'yes' );

		wp_send_json_success();
	}

	/**
	 * Get onboarding default values.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_onboarding_defaults() {
		return [
			1 => [
				'cart_type'                      => 'slideout',
				'floating_cart_button_position'  => 'bottom-right',
				'enable_free_shipping_bar'       => true,
				'enable_product_recommendations' => false,
			],
			2 => [
				'user_detail_firstname'    => wp_get_current_user()->first_name,
				'user_detail_lastname'     => wp_get_current_user()->last_name,
				'user_detail_email'        => wp_get_current_user()->user_email,
				'optin_newsletter_updates' => true,
				'optin_usage_tracking'     => false,
			],
			3 => [
				'cartflows'                     => true,
				'woo-cart-abandonment-recovery' => true,
				'sureforms'                     => true,
				'surerank'                      => true,
			],
		];
	}

	/**
	 * Map onboarding key to original key.
	 *
	 * @param string $key Key.
	 * @param mixed  $value Value.
	 * @return array<string, mixed>|null
	 */
	private function map_onboarding_key_to_original_key( $key, $value ) {
		switch ( $key ) {
			case 'cart_type':
				return [
					'option' => MODERNCART_SETTINGS,
					'key'    => 'cart_type',
					'value'  => $value,
				];

			case 'floating_cart_button_position':
				return [
					'option' => MODERNCART_FLOATING_SETTINGS,
					'key'    => 'floating_cart_position',
					'value'  => $value,
				];

			case 'enable_free_shipping_bar':
				return [
					'option' => MODERNCART_MAIN_SETTINGS,
					'key'    => 'enable_free_shipping_bar',
					'value'  => (bool) $value,
				];

			case 'enable_product_recommendations':
				return [
					'option' => MODERNCART_SETTINGS,
					'key'    => 'recommendation_types',
					'value'  => (bool) $value ? 'upsells' : 'disabled',
				];

			case 'enable_moderncart':
				return [
					'option' => MODERNCART_MAIN_SETTINGS,
					'key'    => 'enable_moderncart',
					'value'  => $value,
				];

			case 'moderncart_appearance_primary_color':
				return [
					'option' => MODERNCART_APPEARANCE_SETTINGS,
					'key'    => 'primary_color',
					'value'  => $value,
				];

			case 'moderncart_appearance_heading_color':
				return [
					'option' => MODERNCART_APPEARANCE_SETTINGS,
					'key'    => 'heading_color',
					'value'  => $value,
				];

			case 'moderncart_appearance_body_color':
				return [
					'option' => MODERNCART_APPEARANCE_SETTINGS,
					'key'    => 'body_color',
					'value'  => $value,
				];

			case 'moderncart_floating_floating_cart_icon':
				return [
					'option' => MODERNCART_FLOATING_SETTINGS,
					'key'    => 'floating_cart_icon',
					'value'  => $value,
				];

			case 'moderncart_floating_custom_trigger_selectors':
				return [
					'option' => MODERNCART_FLOATING_SETTINGS,
					'key'    => 'custom_trigger_selectors',
					'value'  => $value,
				];

			case 'optin_usage_tracking':
				return [
					'option' => MODERNCART_SETTINGS,
					'key'    => 'enable_usage_tracking',
					'value'  => (bool) $value,
				];

			default:
				return null;
		}
	}
}
