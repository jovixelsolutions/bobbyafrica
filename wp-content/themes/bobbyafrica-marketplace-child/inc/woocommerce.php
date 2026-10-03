<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
add_filter( 'woocommerce_add_to_cart_redirect', 'bobbyafrica_buy_now_checkout_redirect', 20 );

add_filter( 'loop_shop_per_page', function() {
	return 12;
}, 20 );

add_action( 'woocommerce_before_main_content', 'bobbyafrica_marketplace_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'bobbyafrica_marketplace_wrapper_end', 10 );
add_filter( 'cartflows_page_template_file', 'bobbyafrica_cartflows_marketplace_checkout_template', 20 );

function bobbyafrica_cartflows_marketplace_checkout_template( $file ) {
	if ( ! function_exists( '_is_wcf_checkout_type' ) || ! _is_wcf_checkout_type() || ! function_exists( 'wcf' ) || ! wcf()->utils->is_step_post_type() ) {
		return $file;
	}

	$flow_id = wcf()->utils->get_flow_id();
	if ( ! $flow_id || ! class_exists( 'Cartflows_Helper' ) || ! Cartflows_Helper::is_instant_layout_enabled( (int) $flow_id ) ) {
		return $file;
	}

	$theme_template = get_stylesheet_directory() . '/templates/cartflows-instant-checkout.php';
	return is_readable( $theme_template ) ? $theme_template : $file;
}

function bobbyafrica_marketplace_wrapper_start() {
	if ( is_product() || is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() || is_account_page() ) {
		echo '<main class="marketplace-page"><div class="container marketplace-shell">';
	}
}

function bobbyafrica_marketplace_wrapper_end() {
	if ( is_product() || is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() || is_account_page() ) {
		echo '</div></main>';
	}
}

add_filter( 'woocommerce_add_to_cart_fragments', 'bobbyafrica_cart_count_fragment' );

function bobbyafrica_cart_count_fragment( $fragments ) {
	if ( ! WC()->cart ) {
		return $fragments;
	}

	$cart_count = WC()->cart->get_cart_contents_count();
	ob_start();
	?>
	<span class="marketplace-badge" data-marketplace-cart-count<?php echo $cart_count ? '' : ' hidden'; ?>><?php echo esc_html( $cart_count ); ?></span>
	<?php
	$fragments['[data-marketplace-cart-count]'] = ob_get_clean();
	return $fragments;
}

add_action( 'woocommerce_product_query', 'bobbyafrica_filter_marketplace_catalog' );

function bobbyafrica_filter_marketplace_catalog( $query ) {
	if ( is_admin() ) {
		return;
	}

	$collection = isset( $_GET['market_collection'] ) ? sanitize_key( wp_unslash( $_GET['market_collection'] ) ) : '';
	$is_sale    = ( isset( $_GET['market_sale'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['market_sale'] ) ) ) || in_array( $collection, array( 'hot-deals', 'flash-sales' ), true );

	if ( 'featured' === $collection ) {
		$featured_ids = wc_get_featured_product_ids();
		$query->set( 'post__in', $featured_ids ? $featured_ids : array( 0 ) );
	} elseif ( $is_sale ) {
		$sale_product_ids = wc_get_product_ids_on_sale();
		$query->set( 'post__in', $sale_product_ids ? $sale_product_ids : array( 0 ) );
	}

	if ( 'new-arrivals' === $collection || 'flash-sales' === $collection ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	} elseif ( 'hot-deals' === $collection ) {
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', 'total_sales' );
		$query->set( 'order', 'DESC' );
	}

	$tax_query = $query->get( 'tax_query' );
	$tax_query = is_array( $tax_query ) ? $tax_query : array();

	if ( isset( $_GET['market_subcategory'] ) ) {
		$subcategory_slugs = array_filter( array_map( 'sanitize_title', (array) wp_unslash( $_GET['market_subcategory'] ) ) );
		if ( $subcategory_slugs ) {
			$tax_query[] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => $subcategory_slugs,
			);
		}
	}

	if ( isset( $_GET['market_filter'] ) && is_array( $_GET['market_filter'] ) ) {
		$filters = wp_unslash( $_GET['market_filter'] );
		foreach ( $filters as $taxonomy => $term_slugs ) {
			$taxonomy = sanitize_key( $taxonomy );
			if ( ! taxonomy_exists( $taxonomy ) || ! is_object_in_taxonomy( 'product', $taxonomy ) ) {
				continue;
			}

			$term_slugs = array_filter( array_map( 'sanitize_title', (array) $term_slugs ) );
			if ( $term_slugs ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $term_slugs,
				);
			}
		}
	}

	if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
		$tax_query['relation'] = 'AND';
	}
	$query->set( 'tax_query', $tax_query );

	$meta_query = $query->get( 'meta_query' );
	$meta_query = is_array( $meta_query ) ? $meta_query : array();
	$min_price  = isset( $_GET['min_price'] ) && is_scalar( $_GET['min_price'] ) ? wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : null;
	$max_price  = isset( $_GET['max_price'] ) && is_scalar( $_GET['max_price'] ) ? wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : null;
	$min_price  = '' !== $min_price ? $min_price : null;
	$max_price  = '' !== $max_price ? $max_price : null;

	if ( null !== $min_price && null !== $max_price ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => array( $min_price, $max_price ),
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL',
		);
	} elseif ( null !== $min_price ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $min_price,
			'compare' => '>=',
			'type'    => 'DECIMAL',
		);
	} elseif ( null !== $max_price ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => $max_price,
			'compare' => '<=',
			'type'    => 'DECIMAL',
		);
	}

	if ( isset( $_GET['rating_filter'] ) && is_scalar( $_GET['rating_filter'] ) && '' !== $_GET['rating_filter'] ) {
		$minimum_rating = min( 5, max( 1, absint( wp_unslash( $_GET['rating_filter'] ) ) ) );
		$meta_query[]   = array(
			'key'     => '_wc_average_rating',
			'value'   => $minimum_rating,
			'compare' => '>=',
			'type'    => 'DECIMAL',
		);
	}

	$query->set( 'meta_query', $meta_query );
}

