<?php
/**
 * Side out ajax.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Inc;

use ModernCart\Inc\Order_Tracking;
use ModernCart\Inc\Traits\Get_Instance;
use WC_Coupon;
use WC_Discounts;

/**
 * Slide out ajax class
 *
 * @since 0.0.1
 */
class Slide_Out_Ajax extends Slide_Out {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		add_action( 'wp_ajax_moderncart_refresh_slide_out_cart', [ $this, 'refresh_slide_out_cart' ] );
		add_action( 'wp_ajax_nopriv_moderncart_refresh_slide_out_cart', [ $this, 'refresh_slide_out_cart' ] );
		add_action( 'wp_ajax_moderncart_remove_product', [ $this, 'remove_product' ] );
		add_action( 'wp_ajax_nopriv_moderncart_remove_product', [ $this, 'remove_product' ] );
		add_action( 'wp_ajax_moderncart_update_cart', [ $this, 'update_cart' ] );
		add_action( 'wp_ajax_nopriv_moderncart_update_cart', [ $this, 'update_cart' ] );
		add_action( 'wp_ajax_moderncart_apply_coupon', [ $this, 'apply_coupon' ] );
		add_action( 'wp_ajax_nopriv_moderncart_apply_coupon', [ $this, 'apply_coupon' ] );
		add_action( 'wp_ajax_moderncart_remove_coupon', [ $this, 'remove_coupon' ] );
		add_action( 'wp_ajax_nopriv_moderncart_remove_coupon', [ $this, 'remove_coupon' ] );
		add_action( 'wp_ajax_moderncart_add_to_cart', [ $this, 'add_to_cart' ] );
		add_action( 'wp_ajax_nopriv_moderncart_add_to_cart', [ $this, 'add_to_cart' ] );
	}

	/**
	 * Add to cart via AJAX
	 *
	 * Handles adding products to the cart via AJAX and returns updated cart content
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function add_to_cart(): void {
		if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
			wp_die();
		}

		if ( empty( $_POST['productData'] ) ) {
			wp_send_json_error( [ 'message' => esc_html__( 'Product Data missing', 'modern-cart' ) ] );
		}

		// Extract form entries so third-party plugin data (add-ons, gift cards, etc.) is available in $_POST.
		$form_entries = [];
		if ( ! empty( $_POST['formEntries'] ) && is_array( $_POST['formEntries'] ) ) {
			foreach ( $_POST['formEntries'] as $key => $value ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below per field.
				$sanitized_key = sanitize_title( wp_unslash( $key ) );

				if ( is_array( $value ) ) {
					$form_entries[ $sanitized_key ] = array_map(
						static function ( $item ) {
							return is_array( $item ) ? array_map( 'sanitize_text_field', $item ) : sanitize_text_field( $item );
						},
						wp_unslash( $value )
					);
				} else {
					$form_entries[ $sanitized_key ] = sanitize_text_field( wp_unslash( $value ) );
				}
			}
		}

		// Setting default for $url.
		$url          = '';
		$message      = '';
		$message_type = '';
		if ( is_array( $_POST['productData'] ) ) {
			foreach ( wp_unslash( $_POST['productData'] ) as $product ) { //phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized --> Not necessary here as we are not directly using the data.
				$product_id   = ( isset( $product['productId'] ) ? absint( $product['productId'] ) : '' );
				$variation_id = ( isset( $product['variationId'] ) ? absint( $product['variationId'] ) : 0 );
				$quantity     = ( isset( $product['quantity'] ) ? absint( $product['quantity'] ) : 1 );
				$variations   = [];

				if ( ! empty( $product['attributes'] ) && is_array( $product['attributes'] ) ) {
					foreach ( $product['attributes'] as $key => $value ) {
						$variations[ sanitize_title( wp_unslash( $key ) ) ] = sanitize_text_field( wp_unslash( $value ) );
					}
				}

				if ( ! $product_id && isset( $form_entries['add-to-cart'] ) ) {
					$product_id = absint( wp_unslash( $form_entries['add-to-cart'] ) );
				}

				if ( ! is_numeric( $product_id ) || $product_id < 0 || ! $product_id ) {
					wp_send_json(
						[
							'error' => esc_html__( 'Did not pass security check', 'modern-cart' ),
						],
						403
					);
				}
				$product_to_add = wc_get_product( $product_id );
				if ( ! $product_to_add || ! is_a( $product_to_add, 'WC_Product' ) ) {
					wp_send_json_error( [ 'message' => esc_html__( 'Product not found', 'modern-cart' ) ] );
				}

				if ( $product_to_add->is_sold_individually() ) {
					// Check if the product is already in the cart.
					$cart = WC()->cart->get_cart();
					foreach ( $cart as $cart_item ) {
						if ( absint( $cart_item['product_id'] ) === absint( $product_id ) ) {
							// Product is already in the cart, return an error message.
							/* translators: %s: product name */
							wp_send_json_error( [ 'message' => sprintf( esc_html__( 'You cannot add another "%s" to your cart.', 'modern-cart' ), esc_html( $product_to_add->get_name() ) ) ] );
						}
					}
				}

				// Merge form entries into $_POST so third-party plugins can read their custom fields.
				if ( ! empty( $form_entries ) ) {
					$_POST = array_merge( $product, $form_entries ); //phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
				}

				$message      = '';
				$message_type = '';
				$product      = moderncart_get_product_by_id( $product_id );
				$url          = esc_url_raw( apply_filters( 'moderncart_woocommerce_add_to_cart_redirect', false, $product ) );

				if ( WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variations ) ) {
					do_action( 'moderncart_woocommerce_ajax_added_to_cart', $product_id );
					Order_Tracking::flag_session();

					/* translators: %s: product name */
					$message      = $product instanceof \WC_Product ? sprintf( esc_html__( '"%s" has been added to the cart.', 'modern-cart' ), esc_html( $product->get_name() ) ) : '';
					$message_type = 'success';
				} else {
					$message      = esc_html__( 'Product not added on cart. Try again.', 'modern-cart' );
					$message_type = 'error';
				}
			}
		}

		$notice = '<div class="moderncart-notification moderncart-has-shadow moderncart-is-light moderncart-is-' . esc_attr( $message_type ) . '" data-type="' . esc_attr( $message_type ) . '" role="status" aria-live="assertive" aria-atomic="true" aria-label="' . esc_attr( $message ) . '">' . esc_html( $message ) . '</div>';

		$data = [
			'classes'    => $this->get_slide_out_classes(),
			'attributes' => [
				'tabindex' => '-1',
				'role'     => 'dialog',
			],
			'notice'     => $notice,
		];

		ob_start();
		moderncart_get_template_part( 'shop/slide-out-inner', '', $data );
		$result = ob_get_contents();
		ob_end_clean();

		$return = [
			'content'     => $result,
			'redirect_to' => $url,
		];
		wp_send_json( $return );
	}

	/**
	 * Apply coupon to cart
	 *
	 * Handles applying coupon codes to the cart via AJAX
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function apply_coupon(): void {
		if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
			wp_die();
		}

		$coupon_code  = ( isset( $_POST['coupon'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) ) : false );
		$message_type = '';

		if ( $coupon_code ) {
			if ( ! WC()->cart->has_discount( $coupon_code ) ) {

				if ( WC()->cart->apply_coupon( $coupon_code ) ) {
					Order_Tracking::flag_session();

					// Set flag for first_coupon_applied analytics event.
					if ( ! get_option( 'mcw_first_coupon_applied', false ) ) {
						update_option( 'mcw_first_coupon_applied', true, false );
					}

					// Increment daily coupon count for coupon_applied_count KPI.
					$today         = current_time( 'Y-m-d' );
					$coupon_counts = (array) get_option( 'mcw_daily_coupon_counts', array() );
					if ( ! isset( $coupon_counts[ $today ] ) ) {
						$coupon_counts[ $today ] = 0;
					}
					$coupon_counts[ $today ]++;

					// Prune entries older than 3 days.
					$cutoff = gmdate( 'Y-m-d', strtotime( '-3 days' ) );
					foreach ( array_keys( $coupon_counts ) as $date_key ) {
						if ( $date_key < $cutoff ) {
							unset( $coupon_counts[ $date_key ] );
						}
					}
					update_option( 'mcw_daily_coupon_counts', $coupon_counts, false );

					$message      = esc_html__( 'Your coupon code was applied successfully.', 'modern-cart' );
					$message_type = 'success';
				} else {
					$coupon    = new WC_Coupon( $coupon_code );
					$discounts = new WC_Discounts( WC()->cart );
					$valid     = $discounts->is_coupon_valid( $coupon );

					if ( is_wp_error( $valid ) ) {
						$message = $valid->get_error_message();

						if ( $message ) {
							$message = wp_kses_post( $message );
							WC()->session->set( 'moderncart_coupon_error', $message );
							$message_type = 'error';
						}
					}

					if ( empty( $message ) ) {
						$message      = esc_html__( 'Sorry, this coupon code is not valid!', 'modern-cart' );
						$message_type = 'error';
					}
				}
			} else {
				$message      = esc_html__( 'Sorry, this coupon code is already applied!', 'modern-cart' );
				$message_type = 'error';
			}
		} else {
			$message      = esc_html__( 'Enter a coupon code!', 'modern-cart' );
			$message_type = 'error';
		}

		$notice = '<div class="moderncart-notification moderncart-has-shadow moderncart-is-light moderncart-is-' . esc_attr( $message_type ) . '" data-type="' . esc_attr( $message_type ) . '" role="status" aria-live="assertive" aria-atomic="true" aria-label="' . esc_attr( $message ) . '">' . esc_html( $message ) . '</div>';

		$data = [
			'classes'      => $this->get_slide_out_classes(),
			'attributes'   => [
				'tabindex' => '-1',
				'role'     => 'dialog',
			],
			'notice'       => $notice,
			'message_type' => $message_type,
		];

		ob_start();
		moderncart_get_template_part( 'shop/slide-out-inner', '', $data );
		$result = ob_get_contents();
		ob_end_clean();

		$return = [ 'content' => $result ];
		wc_clear_notices();
		wp_send_json( $return );
	}

	/**
	 * Remove coupon from cart
	 *
	 * Handles removing coupon codes from the cart via AJAX
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function remove_coupon(): void {
		if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
			wp_die();
		}

		$coupon = ( isset( $_POST['coupon'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) ) : false );

		if ( empty( $coupon ) ) {
			$message      = esc_html__( 'Sorry there was a problem removing this coupon.', 'modern-cart' );
			$message_type = 'error';
		} else {
			WC()->cart->remove_coupon( $coupon );
			Order_Tracking::flag_session();
			$message      = esc_html__( 'Coupon has been removed.', 'modern-cart' );
			$message_type = 'success';

			WC()->cart->calculate_shipping();
			WC()->cart->calculate_totals();
		}

		wc_clear_notices();
		$notice = '<div class="moderncart-notification moderncart-has-shadow moderncart-is-light moderncart-is-' . esc_attr( $message_type ) . '" data-type="' . esc_attr( $message_type ) . '" role="status" aria-live="assertive" aria-atomic="true" aria-label="' . esc_attr( $message ) . '">' . esc_html( $message ) . '</div>';

		$data = [
			'classes'      => $this->get_slide_out_classes(),
			'attributes'   => [
				'tabindex' => '-1',
				'role'     => 'dialog',
			],
			'notice'       => $notice,
			'message_type' => $message_type,
		];

		ob_start();
		moderncart_get_template_part( 'shop/slide-out-inner', '', $data );
		$result = ob_get_contents();
		ob_end_clean();

		$return = [ 'content' => $result ];
		wp_send_json( $return );
	}

	/**
	 * Update the cart quantity
	 *
	 * Handles updating product quantities in the cart via AJAX
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function update_cart(): void {
		if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
			wp_die();
		}

		$cart_key = ( isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '' );
		$quantity = ( isset( $_POST['quantity'] ) ? sanitize_text_field( wp_unslash( $_POST['quantity'] ) ) : '' );

		if ( ! is_numeric( $quantity ) || $quantity < 0 || ! $cart_key ) {
			wp_send_json(
				[
					'error' => esc_html__( 'Did not pass security check', 'modern-cart' ),
				],
				403
			);
		}

		$action                   = '';
		$message                  = '';
		$message_type             = '';
		$removed                  = false;
		$in_stock                 = true;
		$product_qty_in_cart      = WC()->cart->get_cart_item_quantities();
		$current_session_order_id = ( isset( WC()->session->order_awaiting_payment ) ? absint( WC()->session->order_awaiting_payment ) : 0 );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			if ( $cart_key === $cart_item_key ) {
				$product = $values['data'];

				if ( $product->managing_stock() && $quantity > $product->get_stock_quantity() ) {
					$in_stock = false;
					/* translators: %s: product name */
					$message      = sprintf( esc_html__( 'Sorry, "%s" is not in stock. Please edit your cart and try again. We apologize for any inconvenience caused.', 'modern-cart' ), esc_html( $product->get_name() ) );
					$message_type = 'error';
				}

				if ( ! $product->managing_stock() || $product->backorders_allowed() ) {
					$in_stock = true;
					/* translators: %s: product name */
					$message      = sprintf( esc_html__( '"%s" has been updated.', 'modern-cart' ), esc_html( $product->get_name() ) );
					$message_type = 'success';
				}

				$held_stock     = wc_get_held_stock_quantity( $product, $current_session_order_id );
				$required_stock = $product_qty_in_cart[ $product->get_stock_managed_by_id() ];

				if ( $product->managing_stock() && ! $product->backorders_allowed() && $product->get_stock_quantity() < $held_stock + $required_stock ) {
					$in_stock = false;
					/* translators: 1: product name 2: quantity in stock */
					$message      = sprintf( esc_html__( 'Sorry, we do not have enough "%1$s" in stock to fulfill your order (%2$s available). We apologize for any inconvenience caused.', 'modern-cart' ), esc_html( $product->get_name() ), wc_format_stock_quantity_for_display( $product->get_stock_quantity() - $held_stock, $product ) );
					$message_type = 'error';
				}

				if ( 0 === (int) $quantity ) {
					$removed = true;
					/* translators: %s: product name */
					$message      = sprintf( esc_html__( '"%s" was removed from your cart', 'modern-cart' ), esc_html( $product->get_name() ) );
					$message_type = 'success';
				}

				break;
			}
		}

		if ( $in_stock && $quantity > 0 ) {
			$action = WC()->cart->set_quantity( $cart_key, Helper::convert_to_int( $quantity ) );
		} elseif ( $removed ) {
			$action = WC()->cart->remove_cart_item( $cart_key );
		}

		Order_Tracking::flag_session();

		$notice = '<div class="moderncart-notification moderncart-has-shadow moderncart-is-light moderncart-is-' . esc_attr( $message_type ) . '" data-type="' . esc_attr( $message_type ) . '" role="status" aria-live="assertive" aria-atomic="true" aria-label="' . esc_attr( $message ) . '">' . esc_html( $message ) . '</div>';

		$data = [
			'classes'    => $this->get_slide_out_classes(),
			'attributes' => [
				'tabindex' => '-1',
				'role'     => 'dialog',
			],
			'notice'     => $notice,
			'action'     => $action, // Not used, added for PHPInsights.
		];

		ob_start();
		moderncart_get_template_part( 'shop/slide-out-inner', '', $data );
		$result = ob_get_contents();
		ob_end_clean();

		$return = [ 'content' => $result ];
		wp_send_json( $return );
	}

	/**
	 * Refresh slide out cart
	 *
	 * Handles refreshing the slide out cart content via AJAX
	 * Used when cart is updated from other sources
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function refresh_slide_out_cart(): void {
		Helper::set_nocache_headers();

		if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
			wp_die();
		}

		$notice_action = ( ! empty( $_POST['notice_action'] ) ? rest_sanitize_boolean( sanitize_text_field( wp_unslash( $_POST['notice_action'] ) ) ) : false );
		$notice        = '';

		if ( $notice_action ) {
			$type   = 'success';
			$notice = esc_html__( 'Cart updated successfully!', 'modern-cart' );
			$notice = '<div class="moderncart-notification moderncart-has-shadow moderncart-is-light moderncart-is-' . esc_attr( $type ) . '" data-type="' . esc_attr( $type ) . '" role="status" aria-live="assertive" aria-atomic="true" aria-label="' . esc_attr( $notice ) . '">' . esc_html( $notice ) . '</div>';
		}

		$data = [
			'classes'    => $this->get_slide_out_classes(),
			'attributes' => [
				'tabindex' => '-1',
				'role'     => 'dialog',
			],
			'notice'     => $notice,
		];

		ob_start();

		moderncart_get_template_part( 'shop/slide-out-inner', '', $data );
		$result = ob_get_contents();
		ob_end_clean();

		$return = [ 'content' => $result ];
		wp_send_json( $return );
	}

	/**
	 * Remove a product from the cart
	 *
	 * Handles removing products from the cart via AJAX
	 * Includes undo functionality
	 *
	 * @since 0.0.1
	 * @return void
	 */
	public function remove_product(): void {
		if ( ! isset( $_POST['moderncart_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['moderncart_nonce'] ) ), 'moderncart_ajax_nonce' ) ) {
			wp_die();
		}

		$cart_item_key  = ( ! empty( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : null );
		$cart_item      = WC()->cart->get_cart_item( Helper::convert_to_string( $cart_item_key ) );
		$removed_notice = '';

		if ( $cart_item ) {
			WC()->cart->remove_cart_item( Helper::convert_to_string( $cart_item_key ) );
			Order_Tracking::flag_session();
			$product = moderncart_get_product_by_id( $cart_item['product_id'] );
			/* Translators: %s Product title. */
			$item_removed_title = apply_filters( 'moderncart_cart_item_removed_title', ( $product ? sprintf( esc_html_x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'modern-cart' ), esc_html( $product->get_name() ) ) : esc_html__( 'Item', 'modern-cart' ) ), $cart_item );

			if ( $product && $product->is_in_stock() && $product->has_enough_stock( $cart_item['quantity'] ) ) {
				/* Translators: %s Product title. */
				$removed_notice  = sprintf( esc_html__( '%s removed.', 'modern-cart' ), esc_html( $item_removed_title ) );
				$removed_notice .= ' <a href="#" data-key="' . esc_attr( (string) $cart_item_key ) . '" class="moderncart-restore-item">' . esc_html__( 'Undo?', 'modern-cart' ) . '</a>';
			} else {
				/* Translators: %s Product title. */
				$removed_notice = sprintf( esc_html__( '%s removed.', 'modern-cart' ), esc_html( $item_removed_title ) );
			}

			WC()->session->set( 'moderncart_last_removed_item_name', $item_removed_title );
		}

		wc_clear_notices();

		$return = [
			'success' => 1,
			'notice'  => $removed_notice,
		];
		wp_send_json( $return );
	}
}
