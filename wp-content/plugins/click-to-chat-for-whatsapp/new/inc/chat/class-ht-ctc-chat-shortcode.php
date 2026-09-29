<?php
/**
 * Shortcode handler for Click to Chat buttons.
 *
 * Provides the [ht-ctc-chat] shortcode and its supported attributes.
 *
 * @package Click_To_Chat
 * @subpackage Chat
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Chat_Shortcode' ) ) {

	/**
	 * Generates chat shortcode output.
	 */
	class HT_CTC_Chat_Shortcode {

		/**
		 * Register the chat shortcode with WordPress.
		 *
		 * @return void
		 */
		public function shortcodes_init() {
			add_shortcode( 'ht-ctc-chat', array( $this, 'shortcode' ) );
		}

		/**
		 * Render the chat shortcode.
		 *
		 * @param array       $atts      Shortcode attributes.
		 * @param string|null $content   Enclosed content (unused).
		 * @param string      $shortcode Shortcode tag.
		 * @return string Rendered markup or an empty string when hidden.
		 */
		public function shortcode( $atts = array(), $content = null, $shortcode = '' ) {

			$options     = get_option( 'ht_ctc_chat_options' );
			$woo_options = get_option( 'ht_ctc_woo_options' );
			$ht_ctc_os   = array();

			$call_to_action = esc_attr( $options['call_to_action'] );
			$pre_filled     = esc_attr( $options['pre_filled'] );

			$call_to_action = apply_filters( 'wpml_translate_single_string', $call_to_action, 'Click to Chat for WhatsApp', 'call_to_action' );
			$pre_filled     = apply_filters( 'wpml_translate_single_string', $pre_filled, 'Click to Chat for WhatsApp', 'pre_filled' );

			// @since 4.3 if shortcode number attribute is not added, global number will be used at js.
			// $number = (isset($options['number'])) ? esc_attr($options['number']) : '';
			// if woocommerce single product page
			$style_desktop = ( isset( $options['style_desktop'] ) ) ? esc_attr( $options['style_desktop'] ) : '2';
			if ( isset( $options['same_settings'] ) ) {
				$style_mobile = $style_desktop;
			} else {
				$style_mobile = ( isset( $options['style_mobile'] ) ) ? esc_attr( $options['style_mobile'] ) : '2';
			}

			$is_mobile = ht_ctc()->device_type->is_mobile();

			$style = $style_desktop;
			if ( 'yes' === $is_mobile ) {
				$style = $style_mobile;
			}

			if ( function_exists( 'is_product' ) && function_exists( 'wc_get_product' ) ) {
				if ( is_product() ) {

					// $product = wc_get_product();

					// $name = $product->get_name();
					// // $title = $product->get_title();
					// $price         = $product->get_price();
					// $regular_price = $product->get_regular_price();
					// $sku           = $product->get_sku();

					// pre-filled
					if ( isset( $woo_options['woo_pre_filled'] ) && '' !== $woo_options['woo_pre_filled'] ) {
						$pre_filled = esc_attr( $woo_options['woo_pre_filled'] );
						$pre_filled = apply_filters( 'wpml_translate_single_string', $pre_filled, 'Click to Chat for WhatsApp', 'woo_pre_filled' );
					}
					// variables now works in default pre_filled also
					// $pre_filled = str_replace( array( '{product}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price, $regular_price, $sku ), $pre_filled );

					// call to action
					if ( isset( $woo_options['woo_call_to_action'] ) && '' !== $woo_options['woo_call_to_action'] ) {
						$call_to_action = esc_attr( $woo_options['woo_call_to_action'] );
						$call_to_action = apply_filters( 'wpml_translate_single_string', $call_to_action, 'Click to Chat for WhatsApp', 'woo_call_to_action' );
						// $call_to_action = str_replace( array( '{product}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price, $regular_price, $sku ), $call_to_action );
					}
				}
			}

			// $content = do_shortcode($content);

			// $ccw_options_cs = get_option('ccw_options_cs');
			// use like  $ccw_options_cs['']

			$a = shortcode_atts(
				array(
					'number'           => '',
					'call_to_action'   => $call_to_action,
					'pre_filled'       => $pre_filled,
					'style'            => $style,

					'position'         => '',
					'top'              => '',
					'right'            => '',
					'bottom'           => '',
					'left'             => '',
					'home'             => '',  // home -  to hide on experts ..
					'hide_mobile'      => '',
					'hide_desktop'     => '',

					's5_img_position'  => '',  // left, right
					's5_img_url'       => '',
					's5_line_2'        => '',

					's8_width'         => '',
					's8_icon_position' => '',  // left, right, hide

				),
				$atts,
				$shortcode
			);
			// use like -  '.esc_attr($a["title"]).'

			// number
			$number = esc_attr( $a['number'] );

			// if random number feature, this have to modify (ltrim, preg_replace)
			// $number = preg_replace('/[^0-9,\s]/', '', $number );
			$number = preg_replace( '/\D/', '', $number );
			$number = ltrim( $number, '0' );

			// pre-filled text
			$page_url   = get_permalink();
			$post_title = esc_html( get_the_title() );

			$pre_filled = esc_attr( $a['pre_filled'] );
			$pre_filled = str_replace( array( '{{url}}', '{url}', '{{title}}', '{title}', '{{site}}', '{site}' ), array( $page_url, $page_url, $post_title, $post_title, HT_CTC_BLOG_NAME, HT_CTC_BLOG_NAME ), $pre_filled );

			// call to action
			$call_to_action = esc_attr( $a['call_to_action'] );

			if ( function_exists( 'is_product' ) && function_exists( 'wc_get_product' ) ) {
				if ( is_product() ) {

					$product = wc_get_product();

					$name = $product->get_name();
					// $title = $product->get_title();
					$price         = $product->get_price();
					$regular_price = $product->get_regular_price();
					$sku           = $product->get_sku();

					// variables now works in default pre_filled also
					$pre_filled = str_replace( array( '{product}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price, $regular_price, $sku ), $pre_filled );
					// call to action
					$call_to_action = str_replace( array( '{product}', '{price}', '{regular_price}', '{sku}' ), array( $name, $price, $regular_price, $sku ), $call_to_action );
				}
			}

			// output: is throwing '1', '4', '6, '8' in string only
			if ( '' === $call_to_action ) {
				if ( '1' === $style || '4' === $style || '6' === $style || '8' === $style ) {
					$call_to_action = 'WhatsApp us';
				}
			}

			// hide on devices
			// if 'yes' then hide
			$hide_mobile  = esc_attr( $a['hide_mobile'] );
			$hide_desktop = esc_attr( $a['hide_desktop'] );

			if ( 'yes' === $is_mobile ) {
				if ( 'yes' === $hide_mobile ) {
					return '';
				}
			} elseif ( 'yes' === $hide_desktop ) {
					return '';
			}

			$position = esc_attr( $a['position'] );
			$top      = esc_attr( $a['top'] );
			$right    = esc_attr( $a['right'] );
			$bottom   = esc_attr( $a['bottom'] );
			$left     = esc_attr( $a['left'] );

			$css = '';

			if ( '' !== $position ) {
				$css .= 'position:' . $position . ';';
			}
			if ( '' !== $top ) {
				$css .= 'top:' . $top . ';';
			}
			if ( '' !== $right ) {
				$css .= 'right:' . $right . ';';
			}
			if ( '' !== $bottom ) {
				$css .= 'bottom:' . $bottom . ';';
			}
			if ( '' !== $left ) {
				$css .= 'left:' . $left . ';';
			}

			// to hide styles in home, archive, category pages
			$home = esc_attr( $a['home'] );
			// $position !== 'fixed' why !== to avoid double time adding display: none ..
			if ( 'fixed' !== $position && 'hide' === $home && ( is_home() || is_category() || is_archive() ) ) {
				$css .= 'display:none;';
			}
			// By default position: fixed style hide on home screen,
			// if plan to show, then add hide='show' ( actually something not equal to 'hide' )
			if ( 'fixed' === $position && 'show' !== $home && ( is_home() || is_category() || is_archive() ) ) {
				$css .= 'display:none;';
			}

			$return_type = 'chat';

			$style = esc_attr( $a['style'] );
			$style = sanitize_file_name( $style );

			$type        = 'chat-sc';
			$class_names = "ht-ctc-sc ht-ctc-sc-chat sc-style-$style";

			// analytics
			$ht_ctc_os['data-attributes'] = '';
			$ht_ctc_os['class_names']     = '';

			// Hooks
			$ht_ctc_os = apply_filters( 'ht_ctc_fh_os', $ht_ctc_os );

			$data_number = '';
			// if number not null, then add data-number attribute
			if ( '' !== $number ) {
				$data_number .= ' data-number="' . $number . '"';
			}

			$o = '';

			// shortcode template file path
			$sc_path = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/styles-shortcode/sc-style-' . $style . '.php';

			if ( is_file( $sc_path ) ) {
				$o .= '<div ' . $data_number . ' data-pre_filled="' . $pre_filled . '" data-style="' . $style . '" style="display: inline; cursor: pointer; z-index: 99999999; ' . $css . '" class="' . $class_names . ' ht-ctc-inline">';
				include $sc_path;
				$o .= '</div>';
			}

			return $o;
		}
	}


	$shortcode = new HT_CTC_Chat_Shortcode();

	add_action( 'init', array( $shortcode, 'shortcodes_init' ) );

} // END class_exists check
