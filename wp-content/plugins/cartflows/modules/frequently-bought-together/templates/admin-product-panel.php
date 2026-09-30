<?php
/**
 * Frequently Bought Together — Admin product-editor panel template.
 *
 * Rendered by Cartflows_Fbt_Product_Meta::render_panel() on the WC product
 * data metabox. Every value is escaped at output.
 *
 * @package cartflows
 * @var int                                                                                                                                                                                    $product_id Current product ID.
 * @var array{enabled: string, source: string, product_ids: array<int, int>, add_separately: string, selection: string, position: string, custom_qty: string, product_qty: array<int, int>, ai_product_ids: array<int, int>} $settings   Sanitised FBT settings.
 * @var string                                                                                                                                                                                 $cards_html Pre-built product-row markup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$source = '' === $settings['source'] ? 'manual' : $settings['source'];
// The panel always opens on the Products tab — the stored source only feeds the hidden radios below.
$manual_tab      = ' is-active';
$ai_tab          = '';
$manual_pane     = ' is-active';
$ai_pane         = '';
$card_count      = count( $settings['product_ids'] );
$enabled_class   = 'yes' === $settings['enabled'] ? ' is-widget-enabled' : '';
$hide_when_empty = 0 === $card_count ? ' hidden' : '';
$show_when_empty = $card_count > 0 ? ' hidden' : '';
$exclude_ids     = array_values( array_map( 'absint', array_merge( array( $product_id ), $settings['product_ids'] ) ) );
$exclude_attr    = (string) wp_json_encode( $exclude_ids );

// Pro badge is a gate cue — only show it when Pro is missing or unlicensed.
$fbt_show_pro_badge = ! _is_cartflows_pro() || ! _is_cartflows_pro_license_activated();
$fbt_pro_badge      = $fbt_show_pro_badge ? ' <span class="wcf-fbt-badge wcf-fbt-badge-brand">' . esc_html__( 'Pro', 'cartflows' ) . '</span>' : '';
$default_ai_button  = '<button type="button" class="wcf-fbt-sub-tab' . esc_attr( $ai_tab ) . '" data-mode="ai" role="tab">' . esc_html__( 'Auto Suggest', 'cartflows' ) . $fbt_pro_badge . '</button>';

$fbt_cta = '<a href="https://cartflows.com/pricing/?utm_source=fbt&utm_medium=upgrade-wall" target="_blank" rel="noopener noreferrer" class="button button-primary wcf-fbt-upgrade-cta">' . esc_html__( 'Upgrade to CartFlows Pro', 'cartflows' ) . '</a>';

if ( ! _is_cartflows_pro() ) {
	if ( file_exists( WP_PLUGIN_DIR . '/cartflows-pro/cartflows-pro.php' ) ) {
		$fbt_cta = '<a href="' . esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=cartflows-pro/cartflows-pro.php' ), 'activate-plugin_cartflows-pro/cartflows-pro.php' ) ) . '" class="button button-primary wcf-fbt-upgrade-cta">' . esc_html__( 'Activate CartFlows Pro', 'cartflows' ) . '</a>';
	}
} elseif ( ! _is_cartflows_pro_license_activated() ) {
	$fbt_cta = '<a href="" class="button button-primary wcf-fbt-upgrade-cta">' . esc_html__( 'Activate License', 'cartflows' ) . '</a>';
} elseif ( ! Cartflows_Ai_Auth::get_instance()->get_auth_status() ) {
	$fbt_cta = '<a href="#" class="button button-primary wcf-fbt-upgrade-cta wcf-fbt-cta-connect">' . esc_html__( 'Connect your account', 'cartflows' ) . '</a>';
} else {
	$fbt_cta = '';
}

$default_ai_pane = '' === $fbt_cta ? '' : '<div class="wcf-fbt-ai-hero">'
	. '<div class="wcf-fbt-ai-hero-icon" aria-hidden="true">&#x2728;</div>'
	. '<h4>' . esc_html__( 'Unlock Auto Suggest with CartFlows Pro', 'cartflows' ) . '</h4>'
	. '<p>' . esc_html__( 'Automatically suggest relevant products that customers are likely to buy with this product.', 'cartflows' ) . '</p>'
	. $fbt_cta
	. '</div>';

/* translators: %d: current selected count. */
$products_label = sprintf( __( 'Products (%d)', 'cartflows' ), $card_count );

