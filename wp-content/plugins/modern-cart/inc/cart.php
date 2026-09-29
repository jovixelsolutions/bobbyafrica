<?php
/**
 * Cart.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Inc;

use ModernCart\Admin_Core\Inc\Settings_Fields;
use ModernCart\Inc\Traits\Get_Instance;
use WC_Shipping_Zones;

/**
 * Admin menu
 *
 * @since 0.0.1
 */
class Cart {
	use Get_Instance;

	/**
	 * Set a notification for rendering in HTML.
	 *
	 * @since 0.0.1
	 *
	 * @param string $option Option name.
	 * @param string $section Option section.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public function get_option( $option, $section, $default = '' ) {
		$helper  = Helper::get_instance();
		$options = $helper->get_option( $section );

		if ( is_array( $options ) && isset( $options[ $option ] ) ) {
			/**
			 * Option data.
			 *
			 * @var array<string,bool|int|string>|null
			 */
			$value = $options[ $option ];
			return null === $value ? $default : $value;
		}

		if ( empty( $default ) && isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}

	/**
	 * Get the current cart theme style.
	 *
	 * Retrieves the cart theme style option from settings, validates it against
	 * the available options, and returns a valid style. Falls back to 'style1'
	 * if the selected style is not available.
	 *
	 * @since 1.0.0
	 *
	 * @return string The cart theme style ID (e.g., 'style1').
	 */
	public function get_cart_theme_style() {
		static $cart_theme_style = ''; // Cache the result for performance.

		if ( empty( $cart_theme_style ) ) {
			// Get all settings fields, including cart theme style options.
			$fields = Settings_Fields::get_fields();

			// Get the selected cart theme style from options, defaulting to 'style1'.
			$cart_theme_style = $this->get_option( 'cart_theme_style', MODERNCART_SETTINGS, 'style1' );

			// Extract available style IDs from the settings fields.
			$options = ! empty( $fields['moderncart_setting']['moderncart_cart_cart_theme_style']['options'] ) && is_array( $fields['moderncart_setting']['moderncart_cart_cart_theme_style']['options'] )
				? array_column( $fields['moderncart_setting']['moderncart_cart_cart_theme_style']['options'], 'id' )
				: [];

			// If the selected style is not in the available options, fallback to 'style1'.
			if ( ! in_array( $cart_theme_style, $options, true ) ) {
				$cart_theme_style = 'style1';
			}
		}

		return $cart_theme_style;
	}

	/**
	 * Set a notification for rendering in HTML.
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
	public function is_global_enabled() {
		if ( Helper::is_nav_menu_widget_render_request() ) {
			return false;
		}

		$enabled = $this->get_option( 'enable_moderncart', MODERNCART_MAIN_SETTINGS, 'all' );

		// Allow external code to override the global enabled state via filter.
		$override = apply_filters( 'moderncart_override_is_global_enabled', null, $enabled );

		if ( is_bool( $override ) ) {
			// If the filter returns a boolean, use it as the return value.
			return $override;
		}

		if ( 'disabled' === $enabled ) {
			return false;
		}

		if ( 'all' === $enabled && ! is_checkout() ) {
			return true;
		}

		if ( is_shop() || is_product() || is_cart() ) {
			return true;
		}

		return false;
	}

	/**
	 * Shipping line item
	 *
	 * @since 0.0.1
	 *
	 * @param bool $wrapper If true, wrap the value in a div.
	 *
	 * @return string
	 */
	public function get_shipping_html( $wrapper = true ) {
		if ( ! apply_filters( 'moderncart_enable_shipping', $this->get_option( 'enable_shipping', MODERNCART_SETTINGS, true ) ) ) {
			return '';
		}

		$html  = '';
		$value = '';
		$label = esc_html__( 'Shipping', 'modern-cart' );
		WC()->cart->calculate_shipping();
		$packages = WC()->shipping()->get_packages();

		if ( ! empty( $packages ) ) {
			$package           = $packages[0];
			$available_methods = $package['rates'];

			if ( $available_methods ) {
				$value = WC()->cart->get_cart_shipping_total();
			}

			if ( '' === $value ) {
				return '';
			}

			if ( ! $wrapper ) {
				return $value;
			}

			$html = $this->get_single_item(
				$label,
				apply_filters( 'moderncart_cart_totals_shipping_html', $value ),
				'shipping'
			);
		}

		return $html;
	}

	/**
	 * Get the cart vat without shipping.
	 *
	 * @since 0.0.1
	 *
	 * @param bool $wrapper If true, wrap the value in a div.
	 *
	 * @return string
	 */
	public function get_tax_html( $wrapper = true ) {
		if ( ! apply_filters( 'moderncart_enable_tax', true ) ) {
			return '';
		}

		$label = '';
		$value = '';

		if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
			foreach ( WC()->cart->get_tax_totals() as $tax ) {
				$label .= $tax->label;
				$value .= wp_kses_post( wc_price( $tax->amount ) );
			}
		} else {
			$label .= esc_html( WC()->countries->tax_or_vat() );
			$value .= wc_price( WC()->cart->get_taxes_total( false, false ) );
		}

		if ( '' === $value ) {
			return '';
		}

		if ( ! $wrapper ) {
			return $value;
		}

		return $this->get_single_item(
			$label,
			apply_filters( 'moderncart_cart_totals_vat_total_html', $value ),
			'tax'
		);
	}

	/**
	 * Get the cart subtotal.
	 *
	 * @since 0.0.1
	 *
	 * @param bool $wrapper If true, wrap the value in a div.
	 *
	 * @return string
	 */
	public function get_subtotal_html( $wrapper = true ) {
		if ( ! apply_filters( 'moderncart_enable_subtotal', true ) || empty( WC()->cart->get_cart() ) ) {
			return '';
		}

		$label = esc_html__( 'Subtotal', 'modern-cart' );
		$value = WC()->cart->get_cart_subtotal();

		if ( ! $wrapper ) {
			return $value;
		}

		return $this->get_single_item(
			$label,
			apply_filters( 'moderncart_cart_totals_subtotal_html', $value ),
			'subtotal'
		);
	}

	/**
	 * Get the cart discount.
	 *
	 * @since 0.0.1
	 *
	 * @param bool $wrapper If true, wrap the value in a div.
	 *
	 * @return string
	 */
	public function get_discount_html( $wrapper = true ) {
		if ( ! apply_filters( 'moderncart_enable_discount', true ) ) {
			return '';
		}

		$label          = esc_html__( 'Discount', 'modern-cart' );
		$discount_total = WC()->cart->get_cart_discount_total();
		$value          = wc_price( $discount_total );
		$html           = '';

		if ( $discount_total > 0 ) {
			if ( ! $wrapper ) {
				return $value;
			}
			$html = $this->get_single_item(
				$label,
				apply_filters( 'moderncart_cart_totals_discount_total_html', $value ),
				'discount'
			);
		}

		return $html;
	}

	/**
	 * Get the cart total without shipping.
	 *
	 * @since 0.0.1
	 *
	 * @param bool $wrapper If true, wrap the value in a div.
	 *
	 * @return string
	 */
	public function get_total_html( $wrapper = true ) {
		if ( ! apply_filters( 'moderncart_enable_total', true ) ) {
			return '';
		}

		$cart  = WC()->cart;
		$label = esc_html__( 'Total', 'modern-cart' );

		$cart_total = (float) $cart->get_total( 'number' );

		$shipping       = $this->get_shipping_totals();
		$shipping_total = (float) ( $shipping->total ?? 0 );

		// Total before discount (used only for strike-through display).
		$total_without_discount =
			$cart->get_subtotal() +
			$cart->get_fee_total() +
			$cart->get_total_tax() +
			$shipping_total;

		$value = '<strong>' . wc_price( $cart_total ) . '</strong> ';

		/**
		 * TAX DISPLAY (prices include tax).
		 */
		if ( wc_tax_enabled() && $cart->display_prices_including_tax() ) {
			$tax_string_array = [];
			$cart_tax_totals  = $cart->get_tax_totals();

			// Itemized tax display.
			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( $cart_tax_totals as $tax ) {
					// IMPORTANT: do NOT modify WooCommerce tax totals.
					$tax_string_array[] = sprintf(
						'%s %s',
						wc_price( $tax->amount ),
						esc_html( $tax->label )
					);
				}
			} elseif ( ! empty( $cart_tax_totals ) ) {
				$tax_string_array[] = sprintf(
					'%s %s',
					wc_price( $cart->get_taxes_total( true, false ) ),
					esc_html( WC()->countries->tax_or_vat() )
				);
			}

			if ( ! empty( $tax_string_array ) ) {
				$taxable_address = WC()->customer->get_taxable_address();

				$estimated_text = WC()->customer->is_customer_outside_base()
					&& ! WC()->customer->has_calculated_shipping()
					? sprintf(
						/* translators: %s: country name for tax estimation. */
						' ' . esc_html__( 'estimated for %s', 'modern-cart' ),
						esc_html(
							WC()->countries->estimated_for_prefix( $taxable_address[0] )
							. WC()->countries->get_countries()[ $taxable_address[0] ]
						)
					)
					: '';

				$value .= '<small class="includes_tax">' .
					sprintf(
						/* translators: %s: tax amount and estimated text. */
						esc_html__( '(includes %s)', 'modern-cart' ),
						implode( ', ', $tax_string_array ) . $estimated_text
					) .
				'</small>';
			}
		}

		/**
		 * Discount display.
		 */
		if ( $cart->has_discount() ) {
			$value .= '<del class="moderncart-cart-discount">' .
				wc_price( $total_without_discount ) .
			'</del>';
		}

		if ( ! $wrapper ) {
			return $value;
		}

		return $this->get_single_item(
			$label,
			apply_filters( 'moderncart_cart_totals_order_total_html', $value ),
			'total'
		);
	}

	/**
	 * Creates a single line item.
	 *
	 * @since 0.0.1
	 *
	 * @param string $label The label.
	 * @param string $value The value.
	 * @param string $type Iteam type.
	 *
	 * @return string
	 */
	public function get_single_item( $label, $value, $type = 'item' ) {
		$html    = '';
		$classes = apply_filters( 'moderncart_single_line_item_classes', [ 'moderncart-cart-line-items-item', 'moderncart-cart-line-items__' . $type ] );

		// Define ARIA labels based on type.
		$aria_labels = [
			'subtotal' => __( 'Cart Subtotal', 'modern-cart' ),
			'shipping' => __( 'Shipping Cost', 'modern-cart' ),
			'tax'      => __( 'Tax Amount', 'modern-cart' ),
			'discount' => __( 'Discount Amount', 'modern-cart' ),
			'total'    => __( 'Cart Total', 'modern-cart' ),
		];

		$aria_label = $aria_labels[ $type ] ?? $label;

		$html .= '<div class="' . esc_attr( implode( ' ', array_filter( $classes ) ) ) . '" role="row" aria-label="' . esc_attr( $aria_label ) . '" aria-live="polite" aria-atomic="true" >';

		// Add label with screen reader text.
		$html .= '<span class="moderncart-cart-line-items-label moderncart-cart-line-items__' . esc_attr( $type ) . '-label" role="cell">';
		$html .= '<span class="screen-reader-text">' . esc_html( $aria_label ) . ': </span>';
		$html .= esc_html( $label );
		$html .= '</span>';
		/* translators: %1$s: field label , %2$s: field value */
		$html .= '<span class="moderncart-cart-line-items-value moderncart-cart-line-items__' . esc_attr( $type ) . '-value" role="cell" aria-label="' . esc_attr( sprintf( __( '%1$s: %2$s', 'modern-cart' ), $aria_label, wp_strip_all_tags( $value ) ) ) . '">';
		$html .= wp_kses_post( $value );
		$html .= '</span>';

		$html .= '</div>';
		return $html;
	}

	/**
	 * Check package to destination if is a returning customer.
	 *
	 * @since 0.0.1
	 *
	 * @param array $package Shipping package.
	 * @phpstan-param array{
	 *     destination?: array{
	 *         country?: string,
	 *         state?: string,
	 *         postcode?: string,
	 *         city?: string
	 *     }
	 * } $package
	 *
	 * @return bool
	 */
	public function is_destination_exists( $package = [] ) {
		$country  = $package['destination']['country'] ?? null;
		$state    = $package['destination']['state'] ?? null;
		$postcode = $package['destination']['postcode'] ?? null;
		$city     = $package['destination']['city'] ?? null;
		$exists   = true;

		if ( 'AF' === $country && ! $city ) {
			$country = null;
		}
		if ( ! $country && ! $state && ! $postcode ) {
			$exists = false;
		}

		return $exists;
	}

	/**
	 * Get minimum amount for free shipping.
	 *
	 * @since 0.0.1
	 *
	 * @return int
	 */
	public function get_free_shipping_amount() {
		if ( Helper::is_cart_empty() ) {
			// Check if cart is empty or not before processing.
			return 0;
		}

		$amount         = null;
		$initial_zone   = 1;
		$amount         = null;
		$cart           = WC()->cart;
		$packages       = $cart->get_shipping_packages();
		$package        = reset( $packages );
		$zone           = wc_get_shipping_zone( $package );
		$known_customer = $this->is_destination_exists( $package );

		if ( ! $known_customer ) {
			$init_zone = WC_Shipping_Zones::get_zone_by( 'zone_id', $initial_zone );
			$zone      = $init_zone instanceof \WC_Shipping_Zone ? $init_zone : $zone;
		}

		foreach ( $zone->get_shipping_methods( true ) as $method ) {
			if ( 'free_shipping' !== $method->id ) {
				continue;
			}

			$instance = $method->instance_settings ?? null;
			$amount   = isset( $instance['min_amount'] ) && ! empty( $instance['requires'] ) && 'coupon' !== $instance['requires'] ? $instance['min_amount'] : null;
		}

		return apply_filters( 'moderncart_free_shipping_min_amount', $amount );
	}

	/**
	 * Renders the powered by partial.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function render_poweredby(): void {
		if ( ! $this->get_option( 'enable_powered_by', MODERNCART_MAIN_SETTINGS, false ) ) {
			return;
		}

		$data = [
			'classes' => apply_filters( 'moderncart_powered_by_classes', [ 'moderncart-powered-by' ] ),
			'url'     => esc_url( 'https://cartflows.com/' ),
		];

		moderncart_get_template_part( 'cart/powered-by', '', $data );
	}

	/**
	 * Renders the free shipping bar.
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function render_free_shipping_bar(): void {
		if ( ! $this->get_option( 'enable_free_shipping_bar', MODERNCART_MAIN_SETTINGS, false ) ) {
			return;
		}

		$amount_free_shipping = $this->get_free_shipping_amount();

		if ( ! $amount_free_shipping ) {
			return;
		}

		$cart_total          = (float) WC()->cart->get_displayed_subtotal();
		$discount            = WC()->cart->get_discount_total();
		$discount_tax        = WC()->cart->get_discount_tax();
		$price_including_tax = WC()->cart->display_prices_including_tax();
		$price_decimal       = wc_get_price_decimals();

		if ( $price_including_tax ) {
			$cart_total = round( $cart_total - ( $discount + $discount_tax ), $price_decimal );
		} else {
			$cart_total = round( $cart_total - $discount, $price_decimal );
		}

		$remaining = $amount_free_shipping - $cart_total;
		$percent   = 100 - ( $remaining / $amount_free_shipping ) * 100;
		$content   = Helper::convert_to_string( $this->get_option( 'free_shipping_bar_text', MODERNCART_SETTINGS, __( 'You\'re {amount} away from free shipping!', 'modern-cart' ) ) );

		$classes = [ 'moderncart-slide-out-free-shipping-bar-wrapper' ];

		// Display success message when cart total meets/exceeds free shipping threshold.
		if ( $cart_total >= $amount_free_shipping ) {
			$classes[] = 'moderncart-slide-out-free-shipping-bar-wrapper--success';
			$content   = Helper::convert_to_string( $this->get_option( 'free_shipping_success_text', MODERNCART_SETTINGS, __( 'Awesome pick! You\'ve unlocked free shipping.', 'modern-cart' ) ) );
		}

		$data = [
			'classes' => implode( ' ', $classes ),
			'content' => str_replace( '{amount}', wc_price( $remaining ), $content ),
			'percent' => $percent,
		];

		moderncart_get_template_part( 'cart/free-shipping-bar', '', $data );
	}

	/**
	 * Get shipping information.
	 *
	 * @since 0.0.1
	 *
	 * @return object
	 */
	private function get_shipping_totals() {
		$cart     = WC()->cart;
		$shipping = [
			'total'     => (float) $cart->get_shipping_total(),
			'tax_total' => isset( $cart->shipping_tax_total ) ? (float) $cart->shipping_tax_total : '',
		];
		return (object) $shipping;
	}
}
