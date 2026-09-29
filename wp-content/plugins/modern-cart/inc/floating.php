<?php
/**
 * Floating cart.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Inc;

use ModernCart\Inc\Traits\Get_Instance;

/**
 * Floating class
 *
 * @since 0.0.1
 */
class Floating extends Cart {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		if ( Helper::is_maintenance_mode() ) {
			return;
		}
		// Only register wp_footer hook on frontend pages — not in admin/AJAX context.
		// Other hooks must remain registered so AJAX refresh handlers can render cart content.
		if ( ! is_admin() ) {
			add_action( 'wp_footer', [ $this, 'floating_cart' ] );
		}
		add_filter( 'astra_cart_in_menu_class', [ $this, 'modify_mini_cart_classes' ] );
		add_filter( 'astra_get_option_woo-header-cart-click-action', [ $this, 'modify_astra_slideout' ] );
		add_filter( 'astra_get_option_shop-add-to-cart-action', [ $this, 'disable_astra_slideout' ], 10, 3 );
		add_action( 'wp_loaded', [ $this, 'disable_astra_mobile_slideout' ] );
	}

	/**
	 * Modify astra slideout cart
	 *
	 * @since 0.0.1
	 *
	 * @return string
	 */
	public function modify_astra_slideout() {
		return '';
	}

	/**
	 * Modify astra mini cart menu classes
	 *
	 * @since 0.0.1
	 *
	 * @param array<string> $classes Menu classes.
	 *
	 * @return array<string> $classes
	 */
	public function modify_mini_cart_classes( $classes ) {
		$classes[] = 'modern-cart-for-wc-available';

		return $classes;
	}

	/**
	 * Disable Astra's Slideout Cart on shop if it is enabled while using the Modern Cart.
	 *
	 * @since 0.0.1
	 *
	 * @param string $value The option value.
	 * @param string $option The option name.
	 * @param string $default The default value.
	 *
	 * @return string $value The updated option value.
	 */
	public function disable_astra_slideout( $value, $option, $default ) {
		// Check if Astra theme is active and slide in cart is enabled.
		if ( defined( 'ASTRA_THEME_VERSION' ) && ! empty( $value ) && 'slide_in_cart' === $value ) {
				$value = $default;
		}
		return $value;
	}

	/**
	 * Remove Astra's mobile cart flyout.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function disable_astra_mobile_slideout(): void {
		if ( ! class_exists( 'Astra_Builder_Header' ) ) {
			return;
		}
		$astra_builder_header = \Astra_Builder_Header::get_instance();
		remove_action( 'astra_footer', [ $astra_builder_header, 'mobile_cart_flyout' ] );
	}

	/**
	 * Floating cart
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function floating_cart(): void {
		if (
			! $this->is_global_enabled() ||
			! $this->get_option( 'display_floating_cart_icon', MODERNCART_FLOATING_SETTINGS, true )
		) {
			return;
		}

		$hide_if_empty  = $this->get_option( 'enable_floating_if_empty', MODERNCART_FLOATING_SETTINGS, false );
		$cart_icon      = $this->get_option( 'floating_cart_icon', MODERNCART_FLOATING_SETTINGS, 0 );
		$cart_svg_icons = Helper::get_cart_icons();
		$cart_count     = Helper::get_cart_count();

		$data = [
			'classes'        => apply_filters( 'moderncart_floating_cart_launcher_classes', [ 'moderncart-toggle-slide-out' ] ),
			'item_account'   => $cart_count,
			'cart_icon'      => $cart_icon,
			'cart_svg_icons' => $cart_svg_icons,
		];

		if ( $hide_if_empty && 0 === $cart_count ) {
			$data['classes'][] = 'moderncart-floating-cart-empty';
		}

		moderncart_get_template_part( 'shop/floating', '', $data );
	}
}
