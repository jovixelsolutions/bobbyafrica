<?php
/**
 * Plugin Name: Modern Cart Starter for WooCommerce
 * Description: The Modern Cart Starter enhances your WooCommerce cart with more features and customization options.
 * Author: CartFlows
 * Author URI: https://cartflows.com/
 * Version: 1.0.9
 * License: GPL v2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: modern-cart
 * WC requires at least: 3.0
 * WC tested up to: 9.8.4
 *
 * Requires Plugins: woocommerce
 *
 * @package modern-cart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checks if it is safe to initialize the Modern Cart Starter plugin.
 *
 * This function determines whether the Modern Cart Starter plugin can be safely initialized
 * based on the presence and version of the Modern Cart Pro plugin. If the Pro plugin is not
 * installed, or if it is installed and its version is 1.2.0 or higher, it is considered safe
 * to initialize the Starter plugin.
 *
 * @return bool True if safe to initialize, false otherwise.
 */
function moderncart_is_safe_to_init() {
	// Ensure the get_file_data function is available.
	if ( ! function_exists( 'get_file_data' ) ) {
		require_once ABSPATH . 'wp-includes/functions.php';
	}

	$modern_cart_woo_file = untrailingslashit( WP_PLUGIN_DIR ) . '/modern-cart-woo/modern-cart-woo.php';

	if ( ! file_exists( $modern_cart_woo_file ) ) {
		// Pro plugin is not installed on this site.
		return true;
	}

	// Retrieve the version of the Pro plugin, if installed.
	$pro_plugin_data    = get_file_data(
		$modern_cart_woo_file,
		[ 'Version' => 'Version' ],
		'plugin'
	);
	$pro_plugin_version = $pro_plugin_data['Version'];

	if ( version_compare( $pro_plugin_version, '1.2.0', '>=' ) ) {
		// Pro plugin version is 1.2.0 or higher; safe to initialize.
		return true;
	}

	// Pro plugin is installed but version is less than 1.2.0; not safe to initialize.
	return false;
}

if ( ! moderncart_is_safe_to_init() ) {
	add_action(
		'admin_notices',
		function() {
			?>
		<div class="notice notice-error">
			<p>
				<strong><?php esc_html_e( 'Modern Cart for WooCommerce: Update Required', 'modern-cart' ); ?></strong><br>
			<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s and %2$s are opening and closing <strong> tags for emphasis. */
						__( 'You\'re using an older version of %1$sModern Cart for WooCommerce%2$s. Please update to version %1$s1.2.0 or higher%2$s so it works smoothly with %1$sModern Cart Starter%2$s.', 'modern-cart' ),
						'<strong>',
						'</strong>'
					)
				);
			?>
			</p>
		</div>
			<?php
		} 
	);

	// Return from here early and stop the further execution, this will make sure that user don't get any fatal errors due to version incompatibility.
	return;
}


/**
 * Set constants
 */
if ( ! defined( 'MODERNCART_FILE' ) ) {
	define( 'MODERNCART_FILE', __FILE__ );
}
if ( ! defined( 'MODERNCART_BASE' ) ) {
	define( 'MODERNCART_BASE', plugin_basename( MODERNCART_FILE ) );
}
if ( ! defined( 'MODERNCART_DIR' ) ) {
	define( 'MODERNCART_DIR', (string) plugin_dir_path( MODERNCART_FILE ) );
}
if ( ! defined( 'MODERNCART_URL' ) ) {
	define( 'MODERNCART_URL', plugins_url( '/', MODERNCART_FILE ) );
}
if ( ! defined( 'MODERNCART_PLUGIN_PATH' ) ) {
	define( 'MODERNCART_PLUGIN_PATH', untrailingslashit( MODERNCART_DIR ) );
}
if ( ! defined( 'MODERNCART_VER' ) ) {
	define( 'MODERNCART_VER', '1.0.9' );
}
if ( ! defined( 'MODERNCART_MAIN_SETTINGS' ) ) {
	define( 'MODERNCART_MAIN_SETTINGS', 'moderncart_setting' );
}
if ( ! defined( 'MODERNCART_SETTINGS' ) ) {
	define( 'MODERNCART_SETTINGS', 'moderncart_cart' );
}
if ( ! defined( 'MODERNCART_FLOATING_SETTINGS' ) ) {
	define( 'MODERNCART_FLOATING_SETTINGS', 'moderncart_floating' );
}
if ( ! defined( 'MODERNCART_APPEARANCE_SETTINGS' ) ) {
	define( 'MODERNCART_APPEARANCE_SETTINGS', 'moderncart_appearance' );
}
if ( ! defined( 'MODERNCART_ONBOARDING_USER_SUB_WORKFLOW_URL' ) ) {
	define( 'MODERNCART_ONBOARDING_USER_SUB_WORKFLOW_URL', 'https://webhook.ottokit.com/ottokit/82404eb3-7629-4965-9c28-8a1724c7f332' );
}
if ( ! defined( 'CARTFLOWS_DOMAIN_URL' ) ) {
	define( 'CARTFLOWS_DOMAIN_URL', 'https://cartflows.com' );
}
if ( ! defined( 'CARTFLOWS_API_ROUTE' ) ) {
	define( 'CARTFLOWS_API_ROUTE', '/wp-json/powerful-docs/v1' );
}
if ( ! defined( 'CARTFLOWS_DOCS_ENDPOINT' ) ) {
	define( 'CARTFLOWS_DOCS_ENDPOINT', '/get-docs' );
}

if ( ! defined( 'MODERNCART_DEBUG' ) ) {
	define( 'MODERNCART_DEBUG', false );
}

require_once 'inc/functions.php';
require_once 'plugin-loader.php';
