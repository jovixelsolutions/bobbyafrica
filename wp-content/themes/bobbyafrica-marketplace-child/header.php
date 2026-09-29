<?php
/**
 * Custom marketplace header for BobbyAfrica child theme.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="marketplace-header">
	<div class="marketplace-topbar">
		<div class="container">
			<span><?php esc_html_e( 'Fast delivery across major cities', 'bobbyafrica-marketplace-child' ); ?></span>
			<span><?php esc_html_e( 'Secure checkout', 'bobbyafrica-marketplace-child' ); ?></span>
			<span><?php esc_html_e( 'Support 24/7', 'bobbyafrica-marketplace-child' ); ?></span>
		</div>
	</div>
	<div class="container marketplace-header-main">
		<a class="marketplace-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<span class="marketplace-logo-mark">BA</span>
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
					<span class="marketplace-badge"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
				<?php endif; ?>
			</a>
		</div>
	</div>
	<nav class="marketplace-primary-nav" aria-label="Primary navigation">
		<div class="container">
			<ul class="marketplace-nav-links">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'bobbyafrica-marketplace-child' ); ?></a></li>
				<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop', 'bobbyafrica-marketplace-child' ); ?></a></li>
				<?php
				$terms = bobbyafrica_get_product_categories();
				foreach ( $terms as $term ) {
					printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_term_link( $term ) ), esc_html( $term->name ) );
				}
				?>
			</ul>
		</div>
	</nav>
	<div class="marketplace-mobile-menu container">
		<button type="button" aria-expanded="false"><?php esc_html_e( 'Menu', 'bobbyafrica-marketplace-child' ); ?></button>
		<ul class="marketplace-nav-links" hidden>
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'bobbyafrica-marketplace-child' ); ?></a></li>
			<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop', 'bobbyafrica-marketplace-child' ); ?></a></li>
			<?php foreach ( $terms ?? array() as $term ) { printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_term_link( $term ) ), esc_html( $term->name ) ); } ?>
		</ul>
	</div>
</header>