$position_labels = array(
	'after_atc_button' => __( 'After Add-to-Cart button', 'cartflows' ),
	'below_title'      => __( 'Below title', 'cartflows' ),
	'below_price'      => __( 'Below price', 'cartflows' ),
	'below_summary'    => __( 'Below summary', 'cartflows' ),
);
?>
<div id="cartflows_fbt_data" class="panel woocommerce_options_panel hidden wcf-fbt-panel<?php echo esc_attr( $enabled_class ); ?>">

	<input type="hidden" id="wcf_fbt_ai_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wcf_fbt_ai' ) ); ?>" />

	<div class="wcf-fbt-header">
		<div class="wcf-fbt-header-title">
			<h3><?php esc_html_e( 'Frequently Bought Together', 'cartflows' ); ?></h3>
		</div>
		<label class="wcf-fbt-toggle-label">
			<span><?php esc_html_e( 'Enable', 'cartflows' ); ?></span>
			<input type="checkbox" class="wcf-fbt-toggle-input" id="_cartflows_fbt_enabled" name="_cartflows_fbt_enabled" value="yes" <?php checked( $settings['enabled'], 'yes' ); ?> />
			<span class="button-primary wcf-fbt-toggle-switch" aria-hidden="true"></span>
		</label>
		<p class="wcf-fbt-header-desc"><?php esc_html_e( 'Show suggested companion products on the product page.', 'cartflows' ); ?></p>
	</div>

	<div class="wcf-fbt-body">

		<div class="wcf-fbt-sub-tabs" role="tablist">
			<button type="button" class="wcf-fbt-sub-tab<?php echo esc_attr( $manual_tab ); ?>" data-mode="manual" role="tab">
				<span class="wcf-fbt-sub-tab-label" data-role="products-label"><?php echo esc_html( $products_label ); ?></span>
			</button>
			<?php
			echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped in $default_ai_button; Pro's filter override is expected to return safe HTML.
				'cartflows_fbt_ai_tab_html',
				$default_ai_button,
				array(
					'product_id' => $product_id,
					'settings'   => $settings,
				)
			);
			?>
		</div>

		<input type="radio" class="wcf-fbt-source-input" name="_cartflows_fbt_source" value="manual" <?php checked( $source, 'manual' ); ?> aria-hidden="true" />
		<input type="radio" class="wcf-fbt-source-input" name="_cartflows_fbt_source" value="ai" <?php checked( $source, 'ai' ); ?> aria-hidden="true" />

		<div class="wcf-fbt-pane wcf-fbt-pane-manual<?php echo esc_attr( $manual_pane ); ?>" data-pane="manual">
			<div class="wcf-fbt-picker-shell<?php echo 'yes' === $settings['custom_qty'] ? ' has-qty' : ''; ?>">

				<div class="wcf-fbt-picker-topbar">
					<div class="wcf-fbt-picker-search">
						<select id="_cartflows_fbt_product_search" class="wc-product-search wcf-fbt-picker-chooser" multiple="multiple" style="width:100%;" data-placeholder="<?php esc_attr_e( 'Search for a product…', 'cartflows' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude="<?php echo esc_attr( $exclude_attr ); ?>"></select>
					</div>
					<span class="wcf-fbt-picker-count"><strong data-role="picker-count"><?php echo esc_html( (string) $card_count ); ?></strong> <?php esc_html_e( 'selected', 'cartflows' ); ?></span>
				</div>

				<div class="wcf-fbt-picker-values" hidden>
					<?php foreach ( $settings['product_ids'] as $saved_id ) : ?>
						<input type="hidden" name="_cartflows_fbt_product_ids[]" value="<?php echo esc_attr( (string) absint( $saved_id ) ); ?>" />
					<?php endforeach; ?>
				</div>

				<div class="wcf-fbt-picker-ai-values" hidden>
					<?php foreach ( $settings['ai_product_ids'] as $ai_saved_id ) : ?>
						<input type="hidden" name="_cartflows_fbt_ai_product_ids[]" value="<?php echo esc_attr( (string) absint( $ai_saved_id ) ); ?>" />
					<?php endforeach; ?>
				</div>

				<div class="wcf-fbt-picker-header-row<?php echo esc_attr( $hide_when_empty ); ?>">
					<span></span>
					<span><?php esc_html_e( 'Product', 'cartflows' ); ?></span><span></span>
					<span class="h-price"><?php esc_html_e( 'Price', 'cartflows' ); ?></span>
					<span class="h-stock"><?php esc_html_e( 'Stock', 'cartflows' ); ?></span>
					<span class="h-qty"><?php esc_html_e( 'Qty', 'cartflows' ); ?></span>
					<span class="h-actions"></span>
				</div>

				<div class="wcf-fbt-picker-rows<?php echo esc_attr( $hide_when_empty ); ?>">
					<?php echo $cards_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped inside build_product_cards(). ?>
				</div>

				<div class="wcf-fbt-picker-empty<?php echo esc_attr( $show_when_empty ); ?>">
					<div class="icon" aria-hidden="true">
						<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
					</div>
					<?php
					echo wp_kses(
						/* translators: %s: bold "Auto Suggest" label. */
						sprintf( __( 'No products selected yet. Search above, or switch to %s.', 'cartflows' ), '<strong>' . esc_html__( 'Auto Suggest', 'cartflows' ) . '</strong>' ),
						array( 'strong' => array() )
					);
					?>
				</div>

				<div class="wcf-fbt-picker-footer">
					<span><?php esc_html_e( 'Drag the handle to reorder — first item shows first in the frontend widget.', 'cartflows' ); ?></span>
				</div>

			</div>

			<div class="wcf-fbt-settings options_group">
				<p class="form-field _cartflows_fbt_add_separately_field">
					<label for="_cartflows_fbt_add_separately"><?php esc_html_e( 'Add products separately', 'cartflows' ); ?></label>
					<input type="checkbox" class="checkbox" id="_cartflows_fbt_add_separately" name="_cartflows_fbt_add_separately" value="yes" <?php checked( $settings['add_separately'], 'yes' ); ?> />
					<?php echo wp_kses_post( wc_help_tip( __( 'Keep each product as an independent cart item. When off, companion products are removed along with the main product.', 'cartflows' ) ) ); ?>
				</p>

				<fieldset class="form-field _cartflows_fbt_selection_field">
					<legend><?php esc_html_e( 'Product selecting method', 'cartflows' ); ?></legend>
					<ul class="wc-radios wcf-fbt-radios-inline">
						<li>
							<label>
								<input type="radio" name="_cartflows_fbt_selection" value="multiple" <?php checked( $settings['selection'], 'multiple' ); ?> />
								<?php esc_html_e( 'Multiple', 'cartflows' ); ?>
							</label>
						</li>
						<li>
							<label>
								<input type="radio" name="_cartflows_fbt_selection" value="single" <?php checked( $settings['selection'], 'single' ); ?> />
								<?php esc_html_e( 'Single', 'cartflows' ); ?>
							</label>
						</li>
					</ul>
				</fieldset>

				<p class="form-field _cartflows_fbt_custom_qty_field">
					<label for="_cartflows_fbt_custom_qty"><?php esc_html_e( 'Custom quantity', 'cartflows' ); ?></label>
					<input type="checkbox" class="checkbox" id="_cartflows_fbt_custom_qty" name="_cartflows_fbt_custom_qty" value="yes" <?php checked( $settings['custom_qty'], 'yes' ); ?> />
					<?php echo wp_kses_post( wc_help_tip( __( 'Show a quantity box for each product.', 'cartflows' ) ) ); ?>
				</p>

				<p class="form-field _cartflows_fbt_position_field">
					<label for="_cartflows_fbt_position"><?php esc_html_e( 'Widget placement', 'cartflows' ); ?></label>
					<select id="_cartflows_fbt_position" name="_cartflows_fbt_position" class="select short">
						<?php foreach ( $position_labels as $position_value => $position_label ) : ?>
							<option value="<?php echo esc_attr( $position_value ); ?>" <?php selected( $settings['position'], $position_value ); ?>><?php echo esc_html( $position_label ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
			</div>
		</div>

		<div class="wcf-fbt-pane wcf-fbt-pane-ai<?php echo esc_attr( $ai_pane ); ?>" data-pane="ai">
			<?php
			echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Pre-escaped in $default_ai_pane; Pro's filter override is expected to return safe HTML.
				'cartflows_fbt_ai_pane_html',
				$default_ai_pane,
				array(
					'product_id' => $product_id,
					'settings'   => $settings,
				)
			);
			?>
		</div>

	</div>

	<?php wp_nonce_field( 'wcf_fbt_save', 'wcf_fbt_save_nonce' ); ?>
</div>
