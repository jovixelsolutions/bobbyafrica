<?php
/**
 * Cart.
 *
 * @package modern-cart
 * @since 0.0.1
 */

namespace ModernCart\Inc;

use ModernCart\Inc\Traits\Get_Instance;
use WC_Coupon;
use WC_Product;

/**
 * Admin menu
 *
 * @since 0.0.1
 */
class Slide_Out extends Cart {
	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 0.0.1
	 */
	public function __construct() {
		// Only register wp_footer hook on frontend pages — not in admin/AJAX context.
		// Other hooks must remain registered so AJAX refresh handlers can render cart content.
		if ( ! is_admin() ) {
			add_action( 'wp_footer', [ $this, 'slide_out' ] );
		}
		add_action( 'moderncart_slide_out_content', [ $this, 'render_header' ] );
		add_action( 'moderncart_slide_out_content', [ $this, 'render_contents' ] );
		add_action( 'moderncart_slide_out_header_before', [ $this, 'render_free_shipping_bar' ] );
		add_action( 'moderncart_slide_out_content', [ $this, 'render_footer' ], 15 );
		add_action( 'moderncart_slide_out_footer_content', [ $this, 'render_coupon_form' ], 25 );
		add_action( 'moderncart_slide_out_footer_content', [ $this, 'render_totals' ], 35 );
		add_action( 'moderncart_slide_out_cart_after', [ $this, 'render_empty_cart_recommendations' ] );
		add_action( 'moderncart_slide_out_coupon_form_after', [ $this, 'render_coupon_removal' ] );
		add_filter( 'cpsw_express_checkout_selected_location_status', [ $this, 'express_checkout_location_status' ] );
		add_filter( 'cpsw_express_checkout_allow_custom_pages', [ $this, 'express_checkout_show_all_pages' ] );
		add_action( 'cpsw_payment_request_button_before', [ $this, 'action_request_button_before' ] );
		add_filter( 'woocommerce_before_calculate_totals', [ $this, 'set_custom_prices' ], 10 );
	}

	/**
	 * Set custom prices before cart calculations
	 *
	 * @param \WC_Cart $cart Cart object.
	 * @return \WC_Cart $cart
	 */
	public function set_custom_prices( $cart ) {
		if ( Helper::is_cart_empty() ) {
			return $cart;
		}

		foreach ( $cart->get_cart() as $cart_item ) {
			if ( isset( $cart_item['custom_price'] ) ) {
				$cart_item['data']->set_price( $cart_item['custom_price'] );
			}
		}
		return $cart;
	}

	/**
	 * Show Express checkout location OR text
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function action_request_button_before(): void {
		?>
		<div class="moderncart-payment-request-separator">
			<?php esc_html_e( 'OR', 'modern-cart' ); ?>
		</div>
		<?php
	}

	/**
	 * Express checkout location status
	 *
	 * @since 0.0.1
	 *
	 * @return bool
	 */
	public function express_checkout_location_status() {
		return true;
	}

	/**
	 * Express checkout show all pages
	 *
	 * @since 0.0.1
	 *
	 * @param bool $status Current status of supported page.
	 *
	 * @return bool
	 */
	public function express_checkout_show_all_pages( $status ) {
		if ( ! $this->is_global_enabled() ) {
			return $status;
		}

		return true;
	}

