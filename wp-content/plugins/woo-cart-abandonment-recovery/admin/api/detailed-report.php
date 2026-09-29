<?php
/**
 * Detailed Report API.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Api;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class DetailedReport.
 */
class DetailedReport extends ApiBase {
	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/detailed-report/';

	/**
	 * Instance of the class.
	 *
	 * @var DetailedReport
	 */
	private static $instance;

	/**
	 * Initiator.
	 *
	 * @return DetailedReport
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register API routes.
	 */
	public function register_routes(): void {
			register_rest_route(
				$this->get_api_namespace(),
				$this->rest_base,
				[
					[
						'methods'             => \WP_REST_Server::CREATABLE,
						'callback'            => [ $this, 'get_details' ],
						'permission_callback' => [ $this, 'get_permissions_check' ],
					],
				]
			);
	}

	/**
	 * Check permissions.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error
	 */
	public function get_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return new \WP_Error( 'woo_cart_abandonment_recovery_rest_cannot_view', __( 'Sorry, you don\'t have the access to perform this action.', 'woo-cart-abandonment-recovery' ), [ 'status' => rest_authorization_required_code() ] );
		}
		return true;
	}

	/**
	 * Get detailed report data.
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_details( $request ) {
		global $wpdb;
		$id    = isset( $request['id'] ) ? absint( $request['id'] ) : 0;
		$table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$details = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) ); //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! $details ) {
			return rest_ensure_response( [] );
		}

		$user_details     = maybe_unserialize( $details->other_fields );
		$scheduled_emails = wcf_ca()->helper->fetch_scheduled_emails( $details->session_id );
		$token_data       = [ 'wcf_session_id' => $details->session_id ];
		$checkout_link    = wcf_ca()->helper->get_checkout_url( $details->checkout_id, $token_data );
		$order_details    = $this->prepare_order_details( $details->cart_contents, $details->cart_total );

		$details->order_status = $this->get_status_label( $details->order_status );

		$response = [
			'details'          => $details,
			'user_details'     => $user_details,
			'scheduled_emails' => $scheduled_emails,
			'order_details'    => $order_details,
			'checkout_link'    => $checkout_link,
		];

		$response = apply_filters( 'wcar_detailed_report_data', $response, $details );

		return rest_ensure_response( $response );
	}

	/**
	 * Format order status label.
	 *
	 * @param string $status Status code.
	 * @return string
	 */
	private function get_status_label( $status ) {
		switch ( $status ) {
			case WCF_CART_ABANDONED_ORDER:
				return 'Abandoned';
			case WCF_CART_COMPLETED_ORDER:
				return 'Successful';
			case WCF_CART_LOST_ORDER:
			case WCF_CART_FAILED_ORDER:
				return 'Failed';
			case WCF_CART_BLACKLISTED_ORDER:
				return 'Blacklisted';
			default:
				return 'Normal';
		}
	}

	/**
	 * Prepare order details from cart contents.
	 *
	 * Processes the serialized cart contents and cart total to extract detailed information
	 * about each item in the cart, including product name, quantity, price, subtotal, and image URL.
	 * Also calculates summary values such as discount, tax (others), shipping, and total cart value.
	 *
	 * @param string $cart_contents Serialized cart contents.
	 * @param float  $cart_total    Cart total.
	 * @return array {
	 *     @type array $items   List of cart items with details.
	 *     @type array $summary Summary of cart totals (discount, others, shipping, cartTotal).
	 * }
	 */
	private function prepare_order_details( $cart_contents, $cart_total ) {
		$cart_items = maybe_unserialize( $cart_contents );
		$items      = [];
		$total      = 0;
		$discount   = 0;
		$tax        = 0;

		if ( is_array( $cart_items ) ) {
			foreach ( $cart_items as $cart_item ) {
				if ( isset( $cart_item['product_id'], $cart_item['quantity'], $cart_item['line_total'], $cart_item['line_subtotal'] ) ) {
					$id           = 0 !== $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
					$product      = wc_get_product( $id );
					$product_name = $product ? $product->get_formatted_name() : '';
					$image_url    = get_the_post_thumbnail_url( $id );
					$image_url    = ! empty( $image_url ) ? $image_url : get_the_post_thumbnail_url( $cart_item['product_id'] );
					if ( empty( $image_url ) ) {
						$image_url = CARTFLOWS_CA_URL . 'admin/assets/images/image-placeholder.png';
					}

					$discount += $cart_item['line_subtotal'] - $cart_item['line_total'];
					$total    += $cart_item['line_subtotal'];
					$tax      += $cart_item['line_tax'];

					$items[] = [
						'name'     => $product_name,
						'quantity' => $cart_item['quantity'],
						'price'    => $this->format_price( $cart_item['line_total'] ),
						'subtotal' => $this->format_price( $cart_item['line_total'] ),
						'imageUrl' => $image_url,
					];
				}
			}
		}

		$summary = [
			'discount'  => $this->format_price( $discount ),
			'others'    => $this->format_price( $tax ),
			'shipping'  => $this->format_price( $discount + $cart_total - $total - $tax ),
			'cartTotal' => $this->format_price( $cart_total ),
		];

		return [
			'items'   => $items,
			'summary' => $summary,
		];
	}

	/**
	 * Format price string.
	 *
	 * @param float $amount Amount to format.
	 * @return string
	 */
	private function format_price( $amount ) {
		return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ) );
	}
}

DetailedReport::get_instance();
