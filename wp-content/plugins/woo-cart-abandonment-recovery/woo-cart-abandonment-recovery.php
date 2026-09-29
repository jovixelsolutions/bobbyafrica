<?php
/**
 * Plugin Name: Cart Abandonment Recovery for WooCommerce
 * Plugin URI: https://cartflows.com/
 * Description: Recover your lost revenue. Capture email address of users on the checkout page and send follow up emails if they don't complete the purchase.
 * Version: 2.1.1
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Text Domain: woo-cart-abandonment-recovery
 * WC requires at least: 3.0
 * WC tested up to: 9.8.5
 * Requires Plugins: woocommerce
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

/**
 * Set constants.
 */
define( 'CARTFLOWS_CA_FILE', __FILE__ );

/**
 * Loader
 */
require_once 'classes/class-cartflows-ca-loader.php';
