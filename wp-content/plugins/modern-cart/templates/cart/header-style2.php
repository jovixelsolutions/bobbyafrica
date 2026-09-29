<?php
/**
 * Modern Cart Woo slide out Header
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="moderncart-slide-out-header">
	<?php do_action( 'moderncart_slide_out_header_before' ); ?>
	<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<div class="moderncart-slide-out-header-title">
			<span><?php echo esc_html( $title ); ?></span>
		</div>
		<button type="button" class="moderncart-slide-out-header-close" data-dismiss="moderncart-modal" aria-label="<?php esc_attr_e( 'Close cart', 'modern-cart' ); ?>">
			<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" color="#000" aria-hidden="true" focusable="false">
					<path d="m6.343 6.343 11.314 11.314m-11.314 0L17.657 6.343"/>
				</svg>
			<span class="moderncart-sr-only"><?php echo esc_html( __( 'Close cart', 'modern-cart' ) ); ?></span>
		</button>
	</div>
	<?php do_action( 'moderncart_slide_out_header_after' ); ?>
</div>
