<?php
get_header();
?>
<main class="marketplace-page">
	<div class="container marketplace-page-shell">
		<?php if ( is_product() ) : ?>
			<?php woocommerce_breadcrumb(); ?>
		<?php endif; ?>
		<?php woocommerce_content(); ?>
	</div>
</main>
<?php get_footer(); ?>
