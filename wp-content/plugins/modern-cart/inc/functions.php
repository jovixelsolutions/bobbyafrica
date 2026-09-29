<?php
/**
 * Plugin functions.
 *
 * @package modern-cart
 * @since 0.0.1
 */

use ModernCart\Inc\Helper;

if ( ! function_exists( 'moderncart_get_template_part' ) ) {

	/**
	 * Get template part implementation for wedocs.
	 *
	 * @since 0.0.1
	 *
	 * @param string              $slug Template slug.
	 * @param string              $name Template name.
	 * @param array<string,mixed> $args Template passing data.
	 * @param bool                $return Flag for retun with ob_start.
	 *
	 * @return string Return html file.
	 */
	function moderncart_get_template_part( $slug, $name = '', $args = [], $return = false ) {
		$defaults = [
			'pro' => false,
		];

		$args = apply_filters(
			'moderncart_get_template_part_args',
			wp_parse_args( $args, $defaults ),
			compact( 'slug', 'name' )
		);

		// Ensure $args is a valid array and remove keys that could overwrite critical local variables.
		if ( ! is_array( $args ) ) {
			$args = [];
		}
		unset( $args['template'], $args['slug'], $args['name'], $args['return'], $args['template_path'], $args['defaults'] );

		$template = '';

		// Look in yourtheme/modern-cart/slug-name.php and yourtheme/modern-cart/slug.php.
		$template_path = ! empty( $name ) ? "{$slug}-{$name}.php" : "{$slug}.php";
		$template      = locate_template( [ 'modern-cart/' . $template_path ] );

		/**
		 * Change template directory path filter.
		 *
		 * @since 0.0.1
		 */
		$template_path = apply_filters(
			'moderncart_set_template_path',
			MODERNCART_PLUGIN_PATH . '/templates',
			compact(
				'slug',
				'name',
				'template',
				'args'
			)
		);

		// Get default slug-name.php.
		if ( ! $template && $name && file_exists( $template_path . "/{$slug}-{$name}.php" ) ) {
			$template = $template_path . "/{$slug}-{$name}.php";
		}

		if ( ! $template && ! $name && file_exists( $template_path . "/{$slug}.php" ) ) {
			$template = $template_path . "/{$slug}.php";
		}

		// Allow 3rd party plugin filter template file from their plugin.
		$template = apply_filters( 'moderncart_get_template_part', $template, $slug, $name );

		if ( ! $template ) {
			return '';
		}

		// Safe extraction: EXTR_SKIP prevents overwriting any existing local variables ($template, $slug, $name, etc.).
		if ( ! empty( $args ) && is_array( $args ) ) {
			extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- EXTR_SKIP is used with a blocklist of critical keys.
		}

		if ( $return ) {
			ob_start();
			require $template;
			$content = ob_get_clean();
			return Helper::convert_to_string( $content );
		}

		require $template;
		return '';
	}
}

if ( ! function_exists( 'moderncart_cart_item_thumbnail' ) ) {
	/**
	 * Returns cart item thumbnail.
	 *
	 * @since 0.0.1
	 *
	 * @param \WC_Product $product WooCommerce product.
	 * @param string      $cart_item WooCommerce cart item.
	 * @param string      $cart_item_key WooCommerce cart item key.
	 *
	 * @return mixed|void
	 */
	function moderncart_cart_item_thumbnail( &$product, &$cart_item, $cart_item_key ) {
		return apply_filters(
			'moderncart_woocommerce_cart_item_thumbnail',
			$product->get_image(),
			$cart_item,
			$cart_item_key
		);
	}
}

