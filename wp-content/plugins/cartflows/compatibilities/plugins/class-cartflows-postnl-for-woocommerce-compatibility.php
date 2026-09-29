<?php
/**
 * Thrive Visual Editor Compatibility
 *
 * @package CartFlows
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Class for Thrive Visual Editor Compatibility
 */
class Cartflows_Postnl_For_WooCommerce_Compatibility {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Returns the single instance of the class.
	 *
	 * @return self
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
		add_action( 'wp', array( $this, 'remove_not_required_checkout_fields' ), 20 );
	}

	/**
	 * Remove not required checkout fields of PostNl plugin.
	 *
	 * @since 2.1.11
	 */
	public function remove_not_required_checkout_fields() {
		// Check if the current checkout type is CartFlows Checkout Form and PostNL plugin is active and required classes are available.
		if ( _is_wcf_checkout_type() && class_exists( 'PostNLWooCommerce\Shipping_Method\Settings' ) && class_exists( 'PostNLWooCommerce\Frontend\Checkout_Fields' ) ) {
			
			// Get the object of settings class.
			$settings = PostNLWooCommerce\Shipping_Method\Settings::get_instance();
			
			// check is the reorder of address fields are enabled or not.
			if ( $settings->is_reorder_nl_address_enabled() ) {
				// Remove the house number field action.
				$this->remove_class_filter( 'woocommerce_default_address_fields', 'PostNLWooCommerce\Frontend\Checkout_Fields', 'add_house_number' );
				// Remove the country locale restriction.
				$this->remove_class_filter( 'woocommerce_get_country_locale', 'PostNLWooCommerce\Frontend\Checkout_Fields', 'get_country_locale' );
				// Remove the reordering of fields based on address fields.
				$this->remove_class_filter( 'woocommerce_country_locale_field_selectors', 'PostNLWooCommerce\Frontend\Checkout_Fields', 'country_locale_field_selectors' );
			}
		}
	}

	/**
	 * Function to remove the filters.
	 *
	 * @param string $tag The filter tag.
	 * @param string $class_name The class name.
	 * @param string $method_name The method name.
	 * @param int    $priority The filter priority.
	 * @return bool True if the filter was removed, false otherwise.
	 * @since 2.1.11
	 */
	public function remove_class_filter( $tag, $class_name, $method_name, $priority = 10 ) {
		global $wp_filter;

		// Check if the filter tag is available in the global array.
		if ( ! isset( $wp_filter[ $tag ] ) || ! is_a( $wp_filter[ $tag ], 'WP_Hook' ) ) {
			return false;
		}
		// Check the callback and priority of it.
		$callbacks = isset( $wp_filter[ $tag ]->callbacks[ $priority ] ) ? $wp_filter[ $tag ]->callbacks[ $priority ] : array();
		
		// Search and find the hook and remove it from the array as we don't want to apply the settings on the CartFlows Checkout page.
		foreach ( $callbacks as $hook_key => $callback ) {
			if (
				is_array( $callback['function'] )
				&& is_object( $callback['function'][0] )
				&& get_class( $callback['function'][0] ) === $class_name
				&& $callback['function'][1] === $method_name
			) {
				// Remove the filter if callback and priority is found.
				remove_filter( $tag, $callback['function'], $priority );
				return true;
			}
		}
		// Return false if not found.
		return false;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Postnl_For_WooCommerce_Compatibility::get_instance();
