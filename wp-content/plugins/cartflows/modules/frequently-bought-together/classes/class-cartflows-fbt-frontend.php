<?php
/**
 * Frequently Bought Together — Frontend widget renderer.
 *
 * Hooks the FBT widget onto the single product page via
 * woocommerce_after_add_to_cart_button, enqueues the frontend CSS + JS only when
 * the widget will actually render, and handles the per-item add-to-cart AJAX
 * (wcf_fbt_add_to_cart).
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Fbt_Frontend.
 *
 * @since 3.1.4
 */
class Cartflows_Fbt_Frontend {

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
	 * Position slug => [ WC hook, priority ]. Matches Cartflows_Fbt::POSITION_VALUES.
	 *
	 * Theme compat classes remap the below_* positions via the
	 * `cartflows_fbt_position_hooks` filter when the theme owns the summary
	 * layout (e.g. Astra renders the whole summary in one priority-10 callback).
	 *
	 * @return array<string, array{string, int}>
	 */
	private static function position_hooks() {
		return (array) apply_filters(
			'cartflows_fbt_position_hooks',
			array(
				'after_atc_button' => array( 'woocommerce_after_add_to_cart_button', 10 ),
				'below_title'      => array( 'woocommerce_single_product_summary', 6 ),
				'below_price'      => array( 'woocommerce_single_product_summary', 11 ),
				'below_summary'    => array( 'woocommerce_after_single_product_summary', 5 ),
			)
		);
	}

	/**
	 * Register render, enqueue and AJAX hooks.
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'register_position_hooks' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'wp_ajax_wcf_fbt_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_wcf_fbt_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_filter( 'woocommerce_get_item_data', array( $this, 'filter_cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_bundle_companions' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_attribution' ), 10, 4 );
	}

	/**
	 * Register the position render hooks. Deferred to wp_loaded so theme
	 * compat classes have already attached their cartflows_fbt_position_hooks
	 * filter overrides by the time we resolve the final hook map.
	 *
	 * @return void
	 */
	public function register_position_hooks() {
		foreach ( self::position_hooks() as $position => $hook_data ) {
			list( $hook, $priority ) = $hook_data;
			add_action(
				$hook,
				function () use ( $position ) {
					$this->maybe_render_at( $position );
				},
				$priority
			);
		}
	}

	/**
	 * Fires from every position hook; renders only when the current product's setting matches.
	 *
	 * @param string $position The position slug this callback was registered for.
	 * @return void
	 */
	public function maybe_render_at( $position ) {
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		// Only render as part of the main content loop. Sticky add-to-cart bars
		// (e.g. Astra's, built in wp_footer) re-run the add-to-cart template and
		// refire these hooks outside the loop — those renders must not inject us.
		// Block themes fire after_single_product_summary outside any loop (WC compat layer) — exempt them.
		if ( ! wp_doing_ajax() && ! in_the_loop() && ! wp_is_block_theme() ) {
			return;
		}
		// Skip quick-view AJAX contexts — the modal is a preview surface and
		// the widget markup collides with its tight width.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only routing check on the request action, not a data mutation.
		$ajax_action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : '';
		if ( wp_doing_ajax() && false !== strpos( $ajax_action, 'quick_view' ) ) {
			return;
		}
		$product_id = (int) get_the_ID();
		if ( $product_id <= 0 ) {
			return;
		}
		$settings = Cartflows_Fbt::get_settings( $product_id );
		if ( $settings['position'] !== $position ) {
			return;
		}
		$this->render_widget();
	}

