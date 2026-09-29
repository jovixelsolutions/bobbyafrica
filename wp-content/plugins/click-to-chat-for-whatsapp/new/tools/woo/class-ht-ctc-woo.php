<?php
/**
 * Woocommerce related front end.
 *
 * @package Click_To_Chat
 * @since 2.9
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_WOO_Pages' ) ) {

	/**
	 * WooCommerce pages integration class.
	 */
	class HT_CTC_WOO_Pages {

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->woo_hooks();
		}

		/**
		 * Register WooCommerce integration hooks.
		 * Decides where to place the WhatsApp widget on Shop and Single Product pages.
		 *
		 * @return void
		 */
		public function woo_hooks() {

			$woo = get_option( 'ht_ctc_woo_options' );

			// chat - woo values
			add_filter( 'ht_ctc_fh_chat', array( $this, 'override_chat' ) );

			// woo places
			$woo_position = ( isset( $woo['woo_position'] ) ) ? esc_attr( $woo['woo_position'] ) : 'select';
			if ( 'select' !== $woo_position ) {
				add_action( $woo_position, array( $this, 'add_widget_single_product_page' ) );
			}

			// shop page - add styles
			if ( isset( $woo['woo_shop_add_whatsapp'] ) ) {
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'add_widget_shop_page' ), 20 );
			}

			// cart page
			// add_action( 'woocommerce_after_cart_totals', [$this, 'after_cart_totals'] );

			// checkout page
		}


		/**
		 * In cart page.
		 */
		// function after_cart_totals() {
		// foreach ( WC()->cart->get_cart() as $cart_item ) {
		// $product = $cart_item['data'];
		// }
		// }


		/**
		 * Shop page, archive items add style..
		 * Displays the WhatsApp button next to each product in the shop/archive grid.
		 * Retrieves product data and replaces dynamic variables like {product} and {price}.
		 *
		 * @return void
		 */
		public function add_widget_shop_page() {

			$woo_options   = get_option( 'ht_ctc_woo_options' );
			$chat          = get_option( 'ht_ctc_chat_options' );
			$othersettings = get_option( 'ht_ctc_othersettings' );
			$type          = 'chat';
			$calling_from  = 'woo_page';

			$ht_ctc_woo_shop = array();

			$ht_ctc_woo_shop['style'] = ( isset( $woo_options['woo_shop_style'] ) ) ? esc_attr( $woo_options['woo_shop_style'] ) : '8';

			$page_id = get_the_ID();

			// page level
			$ht_ctc_pagelevel = array();

			// is set page_level_settings disabled
			if ( ! isset( $othersettings['disable_page_level_settings'] ) ) {
				// get post meta with ht_ctc_pagelevel key
				$ht_ctc_pagelevel = get_post_meta( $page_id, 'ht_ctc_pagelevel', true );
			}

			/**
			 * Call to action
			 * shop call to action - if not - shop page level - if not - call to action ..
			 * here variables works based on the product .. {url} its product url not the page url..
			 */
			// Call to action
			if ( isset( $ht_ctc_pagelevel['call_to_action'] ) ) {
				$call_to_action = esc_attr( $ht_ctc_pagelevel['call_to_action'] );
			} elseif ( isset( $woo_options['woo_shop_call_to_action'] ) && '' !== $woo_options['woo_shop_call_to_action'] ) {
				$call_to_action = esc_attr( $woo_options['woo_shop_call_to_action'] );
				$call_to_action = apply_filters( 'wpml_translate_single_string', $call_to_action, 'Click to Chat for WhatsApp', 'woo_shop_call_to_action' );
			} else {
				$call_to_action = ( isset( $chat['call_to_action'] ) ) ? esc_attr( $chat['call_to_action'] ) : '';
				$call_to_action = apply_filters( 'wpml_translate_single_string', $call_to_action, 'Click to Chat for WhatsApp', 'call_to_action' );
			}

			// Pre-filled
			if ( isset( $ht_ctc_pagelevel['pre_filled'] ) ) {
				$pre_filled = esc_attr( $ht_ctc_pagelevel['pre_filled'] );
			} elseif ( isset( $woo_options['woo_shop_pre_filled'] ) && '' !== $woo_options['woo_shop_pre_filled'] ) {
				$pre_filled = esc_attr( $woo_options['woo_shop_pre_filled'] );
				$pre_filled = apply_filters( 'wpml_translate_single_string', $pre_filled, 'Click to Chat for WhatsApp', 'woo_shop_pre_filled' );
			} else {
				$pre_filled = ( isset( $chat['pre_filled'] ) ) ? esc_attr( $chat['pre_filled'] ) : '';
				$pre_filled = apply_filters( 'wpml_translate_single_string', $pre_filled, 'Click to Chat for WhatsApp', 'pre_filled' );
			}

			if ( function_exists( 'wc_get_product' ) ) {
				$product = wc_get_product();

				// Validate product object to prevent fatal errors.
				if ( is_object( $product ) && method_exists( $product, 'get_name' ) ) {
					$name = $product->get_name();
					// $title = $product->get_title();

					$price         = $product->get_price();
					$regular_price = $product->get_regular_price();
					$sku           = $product->get_sku();

					// variables works in default pre_filled also for woo pages.
					$call_to_action = str_replace( array( '{product}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price, $regular_price, $sku ), $call_to_action );
					$pre_filled     = str_replace( array( '{product}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price, $regular_price, $sku ), $pre_filled );
				}
			}

			$page_url   = get_permalink();
			$post_title = esc_html( get_the_title() );

			$pre_filled = str_replace( array( '{url}', '{title}', '{site}' ), array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $pre_filled );

			$woo_shop_margin_top      = ( isset( $woo_options['woo_shop_margin_top'] ) ) ? esc_attr( $woo_options['woo_shop_margin_top'] ) : '';
			$woo_shop_layout_cart_btn = ( isset( $woo_options['woo_shop_layout_cart_btn'] ) ) ? esc_attr( $woo_options['woo_shop_layout_cart_btn'] ) : '';
			$woo_shop_margin_right    = ( isset( $woo_options['woo_shop_margin_right'] ) ) ? esc_attr( $woo_options['woo_shop_margin_right'] ) : '';
			$woo_shop_margin_bottom   = ( isset( $woo_options['woo_shop_margin_bottom'] ) ) ? esc_attr( $woo_options['woo_shop_margin_bottom'] ) : '';
			$woo_shop_margin_left     = ( isset( $woo_options['woo_shop_margin_left'] ) ) ? esc_attr( $woo_options['woo_shop_margin_left'] ) : '';

			$css  = '';
			$css .= 'cursor:pointer;';

			if ( isset( $woo_options['woo_shop_position_center'] ) ) {
				$css .= 'text-align: center;';
			}

			if ( '' !== $woo_shop_margin_left ) {
				$css .= "margin-left:$woo_shop_margin_left;";
			}
			if ( '' !== $woo_shop_margin_right ) {
				$css .= "margin-right:$woo_shop_margin_right;";
			}
			if ( '' !== $woo_shop_margin_top ) {
				$css .= "margin-top:$woo_shop_margin_top;";
			}
			if ( '' !== $woo_shop_margin_bottom ) {
				$css .= "margin-bottom:$woo_shop_margin_bottom;";
			}

			$class_names  = '';
			$class_names .= 'ctc_chat ctc_woo_place';

			$display_from_js = '';

			// shop cart layout
			// output : is in string ony '1', '8'
			if ( '' !== $woo_shop_layout_cart_btn ) {
				if ( '1' === $ht_ctc_woo_shop['style'] || '8' === $ht_ctc_woo_shop['style'] ) {
					$class_names    .= ' ctc_woo_shop_cart_layout';
					$display_from_js = 'yes';
				}
			}

			$ht_ctc_woo_shop['shop_schedule'] = 'no';

			$woo_shop_block_type = 'block';

			// filter hook
			$ht_ctc_woo_shop = apply_filters( 'ht_ctc_fh_woo_shop', $ht_ctc_woo_shop );

			if ( 'yes' === $ht_ctc_woo_shop['shop_schedule'] ) {
				$class_names    .= ' ctc_woo_schedule';
				$display_from_js = 'yes';
			}

			if ( 'yes' === $display_from_js ) {
				$css .= 'display: none;';
			} else {
				$css .= "display: $woo_shop_block_type;";
			}

			$path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/styles/style-' . $ht_ctc_woo_shop['style'] . '.php';

			if ( is_file( $path ) ) {
				?>
			<div class="<?php echo esc_attr( $class_names ); ?>" style="<?php echo esc_attr( $css ); ?>" data-pre_filled="<?php echo esc_attr( $pre_filled ); ?>" data-dt="<?php echo esc_attr( $woo_shop_block_type ); ?>">
				<?php include $path; ?>
			</div>
				<?php
			}
		}


		/**
		 * Render styles when viewing WooCommerce single product pages.
		 * Checks if the current page is a single product page before displaying the widget.
		 *
		 * @return void
		 */
		public function add_widget_single_product_page() {

			if ( function_exists( 'is_product' ) && function_exists( 'wc_get_product' ) ) {
				if ( is_product() ) {
					$this->add_styles();
				}
			}
		}

		/**
		 * Woo places - add styles..
		 *
		 * Woo single styles located at woo-single-styles/woo-style-*.php for single product pages.
		 * Renders the WhatsApp widget on the Single Product Page with correct CSS and layout.
		 *
		 * @return void
		 */
		public function add_styles() {

			$woo_options   = get_option( 'ht_ctc_woo_options' );
			$chat          = get_option( 'ht_ctc_chat_options' );
			$page_id       = get_the_ID();
			$othersettings = get_option( 'ht_ctc_othersettings' );

			// page level
			$ht_ctc_pagelevel = array();

			// is set page_level_settings disabled
			if ( ! isset( $othersettings['disable_page_level_settings'] ) ) {
				// get post meta with ht_ctc_pagelevel key
				$ht_ctc_pagelevel = get_post_meta( $page_id, 'ht_ctc_pagelevel', true );
			}

			$type         = 'chat';
			$calling_from = 'woo_page';

			$ht_ctc_woo_single_product = array();

			$ht_ctc_woo_single_product['style'] = ( isset( $woo_options['woo_style'] ) ) ? esc_attr( $woo_options['woo_style'] ) : '8';

			// $side_2 = 'right';

			$page_display = ( isset( $ht_ctc_pagelevel['show_hide'] ) ) ? esc_attr( $ht_ctc_pagelevel['show_hide'] ) : '';

			if ( 'hide' === $page_display ) {
				return;
			}

			// Call to action
			if ( isset( $ht_ctc_pagelevel['call_to_action'] ) ) {
				$call_to_action = esc_attr( $ht_ctc_pagelevel['call_to_action'] );
			} elseif ( isset( $woo_options['woo_call_to_action'] ) && '' !== $woo_options['woo_call_to_action'] ) {
				$call_to_action = esc_attr( $woo_options['woo_call_to_action'] );
				$call_to_action = apply_filters( 'wpml_translate_single_string', $call_to_action, 'Click to Chat for WhatsApp', 'woo_call_to_action' );
			} else {
				$call_to_action = ( isset( $chat['call_to_action'] ) ) ? esc_attr( $chat['call_to_action'] ) : '';
				$call_to_action = apply_filters( 'wpml_translate_single_string', $call_to_action, 'Click to Chat for WhatsApp', 'call_to_action' );
			}

			// Pre-filled
			// if ( isset( $ht_ctc_pagelevel['pre_filled'] ) ) {
			// $pre_filled = esc_attr( $ht_ctc_pagelevel['pre_filled'] );
			// } elseif ( isset( $woo_options['woo_pre_filled'] ) && '' !== $woo_options['woo_pre_filled'] ) {
			// $pre_filled = esc_attr( $woo_options['woo_pre_filled'] );
			// $pre_filled = apply_filters( 'wpml_translate_single_string', $pre_filled, 'Click to Chat for WhatsApp', 'woo_pre_filled' );
			// } else {
			// $pre_filled = ( isset( $chat['pre_filled'] ) ) ? esc_attr( $chat['pre_filled'] ) : '';
			// $pre_filled = apply_filters( 'wpml_translate_single_string', $pre_filled, 'Click to Chat for WhatsApp', 'pre_filled' );
			// }

			include_once HT_CTC_PLUGIN_DIR . 'new/inc/commons/ht-ctc-formatting.php';
			if ( function_exists( 'ht_ctc_woo_single_product_page_variables' ) ) {
				$call_to_action = ht_ctc_woo_single_product_page_variables( $call_to_action );
				// $pre_filled     = ht_ctc_woo_single_product_page_variables( $pre_filled );
			}

			$woo_single_position_center = ( isset( $woo_options['woo_single_position_center'] ) ) ? esc_attr( $woo_options['woo_single_position_center'] ) : '';
			$woo_single_layout_cart_btn = ( isset( $woo_options['woo_single_layout_cart_btn'] ) ) ? esc_attr( $woo_options['woo_single_layout_cart_btn'] ) : '';
			$woo_single_margin_top      = ( isset( $woo_options['woo_single_margin_top'] ) ) ? esc_attr( $woo_options['woo_single_margin_top'] ) : '';
			$woo_single_margin_right    = ( isset( $woo_options['woo_single_margin_right'] ) ) ? esc_attr( $woo_options['woo_single_margin_right'] ) : '';
			$woo_single_margin_bottom   = ( isset( $woo_options['woo_single_margin_bottom'] ) ) ? esc_attr( $woo_options['woo_single_margin_bottom'] ) : '';
			$woo_single_margin_left     = ( isset( $woo_options['woo_single_margin_left'] ) ) ? esc_attr( $woo_options['woo_single_margin_left'] ) : '';

			$woo_single_block_type = ( isset( $woo_options['woo_single_block_type'] ) ) ? esc_attr( $woo_options['woo_single_block_type'] ) : 'inline-block';

			$css = 'cursor:pointer;';

			if ( isset( $woo_options['woo_single_position_center'] ) ) {
				$css .= 'text-align: center;';
			}

			if ( '' !== $woo_single_margin_left ) {
				$css .= "margin-left:$woo_single_margin_left;";
			}
			if ( '' !== $woo_single_margin_right ) {
				$css .= "margin-right:$woo_single_margin_right;";
			}
			if ( '' !== $woo_single_margin_top ) {
				$css .= "margin-top:$woo_single_margin_top;";
			}
			if ( '' !== $woo_single_margin_bottom ) {
				$css .= "margin-bottom:$woo_single_margin_bottom;";
			}

			$class_names  = '';
			$class_names .= 'ctc_chat ctc_woo_place';

			$display_from_js = '';

			// single - cart layout
			if ( '' !== $woo_single_layout_cart_btn ) {
				if ( '1' === $ht_ctc_woo_single_product['style'] || '8' === $ht_ctc_woo_single_product['style'] ) {
					$class_names    .= ' ctc_woo_single_cart_layout';
					$display_from_js = 'yes';
				}
			}

			$ht_ctc_woo_single_product['single_schedule'] = 'no';

			// filter hook
			$ht_ctc_woo_single_product = apply_filters( 'ht_ctc_fh_woo_single_product', $ht_ctc_woo_single_product );

			if ( 'yes' === $ht_ctc_woo_single_product['single_schedule'] ) {
				$class_names    .= ' ctc_woo_schedule';
				$display_from_js = 'yes';
			}

			$style = $ht_ctc_woo_single_product['style'];

			// if ( 'inline-block' === $woo_single_block_type ) {
			// $woo_single_block_type = "inline-flex";
			// }

			if ( 'yes' === $display_from_js ) {
				$css .= 'display: none;';
			} else {
				$css .= "display: $woo_single_block_type;";
			}

			// woo-single-styles/woo-style- .php .. specific to the single product pages.
			$path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/tools/woo/woo-single-styles/woo-style-' . $style . '.php';

			if ( is_file( $path ) ) {
				// data-pre_filled="<?php echo esc_attr( $pre_filled );
				?>
			<div class="<?php echo esc_attr( $class_names ); ?>" style="<?php echo esc_attr( $css ); ?>" data-dt="<?php echo esc_attr( $woo_single_block_type ); ?>">
				<?php include $path; ?>
			</div>
				<?php
			}
		}



		/**
		 * Filter WooCommerce chat configuration for product context.
		 * Intercepts the default chat message settings and replaces generic text
		 * with the current product's Name, Price, and SKU before sending to WhatsApp.
		 *
		 * @param array<string, mixed> $ht_ctc_chat Chat configuration array.
		 * @return array<string, mixed> Filtered chat configuration.
		 */
		public function override_chat( $ht_ctc_chat ) {

			$woo_options   = get_option( 'ht_ctc_woo_options' );
			$othersettings = get_option( 'ht_ctc_othersettings' );

			// $chat = get_option('ht_ctc_chat_options');

			// if woocommerce single product page
			if ( function_exists( 'is_product' ) && function_exists( 'wc_get_product' ) ) {
				if ( is_product() ) {

					$name            = '';
					$price           = '';
					$regular_price   = '';
					$sku             = '';
					$price_formatted = '';

					$product = wc_get_product();

					if ( is_object( $product ) && method_exists( $product, 'get_name' ) ) {
						$name = esc_attr( $product->get_name() );
						// $title = $product->get_title();
						$price         = esc_attr( $product->get_price() );
						$regular_price = esc_attr( $product->get_regular_price() );
						$sku           = esc_attr( $product->get_sku() );

						if ( '' !== $price && null !== $price ) {
							if ( function_exists( 'wc_price' ) ) {
								$price_formatted = html_entity_decode( wp_strip_all_tags( wc_price( $price ) ) );
								$price_formatted = esc_attr( $price_formatted );
							} else {
								$price_formatted = esc_attr( $price );
							}
						} else {
							$price_formatted = ''; // Keep output blank if price is not set
						}
					}

					$page_id = get_the_ID();

					// page level
					$ht_ctc_pagelevel = array();

					// is set page_level_settings disabled
					if ( ! isset( $othersettings['disable_page_level_settings'] ) ) {
						$ht_ctc_pagelevel = get_post_meta( $page_id, 'ht_ctc_pagelevel', true );
					}

					// pre-filled
					if ( isset( $woo_options['woo_pre_filled'] ) && '' !== $woo_options['woo_pre_filled'] ) {
						$ht_ctc_chat['pre_filled'] = esc_attr( $woo_options['woo_pre_filled'] );
						$ht_ctc_chat['pre_filled'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_chat['pre_filled'], 'Click to Chat for WhatsApp', 'woo_pre_filled' );
					}

					// page level settings - woo
					if ( isset( $ht_ctc_pagelevel['pre_filled'] ) ) {
						$ht_ctc_chat['pre_filled'] = esc_attr( $ht_ctc_pagelevel['pre_filled'] );
					}

					// variables works in default pre_filled also for woo pages.
					$ht_ctc_chat['pre_filled'] = str_replace( array( '{product}', '{{price}}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price_formatted, $price, $regular_price, $sku ), $ht_ctc_chat['pre_filled'] );

					// call to action
					if ( isset( $woo_options['woo_call_to_action'] ) && '' !== $woo_options['woo_call_to_action'] ) {
						$ht_ctc_chat['call_to_action'] = esc_attr( $woo_options['woo_call_to_action'] );
						$ht_ctc_chat['call_to_action'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_chat['call_to_action'], 'Click to Chat for WhatsApp', 'woo_call_to_action' );
					}

					// page level settings - woo
					if ( isset( $ht_ctc_pagelevel['call_to_action'] ) ) {
						$ht_ctc_chat['call_to_action'] = esc_attr( $ht_ctc_pagelevel['call_to_action'] );
					}

					$ht_ctc_chat['call_to_action'] = str_replace( array( '{product}', '{{price}}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price_formatted, $price, $regular_price, $sku ), $ht_ctc_chat['call_to_action'] );
				}
			}

			return $ht_ctc_chat;
		}
	}

	new HT_CTC_WOO_Pages();

} // END class_exists check
