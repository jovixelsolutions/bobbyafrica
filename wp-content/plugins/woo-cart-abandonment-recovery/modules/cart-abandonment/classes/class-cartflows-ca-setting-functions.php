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
class Cartflows_Ca_Setting_Functions {
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

		$page = wcf_ca()->helper->sanitize_text_filter( 'page', 'GET' );

		if ( WCF_CA_PAGE_NAME === $page ) {
			// Adding filter to add new button to add custom fields.
			add_filter( 'mce_buttons', [ $this, 'wcf_filter_mce_button' ] );
			add_filter( 'mce_external_plugins', [ $this, 'wcf_filter_mce_plugin' ], 9 );
		}

		// GDPR actions.
		add_action( 'wp_ajax_cartflows_skip_cart_tracking_gdpr', [ $this, 'skip_cart_tracking_by_gdpr' ] );
		add_action( 'wp_ajax_nopriv_cartflows_skip_cart_tracking_gdpr', [ $this, 'skip_cart_tracking_by_gdpr' ] );

		// Delete coupons.

		add_action( 'wp_ajax_wcf_ca_delete_garbage_coupons', [ $this, 'delete_used_and_expired_coupons' ] );

		// TODO: Remove this after new UI is enabled by default.
		add_action( 'wp_ajax_wcf_ca_save_new_ui_option', [ $this, 'save_new_ui_option' ] );
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
	 * Register button.
	 *
	 * @param  array $buttons mce buttons.
	 * @return mixed
	 */
	public function wcf_filter_mce_button( $buttons ) {
		array_push( $buttons, 'cartflows_ac' );
		return $buttons;
	}

	/**
	 * Link JS to mce button.
	 *
	 * @param  array $plugins mce pluggins.
	 * @return mixed
	 */
	public function wcf_filter_mce_plugin( $plugins ) {

		$file_ext = wcf_ca()->helper->get_js_file_ext();

		$plugins['cartflows_ac'] = CARTFLOWS_CA_URL . 'admin/assets/' . $file_ext['folder'] . '/admin-mce.' . $file_ext['file_ext'];
		return $plugins;
	}

	/**
	 *  Delete tracked data and set cookie for the user.
	 */
	public function skip_cart_tracking_by_gdpr(): void {
		check_ajax_referer( 'cartflows_skip_cart_tracking_gdpr', 'security' );

		global $wpdb;
		$cart_abandonment_table = $wpdb->prefix . CARTFLOWS_CA_CART_ABANDONMENT_TABLE;

		$session_id = WC()->session->get( 'wcf_session_id' );
		if ( $session_id ) {
			$wpdb->delete( $cart_abandonment_table, [ 'session_id' => sanitize_key( $session_id ) ] ); // db call ok; no cache ok.
		}

		// Ignoring below rule as it need to replace the already build cookie logic to another logic. Can be update in future scope.
		setcookie( 'wcf_ca_skip_track_data', 'true', 0, '/' ); //phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.cookies_setcookie
		wp_send_json_success();
	}

