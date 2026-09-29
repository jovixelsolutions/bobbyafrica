<?php
/**
 * Checkout Ajax.
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Global Checkout
 *
 * @since 1.0.0
 */
class Cartflows_Checkout_Ajax {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *  Constructor
	 */
	public function __construct() {

		/* Ajax Endpoint */
		add_filter( 'woocommerce_ajax_get_endpoint', array( $this, 'get_ajax_endpoint' ), 10, 2 );

		add_action( 'wp_ajax_wcf_woo_apply_coupon', array( $this, 'apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_apply_coupon', array( $this, 'apply_coupon' ) );

		add_action( 'wp_ajax_wcf_woo_remove_coupon', array( $this, 'remove_coupon' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_remove_coupon', array( $this, 'remove_coupon' ) );

		add_action( 'wp_ajax_wcf_woo_remove_cart_product', array( $this, 'wcf_woo_remove_cart_product' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_remove_cart_product', array( $this, 'wcf_woo_remove_cart_product' ) );

		add_action( 'wp_ajax_nopriv_wcf_check_email_exists', array( $this, 'check_email_exists' ) );
		add_action( 'wp_ajax_nopriv_wcf_woocommerce_login', array( $this, 'woocommerce_user_login' ) );

		add_action( 'wp_ajax_wcf_upload_checkout_file', array( $this, 'upload_checkout_file' ) );
		add_action( 'wp_ajax_nopriv_wcf_upload_checkout_file', array( $this, 'upload_checkout_file' ) );
	}

	/**
	 * Get ajax end points.
	 *
	 * @param string $endpoint_url end point URL.
	 * @param string $request end point request.
	 * @return string
	 */
	public function get_ajax_endpoint( $endpoint_url, $request ) {
		global $post;

		if ( ! empty( $post ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {

			if ( _is_wcf_checkout_type() ) {

				$query_args = array();
				$url        = $endpoint_url;

				if ( mb_strpos( $endpoint_url, 'checkout', 0, 'utf-8' ) === false ) {

					if ( '' === $request ) {
						$query_args = array(
							'wc-ajax' => '%%endpoint%%',
						);
					} else {
						$query_args = array(
							'wc-ajax' => $request,
						);
					}

					$uri = explode( '?', esc_url_raw( $_SERVER['REQUEST_URI'] ), 2 );
					$url = esc_url( $uri[0] );
				}

				$query_args['wcf_checkout_id'] = $post->ID;

				$endpoint_url = add_query_arg( $query_args, $url );
			}
		}

		return $endpoint_url;
	}

	/**
	 * Apply coupon on submit of custom coupon form.
	 */
	public function apply_coupon() {
		$response = '';

		if ( ! check_ajax_referer( 'wcf-apply-coupon', 'security', false ) ) {
			$response_data = array(
				'status' => false,
				'error'  => __( 'Nonce validation failed', 'cartflows' ),
			);
			wp_send_json_error( $response_data );
		}

		// Update the billing email before adding a coupon required for coupon conditions.
		$this->update_billing_email();

		ob_start();

		if ( ! empty( $_POST['coupon_code'] ) ) {
			$result = WC()->cart->add_discount( sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) );
		} else {
			wc_add_notice( WC_Coupon::get_generic_coupon_error( WC_Coupon::E_WC_COUPON_PLEASE_ENTER ), 'error' );
		}

		$response = array(
			'status' => $result,
			'msg'    => wc_print_notices( true ),
		);

		ob_clean(); // Clearing the uncessary echo HTML.
		wp_send_json( $response );

		die();
	}

	/**
	 * Remove coupon.
	 */
	public function remove_coupon() {
		check_ajax_referer( 'wcf-remove-coupon', 'security' );
		$coupon = isset( $_POST['coupon_code'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon_code'] ) ) : false;

		if ( empty( $coupon ) ) {
			echo "<div class='woocommerce-error'>" . esc_html__( 'Sorry there was a problem removing this coupon.', 'cartflows' ) . '</div>';
		} else {
			WC()->cart->remove_coupon( $coupon );
			echo "<div class='woocommerce-error'>" . esc_html__( 'Coupon has been removed.', 'cartflows' ) . '</div>';
		}
		wc_print_notices();
		wp_die();
	}

	/**
	 * Remove cart item.
	 */
	public function wcf_woo_remove_cart_product() {
		check_ajax_referer( 'wcf-remove-cart-product', 'security' );
		$product_key   = isset( $_POST['p_key'] ) ? sanitize_text_field( wp_unslash( $_POST['p_key'] ) ) : false;
		$product_id    = isset( $_POST['p_id'] ) ? sanitize_text_field( wp_unslash( $_POST['p_id'] ) ) : '';
		$product_title = get_the_title( $product_id );

		$needs_shipping = false;
		$is_order_bump  = false;
		$order_bump_id  = '';

		// Check if the product is an order bump before removing it.
		if ( ! empty( $product_key ) ) {
			$cart_item = WC()->cart->get_cart_item( $product_key );
			if ( isset( $cart_item['cartflows_bump'] ) && $cart_item['cartflows_bump'] ) {
				$is_order_bump = true;
				$order_bump_id = isset( $cart_item['ob_id'] ) ? $cart_item['ob_id'] : '';
			}
			
			WC()->cart->remove_cart_item( $product_key );
			$msg = "<div class='woocommerce-message'>" . $product_title . __( ' has been removed.', 'cartflows' ) . '</div>';
		} else {
			$msg = "<div class='woocommerce-message'>" . __( 'Sorry there was a problem removing ', 'cartflows' ) . $product_title;
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
			if ( $values['data']->needs_shipping() ) {
				$needs_shipping = true;
				break;
			}
		}

		$response = array(
			'need_shipping' => $needs_shipping,
			'msg'           => $msg,
			'is_order_bump' => $is_order_bump,
			'order_bump_id' => $order_bump_id,
		);

		echo wp_json_encode( $response );
		wp_die();
	}


	/**
	 * Check email exist.
	 */
	public function check_email_exists() {

		check_ajax_referer( 'check-email-exist', 'security' );

		$email_address = isset( $_POST['email_address'] ) ? sanitize_email( wp_unslash( $_POST['email_address'] ) ) : false;

		$is_login_allowed = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );

		// Security: Only check email existence when the checkout login UX needs it.
		// Otherwise the boolean would leak registered emails for no UX benefit.
		$is_exist = $is_login_allowed && email_exists( $email_address );

		$response = array(
			'success'          => boolval( $is_exist ),
			'is_login_allowed' => $is_login_allowed,
			'msg'              => $is_exist ? __( 'Email Exist.', 'cartflows' ) : __( 'Email not exist', 'cartflows' ),
		);

		wp_send_json_success( $response );
	}

	/**
	 * Update billing email address before applying the coupon. This is used for coupon conditions.
	 *
	 * @return void
	 * @since 2.0.12
	 */
	public function update_billing_email() {

		if ( ! wcf()->is_woo_active ) {
			return;
		}

		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\ArrayUtil' ) ) {
			return;
		}

		// Sanitize the billing email.
		$billing_email = ! empty( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) ) : ''; //phpcs:ignore WordPress.Security.NonceVerification.Missing

		$billing_email = \Automattic\WooCommerce\Utilities\ArrayUtil::get_value_or_default(
			array(
				'billing_email' => $billing_email,
			),
			'billing_email' 
		);

		if ( is_string( $billing_email ) && is_email( $billing_email ) ) {
			wc()->customer->set_billing_email( $billing_email );
		}
	}

	/**
	 * Check email exist.
	 */
	public function woocommerce_user_login() {

		check_ajax_referer( 'woocommerce-login', 'security' );

		$response = array(
			'success' => false,
		);

		$email_address = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : false;
		$password      = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : false; // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$creds = array(
			'user_login'    => $email_address,
			'user_password' => $password,
			'remember'      => false,
		);

		$user = wp_signon( $creds, false );

		if ( ! is_wp_error( $user ) ) {
			$response = array(
				'success' => true,
			);
		} else {
			// Mirror WC's process_login() so security plugins see the failure.
			do_action( 'woocommerce_login_failed' );
			// Generic error to prevent user enumeration.
			$response['error'] = __( 'Invalid email address or password.', 'cartflows' );
		}

		wp_send_json_success( $response );
	}

	/**
	 * Handle checkout file upload via AJAX.
	 *
	 * @since 2.2.2
	 * @return void
	 */
	public function upload_checkout_file() {

		if ( ! check_ajax_referer( 'wcf-file-upload', 'security', false ) ) {
			wp_send_json_error(
				array( 'error' => __( 'Nonce validation failed.', 'cartflows' ) )
			);
		}

		if ( empty( $_FILES['wcf_checkout_file']['tmp_name'] ) ) {
			wp_send_json_error(
				array( 'error' => __( 'No file uploaded.', 'cartflows' ) )
			);
		}

		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$file         = $_FILES['wcf_checkout_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification.Missing
		$file['name'] = sanitize_file_name( $file['name'] );
		$file['ext']  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		$master_allowed = Cartflows_Helper::get_allowed_file_extensions();

		$restrictions = $this->get_field_restrictions();

		$allowed_extensions = empty( $restrictions['extensions'] )
			? $master_allowed
			: array_values( array_intersect( $master_allowed, $restrictions['extensions'] ) );

		if ( ! in_array( $file['ext'], $allowed_extensions, true ) ) {
			wp_send_json_error( array( 'error' => __( 'File type is not allowed.', 'cartflows' ) ) );
		}

		if ( (int) $file['size'] > $restrictions['max_size'] ) {
			wp_send_json_error( array( 'error' => __( 'File size exceeds the allowed limit.', 'cartflows' ) ) );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], wp_get_mime_types() );

		if ( empty( $check['ext'] ) || $check['ext'] !== $file['ext'] ) {
			wp_send_json_error( array( 'error' => __( 'Invalid or corrupted file.', 'cartflows' ) ) );
		}

		$result = $this->move_uploaded_file( $file );

		wp_send_json_success(
			array(
				'success'  => true,
				'url'      => esc_url_raw( $result['url'] ),
				'filename' => sanitize_file_name( basename( $result['file'] ) ),
			)
		);
	}

	/**
	 * Retrieve file size and type restrictions from field settings.
	 *
	 * @since 2.2.2
	 *
	 * @return array{max_size:int, extensions:string[]} Field-level upload restrictions.
	 */
	private function get_field_restrictions() {

		$max_size   = 5 * 1024 * 1024; // 5MB default
		$extensions = array();

		$field_key   = empty( $_POST['field_key'] ) ? '' : sanitize_text_field( wp_unslash( $_POST['field_key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$checkout_id = empty( $_POST['checkout_id'] ) ? 0 : absint( $_POST['checkout_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( empty( $field_key ) || empty( $checkout_id ) ) {
			return compact( 'max_size', 'extensions' );
		}

		$field_type   = ( 0 === strpos( $field_key, 'shipping_' ) ) ? 'shipping' : 'billing';
		$saved_fields = get_post_meta( $checkout_id, 'wcf_field_order_' . $field_type, true );

		if ( ! is_array( $saved_fields ) || empty( $saved_fields[ $field_key ] ) || ! is_array( $saved_fields[ $field_key ] ) ) {
			return compact( 'max_size', 'extensions' );
		}

		$custom_attributes = empty( $saved_fields[ $field_key ]['custom_attributes'] ) || ! is_array( $saved_fields[ $field_key ]['custom_attributes'] )
			? array()
			: $saved_fields[ $field_key ]['custom_attributes'];

		if ( empty( $custom_attributes ) ) {
			return compact( 'max_size', 'extensions' );
		}

		if ( ! empty( $custom_attributes['file_size'] ) && is_scalar( $custom_attributes['file_size'] ) ) {
			$max_size = max( 1, absint( $custom_attributes['file_size'] ) ) * 1024 * 1024;
		}

		if ( ! empty( $custom_attributes['accepted_file_types'] ) ) {
			$extensions = $this->normalize_extension_list( $custom_attributes['accepted_file_types'] );
		}

		return compact( 'max_size', 'extensions' );
	}

	/**
	 * Normalize accepted file types into a lowercase extension list.
	 *
	 * @since 2.2.2
	 *
	 * @param string $raw Raw accepted file types value.
	 * @return array Normalized extensions.
	 */
	private function normalize_extension_list( $raw ) {

		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			return array();
		}

		$extensions = array_map( 'strtolower', array_map( 'trim', explode( ',', $raw ) ) );
		
		return array_values( array_filter( $extensions ) );
	}

	/**
	 * Move the uploaded file into the WordPress uploads directory.
	 *
	 * @since 2.2.2
	 *
	 * @param array $file Normalized file data.
	 * @return array Upload result.
	 */
	private function move_uploaded_file( array $file ) {

		$upload_dir   = wp_upload_dir();
		$file['name'] = wp_unique_filename( $upload_dir['path'], 'wcf_' . wp_generate_uuid4() . '.' . $file['ext'] );

		$result = wp_handle_upload(
			$file,
			array(
				'test_form' => false,
				'mimes'     => wp_get_mime_types(),
			)
		);

		if ( isset( $result['error'] ) ) {
			wp_send_json_error(
				array( 'error' => esc_html( $result['error'] ) )
			);
		}

		return $result;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Checkout_Ajax::get_instance();
