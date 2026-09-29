<?php
/**
 * Cartflows view for cart abandonment reports.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$action_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=cartflows' ), 'install-plugin_cartflows' );
// Array of features.
$features = [
	[
		'title'       => __( 'Faster & Optimized Checkout', 'woo-cart-abandonment-recovery' ),
		'description' => __( "Streamline your checkout process with CartFlows' high-converting pages for a faster, smoother purchase experience.", 'woo-cart-abandonment-recovery' ),
		'icon'        => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"></path>
                      </svg>',
	],
	[
		'title'       => __( 'Increase AOV with One Click Upsells', 'woo-cart-abandonment-recovery' ),
		'description' => __( 'Increase your AOV (Average Order Value) by offering one-click upsells without re-entering payment details.', 'woo-cart-abandonment-recovery' ),
		'icon'        => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"></path>
                      </svg>',
	],
	[
		'title'       => __( 'Increase Revenue with Order Bumps', 'woo-cart-abandonment-recovery' ),
		'description' => __( 'Let customers add complementary products to their order directly at checkout, increasing sales without disrupting the flow.', 'woo-cart-abandonment-recovery' ),
		'icon'        => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"></path>
              </svg>',
	],
	[
		'title'       => __( 'A/B Split Testing', 'woo-cart-abandonment-recovery' ),
		'description' => __( 'Try different funnel variations to find the best checkout and sales strategies for higher conversions.', 'woo-cart-abandonment-recovery' ),
		'icon'        => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"></path>
              </svg>',
	],
	[
		'title'       => __( 'Conversion Optimized Templates', 'woo-cart-abandonment-recovery' ),
		'description' => __( 'Quickly build sales funnels, product pages, and checkout flows using ready-made, high-converting templates.', 'woo-cart-abandonment-recovery' ),
		'icon'        => '<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3"></path>
              </svg>',
	],
	[
		'title'       => __( 'Cart Abandonment Recovery', 'woo-cart-abandonment-recovery' ),
		'description' => __( 'Recover lost sales with email reminders and personalized offers to bring customers back to complete their purchases.', 'woo-cart-abandonment-recovery' ),
		'icon'        => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4V9H4.58152M19.9381 11C19.446 7.05369 16.0796 4 12 4C8.64262 4 5.76829 6.06817 4.58152 9M4.58152 9H9M20 20V15H19.4185M19.4185 15C18.2317 17.9318 15.3574 20 12 20C7.92038 20 4.55399 16.9463 4.06189 13M19.4185 15H15" stroke="#F06434" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>',
	],
];

?>

<main class="wcf-ca-cf-promotion cartflows-promotion">
		<!-- Header Section -->
		<header class="wcf-ca-cf-promo-header-section">
			<div class="wcf-ca-cf-promo-header-content" style="background-image: url('<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/background-image.png' ); ?>');">
				<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/cf-logo.svg' ); ?>" alt="<?php _e( 'CartFlows Logo', 'woo-cart-abandonment-recovery' ); ?>" class="brand-logo">
				<div class="wcf-ca-cf-promo-header-tagline">
				<h1><?php _e( 'Boost Your Conversions & Revenue<br>with CartFlows!', 'woo-cart-abandonment-recovery' ); ?></h1>
				<p><?php _e( 'Create easy sales funnels, increase conversions, and grow your business effortlessly.', 'woo-cart-abandonment-recovery' ); ?></p>
				</div>
				<a href="<?php echo esc_url( $action_url ); ?>" class="wcf-ca-cf-promo-install-button" id="wcf-ca-cf-promo-install-button">
					<?php _e( 'Install CartFlows', 'woo-cart-abandonment-recovery' ); ?>
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="icon">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
					</svg>
				</a>
				<a href="https://cartflows.com/" class="wcf-ca-cf-promo-visit-website" target="_blank"><?php _e( 'Visit CartFlows Website', 'woo-cart-abandonment-recovery' ); ?></a>
			</div>
		</header>

		<!-- Features Section -->
		<section class="wcf-ca-cf-promo-content-section">
			<div class="wcf-ca-cf-promo-features-section">
				<h2><?php _e( 'See How CartFlows Helps You Get More Customers and Revenue', 'woo-cart-abandonment-recovery' ); ?></h2>
				<div class="wcf-ca-cf-promo-features-grid">
					<!-- Feature Items -->
					<?php foreach ( $features as $feature ) { ?>
					<div class="wcf-ca-cf-promo-feature-item">
						<span class="wcf-ca-cf-promo-feature-icon">
							<?php echo $feature['icon']; // phpcs:ignore: WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</span>
						<h3><?php echo esc_html( $feature['title'] ); ?></h3>
						<p><?php echo esc_html( $feature['description'] ); ?></p>
					</div>
					<?php } ?>
					<!-- Repeat for other features -->
				</div>
				<p><?php _e( 'These are just some examples. The possibilities are truly endless!', 'woo-cart-abandonment-recovery' ); ?></p>
			</div>
			<hr class="wcf-ca-cf-promo-divider" />
			<!-- Brands Section -->
			<div class="wcf-ca-cf-promo-brands-section">
				<h2><?php _e( 'Top brands trust CartFlows to create high-converting funnels!', 'woo-cart-abandonment-recovery' ); ?></h2>
				<div class="wcf-ca-cf-promo-brands-grid">
					<!-- Brand Logos -->
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/astra.svg' ); ?>" alt="<?php esc_attr_e( 'Astra Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/Elementor.svg' ); ?>" alt="<?php esc_attr_e( 'Elementor Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/paypal.svg' ); ?>" alt="<?php esc_attr_e( 'PayPal Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/starter-logo.svg' ); ?>" alt="<?php esc_attr_e( 'Starter Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/stripe.svg' ); ?>" alt="<?php esc_attr_e( 'Stripe Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/SureCart.svg' ); ?>" alt="<?php esc_attr_e( 'SureCart Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/surefeedback.svg' ); ?>" alt="<?php esc_attr_e( 'SureFeedback Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/suremail.svg' ); ?>" alt="<?php esc_attr_e( 'SureMail Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/ottokit.svg' ); ?>" alt="<?php esc_attr_e( 'OttoKit Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/Woo_logo.svg' ); ?>" alt="<?php esc_attr_e( 'WooCommerce Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/zipwp.svg' ); ?>" alt="<?php esc_attr_e( 'ZipWP Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/spectra.svg' ); ?>" alt="<?php esc_attr_e( 'Spectra Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<img src="<?php echo esc_url( CARTFLOWS_CA_URL . 'admin/assets/images/spectra.svg' ); ?>" alt="<?php esc_attr_e( 'SureForms Logo', 'woo-cart-abandonment-recovery' ); ?>" class="wcf-ca-cf-promo-brand-logo">
					<!-- Repeat for other logos -->
				</div>
				<!-- Call To Action -->
				<div class="wcf-ca-cf-promo-cta-section">
					<div>
					<h2><?php _e( 'Join Thousands of Customers Who Are Already Boosting Sales with CartFlows!', 'woo-cart-abandonment-recovery' ); ?></h2>
					<p><?php _e( 'Connect your store and boost your sales.', 'woo-cart-abandonment-recovery' ); ?></p>
					</div>
					<a href="<?php echo esc_url( $action_url ); ?>" class="wcf-ca-cf-promo-cta-button">
						<?php _e( 'Install Now', 'woo-cart-abandonment-recovery' ); ?>
						<svg data-slot="icon" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="width:1rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"></path></svg>
					</a>
				</div>
			</div>
		</section>
</main>
