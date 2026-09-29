<?php
/**
 * Formatting API
 *
 * WooSingle product pages
 *  update variable values - call to action, prefilled...
 *
 * @since 3.4
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Replace WooCommerce single-product placeholders with live data.
 *
 * Retrieves product details and substitutes placeholders ({product}, {price}, {regular_price}, {sku}).
 *
 * @since 3.4
 * @param string $value Input value containing placeholders.
 * @return string String with placeholders replaced by product data.
 */
if ( ! function_exists( 'ht_ctc_woo_single_product_page_variables' ) ) {

	/**
	 * Replace WooCommerce single-product placeholders with live data.
	 *
	 * @param string $value Input value containing placeholders.
	 * @return string String with placeholders replaced by product data.
	 */
	function ht_ctc_woo_single_product_page_variables( $value ) {

		// if woocommerce single product page
		if ( function_exists( 'is_product' ) && function_exists( 'wc_get_product' ) ) {
			if ( is_product() ) {

				$product = wc_get_product();

				$name = esc_attr( $product->get_name() );
				// $title = $product->get_title();
				$price           = esc_attr( $product->get_price() );
				$regular_price   = esc_attr( $product->get_regular_price() );
				$sku             = esc_attr( $product->get_sku() );
				$price_formatted = '';

				// Ensure price is not empty or null to prevent displaying "0.00". If wc_price() is used, it may return a default "0.00" when no price is set.
				if ( '' !== $price && null !== $price ) {
					if ( function_exists( 'wc_price' ) ) {
						/**
						 * Get thousand separator, decimal separator, currency symbol
						 *
						 * The wc_price() function returns the formatted price with HTML tags.
						 * Use strip_tags() to remove HTML and html_entity_decode() to display currency symbols correctly.
						 */
						$price_formatted = html_entity_decode( wp_strip_all_tags( wc_price( $price ) ) );
						$price_formatted = esc_attr( $price_formatted );
					} else {
						$price_formatted = esc_attr( $price ); // Use raw price if wc_price() is unavailable.
					}
				} else {
					$price_formatted = ''; // Keep output blank if no price is set.
				}

				// variables works in default pre_filled also for woo pages.
				$value = str_replace( array( '{product}', '{{price}}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price_formatted, $price, $regular_price, $sku ), $value );
			}
		}

		return $value;
	}
}
