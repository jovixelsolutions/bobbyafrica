<?php
/**
 * Modern Cart Woo slide out Header
 *
 * @package modern-cart-woo
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="moderncart-slide-out-header">
	<?php do_action( 'moderncart_slide_out_header_before' ); ?>
	<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
		<button type="button" class="moderncart-slide-out-header-close" data-dismiss="moderncart-modal" aria-expanded="true" aria-label="<?php esc_attr_e( 'Close cart', 'modern-cart' ); ?>">
			<?php if ( isset( $cart_type ) && 'popup' === $cart_type ) : ?>
				<?php // Display "cross sign" if current cart_type is popup. ?>
				<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none" color="#000" aria-hidden="true" focusable="false">
					<path d="m6.343 6.343 11.314 11.314m-11.314 0L17.657 6.343"/>
				</svg>
			<?php else : ?>
				<?php
				// If cart opens from the left, reverse the arrow logic.
				if ( isset( $cart_opening_direction ) && 'left' === $cart_opening_direction ) :
					if ( is_rtl() ) :
						// RTL - Right Arrow SVG.
						?>
						<svg xmlns="http://www.w3.org/2000/svg" width="57.48" height="28.003" viewBox="0 0 57.48 28.003" aria-hidden="true" focusable="false">
							<path d="M57.481 14a1.725 1.725 0 0 0-.415-1.013L45.275.461a1.475 1.475 0 0 0-2.084-.058 1.52 1.52 0 0 0-.058 2.084L52.6 12.528H1.474a1.474 1.474 0 1 0 0 2.948H52.6l-9.466 10.04a1.545 1.545 0 0 0 .058 2.084 1.476 1.476 0 0 0 2.084-.058l11.79-12.527A1.313 1.313 0 0 0 57.481 14z"></path>
						</svg>
						<?php
					else :
						// LTR - Left Arrow SVG.
						?>
						<svg xmlns="http://www.w3.org/2000/svg" width="57.48" height="28.003" viewBox="0 0 57.48 28.003" aria-hidden="true" focusable="false">
							<path d="M0 14a1.725 1.725 0 0 1 .415-1.013L12.206.461a1.475 1.475 0 0 1 2.084-.058 1.52 1.52 0 0 1 .058 2.084L4.88 12.528h51.026a1.474 1.474 0 1 1 0 2.948H4.88l9.466 10.04a1.545 1.545 0 0 1-.058 2.084 1.476 1.476 0 0 1-2.084-.058l-11.79-12.527A1.313 1.313 0 0 1 0 14z"></path>
						</svg>
						<?php
					endif;
				else :
					if ( is_rtl() ) :
						// RTL - Left Arrow SVG.
						?>
						<svg xmlns="http://www.w3.org/2000/svg" width="57.48" height="28.003" viewBox="0 0 57.48 28.003" aria-hidden="true" focusable="false">
							<path d="M0 14a1.725 1.725 0 0 1 .415-1.013L12.206.461a1.475 1.475 0 0 1 2.084-.058 1.52 1.52 0 0 1 .058 2.084L4.88 12.528h51.026a1.474 1.474 0 1 1 0 2.948H4.88l9.466 10.04a1.545 1.545 0 0 1-.058 2.084 1.476 1.476 0 0 1-2.084-.058l-11.79-12.527A1.313 1.313 0 0 1 0 14z"></path>
						</svg>
						<?php
					else :
						// LTR - Right Arrow SVG.
						?>
						<svg xmlns="http://www.w3.org/2000/svg" width="57.48" height="28.003" viewBox="0 0 57.48 28.003" aria-hidden="true" focusable="false">
							<path d="M57.481 14a1.725 1.725 0 0 0-.415-1.013L45.275.461a1.475 1.475 0 0 0-2.084-.058 1.52 1.52 0 0 0-.058 2.084L52.6 12.528H1.474a1.474 1.474 0 1 0 0 2.948H52.6l-9.466 10.04a1.545 1.545 0 0 0 .058 2.084 1.476 1.476 0 0 0 2.084-.058l11.79-12.527A1.313 1.313 0 0 0 57.481 14z"></path>
						</svg>
						<?php
					endif;
				endif;
				?>
			<?php endif; ?>
			<span class="moderncart-sr-only"><?php echo esc_html( __( 'Close cart', 'modern-cart' ) ); ?></span>
		</button>
		<div class="moderncart-slide-out-header-title">
			<span><?php echo esc_html( $title ); ?></span>
		</div>
		<div class="moderncart-slide-out-header-quantity" aria-live="polite" aria-atomic="true" aria-label="
		<?php
			/* translators: %d: number of items in cart */
			printf( esc_attr__( '%d items in cart', 'modern-cart' ), intval( $quantity ) );
		?>
		">
			<span><?php echo intval( $quantity ); ?></span>
		</div>
	</div>
	<?php do_action( 'moderncart_slide_out_header_after' ); ?>
</div>
