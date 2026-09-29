<?php
/**
 * Modern Cart Woo floating inner html
 *
 * @package modern-cart
 * @version 0.0.1
 */

use ModernCart\Inc\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_svg_tags = Helper::get_allowed_tags_kses( 'svg' );

?>

<button class="moderncart-floating-cart-button"
aria-label="
<?php 
/* translators: %d: number of items in cart */
printf( esc_attr__( 'Cart Button with %d items', 'modern-cart' ), esc_attr( $item_account ) );
?>
"
	aria-haspopup="dialog"
	aria-expanded="false"
	aria-controls="moderncart-slide-out-modal" 
	aria-live="polite"
>
	<div class="moderncart-floating-cart-count">
		<span><?php echo esc_html( $item_account ); ?></span>
	</div>
	<span class="moderncart-floating-cart-icon">
		<?php
		if ( 'custom' === $cart_icon && ! empty( $custom_icon_url ) ) {
			// Display custom uploaded icon.
			$allowed_img_tags = Helper::get_allowed_tags_kses( 'img' );
			echo wp_kses(
				'<img src="' . esc_url( $custom_icon_url ) . '" alt="' . esc_attr__( 'Cart', 'modern-cart' ) . '" class="moderncart-custom-cart-icon" width="36" height="36" />',
				$allowed_img_tags
			);
		} else {
			// Display predefined SVG icon.
			echo wp_kses( $cart_svg_icons[ (int) $cart_icon ] ?? $cart_svg_icons[0], $allowed_svg_tags );
		}
		?>
	</span>
</button>
<p class="moderncart-floating-cart-button-notification moderncart-add-to-cart-notification" style="display: none;"><?php esc_html_e( 'Added to cart', 'modern-cart' ); ?></p>
