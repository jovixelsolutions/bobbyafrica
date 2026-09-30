<?php
/**
 * Frequently Bought Together — Product edit screen tab + panel + save.
 *
 * Adds the "Frequently Bought Together" tab to the WooCommerce product edit screen,
 * renders the settings panel (Manual + Auto Suggest UI), and persists the FBT blob
 * via Cartflows_Fbt::update_settings() on save.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Fbt_Product_Meta.
 *
 * @since 3.1.4
 */
class Cartflows_Fbt_Product_Meta {

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * Initiator.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register the WC tab, panel, save hook and asset enqueue.
	 */
	public function __construct() {
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'render_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_wcf_fbt_get_product_meta', array( $this, 'ajax_get_product_meta' ) );
	}

	/**
	 * Adds the FBT tab to the WooCommerce product data metabox.
	 *
	 * @param array<string, array<string, mixed>> $tabs Existing WC product tabs.
	 * @return array<string, array<string, mixed>>
	 */
	public function add_tab( $tabs ) {

		// Tab visibility follows WC's own show_if_* helpers — simple + variable products only.
		$tabs['cartflows_fbt'] = array(
			'label'    => __( 'Frequently Bought Together', 'cartflows' ),
			'target'   => 'cartflows_fbt_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 80,
		);

		return $tabs;
	}

	/**
	 * Enqueues the FBT admin CSS + JS — only on the product edit screen.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets() {

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'product' !== $screen->id ) {
			return;
		}

		wp_enqueue_style(
			'wcf-fbt-admin',
			CARTFLOWS_FBT_URL . 'assets/css/fbt-admin.css',
			array(),
			CARTFLOWS_VER
		);

		wp_enqueue_script(
			'wcf-fbt-admin',
			CARTFLOWS_FBT_URL . 'assets/js/fbt-admin.js',
			array( 'jquery', 'jquery-ui-sortable', 'wc-enhanced-select', 'wp-api-fetch' ),
			CARTFLOWS_VER,
			true
		);

		global $post;
		$product_id = isset( $post->ID ) ? absint( $post->ID ) : 0;

		wp_localize_script(
			'wcf-fbt-admin',
			'wcf_fbt',
			array(
				'ajax_url'          => admin_url( 'admin-ajax.php' ),
				'product_id'        => $product_id,
				'max_products'      => Cartflows_Fbt::max_products( $product_id ),
				'placeholder_thumb' => (string) wc_placeholder_img_src( 'thumbnail' ),
				'auth_connected'    => Cartflows_Ai_Auth::get_instance()->get_auth_status(),
				'select_ids'        => array(
					'upsell'    => 'upsell_ids',
					'crosssell' => 'crosssell_ids',
				),
				'i18n'              => array(
					'remove'                 => __( 'Remove', 'cartflows' ),
					/* translators: 1: current count, 2: max count. */
					'counter'                => __( '%1$d of %2$d products selected.', 'cartflows' ),
					/* translators: %d: current selected count. */
					'products_label'         => __( 'Products (%d)', 'cartflows' ),
					'ai_hero_title'          => __( 'Auto Suggest Products', 'cartflows' ),
					'ai_hero_desc'           => __( 'Automatically suggest relevant products that customers are likely to buy with this product.', 'cartflows' ),
					'ai_generate'            => __( 'Generate suggestions', 'cartflows' ),
					'ai_loading'             => __( 'Analysing catalog… this can take up to 10 seconds.', 'cartflows' ),
					'ai_error'               => __( 'Could not fetch suggestions. Please try again.', 'cartflows' ),
					'ai_endpoint_missing'    => __( 'Auto Suggest is unavailable — the CartFlows Pro plugin needs to be active for suggestions to work.', 'cartflows' ),
					'ai_none'                => __( 'No suggestions available yet — add more products to your catalog to get suggestions.', 'cartflows' ),
					'ai_fallback_notice'     => __( 'Not enough purchase history for this product yet — showing similar and popular products instead.', 'cartflows' ),
					'badge_popular'          => __( 'Popular in your store', 'cartflows' ),
					'badge_new'              => __( 'New', 'cartflows' ),
					'ai_cached'              => __( 'Suggestions cached for up to 24 hours.', 'cartflows' ),
					'ai_regen_all'           => __( 'Regenerate all', 'cartflows' ),
					'ai_accept'              => __( 'Accept', 'cartflows' ),
					'ai_reject'              => __( 'Reject', 'cartflows' ),
					/* translators: %s: match percent number followed by percent sign. */
					'match_label'            => __( '%s match', 'cartflows' ),
					'drawer_title_upsell'    => __( 'Auto Suggest Upsells', 'cartflows' ),
					'drawer_title_crosssell' => __( 'Auto Suggest Cross-sells', 'cartflows' ),
					'drawer_close'           => __( 'Close', 'cartflows' ),
					'connect_title'          => __( 'Unlock Auto Suggest with CartFlows Pro', 'cartflows' ),
					'connect_desc'           => __( 'Automatically suggest relevant products that customers are likely to buy with this product.', 'cartflows' ),
					'connect_btn'            => __( 'Connect your account', 'cartflows' ),
				),
			)
		);

		// Extension point — Pro hooks here to enqueue its own admin CSS/JS on the product edit screen only.
		do_action( 'cartflows_fbt_admin_assets_enqueued', $product_id );
	}

	/**
	 * Renders the FBT panel HTML inside the WC product data metabox.
	 *
	 * @return void
	 */
	public function render_panel() {

		global $post;
		if ( ! isset( $post->ID ) ) {
			return;
		}

		$product_id = absint( $post->ID );
		$settings   = Cartflows_Fbt::get_settings( $product_id );
		$cards_html = $this->build_product_cards( $settings['product_ids'], $settings['product_qty'], 'yes' === $settings['custom_qty'] );

		include CARTFLOWS_FBT_DIR . 'templates/admin-product-panel.php';
	}

	/**
	 * Persists the FBT panel fields on product save.
	 *
	 * @param int $post_id Product post ID being saved.
	 * @return void
	 */
	public function save( $post_id ) {

		$post_id = absint( $post_id );
		if ( $post_id <= 0 ) {
			return;
		}

		// Verify the FBT-specific nonce so PHPCS is satisfied without leaning on WC's implicit nonce.
		$nonce = isset( $_POST['wcf_fbt_save_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wcf_fbt_save_nonce'] ) ) : '';
		if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wcf_fbt_save' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		// Enabled checkbox — HTML checkbox posts 'yes' when checked, nothing when unchecked.
		$enabled = isset( $_POST['_cartflows_fbt_enabled'] ) && 'yes' === $_POST['_cartflows_fbt_enabled'] ? 'yes' : 'no';

		// Source radio — helper's whitelist normalises unexpected values.
		$source = isset( $_POST['_cartflows_fbt_source'] ) ? sanitize_key( wp_unslash( $_POST['_cartflows_fbt_source'] ) ) : 'manual';

		// Product IDs — sanitise at extraction point so PHPCS sees an absint() applied immediately.
		$raw_ids     = isset( $_POST['_cartflows_fbt_product_ids'] ) && is_array( $_POST['_cartflows_fbt_product_ids'] ) ? array_map( 'absint', wp_unslash( $_POST['_cartflows_fbt_product_ids'] ) ) : array();
		$product_ids = array();
		foreach ( $raw_ids as $id ) {
			if ( $id > 0 && $id !== $post_id ) {
				$product_ids[] = $id;
			}
		}

		// New behaviour settings — helper's whitelists normalise everything.
		$add_separately = isset( $_POST['_cartflows_fbt_add_separately'] ) && 'yes' === $_POST['_cartflows_fbt_add_separately'] ? 'yes' : 'no';
		$selection      = isset( $_POST['_cartflows_fbt_selection'] ) ? sanitize_key( wp_unslash( $_POST['_cartflows_fbt_selection'] ) ) : 'multiple';
		$position       = isset( $_POST['_cartflows_fbt_position'] ) ? sanitize_key( wp_unslash( $_POST['_cartflows_fbt_position'] ) ) : 'after_atc_button';
		$custom_qty     = isset( $_POST['_cartflows_fbt_custom_qty'] ) && 'yes' === $_POST['_cartflows_fbt_custom_qty'] ? 'yes' : 'no';

		// Per-product qty map — absint keys + values.
		$product_qty = array();
		if ( isset( $_POST['_cartflows_fbt_product_qty'] ) && is_array( $_POST['_cartflows_fbt_product_qty'] ) ) {
			$raw_qty = array_map( 'absint', wp_unslash( $_POST['_cartflows_fbt_product_qty'] ) );
			foreach ( $raw_qty as $id => $qty ) {
				$id = absint( $id );
				if ( $id > 0 && $qty > 0 ) {
					$product_qty[ $id ] = $qty;
				}
			}
		}

		// AI provenance — intersecting with product_ids drops removed and zero IDs.
		$ai_product_ids = array();
		if ( isset( $_POST['_cartflows_fbt_ai_product_ids'] ) && is_array( $_POST['_cartflows_fbt_ai_product_ids'] ) ) {
			$ai_product_ids = array_values( array_intersect( array_map( 'absint', wp_unslash( $_POST['_cartflows_fbt_ai_product_ids'] ) ), $product_ids ) );
		}

		Cartflows_Fbt::update_settings(
			$post_id,
			array(
				'enabled'        => $enabled,
				'source'         => $source,
				'product_ids'    => $product_ids,
				'add_separately' => $add_separately,
				'selection'      => $selection,
				'position'       => $position,
				'custom_qty'     => $custom_qty,
				'product_qty'    => $product_qty,
				'ai_product_ids' => $ai_product_ids,
			)
		);

		// One-time milestone — days are frozen now so the event does not drift.
		if ( 'yes' === $enabled && ! empty( $product_ids ) ) {
			$install_raw  = Cartflows_Helper::get_analytics_flag( 'usage_installed_time', 0 );
			$install_time = is_numeric( $install_raw ) ? (int) $install_raw : 0;
			Cartflows_Helper::set_analytics_flag(
				'first_fbt_configured',
				array(
					'source'             => empty( $ai_product_ids ) ? 'manual' : 'ai',
					'days_since_install' => $install_time > 0 ? (int) floor( ( time() - $install_time ) / DAY_IN_SECONDS ) : 0,
				)
			);
		}
	}

	/**
	 * AJAX handler — returns price / stock / thumbnail for a single product ID.
	 *
	 * Called by the picker JS right after a product is chosen so the freshly-added
	 * row can populate its price and stock cells without a full page reload.
	 *
	 * @return void
	 */
	public function ajax_get_product_meta() {

		check_ajax_referer( 'wcf_fbt_ai', 'security' );
		if ( ! current_user_can( 'cartflows_manage_flows_steps' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cartflows' ) ), 403 );
		}

		$id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		if ( $id <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Missing product ID.', 'cartflows' ) ), 400 );
		}

		$product = wc_get_product( $id );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => __( 'Product not found.', 'cartflows' ) ), 404 );
		}

		$status   = $product->get_stock_status();
		$price    = (float) $product->get_price();
		$image_id = (int) $product->get_image_id();

		wp_send_json_success(
			array(
				// wc_price encodes the currency symbol as &#36;; decode after stripping tags for a clean single-line label.
				'price_html'  => $price > 0 ? html_entity_decode( wp_strip_all_tags( (string) wc_price( $price ) ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ) : '',
				'stock_label' => $this->stock_label( $status ),
				'stock_class' => $this->stock_badge_class( $status ),
				'thumb_url'   => $image_id > 0 ? (string) wp_get_attachment_image_url( $image_id, 'thumbnail' ) : (string) wc_placeholder_img_src( 'thumbnail' ),
			)
		);
	}

	/**
	 * Builds the product picker rows HTML for currently-saved FBT products.
	 *
	 * @param array<int, int> $product_ids Currently-selected product IDs.
	 * @param array<int, int> $product_qty Per-product qty map (product_id => qty).
	 * @param bool            $show_qty    When true, the qty column is visible.
	 * @return string
	 */
	private function build_product_cards( $product_ids, $product_qty = array(), $show_qty = false ) {

		if ( empty( $product_ids ) ) {
			return '';
		}

		$out = '';
		foreach ( $product_ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}

			$name         = wp_strip_all_tags( $product->get_formatted_name() );
			$price_html   = $product->get_price_html();
			$image_id     = (int) $product->get_image_id();
			$thumb_url    = $image_id > 0 ? (string) wp_get_attachment_image_url( $image_id, 'thumbnail' ) : (string) wc_placeholder_img_src( 'thumbnail' );
			$stock_status = $product->get_stock_status();
			$stock_label  = $this->stock_label( $stock_status );
			$stock_class  = $this->stock_badge_class( $stock_status );
			$qty_value    = isset( $product_qty[ $id ] ) ? max( 1, (int) $product_qty[ $id ] ) : 1;
			// Sold-individually products are locked to 1 — readonly still posts, disabled would not.
			$single_only = $product->is_sold_individually();
			$qty_value   = $single_only ? 1 : $qty_value;
			$qty_lock    = $single_only ? ' readonly title="' . esc_attr__( 'This product is limited to 1 per order.', 'cartflows' ) . '"' : '';

			$out .= '<div class="wcf-fbt-picker-row' . ( $show_qty ? ' has-qty' : '' ) . '" data-product-id="' . esc_attr( (string) $id ) . '">';
			$out .= '<span class="wcf-fbt-picker-drag" aria-hidden="true"><svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/><circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/><circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/></svg></span>';
			$out .= '<img class="wcf-fbt-picker-thumb" src="' . esc_url( $thumb_url ) . '" alt="" loading="lazy" />';
			$out .= '<div class="wcf-fbt-picker-info">';
			$out .= '<p class="wcf-fbt-picker-name">' . esc_html( $name ) . '</p>';
			$out .= '</div>';
			$out .= '<span class="wcf-fbt-picker-price">' . wp_kses_post( $price_html ) . '</span>';
			$out .= '<span class="wcf-fbt-picker-stock">';
			if ( '' !== $stock_label ) {
				$out .= '<span class="wcf-fbt-badge ' . esc_attr( $stock_class ) . '">' . esc_html( $stock_label ) . '</span>';
			}
			$out .= '</span>';
			$out .= '<span class="wcf-fbt-picker-qty">';
			$out .= '<input type="number" min="1" step="1" class="wcf-fbt-picker-qty-input" name="_cartflows_fbt_product_qty[' . esc_attr( (string) $id ) . ']" value="' . esc_attr( (string) $qty_value ) . '"' . $qty_lock . ' aria-label="' . esc_attr__( 'Quantity', 'cartflows' ) . '" />';
			$out .= '</span>';
			$out .= '<button type="button" class="wcf-fbt-picker-remove" aria-label="' . esc_attr__( 'Remove', 'cartflows' ) . '">';
			$out .= '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>';
			$out .= '</button>';
			$out .= '</div>';
		}

		return $out;
	}

	/**
	 * Maps a WC stock status to a translated label.
	 *
	 * @param string $status WC stock status string.
	 * @return string
	 */
	private function stock_label( $status ) {
		if ( 'instock' === $status ) {
			return __( 'In stock', 'cartflows' );
		}
		if ( 'outofstock' === $status ) {
			return __( 'Out of stock', 'cartflows' );
		}
		if ( 'onbackorder' === $status ) {
			return __( 'On backorder', 'cartflows' );
		}
		return '';
	}

	/**
	 * Maps a WC stock status to a badge CSS class.
	 *
	 * @param string $status WC stock status string.
	 * @return string
	 */
	private function stock_badge_class( $status ) {
		if ( 'outofstock' === $status ) {
			return 'wcf-fbt-badge-red';
		}
		if ( 'onbackorder' === $status ) {
			return 'wcf-fbt-badge-yellow';
		}
		return 'wcf-fbt-badge-green';
	}

}

Cartflows_Fbt_Product_Meta::get_instance();
