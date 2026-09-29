<?php
/**
 * Modern Cart Woo empty cart html
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<span><?php echo esc_html( $headline ); ?></span>
	<p><?php echo esc_html( $subheader ); ?></p>
</div>
