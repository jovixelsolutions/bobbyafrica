<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

add_filter( 'loop_shop_per_page', function() {
	return 12;
}, 20 );

add_action( 'woocommerce_before_main_content', 'bobbyafrica_marketplace_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'bobbyafrica_marketplace_wrapper_end', 10 );

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

add_action( 'woocommerce_product_query', 'bobbyafrica_filter_sale_catalog' );

function bobbyafrica_filter_sale_catalog( $query ) {
	if ( is_admin() || ! isset( $_GET['market_sale'] ) || '1' !== sanitize_text_field( wp_unslash( $_GET['market_sale'] ) ) ) {
		return;
	}

	$sale_product_ids = wc_get_product_ids_on_sale();
	$query->set( 'post__in', $sale_product_ids ? $sale_product_ids : array( 0 ) );
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
