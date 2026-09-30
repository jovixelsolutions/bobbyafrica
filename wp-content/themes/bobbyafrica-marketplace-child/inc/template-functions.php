<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bobbyafrica_render_product_card( $product ) {
	if ( ! $product || ! is_object( $product ) ) {
		return '';
	}

	$product_url  = $product->get_permalink();
	$price_html   = $product->get_price_html();
	$image_id     = $product->get_image_id();
	$image_url    = $image_id ? wp_get_attachment_image_url( $image_id, 'marketplace-card' ) : wc_placeholder_img_src( 'marketplace-card' );
	$badge        = $product->is_on_sale() ? __( 'Sale', 'bobbyafrica-marketplace-child' ) : __( 'Popular', 'bobbyafrica-marketplace-child' );
	$stock_status = $product->is_in_stock() ? __( 'In stock', 'bobbyafrica-marketplace-child' ) : __( 'Out of stock', 'bobbyafrica-marketplace-child' );
	$rating_count = $product->get_review_count();
	$average      = $product->get_average_rating();
	$previous_product = isset( $GLOBALS['product'] ) ? $GLOBALS['product'] : null;
	$GLOBALS['product'] = $product;
	ob_start();
	woocommerce_template_loop_add_to_cart();
	$add_to_cart_html = ob_get_clean();
	$GLOBALS['product'] = $previous_product;

	ob_start();
	?>
	<article class="product-card">
		<div class="product-card-media">
			<a href="<?php echo esc_url( $product_url ); ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy" />
			</a>
			<div class="product-card-badges">
				<span class="product-badge"><?php echo esc_html( $badge ); ?></span>
			</div>
		</div>
		<div class="product-card-body">
			<div class="product-card-meta">
				<span><?php echo esc_html( $stock_status ); ?></span>
				<span><?php echo esc_html( $rating_count ); ?> <?php esc_html_e( 'reviews', 'bobbyafrica-marketplace-child' ); ?></span>
			</div>
			<h3><a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
			<div class="product-price-row">
				<?php echo wp_kses_post( $price_html ); ?>
			</div>
			<div class="marketplace-inline-actions">
				<a class="marketplace-button primary" href="<?php echo esc_url( $product_url ); ?>"><?php esc_html_e( 'View product', 'bobbyafrica-marketplace-child' ); ?></a>
				<?php echo $add_to_cart_html; ?>
			</div>
		</div>
	</article>
	<?php
	return ob_get_clean();
}

function bobbyafrica_render_product_strip( $title, $products, $collection_url, $link_label ) {
	if ( empty( $products ) ) {
		return;
	}

	$strip_id = 'marketplace-strip-' . sanitize_title( $title );
	?>
	<section class="marketplace-section marketplace-strip-section">
		<div class="container">
			<div class="marketplace-section-header">
				<h2><?php echo esc_html( $title ); ?></h2>
				<div class="marketplace-strip-actions">
					<a href="<?php echo esc_url( $collection_url ); ?>"><?php echo esc_html( $link_label ); ?></a>
					<button type="button" data-strip-direction="previous" aria-controls="<?php echo esc_attr( $strip_id ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Scroll %s backward', 'bobbyafrica-marketplace-child' ), $title ) ); ?>">&#8592;</button>
					<button type="button" data-strip-direction="next" aria-controls="<?php echo esc_attr( $strip_id ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Scroll %s forward', 'bobbyafrica-marketplace-child' ), $title ) ); ?>">&#8594;</button>
				</div>
			</div>
			<div class="product-grid marketplace-product-strip" id="<?php echo esc_attr( $strip_id ); ?>" data-marketplace-strip tabindex="0" role="region" aria-label="<?php echo esc_attr( $title ); ?>">
				<?php foreach ( $products as $product ) : ?>
					<?php echo bobbyafrica_render_product_card( $product ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
}

function bobbyafrica_get_product_categories() {
	$args = array(
		'taxonomy'   => 'product_cat',
		'orderby'    => 'name',
		'order'      => 'ASC',
		'hide_empty' => true,
		'number'     => 6,
	);

	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}

	return $terms;
}

function bobbyafrica_marketplace_nav_links() {
	$menu_items = wp_get_nav_menu_items( 'primary' );
	if ( ! empty( $menu_items ) ) {
		return $menu_items;
	}

	return array(
		(object) array( 'title' => __( 'Home', 'bobbyafrica-marketplace-child' ), 'url' => home_url( '/' ) ),
		(object) array( 'title' => __( 'Shop', 'bobbyafrica-marketplace-child' ), 'url' => wc_get_page_id( 'shop' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/shop/' ) ),
		(object) array( 'title' => __( 'Deals', 'bobbyafrica-marketplace-child' ), 'url' => home_url( '/shop/?sale=1' ) ),
		(object) array( 'title' => __( 'New arrivals', 'bobbyafrica-marketplace-child' ), 'url' => home_url( '/shop/' ) ),
	);
}