add_action( 'wp_ajax_bobbyafrica_recent_products', 'bobbyafrica_recent_products_ajax' );
add_action( 'wp_ajax_nopriv_bobbyafrica_recent_products', 'bobbyafrica_recent_products_ajax' );

function bobbyafrica_recent_products_ajax() {
	check_ajax_referer( 'bobbyafrica_marketplace', 'nonce' );
	$product_ids = isset( $_POST['product_ids'] ) && is_array( $_POST['product_ids'] ) ? array_slice( array_unique( array_filter( array_map( 'absint', wp_unslash( $_POST['product_ids'] ) ) ) ), 0, 12 ) : array();
	$products    = $product_ids ? wc_get_products(
		array(
			'include' => $product_ids,
			'limit'   => 12,
			'orderby' => 'include',
			'status'  => 'publish',
		)
	) : array();

	ob_start();
	foreach ( $products as $product ) {
		echo bobbyafrica_render_product_card( $product );
	}
	wp_send_json_success( array( 'html' => ob_get_clean() ) );
}

add_action( 'wp_ajax_bobbyafrica_category_products', 'bobbyafrica_category_products_ajax' );
add_action( 'wp_ajax_nopriv_bobbyafrica_category_products', 'bobbyafrica_category_products_ajax' );

function bobbyafrica_category_products_ajax() {
	check_ajax_referer( 'bobbyafrica_marketplace', 'nonce' );

	$category_id = isset( $_POST['category_id'] ) ? absint( wp_unslash( $_POST['category_id'] ) ) : 0;
	$category    = get_term( $category_id, 'product_cat' );

	if ( ! $category_id || is_wp_error( $category ) || ! $category ) {
		wp_send_json_error( array( 'message' => __( 'Category not found.', 'bobbyafrica-marketplace-child' ) ), 404 );
	}

	$transient_key = 'bobbyafrica_mega_products_' . $category_id;
	$products_html = get_transient( $transient_key );

	if ( false === $products_html ) {
		$products = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => 5,
				'category' => array( $category->slug ),
				'orderby'  => 'popularity',
				'order'    => 'DESC',
			)
		);

		ob_start();
		if ( $products ) :
			?>
			<ul class="marketplace-mega-product-links">
				<?php foreach ( $products as $product ) : ?>
					<li>
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
							<span><?php echo esc_html( $product->get_name() ); ?></span>
							<strong><?php echo wp_kses_post( $product->get_price_html() ); ?></strong>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php
		else :
			?>
			<p><?php esc_html_e( 'No products are currently listed in this category.', 'bobbyafrica-marketplace-child' ); ?></p>
			<?php
		endif;
		$products_html = ob_get_clean();
		set_transient( $transient_key, $products_html, 5 * MINUTE_IN_SECONDS );
	}

	wp_send_json_success( array( 'html' => $products_html ) );
}

