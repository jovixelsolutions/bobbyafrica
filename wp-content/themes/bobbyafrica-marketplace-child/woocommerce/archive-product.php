<?php
/**
 * WooCommerce archive-product override
 * Original template: woocommerce/archive-product.php
 * WooCommerce version: 10.7.0
 * Reason: add a marketplace shell, sidebar layout, and improved storefront spacing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
global $wp_query;
$marketplace_banner_products = array();
foreach ( array_slice( wp_list_pluck( $wp_query->posts, 'ID' ), 0, 5 ) as $product_id ) {
	$banner_product = wc_get_product( $product_id );
	if ( $banner_product && $banner_product->get_image_id() ) {
		$marketplace_banner_products[] = $banner_product;
	}
}
?>
<main class="marketplace-page">
	<div class="container">
		<?php if ( $marketplace_banner_products ) : ?>
			<section class="marketplace-archive-banner" data-marketplace-banner-carousel aria-label="<?php esc_attr_e( 'Featured products in this collection', 'bobbyafrica-marketplace-child' ); ?>">
				<?php foreach ( $marketplace_banner_products as $index => $product ) : ?>
					<div class="marketplace-archive-banner-slide" data-marketplace-banner-slide role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'bobbyafrica-marketplace-child' ), $index + 1, count( $marketplace_banner_products ) ) ); ?>"<?php echo 0 === $index ? '' : ' hidden aria-hidden="true"'; ?>>
						<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
							<?php echo wp_get_attachment_image( $product->get_image_id(), 'marketplace-hero', false, array( 'alt' => $product->get_name(), 'loading' => 0 === $index ? 'eager' : 'lazy' ) ); ?>
							<span class="marketplace-archive-banner-caption"><strong><?php echo esc_html( $product->get_name() ); ?></strong><span><?php echo wp_kses_post( $product->get_price_html() ); ?></span></span>
						</a>
					</div>
				<?php endforeach; ?>
				<?php if ( count( $marketplace_banner_products ) > 1 ) : ?>
					<div class="marketplace-archive-banner-controls">
						<button type="button" data-banner-step="-1" aria-label="<?php esc_attr_e( 'Previous banner image', 'bobbyafrica-marketplace-child' ); ?>">&#8592;</button>
						<button type="button" data-banner-step="1" aria-label="<?php esc_attr_e( 'Next banner image', 'bobbyafrica-marketplace-child' ); ?>">&#8594;</button>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<div class="marketplace-shop-grid">
			<aside class="marketplace-sidebar" aria-label="Shop filters">
				<?php bobbyafrica_marketplace_catalog_filters(); ?>
				<?php if ( is_active_sidebar( 'marketplace-sidebar' ) ) : ?>
					<h2 class="marketplace-extra-filters-title"><?php esc_html_e( 'More filters', 'bobbyafrica-marketplace-child' ); ?></h2>
					<?php dynamic_sidebar( 'marketplace-sidebar' ); ?>
				<?php endif; ?>
			</aside>

			<div class="marketplace-results">
				<div class="marketplace-page-intro">
					<?php woocommerce_breadcrumb(); ?>
					<h1><?php woocommerce_page_title(); ?></h1>
				</div>
				<?php if ( woocommerce_product_loop() ) : ?>
					<?php do_action( 'woocommerce_before_shop_loop' ); ?>
					<?php woocommerce_product_loop_start(); ?>
					<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
						<?php while ( have_posts() ) : the_post(); ?>
							<?php do_action( 'woocommerce_shop_loop' ); ?>
							<?php wc_get_template_part( 'content', 'product' ); ?>
						<?php endwhile; ?>
					<?php endif; ?>
					<?php woocommerce_product_loop_end(); ?>
					<?php do_action( 'woocommerce_after_shop_loop' ); ?>
				<?php else : ?>
					<?php do_action( 'woocommerce_no_products_found' ); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
