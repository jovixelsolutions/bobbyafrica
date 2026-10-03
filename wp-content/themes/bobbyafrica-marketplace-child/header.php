<?php
/**
 * Custom marketplace header for Jovixels Solutions child theme.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$marketplace_categories = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);
$marketplace_categories = is_wp_error( $marketplace_categories ) ? array() : $marketplace_categories;
$marketplace_top_categories = array_filter(
	$marketplace_categories,
	function( $category ) {
		return 0 === (int) $category->parent;
	}
);
$marketplace_category_children = array();
foreach ( $marketplace_categories as $marketplace_category ) {
	if ( $marketplace_category->parent ) {
		$marketplace_category_children[ $marketplace_category->parent ][] = $marketplace_category;
	}
}
$marketplace_shop_url = wc_get_page_permalink( 'shop' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
	<?php
	if ( function_exists( '_is_wcf_checkout_type' ) && _is_wcf_checkout_type() ) {
		$checkout_styles = array(
			'bobbyafrica-child-style'      => array( get_stylesheet_uri(), get_stylesheet_directory() . '/style.css' ),
			'bobbyafrica-child-main'       => array( get_stylesheet_directory_uri() . '/assets/css/main.css', get_stylesheet_directory() . '/assets/css/main.css' ),
			'bobbyafrica-child-responsive' => array( get_stylesheet_directory_uri() . '/assets/css/responsive.css', get_stylesheet_directory() . '/assets/css/responsive.css' ),
		);

		foreach ( $checkout_styles as $handle => $style ) {
			if ( ! wp_style_is( $handle, 'done' ) ) {
				printf(
					'<link rel="stylesheet" id="%1$s-css" href="%2$s" media="all" />' . "\n",
					esc_attr( $handle ),
					esc_url( add_query_arg( 'ver', filemtime( $style[1] ), $style[0] ) )
				);
			}
		}
	}
	?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="marketplace-header">
	<div class="marketplace-topbar">
		<div class="container">
			<span><?php esc_html_e( 'Fast delivery across major cities', 'bobbyafrica-marketplace-child' ); ?></span>
			<span><?php esc_html_e( 'Secure checkout', 'bobbyafrica-marketplace-child' ); ?></span>
			<span><?php esc_html_e( '+254 725 676 566', 'bobbyafrica-marketplace-child' ); ?></span>
			<span><?php esc_html_e( 'Support 24/7', 'bobbyafrica-marketplace-child' ); ?></span>
		</div>
	</div>
	<div class="container marketplace-header-main">
		<a class="marketplace-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php
			$marketplace_logo_id = get_theme_mod( 'custom_logo' );
			if ( $marketplace_logo_id ) {
				echo wp_get_attachment_image( $marketplace_logo_id, 'full', false, array( 'class' => 'marketplace-logo-image', 'alt' => '' ) );
			} else {
				printf( '<span class="marketplace-logo-mark" aria-hidden="true">%s</span>', esc_html( strtoupper( substr( get_bloginfo( 'name' ), 0, 2 ) ) ) );
			}
			?>
			<span><?php bloginfo( 'name' ); ?></span>
		</a>

		<form method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="marketplace-search" role="search">
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search products, brands and categories', 'bobbyafrica-marketplace-child' ); ?>" aria-label="<?php esc_attr_e( 'Search products', 'bobbyafrica-marketplace-child' ); ?>" />
			<input type="hidden" name="post_type" value="product" />
			<button type="submit"><?php esc_html_e( 'Search', 'bobbyafrica-marketplace-child' ); ?></button>
		</form>

		<div class="marketplace-user-actions">
			<?php if ( is_user_logged_in() ) : ?>
				<a class="marketplace-icon-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
					<span><?php esc_html_e( 'Account', 'bobbyafrica-marketplace-child' ); ?></span>
				</a>
			<?php else : ?>
				<a class="marketplace-icon-link" href="<?php echo esc_url( wp_login_url() ); ?>">
					<span><?php esc_html_e( 'Login', 'bobbyafrica-marketplace-child' ); ?></span>
				</a>
			<?php endif; ?>
			<a class="marketplace-icon-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
				<span><?php esc_html_e( 'Cart', 'bobbyafrica-marketplace-child' ); ?></span>
				<?php if ( WC()->cart ) : ?>
					<span class="marketplace-badge" data-marketplace-cart-count<?php echo WC()->cart->get_cart_contents_count() ? '' : ' hidden'; ?>><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
				<?php endif; ?>
			</a>
		</div>
	</div>
	<nav class="marketplace-primary-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'bobbyafrica-marketplace-child' ); ?>">
		<div class="container marketplace-primary-nav-inner">
			<button class="marketplace-nav-categories" type="button" aria-expanded="false" aria-controls="marketplace-category-mega">
				<span class="marketplace-menu-bars" aria-hidden="true"><i></i><i></i><i></i></span>
				<span><?php esc_html_e( 'Categories', 'bobbyafrica-marketplace-child' ); ?></span>
			</button>
			<a class="marketplace-nav-feature" href="<?php echo esc_url( add_query_arg( 'market_collection', 'new-arrivals', $marketplace_shop_url ) ); ?>">
				<span><?php esc_html_e( 'New Arrivals', 'bobbyafrica-marketplace-child' ); ?></span><span class="marketplace-nav-icon" aria-hidden="true">✦</span>
			</a>
			<a class="marketplace-nav-feature" href="<?php echo esc_url( add_query_arg( 'market_collection', 'flash-sales', $marketplace_shop_url ) ); ?>">
				<span><?php esc_html_e( 'Flash Sales', 'bobbyafrica-marketplace-child' ); ?></span><span class="marketplace-nav-icon" aria-hidden="true">ϟ</span>
			</a>
			<a class="marketplace-nav-feature" href="<?php echo esc_url( add_query_arg( 'market_collection', 'hot-deals', $marketplace_shop_url ) ); ?>">
				<span><?php esc_html_e( 'Hot Deals', 'bobbyafrica-marketplace-child' ); ?></span><span class="marketplace-nav-icon" aria-hidden="true">%</span>
			</a>
			<span class="marketplace-nav-feature marketplace-nav-static">
				<span><?php esc_html_e( 'Fast Delivery', 'bobbyafrica-marketplace-child' ); ?></span><span class="marketplace-nav-icon" aria-hidden="true">&#10140;</span>
			</span>
			<span class="marketplace-nav-feature marketplace-nav-static">
				<span><?php esc_html_e( '24/7 Support', 'bobbyafrica-marketplace-child' ); ?></span><span class="marketplace-nav-icon marketplace-nav-support-icon" aria-hidden="true">?</span>
			</span>
		</div>

			<div class="marketplace-category-mega" id="marketplace-category-mega" hidden>
				<div class="marketplace-mega-category-list">
					<h2><?php esc_html_e( 'Shop by category', 'bobbyafrica-marketplace-child' ); ?></h2>
					<ul>
						<?php foreach ( $marketplace_top_categories as $index => $category ) : ?>
							<li class="marketplace-mega-category-row">
								<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" data-mega-category="<?php echo esc_attr( $category->term_id ); ?>" aria-controls="marketplace-category-panel-<?php echo esc_attr( $category->term_id ); ?>"<?php echo 0 === $index ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $category->name ); ?></a>
								<button type="button" data-expand-category="<?php echo esc_attr( $category->term_id ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show subcategories in %s', 'bobbyafrica-marketplace-child' ), $category->name ) ); ?>" aria-controls="marketplace-category-panel-<?php echo esc_attr( $category->term_id ); ?>" aria-expanded="<?php echo 0 === $index ? 'true' : 'false'; ?>">&#8250;</button>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
				<div class="marketplace-mega-panels">
					<?php foreach ( $marketplace_top_categories as $index => $category ) : ?>
						<?php $children = $marketplace_category_children[ $category->term_id ] ?? array(); ?>
						<section class="marketplace-mega-panel" id="marketplace-category-panel-<?php echo esc_attr( $category->term_id ); ?>" data-category-panel="<?php echo esc_attr( $category->term_id ); ?>"<?php echo 0 === $index ? '' : ' hidden'; ?>>
							<div class="marketplace-mega-subcategories">
								<a class="marketplace-mega-heading" href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?> <span aria-hidden="true">&#8594;</span></a>
								<?php foreach ( $children as $child_index => $child ) : ?>
									<div class="marketplace-mega-subcategory-row">
										<a class="marketplace-mega-subcategory" href="<?php echo esc_url( get_term_link( $child ) ); ?>" data-mega-subcategory="<?php echo esc_attr( $child->term_id ); ?>" aria-controls="marketplace-mega-products-<?php echo esc_attr( $child->term_id ); ?>"<?php echo 0 === $child_index ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $child->name ); ?></a>
										<button type="button" data-expand-subcategory="<?php echo esc_attr( $child->term_id ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Show products in %s', 'bobbyafrica-marketplace-child' ), $child->name ) ); ?>" aria-controls="marketplace-mega-products-<?php echo esc_attr( $child->term_id ); ?>">&#8250;</button>
									</div>
								<?php endforeach; ?>
							</div>
							<div class="marketplace-mega-product-column">
								<?php if ( $children ) : ?>
									<?php foreach ( $children as $child_index => $child ) : ?>
										<div class="marketplace-mega-product-list" id="marketplace-mega-products-<?php echo esc_attr( $child->term_id ); ?>" data-mega-products="<?php echo esc_attr( $child->term_id ); ?>" aria-live="polite"<?php echo 0 === $child_index ? '' : ' hidden'; ?>>
											<h3><?php echo esc_html( $child->name ); ?></h3>
											<p class="marketplace-mega-loading" hidden><?php esc_html_e( 'Loading products…', 'bobbyafrica-marketplace-child' ); ?></p>
											<div class="marketplace-mega-product-results"><p><?php esc_html_e( 'Point to a subcategory to browse its products.', 'bobbyafrica-marketplace-child' ); ?></p></div>
										</div>
									<?php endforeach; ?>
								<?php else : ?>
									<p><?php esc_html_e( 'Browse all products in this category.', 'bobbyafrica-marketplace-child' ); ?></p>
								<?php endif; ?>
							</div>
						</section>
					<?php endforeach; ?>
				</div>
		</div>
	</nav>
</header>
