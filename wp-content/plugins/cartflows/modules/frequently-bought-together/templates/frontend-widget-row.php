<?php
/**
 * Frequently Bought Together — single widget row (main, simple, or variable).
 *
 * Included per row by frontend-widget.php. Variable rows wrap the label and the
 * variation select in a row-group so the pair collapses and separates as one unit.
 *
 * @package cartflows
 * @var array{id: int, name: string, image: string, available: bool, is_main: bool, is_extra: bool, show_qty: bool, qty: int, price: float, price_html: string, from_label: string, requires_options: bool, permalink: string, variations: array<int, array{id: int, price: float, label: string}>} $fbt_row Row display data from Cartflows_Fbt_Frontend::get_row_data().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fbt_is_variable = ! empty( $fbt_row['variations'] );
$fbt_needs_page  = ! empty( $fbt_row['requires_options'] ) && '' !== $fbt_row['permalink'];

$fbt_row_class = 'wcf-fbt-widget-row';
if ( $fbt_row['is_main'] ) {
	$fbt_row_class .= ' is-main';
}
if ( ! $fbt_row['available'] ) {
	$fbt_row_class .= ' is-unavailable';
}
if ( $fbt_needs_page ) {
	$fbt_row_class .= ' requires-options';
}
if ( $fbt_row['is_extra'] && ! $fbt_is_variable ) {
	$fbt_row_class .= ' is-extra';
}
?>
<?php if ( $fbt_is_variable ) : ?>
<div class="wcf-fbt-widget-row-group<?php echo $fbt_row['is_extra'] ? ' is-extra' : ''; ?>">
<?php endif; ?>
<label class="<?php echo esc_attr( $fbt_row_class ); ?>">
	<?php if ( $fbt_needs_page ) : ?>
		<span class="wcf-fbt-widget-box-spacer" aria-hidden="true"></span>
	<?php elseif ( $fbt_row['is_main'] ) : ?>
		<input type="checkbox" checked disabled data-price="<?php echo esc_attr( (string) $fbt_row['price'] ); ?>" />
	<?php else : ?>
		<input type="checkbox" class="wcf-fbt-widget-input" value="<?php echo esc_attr( (string) $fbt_row['id'] ); ?>" data-price="<?php echo esc_attr( (string) $fbt_row['price'] ); ?>" <?php disabled( ! $fbt_row['available'] ); ?> />
	<?php endif; ?>
	<span class="wcf-fbt-widget-thumb"><img src="<?php echo esc_url( $fbt_row['image'] ); ?>" alt="" loading="lazy" /></span>
	<span class="wcf-fbt-widget-name"><?php echo esc_html( $fbt_row['name'] ); ?>
	<?php
	if ( $fbt_row['is_main'] ) :
		?>
		<em>(<?php esc_html_e( 'this item', 'cartflows' ); ?>)</em><?php endif; ?></span>
	<?php if ( ! $fbt_row['available'] ) : ?>
		<span class="wcf-fbt-widget-stock-badge"><?php esc_html_e( 'Out of stock', 'cartflows' ); ?></span>
	<?php endif; ?>
	<?php if ( $fbt_row['show_qty'] && ! $fbt_row['is_main'] && ! $fbt_needs_page ) : ?>
		<input type="number" min="1" step="1" class="wcf-fbt-widget-qty" value="<?php echo esc_attr( (string) $fbt_row['qty'] ); ?>" aria-label="<?php esc_attr_e( 'Quantity', 'cartflows' ); ?>" <?php disabled( ! $fbt_row['available'] ); ?> />
	<?php endif; ?>
	<?php if ( $fbt_is_variable ) : ?>
		<span class="wcf-fbt-widget-price is-from" data-from="<?php echo esc_attr( $fbt_row['from_label'] ); ?>"><?php echo esc_html( $fbt_row['from_label'] ); ?></span>
	<?php else : ?>
		<span class="wcf-fbt-widget-price"><?php echo wp_kses_post( $fbt_row['price_html'] ); ?></span>
	<?php endif; ?>
	<?php if ( $fbt_needs_page ) : ?>
		<a class="wcf-fbt-widget-options-link" href="<?php echo esc_url( $fbt_row['permalink'] ); ?>"><?php esc_html_e( 'Select options', 'cartflows' ); ?></a>
	<?php endif; ?>
</label>
<?php if ( $fbt_is_variable ) : ?>
	<div class="wcf-fbt-widget-variation">
		<select class="wcf-fbt-widget-variation-select" aria-label="<?php esc_attr_e( 'Product variation', 'cartflows' ); ?>">
			<option value=""><?php esc_html_e( 'Choose an option…', 'cartflows' ); ?></option>
			<?php foreach ( $fbt_row['variations'] as $fbt_choice ) : ?>
				<option value="<?php echo esc_attr( (string) $fbt_choice['id'] ); ?>" data-price="<?php echo esc_attr( (string) $fbt_choice['price'] ); ?>"><?php echo esc_html( $fbt_choice['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
		<div class="wcf-fbt-widget-variation-hint"><?php esc_html_e( 'Select a variation to continue', 'cartflows' ); ?></div>
	</div>
</div>
<?php endif; ?>
