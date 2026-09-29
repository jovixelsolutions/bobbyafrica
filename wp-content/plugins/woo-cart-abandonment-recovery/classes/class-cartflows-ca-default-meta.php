<?php
/**
 * Default Meta Configuration
 *
 * Provides centralized management of all plugin default values,
 * settings configuration, and meta data following modern WordPress
 * and CartFlows architectural patterns.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 * @since   1.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Ca_Default_Meta
 *
 * Centralized configuration class for all plugin defaults, settings,
 * and meta values. Implements singleton pattern with modern WP practices.
 *
 * @since 1.3.3
 */
class Cartflows_Ca_Default_Meta {
	/**
	 * Singleton instance.
	 *
	 * @var Cartflows_Ca_Default_Meta|null
	 * @since 1.3.3
	 */
	private static $instance = null;

	/**
	 * Cache for computed defaults.
	 *
	 * @var array
	 * @since 1.3.3
	 */
	private $defaults_cache = [];

	/**
	 * Constructor - private to enforce singleton.
	 *
	 * @since 1.3.3
	 */
	private function __construct() {
	}

	/**
	 * Get singleton instance.
	 *
	 * @return Cartflows_Ca_Default_Meta
	 * @since 1.3.3
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get email template meta defaults.
	 *
	 * Provides default meta values for email templates.
	 *
	 * @return array Email template meta defaults.
	 * @since 1.3.3
	 */
	public function get_email_template_meta_defaults() {
		if ( isset( $this->defaults_cache['email_template_meta'] ) ) {
			return $this->defaults_cache['email_template_meta'];
		}

		$defaults = [
			'override_global_coupon' => false,
			'discount_type'          => 'percent',
			'coupon_amount'          => 10,
			'coupon_expiry_date'     => '',
			'coupon_expiry_unit'     => 'hours',
			'use_woo_email_style'    => false,
			'auto_coupon'            => false,
			'free_shipping_coupon'   => false,
			'individual_use_only'    => false,
		];

		/**
		 * Filter email template meta defaults
		 *
		 * @param array $defaults Email template meta defaults.
		 * @since 1.3.3
		 */
		$defaults = apply_filters( 'wcf_ca_email_template_meta_defaults', $defaults );

		$this->defaults_cache['email_template_meta'] = $defaults;
		return $defaults;
	}

	/**
	 * Get default sample email templates.
	 *
	 * Provides the default email templates with proper escaping and localization.
	 *
	 * @return array Sample email templates.
	 * @since 1.3.3
	 */
	public function get_sample_email_templates() {
		if ( isset( $this->defaults_cache['sample_templates'] ) ) {
			return $this->defaults_cache['sample_templates'];
		}

		$templates = [
			[
				'template_name'  => __( 'Sample Email Template 1', 'woo-cart-abandonment-recovery' ),
				'subject'        => __( 'Purchase issue?', 'woo-cart-abandonment-recovery' ),
				'body'           => wp_kses_post(
					sprintf(
						/* translators: Email template with placeholders */
						__(
							'<p>Hi {{customer.firstname}}!</p><p>We\'re having trouble processing your recent purchase. Would you mind completing it?</p><p>Here\'s a link to continue where you left off:</p><p><a href=\'{{cart.checkout_url}}\' target=\'_blank\' rel=\'noopener\'> Continue Your Purchase Now </a></p><p>Kindly,<br />{{admin.firstname}}<br />{{admin.company}}</p><p>{{cart.unsubscribe}}</p>',
							'woo-cart-abandonment-recovery'
						)
					)
				),
				'frequency'      => 30,
				'frequency_unit' => 'MINUTE',
			],
			[
				'template_name'  => __( 'Sample Email Template 2', 'woo-cart-abandonment-recovery' ),
				'subject'        => __( 'Need help?', 'woo-cart-abandonment-recovery' ),
				'body'           => wp_kses_post(
					sprintf(
						/* translators: Email template with placeholders */
						__(
							'<p>Hi {{customer.firstname}}!</p><p>I\'m {{admin.firstname}}, and I help handle customer issues at {{admin.company}}.</p><p>I just noticed that you tried to make a purchase, but unfortunately, there was some trouble. Is there anything I can do to help?</p><p>You should be able to complete your checkout in less than a minute:<br /><a href=\'{{cart.checkout_url}}\' target=\'_blank\' rel=\'noopener\'> Click here to continue your purchase </a><p><p>Thanks!<br />{{admin.firstname}}<br />{{admin.company}}</p><p>{{cart.unsubscribe}}</p>',
							'woo-cart-abandonment-recovery'
						)
					)
				),
				'frequency'      => 1,
				'frequency_unit' => 'DAY',
			],
			[
				'template_name'  => __( 'Sample Email Template 3', 'woo-cart-abandonment-recovery' ),
				'subject'        => __( 'Exclusive discount for you. Let\'s get things started!', 'woo-cart-abandonment-recovery' ),
				'body'           => wp_kses_post(
					sprintf(
						/* translators: Email template with placeholders */
						__(
							'<p>Few days back you left {{cart.product.names}} in your cart.</p><p>To help make up your mind, we have added an exclusive 10%% discount coupon {{cart.coupon_code}} to your cart.</p><p><a href=\'{{cart.checkout_url}}\' target=\'_blank\' rel=\'noopener\'>Complete Your Purchase Now &gt;&gt;</a></p><p>Hurry! This is a onetime offer and will expire in 24 Hours.</p><p>In case you couldn\'t finish your order due to technical difficulties or because you need some help, just reply to this email we will be happy to help.</p><p>Kind Regards,<br />{{admin.firstname}}<br />{{admin.company}}</p><p>{{cart.unsubscribe}}</p>',
							'woo-cart-abandonment-recovery'
						)
					)
				),
				'frequency'      => 3,
				'frequency_unit' => 'DAY',
			],
		];

		/**
		 * Filter sample email templates.
		 *
		 * @param array $templates Sample email templates.
		 * @since 1.3.3
		 */
		$templates = apply_filters( 'wcf_ca_sample_email_templates', $templates );

		$this->defaults_cache['sample_templates'] = $templates;
		return $templates;
	}

