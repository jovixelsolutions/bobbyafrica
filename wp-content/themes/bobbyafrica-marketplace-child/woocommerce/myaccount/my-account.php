<?php
/**
 * WooCommerce myaccount override
 * Original template: woocommerce/myaccount/my-account.php
 * WooCommerce version: 10.7.0
 * Reason: style the customer account area with a marketplace dashboard layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

wc_print_notices();
?>

<main class="marketplace-page">
	<div class="container marketplace-page-shell">
		<h1><?php esc_html_e( 'My account', 'bobbyafrica-marketplace-child' ); ?></h1>
		<div class="account-layout">
			<nav class="account-sidebar" aria-label="Account navigation">
				<?php do_action( 'woocommerce_account_navigation' ); ?>
			</nav>
			<div class="account-content">
				<?php do_action( 'woocommerce_account_content' ); ?>
			</div>
		</div>
	</div>
</main>
