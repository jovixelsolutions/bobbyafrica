<?php
/**
 * CartFlows Learn Data Query.
 *
 * @package CartFlows
 */

namespace CartflowsAdmin\AdminLegacyCore\Api;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use CartflowsAdmin\AdminLegacyCore\Api\ApiBase;

/**
 * Class Learn.
 */
class Learn extends ApiBase {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '/admin/learn/';

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class object.
	 * @since 2.2.2
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 2.2.2
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Init Hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_routes() {

		$namespace = $this->get_api_namespace();

		register_rest_route(
			$namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_learn_sections' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(),
				),
			)
		);
	}


	/**
	 * Get learn sections.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return \WP_REST_Response
	 */
	public function get_learn_sections( $request ) {

		// Get completed modules from option.
		$completed_modules = get_option( 'wcf_learn_data', array() );
		$completed_modules = is_array( $completed_modules ) ? $completed_modules : array();

		$sections = array(
			array(
				'id'          => 'funnel-basics',
				'title'       => __( 'Funnel Basics', 'cartflows' ),
				'description' => __( 'Build a solid foundation for your first funnel.', 'cartflows' ),
				'modules'     => array(
					array(
						'id'            => 'install-woocommerce',
						'title'         => __( 'Install WooCommerce', 'cartflows' ),
						'description'   => __( 'Install WooCommerce to enable products, checkout, and payments.', 'cartflows' ),
						'cta'           => __( 'WooCommerce', 'cartflows' ),
						'action'        => 'Installs WooCommerce Instantly',
						'action_type'   => 'install_plugin',
						'action_data'   => array(
							'plugin_slug' => 'woocommerce',
							'plugin_init' => 'woocommerce/woocommerce.php',
						),
						'learn_how'     => false,
						'is_pro'        => false,
						'completed'     => $this->is_module_completed( 'install-woocommerce', 'active' === \Cartflows_Helper::get_plugin_status( 'woocommerce/woocommerce.php', true ), $completed_modules ),
						'plugin_status' => \Cartflows_Helper::get_plugin_status( 'woocommerce/woocommerce.php', true ),
					),
					array(
						'id'          => 'create-your-first-funnel',
						'title'       => __( 'Create Your First Funnel', 'cartflows' ),
						'description' => __( 'Start by creating a funnel and selecting a ready-made template.', 'cartflows' ),
						'cta'         => __( 'Create Funnel', 'cartflows' ),
						'action'      => 'Redirects to Funnel Templates Library',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=cartflows&path=flows' ),
						),
						'learn_how'   => esc_url( 'https://cartflows.com/docs/how-to-create-your-first-cartflows-funnel/?utm_source=dashboard&utm_medium=cartflows&utm_campaign=learn_how' ),
						'is_pro'      => false,
						'completed'   => $this->is_module_completed( 'create-your-first-funnel', intval( wp_count_posts( CARTFLOWS_FLOW_POST_TYPE )->publish ) > 0, $completed_modules ),
					),
				),
			),
			array(
				'id'          => 'design-customize-pages',
				'title'       => __( 'Design & Customize Pages', 'cartflows' ),
				'description' => __( 'Make your funnel pages match your brand and goals.', 'cartflows' ),
				'modules'     => array(
					array(
						'id'          => 'edit-design-funnel-pages-steps',
						'title'       => __( 'Edit & Design Funnel Pages/Steps', 'cartflows' ),
						'description' => __( 'Customize your landing, checkout, and thank you pages using your preferred page builder.', 'cartflows' ),
						'cta'         => __( 'Edit Steps', 'cartflows' ),
						'action'      => 'Redirects to Funnel Editor',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=cartflows&path=flows' ),
						),
						'learn_how'   => esc_url( 'https://cartflows.com/docs/editing-and-customising-funnel-steps/?utm_source=dashboard&utm_medium=cartflows&utm_campaign=learn_how' ),
						'is_pro'      => false,
						'completed'   => $this->is_module_completed( 'edit-design-funnel-pages-steps', $this->has_step_been_edited(), $completed_modules ),
					),
				),
			),
			array(
				'id'          => 'setup-products',
				'title'       => __( 'Setup Products', 'cartflows' ),
				'description' => __( 'Add and manage the products you\'ll sell through your funnels.', 'cartflows' ),
				'modules'     => array(
					array(
						'id'          => 'add-products',
						'title'       => __( 'Add Products', 'cartflows' ),
						'description' => __( 'Create or import products to use in your CartFlows funnel.', 'cartflows' ),
						'cta'         => __( 'Add Products', 'cartflows' ),
						'action'      => 'Redirects to WooCommerce > Products',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'edit.php?post_type=product' ),
						),
						'learn_how'   => esc_url( 'https://cartflows.com/docs/how-to-add-products-in-woocommerce/?utm_source=dashboard&utm_medium=cartflows&utm_campaign=learn_how' ),
						'is_pro'      => false,
						'completed'   => $this->is_module_completed( 'add-products', intval( wp_count_posts( 'product' )->publish ) > 0, $completed_modules ),
					),
					array(
						'id'          => 'assign-products-to-checkout',
						'title'       => __( 'Assign Products to Checkout', 'cartflows' ),
						'description' => __( 'Attach products to your checkout step and control pricing & quantity.', 'cartflows' ),
						'cta'         => __( 'Select Product/s', 'cartflows' ),
						'action'      => 'Redirects to Funnel > Checkout Step > Products',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=cartflows&path=flows' ),
						),
						'learn_how'   => esc_url( 'https://cartflows.com/docs/how-to-add-assign-products-to-a-checkout-step-in-cartflows/?utm_source=dashboard&utm_medium=cartflows&utm_campaign=learn_how' ),
						'is_pro'      => false,
						'completed'   => $this->is_module_completed( 'assign-products-to-checkout', $this->is_checkout_product_is_assigned(), $completed_modules ),
					),
				),
			),
			array(
				'id'          => 'setup-payments',
				'title'       => __( 'Setup Payments', 'cartflows' ),
				'description' => __( 'Accept payments securely with your preferred gateways.', 'cartflows' ),
				'modules'     => array(
					array(
						'id'          => 'connect-payment-gateway',
						'title'       => __( 'Connect Payment Gateway', 'cartflows' ),
						'description' => __( 'Set up Stripe, PayPal, or other WooCommerce-supported payment methods.', 'cartflows' ),
						'cta'         => __( 'Setup Payments', 'cartflows' ),
						'action'      => 'Redirects to WooCommerce > Payments',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
						),
						'learn_how'   => false,
						'is_pro'      => false,
						'completed'   => $this->is_module_completed( 'connect-payment-gateway', $this->check_supported_payment_gateway_used(), $completed_modules ),
					),
				),
			),
			array(
				'id'          => 'setup-cart-abandonment-recovery',
				'title'       => __( 'Setup Cart Abandonment Recovery', 'cartflows' ),
				'description' => __( 'Recover lost revenue from incomplete checkouts.', 'cartflows' ),
				'modules'     => array(
					array(
						'id'            => 'enable-cart-abandonment-tracking',
						'title'         => __( 'Enable Cart Abandonment Tracking', 'cartflows' ),
						'description'   => __( 'Start tracking abandoned carts automatically.', 'cartflows' ),
						'cta'           => __( 'Cart Abandonment Recovery', 'cartflows' ),
						'action'        => 'Installs CAR Free Instantly',
						'action_type'   => 'install_plugin',
						'action_data'   => array(
							'plugin_slug' => 'woo-cart-abandonment-recovery',
							'plugin_init' => 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php',
						),
						'learn_how'     => false,
						'is_pro'        => false,
						'completed'     => $this->is_module_completed( 'enable-cart-abandonment-tracking', 'active' === \Cartflows_Helper::get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php', true ), $completed_modules ),
						'plugin_status' => \Cartflows_Helper::get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php', true ),
					),
					array(
						'id'          => 'setup-recovery-emails',
						'title'       => __( 'Setup Recovery Emails', 'cartflows' ),
						'description' => __( 'Edit and setup recovery emails to start recovering lost sales automatically.', 'cartflows' ),
						'cta'         => __( 'Setup Emails', 'cartflows' ),
						'action'      => 'Redirects to CAR > Follow Ups',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=woo-cart-abandonment-recovery&path=follow-up-templates' ),
						),
						'learn_how'   => false,
						'is_pro'      => false,
						'completed'   => $this->is_module_completed( 'setup-recovery-emails', $this->check_folloup_emails_created(), $completed_modules ),
					),
				),
			),
			array(
				'id'          => 'modernise-your-cart',
				'title'       => __( 'Modernise Your Cart', 'cartflows' ),
				'description' => __( 'Deliver a faster, cleaner, and more conversion-focused cart experience.', 'cartflows' ),
				'modules'     => array(
					array(
						'id'            => 'enable-modern-cart',
						'title'         => __( 'Enable Modern Cart', 'cartflows' ),
						'description'   => __( 'Switch to the modern cart layout for better UX and performance.', 'cartflows' ),
						'cta'           => __( 'Modern Cart', 'cartflows' ),
						'action'        => 'Installs MCW Free Instantly',
						'action_type'   => 'install_plugin',
						'action_data'   => array(
							'plugin_slug' => 'modern-cart',
							'plugin_init' => 'modern-cart/modern-cart.php',
						),
						'learn_how'     => false,
						'is_pro'        => false,
						'completed'     => $this->is_module_completed( 'enable-modern-cart', 'active' === \Cartflows_Helper::get_plugin_status( 'modern-cart/modern-cart.php', true ), $completed_modules ),
						'plugin_status' => \Cartflows_Helper::get_plugin_status( 'modern-cart/modern-cart.php', true ),
					),
					array(
						'id'          => 'setup-your-cart',
						'title'       => __( 'Setup Your Cart', 'cartflows' ),
						'description' => __( 'Set up and customize your cart settings to match your brand style.', 'cartflows' ),
						'cta'         => __( 'Setup Cart', 'cartflows' ),
						'action'      => 'Redirects to MCW > Settings',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=moderncart_settings' ),
						),
						'learn_how'   => false,
						'is_pro'      => false,
						'completed'   => $this->is_module_completed( 'setup-your-cart', $this->is_modern_cart_configured(), $completed_modules ),
					),
				),
			),
			array(
				'id'          => 'setup-offers',
				'title'       => __( 'Setup Offers', 'cartflows' ),
				'description' => __( 'Increase order value with smart one-click offers.', 'cartflows' ),
				'modules'     => array(
					array(
						'id'          => 'add-order-bump',
						'title'       => __( 'Add Order Bump', 'cartflows' ),
						'description' => __( 'Offer complementary products directly on the checkout page.', 'cartflows' ),
						'cta'         => __( 'Add Order Bump', 'cartflows' ),
						'action'      => 'Redirects to Funnel > Checkout Step > Order Bump',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=cartflows&path=flows' ),
						),
						'learn_how'   => esc_url( 'https://cartflows.com/docs/how-to-add-order-bumps-to-woocommerce-sales-funnel/?utm_source=dashboard&utm_medium=cartflows&utm_campaign=learn_how' ),
						'is_pro'      => ! _is_cartflows_pro(),
						'completed'   => $this->is_module_completed( 'add-order-bump', $this->has_published_order_bump(), $completed_modules ),
					),
					array(
						'id'          => 'setup-upsell-downsell-offers',
						'title'       => __( 'Setup Upsell / Downsell Offers', 'cartflows' ),
						'description' => __( 'Create one-click post-checkout offers to boost revenue.', 'cartflows' ),
						'cta'         => __( 'Add Offer Step', 'cartflows' ),
						'action'      => 'Redirects to Funnel > Checkout Step > Order Bump',
						'action_type' => 'redirect',
						'action_data' => array(
							'url' => admin_url( 'admin.php?page=cartflows&path=flows' ),
						),
						'learn_how'   => esc_url( 'https://cartflows.com/docs/how-to-create-one-click-upsell-and-downsell-offers-in-cartflows/?utm_source=dashboard&utm_medium=cartflows&utm_campaign=learn_how' ),
						'is_pro'      => ! _is_cartflows_pro(),
						'completed'   => $this->is_module_completed( 'setup-upsell-downsell-offers', $this->has_published_offer_step(), $completed_modules ),
					),
				),
			),
		);

		$response = new \WP_REST_Response( $sections );
		$response->set_status( 200 );

		return $response;
	}

	/**
	 * Check whether a module is completed.
	 *
	 * @param string $module_id         Module identifier.
	 * @param bool   $is_auto_completed Whether the module is auto-completed.
	 * @param array  $completed_modules Stored list of completed modules.
	 *
	 * @return bool Whether the module should be treated as completed.
	 */
	private function is_module_completed( $module_id, $is_auto_completed, $completed_modules ) {
		
		if ( $is_auto_completed ) {
			return true;
		}

		return in_array( $module_id, $completed_modules, true );
	}

	/**
	 * Checks if any checkout step has a product assigned.
	 *
	 * Iterates through recent checkout steps and determines
	 * if at least one has an assigned product in the 'wcf-checkout-products' meta.
	 *
	 * @since 2.2.2
	 *
	 * @return bool True if a checkout step has a product assigned, otherwise false.
	 */
	public function is_checkout_product_is_assigned() {

		// Fetch the 10 most recently modified published checkout steps that have the 'wcf-checkout-products' meta key.
		$steps = get_posts(
			array(
				'post_type'      => CARTFLOWS_STEP_POST_TYPE, // Only 'cartflows_step' post type.
				'post_status'    => array( 'publish' ), // Only published posts.
				'posts_per_page' => 10, // Limit to 10 results for performance.
				'orderby'        => 'modified', // Order by last modified date.
				'order'          => 'DESC', // Most recently modified first.
				'fields'         => 'ids', // Only retrieve post IDs for efficiency.
				'meta_query'     => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => 'wcf-step-type', // Must be a checkout step.
						'value' => 'checkout',
					),
					array(
						'key'     => 'wcf-checkout-products', // Step must have checkout products assigned.
						'compare' => 'EXISTS',
					),
				),
			)
		);

		// If no eligible steps found, return false.
		if ( empty( $steps ) ) {
			return false;
		}

		// Loop through each checkout step.
		foreach ( $steps as $step_id ) {
			// Get assigned products for current step.
			$products = get_post_meta( $step_id, 'wcf-checkout-products', true );

			// Skip if there are no products or the first product entry is empty.
			if ( ! is_array( $products ) || empty( $products ) || empty( $products[0]['product'] ) ) {
				continue;
			}

			// Get the product ID of the first assigned product.
			$product_id = $products[0]['product'];

			// Check if a valid numeric product ID is assigned.
			if ( is_numeric( $product_id ) && (int) $product_id > 0 ) {
				return true; // A product has been assigned to at least one checkout step.
			}
		}

		// If no checkout step with a valid product was found, return false.
		return false;
	}

	/**
	 * Check if a configured WooCommerce payment gateway is available.
	 *
	 * For the learn step we only need to know whether the store has at least one
	 * payment gateway plugin that is installed, configured, and enabled in either
	 * live or sandbox/test mode. WooCommerce's
	 * WC_Payment_Gateways::get_available_payment_gateways() already returns only
	 * gateways that are enabled and ready to use, so we can rely on that.
	 *
	 * @since 2.2.2
	 *
	 * @return bool True if at least one gateway is available, otherwise false.
	 */
	public function check_supported_payment_gateway_used() {

		// Ensure WooCommerce is active before checking gateways.
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}

		$payment_gateways = WC()->payment_gateways();

		if ( ! is_object( $payment_gateways ) || ! method_exists( $payment_gateways, 'get_available_payment_gateways' ) ) {
			return false;
		}

		// Get gateways that are installed, configured, and enabled (live or sandbox).
		$available_gateways = $payment_gateways->get_available_payment_gateways();

		if ( empty( $available_gateways ) || ! is_array( $available_gateways ) ) {
			return false;
		}

		// If at least one gateway is available, consider the requirement satisfied.
		return true;
	}

	/**
	 * Check if the user has created and activated follow-up recovery emails.
	 *
	 * First verifies that the Woo Cart Abandonment Recovery plugin is active.
	 * If it is, queries the plugin's email templates table to confirm at least
	 * one template exists with `is_activated = 1`.
	 *
	 * @since 2.2.2
	 *
	 * @return bool True if at least one active follow-up email template exists, otherwise false.
	 */
	public function check_folloup_emails_created() {

		// Bail early if the Cart Abandonment Recovery plugin is not active.
		if ( 'active' !== \Cartflows_Helper::get_plugin_status( 'woo-cart-abandonment-recovery/woo-cart-abandonment-recovery.php', true ) ) {
			return false;
		}

		global $wpdb;

		$template_table = $wpdb->prefix . 'cartflows_ca_email_templates';

		// Check whether the table exists before querying it.
		$table_exists = $wpdb->get_var( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( 'SHOW TABLES LIKE %s', $template_table )
		);

		if ( ! $table_exists ) {
			return false;
		}

		// Count active follow-up email templates.
		$active_count = (int) $wpdb->get_var( //phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}cartflows_ca_email_templates WHERE is_activated = %d", //phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				1
			)
		);

		return $active_count > 0;
	}

	/**
	 * Check if any published checkout step has at least one order bump configured.
	 *
	 * Queries published checkout steps for the presence of the 'wcf-order-bumps'
	 * meta key. Only post IDs are retrieved for performance.
	 *
	 * @since 2.2.2
	 *
	 * @return bool True if at least one published checkout step has an order bump, otherwise false.
	 */
	private function has_published_order_bump() {

		// Look for any published checkout step that has an order bump configured.
		$steps = get_posts(
			array(
				'post_type'      => CARTFLOWS_STEP_POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => 'wcf-step-type',
						'value' => 'checkout',
					),
					array(
						'key'     => 'wcf-order-bumps',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		return ! empty( $steps );
	}

	/**
	 * Check if any published offer step (upsell or downsell) exists.
	 *
	 * Queries published steps whose 'wcf-step-type' is 'upsell' or 'downsell'.
	 * Only post IDs are retrieved for performance.
	 *
	 * @since 2.2.2
	 *
	 * @return bool True if at least one published upsell or downsell step exists, otherwise false.
	 */
	private function has_published_offer_step() {

		// Look for any published upsell or downsell step.
		$offer_steps = get_posts(
			array(
				'post_type'      => CARTFLOWS_STEP_POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_query'     => array( //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => 'wcf-step-type',
						'value'   => array( 'upsell', 'downsell' ),
						'compare' => 'IN',
					),
				),
			)
		);

		return ! empty( $offer_steps );
	}

	/**
	 * Check if any published CartFlows step exists.
	 *
	 * @since 2.2.2
	 *
	 * @return bool True if at least one published step exists, otherwise false.
	 */
	private function has_step_been_edited() {
		$steps = get_posts(
			array(
				'post_type'      => CARTFLOWS_STEP_POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		return ! empty( $steps );
	}

	/**
	 * Check if the Modern Cart plugin is active and has been configured by the user.
	 *
	 * Modern Cart only writes its settings options to the database when the user
	 * explicitly saves the settings page. A fresh install has none of these options,
	 * so their presence reliably indicates that the user has configured the plugin.
	 *
	 * @since 2.2.2
	 *
	 * @return bool True if Modern Cart is active and at least one settings option exists, otherwise false.
	 */
	private function is_modern_cart_configured() {

		if ( 'active' !== \Cartflows_Helper::get_plugin_status( 'modern-cart/modern-cart.php', true ) ) {
			return false;
		}

		$setting_keys = array( 'moderncart_setting', 'moderncart_cart', 'moderncart_floating', 'moderncart_appearance' );

		foreach ( $setting_keys as $key ) {
			if ( false !== get_option( $key, false ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check whether a given request has permission to read notes.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return \WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {

		if ( ! current_user_can( 'cartflows_manage_settings' ) ) {
			return new \WP_Error( 'cartflows_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cartflows' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}
}
