<?php
if ( is_shop() || is_product_category() || is_product_tag() ) {
	wc_get_template( 'archive-product.php' );
} else {
	get_header();
	woocommerce_content();
	get_footer();
}