	/**
	 * Check if transient is set for delete garbage coupons.
	 */
	public function delete_used_and_expired_coupons(): void {
		$is_ajax_request  = wp_doing_ajax();
		$is_transient_set = false;
		global $wpdb;
		if ( $is_ajax_request ) {
			if ( ! current_user_can( 'manage_woocommerce' ) ) {
				wp_send_json_error( __( 'Permission denied.', 'woo-cart-abandonment-recovery' ) );
			}
			check_ajax_referer( 'wcf_ca_delete_garbage_coupons', 'security' );
		} else {
			$is_transient_set = get_transient( 'woocommerce_ca_delete_garbage_coupons' );
		}

		if ( false === $is_transient_set || $is_ajax_request ) {
			$coupons      = $this->delete_garbage_coupons();
			$coupon_count = count( $coupons );

			if ( $coupon_count ) {
				$coupons_post_ids = array_map( 'absint', wp_list_pluck( $coupons, 'ID' ) );
				$placeholders     = implode( ',', array_fill( 0, count( $coupons_post_ids ), '%d' ) );
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}postmeta WHERE post_id IN({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
						...$coupons_post_ids
					)
				); // db call ok; no cache ok.
				$wpdb->query(
					$wpdb->prepare(
						"DELETE FROM {$wpdb->prefix}posts WHERE ID IN({$placeholders})", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
						...$coupons_post_ids
					)
				); // db call ok; no cache ok.
			}

			if ( ! $is_ajax_request ) {
				set_transient( 'woocommerce_ca_delete_garbage_coupons', $coupons, WEEK_IN_SECONDS );
				return;
			}

			// translators: %1$s: Coupons Deleted, %2$s: Deleted coupons count'.
			wp_send_json_success( sprintf( __( '%1$s: %2$d', 'woo-cart-abandonment-recovery' ), 'Coupons Deleted', $coupon_count ) );

		}
	}

	/**
	 * Set transient and delete garbage coupons.
	 */
	public function delete_garbage_coupons() {

		global $wpdb;

		$coupon_generated_by = WCF_CA_COUPON_GENERATED_BY;
		$timestamp           = time();
		$post_type           = 'shop_coupon';
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, coupon_code, usage_limit, total_usaged, expiry_date FROM (
			    SELECT p.ID,
			    p.post_title AS coupon_code,
			    Max(CASE WHEN pm.meta_key = 'date_expires'    AND  p.`ID` = pm.`post_id` THEN pm.meta_value END) AS expiry_date,
			    Max(CASE WHEN pm.meta_key = 'usage_limit'     AND  p.`ID` = pm.`post_id` THEN pm.meta_value END) AS usage_limit,
			    Max(CASE WHEN pm.meta_key = 'usage_count'     AND  p.`ID` = pm.`post_id` THEN pm.meta_value END) AS total_usaged,

			    Max(CASE WHEN pm.meta_key = 'coupon_generated_by'     AND  p.`ID` = pm.`post_id` THEN pm.meta_value END) AS coupon_generated_by
			    FROM   {$wpdb->prefix}posts AS p
			    INNER JOIN {$wpdb->prefix}postmeta AS pm ON  p.ID = pm.post_id
				WHERE  p.`post_type` = %s

		  		GROUP BY p.ID
 			) AS final_res WHERE coupon_generated_by IS NOT NULL AND coupon_generated_by = %s AND ( ( usage_limit = total_usaged ) OR ( expiry_date <= %d AND expiry_date != '') )",
				$post_type,
				$coupon_generated_by,
				$timestamp
			)
		);
	}

	/**
	 * AJAX handler for saving the new UI option.
	 * TODO: Remove this after new UI is enabled by default.
	 *
	 * @since 1.0.0
	 */
	public function save_new_ui_option() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'woo-cart-abandonment-recovery' ) );
		}

		if ( ! check_ajax_referer( 'wcf_ca_new_ui_nonce', 'security' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'woo-cart-abandonment-recovery' ) );
		}

		if ( ! isset( $_POST['value'] ) ) {
			wp_send_json_error( __( 'Required data missing.', 'woo-cart-abandonment-recovery' ) );
		}

		$value = sanitize_text_field( wp_unslash( $_POST['value'] ) );
		
		if ( ! in_array( $value, [ 'on', '' ], true ) ) {
			wp_send_json_error( __( 'Invalid value provided.', 'woo-cart-abandonment-recovery' ) );
		}

		// Save the option.
		$result = update_option( 'cartflows_ca_use_new_ui', $value );

		if ( $result ) {
			wp_send_json_success( __( 'Setting saved successfully.', 'woo-cart-abandonment-recovery' ) );
		} else {
			wp_send_json_error( __( 'Failed to save setting.', 'woo-cart-abandonment-recovery' ) );
		}
	}

}

Cartflows_Ca_Setting_Functions::get_instance();