add_action( 'woocommerce_single_product_summary', 'bobbyafrica_product_purchase_notes', 29 );
add_action( 'woocommerce_single_product_summary', 'bobbyafrica_render_product_overview', 8 );
add_action( 'woocommerce_after_single_product_summary', 'bobbyafrica_render_delivery_and_seller', 5 );

function bobbyafrica_buy_now_checkout_redirect( $url ) {
	if ( ! isset( $_REQUEST['buy_now'] ) || '1' !== sanitize_text_field( wp_unslash( $_REQUEST['buy_now'] ) ) ) {
		return $url;
	}

	if ( function_exists( 'wc_get_checkout_url' ) ) {
		return wc_get_checkout_url();
	}

	return $url;
}

function bobbyafrica_render_product_overview() {
	global $product;
	if ( ! $product ) {
		return;
	}

	$overview_items = array();
	$sku            = $product->get_sku();
	if ( $sku ) {
		$overview_items[] = sprintf( __( 'SKU: %s', 'bobbyafrica-marketplace-child' ), esc_html( $sku ) );
	}

	if ( $product->is_in_stock() ) {
		$overview_items[] = __( 'In stock', 'bobbyafrica-marketplace-child' );
	} else {
		$overview_items[] = __( 'Out of stock', 'bobbyafrica-marketplace-child' );
	}

	if ( $product->get_review_count() ) {
		$review_count = (int) $product->get_review_count();
		$overview_items[] = sprintf( _n( '%s review', '%s reviews', $review_count, 'bobbyafrica-marketplace-child' ), number_format_i18n( $review_count ) );
	}

	if ( empty( $overview_items ) ) {
		return;
	}
	?>
	<div class="marketplace-product-overview" aria-label="<?php esc_attr_e( 'Product overview', 'bobbyafrica-marketplace-child' ); ?>">
		<?php echo esc_html( implode( ' • ', $overview_items ) ); ?>
	</div>
	<?php
}

function bobbyafrica_product_purchase_notes() {
	global $product;
	if ( ! $product ) {
		return;
	}
	?>
	<ul class="marketplace-product-notes" aria-label="<?php esc_attr_e( 'Purchase information', 'bobbyafrica-marketplace-child' ); ?>">
		<?php if ( $product->needs_shipping() ) : ?>
			<li><?php esc_html_e( 'Shipping options and rates are shown at checkout', 'bobbyafrica-marketplace-child' ); ?></li>
		<?php endif; ?>
		<li><?php esc_html_e( 'Payment methods are available at checkout', 'bobbyafrica-marketplace-child' ); ?></li>
		<li><?php esc_html_e( 'Product details and customer reviews are below', 'bobbyafrica-marketplace-child' ); ?></li>
	</ul>
	<?php
}

