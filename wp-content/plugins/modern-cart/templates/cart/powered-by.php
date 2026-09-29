<?php
/**
 * Modern Cart Woo powered by content
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<?php
	printf(
		'%2$s <a href="%1$s" target="_blank" rel="noopener noreferrer">%3$s <span class="screen-reader-text">%4$s</span></a>',
		esc_url( apply_filters( 'moderncart_powered_by_link', $url ) ),
		esc_html__( 'Powered by', 'modern-cart' ),
		esc_html__( 'CartFlows', 'modern-cart' ),
		esc_html__( '(opens in a new tab)', 'modern-cart' )
	);
	?>
</div>
