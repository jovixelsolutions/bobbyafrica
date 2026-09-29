<?php
/**
 * Slide out html
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data = [
	'classes'      => $classes,
	'attributes'   => $attributes,
	'notice'       => $notice,
	'message_type' => isset( $message_type ) ? $message_type : '',
];
?>

<div id="moderncart-slide-out-modal" class="<?php echo esc_attr( implode( ' ', array_filter( $modal_classes ) ) ); ?>" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Shopping Cart', 'modern-cart' ); ?>">
	<?php moderncart_get_template_part( 'shop/slide-out-inner', '', $data ); ?>
</div>
<div id="live-region" aria-live="polite"></div>
