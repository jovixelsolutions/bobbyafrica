<?php
/**
 * CartFlows Webhook Dispatcher.
 *
 * Core delivery engine. Schedules async webhook delivery via Action Scheduler.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Webhook_Dispatcher' ) ) {

	/**
	 * Class Cartflows_Webhook_Dispatcher.
	 */
	class Cartflows_Webhook_Dispatcher {

		/**
		 * Action Scheduler hook name.
		 *
		 * @var string
		 */
		const AS_HOOK = 'cartflows_process_webhook_delivery';

		/**
		 * Retry backoff intervals in seconds.
		 *
		 * @var array
		 */
		const RETRY_INTERVALS = array( 60, 300, 1800, 3600, 7200 );

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
			add_action( self::AS_HOOK, array( $this, 'process_webhook_delivery' ), 10, 4 );
		}

		/**
		 * Dispatch webhooks for an event.
		 *
		 * Finds all enabled webhooks for the event and schedules Action Scheduler jobs.
		 *
		 * @param string $event Event name.
		 * @param array  $context Event context data.
		 * @return void
		 */
		public function dispatch( $event, $context = array() ) {
			$manager  = Cartflows_Webhook_Manager::get_instance();
			$webhooks = $manager->get_webhooks_for_event( $event );

			if ( empty( $webhooks ) ) {
				wcf()->logger->log( 'Webhook dispatch: No webhooks found for event ' . $event );
				return;
			}

			$object_id = isset( $context['order_id'] ) ? $context['order_id'] : ( isset( $context['step_id'] ) ? $context['step_id'] : 0 );

			foreach ( $webhooks as $webhook ) {
				// Deduplication check.
				$dedup_key = 'wcf_wh_' . md5( $webhook['id'] . '_' . $event . '_' . $object_id );

				if ( get_transient( $dedup_key ) ) {
					wcf()->logger->log( 'Webhook dispatch: Skipping duplicate for webhook ' . $webhook['id'] . ' event ' . $event );
					continue;
				}

				// Set dedup transient for 60 seconds.
				set_transient( $dedup_key, 1, 60 );

				$payload_builder = Cartflows_Webhook_Payload::get_instance();
				$payload         = $payload_builder->build( $event, $context, $webhook['id'] );
				$body            = wp_json_encode( $payload );

				wcf()->logger->log( 'Webhook dispatch: Scheduling delivery for webhook ' . $webhook['id'] . ' event ' . $event );

				// Schedule async delivery via Action Scheduler.
				if ( function_exists( 'as_schedule_single_action' ) ) {
					as_schedule_single_action(
						time(),
						self::AS_HOOK,
						array(
							'webhook_id'    => $webhook['id'],
							'body'          => $body,
							'event'         => $event,
							'retry_attempt' => 0,
						),
						'cartflows-webhooks'
					);
				}
			}
		}

		/**
		 * Process a webhook delivery (called by Action Scheduler).
		 *
		 * @param string $webhook_id Webhook ID.
		 * @param string $body JSON payload body.
		 * @param string $event Event name.
		 * @param int    $retry_attempt Current retry attempt.
		 * @return void
		 */
		public function process_webhook_delivery( $webhook_id, $body, $event, $retry_attempt = 0 ) {
			$manager = Cartflows_Webhook_Manager::get_instance();
			$webhook = $manager->get_webhook( $webhook_id );

			if ( ! $webhook ) {
				wcf()->logger->log( 'Webhook delivery: Webhook not found - ' . $webhook_id );
				return;
			}

			if ( empty( $webhook['enabled'] ) ) {
				wcf()->logger->log( 'Webhook delivery: Webhook disabled, skipping - ' . $webhook_id );
				return;
			}

			$url    = $webhook['url'];
			$secret = isset( $webhook['secret'] ) ? $webhook['secret'] : '';

			// Generate HMAC signature.
			$signature = hash_hmac( 'sha256', $body, $secret );

			$headers = array(
				'Content-Type'                  => 'application/json',
				'X-CartFlows-Webhook-Signature' => $signature,
				'X-CartFlows-Event'             => $event,
				'X-CartFlows-Delivery-ID'       => wp_generate_uuid4(),
				'User-Agent'                    => 'CartFlows-Webhook/1.0',
			);

			wcf()->logger->log( 'Webhook delivery: Sending to ' . $url . ' for event ' . $event . ' (attempt ' . ( $retry_attempt + 1 ) . ')' );

			$response = wp_safe_remote_post(
				$url,
				array(
					'body'      => $body,
					'headers'   => $headers,
					'timeout'   => 3,
					'sslverify' => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				wcf()->logger->log( 'Webhook delivery failed: ' . $error_message . ' - Webhook: ' . $webhook_id );

				$manager->update_last_delivery(
					$webhook_id,
					array(
						'status'       => 'failed',
						'triggered_at' => current_time( 'mysql' ),
						'error'        => $error_message,
					)
				);

				$this->maybe_schedule_retry( $webhook_id, $body, $event, $retry_attempt, $webhook );
				return;
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			if ( $response_code >= 200 && $response_code < 300 ) {
				wcf()->logger->log( 'Webhook delivery success: HTTP ' . $response_code . ' - Webhook: ' . $webhook_id );

				$manager->update_last_delivery(
					$webhook_id,
					array(
						'status'       => 'success',
						'triggered_at' => current_time( 'mysql' ),
						'error'        => '',
					)
				);
			} else {
				$error_message = 'HTTP ' . $response_code . ': ' . wp_remote_retrieve_response_message( $response );
				wcf()->logger->log( 'Webhook delivery failed: ' . $error_message . ' - Webhook: ' . $webhook_id );

				$manager->update_last_delivery(
					$webhook_id,
					array(
						'status'       => 'failed',
						'triggered_at' => current_time( 'mysql' ),
						'error'        => $error_message,
					)
				);

				$this->maybe_schedule_retry( $webhook_id, $body, $event, $retry_attempt, $webhook );
			}
		}

		/**
		 * Schedule a retry if within max retries.
		 *
		 * @param string $webhook_id Webhook ID.
		 * @param string $body JSON payload body.
		 * @param string $event Event name.
		 * @param int    $retry_attempt Current retry attempt.
		 * @param array  $webhook Webhook configuration.
		 * @return void
		 */
		private function maybe_schedule_retry( $webhook_id, $body, $event, $retry_attempt, $webhook ) {
			$max_retries  = isset( $webhook['max_retries'] ) ? absint( $webhook['max_retries'] ) : 3;
			$next_attempt = $retry_attempt + 1;

			if ( $next_attempt >= $max_retries ) {
				wcf()->logger->log( 'Webhook delivery: Max retries reached (' . $max_retries . ') for webhook ' . $webhook_id . '. Giving up.' );
				return;
			}

			$delay = isset( self::RETRY_INTERVALS[ $retry_attempt ] ) ? self::RETRY_INTERVALS[ $retry_attempt ] : 7200;

			wcf()->logger->log( 'Webhook delivery: Scheduling retry ' . $next_attempt . ' in ' . $delay . 's for webhook ' . $webhook_id );

			if ( function_exists( 'as_schedule_single_action' ) ) {
				as_schedule_single_action(
					time() + $delay,
					self::AS_HOOK,
					array(
						'webhook_id'    => $webhook_id,
						'body'          => $body,
						'event'         => $event,
						'retry_attempt' => $next_attempt,
					),
					'cartflows-webhooks'
				);
			}
		}

		/**
		 * Send a test webhook synchronously.
		 *
		 * @param string $webhook_id Webhook ID.
		 * @return array Result with 'success', 'response_code', and 'error' keys.
		 */
		public function send_test( $webhook_id ) {
			$manager = Cartflows_Webhook_Manager::get_instance();
			$webhook = $manager->get_webhook( $webhook_id );

			if ( ! $webhook ) {
				return array(
					'success'       => false,
					'response_code' => 0,
					'error'         => __( 'Webhook not found.', 'cartflows' ),
				);
			}

			$payload_builder = Cartflows_Webhook_Payload::get_instance();
			$payload         = $payload_builder->build_test( $webhook_id );
			$body            = wp_json_encode( $payload );
			$secret          = isset( $webhook['secret'] ) ? $webhook['secret'] : '';
			$signature       = hash_hmac( 'sha256', $body, $secret );

			$headers = array(
				'Content-Type'                  => 'application/json',
				'X-CartFlows-Webhook-Signature' => $signature,
				'X-CartFlows-Event'             => 'test',
				'X-CartFlows-Delivery-ID'       => wp_generate_uuid4(),
				'User-Agent'                    => 'CartFlows-Webhook/1.0',
			);

			wcf()->logger->log( 'Webhook test: Sending test to ' . $webhook['url'] );

			$response = wp_safe_remote_post(
				$webhook['url'],
				array(
					'body'      => $body,
					'headers'   => $headers,
					'timeout'   => 3,
					'sslverify' => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();
				wcf()->logger->log( 'Webhook test failed: ' . $error_message );

				$manager->update_last_delivery(
					$webhook_id,
					array(
						'status'       => 'failed',
						'triggered_at' => current_time( 'mysql' ),
						'error'        => $error_message,
					)
				);

				return array(
					'success'       => false,
					'response_code' => 0,
					'error'         => $error_message,
				);
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			$manager->update_last_delivery(
				$webhook_id,
				array(
					'status'       => ( $response_code >= 200 && $response_code < 300 ) ? 'success' : 'failed',
					'triggered_at' => current_time( 'mysql' ),
					'error'        => ( $response_code >= 200 && $response_code < 300 ) ? '' : 'HTTP ' . $response_code,
				)
			);

			wcf()->logger->log( 'Webhook test result: HTTP ' . $response_code );

			return array(
				'success'       => ( $response_code >= 200 && $response_code < 300 ),
				'response_code' => $response_code,
				'error'         => ( $response_code >= 200 && $response_code < 300 ) ? '' : 'HTTP ' . $response_code,
			);
		}
	}
}