	/**
	 * Renders the empty cart recommendations partial.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_empty_cart_recommendations(): void {
		if ( 'disabled' === $this->get_option( 'empty_cart_recommendation', MODERNCART_SETTINGS, 'disabled' ) ) {
			return;
		}

		if ( ! Helper::is_cart_empty() ) {
			return;
		}

		$recommended_products = $this->get_empty_cart_recommendations();

		if ( empty( $recommended_products ) ) {
			return;
		}

		$data = [
			'classes'              => apply_filters( 'moderncart_slide_out_empty_cart_recommendations_classes', [ 'moderncart-slide-out-empty-cart-recommendations' ] ),
			'title'                => $this->get_option( 'empty_cart_recommendation_title', MODERNCART_SETTINGS, __( 'Your Cart Is Empty, Let\'s Fix That!', 'modern-cart' ) ),
			'recommended_products' => $recommended_products,
			'button_text'          => apply_filters( 'moderncart_slide_out_empty_cart_recommendations_button_text', __( 'Add', 'modern-cart' ) ),
			'button_type'          => 'icon',
		];

		moderncart_get_template_part( 'cart/recommendation-empty', '', $data );
	}

	/**
	 * Get an object of products to recommend based on plugin settings.
	 *
	 * @since 0.0.1
	 *
	 * @return array<object> $products An object of products.
	 */
	public function get_recommendations() {
		$recommendation_type     = $this->get_option( 'recommendation_types', MODERNCART_SETTINGS, true );
		$recommendation_fallback = 'random_products';
		$cart                    = WC()->cart->get_cart();
		$products                = [];
		$current_cart_item_ids   = [];
		$product_ids             = [];
		$product_cart_ids        = [];

		if ( 'upsells' === $recommendation_type ) {
			foreach ( $cart as $item ) {
				$_product    = new WC_Product( $item['product_id'] );
				$product_ids = $_product->get_upsell_ids();
			}
		}
		if ( 'cross_sells' === $recommendation_type ) {
			foreach ( $cart as $item ) {
				$_product    = new WC_Product( $item['product_id'] );
				$product_ids = $_product->get_cross_sell_ids();
			}
		}
		// Gather products and check stock.
		foreach ( $product_ids as $product_id ) {
			$_product = wc_get_product( $product_id );
			if ( ! $_product instanceof \WC_Product ) {
				continue;
			}
			$product_cart_ids[] = WC()->cart->generate_cart_id( $product_id );

			if ( $_product->is_in_stock() ) {
				$products[] = $_product;
			}
		}
		// When no products found.
		if ( empty( $products ) ) {
			if ( 'random_products' === $recommendation_fallback ) {
				$products = $this->get_random_products( [], 8, $current_cart_item_ids );
			}
		}
		return $products;
	}

	/**
	 * Get an object of products to recommend based admin settings for empty cart.
	 *
	 * @since 0.0.1
	 *
	 * @return array<object> $products An object of products.
	 */
	public function get_empty_cart_recommendations() {
		$recommendation_type = $this->get_option( 'empty_cart_recommendation', MODERNCART_SETTINGS );

		// Return empty if the type is invalid.
		if ( ! in_array( $recommendation_type, [ 'upsells', 'cross_sells', 'featured' ], true ) ) {
			return [];
		}

		static $products = null;

		// Check if the product is already cached.
		if ( ! is_null( $products ) ) {
			// Return the cached product if it exists. This ensures the performance of the function as we are not querying the database multiple times.
			return $products;
		}

		$limit = absint( apply_filters( 'moderncart_empty_cart_recommendation_limit', 5 ) );

		// Handle featured products using WC_Product_Query.
		if ( 'featured' === $recommendation_type ) {
			$query = new \WC_Product_Query(
				[
					'limit'        => $limit,
					'status'       => 'publish',
					'featured'     => true,
					'orderby'      => 'rand', // Randomize results.
					'stock_status' => 'instock', // Only in-stock products.
				]
			);

			/**
			 * Featured products.
			 *
			 * @var array<object>
			 */
			$products = $query->get_products();
			return $products;
		}

		// Handle upsells or cross-sells.
		global $wpdb;
		$meta_key = 'upsells' === $recommendation_type ? '_upsell_ids' : '_crosssell_ids';

		// Get up to 5 meta_value rows that contain serialized arrays of product IDs.
		// Ignored the phpcs error here because we are using a prepared statement and we are already caching this function internally for the performance.
		$results = $wpdb->get_col( // @phpcs:ignore
			$wpdb->prepare(
				"SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s LIMIT %d",
				$meta_key,
				$limit
			)
		);

		$collected_ids = [];

		foreach ( $results as $row ) {
			$ids = maybe_unserialize( $row );
			if ( is_array( $ids ) ) {
				$collected_ids = array_merge( $collected_ids, $ids );
			}
		}

		// Clean up: remove duplicates and non-numeric values.
		$product_ids = array_unique( array_filter( $collected_ids, 'is_numeric' ) );

		if ( empty( $product_ids ) ) {
			return [];
		}

		// Shuffle to randomize the order.
		shuffle( $product_ids );

		// Limit the final result to $limit.
		$product_ids = array_slice( $product_ids, 0, $limit );

		// Get WC_Product objects, filter out any invalid, unpublished, or out-of-stock products.
		$products = array_filter(
			array_map( 'wc_get_product', $product_ids ),
			static function ( $product ) {
				return $product && $product->is_visible() && $product->is_in_stock();
			}
		);

		return $products;
	}

