<?php
/**
 * CartFlows Webhook Events.
 *
 * Registers WordPress action hooks that trigger webhook dispatches.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Webhook_Events' ) ) {

	/**
	 * Class Cartflows_Webhook_Events.
	 */
	class Cartflows_Webhook_Events {

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
		 * Constructor
		 */
		public function __construct() {
			$this->register_hooks();
		}

		/**
		 * Register all event hooks.
		 */
		private function register_hooks() {
			// Step visited.
			add_action( 'template_redirect', array( $this, 'handle_step_visited' ) );

			// Checkout initiated — hook passes parsed checkout form data as first argument.
			add_action( 'cartflows_woo_checkout_update_order_review_init', array( $this, 'handle_checkout_initiated' ), 10, 1 );

			// Order created (free version uses woocommerce_checkout_order_processed, filtered by CartFlows flow).
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'handle_order_created' ), 10, 3 );

			// Order completed and Order failed — uses woocommerce_order_status_changed for reliability across WC versions and HPOS.
			add_action( 'woocommerce_order_status_changed', array( $this, 'handle_order_status_changed' ), 10, 4 );
		}

		/**
		 * Get available events for the free version.
		 *
		 * @return array
		 */
		public static function get_available_events() {
			$events = array(
				'cartflows_step_visited' => __( 'Funnel Step Visited', 'cartflows' ),
				'checkout_initiated'     => __( 'Checkout Initiated', 'cartflows' ),
				'order_created'          => __( 'Order Created', 'cartflows' ),
				'order_completed'        => __( 'Order Completed', 'cartflows' ),
				'order_failed'           => __( 'Order Failed', 'cartflows' ),
			);

			return apply_filters( 'cartflows_webhook_available_events', $events );
		}

		/**
		 * Handle step visited event.
		 */
		public function handle_step_visited() {
			if ( ! function_exists( 'wcf_get_step_type' ) ) {
				return;
			}

			global $post;

			if ( ! $post ) {
				return;
			}

			$step_id   = $post->ID;
			$step_type = wcf_get_step_type( $step_id );

			if ( empty( $step_type ) ) {
				return;
			}

			$flow_id = get_post_meta( $step_id, 'wcf-flow-id', true );

			if ( empty( $flow_id ) ) {
				return;
			}

			$context = array(
				'funnel_id' => absint( $flow_id ),
				'step_id'   => absint( $step_id ),
				'step_type' => $step_type,
				'step_url'  => get_permalink( $step_id ),
			);

			wcf()->logger->log( 'Webhook event: cartflows_step_visited - Step ' . $step_id . ' (Type: ' . $step_type . ')' );

			Cartflows_Webhook_Dispatcher::get_instance()->dispatch( 'cartflows_step_visited', $context );
		}

		/**
		 * Handle checkout initiated event.
		 *
		 * Fired by cartflows_woo_checkout_update_order_review_init which passes
		 * the parsed checkout form data (from $_POST['post_data']).
		 *
		 * @param array $ajax_data Parsed checkout form data.
		 */
		public function handle_checkout_initiated( $ajax_data = array() ) {
			$step_id = 0;

			// Get checkout ID from the hook's parsed form data.
			if ( ! empty( $ajax_data ) && isset( $ajax_data['_wcf_checkout_id'] ) ) {
				$step_id = absint( $ajax_data['_wcf_checkout_id'] );
			}

			// Fallback: try the utility method.
			if ( empty( $step_id ) && is_callable( array( wcf()->utils, 'get_checkout_id_from_post_data' ) ) ) {
				$step_id = absint( wcf()->utils->get_checkout_id_from_post_data() );
			}

			// Fallback: try the global function.
			if ( empty( $step_id ) && function_exists( '_get_wcf_checkout_id' ) ) {
				$step_id = absint( _get_wcf_checkout_id() );
			}

			if ( empty( $step_id ) ) {
				return;
			}

			$flow_id = get_post_meta( $step_id, 'wcf-flow-id', true );

			if ( empty( $flow_id ) ) {
				return;
			}

			$context = array(
				'funnel_id' => absint( $flow_id ),
				'step_id'   => absint( $step_id ),
			);

			wcf()->logger->log( 'Webhook event: checkout_initiated - Step ' . $step_id );

			Cartflows_Webhook_Dispatcher::get_instance()->dispatch( 'checkout_initiated', $context );
		}

		/**
		 * Handle order created event (from woocommerce_checkout_order_processed).
		 *
		 * @param int      $order_id Order ID.
		 * @param array    $posted_data Posted checkout data.
		 * @param WC_Order $order Order object.
		 */
		public function handle_order_created( $order_id, $posted_data, $order ) {
			if ( ! $order ) {
				return;
			}

			$flow_id = $order->get_meta( '_wcf_flow_id' );

			if ( empty( $flow_id ) ) {
				return;
			}

			$step_id = $order->get_meta( '_wcf_checkout_id' );

			$context = array(
				'funnel_id' => absint( $flow_id ),
				'step_id'   => absint( $step_id ),
				'order_id'  => absint( $order_id ),
				'order'     => $order,
			);

			wcf()->logger->log( 'Webhook event: order_created - Order ' . $order_id . ' Flow ' . $flow_id );

			Cartflows_Webhook_Dispatcher::get_instance()->dispatch( 'order_created', $context );
		}

		/**
		 * Handle order status changed event.
		 *
		 * Uses woocommerce_order_status_changed which is more reliable across
		 * WooCommerce versions and HPOS configurations. Fires for all status
		 * transitions; we filter for completed and failed.
		 *
		 * @param int      $order_id Order ID.
		 * @param string   $old_status Old order status.
		 * @param string   $new_status New order status.
		 * @param WC_Order $order Order object.
		 */
		public function handle_order_status_changed( $order_id, $old_status, $new_status, $order ) {
			$event_map = array(
				'completed'      => 'order_completed',
				'processing'     => 'order_completed',
				'wcf-main-order' => 'order_completed',
				'failed'         => 'order_failed',
			);

			if ( ! isset( $event_map[ $new_status ] ) ) {
				return;
			}

			$event = $event_map[ $new_status ];

			if ( ! $order || ! is_a( $order, 'WC_Order' ) ) {
				$order = wc_get_order( $order_id );
			}

			if ( ! $order ) {
				wcf()->logger->log( 'Webhook event: ' . $event . ' - Order ' . $order_id . ' not found, skipping.' );
				return;
			}

			$flow_id = $order->get_meta( '_wcf_flow_id' );

			// Force fresh DB read if meta appears empty (guards against stale cache).
			if ( empty( $flow_id ) ) {
				$order   = wc_get_order( $order_id );
				$flow_id = $order ? $order->get_meta( '_wcf_flow_id' ) : '';
			}

			if ( empty( $flow_id ) ) {
				return;
			}

			$step_id = $order->get_meta( '_wcf_checkout_id' );

			$context = array(
				'funnel_id' => absint( $flow_id ),
				'step_id'   => absint( $step_id ),
				'order_id'  => absint( $order_id ),
				'order'     => $order,
			);

			wcf()->logger->log( 'Webhook event: ' . $event . ' - Order ' . $order_id . ' Flow ' . $flow_id . ' (status: ' . $old_status . ' -> ' . $new_status . ')' );

			Cartflows_Webhook_Dispatcher::get_instance()->dispatch( $event, $context );
		}
	}

	Cartflows_Webhook_Events::get_instance();
}
