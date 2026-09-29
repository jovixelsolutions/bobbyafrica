<?php
/**
 * Cart Abandonment Recovery Email Templates AJAX Handler.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Admin\Ajax;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Email_Templates.
 */
class Email_Templates extends Ajax_Base {
	
	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 2.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register ajax events.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function register_ajax_events(): void {
		if ( current_user_can( 'manage_woocommerce' ) ) {
			$ajax_events = [
				'save_email_template',
				'update_email_template',
				'update_email_template_status',
				'delete_email_template',
				'clone_email_template',
				'search_products',
				'restore_email_templates',
				'send_preview_email',
			];
			$this->init_ajax_events( $ajax_events );
		}
	}

	/**
	 * Save email template.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function save_email_template(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'wcar_save_email_template', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}
		
		// Get email templates instance.
		$email_templates = \Cartflows_Ca_Email_Templates::get_instance();

		// Save template.
		$template_id = $this->add_email_template();
		
		if ( $template_id ) {
			$template      = (array) $email_templates->get_email_template_by_id( $template_id );
			$meta          = $email_templates->get_all_email_template_meta( $template_id );
			$response_data = [
				'success'     => true,
				'message'     => __( 'Email template saved successfully.', 'woo-cart-abandonment-recovery' ),
				'template_id' => $template_id,
				'template'    => array_merge(
					[
						'id'                   => $template['id'],
						'template_name'        => $template['template_name'],
						'email_subject'        => $template['email_subject'],
						'email_body'           => $template['email_body'],
						'is_activated'         => boolval( $template['is_activated'] ) ? 'on' : '',
						'email_frequency'      => $template['frequency'],
						'email_frequency_unit' => $template['frequency_unit'],
					],
					$meta
				),
			];
			wp_send_json_success( $response_data );
		} else {
			$response_data = [ 'message' => __( 'Failed to save email template.', 'woo-cart-abandonment-recovery' ) ];
			wp_send_json_error( $response_data );
		}
	}
	/**
	 * Add a new email template to the database.
	 *
	 * This function inserts a new email template into the 'cartflows_ca_email_templates' table
	 * and saves associated meta data into the 'cartflows_ca_email_templates_meta' table.
	 *
	 * @since 2.0.0
	 *
	 * @return int The ID of the newly created email template.
	 */
	public function add_email_template() {
		// Use centralized email template sanitization.
		$sanitized_post = wcf_ca()->options->sanitize_email_template_data();
		
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'cartflows_ca_email_templates',
			array(
				'template_name'  => $sanitized_post['wcf_template_name'],
				'email_subject'  => $sanitized_post['wcf_email_subject'],
				'email_body'     => $sanitized_post['wcf_email_body'],
				'frequency'      => $sanitized_post['wcf_email_frequency'],
				'frequency_unit' => $sanitized_post['wcf_email_frequency_unit'],
				'is_activated'   => $sanitized_post['wcf_activate_email_template'],
			),
			array( '%s', '%s', '%s', '%d', '%s', '%d' )
		); // db call ok; no cache ok.

		$email_template_id = $wpdb->insert_id;
		
		// Handle meta fields dynamically.
		$meta_fields = wcf_ca()->options->get_email_template_meta_fields();

		foreach ( $meta_fields as $meta_key ) {
			$post_key = 'wcf_' . $meta_key;
			if ( isset( $sanitized_post[ $post_key ] ) ) {
				$this->add_email_template_meta( $email_template_id, $meta_key, $sanitized_post[ $post_key ] );
			}
		}

