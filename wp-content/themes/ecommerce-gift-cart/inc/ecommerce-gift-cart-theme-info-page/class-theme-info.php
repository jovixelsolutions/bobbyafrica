<?php
/**
 * Theme Info Page
 *
 * @package Ecommerce Gift Cart
 */

function ecommerce_gift_cart_theme_details() {
	add_theme_page( 'Themes', 'Ecommerce Gift Cart Theme', 'edit_theme_options', 'ecommerce-gift-cart-theme-info-page', 'theme_details_display', null );
}
add_action( 'admin_menu', 'ecommerce_gift_cart_theme_details' );

function theme_details_display() {

	include_once 'templates/theme-details.php';

}

add_action( 'admin_enqueue_scripts', 'ecommerce_gift_cart_theme_details_style' );

function ecommerce_gift_cart_theme_details_style() {
    wp_register_style( 'ecommerce_gift_cart_theme_details_css', get_template_directory_uri() . '/inc/ecommerce-gift-cart-theme-info-page/css/theme-details.css', false, '1.0.0' );
    wp_enqueue_style( 'ecommerce_gift_cart_theme_details_css' );
}