<?php
/**
 * Helper.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Inc;

use ModernCart\Inc\Traits\Get_Instance;

/**
 * Helper
 *
 * @since 0.0.1
 */
class Helper {
	use Get_Instance;

	/**
	 * Keep default values of all settings.
	 *
	 * @param bool $with_schema Whether or not to return defaults with schema.
	 * @since 0.0.1
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public function get_defaults( $with_schema = false ) {
		$defaults = [
			MODERNCART_MAIN_SETTINGS       => [
				'enable_moderncart'        => [
					'value' => 'all',
					'type'  => 'string',
				],
				'enable_powered_by'        => [
					'value' => true,
					'type'  => 'boolean',
				],
				'enable_ajax_add_to_cart'  => [
					'value' => true,
					'type'  => 'boolean',
				],
				'enable_free_shipping_bar' => [
					'value' => false,
					'type'  => 'boolean',
				],
				'enable_express_checkout'  => [
					'value' => false,
					'type'  => 'boolean',
				],
				'delete_data_on_uninstall' => [
					'value' => false,
					'type'  => 'boolean',
				],
			],
			MODERNCART_SETTINGS            => [
				'cart_style'                      => [
					'value' => 'slideout',
					'type'  => 'string',
				],
				'cart_theme_style'                => [
					'value' => 'style1',
					'type'  => 'string',
				],
				'product_image_size'              => [
					'value' => 'medium',
					'type'  => 'string',
				],
				'enable_coupon_field'             => [
					'value' => 'minimize',
					'type'  => 'string',
				],
				'cart_item_padding'               => [
					'value' => 20,
					'type'  => 'number',
				],
				'animation_speed'                 => [
					'value' => 300,
					'type'  => 'number',
				],
				'section_styling'                 => [
					'value' => 'accordian',
					'type'  => 'string',
				],
				'enable_usage_tracking'           => [
					'value' => false,
					'type'  => 'boolean',
				],
				'main_title'                      => [
					'value' => __( 'Review Your Cart', 'modern-cart' ),
					'type'  => 'string',
				],
				'recommendation_title'            => [
					'value' => __( 'Even better with these!', 'modern-cart' ),
					'type'  => 'string',
				],
				'empty_cart_recommendation_title' => [
					'value' => __( 'Let\'s find you something perfect', 'modern-cart' ),
					'type'  => 'string',
				],
				'coupon_title'                    => [
					'value' => __( 'Got a Discount Code?', 'modern-cart' ),
					'type'  => 'string',
				],
				'coupon_placeholder'              => [
					'value' => __( 'Enter discount code', 'modern-cart' ),
					'type'  => 'string',
				],
				'checkout_button_label'           => [
					'value' => __( 'Proceed to Checkout', 'modern-cart' ),
					'type'  => 'string',
				],
				'free_shipping_bar_text'          => [
					'value' => __( 'You\'re {amount} away from free shipping!', 'modern-cart' ),
					'type'  => 'string',
				],
				'free_shipping_success_text'      => [
					'value' => __( 'Awesome pick! You\'ve unlocked free shipping.', 'modern-cart' ),
					'type'  => 'string',
				],
				'on_sale_percentage_text'         => [
					'value' => __( 'You saved {percent}%', 'modern-cart' ),
					'type'  => 'string',
				],
			],
			MODERNCART_FLOATING_SETTINGS   => [
				'floating_cart_position' => [
					'value' => 'bottom-right',
					'type'  => 'string',
				],
			],
			MODERNCART_APPEARANCE_SETTINGS => [
				'primary_color'              => [
					'value' => '#0284C7',
					'type'  => 'hex',
				],
				'heading_color'              => [
					'value' => '#1F2937',
					'type'  => 'hex',
				],
				'body_color'                 => [
					'value' => '#374151',
					'type'  => 'hex',
				],
				'cart_header_text_alignment' => [
					'value' => 'center',
					'type'  => 'string',
				],
				'cart_header_font_size'      => [
					'value' => 22,
					'type'  => 'number',
				],
			],
		];

		/**
		 * Filter the default settings for Modern Cart.
		 *
		 * This filter allows you to modify the default settings array before it is used.
		 *
		 * @since 1.0.0
		 *
		 * @param array $defaults The default settings array.
		 */
		$defaults = apply_filters( 'moderncart_default_settings', $defaults );

		if ( $with_schema ) {
			return $defaults;
		}

		static $_defaults = [];