	/**
	 * Enqueues the frontend CSS + JS only when the widget will render.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {

		if ( ! is_singular( 'product' ) ) {
			return;
		}
		$product_id = get_the_ID();
		if ( ! $product_id || ! Cartflows_Fbt::is_enabled( (int) $product_id ) ) {
			return;
		}
		$ids = Cartflows_Fbt::get_product_ids( (int) $product_id );
		if ( empty( $ids ) ) {
			return;
		}

		wp_enqueue_style(
			'wcf-fbt-frontend',
			CARTFLOWS_FBT_URL . 'assets/css/fbt-frontend.css',
			array(),
			CARTFLOWS_VER
		);

		$script_deps = (array) apply_filters( 'cartflows_fbt_frontend_script_deps', array( 'jquery' ) );

		wp_enqueue_script(
			'wcf-fbt-frontend',
			CARTFLOWS_FBT_URL . 'assets/js/fbt-frontend.js',
			$script_deps,
			CARTFLOWS_VER,
			true
		);

		wp_localize_script(
			'wcf-fbt-frontend',
			'wcf_fbt_frontend',
			array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'wcf_fbt_frontend' ),
				'currency_symbol' => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ),
				'decimal_sep'     => wc_get_price_decimal_separator(),
				'thousand_sep'    => wc_get_price_thousand_separator(),
				'i18n'            => array(
					'added'          => __( 'Added to cart', 'cartflows' ),
					'error'          => __( 'Could not add to cart. Please try again.', 'cartflows' ),
					'adding'         => __( 'Adding…', 'cartflows' ),
					/* translators: %d: item count. */
					'label_single'   => __( 'Total for %d item', 'cartflows' ),
					/* translators: %d: item count. */
					'label_plural'   => __( 'Total for %d items', 'cartflows' ),
					'button_single'  => __( 'Add to cart', 'cartflows' ),
					/* translators: %d: total items in the bundle. */
					'button_some'    => __( 'Add %d items to cart', 'cartflows' ),
					/* translators: %d: total items in the bundle. */
					'button_all'     => __( 'Add all %d items to cart', 'cartflows' ),
					'select_options' => __( 'Select options to continue', 'cartflows' ),
					'show_fewer'     => __( 'Show fewer', 'cartflows' ),
					/* translators: %d: number of additional companion products. */
					'show_more'      => __( 'Show %d more', 'cartflows' ),
				),
			)
		);

		// Extension point — Pro hooks here to enqueue its own frontend CSS/JS on the single product page only.
		do_action( 'cartflows_fbt_frontend_assets_enqueued', (int) $product_id );
	}

	/**
	 * Renders the FBT widget block on the single product page.
	 *
	 * @return void
	 */
	public function render_widget() {

		if ( ! is_singular( 'product' ) ) {
			return;
		}
		$product_id = (int) get_the_ID();
		if ( $product_id <= 0 || ! Cartflows_Fbt::is_enabled( $product_id ) ) {
			return;
		}

		$fbt_settings = Cartflows_Fbt::get_settings( $product_id );
		$product_ids  = $fbt_settings['product_ids'];
		if ( empty( $product_ids ) ) {
			return;
		}

		$products = $this->load_products( $product_ids );
		if ( empty( $products ) ) {
			return;
		}

		$default_template = CARTFLOWS_FBT_DIR . 'templates/frontend-widget.php';
		$template         = (string) apply_filters( 'cartflows_fbt_frontend_template', $default_template, $products, $product_id );
		if ( ! file_exists( $template ) ) {
			return;
		}

		include $template;
	}

	/**
	 * Builds the display data one widget row needs; null when the product can't be offered.
	 *
	 * @param WC_Product              $product The product to describe.
	 * @param array<string, bool|int> $args    Row context: is_main, show_qty, qty.
	 * @return array{id: int, name: string, image: string, available: bool, is_main: bool, is_extra: bool, show_qty: bool, qty: int, price: float, price_html: string, from_label: string, requires_options: bool, permalink: string, variations: array<int, array{id: int, price: float, label: string}>}|null
	 */
	public function get_row_data( $product, $args = array() ) {

		// The main product is always shown as-is; variation choice for it is out of scope here.
		$variations = empty( $args['is_main'] ) ? $this->get_variation_choices( $product ) : array();
		if ( $product->is_type( 'variable' ) && empty( $args['is_main'] ) && empty( $variations ) ) {
			return null;
		}

		$price = empty( $variations )
			? (float) wc_get_price_to_display( $product )
			: (float) min( array_column( $variations, 'price' ) );

		$image = (string) wp_get_attachment_image_url( (int) $product->get_image_id(), 'woocommerce_thumbnail' );
		if ( '' === $image ) {
			$image = (string) wc_placeholder_img_src( 'woocommerce_thumbnail' );
		}

		$requires_options = $this->requires_own_page( $product, ! empty( $args['is_main'] ), $variations );

		// Products priced from their own form (gift cards, name-your-price) store a placeholder, so show what they advertise.
		$price_html = ! $product->is_type( 'variable' ) && ! $product->supports( 'ajax_add_to_cart' )
			? (string) $product->get_price_html()
			: (string) wc_price( $price );
		if ( '' === $price_html ) {
			$price_html = (string) wc_price( $price );
		}

		return array(
			'id'               => (int) $product->get_id(),
			'name'             => (string) $product->get_name(),
			'image'            => $image,
			'available'        => 'outofstock' !== $product->get_stock_status(),
			'is_main'          => ! empty( $args['is_main'] ),
			'is_extra'         => false,
			// A sold-individually product can only ever be qty 1 — WC_Cart clamps it, so never offer a qty box.
			'show_qty'         => ! empty( $args['show_qty'] ) && ! $product->is_sold_individually(),
			'qty'              => isset( $args['qty'] ) ? max( 1, (int) $args['qty'] ) : 1,
			'price'            => $price,
			'price_html'       => $price_html,
			/* translators: %s: minimum variation price. */
			'from_label'       => empty( $variations ) ? '' : sprintf( __( 'From %s', 'cartflows' ), $this->plain_price( $price ) ),
			'requires_options' => $requires_options,
			'permalink'        => $requires_options ? (string) $product->get_permalink() : '',
			'variations'       => $variations,
		);
	}

	/**
	 * Whether a companion can only be added from its own product page.
	 * Variable rows resolve in-widget via the flat select, so they are never affected.
	 *
	 * @param WC_Product                                              $product    Candidate product.
	 * @param bool                                                    $is_main    Whether this is the "this item" row.
	 * @param array<int, array{id: int, price: float, label: string}> $variations Resolvable variation choices.
	 * @return bool
	 */
	private function requires_own_page( $product, $is_main, $variations ) {

		// The main row's own fields are already on the page, so it always stays addable.
		if ( $is_main || ! empty( $variations ) ) {
			return false;
		}

		// Products opting out of ajax_add_to_cart declare they need their page — gift cards, grouped, external.
		return ! $product->supports( 'ajax_add_to_cart' );
	}

	/**
	 * Lists the variations a shopper can pick directly from the widget's flat select.
	 *
	 * @param WC_Product $product Candidate product.
	 * @return array<int, array{id: int, price: float, label: string}>
	 */
	private function get_variation_choices( $product ) {

		if ( ! $product instanceof WC_Product_Variable ) {
			return array();
		}

		$choices = array();
		foreach ( $product->get_available_variations( 'objects' ) as $variation ) {
			if ( ! $variation instanceof WC_Product_Variation ) {
				continue;
			}
			// A variation with an "Any …" attribute needs input a flat select can't collect — skip it.
			if ( ! $variation->is_purchasable() || ! $variation->is_in_stock() || in_array( '', $variation->get_variation_attributes(), true ) ) {
				continue;
			}
			$price     = (float) wc_get_price_to_display( $variation );
			$choices[] = array(
				'id'    => (int) $variation->get_id(),
				'price' => $price,
				'label' => wc_get_formatted_variation( $variation, true, false, false ) . ' — ' . $this->plain_price( $price ),
			);
		}

		return $choices;
	}

	/**
	 * Drains the queued WooCommerce notices as HTML for the client to inject.
	 *
	 * @return string
	 */
	private function collect_notices() {
		return function_exists( 'wc_print_notices' ) ? (string) wc_print_notices( true ) : '';
	}

	/**
	 * Formats a price as plain text for option labels and data attributes.
	 *
	 * @param float $price Raw price.
	 * @return string
	 */
	private function plain_price( $price ) {
		return html_entity_decode( wp_strip_all_tags( (string) wc_price( $price ) ), ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) );
	}

	/**
	 * Handles the per-item add-to-cart AJAX call.
	 *
	 * @return void
	 */
	public function ajax_add_to_cart() {

		check_ajax_referer( 'wcf_fbt_frontend', 'security' );

		if ( ! function_exists( 'WC' ) || null === WC()->cart ) {
			wp_send_json_error( array( 'message' => __( 'Cart unavailable.', 'cartflows' ) ), 500 );
		}

		$source_id           = isset( $_POST['source_id'] ) ? absint( wp_unslash( $_POST['source_id'] ) ) : 0;
		$source_variation_id = isset( $_POST['source_variation_id'] ) ? absint( wp_unslash( $_POST['source_variation_id'] ) ) : 0;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every key/value pair is sanitized inside the loop below.
		$raw_attributes    = isset( $_POST['source_attributes'] ) && is_array( $_POST['source_attributes'] ) ? wp_unslash( $_POST['source_attributes'] ) : array();
		$source_attributes = array();
		foreach ( $raw_attributes as $attr_key => $attr_value ) {
			$attr_key = sanitize_text_field( (string) $attr_key );
			if ( 0 === strpos( $attr_key, 'attribute_' ) ) {
				$source_attributes[ $attr_key ] = sanitize_text_field( (string) $attr_value );
			}
		}
		$settings = $source_id > 0 ? Cartflows_Fbt::get_settings( $source_id ) : Cartflows_Fbt::get_defaults();
		$separate = 'yes' === $settings['add_separately'];

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Every inner field is absint()'d inside the loop below.
		$raw_items = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : array();
		if ( empty( $raw_items ) ) {
			wp_send_json_error( array( 'message' => __( 'No items selected.', 'cartflows' ) ), 400 );
		}

		// Bundle key groups the parent + companions in the cart when add_separately = 'no'.
		// Deterministic per source so re-adding the same bundle merges lines instead of duplicating them.
		$bundle_key = $separate ? '' : 'wcf_fbt_' . $source_id;
		$added_any  = false;

		// Variable source products add as the variation chosen in WC's own variations form.
		$main_id = $source_id;
		if ( $source_variation_id > 0 && wp_get_post_parent_id( $source_variation_id ) === $source_id ) {
			$main_id = $source_variation_id;
		}

		$source_qty = isset( $_POST['source_qty'] ) ? max( 1, absint( wp_unslash( $_POST['source_qty'] ) ) ) : 1;

		// Captured before the main product is prepended so the milestone always means companions.
		$companion_count = count( $raw_items );

		// Prepend the main product ("this item" row in the widget) so the cart matches the shown total.
		if ( $main_id > 0 ) {
			array_unshift(
				$raw_items,
				array(
					'id'  => $main_id,
					'qty' => $source_qty,
				)
			);
		}

		// The product form posts its own fields with this request, so add-on hooks read a genuine $_POST.
		$queue   = array();
		$blocked = false;

		foreach ( $raw_items as $item ) {
			$id  = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$qty = isset( $item['qty'] ) ? max( 1, absint( $item['qty'] ) ) : 1;
			if ( $id <= 0 ) {
				continue;
			}
			$product = wc_get_product( $id );
			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			// Backstop for crafted requests — the widget renders these rows as a link, not a checkbox.
			if ( $id !== $main_id && $this->requires_own_page( $product, false, $this->get_variation_choices( $product ) ) ) {
				/* translators: %s: product name. */
				wc_add_notice( sprintf( __( '“%s” has options to choose. Please add it from its product page.', 'cartflows' ), $product->get_name() ), 'error' );
				$blocked = true;
				break;
			}

			$cart_item_data = $this->build_cart_item_data( $id, $main_id, $source_id, $bundle_key );

			$entry = array(
				'product_id'     => $id,
				'quantity'       => $qty,
				'variation_id'   => 0,
				'attributes'     => array(),
				'cart_item_data' => $cart_item_data,
			);

			if ( $product instanceof WC_Product_Variation ) {
				// WC's contract: this argument is the shopper's CHOSEN attributes, not the variation's stored set.
				$entry['attributes']   = $id === $main_id && ! empty( $source_attributes )
					? $source_attributes
					: $product->get_variation_attributes();
				$entry['variation_id'] = $id;
				$entry['product_id']   = (int) $product->get_parent_id();
			}

			$queue[] = $entry;
		}

		// WC_Cart::add_to_cart() does not run this filter — its callers do, so FBT must too.
		if ( ! $blocked ) {
			foreach ( $queue as $entry ) {
				$passed = apply_filters(
					'woocommerce_add_to_cart_validation',
					true,
					$entry['product_id'],
					$entry['quantity'],
					$entry['variation_id'],
					$entry['attributes'],
					$entry['cart_item_data']
				);
				if ( ! $passed ) {
					$blocked = true;
					break;
				}
			}
		}

		// All-or-nothing: a rejected companion must never leave a half-built bundle behind.
		if ( $blocked ) {
			wp_send_json_error(
				array(
					'message'      => __( 'Could not add product.', 'cartflows' ),
					'notices_html' => $this->collect_notices(),
				),
				400
			);
		}

		foreach ( $queue as $entry ) {
			$added = WC()->cart->add_to_cart(
				$entry['product_id'],
				$entry['quantity'],
				$entry['variation_id'],
				$entry['attributes'],
				$entry['cart_item_data']
			);
			if ( $added ) {
				$added_any = true;
			}
		}

		if ( ! $added_any ) {
			wp_send_json_error(
				array(
					'message'      => __( 'Could not add product.', 'cartflows' ),
					'notices_html' => $this->collect_notices(),
				),
				500
			);
		}

		Cartflows_Helper::set_analytics_flag(
			'first_fbt_widget_added_to_cart',
			array( 'item_count' => $companion_count )
		);

		wc_add_notice(
			sprintf(
				'%s <a href="%s" class="button wc-forward">%s</a>',
				esc_html__( 'Selected products have been added to your cart.', 'cartflows' ),
				esc_url( wc_get_cart_url() ),
				esc_html__( 'View cart', 'cartflows' )
			),
			'success'
		);

		// Capture the notices HTML so the client can inject it above the widget.
		$notices_html = $this->collect_notices();

		// Build the same fragments payload WC's own AJAX add-to-cart returns.
		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		$fragments = (array) apply_filters(
			'woocommerce_add_to_cart_fragments',
			array( 'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>' )
		);

		wp_send_json(
			array(
				'fragments'    => $fragments,
				'cart_hash'    => WC()->cart->get_cart_hash(),
				'notices_html' => $notices_html,
			)
		);
	}

	/**
	 * Builds the cart item data for one widget line.
	 *
	 * The parent link is deliberately independent of the bundle key: revenue
	 * attribution needs it in separate mode too, which is the default.
	 *
	 * @param int    $id         Product or variation ID being added.
	 * @param int    $main_id    The source product's own line ID.
	 * @param int    $source_id  Source product ID.
	 * @param string $bundle_key Bundle key; empty when items are added separately.
	 * @return array<string, mixed>
	 */
	public function build_cart_item_data( $id, $main_id, $source_id, $bundle_key ) {

		$data = '' === $bundle_key ? array() : array( '_wcf_fbt_bundle_key' => $bundle_key );

		if ( $id !== $main_id && $source_id > 0 ) {
			$data['_wcf_fbt_parent_id'] = $source_id;
		}

		return $data;
	}

	/**
	 * Loads all FBT product objects in a single query for the frontend template.
	 *
	 * @param array<int, int> $ids Product IDs to load.
	 * @return array<int, WC_Product>
	 */
	private function load_products( $ids ) {

		$out = array();
		foreach ( $ids as $id ) {
			$id = absint( $id );
			if ( $id <= 0 ) {
				continue;
			}
			$product = wc_get_product( $id );
			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			$parent = $product->is_type( 'variation' ) ? wc_get_product( $product->get_parent_id() ) : null;
			$status = ( $parent instanceof WC_Product ) ? $parent->get_status() : $product->get_status();
			if ( 'publish' !== $status ) {
				continue;
			}
			$out[] = $product;
		}
		return $out;
	}

	/**
	 * Adds a "Bought together: <parent>" meta row on companion cart lines.
	 * Disabled by default; enable via the cartflows_fbt_show_bought_together_meta filter.
	 *
	 * @param array<int,array{name:string,value:string,display?:string}> $item_data Current item meta rows.
	 * @param array<string,mixed>                                        $cart_item WC cart item.
	 * @return array<int,array{name:string,value:string,display?:string}>
	 */
	public function filter_cart_item_data( $item_data, $cart_item ) {

		// Off by default — themes/devs opt in via the filter.
		if ( ! apply_filters( 'cartflows_fbt_show_bought_together_meta', false, $cart_item ) || empty( $cart_item['_wcf_fbt_parent_id'] ) ) {
			return $item_data;
		}
		$parent = wc_get_product( is_numeric( $cart_item['_wcf_fbt_parent_id'] ) ? (int) $cart_item['_wcf_fbt_parent_id'] : 0 );
		if ( ! $parent instanceof WC_Product ) {
			return $item_data;
		}
		// Full name always travels in value (emails, plain-text); the checkout table gets the ellipsizing span.
		$parent_name = $parent->get_name();
		$item_data[] = array(
			'name'    => __( 'Bought together', 'cartflows' ),
			'value'   => $parent_name,
			'display' => '<span class="wcf-fbt-parent-name" title="' . esc_attr( wp_strip_all_tags( $parent_name ) ) . '">' . esc_html( $parent_name ) . '</span>',
		);
		return $item_data;
	}

	/**
	 * Cascades bundle-parent removal to companion lines sharing the same bundle key.
	 *
	 * @param string  $removed_key Cart item key that was just removed.
	 * @param WC_Cart $cart        The cart instance.
	 * @return void
	 */
	public function remove_bundle_companions( $removed_key, $cart ) {

		$removed = isset( $cart->removed_cart_contents[ $removed_key ] ) ? $cart->removed_cart_contents[ $removed_key ] : null;
		if ( empty( $removed['_wcf_fbt_bundle_key'] ) || ! empty( $removed['_wcf_fbt_parent_id'] ) ) {
			// Only cascade when the removed line is the bundle parent (has bundle_key, no parent_id).
			return;
		}
		$bundle_key = $removed['_wcf_fbt_bundle_key'];
		foreach ( $cart->get_cart() as $key => $item ) {
			if ( isset( $item['_wcf_fbt_bundle_key'] ) && $item['_wcf_fbt_bundle_key'] === $bundle_key ) {
				$cart->remove_cart_item( $key );
			}
		}
	}

	/**
	 * Carries the widget's parent-product link from the cart line onto the order item.
	 * Store API checkout routes through the same WC_Checkout method, so blocks are covered too.
	 *
	 * @param WC_Order_Item_Product $item          Order line item being built.
	 * @param string                $cart_item_key Cart item key.
	 * @param array<string, mixed>  $values        Cart item data for this line.
	 * @param WC_Order              $order         Order being created.
	 * @return void
	 */
	public function add_order_item_attribution( $item, $cart_item_key, $values, $order ) {

		if ( empty( $values['_wcf_fbt_parent_id'] ) || ! is_numeric( $values['_wcf_fbt_parent_id'] ) ) {
			return;
		}

		// WC_Data::add_meta_data() types $value as array|string; meta is stored as a string regardless.
		$item->add_meta_data( '_wcf_fbt_parent_id', (string) absint( $values['_wcf_fbt_parent_id'] ), true );
	}
}

Cartflows_Fbt_Frontend::get_instance();
