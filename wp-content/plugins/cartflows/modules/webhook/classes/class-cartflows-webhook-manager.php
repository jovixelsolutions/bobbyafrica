<?php
/**
 * CartFlows Webhook Manager.
 *
 * Handles CRUD operations for webhook configurations stored in wp_options.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Cartflows_Webhook_Manager' ) ) {

	/**
	 * Class Cartflows_Webhook_Manager.
	 */
	class Cartflows_Webhook_Manager {

		/**
		 * Option key for storing webhooks.
		 *
		 * @var string
		 */
		const OPTION_KEY = '_cartflows_webhooks';

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
		 * Get all webhooks.
		 *
		 * @return array
		 */
		public function get_webhooks() {
			$webhooks = get_option( self::OPTION_KEY, array() );
			return is_array( $webhooks ) ? $webhooks : array();
		}

		/**
		 * Get a single webhook by ID.
		 *
		 * @param string $id Webhook ID.
		 * @return array|false
		 */
		public function get_webhook( $id ) {
			$webhooks = $this->get_webhooks();

			foreach ( $webhooks as $webhook ) {
				if ( isset( $webhook['id'] ) && $webhook['id'] === $id ) {
					return $webhook;
				}
			}

			return false;
		}

		/**
		 * Add a new webhook.
		 *
		 * @param array $data Webhook data.
		 * @return array|false The created webhook or false on failure.
		 */
		public function add_webhook( $data ) {
			$url = isset( $data['url'] ) ? esc_url_raw( $data['url'] ) : '';

			if ( empty( $url ) || ! wp_http_validate_url( $url ) ) {
				return false;
			}

			$webhook = array(
				'id'            => wp_generate_uuid4(),
				'name'          => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '',
				'url'           => $url,
				'events'        => isset( $data['events'] ) && is_array( $data['events'] ) ? array_map( 'sanitize_text_field', $data['events'] ) : array(),
				'enabled'       => isset( $data['enabled'] ) ? (bool) $data['enabled'] : true,
				'secret'        => wp_generate_password( 32, false ),
				'max_retries'   => isset( $data['max_retries'] ) ? absint( min( $data['max_retries'], 5 ) ) : 3,
				'created_at'    => current_time( 'mysql' ),
				'updated_at'    => current_time( 'mysql' ),
				'last_delivery' => array(
					'status'       => '',
					'triggered_at' => '',
					'error'        => '',
				),
			);

			$webhooks   = $this->get_webhooks();
			$webhooks[] = $webhook;

			update_option( self::OPTION_KEY, $webhooks, false );

			wcf()->logger->log( 'Webhook created: ' . $webhook['id'] . ' - ' . $webhook['name'] );

			return $webhook;
		}

		/**
		 * Update an existing webhook.
		 *
		 * @param string $id Webhook ID.
		 * @param array  $data Updated data.
		 * @return array|false The updated webhook or false on failure.
		 */
		public function update_webhook( $id, $data ) {
			$webhooks = $this->get_webhooks();
			$updated  = false;

			foreach ( $webhooks as $index => $webhook ) {
				if ( isset( $webhook['id'] ) && $webhook['id'] === $id ) {

					if ( isset( $data['name'] ) ) {
						$webhooks[ $index ]['name'] = sanitize_text_field( $data['name'] );
					}

					if ( isset( $data['url'] ) ) {
						$url = esc_url_raw( $data['url'] );
						if ( ! empty( $url ) && wp_http_validate_url( $url ) ) {
							$webhooks[ $index ]['url'] = $url;
						}
					}

					if ( isset( $data['events'] ) && is_array( $data['events'] ) ) {
						$webhooks[ $index ]['events'] = array_map( 'sanitize_text_field', $data['events'] );
					}

					if ( isset( $data['enabled'] ) ) {
						$webhooks[ $index ]['enabled'] = (bool) $data['enabled'];
					}

					if ( isset( $data['max_retries'] ) ) {
						$webhooks[ $index ]['max_retries'] = absint( min( $data['max_retries'], 5 ) );
					}

					$webhooks[ $index ]['updated_at'] = current_time( 'mysql' );

					$updated = $webhooks[ $index ];
					break;
				}
			}

			if ( $updated ) {
				update_option( self::OPTION_KEY, $webhooks, false );
				wcf()->logger->log( 'Webhook updated: ' . $id );
			}

			return $updated;
		}

		/**
		 * Delete a webhook.
		 *
		 * @param string $id Webhook ID.
		 * @return bool
		 */
		public function delete_webhook( $id ) {
			$webhooks = $this->get_webhooks();
			$found    = false;

			foreach ( $webhooks as $index => $webhook ) {
				if ( isset( $webhook['id'] ) && $webhook['id'] === $id ) {
					unset( $webhooks[ $index ] );
					$found = true;
					break;
				}
			}

			if ( $found ) {
				update_option( self::OPTION_KEY, array_values( $webhooks ), false );
				wcf()->logger->log( 'Webhook deleted: ' . $id );
				return true;
			}

			return false;
		}

		/**
		 * Get all enabled webhooks for a specific event.
		 *
		 * @param string $event Event name.
		 * @return array
		 */
		public function get_webhooks_for_event( $event ) {
			$webhooks = $this->get_webhooks();
			$matched  = array();

			foreach ( $webhooks as $webhook ) {
				if ( ! empty( $webhook['enabled'] ) && isset( $webhook['events'] ) && in_array( $event, $webhook['events'], true ) ) {
					$matched[] = $webhook;
				}
			}

			return $matched;
		}

		/**
		 * Update last delivery status for a webhook.
		 *
		 * @param string $id Webhook ID.
		 * @param array  $delivery_data Delivery status data.
		 * @return bool
		 */
		public function update_last_delivery( $id, $delivery_data ) {
			$webhooks = $this->get_webhooks();

			foreach ( $webhooks as $index => $webhook ) {
				if ( isset( $webhook['id'] ) && $webhook['id'] === $id ) {
					$webhooks[ $index ]['last_delivery'] = array(
						'status'       => isset( $delivery_data['status'] ) ? sanitize_text_field( $delivery_data['status'] ) : '',
						'triggered_at' => isset( $delivery_data['triggered_at'] ) ? sanitize_text_field( $delivery_data['triggered_at'] ) : current_time( 'mysql' ),
						'error'        => isset( $delivery_data['error'] ) ? sanitize_text_field( $delivery_data['error'] ) : '',
					);

					update_option( self::OPTION_KEY, $webhooks, false );
					return true;
				}
			}

			return false;
		}
	}
}