		if ( empty( $_defaults ) ) {
			// Set the $_defaults if not cached, this will make sure we don't impact performance.
			$_defaults = [];

			foreach ( $defaults as $key => $value ) {
				if ( ! is_array( $value ) ) {
					continue;
				}

				$_defaults[ $key ] = [];

				foreach ( $value as $_key => $_value ) {
					$_defaults[ $key ][ $_key ] = is_array( $_value ) ? $_value['value'] : $_value;
				}
			}
		}

		return $_defaults;
	}

	/**
	 * Get option value from database and retruns value merged with default values
	 *
	 * @param string $option option name to get value from.
	 * @return array<string, array<string, bool|int|string>>
	 * @since 0.0.1
	 */
	public function get_option( $option ) {
		$db_values = self::convert_to_array( get_option( $option, [] ) );
		$defaults  = $this->get_defaults();

		$default = $defaults[ $option ] ?? [];

		if ( ( MODERNCART_APPEARANCE_SETTINGS === $option || MODERNCART_FLOATING_SETTINGS === $option ) && 'astra' === get_template() ) {
			// If Astra theme is active and no color appearance settings are set, use Astra's default color variables.
			// This is to ensure that the plugin works well with Astra theme.
			$default = array_merge( $default, self::get_astra_color_vars() );
		}

		/**
		 * Filter the database values for Modern Cart settings before merging with defaults.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $db_values The values retrieved from the database for the given option.
		 * @param string $option    The option name being retrieved.
		 * @return array Filtered database values.
		 */
		$db_values = apply_filters( 'moderncart_settings_db_values', array_intersect_key( $db_values, $default ), $option );

		return wp_parse_args( $db_values, $default );
	}

	/**
	 * Check if cart is empty
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
	public static function is_cart_empty() {
		return null !== WC()->cart && WC()->cart->is_empty();
	}

	/**
	 * Check if Astra theme is active and has required functions.
	 *
	 * @since 0.0.1
	 *
	 * @return bool True if Astra theme is active and has required functions, false otherwise.
	 */
	public static function is_astra_active() {
		return defined( 'ASTRA_THEME_VERSION' );
	}

	/**
	 * Get Astra color variables
	 *
	 * @since 1.0.2
	 *
	 * @return array<string,string> Returns the Astra global color variables.
	 */
	public static function get_astra_color_vars() {
		if ( is_admin() ) {
			// Here if we are in the admin area, provide the color value rather than the variables.
			$color_preset = self::get_compatible_colors();

			return [
				'primary_color'             => $color_preset[0],
				'heading_color'             => $color_preset[1],
				'body_color'                => $color_preset[2],
				'highlight_color'           => $color_preset[3],
				'background_color'          => $color_preset[4],
				'button_font_color'         => $color_preset[5],
				'header_font_color'         => $color_preset[1],
				'header_background_color'   => $color_preset[4],
				'quantity_font_color'       => $color_preset[1],
				'quantity_background_color' => $color_preset[6],
				'icon_color'                => $color_preset[5],
				'count_text_color'          => $color_preset[1],
				'count_background_color'    => $color_preset[6],
				'icon_background_color'     => $color_preset[0],
			];
		}

		// Provide Astra's global color variables for the frontend.
		return [
			'primary_color'             => 'var(--ast-global-color-0)',
			'heading_color'             => 'var(--ast-global-color-1)',
			'body_color'                => 'var(--ast-global-color-2)',
			'highlight_color'           => 'var(--ast-global-color-3)',
			'background_color'          => 'var(--ast-global-color-4)',
			'button_font_color'         => 'var(--ast-global-color-5)',
			'header_font_color'         => 'var(--ast-global-color-1)',
			'header_background_color'   => 'var(--ast-global-color-4)',
			'quantity_font_color'       => 'var(--ast-global-color-1)',
			'quantity_background_color' => 'var(--ast-global-color-6)',
			'icon_color'                => 'var(--ast-global-color-5)',
			'count_text_color'          => 'var(--ast-global-color-1)',
			'count_background_color'    => 'var(--ast-global-color-6)',
			'icon_background_color'     => 'var(--ast-global-color-0)',
		];
	}

	/**
	 * Get theme compatible or default color preset.
	 *
	 * @return array<string> colors.
	 *
	 * @since 1.0.0
	 */
	public static function get_compatible_colors(): array {
		$color_preset = [ '#046bd2', '#045cb4', '#1e293b', '#334155', '#F0F5FA', '#FFFFFF', '#D1D5DB', '#111111' ];

		if ( defined( 'ASTRA_THEME_VERSION' ) && function_exists( 'astra_get_option' ) ) {
			// Astra theme compatibility.
			$theme_colors = astra_get_option( 'global-color-palette' );
			$color_preset = ! empty( $theme_colors['palette'] ) ? $theme_colors['palette'] : $color_preset;
		}

		return $color_preset;
	}

	/**
	 * Checks if current value is string or else returns default value
	 *
	 * @param mixed $data data which need to be checked if is string.
	 *
	 * @since 0.0.1
	 * @return string
	 */
	public static function convert_to_string( $data ) {
		if ( is_scalar( $data ) ) {
			return (string) $data;
		}
		if ( is_object( $data ) && method_exists( $data, '__toString' ) ) {
			return $data->__toString();
		}
		if ( is_null( $data ) ) {
			return '';
		}
			return '';
	}

	/**
	 * Checks if current value is number or else returns default value
	 *
	 * @param mixed $value data which need to be checked if is string.
	 * @param int   $base value can be set is $data is not a string, defaults to empty string.
	 *
	 * @since 0.0.1
	 * @return int
	 */
	public static function convert_to_int( $value, $base = 10 ) {
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}
		if ( is_string( $value ) ) {
			$trimmed_value = trim( $value );
			return intval( $trimmed_value, $base );
		}
			return 0;
	}

	/**
	 * Checks if current value is an array or else returns default value
	 *
	 * @param mixed $data Data which needs to be checked if it is an array.
	 *
	 * @since 0.0.1
	 * @return array<mixed>
	 */
	public static function convert_to_array( $data ) {
		if ( is_array( $data ) ) {
			return $data;
		}
		if ( is_null( $data ) ) {
			return [];
		}
			return (array) $data;
	}

	/**
	 * Checks if current request is a REST API request for nav menu widget render
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function is_nav_menu_widget_render_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			return strpos( $request_uri, '/wp-json/wp/v2/widget-types/' ) !== false;
		}
		return false;
	}

	/**
	 * Returns cart icons
	 *
	 * @since 1.0.0
	 *
	 * @return array<int, string>
	 */
	public static function get_cart_icons() {
		return [
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M6.55 13.0581L9.225 21.4481C9.425 22.0456 9.95 22.444 10.575 22.444H20.9C21.5 22.444 22.075 22.0705 22.275 21.5228L26.225 10.9917H28.5C29.05 10.9917 29.5 10.5436 29.5 9.99585C29.5 9.44813 29.05 9 28.5 9H25.525C25.1 9 24.725 9.27386 24.575 9.6722L20.5 20.4523H11L8.875 13.7303H20.65C21.2 13.7303 21.65 13.2822 21.65 12.7344C21.65 12.1867 21.2 11.7386 20.65 11.7386H7.5C7.175 11.7386 6.875 11.9129 6.7 12.1618C6.5 12.4108 6.45 12.7593 6.55 13.0581ZM20.4 23.7635C20.825 23.7635 21.25 23.9378 21.55 24.2365C21.85 24.5353 22.025 24.9585 22.025 25.3817C22.025 25.805 21.85 26.2282 21.55 26.527C21.25 26.8257 20.825 27 20.4 27C19.975 27 19.55 26.8257 19.25 26.527C18.95 26.2282 18.775 25.805 18.775 25.3817C18.775 24.9585 18.95 24.5353 19.25 24.2365C19.55 23.9378 19.975 23.7635 20.4 23.7635ZM11.425 23.7635C11.85 23.7635 12.275 23.9378 12.575 24.2365C12.875 24.5353 13.05 24.9585 13.05 25.3817C13.05 25.805 12.875 26.2282 12.575 26.527C12.275 26.8257 11.85 27 11.425 27C11 27 10.575 26.8257 10.275 26.527C9.975 26.2282 9.8 25.805 9.8 25.3817C9.8 24.9585 9.975 24.5353 10.275 24.2365C10.575 23.9378 11 23.7635 11.425 23.7635Z" fill="currentColor"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M12.6667 9L10 12.6V25.2C10 25.6774 10.1873 26.1352 10.5207 26.4728C10.8541 26.8104 11.3063 27 11.7778 27H24.2222C24.6937 27 25.1459 26.8104 25.4793 26.4728C25.8127 26.1352 26 25.6774 26 25.2V12.6L23.3333 9H12.6667Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M10 12.5996H26" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M21.5554 16.2002C21.5554 17.155 21.1808 18.0706 20.514 18.7458C19.8473 19.4209 18.9429 19.8002 17.9999 19.8002C17.0569 19.8002 16.1525 19.4209 15.4857 18.7458C14.8189 18.0706 14.4443 17.155 14.4443 16.2002" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M21.3759 16.875L20.251 26.9997" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M25.8737 16.8748L21.3738 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M6.75024 16.875H29.2497" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M8.43726 16.875L10.2372 25.1998C10.3424 25.7156 10.6252 26.1783 11.0363 26.5072C11.4474 26.8361 11.9608 27.0104 12.4872 26.9997H23.5119C24.0382 27.0104 24.5517 26.8361 24.9628 26.5072C25.3739 26.1783 25.6566 25.7156 25.7618 25.1998L27.6743 16.875" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M9.56274 21.9365H26.4373" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M10.1243 16.8748L14.6242 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		<path d="M14.626 16.875L15.7509 26.9997" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M18.3043 15.8571V13.2857H20.913V11.5714H18.3043V9H16.5652V11.5714H13.9565V13.2857H16.5652V15.8571H18.3043ZM21.7826 27C22.2609 27 22.6703 26.8321 23.0109 26.4964C23.3514 26.1607 23.5217 25.7571 23.5217 25.2857C23.5217 24.8143 23.3514 24.4107 23.0109 24.075C22.6703 23.7393 22.2609 23.5714 21.7826 23.5714C21.3043 23.5714 20.8949 23.7393 20.5543 24.075C20.2138 24.4107 20.0435 24.8143 20.0435 25.2857C20.0435 25.7571 20.2138 26.1607 20.5543 26.4964C20.8949 26.8321 21.3043 27 21.7826 27ZM13.087 27C13.5652 27 13.9746 26.8321 14.3152 26.4964C14.6558 26.1607 14.8261 25.7571 14.8261 25.2857C14.8261 24.8143 14.6558 24.4107 14.3152 24.075C13.9746 23.7393 13.5652 23.5714 13.087 23.5714C12.6087 23.5714 12.1993 23.7393 11.8587 24.075C11.5181 24.4107 11.3478 24.8143 11.3478 25.2857C11.3478 25.7571 11.5181 26.1607 11.8587 26.4964C12.1993 26.8321 12.6087 27 13.087 27ZM27 11.5714V9.85714H24.1522L20.4565 17.5714H14.3696L10.9783 11.5714H9L12.8261 18.3857C12.9855 18.6714 13.1993 18.8929 13.4674 19.05C13.7355 19.2071 14.029 19.2857 14.3478 19.2857H20.8261L21.7826 21H11.3478V22.7143H21.7826C22.4348 22.7143 22.9312 22.4357 23.2717 21.8786C23.6123 21.3214 23.6232 20.7571 23.3043 20.1857L22.1304 18.0857L25.2609 11.5714H27Z" fill="currentColor"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M11.9999 27C11.5285 27 11.1249 26.8321 10.7892 26.4964C10.4535 26.1607 10.2856 25.7571 10.2856 25.2857V15C10.2856 14.5286 10.4535 14.125 10.7892 13.7893C11.1249 13.4536 11.5285 13.2857 11.9999 13.2857H13.7142C13.7142 12.1 14.1321 11.0893 14.9678 10.2536C15.8035 9.41786 16.8142 9 17.9999 9C19.1856 9 20.1964 9.41786 21.0321 10.2536C21.8678 11.0893 22.2856 12.1 22.2856 13.2857H23.9999C24.4714 13.2857 24.8749 13.4536 25.2106 13.7893C25.5464 14.125 25.7142 14.5286 25.7142 15V25.2857C25.7142 25.7571 25.5464 26.1607 25.2106 26.4964C24.8749 26.8321 24.4714 27 23.9999 27H11.9999ZM11.9999 25.2857H23.9999V15H11.9999V25.2857ZM17.9999 20.1429C19.1856 20.1429 20.1964 19.725 21.0321 18.8893C21.8678 18.0536 22.2856 17.0429 22.2856 15.8571H20.5714C20.5714 16.5714 20.3214 17.1786 19.8214 17.6786C19.3214 18.1786 18.7142 18.4286 17.9999 18.4286C17.2856 18.4286 16.6785 18.1786 16.1785 17.6786C15.6785 17.1786 15.4285 16.5714 15.4285 15.8571H13.7142C13.7142 17.0429 14.1321 18.0536 14.9678 18.8893C15.8035 19.725 16.8142 20.1429 17.9999 20.1429ZM15.4285 13.2857H20.5714C20.5714 12.5714 20.3214 11.9643 19.8214 11.4643C19.3214 10.9643 18.7142 10.7143 17.9999 10.7143C17.2856 10.7143 16.6785 10.9643 16.1785 11.4643C15.6785 11.9643 15.4285 12.5714 15.4285 13.2857Z" fill="currentColor"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M27 9H25L24.6 11M23 19H13L9 11H24.6M23 19L24.6 11M23 19L25.2929 21.2929C25.9229 21.9229 25.4767 23 24.5858 23H13M13 23C14.1046 23 15 23.8954 15 25C15 26.1046 14.1046 27 13 27C11.8954 27 11 26.1046 11 25C11 23.8954 11.8954 23 13 23ZM21 25C21 26.1046 21.8954 27 23 27C24.1046 27 25 26.1046 25 25C25 23.8954 24.1046 23 23 23C21.8954 23 21 23.8954 21 25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M22.0555 13.5V12.75C22.0555 10.6789 20.2398 9 18 9C15.7601 9 13.9444 10.6789 13.9444 12.75V13.5H10.7V24.75C10.7 25.9931 11.7889 27 13.1333 27H22.8666C24.211 27 25.3 25.9931 25.3 24.75V13.5H22.0555ZM15.5666 12.75C15.5666 11.5074 16.6561 10.5 18 10.5C19.3438 10.5 20.4333 11.5074 20.4333 12.75V13.5H15.5666V12.75ZM23.6777 24.75C23.6777 25.1644 23.3148 25.5 22.8666 25.5H13.1333C12.6851 25.5 12.3222 25.1644 12.3222 24.75V15H23.6777V24.75Z" fill="currentColor"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M11.8125 27C11.3825 27 11.0002 26.8697 10.6658 26.6092C10.3313 26.3487 10.1004 26.0132 9.97297 25.6026L7.5362 16.8395C7.45656 16.5395 7.50833 16.2632 7.69148 16.0105C7.87464 15.7579 8.12548 15.6316 8.44402 15.6316H12.9831L17.1877 9.42632C17.2674 9.3 17.3789 9.19737 17.5222 9.11842C17.6655 9.03947 17.8168 9 17.9761 9C18.1354 9 18.2867 9.03947 18.43 9.11842C18.5734 9.19737 18.6848 9.3 18.7645 9.42632L22.9691 15.6316H27.556C27.8745 15.6316 28.1254 15.7579 28.3085 16.0105C28.4917 16.2632 28.5434 16.5395 28.4638 16.8395L26.027 25.6026C25.8996 26.0132 25.6687 26.3487 25.3342 26.6092C24.9998 26.8697 24.6175 27 24.1875 27H11.8125ZM11.7886 25.1053H24.2114L26.3137 17.5263H9.68629L11.7886 25.1053ZM18 23.2105C18.5256 23.2105 18.9755 23.025 19.3498 22.6539C19.7241 22.2829 19.9112 21.8368 19.9112 21.3158C19.9112 20.7947 19.7241 20.3487 19.3498 19.9776C18.9755 19.6066 18.5256 19.4211 18 19.4211C17.4744 19.4211 17.0245 19.6066 16.6502 19.9776C16.2759 20.3487 16.0888 20.7947 16.0888 21.3158C16.0888 21.8368 16.2759 22.2829 16.6502 22.6539C17.0245 23.025 17.4744 23.2105 18 23.2105ZM15.3004 15.6316H20.6757L17.9761 11.6526L15.3004 15.6316Z" fill="currentColor"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M17.4034 16.2L18.6884 14.94L17.2428 13.5H21.0749V11.7H17.2428L18.7114 10.26L17.4034 9L13.7319 12.6L17.4034 16.2ZM21.9928 27C22.4976 27 22.9298 26.8238 23.2893 26.4713C23.6488 26.1188 23.8285 25.695 23.8285 25.2C23.8285 24.705 23.6488 24.2813 23.2893 23.9288C22.9298 23.5763 22.4976 23.4 21.9928 23.4C21.4879 23.4 21.0558 23.5763 20.6963 23.9288C20.3368 24.2813 20.157 24.705 20.157 25.2C20.157 25.695 20.3368 26.1188 20.6963 26.4713C21.0558 26.8238 21.4879 27 21.9928 27ZM12.814 27C13.3188 27 13.751 26.8238 14.1105 26.4713C14.47 26.1188 14.6498 25.695 14.6498 25.2C14.6498 24.705 14.47 24.2813 14.1105 23.9288C13.751 23.5763 13.3188 23.4 12.814 23.4C12.3092 23.4 11.877 23.5763 11.5175 23.9288C11.158 24.2813 10.9783 24.705 10.9783 25.2C10.9783 25.695 11.158 26.1188 11.5175 26.4713C11.877 26.8238 12.3092 27 12.814 27ZM27.5 10.8V9H24.494L20.593 17.1H14.1679L10.5882 10.8H8.5L12.5386 17.955C12.7069 18.255 12.9326 18.4875 13.2156 18.6525C13.4986 18.8175 13.8084 18.9 14.1449 18.9H20.9831L21.9928 20.7H10.9783V22.5H21.9928C22.6812 22.5 23.2051 22.2075 23.5646 21.6225C23.9241 21.0375 23.9356 20.445 23.599 19.845L22.3599 17.64L25.6643 10.8H27.5Z" fill="currentColor"/>
		</svg>
		',
			'<svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M27.6958 9H26.3101C25.8004 9 25.3544 9.34265 25.2231 9.83513L24.8399 11.2721M22.4458 20.25C24.1027 20.25 25.4458 21.5931 25.4458 23.25H9.6958M22.4458 20.25H11.2275C10.1064 17.9494 9.1281 15.5664 8.30407 13.1125C13.0658 11.8965 18.0553 11.25 23.1958 11.25C23.7456 11.25 24.2937 11.2574 24.8399 11.2721M22.4458 20.25L24.8399 11.2721M23.9458 26.25C23.9458 26.6642 24.2816 27 24.6958 27C25.11 27 25.4458 26.6642 25.4458 26.25C25.4458 25.8358 25.11 25.5 24.6958 25.5C24.2816 25.5 23.9458 25.8358 23.9458 26.25ZM11.1958 26.25C11.1958 26.6642 11.5316 27 11.9458 27C12.36 27 12.6958 26.6642 12.6958 26.25C12.6958 25.8358 12.36 25.5 11.9458 25.5C11.5316 25.5 11.1958 25.8358 11.1958 26.25Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		',
		];
	}

	/**
	 * Returns modern cart knowledge base data
	 *
	 * @since 0.0.1
	 *
	 * @return array<string>
	 */
	public static function get_knowledge_base(): array {
		$transient_key = 'moderncart_knowledge_base_data';
		$cached_data   = get_transient( $transient_key );

		if ( false !== $cached_data && is_array( $cached_data ) ) {
			/**
			 * Cached knowledge base data from transient
			 *
			 * @var array<string> Array of knowledge base articles
			 */
			return $cached_data;
		}

		$domain_url = defined( 'CARTFLOWS_DOMAIN_URL' ) ? CARTFLOWS_DOMAIN_URL : 'https://cartflows.com';
		$api_route  = defined( 'CARTFLOWS_API_ROUTE' ) ? CARTFLOWS_API_ROUTE : '/wp-json/powerful-docs/v1';
		$endpoint   = defined( 'CARTFLOWS_DOCS_ENDPOINT' ) ? CARTFLOWS_DOCS_ENDPOINT : '/get-docs';

		$url      = $domain_url . $api_route . $endpoint;
		$response = wp_remote_get( $url ); // phpcs:ignore -- This is a valid use case cannot use VIP rules here.

		if ( is_wp_error( $response ) ) {
			return []; // Return empty array on failure.
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! isset( $data['docs'] ) || ! is_array( $data['docs'] ) ) {
			return []; // Return empty array if docs are not available.
		}

		$target_category = 'modern-cart-for-woocommerce';

		$filtered_docs = array_filter(
			$data['docs'],
			static function ( $item ) use ( $target_category ) {
				return in_array( $target_category, $item['category'] );
			}
		);

		/**
		 * Cached knowledge base data from transient
		 *
		 * @var array<string> Array of knowledge base articles
		 */
		$result = array_reverse( array_values( $filtered_docs ) ); // Reindex and reverse.

		// Cache the result for 12 hours.
		set_transient( $transient_key, $result, 12 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Returns allowed tags kses.
	 *
	 * @since 1.0.0
	 * @param string $element The HTML element for which to get allowed tags.
	 *
	 * @return array<string, array<string, array<string, bool|string>|bool>>
	 */
	public static function get_allowed_tags_kses( $element ) {
		$allowed_tags = [
			'common'   => [
				'span' => [
					'class'                  => [],
					'data-moderncart-toggle' => [],
					'data-moderncart-target' => [],
					'role'                   => [],
					'tabindex'               => [],
				],
				'p'    => [
					'class' => [],
				],
				'bdi'  => [
					'class' => [],
				],
				'del'  => [
					'aria-hidden' => [],
				],
				'div'  => [
					'class' => [],
				],
				'a'    => [
					'href'  => [],
					'title' => [],
					'class' => [],
				],
			],
			'svg'      => [
				'svg'  => [
					'xmlns'           => true,
					'viewBox'         => true,
					'width'           => true,
					'height'          => true,
					'fill'            => true,
					'stroke'          => true,
					'stroke-width'    => true,
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
				],
				'path' => [
					'd'               => true,
					'fill'            => true,
					'stroke'          => true,
					'stroke-width'    => true,
					'stroke-linecap'  => true,
					'stroke-linejoin' => true,
				],
			],
			'img'      => [
				'img' => [
					'title'       => [],
					'src'         => [],
					'data-src'    => [],
					'data-srcset' => [],
					'data-sizes'  => [],
					'decoding'    => [],
					'class'       => [],
					'alt'         => [],
					'width'       => [],
					'height'      => [],
					'loading'     => [],
				],
			],
			'quantity' => [
				'span'   => [
					'class'       => [],
					'aria-hidden' => [],
					'id'          => [],
				],
				'svg'    => [
					'aria-hidden' => [],
					'focusable'   => [],
					'role'        => [],
					'class'       => [],
					'viewBox'     => [],
				],
				'path'   => [
					'fill' => [],
					'd'    => [],
				],
				'input'  => [
					'class'         => [],
					'type'          => [],
					'aria-label'    => [],
					'step'          => [],
					'min'           => [],
					'max'           => [],
					'value'         => [],
					'placeholder'   => [],
					'inputmode'     => [],
					'data-key'      => [],
					'id'            => [],
					'data-action'   => [],
					'pattern'       => [],
					'tabindex'      => [],
					'aria-valuemin' => [],
					'aria-valuemax' => [],
					'aria-valuenow' => [],
					'aria-live'     => [],
					'aria-atomic'   => [],
				],
				'div'    => [
					'class'           => [],
					'role'            => [],
					'aria-labelledby' => [],
				],
				'button' => [
					'class'       => [],
					'data-key'    => [],
					'data-action' => [],
					'aria-label'  => [],
					'tabindex'    => [],

				],
			],
		];

		if ( ! isset( $allowed_tags[ $element ] ) ) {
			// Return empty array if element is not found.
			return [];
		}

		// Return allowed tags for the element.
		return $allowed_tags[ $element ];
	}

	/**
	 * Sets no-cache headers to prevent caching of cart pages.
	 *
	 * This method sets HTTP headers to prevent browsers and proxies from caching cart-related pages.
	 * It can be disabled using the 'modern_cart_disable_nocache_headers' filter.
	 *
	 * @since 1.0.2
	 * @return void
	 */
	public static function set_nocache_headers(): void {
		if ( true === apply_filters( 'modern_cart_disable_nocache_headers', false ) ) {
			return;
		}

		nocache_headers();
		header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}

	/**
	 * Check if site is in maintenance mode.
	 *
	 * Checks various maintenance mode implementations:
	 * - Elementor maintenance mode
	 * - WooCommerce coming soon mode
	 * - WordPress core maintenance mode
	 *
	 * @since 1.0.3
	 * @return bool True if site is in maintenance mode, false otherwise.
	 */
	public static function is_maintenance_mode() {
		// Skip maintenance mode checks for admin users.
		if ( current_user_can( 'manage_options' ) ) {
			// Return false for admins.
			return false;
		}

		// Check if WooCommerce Coming Soon feature is available.
		if ( class_exists( 'Automattic\WooCommerce\Internal\ComingSoon\ComingSoonHelper' ) ) {
			// Check if the private link option is enabled.
			if ( get_option( 'woocommerce_private_link' ) === 'yes' ) {
				// Allow access if valid share key cookie is present.
				// phpcs:ignore WordPressVIPMinimum.Variables.RestrictedVariables.cache_constraints___COOKIE -- Required for WooCommerce Coming Soon private link functionality
				if ( isset( $_COOKIE['woo-share'] ) && get_option( 'woocommerce_share_key' ) === sanitize_text_field( wp_unslash( $_COOKIE['woo-share'] ) ) ) {
					return false;
				}
			}

			// Create instance of WooCommerce Coming Soon helper class.
			$wc_coming_soon_helper = new \Automattic\WooCommerce\Internal\ComingSoon\ComingSoonHelper();

			// Check if entire site is in coming soon mode.
			if ( $wc_coming_soon_helper->is_site_coming_soon() ) {
				return true;
			}

			// Check if just the store is in coming soon mode.
			if ( $wc_coming_soon_helper->is_store_coming_soon() ) {
				return true;
			}
		}

		// Check if Elementor maintenance mode is active.
		if ( defined( 'ELEMENTOR_VERSION' ) && class_exists( 'Elementor\Maintenance_Mode' ) && 'maintenance' === \Elementor\Maintenance_Mode::get( 'mode' ) ) {
			return true;
		}

		// Check WordPress core maintenance mode.
		if ( wp_is_maintenance_mode() ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the installation and activation status of Modern Cart Pro plugin.
	 *
	 * @since 1.0.3
	 * @return string Returns 'not-installed', 'active' or 'inactive' based on plugin status.
	 */
	public static function get_pro_status() {
		// Ensure the get_file_data function is available.
		if ( ! function_exists( 'get_file_data' ) ) {
			require_once ABSPATH . 'wp-includes/functions.php';
		}

		// Build path to pro plugin main file.
		$modern_cart_woo_file = untrailingslashit( WP_PLUGIN_DIR ) . '/modern-cart-woo/modern-cart-woo.php';

		if ( ! file_exists( $modern_cart_woo_file ) ) {
			// Pro plugin is not installed on this site.
			return 'not-installed';
		}

		if ( defined( 'MODERNCART_PRO_FILE' ) ) {
			// Pro plugin is installed and active.
			return 'active';
		}

		// Pro plugin is installed but not active.
		return 'inactive';
	}

	/**
	 * Gets the cart item count.
	 *
	 * Retrieves the number of items in the cart and allows filtering through 'moderncart_filter_cart_count'.
	 *
	 * @since 1.0.3
	 * @return int Number of items in cart
	 */
	public static function get_cart_count() {
		if ( self::is_cart_empty() ) {
			// Return 0 if cart is empty.
			return 0;
		}

		/**
		 * Filters the cart item count.
		 *
		 * Allows modifying the number of items shown in the cart count before display.
		 *
		 * @since 1.0.3
		 * @param int $count Number of items in cart
		 * @return int Modified cart item count
		 */
		return apply_filters( 'moderncart_filter_cart_count', WC()->cart->get_cart_contents_count() );
	}

	/**
	 * Check whether the current admin screen is the Modern Cart onboarding page.
	 *
	 * This validates:
	 * - The admin page slug
	 * - Presence of the onboarding flag
	 * - A valid nonce for security
	 *
	 * @since 1.0.5
	 *
	 * @return bool True if the current admin screen is the onboarding page, false otherwise.
	 */
	public static function is_admin_onboarding_screen() {
		// Bail early if required query parameters are missing.
		if ( empty( $_GET['page'] ) || empty( $_GET['onboarding'] ) || empty( $_GET['nonce'] ) ) {
			return false;
		}

		// Verify we are on the Modern Cart settings page.
		if ( 'moderncart_settings' !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return false;
		}

		// Verify the nonce to ensure the request is legitimate.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'moderncart_onboarding_nonce' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Install WordPress plugins available in WordPress repositories.
	 *
	 * @since 1.0.5
	 * @param array<string> $installable_plugin_slugs Array of WordPress plugins with value being plugin slugs.
	 * @return bool True on success.
	 */
	public static function install_wordpress_plugins( $installable_plugin_slugs ) {
		if ( empty( $installable_plugin_slugs ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		$installed_plugins = get_plugins();

		foreach ( $installable_plugin_slugs as $plugin_slug ) {
			// Check if plugin is already installed.
			$installed = false;
			foreach ( $installed_plugins as $installed_plugin_path => $data ) {
				if ( strpos( $installed_plugin_path, $plugin_slug . '/' ) === 0 ) {
					$installed = true;
					break;
				}
			}

			if ( ! $installed ) {
				// Get plugin info from WordPress.org.
				$api = plugins_api(
					'plugin_information',
					[
						'slug'   => (string) $plugin_slug,
						'fields' => [
							'short_description' => false,
							'sections'          => false,
							'requires'          => false,
							'rating'            => false,
							'ratings'           => false,
							'downloaded'        => false,
							'last_updated'      => false,
							'added'             => false,
							'tags'              => false,
							'compatibility'     => false,
							'homepage'          => false,
							'donate_link'       => false,
						],
					]
				);

				if ( ! is_wp_error( $api ) && is_object( $api ) && isset( $api->download_link ) ) {
					// Install plugin.
					$upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
					$install  = $upgrader->install( $api->download_link );

					if ( ! is_wp_error( $install ) ) {
						// Activate plugin.
						$plugin_path = $upgrader->plugin_info();
						if ( $plugin_path ) {
							activate_plugin( $plugin_path );
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * Generate a documentation link with description.
	 *
	 * Creates an HTML string containing a description followed by a "Learn More" link.
	 *
	 * @since 1.0.6
	 * @param string $description The text description to display before the link.
	 * @param string $doc_link The URL of the documentation page.
	 * @return string HTML string containing description and formatted link.
	 */
	public static function setting_doc_link( $description, $doc_link ) {
		// Build array of HTML parts.
		$parts = [
			$description, // Description text.
			'<a href="' . esc_url( $doc_link ) . '" class="text-wpcolor hover:text-wphovercolor no-underline" target="_blank">', // Link opening tag with styles.
			__( 'Learn More', 'modern-cart' ), // Translatable "Learn More" text.
			'</a>', // Link closing tag.
		];
		// Join parts with spaces and return.
		return implode( ' ', $parts );
	}

}
