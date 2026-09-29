<?php
/**
 * Scripts.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Inc;

use ModernCart\Inc\Traits\Get_Instance;
use ModernCart\Inc\Cart;

/**
 * Scripts
 *
 * @since 0.0.1
 */
class Scripts extends Cart {
	use Get_Instance;

	/**
	 * Plugin version.
	 *
	 * @var string $version Current plugin version.
	 */
	public $version;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		// @phpstan-ignore-next-line - Debug condition can be overridden
		$this->version = defined( 'MODERNCART_DEBUG' ) && MODERNCART_DEBUG ? (string) time() . '-' . MODERNCART_VER : MODERNCART_VER;

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'dynamic_styles' ] );
	}

	/**
	 * Dynamic styles
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function dynamic_styles() {
		if ( ! $this->is_global_enabled() ) {
			return;
		}

		$primary_color = Helper::convert_to_string( $this->get_option( 'primary_color', MODERNCART_APPEARANCE_SETTINGS, '#0284C7' ) );
		$heading_color = Helper::convert_to_string( $this->get_option( 'heading_color', MODERNCART_APPEARANCE_SETTINGS, '#0284C7' ) );
		$body_color    = Helper::convert_to_string( $this->get_option( 'body_color', MODERNCART_APPEARANCE_SETTINGS, '#0284C7' ) );

		$cart_header_text_alignment = Helper::convert_to_string( $this->get_option( 'cart_header_text_alignment', MODERNCART_APPEARANCE_SETTINGS, 'center' ) );
		$cart_header_font_size      = Helper::convert_to_string( $this->get_option( 'cart_header_font_size', MODERNCART_APPEARANCE_SETTINGS, 22 ) ) . 'px';

		// Sanitize color/number settings.
		$css_vars = apply_filters(
			'moderncart_css_vars',
			[
				'--moderncart-background-color'           => '#FFFFFF',
				'--moderncart-highlight-color'            => '#10B981',
				'--moderncart-button-font-color'          => '#FFFFFF',
				'--moderncart-header-font-color'          => '#1F2937',
				'--moderncart-header-background-color'    => '#FFFFFF',
				'--moderncart-quantity-font-color'        => '#1F2937',
				'--moderncart-quantity-background-color'  => '#EAEFF3',
				'--moderncart-floating-icon-color'        => '#FFFFFF',
				'--moderncart-floating-count-text-color'  => '#FFFFFF',
				'--moderncart-floating-count-bg-color'    => '#10B981',
				'--moderncart-cart-header-text-alignment' => $cart_header_text_alignment,
				'--moderncart-cart-header-font-size'      => $cart_header_font_size,
				'--moderncart-floating-icon-bg-color'     => $primary_color,
				'--moderncart-primary-color'              => $primary_color,
				'--moderncart-heading-color'              => $heading_color,
				'--moderncart-body-color'                 => $body_color,
				'--moderncart-slide-out-desktop-width'    => $this->get_option( 'slide_out_width_desktop', MODERNCART_SETTINGS, 450 ) . 'px',
				'--moderncart-slide-out-mobile-width'     => $this->get_option( 'slide_out_width_mobile', MODERNCART_SETTINGS, 80 ) . '%',
				'--moderncart-animation-duration'         => $this->get_option( 'animation_speed', MODERNCART_SETTINGS, 300 ) . 'ms',
				'--moderncart-cart-item-padding'          => $this->get_option( 'cart_item_padding', MODERNCART_SETTINGS, 20 ) . 'px',
			]
		);

		$dynamic_css = ':root {' . PHP_EOL;

		foreach ( $css_vars as $var => $value ) {
			$escaped_var   = esc_html( $var );
			$escaped_value = esc_html( (string) $value );
			$dynamic_css  .= "\t{$escaped_var}: {$escaped_value};" . PHP_EOL;

			// Add light variant if it's a valid hex color.
			if ( false !== strpos( $escaped_var, '-color' ) && preg_match( '/^#[0-9a-fA-F]{6}$/', $escaped_value ) ) {
				$dynamic_css .= "\t{$escaped_var}-light: {$escaped_value}12;" . PHP_EOL;
			}
		}

		$dynamic_css .= '}';

		// Add cart item padding CSS.
		$dynamic_css .= '.moderncart-cart-item {
			padding-left: var(--moderncart-cart-item-padding);
			padding-right: var(--moderncart-cart-item-padding);
		}';

		$cart_launcher_position = $this->get_option( 'floating_cart_position', MODERNCART_FLOATING_SETTINGS, 'bottom-right' );

		if ( $cart_launcher_position && 'bottom-right' === $cart_launcher_position ) {
			$dynamic_css .= '#moderncart-floating-cart {
				left: auto;
				right: 20px;
				flex-direction: row-reverse;
			}';
		} elseif ( $cart_launcher_position && 'bottom-left' === $cart_launcher_position ) {
			$dynamic_css .= '#moderncart-floating-cart {
				left: 20px;
				right: auto;
			}';
		}

		if ( empty( $this->get_option( 'enable_express_checkout', MODERNCART_MAIN_SETTINGS, false ) ) ) {
			$dynamic_css .= '.moderncart-slide-out-footer #cpsw-payment-request-wrapper {
				display: none !important;
			}';
		}

		// Dynamic CSS related to RTL mode.
		if ( is_rtl() ) {
			$dynamic_css .= '#moderncart-slide-out .moderncart-slide-out-footer .moderncart-order-summary-style-style2 #moderncart-coupon-form-container .moderncart-coupon-remove {
				justify-content: right;
			}';
		}

		wp_add_inline_style( 'moderncart-cart-css', $dynamic_css );
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_global_enabled() ) {
			return;
		}

		$moderncart_setting = (array) get_option( MODERNCART_MAIN_SETTINGS, [] );

		wp_register_style( 'moderncart-cart-css', MODERNCART_URL . 'assets/css/cart.css', [], $this->version );
		wp_style_add_data( 'moderncart-cart-css', 'rtl', 'replace' );
		wp_enqueue_style( 'moderncart-cart-css' );

		wp_register_script( 'moderncart-cart-js', MODERNCART_URL . 'assets/js/cart.js', [ 'jquery' ], $this->version, true );
		wp_enqueue_script( 'moderncart-cart-js' );

		wp_localize_script(
			'moderncart-cart-js',
			'moderncart_ajax_object',
			apply_filters(
				'moderncart_localize_script_args',
				[
					'ajax_url'                  => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'                => wp_create_nonce( 'moderncart_ajax_nonce' ),
					'general_error'             => esc_html__( 'Somethings wrong! try again later', 'modern-cart' ),
					'edit_cart_text'            => esc_html__( 'Edit Cart', 'modern-cart' ),
					'is_needed_edit_cart'       => ! function_exists( '_get_wcf_step_id' ) && 'astra' !== get_template() ? false : true,
					'empty_cart_recommendation' => $this->get_option( 'empty_cart_recommendation', MODERNCART_SETTINGS, 'disabled' ),
					'animation_speed'           => $this->get_option( 'animation_speed', MODERNCART_SETTINGS, '300' ),
					'enable_coupon_field'       => $this->get_option( 'enable_coupon_field', MODERNCART_SETTINGS, 'minimize' ),
					'cart_redirect_after_add'   => apply_filters( 'moderncart_redirect_after_add_to_cart', false ),
					'cart_opening_direction'    => 'right',
					'disable_ajax_add_to_cart'  => apply_filters( 'moderncart_disable_ajax_add_to_cart', isset( $moderncart_setting['enable_ajax_add_to_cart'] ) ? ! $moderncart_setting['enable_ajax_add_to_cart'] : false ),
					'i18n'                      => [
						'cart_opened'            => esc_html__( 'Cart is opened', 'modern-cart' ),
						'cart_closed'            => esc_html__( 'Cart is closed', 'modern-cart' ),
						/* translators: %d: current cart quantity */
						'quantity_decreased'     => esc_html__( 'Quantity decreased, current quantity %d', 'modern-cart' ),
						/* translators: %d: current cart quantity */
						'quantity_increased'     => esc_html__( 'Quantity increased, current quantity %d', 'modern-cart' ),
						'recommendations_opened' => esc_html__( 'Recommendations opened', 'modern-cart' ),
						'recommendations_closed' => esc_html__( 'Recommendations closed', 'modern-cart' ),
					],
				]
			)
		);
	}
}