	/**
	 * Get universal plugin options with defaults and sanitization.
	 *
	 * This is the central function that defines all plugin options.
	 * Use this throughout the plugin for consistent defaults and sanitization.
	 *
	 * @return array Array of option configurations.
	 * @since 1.3.3
	 */
	public function get_plugin_options() {
		if ( isset( $this->defaults_cache['plugin_options'] ) ) {
			return $this->defaults_cache['plugin_options'];
		}

		$current_user = wp_get_current_user();
		$admin_name   = $this->get_admin_display_name( $current_user );

		$options = [
			// Core Plugin Settings.
			'wcf_ca_status'                               => [
				'default'  => 'on',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_cron_run_time'                        => [
				'default'  => 20,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_ca_cart_lost_time'                       => [
				'default'  => WCF_DEFAULT_CART_LOST_TIME,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_ca_ignore_users'                         => [
				'default'  => [],
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_excludes_orders'                      => [
				'default'  => [ 'processing', 'completed' ],
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_from_name'                            => [
				'default'  => $admin_name,
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_from_email'                           => [
				'default'  => $current_user->user_email,
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_reply_email'                          => [
				'default'  => $current_user->user_email,
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_admin_email'                          => [
				'default'  => get_option( 'admin_email' ),
				'sanitize' => 'FILTER_SANITIZE_MULTILINE_STRING',
			],
			'wcf_ca_send_recovery_report_emails_to_admin' => [
				'default'  => 'on',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_zapier_tracking_status'               => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_zapier_cart_abandoned_webhook'        => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_URL',
			],
			'wcf_ca_coupon_code_status'                   => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_discount_type'                        => [
				'default'  => 'percent',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_coupon_amount'                        => [
				'default'  => 10,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_ca_coupon_expiry'                        => [
				'default'  => 0,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_ca_coupon_expiry_unit'                   => [
				'default'  => 'hours',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_auto_delete_coupons'                  => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_gdpr_status'                          => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_gdpr_message'                         => [
				'default'  => __( 'Your email & cart are saved so we can send email reminders about this order.', 'woo-cart-abandonment-recovery' ),
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_delete_plugin_data'                   => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcar_email_admin_on_recovery'                => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_ca_global_param'                         => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_MULTILINE_STRING',
			],
			'wcf_ca_cut_off_time'                         => [
				'default'  => 15,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_whatsapp_tracking_status'                => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_sms_tracking_status'                     => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcar_usage_optin'                            => [
				'default'  => 'off',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			// TODO: Remove this after new UI is enabled by default.
			'cartflows_ca_use_new_ui'                     => [
				'default'  => false,
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'car_legacy_ui_notice_dismissed'              => [
				'default'  => false,
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
		];

		/**
		 * Filter plugin options configuration.
		 *
		 * Allows pro version and other plugins to extend the plugin options.
		 *
		 * @param array $options Plugin options configuration.
		 * @since 1.3.3
		 */
		$options = apply_filters( 'wcf_ca_plugin_default_options', $options );

		$this->defaults_cache['plugin_options'] = $options;
		return $options;
	}

	/**
	 * Retrieves a specific default setting value.
	 *
	 * Public API method to retrieve individual default values.
	 *
	 * @return mixed $all_defaults The All default valyes of the settings optionss.
	 * @since 1.3.3
	 */
	public function get_default_settings() {
		$plugin_options = $this->get_plugin_options();
		$defaults       = [];

		foreach ( $plugin_options as $option_key => $config ) {
			$defaults[ $option_key ] = $config['default'];
		}

		return $defaults;
	}

	/**
	 * Sanitize setting value
	 *
	 * Single function responsible for all plugin option sanitization.
	 * This eliminates code duplication and ensures consistent sanitization.
	 *
	 * @param string $setting_key Setting key.
	 * @param mixed  $value       Value to sanitize.
	 * @return mixed Sanitized value
	 * @since 1.3.3
	 */
	public function sanitize_setting_value( $setting_key, $value ) {
		// Check plugin options first.
		$plugin_options  = $this->get_plugin_options();
		$sanitize_method = null;

		if ( isset( $plugin_options[ $setting_key ]['sanitize'] ) ) {
			$sanitize_method = $plugin_options[ $setting_key ]['sanitize'];
		} else {
			// Check email template fields.
			$email_fields = $this->get_email_template_fields();
			if ( isset( $email_fields[ $setting_key ]['sanitize'] ) ) {
				$sanitize_method = $email_fields[ $setting_key ]['sanitize'];
			}
		}

		// Default to string sanitization if not found.
		if ( ! $sanitize_method ) {
			$sanitize_method = 'FILTER_SANITIZE_STRING';
		}
		
		$meta_value = '';

		// Handle different sanitization methods.
		switch ( $sanitize_method ) {
			case 'FILTER_SANITIZE_URL':
				$meta_value = esc_url_raw( $value );
				break;

			case 'FILTER_SANITIZE_NUMBER_INT':
				$meta_value = absint( $value );
				break;

			case 'FILTER_SANITIZE_FULL_SPECIAL_CHARS':
				// Preserve the original encode/decode pattern for email body HTML, then strip
				// the three main stored-XSS vectors: <script> blocks, inline event-handler
				// attributes (on*=), and javascript: protocol in href/src.
				$sanitized  = filter_var( wp_unslash( $value ), FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				$meta_value = html_entity_decode( $sanitized, ENT_COMPAT, 'UTF-8' );
				$meta_value = preg_replace( '#<script\b[^>]*>[\s\S]*?<\/script>#i', '', $meta_value );
				$meta_value = preg_replace( '#\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)#i', '', $meta_value );
				$meta_value = preg_replace( '#(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1=$2$2', $meta_value );
				break;

			case 'FILTER_SANITIZE_STRING':
				if ( is_array( $value ) ) {
					$meta_value = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $value ) );
				} else {
					$meta_value = sanitize_text_field( wp_unslash( $value ) );
				}

				break;
			
			case 'FILTER_SANITIZE_MULTILINE_STRING':
				if ( is_array( $value ) ) {
					$meta_value = array_map( 'sanitize_textarea_field', array_map( 'wp_unslash', $value ) );
				} else {
					$meta_value = sanitize_textarea_field( wp_unslash( $value ) );
				}
				break;
				
			case 'FILTER_SANITIZE_ARRAY':
				if ( is_array( $value ) ) {
					$meta_value = array_map( 'sanitize_text_field', array_map( 'wp_unslash', $value ) );
				}
				break;

			default:
				if ( 'FILTER_DEFAULT' === $sanitize_method ) {
					$meta_value = sanitize_text_field( wp_unslash( $value ) );
				} else {
					$meta_value = apply_filters( 'wcar_sanitize_settings_values', $value, $setting_key, $sanitize_method );
				}
				break;
		}

		return $meta_value;
	}

	/**
	 * Get email template fields configuration.
	 *
	 * Centralized definition of all email template fields with defaults and sanitization.
	 * Use this function when creating or updating email templates.
	 *
	 * @return array Email template fields configuration.
	 * @since 1.3.3
	 */
	public function get_email_template_fields() {
		$fields = [
			'wcf_email_subject'           => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_email_body'              => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_FULL_SPECIAL_CHARS',
			],
			'wcf_template_name'           => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_email_frequency'         => [
				'default'  => 30,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_email_frequency_unit'    => [
				'default'  => 'MINUTE',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_activate_email_template' => [
				'default'  => 0,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_discount_type'           => [
				'default'  => 'percent',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_coupon_amount'           => [
				'default'  => 10,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
			'wcf_coupon_expiry_date'      => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_coupon_expiry_unit'      => [
				'default'  => 'hours',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_override_global_coupon'  => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_auto_coupon'             => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_free_shipping_coupon'    => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_individual_use_only'     => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'wcf_use_woo_email_style'     => [
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			],
			'id'                          => [
				'default'  => null,
				'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
			],
		];

		/**
		 * Filter email template fields configuration.
		 *
		 * Allows pro version and other plugins to extend the email template fields.
		 *
		 * @param array $fields Email template fields configuration.
		 * @since 1.3.3
		 */
		return apply_filters( 'wcf_ca_email_template_default_fields', $fields );
	}

