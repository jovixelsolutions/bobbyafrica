<?php
/**
 * Plugin Name: Ultimate Addons for Elementor (UAE)
 * Plugin URI:  https://wordpress.org/plugins/header-footer-elementor/
 * Description: Ultimate Addons is a powerful plugin allows you to create custom headers and footers with Elementor and display them in selected locations. You can also create custom Elementor blocks and place them anywhere on your website using a shortcode.
 * Author:      Brainstorm Force
 * Author URI:  https://www.brainstormforce.com/
 * Text Domain: header-footer-elementor
 * Domain Path: /languages
 * Version: 2.8.7
 * Elementor tested up to: 4.0
 * Elementor Pro tested up to: 4.0
 *
 * @package         header-footer-elementor
 */

define( 'HFE_VER', '2.8.7' );
define( 'HFE_FILE', __FILE__ );
define( 'HFE_DIR', plugin_dir_path( __FILE__ ) );
define( 'HFE_URL', plugins_url( '/', __FILE__ ) );
define( 'HFE_PATH', plugin_basename( __FILE__ ) );
define( 'HFE_DOMAIN', trailingslashit( 'https://ultimateelementor.com' ) );
define( 'UAE_LITE', true );

/**
 * Load the class loader.
 */
require_once HFE_DIR . '/inc/class-header-footer-elementor.php';

/**
 * Load the Plugin Class.
 *
 * @return void
 */
function hfe_plugin_activation() {
	update_option( 'uae_lite_is_activated', 'yes' );
	update_option( 'hfe_start_onboarding', true );

	// Track plugin activation event.
	require_once HFE_DIR . 'inc/class-hfe-analytics-events.php';
	$bsf_referrers = get_option( 'bsf_product_referers', [] );
	$source        = ! empty( $bsf_referrers['header-footer-elementor'] ) ? $bsf_referrers['header-footer-elementor'] : 'self';
	HFE_Analytics_Events::track( 'plugin_activated', HFE_VER, [ 'source' => $source ] );
}

register_activation_hook( HFE_FILE, 'hfe_plugin_activation' );

/**
 * Remove the Data not required.
 *
 * @return void
 */
function hfe_plugin_deactivation() {
	$timestamp = wp_next_scheduled( 'hfe_widgets_usage_cron' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'hfe_widgets_usage_cron' );
	}
}

// deActivation hook.
register_deactivation_hook( HFE_FILE, 'hfe_plugin_deactivation' );

/**
 * Load the Plugin Class.
 *
 * @return void
 */
function hfe_init() {
	Header_Footer_Elementor::instance();
}

add_action( 'plugins_loaded', 'hfe_init' );

/** Function for FA5, Social Icons, Icon List */
function hfe_enqueue_font_awesome() {

	if ( class_exists( 'Elementor\Plugin' ) ) {

		// Ensure Elementor Icons CSS is loaded.
		wp_enqueue_style(
			'hfe-elementor-icons',
			plugins_url( '/elementor/assets/lib/eicons/css/elementor-icons.min.css', 'elementor' ),
			[],
			'5.34.0'
		);
		wp_enqueue_style(
			'hfe-icons-list',
			plugins_url( '/elementor/assets/css/widget-icon-list.min.css', 'elementor' ),
			[],
			'3.24.3'
		);
		wp_enqueue_style(
			'hfe-social-icons',
			plugins_url( '/elementor/assets/css/widget-social-icons.min.css', 'elementor' ),
			[],
			'3.24.0'
		);
		wp_enqueue_style(
			'hfe-social-share-icons-brands',
			plugins_url( '/elementor/assets/lib/font-awesome/css/brands.css', 'elementor' ),
			[],
			'5.15.3'
		);

		wp_enqueue_style(
			'hfe-social-share-icons-fontawesome',
			plugins_url( '/elementor/assets/lib/font-awesome/css/fontawesome.css', 'elementor' ),
			[],
			'5.15.3'
		);
		wp_enqueue_style(
			'hfe-nav-menu-icons',
			plugins_url( '/elementor/assets/lib/font-awesome/css/solid.css', 'elementor' ),
			[],
			'5.15.3'
		);
	}
	if ( class_exists( '\ElementorPro\Plugin' ) ) {
		wp_enqueue_style(
			'hfe-widget-blockquote',
			plugins_url( '/elementor-pro/assets/css/widget-blockquote.min.css', 'elementor' ),
			[],
			'3.25.0'
		);
		wp_enqueue_style(
			'hfe-mega-menu',
			plugins_url( '/elementor-pro/assets/css/widget-mega-menu.min.css', 'elementor' ),
			[],
			'3.26.2'
		);
		wp_enqueue_style(
			'hfe-nav-menu-widget',
			plugins_url( '/elementor-pro/assets/css/widget-nav-menu.min.css', 'elementor' ),
			[],
			'3.26.0'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hfe_enqueue_font_awesome', 20 );
