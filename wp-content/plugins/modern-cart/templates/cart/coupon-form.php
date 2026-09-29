<?php
/**
 * Modern Cart Woo slide out coupon
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_error_notice = ! empty( $data_args['message_type'] ) && ! empty( $data_args['notice'] ) && 'error' === $data_args['message_type'];

if ( $has_error_notice ) {
	$classes[] = 'moderncart-invalid-coupon-code-error';
}
?>

<?php do_action( 'moderncart_slide_out_coupon_wrapper_start' ); ?>
<div class="moderncart-have-coupon-code-area"
	aria-expanded="<?php echo esc_attr( 'moderncart-hide' === $arrow_down ? 'true' : 'false' ); ?>"
		aria-controls="moderncart-coupon-form-container"
		role="button"
		tabindex="0"
>
		<span class="moderncart-have-coupon-code"><?php echo esc_html( $title ); ?></span>
		<svg class="moderncart-arrow-down-icon <?php echo esc_attr( $arrow_down ); ?>" width="25px" height="25px" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M19.302 9.864C19.608 10.169 19.608 10.664 19.302 10.969L13.052 17.219C12.747 17.524 12.253 17.524 11.948 17.219L5.698 10.969C5.392 10.664 5.392 10.169 5.698 9.864C6.003 9.559 6.497 9.559 6.802 9.864L12.5 15.562L18.198 9.864C18.503 9.559 18.997 9.559 19.302 9.864Z" fill="#030D45"/>
		</svg>
		<svg class="moderncart-arrow-up-icon <?php echo esc_attr( $arrow_up ); ?>" width="25px" height="25px" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M5.698 15.136C5.392 14.831 5.392 14.336 5.698 14.031L11.948 7.781C12.253 7.476 12.747 7.476 13.052 7.781L19.302 14.031C19.608 14.336 19.608 14.831 19.302 15.136C18.997 15.441 18.503 15.441 18.198 15.136L12.5 9.438L6.802 15.136C6.497 15.441 6.003 15.441 5.698 15.136Z" fill="#030D45"/>
		</svg>
</div>
<div data-order-summary-style="<?php echo ! empty( $data_args['order_summary_style'] ) ? esc_attr( $data_args['order_summary_style'] ) : 'style1'; ?>" id="moderncart-coupon-form-container" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" aria-hidden="<?php echo esc_attr( in_array( 'moderncart-hide', $classes, true ) ? 'true' : 'false' ); ?>">
	<?php do_action( 'moderncart_slide_out_coupon_form_before' ); ?>
	<form class="moderncart-coupon-form" aria-labelledby="moderncart-coupon-form-label">
		<p id="moderncart-coupon-form-label" class="moderncart-sr-only"><?php esc_html_e( 'Enter coupon code', 'modern-cart' ); ?></p>
		<div class="moderncart-slide-out-coupon-input">
			<label for="moderncart-coupon-input" class="moderncart-sr-only"><?php esc_html_e( 'Coupon code', 'modern-cart' ); ?></label>
			<input type="text" 
				id="moderncart-coupon-input" 
				placeholder="<?php echo esc_attr( $placeholder_text ); ?>" 
				required 
				aria-required="true">
			<button type="submit" 
				class="moderncart-slide-out-coupon-form-button moderncart-button" 
				aria-label="<?php esc_attr_e( 'Apply coupon code', 'modern-cart' ); ?>">
				<?php echo esc_html( $button_text ); ?>
			</button>
		</div>
		<?php
		if ( $has_error_notice ) {
			echo '<div role="alert" aria-live="assertive">';
			echo wp_kses(
				$data_args['notice'],
				[
					'div' => [
						'class'     => [],
						'data-type' => [],
					],
				]
			);
			echo '</div>';
		}
		?>
	</form>
	<?php do_action( 'moderncart_slide_out_coupon_form_after' ); ?>
</div>
<?php do_action( 'moderncart_slide_out_coupon_wrapper_end' ); ?>
