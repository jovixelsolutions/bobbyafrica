<?php
/**
 * Frequently Bought Together — Frontend widget template (Config B, checkbox list).
 *
 * Main product renders first as a disabled "this item" row; the rest are checkable
 * companions. Every row renders through frontend-widget-row.php, fed by
 * Cartflows_Fbt_Frontend::get_row_data(). A single button submits the bundle.
 *
 * @package cartflows
 * @var array<int, WC_Product>                                                                                                                                              $products     Companion products, stock-filtered by the caller.
 * @var array{enabled: string, source: string, product_ids: array<int, int>, add_separately: string, selection: string, position: string, custom_qty: string, product_qty: array<int, int>, ai_product_ids: array<int, int>} $fbt_settings Full FBT settings blob.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $products ) ) {
	return;
}

$fbt_frontend     = Cartflows_Fbt_Frontend::get_instance();
$fbt_source       = wc_get_product( get_the_ID() );
$fbt_source_id    = $fbt_source ? (int) $fbt_source->get_id() : 0;
$fbt_source_price = $fbt_source ? (float) wc_get_price_to_display( $fbt_source ) : 0.0;
$fbt_show_qty     = 'yes' === $fbt_settings['custom_qty'];
$fbt_qty_map      = $fbt_settings['product_qty'];

$fbt_rows = array();
if ( $fbt_source ) {
	$fbt_rows[] = $fbt_frontend->get_row_data( $fbt_source, array( 'is_main' => true ) );
}
foreach ( $products as $fbt_product ) {
	if ( ! $fbt_product instanceof WC_Product ) {
		continue;
	}
	$fbt_rows[] = $fbt_frontend->get_row_data(
		$fbt_product,
		array(
			'show_qty' => $fbt_show_qty,
			'qty'      => isset( $fbt_qty_map[ $fbt_product->get_id() ] ) ? (int) $fbt_qty_map[ $fbt_product->get_id() ] : 1,
		)
	);
}
$fbt_rows = array_values( array_filter( $fbt_rows ) );

// Collapse the widget when it would otherwise show more than 3 companions.
$fbt_visible_limit = 3;
$fbt_companions    = 0;
foreach ( $fbt_rows as $fbt_i => $fbt_row ) {
	if ( ! $fbt_row['is_main'] ) {
		++$fbt_companions;
		$fbt_rows[ $fbt_i ]['is_extra'] = $fbt_companions > $fbt_visible_limit;
	}
}
$fbt_extras = max( 0, $fbt_companions - $fbt_visible_limit );

// Block themes (FSE) style buttons via wp-element-button; classic themes theme
// WC's own Add-to-Cart button via the WC classes below. Pick the right hook.
$fbt_button_class = wp_is_block_theme()
	? 'wcf-fbt-widget-add wp-element-button'
	: 'wcf-fbt-widget-add button alt single_add_to_cart_button';
?>
<section class="wcf-fbt-widget<?php echo 'single' === $fbt_settings['selection'] ? ' is-single' : ''; ?><?php echo $fbt_extras > 0 ? ' is-collapsed' : ''; ?>" data-source-id="<?php echo esc_attr( (string) $fbt_source_id ); ?>">
	<h3 class="wcf-fbt-widget-title"><?php esc_html_e( 'Frequently bought together', 'cartflows' ); ?></h3>
	<div class="wcf-fbt-widget-list">
		<?php
		foreach ( $fbt_rows as $fbt_row ) {
			include CARTFLOWS_FBT_DIR . 'templates/frontend-widget-row.php';
		}
		?>
	</div>
	<?php if ( $fbt_extras > 0 ) : ?>
		<button type="button" class="wcf-fbt-widget-toggle" data-extras="<?php echo esc_attr( (string) $fbt_extras ); ?>">
			<?php
			printf(
				/* translators: %d: number of additional companion products. */
				esc_html( _n( 'Show %d more', 'Show %d more', $fbt_extras, 'cartflows' ) ),
				esc_html( (string) $fbt_extras )
			);
			?>
		</button>
	<?php endif; ?>
	<div class="wcf-fbt-widget-footer">
		<div class="wcf-fbt-widget-total-wrap">
			<div class="wcf-fbt-widget-total-label" data-role="wcf-fbt-label"><?php echo esc_html( sprintf( /* translators: %d: item count. */ _n( 'Total for %d item', 'Total for %d items', 1, 'cartflows' ), 1 ) ); ?></div>
			<div class="wcf-fbt-widget-total" data-role="wcf-fbt-total"><?php echo wp_kses_post( (string) wc_price( $fbt_source_price ) ); ?></div>
		</div>
		<button type="button" class="<?php echo esc_attr( $fbt_button_class ); ?>"><?php esc_html_e( 'Add to cart', 'cartflows' ); ?></button>
	</div>
</section>
