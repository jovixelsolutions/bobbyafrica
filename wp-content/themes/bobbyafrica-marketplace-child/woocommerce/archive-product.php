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
?>
<main class="marketplace-page">
	<div class="container">
		<div class="marketplace-page-intro">
			<?php woocommerce_breadcrumb(); ?>
			<h1><?php woocommerce_page_title(); ?></h1>
			<?php if ( wc_get_loop_prop( 'total' ) ) : ?>
				<div class="woocommerce-result-count-wrapper">
					<?php woocommerce_result_count(); ?>
					<?php woocommerce_catalog_ordering(); ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="marketplace-shop-grid">
			<aside class="marketplace-sidebar" aria-label="Shop filters">
				<?php if ( is_active_sidebar( 'marketplace-sidebar' ) ) : ?>
					<?php dynamic_sidebar( 'marketplace-sidebar' ); ?>
				<?php else : ?>
					<?php the_widget( 'WC_Widget_Product_Categories' ); ?>
					<?php the_widget( 'WC_Widget_Price_Filter' ); ?>
				<?php endif; ?>
			</aside>

			<div class="marketplace-results">
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