function bobbyafrica_render_delivery_and_seller() {
	global $product;
	if ( ! $product ) {
		return;
	}

	$shop_name = get_bloginfo( 'name' );
	$shop_url  = wc_get_page_permalink( 'shop' );
	?>
	<div class="marketplace-product-meta-panels">
		<div class="marketplace-detail-panel" aria-label="<?php esc_attr_e( 'Delivery information', 'bobbyafrica-marketplace-child' ); ?>">
			<h3><?php esc_html_e( 'Delivery', 'bobbyafrica-marketplace-child' ); ?></h3>
			<div class="marketplace-detail-row">
				<span><?php esc_html_e( 'Deliver to', 'bobbyafrica-marketplace-child' ); ?></span>
				<strong><?php esc_html_e( 'Checkout for your location', 'bobbyafrica-marketplace-child' ); ?></strong>
			</div>
			<div class="marketplace-detail-row">
				<span><?php esc_html_e( 'Shipping', 'bobbyafrica-marketplace-child' ); ?></span>
				<strong><?php esc_html_e( 'Rates calculated at checkout', 'bobbyafrica-marketplace-child' ); ?></strong>
			</div>
			<div class="marketplace-detail-row">
				<span><?php esc_html_e( 'Payment', 'bobbyafrica-marketplace-child' ); ?></span>
				<strong><?php esc_html_e( 'Secure checkout available', 'bobbyafrica-marketplace-child' ); ?></strong>
			</div>
		</div>
		<div class="marketplace-detail-panel" aria-label="<?php esc_attr_e( 'Seller information', 'bobbyafrica-marketplace-child' ); ?>">
			<h3><?php esc_html_e( 'Sold by', 'bobbyafrica-marketplace-child' ); ?></h3>
			<div class="marketplace-seller-card">
				<strong><?php echo esc_html( $shop_name ); ?></strong>
				<?php if ( $product->get_average_rating() ) : ?>
					<span class="marketplace-product-rating">
						<?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating(), $product->get_rating_count() ) ); ?>
					</span>
				<?php endif; ?>
				<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'View store', 'bobbyafrica-marketplace-child' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}

add_action( 'woocommerce_after_add_to_cart_button', 'bobbyafrica_render_buy_now_button', 25 );

function bobbyafrica_render_buy_now_button() {
	global $product;
	if ( ! $product || ! $product->is_purchasable() || $product->is_type( 'external' ) ) {
		return;
	}
	?>
	<button type="submit" class="single_buy_now_button button alt" name="buy_now" value="1">
		<?php esc_html_e( 'Buy Now', 'bobbyafrica-marketplace-child' ); ?>
	</button>
	<?php
}

add_action( 'wp_footer', 'bobbyafrica_product_mobile_buy_bar', 30 );

function bobbyafrica_product_mobile_buy_bar() {
	if ( ! is_product() ) {
		return;
	}

	global $product;
	if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
		return;
	}
	?>
	<div class="marketplace-mobile-buy-bar<?php echo $product->is_type( 'variable' ) ? ' is-variable' : ''; ?>" data-marketplace-product="<?php echo esc_attr( $product->get_id() ); ?>">
		<div class="marketplace-mobile-buy-price" aria-live="polite"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<button class="marketplace-mobile-buy-button" type="button" aria-controls="product-<?php echo esc_attr( $product->get_id() ); ?>">
			<?php echo $product->is_type( 'variable' ) ? esc_html__( 'Choose options', 'bobbyafrica-marketplace-child' ) : esc_html__( 'Add to cart', 'bobbyafrica-marketplace-child' ); ?>
		</button>
	</div>
	<?php
}

add_action( 'wp_footer', 'bobbyafrica_marketplace_cart_drawer' );

function bobbyafrica_marketplace_cart_drawer() {
	if ( is_cart() || is_checkout() ) {
		return;
	}
	?>
	<div class="marketplace-mini-cart" aria-live="polite">
		<?php echo wp_kses_post( woocommerce_mini_cart() ); ?>
	</div>
	<?php
}

add_action( 'woocommerce_before_shop_loop_item_title', 'bobbyafrica_product_loop_badges', 10 );

function bobbyafrica_product_loop_badges() {
	global $product;
	if ( ! $product ) {
		return;
	}
	if ( $product->is_on_sale() ) {
		echo '<span class="product-badge secondary">' . esc_html__( 'Featured', 'bobbyafrica-marketplace-child' ) . '</span>';
	}
}
