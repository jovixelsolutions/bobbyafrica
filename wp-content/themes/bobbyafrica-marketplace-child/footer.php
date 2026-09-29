<?php
/**
 * Marketplace footer.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="marketplace-footer">
	<div class="container">
		<div class="footer-grid">
			<div class="footer-card">
				<h3><?php bloginfo( 'name' ); ?></h3>
				<p><?php esc_html_e( 'Your trusted marketplace for everyday essentials, household favorites, and modern lifestyle finds.', 'bobbyafrica-marketplace-child' ); ?></p>
			</div>
			<div class="footer-card">
				<h4><?php esc_html_e( 'Customer service', 'bobbyafrica-marketplace-child' ); ?></h4>
				<ul>
					<li><a href="#"><?php esc_html_e( 'Help center', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Shipping & delivery', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Returns', 'bobbyafrica-marketplace-child' ); ?></a></li>
				</ul>
			</div>
			<div class="footer-card">
				<h4><?php esc_html_e( 'Company', 'bobbyafrica-marketplace-child' ); ?></h4>
				<ul>
					<li><a href="#"><?php esc_html_e( 'About us', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Privacy policy', 'bobbyafrica-marketplace-child' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Terms & conditions', 'bobbyafrica-marketplace-child' ); ?></a></li>
				</ul>
			</div>
			<div class="footer-card">
				<h4><?php esc_html_e( 'Stay in the loop', 'bobbyafrica-marketplace-child' ); ?></h4>
				<form action="#" method="post">
					<input type="email" aria-label="Email" placeholder="<?php esc_attr_e( 'Your email', 'bobbyafrica-marketplace-child' ); ?>" />
					<button type="submit"><?php esc_html_e( 'Subscribe', 'bobbyafrica-marketplace-child' ); ?></button>
				</form>
			</div>
		</div>
		<div class="footer-bottom">
			<?php echo esc_html( sprintf( __( '© %s %s. All rights reserved.', 'bobbyafrica-marketplace-child' ), date_i18n( 'Y' ), get_bloginfo( 'name' ) ) ); ?>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