		// Return the ID if requested.
		return $email_template_id;
	}

	/**
	 * Update an existing email template.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function update_email_template(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		if ( ! check_ajax_referer( 'wcar_update_email_template', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		// Use centralized email template sanitization.
		$sanitized_post = wcf_ca()->options->sanitize_email_template_data();
		$template_id    = intval( $sanitized_post['id'] );

		if ( ! $template_id ) {
			$response_data = [ 'message' => __( 'Invalid template ID.', 'woo-cart-abandonment-recovery' ) ];
			wp_send_json_error( $response_data );
		}

		global $wpdb;
		$updated = $wpdb->update(
			$wpdb->prefix . 'cartflows_ca_email_templates',
			array(
				'template_name'  => $sanitized_post['wcf_template_name'],
				'email_subject'  => $sanitized_post['wcf_email_subject'],
				'email_body'     => $sanitized_post['wcf_email_body'],
				'frequency'      => $sanitized_post['wcf_email_frequency'],
				'frequency_unit' => $sanitized_post['wcf_email_frequency_unit'],
				'is_activated'   => $sanitized_post['wcf_activate_email_template'],
			),
			array( 'id' => $template_id ),
			array( '%s', '%s', '%s', '%d', '%s', '%d' ),
			array( '%d' )
		);

		if ( false !== $updated ) {
			// Handle meta fields dynamically.
			$meta_fields = wcf_ca()->options->get_email_template_meta_fields();

			foreach ( $meta_fields as $meta_key ) {
				$post_key = 'wcf_' . $meta_key;
				if ( isset( $sanitized_post[ $post_key ] ) ) {
					$this->update_email_template_meta( $template_id, $meta_key, $sanitized_post[ $post_key ] );
				}
			}

			$email_templates = \Cartflows_Ca_Email_Templates::get_instance();
			$template        = (array) $email_templates->get_email_template_by_id( $template_id );
			$meta            = $email_templates->get_all_email_template_meta( $template_id );

			$response_data = [
				'success'  => true,
				'message'  => __( 'Email template updated successfully.', 'woo-cart-abandonment-recovery' ),
				'template' => array_merge(
					[
						'id'                   => $template['id'],
						'template_name'        => $template['template_name'],
						'email_subject'        => $template['email_subject'],
						'email_body'           => $template['email_body'],
						'is_activated'         => boolval( $template['is_activated'] ) ? 'on' : '',
						'email_frequency'      => $template['frequency'],
						'email_frequency_unit' => $template['frequency_unit'],
					],
					$meta
				),
			];
			wp_send_json_success( $response_data );
		} else {
			$response_data = [ 'message' => __( 'Failed to update email template.', 'woo-cart-abandonment-recovery' ) ];
			wp_send_json_error( $response_data );
		}
	}

	/**
	 * Update email template meta.
	 *
	 * @param integer $email_template_id email template id.
	 * @param string  $meta_key meta key.
	 * @param string  $meta_value meta value.
	 */
	public function update_email_template_meta( $email_template_id, $meta_key, $meta_value ) {
		global $wpdb;
		$email_templates = \Cartflows_Ca_Email_Templates::get_instance();
		// Check if the meta key already exists for this template.
		$existing_meta = $email_templates->get_email_template_meta_by_key( $email_template_id, $meta_key );

		if ( $existing_meta ) {
			// Update existing meta.
			$cart_abandonment_template_meta_table_name = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$cart_abandonment_template_meta_table_name} SET meta_value = %s WHERE email_template_id = %d AND meta_key = %s", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$meta_value,
					$email_template_id,
					sanitize_text_field( $meta_key )
				)
			); // db call ok; no cache ok.
		} else {
			// Insert new meta.
			$this->add_email_template_meta( $email_template_id, $meta_key, $meta_value );
		}
	}

	/**
	 * Sanitize email post data.
	 *
	 * @param string $nonce_action The nonce action to verify.
	 * @return array
	 */
	public function sanitize_email_post_data( $nonce_action = 'wcar_save_email_template' ) {
		
		/**
		 * Re-verify the Nonce just in case, if this function is called directly.
		 */
		if ( ! check_ajax_referer( $nonce_action, 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		$input_post_values = array(
			'wcf_email_subject'           => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'wcf_email_body'              => array(
				'default'  => '',
				'sanitize' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			),
			'wcf_template_name'           => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'wcf_email_frequency'         => array(
				'default'  => 30,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),
			'wcf_email_frequency_unit'    => array(
				'default'  => 'MINUTE',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'wcf_activate_email_template' => array(
				'default'  => 0,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),

			'wcf_discount_type'           => array(
				'default'  => 'percent',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'wcf_coupon_amount'           => array(
				'default'  => 10,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),
			'wcf_coupon_expiry_date'      => array(
				'default'  => '',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'wcf_coupon_expiry_unit'      => array(
				'default'  => 'hours',
				'sanitize' => 'FILTER_SANITIZE_STRING',
			),
			'id'                          => array(
				'default'  => null,
				'sanitize' => FILTER_SANITIZE_NUMBER_INT,
			),
		);

		$sanitized_post = array();
		foreach ( $input_post_values as $key => $input_post_value ) {

			if ( isset( $_POST[ $key ] ) ) {
				if ( 'FILTER_SANITIZE_STRING' === $input_post_value['sanitize'] ) {
					$sanitized_post[ $key ] = \Cartflows_Ca_Helper::get_instance()->sanitize_text_filter( $key, 'POST' );
				} else {
					$sanitized_post[ $key ] = filter_input( INPUT_POST, $key, $input_post_value['sanitize'] );
				}
			} else {
				$sanitized_post[ $key ] = $input_post_value['default'];
			}
		}

		$sanitized_post['wcf_override_global_coupon'] = isset( $_POST['wcf_override_global_coupon'] ) ? sanitize_text_field( $_POST['wcf_override_global_coupon'] ) : '';
		$sanitized_post['wcf_auto_coupon']            = isset( $_POST['wcf_auto_coupon'] ) ? sanitize_text_field( $_POST['wcf_auto_coupon'] ) : '';
		$sanitized_post['wcf_free_shipping_coupon']   = isset( $_POST['wcf_free_shipping_coupon'] ) ? sanitize_text_field( $_POST['wcf_free_shipping_coupon'] ) : '';
		$sanitized_post['wcf_individual_use_only']    = isset( $_POST['wcf_individual_use_only'] ) ? sanitize_text_field( $_POST['wcf_individual_use_only'] ) : '';
		$sanitized_post['wcf_email_body']             = html_entity_decode( $sanitized_post['wcf_email_body'], ENT_COMPAT, 'UTF-8' );
		$sanitized_post['wcf_email_body']             = preg_replace( '#<script\b[^>]*>[\s\S]*?<\/script>#i', '', $sanitized_post['wcf_email_body'] );
		$sanitized_post['wcf_email_body']             = preg_replace( '#\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)#i', '', $sanitized_post['wcf_email_body'] );
		$sanitized_post['wcf_email_body']             = preg_replace( '#(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1=$2$2', $sanitized_post['wcf_email_body'] );
		$sanitized_post['wcf_use_woo_email_style']    = isset( $_POST['wcf_use_woo_email_style'] ) ? sanitize_text_field( $_POST['wcf_use_woo_email_style'] ) : '';

		return $sanitized_post;

	}

	/**
	 * Add the meta values.
	 *
	 * @param integer $email_template_id email template id.
	 * @param string  $meta_key meta key.
	 * @param string  $meta_value meta value.
	 */
	public function add_email_template_meta( $email_template_id, $meta_key, $meta_value ) {
		global $wpdb;
		$cart_abandonment_template_meta_table_name = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_META_TABLE;
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$cart_abandonment_template_meta_table_name} (email_template_id, meta_key, meta_value) VALUES (%d, %s, %s)", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$email_template_id,
				sanitize_text_field( $meta_key ),
				sanitize_text_field( $meta_value )
			)
		); // db call ok; no cache ok.
	}

	/**
	 * Update template status.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function update_email_template_status() {
		
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		// Check nonce and permissions.
		if ( ! check_ajax_referer( 'wcar_update_email_template_status', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		$template_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT ) ?? 0;
		$is_active   = \Cartflows_Ca_Helper::get_instance()->sanitize_text_filter( 'state', 'POST' );

		if ( ! $template_id ) {
			wp_send_json_error( [ 'message' => 'Invalid template ID' ] );
		}

		global $wpdb;
		
		$cart_abandonment_template_table_name = $wpdb->prefix . CARTFLOWS_CA_EMAIL_TEMPLATE_TABLE;

		// Can't use placeholders for table/column names, it will be wrapped by a single quote (') instead of a backquote (`).
		$updated = $wpdb->query(
			$wpdb->prepare( "UPDATE {$cart_abandonment_template_table_name} SET is_activated = %d WHERE id = %d ", $is_active, absint( $template_id ) ) //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		); // db call ok; no cache ok.


		if ( false === $updated ) {
			wp_send_json_error( [ 'message' => 'Failed to update status' ] );
		}

		wp_send_json_success(
			[
				'id'        => $template_id,
				'is_active' => $is_active,
			] 
		);
	}

	/**
	 * Delete email template via AJAX.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function delete_email_template(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		// Check nonce and permissions.
		if ( ! check_ajax_referer( 'wcar_delete_email_template', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		global $wpdb;
		$template_table = $wpdb->prefix . 'cartflows_ca_email_templates';
		$meta_table     = $wpdb->prefix . 'cartflows_ca_email_templates_meta';

		// The ids are sanitized later in the scope.
		$ids = isset( $_POST['ids'] ) ? (array) $_POST['ids'] : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $ids ) ) {
			// Fallback to single ID for backward compatibility.
			$single_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );
			if ( $single_id ) {
				$ids = [ $single_id ];
			}
		}
		$ids = array_filter( array_map( 'intval', $ids ) );
		if ( empty( $ids ) ) {
			wp_send_json_error( [ 'message' => 'Invalid template ID(s)' ] );
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// Delete from templates table.
		$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$wpdb->prepare(
				"DELETE FROM {$template_table} WHERE id IN ($placeholders)", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				...$ids
			)
		);
		// Delete from meta table.
		$wpdb->query( // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$wpdb->prepare(
				"DELETE FROM {$meta_table} WHERE email_template_id IN ($placeholders)", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				...$ids
			)
		);

		if ( $deleted ) {
			wp_send_json_success( [ 'ids' => $ids ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to delete template(s).', 'woo-cart-abandonment-recovery' ) ] );
		}
	}

	/**
	 * Clone an email template via AJAX.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function clone_email_template(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		if ( ! check_ajax_referer( 'wcar_clone_email_template', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		$template_id = filter_input( INPUT_POST, 'id', FILTER_VALIDATE_INT );

		if ( ! $template_id ) {
			wp_send_json_error( [ 'message' => __( 'Invalid template ID', 'woo-cart-abandonment-recovery' ) ] );
		}

		$email_templates = \Cartflows_Ca_Email_Templates::get_instance();
		$template        = $email_templates->get_template_by_id( $template_id );
		$meta_data       = $email_templates->get_all_email_template_meta( $template_id );

		if ( empty( $template ) ) {
			wp_send_json_error( [ 'message' => __( 'Template not found', 'woo-cart-abandonment-recovery' ) ] );
		}

		global $wpdb;

		$inserted = $wpdb->insert(
			$wpdb->prefix . 'cartflows_ca_email_templates',
			[
				'template_name'  => sanitize_text_field( $template->template_name ) . __( ' - clone', 'woo-cart-abandonment-recovery' ),
				'email_subject'  => sanitize_text_field( $template->email_subject ),
				'email_body'     => $template->email_body,
				'frequency'      => intval( $template->frequency ),
				'frequency_unit' => sanitize_text_field( $template->frequency_unit ),
				'is_activated'   => $template->is_activated,
			],
			[ '%s', '%s', '%s', '%d', '%s', '%d' ]
		);

		if ( ! $inserted ) {
			wp_send_json_error( [ 'message' => __( 'Failed to clone template', 'woo-cart-abandonment-recovery' ) ] );
		}

		$new_id = $wpdb->insert_id;

		foreach ( $meta_data as $meta_key => $meta_value ) {
			$this->add_email_template_meta( $new_id, $meta_key, $meta_value );
		}

		$new_template = (array) $email_templates->get_email_template_by_id( $new_id );
		$meta         = $email_templates->get_all_email_template_meta( $new_id );

		wp_send_json_success(
			[
				'message'  => __( 'Template cloned successfully', 'woo-cart-abandonment-recovery' ),
				'template' => array_merge(
					[
						'id'                   => $new_template['id'],
						'template_name'        => $new_template['template_name'],
						'email_subject'        => $new_template['email_subject'],
						'email_body'           => $new_template['email_body'],
						'is_activated'         => boolval( $new_template['is_activated'] ) ? 'on' : '',
						'email_frequency'      => $new_template['frequency'],
						'email_frequency_unit' => $new_template['frequency_unit'],
					],
					$meta
				),
			]
		);
	}

	/**
	 * Get all meta key-value pairs for a template, filling missing keys with defaults.
	 *
	 * @param int $email_template_id The email template ID.
	 * @return array
	 */
	public function get_all_email_template_meta( $email_template_id ) {
		global $wpdb;
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->prefix}cartflows_ca_email_templates_meta WHERE email_template_id = %d",
				$email_template_id
			),
			ARRAY_A
		);
		$meta    = [];
		foreach ( $results as $row ) {
			$meta[ $row['meta_key'] ] = maybe_unserialize( $row['meta_value'] );
		}
		// Fill missing keys with defaults.
		$defaults = function_exists( 'wcf_ca' ) ? wcf_ca()->options->get_email_template_meta_defaults() : [];
		return array_merge( $defaults, $meta );
	}

	/**
	 * AJAX: Search WooCommerce products for ProductSearchField.
	 * Handles both search by term and get products by comma-separated IDs.
	 */
	public function search_products() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( [ 'message' => $this->get_error_msg( 'permission' ) ] );
		}

		$nonce = isset( $_POST['security'] ) ? sanitize_text_field( $_POST['security'] ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wcar_search_products' ) ) {
			wp_send_json_error( [ 'message' => $this->get_error_msg( 'nonce' ) ] );
		}
		
		$term           = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
		$product_ids    = isset( $_POST['product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['product_ids'] ) ) : '';
		$posts_per_page = isset( $_POST['posts_per_page'] ) ? absint( wp_unslash( $_POST['posts_per_page'] ) ) : 20;
		
		$products = [];
		
		if ( ! empty( $term ) && strlen( $term ) >= 3 ) {
			// Use WooCommerce's search method to search the products.
			$data = \WC_Data_Store::load( 'product' );
			$ids  = $data->search_products( $term, '', true, false, $posts_per_page );
			
			// Get all product objects including variations.
			$product_objects = array_filter( array_map( 'wc_get_product', $ids ), 'wc_products_array_filter_readable' );
			
			foreach ( $product_objects as $product_object ) {
				if ( ! $product_object ) {
					continue;
				}
				
				$products[] = $this->format_product_data( $product_object );
			}
		} elseif ( ! empty( $product_ids ) ) {
			// Get products by IDs.
			$ids_array = array_filter( array_map( 'intval', explode( ',', $product_ids ) ) );
			if ( ! empty( $ids_array ) ) {
				foreach ( $ids_array as $product_id ) {
					$product_object = wc_get_product( $product_id );
					if ( $product_object ) {
						$products[] = $this->format_product_data( $product_object );
					}
				}
			}
		}
		
		wp_send_json_success( $products );
	}

	/**
	 * Format product data for response.
	 *
	 * @param WC_Product $product_object Product object.
	 * @return array Formatted product data.
	 */
	private function format_product_data( $product_object ) {
		$product_id = $product_object->get_id();
		$image_id   = get_post_thumbnail_id( $product_id );
		$image_url  = $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
		
		$managing_stock = $product_object->managing_stock();
		$is_in_stock    = $product_object->is_in_stock();
		$product_type   = $product_object->get_type();
		
		$availibility_text   = $this->get_stock_availability_text( $product_object );
		$product_price_range = $this->get_formatted_product_price_range( $product_object );
		
		// Format name for variations.
		$formatted_name = $product_object->get_name();

		// phpcs:disable
		/*
		 if ( $product_type === 'variation' ) {
			 $formatted_name = $formatted_name . ' - ' . __( 'Variation', 'woo-cart-abandonment-recovery' );
		} */
		// phpcs:enable
		
		return [
			'value'          => (string) $product_id,
			'label'          => $formatted_name,
			'image'          => $image_url,
			'original_price' => $product_object->get_price(),
			'product_name'   => $product_object->get_name(),
			'product_type'   => $product_type,
			'stock_status'   => $availibility_text,
			'in_stock'       => $is_in_stock,
			'price_range'    => $product_price_range,
		];
	}

	/**
	 * Get stock availability text for product.
	 *
	 * @param WC_Product $product_object Product object.
	 * @return string Stock availability text.
	 */
	private function get_stock_availability_text( $product_object ) {
		if ( ! $product_object->is_in_stock() ) {
			return __( 'Out of stock', 'woo-cart-abandonment-recovery' );
		}
		
		if ( $product_object->managing_stock() ) {
			$stock_quantity = $product_object->get_stock_quantity();
			if ( $stock_quantity <= 0 ) {
				return __( 'Out of stock', 'woo-cart-abandonment-recovery' );
			}
			return sprintf( 
				// translators: %d: Number of items in stock.
				__( '%d in stock', 'woo-cart-abandonment-recovery' ), 
				$stock_quantity
			);
		}
		
		return __( 'In stock', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * Get formatted product price range.
	 *
	 * @param WC_Product $product_object Product object.
	 * @return string Formatted price range.
	 */
	private function get_formatted_product_price_range( $product_object ) {
		if ( $product_object->is_type( 'variable' ) ) {
			$prices = $product_object->get_variation_prices( true );
			if ( ! empty( $prices['price'] ) ) {
				$min_price = current( $prices['price'] );
				$max_price = end( $prices['price'] );
				if ( $min_price !== $max_price ) {
					return wc_format_price_range( $min_price, $max_price );
				}
			}
		}
		
		return $product_object->get_price_html();
	}

	/**
	 * Restore default email templates via AJAX.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function restore_email_templates(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( ! check_ajax_referer( 'wcar_restore_email_templates', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		include_once CARTFLOWS_CA_DIR . 'modules/cart-abandonment/classes/class-cartflows-ca-database.php';
		$db = \Cartflows_Ca_Database::get_instance();
		$db->template_table_seeder( true );

		global $wpdb;
		$templates           = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}cartflows_ca_email_templates ORDER BY id ASC" );
		$email_templates     = \Cartflows_Ca_Email_Templates::get_instance();
		$formatted_templates = [];

		foreach ( $templates as $template ) {
			$meta                  = $email_templates->get_all_email_template_meta( $template->id );
			$formatted_templates[] = array_merge(
				[
					'id'                   => $template->id,
					'template_name'        => $template->template_name,
					'email_subject'        => $template->email_subject,
					'email_body'           => $template->email_body,
					'is_activated'         => boolval( $template->is_activated ) ? 'on' : '',
					'email_frequency'      => $template->frequency,
					'email_frequency_unit' => $template->frequency_unit,
				],
				$meta
			);
		}

		wp_send_json_success(
			[
				'message'   => __( 'Default email templates restored successfully.', 'woo-cart-abandonment-recovery' ),
				'templates' => $formatted_templates,
			] 
		);
	}

	/**
	 * Send preview email.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function send_preview_email(): void {
		$response_data = [ 'message' => $this->get_error_msg( 'permission' ) ];

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( $response_data );
		}

		if ( empty( $_POST ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'missing-data' ) ];
			wp_send_json_error( $response_data );
		}

		/**
		 * Nonce verification
		 */
		if ( ! check_ajax_referer( 'wcar_send_preview_email', 'security', false ) ) {
			$response_data = [ 'message' => $this->get_error_msg( 'nonce' ) ];
			wp_send_json_error( $response_data );
		}

		$email_schedule = \Cartflows_Ca_Email_Schedule::get_instance();
		$mail_result    = $email_schedule->send_email_templates( null, true );

		if ( $mail_result ) {
			wp_send_json_success( [ 'message' => __( 'Test email sent successfully!', 'woo-cart-abandonment-recovery' ) ] );
		} else {
			wp_send_json_error( [ 'message' => __( 'Failed to send test email', 'woo-cart-abandonment-recovery' ) ] );
		}
	}
}
