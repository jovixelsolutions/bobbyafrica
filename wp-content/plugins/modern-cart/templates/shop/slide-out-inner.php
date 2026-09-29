<?php
/**
 * Slide out inner html
 *
 * @package modern-cart
 * @version 0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wp_kses_allowed = [
	'div' => [
		'class'       => [],
		'data-type'   => [],
		'role'        => [],
		'aria-live'   => [],
		'aria-label'  => [],
		'aria-atomic' => [],
	],
];
?>

<?php do_action( 'moderncart_slide_out_wrapper_start' ); ?>
<div id="moderncart-slide-out" class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>">
	<?php do_action( 'moderncart_slide_out_panel_wrapper_start' ); ?>
	<div class="moderncart-panel">
		<?php if ( isset( $message_type ) && 'success' === $message_type ) { ?>
		<div class="moderncart-slide-out-notices-wrapper">
			<?php echo wp_kses( $notice, $wp_kses_allowed ); ?>
		</div>
		<?php } ?>
		<?php do_action( 'moderncart_slide_out_content', $args ); ?>
	</div>
	<?php do_action( 'moderncart_slide_out_panel_wrapper_end' ); ?>
</div>
<?php do_action( 'moderncart_slide_out_wrapper_end' ); ?>
