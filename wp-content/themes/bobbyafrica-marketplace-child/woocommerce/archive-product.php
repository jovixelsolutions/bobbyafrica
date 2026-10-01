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
$queried_category = is_product_category() ? get_queried_object() : null;
$banner_args      = array(
	'limit'   => 4,
	'status'  => 'publish',
	'featured' => true,
	'orderby' => 'date',
	'order'   => 'DESC',
);
if ( $queried_category instanceof WP_Term ) {
	$banner_args['category'] = array( $queried_category->slug );
}
$banner_products = wc_get_products( $banner_args );
if ( count( $banner_products ) < 2 ) {
	$banner_args['featured'] = false;
	$banner_products         = wc_get_products( $banner_args );
}
$banner_products = array_slice( $banner_products, 0, 4 );
$collection      = isset( $_GET['market_collection'] ) ? sanitize_key( wp_unslash( $_GET['market_collection'] ) ) : '';
$collection_titles = array(
	'new-arrivals' => __( 'New arrivals', 'bobbyafrica-marketplace-child' ),
	'hot-deals'    => __( 'Hot deals', 'bobbyafrica-marketplace-child' ),
	'flash-sales'  => __( 'Flash sales', 'bobbyafrica-marketplace-child' ),
	'featured'     => __( 'Featured products', 'bobbyafrica-marketplace-child' ),
);
$archive_title = isset( $collection_titles[ $collection ] ) && is_shop() ? $collection_titles[ $collection ] : woocommerce_page_title( false );
$min_price_value = isset( $_GET['min_price'] ) && is_scalar( $_GET['min_price'] ) ? wc_format_decimal( wp_unslash( $_GET['min_price'] ) ) : '';
$max_price_value = isset( $_GET['max_price'] ) && is_scalar( $_GET['max_price'] ) ? wc_format_decimal( wp_unslash( $_GET['max_price'] ) ) : '';
$rating_value    = isset( $_GET['rating_filter'] ) && is_scalar( $_GET['rating_filter'] ) ? absint( wp_unslash( $_GET['rating_filter'] ) ) : 0;
$price_lookup_args = array(
	'status' => 'publish',
	'limit'  => -1,
	'return' => 'objects',
);
if ( $queried_category instanceof WP_Term ) {
	$price_lookup_args['category'] = array( $queried_category->slug );
}
$price_products = wc_get_products( $price_lookup_args );
$price_values   = array_filter(
	array_map(
		function ( $product ) {
			return $product instanceof WC_Product && '' !== $product->get_price() ? (float) $product->get_price() : null;
		},
		$price_products
	),
	function ( $value ) {
		return null !== $value;
	}
);
$price_min = $price_values ? (int) floor( min( $price_values ) ) : 0;
$price_max = $price_values ? (int) ceil( max( $price_values ) ) : 1000;
$price_max = max( $price_min + 1, $price_max );
$selected_min_price = '' !== $min_price_value ? min( $price_max, max( $price_min, (float) $min_price_value ) ) : $price_min;
$selected_max_price = '' !== $max_price_value ? min( $price_max, max( $price_min, (float) $max_price_value ) ) : $price_max;
if ( $selected_min_price > $selected_max_price ) {
	$selected_min_price = $selected_max_price;
}
?>
<main class="marketplace-page">
	<div class="container">
		<?php if ( count( $banner_products ) > 0 ) : ?>
			<section class="marketplace-archive-banner" data-marketplace-carousel tabindex="0" role="region" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Featured products', 'bobbyafrica-marketplace-child' ); ?>">
				<div class="marketplace-archive-banner-copy">
					<?php foreach ( $banner_products as $index => $product ) : ?>
						<div class="marketplace-archive-copy-slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-carousel-copy<?php echo 0 === $index ? '' : ' hidden'; ?> role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'bobbyafrica-marketplace-child' ), $index + 1, count( $banner_products ) ) ); ?>">
							<p><?php esc_html_e( 'Handpicked for you', 'bobbyafrica-marketplace-child' ); ?></p>
							<h2><?php echo esc_html( $product->get_name() ); ?></h2>
							<div class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
							<a class="marketplace-button primary" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Explore product', 'bobbyafrica-marketplace-child' ); ?></a>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="marketplace-archive-banner-visual">
					<?php foreach ( $banner_products as $index => $product ) : ?>
						<div class="marketplace-archive-image-slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-carousel-image<?php echo 0 === $index ? '' : ' hidden'; ?> role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'bobbyafrica-marketplace-child' ), $index + 1, count( $banner_products ) ) ); ?>">
							<a class="marketplace-hero-product" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>">
								<?php echo $product->get_image( 'large', array( 'loading' => 0 === $index ? 'eager' : 'lazy' ) ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="marketplace-carousel-controls" aria-label="<?php esc_attr_e( 'Featured product slides', 'bobbyafrica-marketplace-child' ); ?>">
					<button class="marketplace-carousel-arrow" type="button" data-carousel-direction="previous" aria-label="<?php esc_attr_e( 'Previous featured product', 'bobbyafrica-marketplace-child' ); ?>">&#8592;</button>
					<div class="marketplace-carousel-dots">
						<?php foreach ( $banner_products as $index => $product ) : ?>
							<button type="button" data-carousel-slide="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show featured product %d', 'bobbyafrica-marketplace-child' ), $index + 1 ) ); ?>" aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
						<?php endforeach; ?>
					</div>
					<button class="marketplace-carousel-arrow" type="button" data-carousel-direction="next" aria-label="<?php esc_attr_e( 'Next featured product', 'bobbyafrica-marketplace-child' ); ?>">&#8594;</button>
				</div>
				<div class="marketplace-hero-progress" aria-hidden="true"><span class="marketplace-hero-progress-bar"></span></div>
			</section>
		<?php endif; ?>

		<div class="marketplace-shop-grid">
			<aside class="marketplace-sidebar" aria-label="<?php esc_attr_e( 'Shop filters', 'bobbyafrica-marketplace-child' ); ?>">
				<button class="marketplace-filter-toggle" type="button" aria-expanded="false" aria-controls="marketplace-filter-form"><?php esc_html_e( 'Filter products', 'bobbyafrica-marketplace-child' ); ?><span aria-hidden="true">+</span></button>
				<form id="marketplace-filter-form" class="marketplace-filter-form" method="get" action="<?php echo esc_url( is_product_category() ? get_term_link( $queried_category ) : wc_get_page_permalink( 'shop' ) ); ?>">
					<h2><?php esc_html_e( 'Filter products', 'bobbyafrica-marketplace-child' ); ?></h2>
					<?php if ( $collection && isset( $collection_titles[ $collection ] ) ) : ?>
						<input type="hidden" name="market_collection" value="<?php echo esc_attr( $collection ); ?>" />
					<?php endif; ?>
					<?php if ( isset( $_GET['orderby'] ) ) : ?>
						<input type="hidden" name="orderby" value="<?php echo esc_attr( sanitize_key( wp_unslash( $_GET['orderby'] ) ) ); ?>" />
					<?php endif; ?>
					<fieldset>
						<legend><?php esc_html_e( 'Price', 'bobbyafrica-marketplace-child' ); ?></legend>
						<div class="marketplace-price-range" data-price-range>
							<div class="marketplace-price-range-values" aria-live="polite">
								<span><?php esc_html_e( 'From', 'bobbyafrica-marketplace-child' ); ?> <strong data-price-min-value><?php echo esc_html( wp_strip_all_tags( wc_price( $selected_min_price, array( 'decimals' => 0 ) ) ) ); ?></strong></span>
								<span><?php esc_html_e( 'To', 'bobbyafrica-marketplace-child' ); ?> <strong data-price-max-value><?php echo esc_html( wp_strip_all_tags( wc_price( $selected_max_price, array( 'decimals' => 0 ) ) ) ); ?></strong></span>
							</div>
							<div class="marketplace-price-slider" data-price-slider>
								<span class="marketplace-price-slider-track" aria-hidden="true"></span>
								<span class="marketplace-price-slider-fill" data-price-slider-fill aria-hidden="true"></span>
								<label class="screen-reader-text" for="marketplace-min-price"><?php esc_html_e( 'Minimum price', 'bobbyafrica-marketplace-child' ); ?></label>
								<input id="marketplace-min-price" type="range" name="min_price" min="<?php echo esc_attr( $price_min ); ?>" max="<?php echo esc_attr( $price_max ); ?>" step="1" value="<?php echo esc_attr( $selected_min_price ); ?>" data-price-min />
								<label class="screen-reader-text" for="marketplace-max-price"><?php esc_html_e( 'Maximum price', 'bobbyafrica-marketplace-child' ); ?></label>
								<input id="marketplace-max-price" type="range" name="max_price" min="<?php echo esc_attr( $price_min ); ?>" max="<?php echo esc_attr( $price_max ); ?>" step="1" value="<?php echo esc_attr( $selected_max_price ); ?>" data-price-max />
							</div>
						</div>
					</fieldset>
					<fieldset>
						<legend><?php esc_html_e( 'Customer rating', 'bobbyafrica-marketplace-child' ); ?></legend>
						<select name="rating_filter">
							<option value=""><?php esc_html_e( 'Any rating', 'bobbyafrica-marketplace-child' ); ?></option>
							<?php for ( $rating = 5; $rating >= 1; $rating-- ) : ?>
								<option value="<?php echo esc_attr( $rating ); ?>" <?php selected( $rating_value, $rating ); ?>><?php echo esc_html( sprintf( _n( '%d star and up', '%d stars and up', $rating, 'bobbyafrica-marketplace-child' ), $rating ) ); ?></option>
							<?php endfor; ?>
						</select>
					</fieldset>
					<?php if ( $queried_category instanceof WP_Term ) : ?>
						<?php $subcategories = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $queried_category->term_id, 'hide_empty' => true ) ); ?>
						<?php if ( ! is_wp_error( $subcategories ) && $subcategories ) : ?>
							<fieldset>
								<legend><?php esc_html_e( 'Subcategories', 'bobbyafrica-marketplace-child' ); ?></legend>
								<?php foreach ( $subcategories as $subcategory ) : ?>
									<label class="marketplace-filter-option"><input type="checkbox" name="market_subcategory[]" value="<?php echo esc_attr( $subcategory->slug ); ?>" <?php checked( isset( $_GET['market_subcategory'] ) && in_array( $subcategory->slug, array_map( 'sanitize_title', (array) wp_unslash( $_GET['market_subcategory'] ) ), true ) ); ?> /><?php echo esc_html( $subcategory->name ); ?></label>
								<?php endforeach; ?>
							</fieldset>
						<?php endif; ?>
					<?php endif; ?>
					<?php foreach ( get_object_taxonomies( 'product', 'objects' ) as $taxonomy ) : ?>
						<?php
						if ( in_array( $taxonomy->name, array( 'product_cat', 'product_tag', 'product_type', 'product_visibility', 'product_shipping_class' ), true ) || ! $taxonomy->public || ! $taxonomy->show_ui ) {
							continue;
						}
						$filter_terms = get_terms( array( 'taxonomy' => $taxonomy->name, 'hide_empty' => true, 'number' => 40 ) );
						if ( is_wp_error( $filter_terms ) || ! $filter_terms ) {
							continue;
						}
						$selected_terms = isset( $_GET['market_filter'][ $taxonomy->name ] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['market_filter'][ $taxonomy->name ] ) ) : array();
						?>
						<fieldset>
							<legend><?php echo esc_html( $taxonomy->label ); ?></legend>
							<?php foreach ( $filter_terms as $filter_term ) : ?>
								<label class="marketplace-filter-option"><input type="checkbox" name="market_filter[<?php echo esc_attr( $taxonomy->name ); ?>][]" value="<?php echo esc_attr( $filter_term->slug ); ?>" <?php checked( in_array( $filter_term->slug, $selected_terms, true ) ); ?> /><?php echo esc_html( $filter_term->name ); ?></label>
							<?php endforeach; ?>
						</fieldset>
					<?php endforeach; ?>
					<button class="marketplace-button primary" type="submit"><?php esc_html_e( 'Apply filters', 'bobbyafrica-marketplace-child' ); ?></button>
					<a class="marketplace-filter-reset" href="<?php echo esc_url( is_product_category() ? get_term_link( $queried_category ) : wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Clear all filters', 'bobbyafrica-marketplace-child' ); ?></a>
				</form>
			</aside>

			<div class="marketplace-results">
				<div class="marketplace-mobile-listing-controls" aria-label="<?php esc_attr_e( 'Product listing controls', 'bobbyafrica-marketplace-child' ); ?>">
					<button class="marketplace-mobile-filter-button" type="button" aria-expanded="false" aria-controls="marketplace-filter-form">
						<span aria-hidden="true">&#9776;</span>
						<?php esc_html_e( 'Filters', 'bobbyafrica-marketplace-child' ); ?>
					</button>
				</div>
				<div class="marketplace-page-intro">
					<?php woocommerce_breadcrumb(); ?>
					<h1><?php echo esc_html( $archive_title ); ?></h1>
				</div>
				<?php if ( '' !== $min_price_value || '' !== $max_price_value || $rating_value ) : ?>
					<div class="marketplace-active-filters" aria-label="<?php esc_attr_e( 'Active filters', 'bobbyafrica-marketplace-child' ); ?>">
						<?php if ( '' !== $min_price_value || '' !== $max_price_value ) : ?>
							<span class="marketplace-filter-chip"><?php echo esc_html( sprintf( __( 'Price: %1$s - %2$s', 'bobbyafrica-marketplace-child' ), $min_price_value ? $min_price_value : __( 'Any', 'bobbyafrica-marketplace-child' ), $max_price_value ? $max_price_value : __( 'Any', 'bobbyafrica-marketplace-child' ) ) ); ?></span>
						<?php endif; ?>
						<?php if ( $rating_value ) : ?>
							<span class="marketplace-filter-chip"><?php echo esc_html( sprintf( _n( '%d star and up', '%d stars and up', $rating_value, 'bobbyafrica-marketplace-child' ), $rating_value ) ); ?></span>
						<?php endif; ?>
						<a class="marketplace-filter-clear" href="<?php echo esc_url( is_product_category() ? get_term_link( $queried_category ) : wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Clear all', 'bobbyafrica-marketplace-child' ); ?></a>
					</div>
				<?php endif; ?>
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
		<div class="marketplace-filter-backdrop" hidden></div>
	</div>
</main>
<?php
get_footer();
