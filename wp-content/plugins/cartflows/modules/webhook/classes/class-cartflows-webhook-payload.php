<?php
/**
 * CartFlows Webhook Payload Builder.
 *
 * Builds JSON payloads per event type.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Webhook_Payload' ) ) {

	/**
	 * Class Cartflows_Webhook_Payload.
	 */
	class Cartflows_Webhook_Payload {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance = null;

		/**
		 * Initiator
		 *
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Build payload for an event.
		 *
		 * @param string $event Event name.
		 * @param array  $context Event context data.
		 * @param string $webhook_id Webhook ID.
		 * @return array
		 */
		public function build( $event, $context, $webhook_id = '' ) {
			$payload = array(
				'event'      => $event,
				'timestamp'  => gmdate( 'c' ),
				'webhook_id' => $webhook_id,
				'data'       => $this->build_event_data( $event, $context ),
			);

			return apply_filters( 'cartflows_webhook_payload', $payload, $event, $context );
		}

		/**
		 * Build a test payload.
		 *
		 * @param string $webhook_id Webhook ID.
		 * @return array
		 */
		public function build_test( $webhook_id ) {
			return array(
				'event'      => 'test',
				'timestamp'  => gmdate( 'c' ),
				'webhook_id' => $webhook_id,
				'data'       => array(
					'message'   => 'This is a test webhook from CartFlows.',
					'funnel_id' => 0,
					'step_id'   => 0,
				),
			);
		}

		/**
		 * Build event-specific data.
		 *
		 * @param string $event Event name.
		 * @param array  $context Event context.
		 * @return array
		 */
		private function build_event_data( $event, $context ) {
			$data = array(
				'funnel_id' => isset( $context['funnel_id'] ) ? absint( $context['funnel_id'] ) : 0,
				'step_id'   => isset( $context['step_id'] ) ? absint( $context['step_id'] ) : 0,
			);

			switch ( $event ) {
				case 'cartflows_step_visited':
					$data['step_type'] = isset( $context['step_type'] ) ? sanitize_text_field( $context['step_type'] ) : '';
					$data['step_url']  = isset( $context['step_url'] ) ? esc_url_raw( $context['step_url'] ) : '';
					break;

				case 'checkout_initiated':
					$data = array_merge( $data, $this->get_order_data( $context ) );
					break;

				case 'order_created':
				case 'order_completed':
				case 'wcf-main-order':
				case 'order_failed':
					$data = array_merge( $data, $this->get_order_data( $context ) );
					break;

				case 'order_bump_accepted':
				case 'order_bump_skipped':
					$data                 = array_merge( $data, $this->get_order_data( $context ) );
					$data['bump_product'] = isset( $context['bump_product'] ) ? $context['bump_product'] : array();
					break;

				case 'upsell_accepted':
				case 'upsell_skipped':
				case 'downsell_accepted':
				case 'downsell_skipped':
					$data                  = array_merge( $data, $this->get_order_data( $context ) );
					$data['offer_product'] = isset( $context['offer_product'] ) ? $context['offer_product'] : array();
					break;
			}

			return $data;
		}

		/**
		 * Get order-related data from context.
		 *
		 * @param array $context Event context.
		 * @return array
		 */
		private function get_order_data( $context ) {
			$data = array();

			if ( isset( $context['order_id'] ) ) {
				$data['order_id'] = absint( $context['order_id'] );
			}

			$order = isset( $context['order'] ) ? $context['order'] : null;

			if ( ! $order && isset( $context['order_id'] ) ) {
				$order = wc_get_order( $context['order_id'] );
			}

			if ( $order && is_a( $order, 'WC_Order' ) ) {
				$data['order_id'] = $order->get_id();
				$data['customer'] = array(
					'id'         => $order->get_customer_id(),
					'email'      => $order->get_billing_email(),
					'first_name' => $order->get_billing_first_name(),
					'last_name'  => $order->get_billing_last_name(),
				);

				$data['products'] = array();
				foreach ( $order->get_items() as $item ) {
					$product            = $item->get_product();
					$data['products'][] = array(
						'id'       => $product ? $product->get_id() : 0,
						'name'     => $item->get_name(),
						'quantity' => $item->get_quantity(),
						'total'    => $item->get_total(),
					);
				}

				$data['totals'] = array(
					'total'    => $order->get_total(),
					'currency' => $order->get_currency(),
				);
			}

			return $data;
		}
	}
}
