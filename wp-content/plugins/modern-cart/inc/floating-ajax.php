<?php
/**
 * Floating cart ajax.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Inc;

use ModernCart\Inc\Traits\Get_Instance;

/**
 * Floating cart ajax class
 *
 * @since 0.0.1
 */
class Floating_Ajax extends Floating {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		add_action( 'wp_ajax_moderncart_refresh_floating_cart', [ $this, 'refresh_floating_cart' ] );
		add_action( 'wp_ajax_nopriv_moderncart_refresh_floating_cart', [ $this, 'refresh_floating_cart' ] );
	}

	/**
	 * Refresh slide out cart
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function refresh_floating_cart(): void {
		Helper::set_nocache_headers();
		if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Nonce verification failed.', 'modern-cart' ) ], 403 );
		}

		if ( 'disabled' === $this->get_option( 'floating_cart_position', MODERNCART_FLOATING_SETTINGS, 'bottom-left' ) ) {
			return;
		}

		$hide_if_empty  = $this->get_option( 'enable_floating_if_empty', MODERNCART_FLOATING_SETTINGS, false );
		$cart_count     = Helper::get_cart_count();
		$cart_icon      = $this->get_option( 'floating_cart_icon', MODERNCART_FLOATING_SETTINGS, 0 );
		$cart_svg_icons = Helper::get_cart_icons();

		$data = [
			'classes'        => apply_filters( 'moderncart_floating_cart_launcher_classes', [ 'moderncart-toggle-slide-out' ] ),
			'item_account'   => $cart_count,
			'cart_icon'      => $cart_icon, // Cart icon for floating cart refresh.
			'cart_svg_icons' => $cart_svg_icons, // Cart icon for floating cart refresh.
		];

		if ( $hide_if_empty && 0 === $cart_count ) {
			$data['classes'][] = 'moderncart-floating-cart-empty';
		}

		ob_start();
		moderncart_get_template_part( 'shop/floating-inner', '', $data );
		$result = ob_get_contents();
		ob_end_clean();

		$return = [ 'content' => $result ];
		wp_send_json( $return );
	}
}