if ( ! function_exists( 'moderncart_cart_item_price' ) ) {
	/**
	 * Returns the price of an item in the cart.
	 *
	 * @since 0.0.1
	 *
	 * @param \WC_Product   $product WooCommerce product.
	 * @param array<string> $cart_item WooCommerce cart item.
	 * @param string        $cart_item_key WooCommerce cart item key.
	 *
	 * @return mixed|void
	 */
	function moderncart_cart_item_price( &$product, &$cart_item, $cart_item_key ) {
		// Ensure cart totals are calculated so that plugins which set prices
		// dynamically via woocommerce_before_calculate_totals (e.g. gift cards,
		// name-your-price) have their prices reflected on the product objects.
		if ( 0 === did_action( 'woocommerce_before_calculate_totals' ) ) {
			WC()->cart->calculate_totals();
		}

		$price                 = '';
		$percentage            = '';
		$onsale                = $product->is_on_sale();
		$product_regular_price = floatval( Helper::convert_to_string( get_post_meta( $product->get_id(), '_regular_price', true ) ) );

		// Ensure custom price is set on the product object.
		if ( isset( $cart_item['custom_price'] ) ) {
			$product->set_price( $cart_item['custom_price'] );
		}

		// Get the current price (either custom or regular).
		$current_price = (float) $product->get_price();

		if ( ! empty( $product_regular_price ) && $current_price < $product_regular_price ) {
			$percentage = ( $product_regular_price - $current_price ) * 100 / $product_regular_price;
			$percentage = round( floatval( $percentage ), 2 );
		}

		$quantity = Helper::convert_to_int( $cart_item['quantity'] );

		// Add the price with proper ARIA attributes.

		/* translators: %s: product price */
		$price .= '<span aria-label="' . esc_attr( sprintf( __( 'Price: %s', 'modern-cart' ), wp_strip_all_tags( WC()->cart->get_product_subtotal( $product, $quantity ) ) ) ) . '">';
		$price .= apply_filters( 'moderncart_woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $product, $quantity ), $cart_item, $cart_item_key );
		$price .= '</span>';

		$on_sale_text = Helper::convert_to_string( ModernCart\Inc\Cart::Get_Instance()->get_option( 'on_sale_percentage_text', MODERNCART_SETTINGS, __( ' You saved {percent}%', 'modern-cart' ) ) );

		if ( $percentage && $onsale ) {
			if ( strpos( $on_sale_text, '{percent}' ) !== false ) {
				$savings_text = str_replace( '{percent}', Helper::convert_to_string( $percentage ), $on_sale_text );
			} else {
				/* translators: 1: sale text (e.g., "Save"), 2: percentage value (e.g., "20") */
				$savings_text = sprintf( esc_html__( '%1$s %2$s', 'modern-cart' ), esc_html( $on_sale_text ), esc_html( Helper::convert_to_string( $percentage ) ) );
				$savings_text = is_rtl() ? "%{$savings_text}" : "{$savings_text}%";
			}
			$price .= '<small class="" aria-label="' . esc_attr( $savings_text ) . '">' . esc_html( $savings_text ) . '</small>';
		}

		return $price;
	}
}

if ( ! function_exists( 'moderncart_get_quantity_from_cart_item' ) ) {
	/**
	 * Returns the quantity from the cart item.
	 *
	 * @since 0.0.1
	 *
	 * @param array<int> $cart_item WooCommerce cart item.
	 * @param string     $cart_item_key WooCommerce cart item key.
	 *
	 * @return int
	 */
	function moderncart_get_quantity_from_cart_item( &$cart_item, $cart_item_key ) {
		$default_qty = 1;
		if ( empty( $cart_item_key ) ) {
			return $default_qty;
		}
		return $cart_item['quantity'];
	}
}

if ( ! function_exists( 'moderncart_get_product_by_id' ) ) {
	/**
	 * Get the product object by product id.
	 *
	 * @since 0.0.1
	 *
	 * @param int $product_id WooCommerce product.
	 *
	 * @return \WC_Product|false|null
	 */
	function moderncart_get_product_by_id( $product_id ) {
		if ( ! apply_filters( 'moderncart_is_valid_product', true, $product_id ) ) {
			return false;
		}

		return apply_filters( 'moderncart_get_product_by_id', wc_get_product( $product_id ), $product_id );
	}
}

if ( ! function_exists( 'moderncart_implode_html_attributes' ) ) {
	/**
	 * Get the implode html attributes.
	 *
	 * @since 0.0.1
	 *
	 * @param array<string> $raw_attributes WooCommerce raw attributes.
	 *
	 * @return string
	 */
	function moderncart_implode_html_attributes( $raw_attributes ) {
		$attributes = [];
		foreach ( $raw_attributes as $name => $value ) {
			$attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
		}
		return implode( ' ', $attributes );
	}
}
