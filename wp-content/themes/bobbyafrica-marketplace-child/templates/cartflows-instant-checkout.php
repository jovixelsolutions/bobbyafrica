<?php
/**
 * Theme-wrapped CartFlows instant checkout template.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wp_enqueue_style(
	'bobbyafrica-step-checkout-theme',
	get_stylesheet_uri(),
	array(),
	filemtime( get_stylesheet_directory() . '/style.css' )
);
wp_enqueue_style(
	'bobbyafrica-step-checkout-main',
	get_stylesheet_directory_uri() . '/assets/css/main.css',
	array( 'bobbyafrica-step-checkout-theme' ),
	filemtime( get_stylesheet_directory() . '/assets/css/main.css' )
);
wp_enqueue_style(
	'bobbyafrica-step-checkout-responsive',
	get_stylesheet_directory_uri() . '/assets/css/responsive.css',
	array( 'bobbyafrica-step-checkout-main' ),
	filemtime( get_stylesheet_directory() . '/assets/css/responsive.css' )
);

get_header();

$checkout_html = do_shortcode( '[cartflows_checkout]' );
?>
<main class="marketplace-page marketplace-step-checkout">
	<div class="container marketplace-page-shell">
		<?php
		if ( empty( $checkout_html ) || '<div class="woocommerce"></div>' === trim( $checkout_html ) ) {
			do_action( 'cartflows_checkout_cart_empty', get_the_ID() );
		} else {
			echo $checkout_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</div>
</main>
<?php
get_footer();
