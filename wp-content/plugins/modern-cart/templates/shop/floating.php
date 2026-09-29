<?php
/**
 * Modern Cart Woo floating html
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div id="moderncart-floating-cart" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<?php
	moderncart_get_template_part(
		'shop/floating-inner',
		'',
		[
			'item_account'   => $item_account,
			'cart_icon'      => $cart_icon,
			'cart_svg_icons' => $cart_svg_icons,
		] 
	);
	?>
</div>
