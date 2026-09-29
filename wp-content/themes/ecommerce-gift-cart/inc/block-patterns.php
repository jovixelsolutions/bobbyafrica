<?php   
/**
 * Block Patterns
 *
 * @package Ecommerce Gift Cart
 * @since 1.2
 */

/**
 * Registers block patterns and categories.
 *
 * @since 1.0
 *
 * @return void
 */
function ecommerce_gift_cart_register_block_patterns() {
	$block_pattern_categories = array(
		'ecommerce-gift-cart' => array( 'label' => esc_html__( 'Ecommerce Gift Cart Patterns', 'ecommerce-gift-cart' ) ),
		'pages'    => array( 'label' => esc_html__( 'Pages', 'ecommerce-gift-cart' ) ),
	);

	$block_pattern_categories = apply_filters( 'ecommerce_gift_cart_block_pattern_categories', $block_pattern_categories );

	foreach ( $block_pattern_categories as $name => $properties ) {
		if ( ! WP_Block_Pattern_Categories_Registry::get_instance()->is_registered( $name ) ) {
			register_block_pattern_category( $name, $properties );
		}
	}

	$block_patterns = array(
		'header-default',
		'header-banner',
		'product-section',
		'sale-section',
		'about-section',
		'archive-inner-banner',
		'inner-banner',
		'post-one-column',
		'post-two-column',
		'latest-blog',
		'hidden-404',
		'sidebar',
		'footer-default',
	);

	$block_patterns = apply_filters( 'ecommerce_gift_cart_block_patterns', $block_patterns );

	foreach ( $block_patterns as $block_pattern ) {
		$pattern_file = get_parent_theme_file_path( '/inc/patterns/' . $block_pattern . '.php' );

		register_block_pattern(
			'ecommerce-gift-cart/' . $block_pattern,
			require $pattern_file
		);
	}
}
add_action( 'init', 'ecommerce_gift_cart_register_block_patterns', 9 );