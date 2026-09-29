<?php
/**
 * Modern Cart Woo slide out footer
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="moderncart-slide-out-footer">
	<?php do_action( 'moderncart_slide_out_footer_before' ); ?>
	<?php do_action( 'moderncart_slide_out_footer_content', $args ); ?>
	<?php do_action( 'moderncart_slide_out_footer_after' ); ?>
</div>