	/**
	 * Renders the coupon removal partial.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_coupon_removal(): void {
		$coupons = WC()->cart->get_applied_coupons();
		?>
		<div class="moderncart-coupon-remove" role="region" aria-label="<?php esc_attr_e( 'Applied Coupons', 'modern-cart' ); ?>">
			<?php
			if ( $coupons ) {
				$order_summary_style = $this->get_option( 'order_summary_style', MODERNCART_SETTINGS, 'style1' );

				$html = '';
				foreach ( $coupons as $coupon ) {
					$coupon      = new WC_Coupon( $coupon );
					$code        = $coupon->get_code();
					$coupon_data = $coupon->get_data();

					if ( empty( WC()->cart->get_cart() ) ) {
						WC()->cart->remove_coupon( $code );
						continue;
					}

					$discount_amount   = $coupon_data['amount'];
					$discount_type     = $coupon_data['discount_type'];
					$currency_symbol   = get_woocommerce_currency_symbol();
					$coupon_text_price = ( 'percent' === $discount_type ? $discount_amount . '%' : $currency_symbol . $discount_amount );

					$html .= '<div class="moderncart-coupons-tag">';
					$html .= '<span class="moderncart-coupon-remove-item moderncart-coupon-remove-item-link" aria-hidden="true">';
					/* translators: %1$s is replaced with the coupon name and %2$s with the coupon amount  */
					$html .= 'style2' === $order_summary_style ? strtoupper( $code ) : sprintf( esc_html__( 'Coupon "%1$s" (%2$s)', 'modern-cart' ), esc_html( $code ), esc_html( $coupon_text_price ) );
					$html .= '</span>';
					/* translators: %1$s is replaced with the coupon name and %2$s with the coupon amount  */
					$html .= sprintf(
						'<button type="button" class="moderncart-coupon-remove-item moderncart-coupon-remove-item-delete" data-coupon="%1$s" aria-label="%3$s" title="%3$s">%4$s</button>',
						esc_attr( $code ),
						esc_attr( $coupon_text_price ),
						/* translators: %1$s is replaced with the coupon name and %2$s with the coupon amount  */
						sprintf( esc_attr__( 'Remove coupon %1$s (%2$s)', 'modern-cart' ), esc_attr( $code ), esc_attr( $coupon_text_price ) ),
						'<span class="moderncart-sr-only">' . esc_html__( 'Remove coupon', 'modern-cart' ) . '</span>'
					);
					$html .= '</div>';
				}
				echo wp_kses_post( $html );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Gets the cart toals
	 *
	 * @param array<string> $args Arguments for the coupon form.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_coupon_form( $args ): void {
		$enable_coupon = $this->get_option( 'enable_coupon_field', MODERNCART_SETTINGS, 'minimize' );

		if ( 'disabled' === $enable_coupon || empty( WC()->cart->get_cart() ) ) {
			return;
		}

		$coupons = WC()->cart->get_applied_coupons();

		if ( ! empty( $coupons ) ) {
			foreach ( $coupons as $coupon ) {
				$coupon = new WC_Coupon( $coupon );
			}
		}

		if ( ! empty( $args['message_type'] ) && 'error' === $args['message_type'] ) {
			$enable_coupon = 'expand';
		}

		$data = [
			'classes'           => apply_filters( 'moderncart_slide_out_coupon_form_classes', [ 'moderncart-slide-out-coupon', 'sample' ] ),
			'active_coupon'     => WC()->cart->get_applied_coupons(),
			'currency_symbol'   => get_woocommerce_currency_symbol(),
			'moderncart_coupon' => ( ! empty( $coupon ) ? $coupon->get_code() : '' ),
			'title'             => $this->get_option( 'coupon_title', MODERNCART_SETTINGS, __( 'Got a Discount Code?', 'modern-cart' ) ),
			'placeholder_text'  => $this->get_option( 'coupon_placeholder', MODERNCART_SETTINGS, __( 'Enter discount code', 'modern-cart' ) ),
			'button_text'       => esc_html__( 'Apply', 'modern-cart' ),
			'arrow_down'        => 'minimize' === $enable_coupon ? '' : 'moderncart-hide',
			'arrow_up'          => 'minimize' === $enable_coupon ? 'moderncart-hide' : '',
			'data_args'         => $args,
		];

		if ( 'minimize' === $enable_coupon ) {
			$data['classes'][] = 'moderncart-hide';
		}

		moderncart_get_template_part( 'cart/coupon-form', '', $data );
	}

	/**
	 * Gets the cart toals
	 *
	 * @param array<string> $args Arguments for the cart totals.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_totals( $args ): void {
		$cart         = WC()->cart->get_cart();
		$checkout_url = apply_filters( 'moderncart_checkout_button_url', wc_get_checkout_url() );
		$shop_url     = apply_filters( 'moderncart_empty_cart_button_url', get_permalink( wc_get_page_id( 'shop' ) ) );

		$order_summary_style = apply_filters( 'moderncart_order_summary_style', 'style1' );

		if ( ! empty( $cart ) ) {
			$button_text = $this->get_option( 'checkout_button_label', MODERNCART_SETTINGS, esc_html__( 'Checkout now', 'modern-cart' ) );
			$url         = $checkout_url;
		} else {
			$button_text = apply_filters( 'moderncart_empty_cart_message', $this->get_option( 'empty_cart_button_text', MODERNCART_SETTINGS, esc_html__( 'Your cart is empty. Shop now', 'modern-cart' ) ) );
			$url         = $shop_url;

			$order_summary_style = 'style1'; // Set to default style if cart is empty.
		}

		$wrapper = 'style1' === $order_summary_style;

		$data = [
			'order_summary_style' => $order_summary_style,
			'classes'             => apply_filters( 'moderncart_slide_out_cart_totals_classes', [ 'moderncart-cart-total', 'sample', "moderncart-order-summary-style-{$order_summary_style}" ] ),
			'subtotal'            => $this->get_subtotal_html( $wrapper ),
			'discount'            => $this->get_discount_html( $wrapper ),
			'shipping'            => $this->get_shipping_html( $wrapper ),
			'total'               => $this->get_total_html( $wrapper ),
			'tax'                 => $this->get_tax_html( $wrapper ),
			'button_text'         => $button_text,
			'coupon'              => [
				'title'            => $this->get_option( 'coupon_title', MODERNCART_SETTINGS, __( 'Got a Discount Code?', 'modern-cart' ) ),
				'placeholder_text' => $this->get_option( 'coupon_placeholder', MODERNCART_SETTINGS, __( 'Enter discount code', 'modern-cart' ) ),
			],
			'url'                 => $url,
			'data_args'           => $args,
		];

		moderncart_get_template_part( 'cart/cart-totals', $order_summary_style, $data );
	}

	/**
	 * Load slide out header
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_header(): void {
		$data = [
			'classes'  => apply_filters( 'moderncart_slide_out_header_classes', [ 'moderncart-slide-out-header-heading', 'sample' ] ),
			'title'    => $this->get_option( 'main_title', MODERNCART_SETTINGS, esc_html__( 'Review Your Cart', 'modern-cart' ) ),
			'quantity' => Helper::get_cart_count(),
		];

		if ( empty( WC()->cart->get_cart() ) ) {
			$data['title'] = esc_html__( 'Your Cart Is Empty', 'modern-cart' );
		}

		moderncart_get_template_part( 'cart/header-' . $this->get_option( 'cart_header_style', MODERNCART_SETTINGS, 'style1' ), '', $data );
	}

	/**
	 * Load slide out footer
	 *
	 * @param array<string> $args Arguments for the cart footer template.
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_footer( $args ): void {
		moderncart_get_template_part( 'cart/footer', '', $args );
	}

	/**
	 * Get all cart contents
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function render_contents(): void {
		$cart                              = WC()->cart->get_cart();
		$cart_class                        = empty( $cart ) ? 'moderncart-slide-out-cart-empty' : 'moderncart-slide-out-cart-data';
		$enabled_empty_cart_recommendation = ( empty( $cart ) && ( 'disabled' !== $this->get_option( 'empty_cart_recommendation', MODERNCART_SETTINGS, 'disabled' ) ) ) && ! empty( $this->get_empty_cart_recommendations() );

		if ( ! $enabled_empty_cart_recommendation ) {
			// If empty cart recommendation is not enabled then only render this section in the cart panel.
			?>
		<div class="moderncart-slide-out-cart">
			<div class="moderncart-slide-out-cart-inner <?php echo esc_attr( $cart_class ); ?>" role="list">
				<?php
				if ( ! empty( $cart ) ) {
					foreach ( $cart as $cart_item_key => $cart_item ) {
						$_product = apply_filters(
							'moderncart_woocommerce_cart_item_product',
							$cart_item['data'],
							$cart_item,
							$cart_item_key
						);
						$name     = $this->get_product_name( $cart_item, $cart_item_key );
						$data     = [
							'product_name'      => $name,
							'classes'           => apply_filters( 'moderncart_slide_out_cart_item_classes', [ 'moderncart-cart-item' ] ),
							'quantity'          => $this->render_quantity_selectors(
								[
									'input_value'   => $cart_item['quantity'],
									'quantity'      => $cart_item['quantity'],
									'max_value'     => $_product->get_max_purchase_quantity(),
									'min_value'     => $_product->get_min_purchase_quantity(),
									'product_name'  => $name,
									'cart_item_key' => $cart_item_key,
								],
								$_product,
								true
							),
							'delete'            => true,
							'cart_item'         => $cart_item,
							'cart_item_key'     => $cart_item_key,
							'product'           => $_product,
							'product_id'        => apply_filters(
								'moderncart_woocommerce_cart_item_product_id',
								$cart_item['product_id'],
								$cart_item,
								$cart_item_key
							),
							'product_permalink' => apply_filters(
								'moderncart_woocommerce_cart_item_permalink',
								( $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '' ),
								$cart_item,
								$cart_item_key
							),
							'thumbnail'         => wp_get_attachment_image( $_product->get_image_id(), 'thumbnail' ),
							'product_subtotal'  => moderncart_cart_item_price( $_product, $cart_item, $cart_item_key ),
						];

						moderncart_get_template_part( 'cart/cart-item-' . $this->get_cart_theme_style(), '', $data, false );
					}
				} else {
					$data = [
						'classes'   => apply_filters( 'moderncart_slide_out_empty_cart_classes', [ 'moderncart-empty-cart', 'simple' ] ),
						'headline'  => '',
						'subheader' => __( 'Check out our shop to see what\'s available', 'modern-cart' ),
					];

					moderncart_get_template_part( 'cart/empty-state', '', $data, false );
				}
				?>
			</div>
			<?php if ( ! empty( $cart ) ) { ?>
				<?php do_action( 'moderncart_slide_out_cart_after' ); ?>
			<?php } ?>
		</div>
			<?php
		}
		if ( empty( $cart ) ) {
			?>
			<?php do_action( 'moderncart_slide_out_cart_after' ); ?>
		<?php } ?>
		<?php
	}

	/**
	 * Output the quantity input for add to cart forms.
	 *
	 * @since 0.0.1
	 *
	 * @param  array<string>    $data Args for the input.
	 * @param  \WC_Product|null $product Product.
	 * @param  bool             $echo Whether to return or echo|string.
	 *
	 * @return string
	 */
	public function render_quantity_selectors( $data = [], $product = null, $echo = true ) {
		if ( is_null( $product ) ) {
			return '';
		}
		if ( $product->is_sold_individually() ) {
			return '';
		}
		$defaults          = [
			'input_id'      => uniqid( 'quantity_' ),
			'input_name'    => 'quantity',
			'input_value'   => '1',
			'classes'       => apply_filters( 'moderncart_woocommerce_quantity_input_classes', [ 'input-text', 'qty', 'text' ], $product ),
			'max_value'     => apply_filters( 'moderncart_woocommerce_quantity_input_max', -1, $product ),
			'min_value'     => apply_filters( 'moderncart_woocommerce_quantity_input_min', 0, $product ),
			'step'          => apply_filters( 'moderncart_woocommerce_quantity_input_step', 1, $product ),
			'pattern'       => apply_filters( 'moderncart_woocommerce_quantity_input_pattern', ( has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ) ),
			'inputmode'     => apply_filters( 'moderncart_woocommerce_quantity_input_inputmode', ( has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ) ),
			'product_name'  => $product->get_title(),
			'placeholder'   => apply_filters( 'moderncart_woocommerce_quantity_input_placeholder', '', $product ),
			'cart_item_key' => '',
		];
		$data              = apply_filters( 'moderncart_woocommerce_quantity_input_args', wp_parse_args( $data, $defaults ), $product );
		$data['min_value'] = max( $data['min_value'], 0 );
		$data['max_value'] = ( 0 < $data['max_value'] ? $data['max_value'] : '' );

		if ( '' !== $data['max_value'] && $data['max_value'] < $data['min_value'] ) {
			$data['max_value'] = $data['min_value'];
		}
		return moderncart_get_template_part( 'cart/quantity-selector', '', $data, $echo );
	}

	/**
	 * Helper fuction to get the product name.
	 *
	 * @since 0.0.1
	 *
	 * @param  array<object> $cart_item     Single cart item.
	 * @param  string        $cart_item_key Single cart key.
	 * @return string
	 */
	public function get_product_name( $cart_item, $cart_item_key ) {
		$_product              = apply_filters(
			'moderncart_woocommerce_cart_item_product',
			$cart_item['data'],
			$cart_item,
			$cart_item_key
		);
		$product_id            = apply_filters(
			'moderncart_woocommerce_cart_item_product_id',
			$cart_item['product_id'],
			$cart_item,
			$cart_item_key
		);
		$product_permalink     = apply_filters(
			'moderncart_woocommerce_cart_item_permalink',
			( $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '' ),
			$cart_item,
			$cart_item_key
		);
		$cart_item_data        = wc_get_formatted_cart_item_data( $cart_item );
		$show_details_collapse = apply_filters( 'moderncart_after_cart_item_name_hook_collapsible', true );
		$html                  = '<div class="moderncart-cart-item-product-link">';
		$slashed_price         = '';

		if ( ! $product_permalink ) {
			$html .= wp_kses_post(
				apply_filters(
					'moderncart_woocommerce_cart_item_name',
					$_product->get_name(),
					$cart_item,
					$cart_item_key
				) . '&nbsp;'
			);
		} else {
			$html .= wp_kses_post(
				apply_filters(
					'moderncart_woocommerce_cart_item_name',
					sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), // No need to add esc_html here as it is not required and we are using wp_kses_post too.
					$cart_item,
					$cart_item_key
				)
			);
		}

		$html .= '</div>';
		do_action( 'moderncart_woocommerce_after_cart_item_name', $cart_item, $cart_item_key );
		if ( apply_filters( 'moderncart_after_cart_item_name_price', false ) ) {
			$html .= '<div class="moderncart-cart-item-product--single-price">' . esc_html( $slashed_price ) . '</div>';
		}

		if ( $show_details_collapse && $cart_item_data ) {
			$html .= '<span class="moderncart-collapse-btn-link" data-moderncart-toggle="collapse" data-moderncart-target="moderncart-collapse-' . esc_attr( $cart_item_key ) . '" role="button" tabindex="0">' . esc_html__( 'View details', 'modern-cart' ) . '</span>';
			$html .= '<div class="moderncart-cart-item-product-data moderncart-collapse moderncart-collapse-' . esc_attr( $cart_item_key ) . '">';
			$html .= '<h5>' . esc_html__( 'Product details', 'modern-cart' ) . '</h5>';
			$html .= wp_kses_post( $cart_item_data );
			$html .= '</div>';
		}

		if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
			$html .= wp_kses_post( apply_filters( 'moderncart_woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'modern-cart' ) . '</p>', $product_id ) );
		}

		return $html;
	}

	/**
	 * Slide out cart
	 *
	 * @since 0.0.1
	 *
	 * @return void
	 */
	public function slide_out(): void {
		if ( ! $this->is_global_enabled() ) {
			return;
		}

		$modal_classes = [
			'moderncart-plugin',
			'moderncart-modal',
			'moderncart-cart-style-slideout',
			'moderncart-recommendation-style-style1',
			'moderncart-cart-theme-' . $this->get_cart_theme_style(),
			'moderncart-slide-right',
		];

		$data = [
			'modal_classes' => apply_filters( 'moderncart_modal_slide_out_classes', $modal_classes ),
			'classes'       => $this->get_slide_out_classes(),
			'attributes'    => [
				'tabindex' => '-1',
				'role'     => 'dialog',
			],
			'notice'        => '',
			'message_type'  => '',
		];

		moderncart_get_template_part( 'shop/slide-out', '', $data );
	}

	/**
	 * Get slide out classes
	 *
	 * @since 0.0.1
	 *
	 * @return array<string>
	 */
	protected function get_slide_out_classes() {
		$order_summary_style = Helper::convert_to_string( $this->get_option( 'order_summary_style', MODERNCART_SETTINGS, 'style1' ) );
		$product_image_size  = Helper::convert_to_string( $this->get_option( 'product_image_size', MODERNCART_SETTINGS, 'medium' ) );

		return apply_filters(
			'moderncart_slide_out_classes',
			[
				'moderncart-default-slide-out',
				'moderncart-modal-wrap',
				'moderncart-animation-simple',
				"moderncart-{$order_summary_style}-order-summary-style",
				"moderncart-image-size-{$product_image_size}",
			]
		);
	}

	/**
	 * Get an array of random recommended products.
	 *
	 * @since 0.0.1
	 *
	 * @param array<\WC_Product> $products Array of already recommended products.
	 * @param int                $count    Maximum number of products to return.
	 * @param array<int>         $excludes Product IDs to exclude from results.
	 *
	 * @return array<object>
	 */
	private function get_random_products( $products = [], $count = 4, $excludes = [] ) {
		// Copy existing products to work with.
		$recommended = $products;

		// Fetch random in-stock products, excluding hidden ones.
		$query = new \WC_Product_Query(
			[
				'limit'        => $count,
				'status'       => 'publish',
				'orderby'      => 'rand',
				'stock_status' => 'instock',
				'tax_query'    => [ // @phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => [ 'exclude-from-catalog' ],
						'operator' => 'NOT IN',
					],
				],
			]
		);

		/**
		 * Random products list.
		 *
		 * @var array<\WC_Product> $random_products.
		 */
		$random_products = $query->get_products();

		$cart = WC()->cart;

		foreach ( $random_products as $product ) {
			$product_id = $product->get_id();

			// Skip if product is in the exclude list.
			if ( in_array( $product_id, $excludes, true ) ) {
				continue;
			}

			// Prevent duplicate recommendations and already added cart items.
			$cart_id = $cart->generate_cart_id( $product_id );

			if (
				! $cart->find_product_in_cart( $cart_id ) &&
				count( $recommended ) < $count &&
				$product->is_in_stock()
			) {
				$recommended[] = $product;
			}

			// Add to excludes to avoid re-processing.
			$excludes[] = $product_id;

			// Early exit if we’ve reached the desired count.
			if ( count( $recommended ) >= $count ) {
				break;
			}
		}

		return $recommended;
	}
}
