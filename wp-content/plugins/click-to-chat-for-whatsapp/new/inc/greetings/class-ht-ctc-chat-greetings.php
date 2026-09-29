<?php
/**
 * WhatsApp Chat  - main page ..
 *
 * @uses ht-ctc-chat  if: 'no' !== $greetings['greetings_template']
 *
 * @subpackage chat
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Chat_Greetings' ) ) {

	/**
	 * WhatsApp Chat Greetings handler class.
	 */
	class HT_CTC_Chat_Greetings {

		/**
		 * Constructor to initialize the greetings functionality.
		 */
		public function __construct() {
			$this->start();
		}

		/**
		 * Initialize hooks and actions for greetings.
		 */
		public function start() {
			add_action( 'ht_ctc_ah_in_fixed_position', array( $this, 'greetings_dialog' ) );
		}


		/**
		 * Render the greetings dialog markup when enabled.
		 *
		 * @return void
		 */
		public function greetings_dialog() {

			$greetings          = get_option( 'ht_ctc_greetings_options' );
			$chat               = get_option( 'ht_ctc_chat_options' );
			$greetings_settings = get_option( 'ht_ctc_greetings_settings' );
			$g_box_classes      = '';

			$ht_ctc_greetings = array();

			$ht_ctc_greetings['greetings_template'] = ( isset( $greetings['greetings_template'] ) ) ? esc_attr( $greetings['greetings_template'] ) : '';
			$ht_ctc_greetings['header_content']     = ( isset( $greetings['header_content'] ) ) ? esc_attr( $greetings['header_content'] ) : '';
			$ht_ctc_greetings['main_content']       = ( isset( $greetings['main_content'] ) ) ? esc_attr( $greetings['main_content'] ) : '';
			$ht_ctc_greetings['bottom_content']     = ( isset( $greetings['bottom_content'] ) ) ? esc_attr( $greetings['bottom_content'] ) : '';
			$ht_ctc_greetings['call_to_action']     = ( isset( $greetings['call_to_action'] ) ) ? esc_attr( $greetings['call_to_action'] ) : '';

			$ht_ctc_greetings['is_opt_in'] = ( isset( $greetings_settings['is_opt_in'] ) ) ? esc_attr( $greetings_settings['is_opt_in'] ) : '';
			$ht_ctc_greetings['opt_in']    = ( isset( $greetings_settings['opt_in'] ) ) ? esc_attr( $greetings_settings['opt_in'] ) : '';

			if ( '' === $ht_ctc_greetings['call_to_action'] ) {
				$ht_ctc_greetings['call_to_action'] = 'WhatsApp';
			}

			$ht_ctc_greetings['header_content'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['header_content'], 'Click to Chat for WhatsApp', 'greetings_header_content' );
			$ht_ctc_greetings['main_content']   = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['main_content'], 'Click to Chat for WhatsApp', 'greetings_main_content' );
			$ht_ctc_greetings['bottom_content'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['bottom_content'], 'Click to Chat for WhatsApp', 'greetings_bottom_content' );
			$ht_ctc_greetings['call_to_action'] = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['call_to_action'], 'Click to Chat for WhatsApp', 'greetings_call_to_action' );
			$ht_ctc_greetings['opt_in']         = apply_filters( 'wpml_translate_single_string', $ht_ctc_greetings['opt_in'], 'Click to Chat for WhatsApp', 'greetings_opt_in' );

			// greetings dialog window type - next (default behaviour) or modal. next: next to button to open dialog, modal: open dialog in modal style
			$g_position = ( isset( $greetings_settings['g_position'] ) ) ? esc_attr( $greetings_settings['g_position'] ) : 'next';

			// greetings dialog size. s: small, m: mid, l: large
			$g_size = ( isset( $greetings_settings['g_size'] ) ) ? esc_attr( $greetings_settings['g_size'] ) : 's';

			$ht_ctc_greetings = apply_filters( 'ht_ctc_fh_greetings_start', $ht_ctc_greetings );

			$page_id = get_the_ID();

			// $page_id = get_queried_object_id();
			// if ( 0 === $page_id || '' === $page_id ) {
			// $page_id = get_the_ID();
			// }

			$page_url   = get_permalink();
			$post_title = esc_html( get_the_title() );

			if ( is_home() || is_front_page() ) {
				// is home page
				$page_url = home_url( '/' );
				// if home page is a loop then return site name.. (instead of getting the last post title in that loop)
				$post_title = HT_CTC_BLOG_NAME;

				// if home page is a page then return page title.. (if not {site} and {title} will be same )
				if ( is_page() ) {
					$post_title = esc_html( get_the_title() );
				}
			} elseif ( is_singular() ) {
				// is singular
				$page_url   = get_permalink();
				$post_title = esc_html( get_the_title() );
			} elseif ( is_archive() ) {

				if ( isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) ) {
					$protocol    = ( isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ) ? 'https' : 'http';
					$http_host   = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
					$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
					$page_url    = $protocol . '://' . $http_host . $request_uri;
				}

				if ( is_category() ) {
					$post_title = single_cat_title( '', false );
				} elseif ( is_tag() ) {
					$post_title = single_tag_title( '', false );
				} elseif ( is_author() ) {
					$post_title = get_the_author();
				} elseif ( is_post_type_archive() ) {
					$post_title = post_type_archive_title( '', false );
				} elseif ( function_exists( 'is_tax' ) && function_exists( 'single_term_title' ) && is_tax() ) {
					$post_title = single_term_title( '', false );
				} elseif ( function_exists( 'get_the_archive_title' ) ) {
						$post_title = get_the_archive_title();
				}
			}

			// is shop page
			if ( class_exists( 'WooCommerce' ) && function_exists( 'is_shop' ) && function_exists( 'wc_get_page_id' ) && is_shop() ) {
				$page_id    = wc_get_page_id( 'shop' );
				$post_title = esc_html( get_the_title( $page_id ) );
			}

			$allowed_html = wp_kses_allowed_html( 'post' );

			// $allowed_html['iframe'] = array(
			// 'src'             => true,
			// 'height'          => true,
			// 'width'           => true,
			// 'frameborder'     => true,
			// 'allowfullscreen' => true,
			// 'title' => true,
			// 'allow' => true,
			// 'autoplay' => true,
			// 'clipboard-write' => true,
			// 'encrypted-media' => true,
			// 'gyroscope' => true,
			// 'picture-in-picture' => true,
			// );

			// greetings dialog position based on chat icon/button position
			$g_position_r_l   = ( isset( $chat['side_2'] ) ) ? esc_attr( $chat['side_2'] ) : 'right';
			$g_m_position_r_l = ( isset( $chat['mobile_side_2'] ) ) ? esc_attr( $chat['mobile_side_2'] ) : 'right';

			$g_position_t_b = ( isset( $chat['side_1'] ) ) ? esc_attr( $chat['side_1'] ) : 'bottom';
			// $g_m_position_t_b = ( isset( $chat['mobile_side_1']) ) ? esc_attr( $chat['mobile_side_1'] ) : 'bottom';

			// is rtl page
			$rtl_page = '';
			if ( function_exists( 'is_rtl' ) && is_rtl() ) {
				$rtl_page = 'yes';
			}

			// close button position
			$g_close_button_position = ( 'yes' === $rtl_page ) ? 'left' : 'right';

			$g_close_button_styles = "position:absolute; top:0; $g_close_button_position:0; cursor:pointer; padding:5px; margin:4px; border-radius:50%; background-color: unset !important; z-index: 9999; line-height: 1;";

			include_once HT_CTC_PLUGIN_DIR . 'new/admin/db/defaults/class-ht-ctc-defaults-greetings.php';

			$greetings_fallback_values  = array();
			$g1_fallback_values         = array();
			$g2_fallback_values         = array();
			$g_settings_fallback_values = array();

			// check if class exists
			if ( class_exists( 'HT_CTC_Defaults_Greetings' ) ) {

				$default_greetings = new HT_CTC_Defaults_Greetings();

				// if greetings function exist in class
				// if ( method_exists( $default_greetings, 'greetings' ) ) {
				// $greetings_fallback_values = $default_greetings->greetings();
				// }

				$greetings_fallback_values  = $default_greetings->greetings;
				$g1_fallback_values         = $default_greetings->g_1;
				$g2_fallback_values         = $default_greetings->g_2;
				$g_settings_fallback_values = $default_greetings->g_settings;

			}

			/**
			 * If desktop and mobile have different settings and different position (left/right)
			 * If greetings dialog is set to 'next' position and if greetings dialog size is 'small' (for 'mid' and 'large' sizes, position adjustments are not required)
			 * Then add a class to adjust the greetings position specifically for mobile devices.
			 * By default, greetings are positioned for desktop; this additional class fine-tunes it for mobile.
			 * && 's' === $g_size
			 */
			if ( ! isset( $chat['same_settings'] ) && $g_position_r_l !== $g_m_position_r_l && 'modal' !== $g_position ) {
				// $g_box_classes .= ('left' === $g_position_r_l) ? ' ctc_d_p_left ' : ' ctc_d_p_right ';
				$g_box_classes .= ( 'left' === $g_m_position_r_l ) ? ' ctc_m_p_left ' : ' ctc_m_p_right ';
			}

			// path to greetings template
			$ht_ctc_greetings['path'] = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/' . $ht_ctc_greetings['greetings_template'] . '.php';

			// filter hook to update values...
			$ht_ctc_greetings = apply_filters( 'ht_ctc_fh_greetings', $ht_ctc_greetings );

			// return if template not set..
			if ( '' === $ht_ctc_greetings['greetings_template'] || 'no' === $ht_ctc_greetings['greetings_template'] ) {
				return;
			}

			if ( '' !== $ht_ctc_greetings['header_content'] ) {
				$ht_ctc_greetings['header_content'] = html_entity_decode( wp_kses( $ht_ctc_greetings['header_content'], $allowed_html ) );
				$ht_ctc_greetings['header_content'] = str_replace( array( '{url}', '{title}', '{site}' ), array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['header_content'] );
			}
			if ( '' !== $ht_ctc_greetings['main_content'] ) {
				$ht_ctc_greetings['main_content'] = html_entity_decode( wp_kses( $ht_ctc_greetings['main_content'], $allowed_html ) );
				$ht_ctc_greetings['main_content'] = str_replace( array( '{url}', '{title}', '{site}' ), array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['main_content'] );
			}
			if ( '' !== $ht_ctc_greetings['bottom_content'] ) {
				$ht_ctc_greetings['bottom_content'] = html_entity_decode( wp_kses( $ht_ctc_greetings['bottom_content'], $allowed_html ) );
				$ht_ctc_greetings['bottom_content'] = str_replace( array( '{url}', '{title}', '{site}' ), array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['bottom_content'] );
			}
			if ( '' !== $ht_ctc_greetings['is_opt_in'] && '' !== $ht_ctc_greetings['opt_in'] ) {
				$ht_ctc_greetings['opt_in'] = html_entity_decode( wp_kses( $ht_ctc_greetings['opt_in'], $allowed_html ) );
				$ht_ctc_greetings['opt_in'] = str_replace( array( '{url}', '{title}', '{site}' ), array( $page_url, $post_title, HT_CTC_BLOG_NAME ), $ht_ctc_greetings['opt_in'] );
			}

			$box_shadow = '0px 1px 9px 0px rgba(0,0,0,.14)';
			if ( 'greetings-2' === $ht_ctc_greetings['greetings_template'] ) {
				$box_shadow = '0px 0px 5px 1px rgba(0,0,0,.14)';
			}

			// ctc_m_full_width: class to make mobile full width for medium and large
			$min_width        = '300px';
			$ctc_m_full_width = '';

			if ( 's' === $g_size ) {
				// Small size - use default values.
				$min_width = '300px';
			} elseif ( 'm' === $g_size ) {
				$min_width        = '330px';
				$ctc_m_full_width = 'ctc_m_full_width';
			} elseif ( 'l' === $g_size ) {
				$min_width        = '360px';
				$ctc_m_full_width = 'ctc_m_full_width';
			}

			$box_layout_bg_color = '';
			if ( 'greetings-1' === $ht_ctc_greetings['greetings_template'] || 'greetings-2' === $ht_ctc_greetings['greetings_template'] ) {
				// Use template default background.
				$box_layout_bg_color = '';
			} else {
				$box_layout_bg_color = 'background-color: #ffffff;';
			}

			// if modal then add class ctc_g_modal, if next then add ctc_g_next to greetings dialog
			$ctc_g_position              = '';
			$greetings_box_styles        = '';
			$greetings_box_layout_styles = '';

			if ( 'modal' === $g_position ) {
				$ctc_g_position = 'ctc_greetings_modal';
				// different styles for modal dialog. unset things.
				$ctc_m_full_width            = '';
				$greetings_box_styles        = 'position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); max-height: 90vh;';
				$greetings_box_layout_styles = "overflow-y:auto; $box_layout_bg_color ";
				$box_shadow                  = '0 8px 32px rgba(0, 0, 0, 0.3)';
			} else {
				// next: as now else is consider as next only. instead of adding elseif ( 'next' === $g_position )
				$ctc_g_position              = 'ctc_greetings_next';
				$greetings_box_styles        = "position: absolute; $g_position_r_l: 0px;";
				$greetings_box_layout_styles = "max-height: 84vh; overflow-y:auto; $box_layout_bg_color ";

				// if base widget from top position display greeting below to the base widget. desktop is enough, in mobile it is full width and no need to set.
				// (if g_size is small still greeting dialog above the base widget - if desktop base widget is at bottom and mobile base widget is at top)
				if ( 'top' === $g_position_t_b ) {
					// margin-top: 60px; to display below the button. 18px + chat base icon approx + some space
					$greetings_box_styles .= 'top: 100%; bottom: auto; margin-top: 70px;';
				} else {
					$greetings_box_styles .= 'bottom: 0px;';
				}
			}

			/**
			 *
			 * For inside close button - to the close button, ctc_greetings_close_btn added background-color: unset !important; border-radius:50%; for backword compatibility i.e. user changed by adding css..
			 * z-index: 9 added to fix style-5 icon mobile layer with greetings dialog
			 */
			if ( is_file( $ht_ctc_greetings['path'] ) ) {

				$template       = $ht_ctc_greetings['greetings_template'];
				$g_box_classes .= " template-$template";

				// styles specific to rtl pages..
				if ( 'yes' === $rtl_page ) {
					?>
				<style id="ht_ctc_rtl">.g_header_badge_online {left: 0;right: unset;}</style>
					<?php
				}
				?>
			<div style="position: relative; bottom: 18px; cursor: auto; z-index:9;" class="ht_ctc_greetings <?php echo esc_attr( $ctc_m_full_width ); ?>">

				<div class="ht_ctc_chat_greetings_box <?php echo esc_attr( $g_box_classes ); ?>  <?php echo esc_attr( $ctc_g_position ); ?>" style="display: none; <?php echo esc_attr( $greetings_box_styles ); ?> min-width: <?php echo esc_attr( $min_width ); ?>; max-width: 420px; ">

					<div class="ht_ctc_chat_greetings_box_layout" style="<?php echo esc_attr( $greetings_box_layout_styles ); ?>  box-shadow: <?php echo esc_attr( $box_shadow ); ?>; border-radius:8px;">

						<span style="<?php echo esc_attr( $g_close_button_styles ); ?>" class="ctc_greetings_close_btn">
							<svg style="color:lightgray; background-color: unset !important; border-radius:50%;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
								<path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
							</svg>
						</span>

						<div class="ctc_greetings_template">
							<?php include $ht_ctc_greetings['path']; ?>
						</div>
					</div>
				</div>
			</div>
				<?php
			}
		}
	}


	new HT_CTC_Chat_Greetings();

} // END class_exists check