	/**
	 * Sanitize email template data.
	 *
	 * Simple function to sanitize email template POST data using centralized field definitions.
	 * Note: This function assumes nonce verification has been done by the calling function.
	 *
	 * @return array Sanitized email template data.
	 * @since 1.3.3
	 */
	public function sanitize_email_template_data() {
		$email_fields   = $this->get_email_template_fields();
		$sanitized_data = [];

		foreach ( $email_fields as $field_key => $field_config ) {
			if ( isset( $_POST[ $field_key ] ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
				$sanitized_data[ $field_key ] = $this->sanitize_setting_value( $field_key, $_POST[ $field_key ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			} else {
				$sanitized_data[ $field_key ] = $field_config['default'];
			}
		}

		return $sanitized_data;
	}
	/**
	 * Get email template meta fields.
	 *
	 * Automatically extracts meta fields from email template configuration.
	 * Meta fields are those that start with 'wcf_' and are not main template fields.
	 *
	 * @return array List of meta field keys (without 'wcf_' prefix).
	 * @since 1.3.3
	 */
	public function get_email_template_meta_fields() {
		$email_fields = $this->get_email_template_fields();

		// Main template fields (not meta fields).
		$main_fields = [
			'wcf_email_subject',
			'wcf_email_body',
			'wcf_template_name',
			'wcf_email_frequency',
			'wcf_email_frequency_unit',
			'wcf_activate_email_template',
			'id',
		];

		$meta_fields = [];

		foreach ( $email_fields as $field_key => $config ) {
			// Skip main template fields.
			if ( in_array( $field_key, $main_fields, true ) ) {
				continue;
			}

			// Convert 'wcf_discount_type' to 'discount_type'.
			if ( strpos( $field_key, 'wcf_' ) === 0 ) {
				$meta_fields[] = str_replace( 'wcf_', '', $field_key );
			}
		}

		return $meta_fields;
	}
	/**
	 * Get admin display name for email from field
	 *
	 * Determines appropriate admin display name with fallbacks.
	 *
	 * @param WP_User $user WordPress user object.
	 * @return string Admin display name
	 * @since 1.3.3
	 */
	private function get_admin_display_name( $user ) {
		if ( ! empty( $user->user_firstname ) && ! empty( $user->user_lastname ) ) {
			return sanitize_text_field( $user->user_firstname . ' ' . $user->user_lastname );
		}
		if ( ! empty( $user->user_firstname ) ) {
			return sanitize_text_field( $user->user_firstname );
		}
		if ( ! empty( $user->display_name ) ) {
			return sanitize_text_field( $user->display_name );
		}

		return __( 'Admin', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * Checks if plugin option exists
	 *
	 * @param string $setting_key Setting key to check.
	 * @return bool
	 * @since 2.0.8
	 */
	public function plugin_option_exist( $setting_key ) {
		$plugin_options = $this->get_plugin_options();

		if ( isset( $plugin_options[ $setting_key ] ) ) {
			return true;
		}
		return false;
	}
}

// Initialize the class.
Cartflows_Ca_Default_Meta::get_instance();
