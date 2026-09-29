<?php
get_header();
$hero_products = wc_get_products(
	array(
		'limit'   => 8,
		'status'  => 'publish',
		'orderby' => 'date',
		'order'   => 'DESC',
	)
);
$hero_products = array_values(
	array_filter(
	$hero_products,
	function( $product ) {
		return $product->get_image_id();
	}
	)
);
$hero_products = array_slice( $hero_products, 0, 3 );
$featured_products = wc_get_products(
	array(
		'limit'    => 4,
		'status'   => 'publish',
		'featured' => true,
	)
);
$deals = wc_get_products(
	array(
		'limit'    => 4,
		'status'   => 'publish',
		'on_sale'  => true,
		'orderby'  => 'date',
		'order'    => 'DESC',
	)
);
$categories = bobbyafrica_get_product_categories();
?>
<main>
	<section class="marketplace-hero">
		<div class="container marketplace-hero-grid"<?php if ( count( $hero_products ) > 1 ) : ?> data-marketplace-carousel tabindex="0" role="region" aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'Featured products and shopping ideas', 'bobbyafrica-marketplace-child' ); ?>"<?php endif; ?>>
			<?php if ( ! empty( $hero_products ) ) : ?>
				<div class="marketplace-hero-copy-stack">
					<?php
					$hero_headlines = array(
						__( 'Find your next everyday favourite.', 'bobbyafrica-marketplace-child' ),
						__( 'Make a little room for better.', 'bobbyafrica-marketplace-child' ),
						__( 'Your next great find is waiting.', 'bobbyafrica-marketplace-child' ),
					);
					$hero_descriptions = array(
						__( 'Useful picks for home, work and everything in between, all in one place.', 'bobbyafrica-marketplace-child' ),
						__( 'Discover thoughtful finds that make the everyday feel easier.', 'bobbyafrica-marketplace-child' ),
						__( 'Explore fresh picks and bring a little more joy to your routine.', 'bobbyafrica-marketplace-child' ),
					);
					foreach ( $hero_products as $index => $product ) :
						$copy_index = $index % count( $hero_headlines );
						?>
						<div class="marketplace-hero-copy marketplace-hero-copy-slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-carousel-copy<?php echo 0 === $index ? '' : ' hidden'; ?> role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'bobbyafrica-marketplace-child' ), $index + 1, count( $hero_products ) ) ); ?>">
							<p class="marketplace-hero-eyebrow">
								<?php if ( $product->is_on_sale() ) : ?>
									<?php esc_html_e( 'Special price', 'bobbyafrica-marketplace-child' ); ?>
								<?php else : ?>
									<?php esc_html_e( 'Recently added', 'bobbyafrica-marketplace-child' ); ?>
								<?php endif; ?>
							</p>
							<h1><?php echo esc_html( $hero_headlines[ $copy_index ] ); ?></h1>
							<p><?php echo esc_html( $hero_descriptions[ $copy_index ] ); ?></p>
							<div class="marketplace-hero-featured-product">
								<span><?php esc_html_e( 'Featured find', 'bobbyafrica-marketplace-child' ); ?></span>
								<strong><?php echo esc_html( $product->get_name() ); ?></strong>
								<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
								<?php if ( $product->get_review_count() > 0 ) : ?>
									<span class="marketplace-hero-rating">
										<?php echo wp_kses_post( wc_get_rating_html( $product->get_average_rating() ) ); ?>
										<span><?php echo esc_html( sprintf( _n( '%s review', '%s reviews', $product->get_review_count(), 'bobbyafrica-marketplace-child' ), number_format_i18n( $product->get_review_count() ) ) ); ?></span>
									</span>
								<?php endif; ?>
							</div>
							<div class="marketplace-cta-group">
								<a class="marketplace-button primary" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'Explore this find', 'bobbyafrica-marketplace-child' ); ?></a>
								<a class="marketplace-button secondary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse the shop', 'bobbyafrica-marketplace-child' ); ?></a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="marketplace-hero-visual">
					<?php foreach ( $hero_products as $index => $product ) : ?>
						<div class="marketplace-hero-image marketplace-hero-image-slide<?php echo 0 === $index ? ' is-active' : ''; ?>" data-carousel-image<?php echo 0 === $index ? '' : ' hidden'; ?> role="group" aria-roledescription="slide" aria-label="<?php echo esc_attr( sprintf( __( '%1$d of %2$d', 'bobbyafrica-marketplace-child' ), $index + 1, count( $hero_products ) ) ); ?>">
							<a class="marketplace-hero-product" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>">
								<?php echo wp_get_attachment_image( $product->get_image_id(), 'large', false, array( 'alt' => $product->get_name(), 'loading' => 0 === $index ? 'eager' : 'lazy', 'fetchpriority' => 0 === $index ? 'high' : 'auto' ) ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( count( $hero_products ) > 1 ) : ?>
					<div class="marketplace-hero-progress" aria-hidden="true"><span class="marketplace-hero-progress-bar"></span></div>
				<?php endif; ?>
			<?php else : ?>
				<div class="marketplace-hero-copy">
					<p class="marketplace-hero-eyebrow"><?php esc_html_e( 'BobbyAfrica marketplace', 'bobbyafrica-marketplace-child' ); ?></p>
					<h1><?php esc_html_e( 'Smart shopping for everyday life.', 'bobbyafrica-marketplace-child' ); ?></h1>
					<p><?php esc_html_e( 'Discover useful finds for home, work and everything in between.', 'bobbyafrica-marketplace-child' ); ?></p>
					<a class="marketplace-button primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Browse the shop', 'bobbyafrica-marketplace-child' ); ?></a>
				</div>
				<div class="marketplace-hero-image" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
	</section>

	<section class="marketplace-section">
		<div class="container">
			<div class="marketplace-section-header">
				<h2><?php esc_html_e( 'Featured products', 'bobbyafrica-marketplace-child' ); ?></h2>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Explore all', 'bobbyafrica-marketplace-child' ); ?></a>
			</div>
			<div class="product-grid">
				<?php foreach ( $featured_products as $product ) : ?>
					<?php echo bobbyafrica_render_product_card( $product ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="marketplace-section">
		<div class="container">
			<div class="marketplace-section-header">
				<h2><?php esc_html_e( 'Hot deals', 'bobbyafrica-marketplace-child' ); ?></h2>
				<a href="<?php echo esc_url( home_url( '/shop/?sale=1' ) ); ?>"><?php esc_html_e( 'See more deals', 'bobbyafrica-marketplace-child' ); ?></a>
			</div>
			<div class="product-grid">
				<?php foreach ( $deals as $product ) : ?>
					<?php echo bobbyafrica_render_product_card( $product ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="marketplace-section">
		<div class="container">
			<div class="marketplace-trust-grid">
				<div class="marketplace-trust-item"><span class="icon">🔒</span><div><strong><?php esc_html_e( 'Secure payments', 'bobbyafrica-marketplace-child' ); ?></strong><div><?php esc_html_e( 'Protected checkout', 'bobbyafrica-marketplace-child' ); ?></div></div></div>
				<div class="marketplace-trust-item"><span class="icon">🚚</span><div><strong><?php esc_html_e( 'Fast delivery', 'bobbyafrica-marketplace-child' ); ?></strong><div><?php esc_html_e( 'On-time arrival', 'bobbyafrica-marketplace-child' ); ?></div></div></div>
				<div class="marketplace-trust-item"><span class="icon">🔄</span><div><strong><?php esc_html_e( 'Easy returns', 'bobbyafrica-marketplace-child' ); ?></strong><div><?php esc_html_e( 'Hassle-free', 'bobbyafrica-marketplace-child' ); ?></div></div></div>
				<div class="marketplace-trust-item"><span class="icon">💬</span><div><strong><?php esc_html_e( 'Support', 'bobbyafrica-marketplace-child' ); ?></strong><div><?php esc_html_e( '24/7 assistance', 'bobbyafrica-marketplace-child' ); ?></div></div></div>
			</div>
		</div>
	</section>
</main>
<?php get_footer(); ?>
