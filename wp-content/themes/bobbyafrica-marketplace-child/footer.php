<?php
/**
 * Jovixels Marketplace footer.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_categories = function_exists( 'bobbyafrica_get_product_categories' ) ? bobbyafrica_get_product_categories() : array();
$footer_payment_gateways = array();
$footer_logo_id = get_theme_mod( 'custom_logo' );
$footer_terms_id = absint( get_option( 'woocommerce_terms_page_id' ) );

if ( function_exists( 'WC' ) && WC()->payment_gateways() ) {
	foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
		if ( isset( $gateway->enabled ) && 'yes' === $gateway->enabled ) {
			$footer_payment_gateways[] = $gateway;
		}
	}
}
?>
<footer class="marketplace-footer">
	<div class="container">
		<div class="marketplace-footer-grid">
			<div class="marketplace-footer-column marketplace-footer-brand">
				<?php if ( $footer_logo_id ) : ?>
					<a class="marketplace-footer-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
						<?php echo wp_get_attachment_image( $footer_logo_id, 'full', false, array( 'alt' => '' ) ); ?>
					</a>
				<?php endif; ?>
				<h3><?php bloginfo( 'name' ); ?></h3>
				<p><?php esc_html_e( 'Everyday finds for home, work and everything in between.', 'bobbyafrica-marketplace-child' ); ?></p>
			</div>
			<div class="marketplace-footer-column">
				<h4><?php esc_html_e( 'Shop by category', 'bobbyafrica-marketplace-child' ); ?></h4>
				<ul>
					<?php foreach ( array_slice( $footer_categories, 0, 6 ) as $category ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
					<?php endforeach; ?>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'View all products', 'bobbyafrica-marketplace-child' ); ?></a></li>
				</ul>
			</div>
			<div class="marketplace-footer-column">
				<h4><?php esc_html_e( 'Customer service', 'bobbyafrica-marketplace-child' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Orders & account', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<li><a href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Shopping cart', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<?php if ( get_privacy_policy_url() ) : ?>
						<li><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy policy', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<?php endif; ?>
					<?php if ( $footer_terms_id ) : ?>
						<li><a href="<?php echo esc_url( get_permalink( $footer_terms_id ) ); ?>"><?php esc_html_e( 'Terms & conditions', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="marketplace-footer-column">
				<h4><?php esc_html_e( 'More', 'bobbyafrica-marketplace-child' ); ?></h4>
				<?php if ( has_nav_menu( 'footer' ) ) : ?>
					<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'marketplace-footer-menu', 'depth' => 1 ) ); ?>
				<?php else : ?>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'bobbyafrica-marketplace-child' ); ?></a></li>
						<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop', 'bobbyafrica-marketplace-child' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( ! empty( $footer_payment_gateways ) ) : ?>
			<section class="marketplace-footer-payments" aria-labelledby="marketplace-footer-payments-title">
				<h4 id="marketplace-footer-payments-title"><?php esc_html_e( 'Payment methods', 'bobbyafrica-marketplace-child' ); ?></h4>
				<ul class="marketplace-payment-methods">
					<?php foreach ( $footer_payment_gateways as $gateway ) : ?>
						<li>
							<?php if ( $gateway->get_icon() ) : ?>
								<span class="marketplace-payment-icon" aria-hidden="true"><?php echo wp_kses_post( $gateway->get_icon() ); ?></span>
							<?php endif; ?>
							<span><?php echo esc_html( $gateway->get_title() ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endif; ?>

		<div class="footer-bottom">
			<?php echo esc_html( sprintf( __( '© %s %s. All rights reserved.', 'bobbyafrica-marketplace-child' ), date_i18n( 'Y' ), get_bloginfo( 'name' ) ) ); ?>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
