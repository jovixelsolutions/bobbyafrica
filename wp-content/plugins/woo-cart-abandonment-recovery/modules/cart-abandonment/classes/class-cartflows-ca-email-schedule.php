<?php
/**
 * Cart Abandonment
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Cart abandonment tracking class.
 */
class Cartflows_Ca_Email_Schedule {
	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Constructor function that initializes required actions and hooks.
	 */
	public function __construct() {

		add_action( 'wp_ajax_wcf_ca_preview_email_send', [ $this, 'send_preview_email' ] );
	}

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
	 *  Send preview emails.
	 */
	public function send_preview_email(): void {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'woo-cart-abandonment-recovery' ) );
		}

		check_ajax_referer( WCF_EMAIL_TEMPLATES_NONCE, 'security' );
		$mail_result = $this->send_email_templates( null, true );
		if ( $mail_result ) {
			wp_send_json_success( __( 'Mail has been sent successfully!', 'woo-cart-abandonment-recovery' ) );
		} else {
			wp_send_json_error( __( 'Mail sending failed!', 'woo-cart-abandonment-recovery' ) );
		}
	}

	/**
	 * Callback function to send email templates.
	 *
	 * @param array $email_data email data  .
	 * @param bool  $preview_email preview email.
	 * @since 1.0.0
	 */
	public function send_email_templates( $email_data, $preview_email = false ) {

		if ( $preview_email ) {
			$email_data = $this->create_dummy_session_for_preview_email();
		}

		if ( filter_var( $email_data->email, FILTER_VALIDATE_EMAIL ) ) {
			if ( ! $preview_email ) {

				$cart_items = maybe_unserialize( $email_data->cart_contents );
				if ( $this->cart_contains_out_of_stock_products( $cart_items ) ) {
					return false;
				}

				if ( ! $this->check_if_already_purchased_by_email_product_ids( $email_data, $email_data->cart_contents ) ) {
					return false;
				}
				
				/**
				 * Filter to determine if email should be sent.
				 *
				 * @param bool   $should_send Whether email should be sent.
				 * @param object $email_data Email data object.
				 * @since 1.0.0
				 */
				$should_send = apply_filters( 'wcf_ca_should_send_email', true, $email_data );

				if ( ! $should_send ) {
					return false;
				}
			}

			$email_data = apply_filters( 'woo_ca_recovery_email_data', $email_data, $preview_email );

			$other_fields = maybe_unserialize( $email_data->other_fields );

			$from_email_name    = get_option( 'wcf_ca_from_name' );
			$reply_name_preview = get_option( 'wcf_ca_reply_email' );
			$from_email_preview = get_option( 'wcf_ca_from_email' );

			$user_first_name = ! empty( $other_fields['wcf_first_name'] ) ? ucfirst( $other_fields['wcf_first_name'] ) : ucfirst( $other_fields['wcf_shipping_first_name'] );
			$user_first_name = $user_first_name ? $user_first_name : apply_filters( 'woo_ca_default_first_name', __( 'there', 'woo-cart-abandonment-recovery' ) );
			$user_last_name  = ucfirst( $other_fields['wcf_last_name'] );
			$user_full_name  = trim( $user_first_name . ' ' . $user_last_name );

			$subject_email_preview = stripslashes( html_entity_decode( $email_data->email_subject, ENT_QUOTES, 'UTF-8' ) );
			$subject_email_preview = convert_smilies( $subject_email_preview );
			$subject_email_preview = str_replace( '{{customer.firstname}}', $user_first_name, $subject_email_preview );
			$subject_email_preview = str_replace( '{{customer.lastname}}', $user_last_name, $subject_email_preview );
			$subject_email_preview = str_replace( '{{customer.fullname}}', $user_full_name, $subject_email_preview );
			
			$body_email_preview = html_entity_decode( $email_data->email_body, ENT_QUOTES, 'UTF-8' );
			$body_email_preview = convert_smilies( $body_email_preview );
			$body_email_preview = str_replace( '{{customer.firstname}}', $user_first_name, $body_email_preview );
			$body_email_preview = str_replace( '{{customer.lastname}}', $user_last_name, $body_email_preview );
			$body_email_preview = str_replace( '{{customer.fullname}}', $user_full_name, $body_email_preview );

			$email_instance = Cartflows_Ca_Email_Templates::get_instance();
			if ( $preview_email ) {
				$coupon_code = 'DUMMY-COUPON';
			} else {
				$override_global_coupon = $email_instance->get_email_template_meta_by_key( $email_data->email_template_id, 'override_global_coupon' );
				if ( ! empty( $override_global_coupon ) && ! empty( $override_global_coupon->meta_value ) && $override_global_coupon->meta_value ) {
					$email_history = $email_instance->get_email_history_by_id( $email_data->email_history_id );
					$coupon_code   = $email_history->coupon_code;
				} else {
					$coupon_code = $email_data->coupon_code;
				}
			}

			$auto_apply_coupon = $email_instance->get_email_template_meta_by_key( $email_data->email_template_id, 'auto_coupon' );

			$token_data = [
				'wcf_session_id'    => $email_data->session_id,
				'wcf_coupon_code'   => isset( $auto_apply_coupon ) && $auto_apply_coupon->meta_value ? $coupon_code : null,
				'wcf_preview_email' => $preview_email ? true : false,
			];

			// Filter to modify token data.
			$token_data = apply_filters( 'wcar_add_token_data', $token_data, $email_data );

			$checkout_url = Cartflows_Ca_Helper::get_instance()->get_checkout_url( $email_data->checkout_id, $token_data );

			$subject_email_preview = str_replace( '{{cart.coupon_code}}', $coupon_code, $subject_email_preview );
			$body_email_preview    = str_replace( '{{cart.coupon_code}}', $coupon_code, $body_email_preview );

			$current_time_stamp    = $email_data->time;
			$subject_email_preview = str_replace( '{{cart.abandoned_date}}', $current_time_stamp, $subject_email_preview );
			$body_email_preview    = str_replace( '{{cart.abandoned_date}}', $current_time_stamp, $body_email_preview );
			$unsubscribe_element   = '<a target="_blank" style="color: lightgray" href="' . $checkout_url . '&unsubscribe=true ">' . __( 'Don\'t remind me again.', 'woo-cart-abandonment-recovery' ) . '</a>';
			$body_email_preview    = str_replace( '{{cart.unsubscribe}}', $unsubscribe_element, $body_email_preview );
			$body_email_preview    = str_replace( 'http://{{cart.checkout_url}}', '{{cart.checkout_url}}', $body_email_preview );
			$body_email_preview    = str_replace( 'https://{{cart.checkout_url}}', '{{cart.checkout_url}}', $body_email_preview );
			$body_email_preview    = str_replace( "'{{cart.checkout_url}}'", '{{cart.checkout_url}}', $body_email_preview );
			$body_email_preview    = str_replace( '{{cart.checkout_url}}', $checkout_url, $body_email_preview );
			$host                  = wp_parse_url( get_site_url() );
			$body_email_preview    = str_replace( '{{site.url}}', $host['host'], $body_email_preview );

			if ( false !== strpos( $body_email_preview, '{{cart.product.names}}' ) ) {
				$body_email_preview = str_replace( '{{cart.product.names}}', Cartflows_Ca_Helper::get_instance()->get_comma_separated_products( $email_data->cart_contents ), $body_email_preview );
			}

			$admin_user            = get_users(
				[
					'role'   => 'Administrator',
					'number' => 1,
				]
			);
			$admin_user            = reset( $admin_user );
			$admin_first_name      = $admin_user->user_firstname ? $admin_user->user_firstname : __( 'Admin', 'woo-cart-abandonment-recovery' );
			$subject_email_preview = str_replace( '{{admin.firstname}}', $admin_first_name, $subject_email_preview );
			$body_email_preview    = str_replace( '{{admin.firstname}}', $admin_first_name, $body_email_preview );
			$body_email_preview    = str_replace( '{{admin.company}}', get_bloginfo( 'name' ), $body_email_preview );

			$headers  = 'From: ' . $from_email_name . ' <' . $from_email_preview . '>' . "\r\n";
			$headers .= 'Content-Type: text/html' . "\r\n";
			$headers .= 'Reply-To:  ' . $reply_name_preview . ' ' . "\r\n";
			$var      = $this->get_email_product_block( $email_data->cart_contents, $email_data->cart_total );

			$body_email_preview = str_replace( '{{cart.product.table}}', $var, $body_email_preview );
			$body_email_preview = wpautop( $body_email_preview );

			// Convert TinyMCE alignment classes to inline styles for email compatibility.
			$body_email_preview = $this->convert_alignment_classes_to_styles( $body_email_preview );

			/**
			 * Filter to modify email body before sending.
			 *
			 * @param string $body_email_preview Email body content.
			 * @param object $email_data Email data object.
			 * @param bool   $preview_email Whether this is a preview email.
			 * @return string Modified email body.
			 * @since 1.0.0
			 */
			$body_email_preview = apply_filters( 'wcf_ca_email_body_before_send', $body_email_preview, $email_data, $preview_email );

			$use_woo_style = $email_instance->get_email_template_meta_by_key( $email_data->email_template_id, 'use_woo_email_style' );

			if ( '1' === $use_woo_style->meta_value ) {
				ob_start();

				wc_get_template( 'emails/email-header.php', [ 'email_heading' => $subject_email_preview ] );
				$email_body_template_header = ob_get_clean();

				ob_start();

				wc_get_template( 'emails/email-footer.php' );
				$email_body_template_footer = ob_get_clean();

				$site_title                 = get_bloginfo( 'name' );
				$email_body_template_footer = str_ireplace( '{site_title}', $site_title, $email_body_template_footer );
				
				$address  = get_option( 'woocommerce_store_address' );
				$address2 = get_option( 'woocommerce_store_address_2' );
				$city     = get_option( 'woocommerce_store_city' );
				$postcode = get_option( 'woocommerce_store_postcode' );
				$country  = get_option( 'woocommerce_default_country' );
				// Build the address only with non-empty fields.
				$parts = array_filter(
					[
						$address,
						$address2,
						$city,
						$postcode,
						$country,
					] 
				);

				// Join parts with commas.
				$full_address = implode( ', ', $parts );

				$email_body_template_footer = str_replace( '{store_address}', $full_address, $email_body_template_footer );

				$final_email_body = $email_body_template_header . $body_email_preview . $email_body_template_footer;
				return $this->send_email( $email_data, $subject_email_preview, $final_email_body, $headers, $preview_email, 'wc_mail' );
			}

			// Ignoring the below rule as rule asking to use third party mailing functions but third-party SMTP plugins overrides the wp_mail and uses their mailing system.
			//phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail

			return $this->send_email( $email_data, $subject_email_preview, $body_email_preview, $headers, $preview_email, 'wp_mail' );

			//phpcs:enable WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
		}
			return false;
	}

	/**
	 * Create a dummy object for the preview email.
	 *
	 * @return stdClass
	 */
	public function create_dummy_session_for_preview_email() {

		$email_data                    = new stdClass();
		$current_user                  = wp_get_current_user();
		$helper_class                  = Cartflows_Ca_Helper::get_instance();
		$email_data->email_template_id = $helper_class->sanitize_text_filter( 'email_template_id', 'POST' );

		$email_data->checkout_id   = wc_get_page_id( 'checkout' );
		$email_data->session_id    = 'dummy-session-id';
		$email_send_to             = filter_input( INPUT_POST, 'email_send_to', FILTER_SANITIZE_EMAIL );
		$email_data->email         = $email_send_to ? $email_send_to : $current_user->user_email;
		$email_data->email_body    = filter_input( INPUT_POST, 'email_body', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$email_data->email_subject = $helper_class->sanitize_text_filter( 'email_subject', 'POST' );
		$email_data->email_body    = html_entity_decode( $email_data->email_body, ENT_COMPAT, 'UTF-8' );
		$email_data->email_body    = preg_replace( '#<script\b[^>]*>[\s\S]*?<\/script>#i', '', $email_data->email_body );
		$email_data->email_body    = preg_replace( '#\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)#i', '', $email_data->email_body );
		$email_data->email_body    = preg_replace( '#(href|src)\s*=\s*(["\'])\s*javascript:[^"\']*\2#i', '$1=$2$2', $email_data->email_body );
		$email_data->other_fields  = maybe_serialize(
			[
				'wcf_first_name' => $current_user->user_firstname,
				'wcf_last_name'  => $current_user->user_lastname,
			]
		);
		if ( ! WC()->cart->get_cart_contents_count() ) {
			$args = [
				'posts_per_page' => 1,
				'orderby'        => 'rand',
				'post_type'      => 'product',
				'meta_query'     => [ //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					// Exclude out of stock products.
					[
						'key'     => '_stock_status',
						'value'   => 'outofstock',
						'compare' => 'NOT IN',
					],
				],
				'tax_query'      => [ //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => 'simple',
					],
				],
			];

			$random_products = get_posts( $args );
			if ( ! empty( $random_products ) ) {
				$random_product = reset( $random_products );
				WC()->cart->add_to_cart( $random_product->ID );
			}
		}

		$email_data->cart_total    = WC()->cart->total + floatval( WC()->cart->get_cart_shipping_total() );
		$email_data->cart_contents = maybe_serialize( WC()->cart->get_cart() );
		$email_data->time          = current_time( WCF_CA_DATETIME_FORMAT );
		return $email_data;
	}

	/**
	 * Generate the view for email product cart block.
	 *
	 * @param  string $cart_contents user cart contents details.
	 * @param  float  $cart_total user cart total.
	 * @return string
	 */
	public function get_email_product_block( $cart_contents, $cart_total ) {

		$cart_items = maybe_unserialize( $cart_contents );

		if ( ! is_array( $cart_items ) || ! count( $cart_items ) ) {
			return;
		}

		$tr    = '';
		$style = [
			'product_image' => [
				'style'     => 'height: 42px; width: 42px;',
				'attribute' => 'width=42 height=42',
			],
			'table'         => [
				'style'     => 'color: #636363; border: 1px solid #e5e5e5;',
				'attribute' => 'align= left;',
			],
		];

		$style_filter        = apply_filters( 'woo_ca_email_template_table_style', $style );
		$product_image_style = $style_filter['product_image']['style'] ?? '';
		$style               = $style_filter['table']['style'] ?? '';

		foreach ( $cart_items as $cart_item ) {

			if ( isset( $cart_item['product_id'] ) && isset( $cart_item['quantity'] ) && isset( $cart_item['line_total'] ) ) {
				$id        = 0 !== $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
				$image_url = get_the_post_thumbnail_url( $id );
				$image_url = ! empty( $image_url ) ? $image_url : get_the_post_thumbnail_url( $cart_item['product_id'] );

				$product      = wc_get_product( $id );
				$product_name = $product ? $product->get_formatted_name() : '';

				if ( empty( $image_url ) ) {
					$image_url = CARTFLOWS_CA_URL . 'admin/assets/images/image-placeholder.png';
				}
				$tr .= '<tr style=' . $style . ' align="center">
                           <td style="' . $style . '"><img  class="demo_img" style="' . $product_image_style . '" src="' . esc_url( $image_url ) . '" ' . $style_filter['product_image']['attribute'] . '></td>
                           <td style="' . $style . '">' . $product_name . '</td>
                           <td style="' . $style . '"> ' . $cart_item['quantity'] . ' </td>
                           <td style="' . $style . '">' . wc_price( $cart_item['line_total'] ) . '</td>
                           <td style="' . $style . '" >' . wc_price( $cart_item['line_total'] ) . '</td>
                        </tr> ';
			}
		}

		/**
		 * Add filter to toggle the Cart Total row.
		 */
		$enable_cart_total = apply_filters( 'woo_ca_recovery_enable_cart_total', false );
		if ( $enable_cart_total ) {
			$tr .= '<tr style="' . $style . '" align="center">
                           <td colspan="4" style="' . $style . '"> ' . __( 'Cart Total ( Cart Total + Shipping + Tax )', 'woo-cart-abandonment-recovery' ) . ' </td>
                           <td style="' . $style . '" >' . wc_price( $cart_total ) . '</td>
                        </tr> ';
		}

		return '<table ' . $style_filter['table']['attribute'] . ' cellpadding="10" cellspacing="0" style="float: none; border: 1px solid #e5e5e5;">
	                <tr align="center">
	                   <th  style="' . $style . '">' . __( 'Item', 'woo-cart-abandonment-recovery' ) . '</th>
	                   <th  style="' . $style . '">' . __( 'Name', 'woo-cart-abandonment-recovery' ) . '</th>
	                   <th  style="' . $style . '">' . __( 'Quantity', 'woo-cart-abandonment-recovery' ) . '</th>
	                   <th  style="' . $style . '">' . __( 'Price', 'woo-cart-abandonment-recovery' ) . '</th>
	                   <th  style="' . $style . '">' . __( 'Line Subtotal', 'woo-cart-abandonment-recovery' ) . '</th>
	                </tr> ' . $tr . '
	        </table>';
	}

	/**
	 * Check before emails actually send to user.
	 *
	 * @param object $email_data email_data.
	 * @param string $current_cart_data cart data.
	 * @return bool
	 */
	public function check_if_already_purchased_by_email_product_ids( $email_data, $current_cart_data ) {

		global $wpdb;
		$current_cart_data = maybe_unserialize( $current_cart_data );

		// Fetch products & variations.
		$products         = array_values( wp_list_pluck( $current_cart_data, 'product_id' ) );
		$variations       = array_values( wp_list_pluck( $current_cart_data, 'variation_id' ) );
		$current_products = array_unique( array_merge( $products, $variations ) );

		$cart_abandonment_table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$orders             = wc_get_orders(
			[
				'billing_email' => $email_data->email,
				'status'        => [ 'processing', 'completed' ],
				'date_after'    => gmdate(
					'Y-m-d h:i:s',
					strtotime( '-30 days' )
				),
			]
		);
		$need_to_send_email = true;

		foreach ( $orders as $order ) {
			$order = wc_get_order( $order->get_id() );
			$items = $order->get_items();
			foreach ( $items as $item ) {
				$product_id = $item->get_product_id();
				if ( in_array( $product_id, $current_products, true ) ) {
					/**
					 * Remove duplicate captured order for tracking.
					 */
					$wpdb->delete( $cart_abandonment_table, [ 'session_id' => sanitize_key( $email_data->session_id ) ] ); // db call ok; no-cache ok.
					$need_to_send_email = false;
					break;
				}
			}
		}
		return $need_to_send_email;
	}

	/**
	 * Schedule events for the abadoned carts to send emails.
	 *
	 * @param int  $session_id user session id.
	 * @param bool $force_reschedule force reschedule.
	 */
	public function schedule_emails( $session_id, $force_reschedule = false ): void {

		$checkout_details = Cartflows_Ca_Helper::get_instance()->get_checkout_details( $session_id );

		if ( $checkout_details->unsubscribed || ( WCF_CART_COMPLETED_ORDER === $checkout_details->order_status ) ) {
			return;
		}

		$scheduled_emails = Cartflows_Ca_Helper::get_instance()->fetch_scheduled_emails( $session_id );
		// We are recommending PHP 7.2 version to use our plugin.
		$scheduled_templates = array_column( $scheduled_emails, 'template_id' ); //phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_columnFound
		$scheduled_time_from = $checkout_details->time;

		if ( $force_reschedule ) {
			$scheduled_time_from = current_time( WCF_CA_DATETIME_FORMAT );
		}

		$email_tmpl = Cartflows_Ca_Email_Templates::get_instance();
		$templates  = $email_tmpl->fetch_all_active_templates();

		global $wpdb;

		$email_history_table = $wpdb->prefix . CARTFLOWS_CA_EMAIL_HISTORY_TABLE;

		foreach ( $templates as $template ) {

			if ( false !== array_search( $template->id, $scheduled_templates, true ) && false === $force_reschedule ) {
				continue;
			}

			/**
			 * Filter to determine if template should be scheduled.
			 *
			 * @param bool   $should_schedule Whether template should be scheduled.
			 * @param object $template Email template object.
			 * @param object $checkout_details Checkout details object.
			 * @since 1.0.0
			 */
			$should_schedule = apply_filters( 'wcf_ca_should_schedule_template', true, $template, $checkout_details );

			if ( ! $should_schedule ) {
				continue;
			}

			$timestamp_str  = '+' . $template->frequency . ' ' . $template->frequency_unit . 'S';
			$scheduled_time = gmdate( WCF_CA_DATETIME_FORMAT, strtotime( $scheduled_time_from . $timestamp_str ) );
			$discount_type  = $email_tmpl->get_email_template_meta_by_key( $template->id, 'discount_type' );
			$discount_type  = $discount_type->meta_value ?? '';
			$amount         = $email_tmpl->get_email_template_meta_by_key( $template->id, 'coupon_amount' );
			$amount         = $amount->meta_value ?? '';

			$coupon_expiry_date = $email_tmpl->get_email_template_meta_by_key( $template->id, 'coupon_expiry_date' );
			$coupon_expiry_unit = $email_tmpl->get_email_template_meta_by_key( $template->id, 'coupon_expiry_unit' );
			$coupon_expiry_date = $coupon_expiry_date->meta_value ?? '';
			$coupon_expiry_unit = $coupon_expiry_unit->meta_value ?? 'hours';

			$coupon_expiry_date = $coupon_expiry_date ? strtotime( $scheduled_time . ' +' . $coupon_expiry_date . ' ' . $coupon_expiry_unit ) : '';

			$free_shipping_coupon = $email_tmpl->get_email_template_meta_by_key( $template->id, 'free_shipping_coupon' );
			$free_shipping        = isset( $free_shipping_coupon ) && ( $free_shipping_coupon->meta_value ) ? 'yes' : 'no';

			$individual_use_only = $email_tmpl->get_email_template_meta_by_key( $template->id, 'individual_use_only' );
			$individual_use      = isset( $individual_use_only ) && ( $individual_use_only->meta_value ) ? 'yes' : 'no';

			$override_global_coupon = $email_tmpl->get_email_template_meta_by_key( $template->id, 'override_global_coupon' );

			$new_coupon_code = '';
			if ( ! empty( $override_global_coupon ) && ! empty( $override_global_coupon->meta_value ) && $override_global_coupon->meta_value ) {
				$new_coupon_code = $this->generate_coupon_code( $discount_type, $amount, $coupon_expiry_date, $free_shipping, $individual_use, $template->id );
			}

			$wpdb->replace(
				$email_history_table,
				[
					'template_id'    => $template->id,
					'ca_session_id'  => $checkout_details->session_id,
					'coupon_code'    => $new_coupon_code,
					'scheduled_time' => $scheduled_time,
				]
			); // db call ok; no-cache ok.
		}
	}

	/**
	 *  Generate new coupon code for abandoned cart.
	 *
	 * @param string $discount_type discount type.
	 * @param float  $amount amount.
	 * @param string $expiry expiry.
	 * @param string $free_shipping is free shipping.
	 * @param string $individual_use use coupon individual.
	 * @param int    $template_id template ID.
	 */
	public function generate_coupon_code( $discount_type, $amount, $expiry = '', $free_shipping = 'no', $individual_use = 'no', $template_id = 0 ) {

		$coupon_code = '';

			$coupon_code = wp_generate_password( 8, false, false );

			$new_coupon_id = wp_insert_post(
				[
					'post_title'   => $coupon_code,
					'post_content' => '',
					'post_status'  => 'publish',
					'post_author'  => 1,
					'post_type'    => 'shop_coupon',
				]
			);

			$coupon_post_data = [
				'discount_type'       => $discount_type,
				'description'         => WCF_CA_COUPON_DESCRIPTION,
				'coupon_amount'       => $amount,
				'individual_use'      => $individual_use,
				'product_ids'         => '',
				'usage_limit'         => '1',
				'usage_count'         => '0',
				'date_expires'        => $expiry,
				'apply_before_tax'    => 'yes',
				'free_shipping'       => $free_shipping,
				'coupon_generated_by' => WCF_CA_COUPON_GENERATED_BY,
			];

			/**
			 * Filter coupon post data.
			 *
			 * @param array $coupon_post_data Coupon post data.
			 * @param int   $template_id Template ID if available.
			 */
			$coupon_post_data = apply_filters( 'woo_ca_generate_coupon', $coupon_post_data, $template_id );

			foreach ( $coupon_post_data as $key => $value ) {
				update_post_meta( $new_coupon_id, $key, $value );
			}

			/**
			 * Action after coupon is created.
			 *
			 * @param string $coupon_code Generated coupon code.
			 * @param int    $new_coupon_id Coupon post ID.
			 * @param array  $context Additional context.
			 * @since 1.0.0
			 */
			do_action( 'wcf_ca_after_coupon_created', $coupon_code, $new_coupon_id );

			return $coupon_code;
	}

	/**
	 * Check if cart contains out-of-stock products.
	 *
	 * @param array $cart_items Cart items array.
	 * @return bool
	 */
	public function cart_contains_out_of_stock_products( $cart_items ) {
		if ( empty( $cart_items ) || ! is_array( $cart_items ) ) {
			return false;
		}

		foreach ( $cart_items as $cart_item ) {
			$product_id = isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
			$product    = wc_get_product( $product_id );

			if ( ! $product || ! $product->is_in_stock() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Send email and trigger actions in one place.
	 *
	 * @param object $email_data Email data object.
	 * @param string $subject Email subject.
	 * @param string $body Email body.
	 * @param string $headers Email headers.
	 * @param bool   $preview_email Whether this is a preview email.
	 * @param string $mail_type Mail type: 'wc_mail' or 'wp_mail'.
	 * @return bool
	 */
	private function send_email( $email_data, $subject, $body, $headers, $preview_email, $mail_type ) {
		$result = false;

		//phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail
		if ( 'wc_mail' === $mail_type ) {
			// WooCommerce style email.
			wc_mail( $email_data->email, $subject, stripslashes( $body ), $headers );
			$result = true;
		} else {
			// Regular wp_mail with retry mechanism.
			$result = wp_mail( $email_data->email, $subject, stripslashes( $body ), $headers );
			if ( ! $result ) {
				// Retry sending mail.
				$result = wp_mail( $email_data->email, $subject, stripslashes( $body ), $headers );
			}
		}
		//phpcs:enable WordPressVIPMinimum.Functions.RestrictedFunctions.wp_mail_wp_mail

		// Trigger action after successful email sending (only for non-preview emails).
		if ( ! $preview_email && $result ) {
			do_action( 'wcf_ca_after_email_sent', $email_data, $mail_type );
		}

		return $result;
	}

	/**
	 * Convert TinyMCE alignment classes to inline styles for email compatibility.
	 *
	 * @param string $content Email content with potential alignment classes.
	 * @return string Content with alignment classes converted to inline styles.
	 */
	public function convert_alignment_classes_to_styles( $content ) {
		// Pattern to match img tags with alignment classes.
		$pattern = '/<img([^>]*?)class=["\']([^"\']*?)(alignleft|aligncenter|alignright|alignnone)([^"\']*?)["\']([^>]*?)>/i';
		
		return preg_replace_callback( $pattern, array( __CLASS__, 'replace_alignment_callback' ), $content );
	}

	/**
	 * Callback function to replace alignment classes with inline styles.
	 *
	 * @param array $matches Regex matches.
	 * @return string Modified img tag with inline styles.
	 */
	private function replace_alignment_callback( $matches ) {
		$before_class = $matches[1];
		$class_before = $matches[2];
		$alignment    = $matches[3];
		$class_after  = $matches[4];
		$after_class  = $matches[5];
		
		// Remove the alignment class from the class attribute.
		$new_classes = trim( $class_before . ' ' . $class_after );
		$new_classes = preg_replace( '/\s+/', ' ', $new_classes );
		
		// Get alignment style.
		$alignment_style = $this->get_alignment_style( $alignment );
		
		// Check if style attribute already exists.
		if ( preg_match( '/style=["\']([^"\']*)["\']/', $before_class . $after_class, $style_matches ) ) {
			// Merge with existing styles.
			$existing_style = rtrim( $style_matches[1], '; ' );
			$new_style      = $existing_style . '; ' . $alignment_style;
			$img_tag        = preg_replace( '/style=["\'][^"\']*["\']/', 'style="' . $new_style . '"', $before_class . $after_class );
		} else {
			// Add new style attribute.
			$img_tag = $before_class . ' style="' . $alignment_style . '"' . $after_class;
		}
		
		// Rebuild the img tag.
		if ( ! empty( $new_classes ) ) {
			$class_attr = ' class="' . $new_classes . '"';
		} else {
			$class_attr = '';
			// Remove empty class attribute.
			$img_tag = preg_replace( '/\s*class=["\']["\']/', '', $img_tag );
		}
		
		return '<img' . $img_tag . $class_attr . '>';
	}

	/**
	 * Get CSS style for alignment.
	 *
	 * @param string $alignment Alignment class (alignleft, aligncenter, alignright, alignnone).
	 * @return string CSS style string.
	 */
	private function get_alignment_style( $alignment ) {
		switch ( $alignment ) {
			case 'alignleft':
				return 'float: left; margin: 0 10px 10px 0';
			case 'alignright':
				return 'float: right; margin: 0 0 10px 10px';
			case 'aligncenter':
				return 'display: block; margin: 0 auto';
			case 'alignnone':
				return 'display: inline; margin: 0';
			default:
				return '';
		}
	}

}

Cartflows_Ca_Email_Schedule::get_instance();
