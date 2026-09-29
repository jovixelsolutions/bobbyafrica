<?php
/**
 *
 * Admin demo - main page
 *
 * _ad_ - admin demo
 * _mp_ - main page
 * _os_ - other settings
 *
 * @since 3.30
 *  - s1. front end looks like theme button
 *  - for some styles the default view may need to change. like hover effects, ..
 *
 * class names added to settings pages for demo purpose:
 * .ctc_no_demo - to display no demo notification
 * .ctc_demo_style
 * .ctc_ad_main_page_on_change_style
 * .ctc_ad_main_page_on_change_input
 * .ctc_ad_main_page_on_change_input_update_var
 * .ctc_demo_position - positions: bottom_top, right_left, side_1_value, side_2_value
 * .ctc_an_demo_btn
 * .ctc_ee_demo_btn
 * .ctc_demo_style
 * .ctc_oninput
 *      attributes - data-update-type
 *                 - data-update-type-2
 *                 - data-update-selector
 *
 *
 * class names at demo:
 * ctc_demo_style ctc_demo_style_${style}
 * ctc_demo_load
 *
 *
 * direct class names used for demo:
 * .ht-ctc-admin-sidebar .collapsible
 * @package Click_To_Chat
 * @subpackage Administration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Demo' ) ) {

	/**
	 * Admin demo functionality for live preview in admin pages.
	 */
	class HT_CTC_Admin_Demo {

		/**
		 * Current admin page being viewed.
		 *
		 * @var string
		 */
		public $get_page = '';

		/**
		 * Whether to load demo functionality.
		 *
		 * @var string
		 */
		public $load_demo = 'yes';

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Register admin demo hooks.
		 *
		 * @return void
		 */
		public function hooks() {

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only checking which admin page is being loaded.
			if ( isset( $_GET ) && isset( $_GET['page'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$this->get_page = sanitize_text_field( wp_unslash( $_GET['page'] ) );
			} else {
				return;
			}

			/**
			 * Check if admin demo is active.
			 * Return if not active.
			 *
			 * To deactivate from user side:
			 *  -> if _GET has "&demo=deactive"
			 *  set ht_ctc_admin_demo_active to yes
			 *
			 * To activate from user side:
			 *  -> if _GET has "&demo=active"
			 *  set ht_ctc_admin_demo_active to no
			 */

			// 'click-to-chat-greetings' === $this->get_page  add this when admin demo is added to greetings page
			if ( 'click-to-chat' === $this->get_page || 'click-to-chat-other-settings' === $this->get_page || 'click-to-chat-customize-styles' === $this->get_page ) {

				// check if admin demo is active.. (added inside to run only in ctc admin pages..)
				$demo_active = get_option( 'ht_ctc_admin_demo_active' );

				// check if demo is activating or deactivating..
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['demo'] ) ) {

					// $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
					// if ( ! wp_verify_nonce( $nonce, 'ht_ctc_admin_demo' ) ) {
					// return;
					// }

					// phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$demo_action = sanitize_text_field( wp_unslash( $_GET['demo'] ) );
					if ( 'active' === $demo_action ) {
						$this->load_demo = 'yes';
						// add option to db
						update_option( 'ht_ctc_admin_demo_active', 'yes' );
					} elseif ( 'deactive' === $demo_action ) {
						$this->load_demo = 'no';
						// add option to db
						update_option( 'ht_ctc_admin_demo_active', 'no' );
					}
				} elseif ( 'no' === $demo_active ) {
					// not activating or deactivating.. check if admin demo already deactived...
					$this->load_demo = 'no';
				}

				// return if load_demo is no
				if ( 'no' === $this->load_demo ) {
					return;
				}

				// #### below this only run if admin demo is active.. (i.e. user activated demo from user side and only in click to chat admin pages..)

				// ht_ctc_ah_admin_demo action hook.
				do_action( 'ht_ctc_ah_admin_demo' );

				// load styles (widgets)
				add_action( 'admin_footer', array( $this, 'load_styles' ) );

				// enqueue scripts
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

				// enqueue styles at bottom of the page
				add_action( 'admin_footer', array( $this, 'load_css_bottom' ) );

				// // other settings page
				// if ( 'click-to-chat-other-settings' === $this->get_page ) {
				// }

				// // customize styles page
				// if ( 'click-to-chat-customize-styles' === $this->get_page ) {
				// }

			}
		}

		/**
		 * Enqueue admin demo scripts.
		 *
		 * @return void
		 */
		public function enqueue_scripts() {

			$os = get_option( 'ht_ctc_othersettings' );

			$js = 'admin-demo.js';

			if ( defined( 'HT_CTC_DEBUG_MODE' ) ) {
				$js = 'dev/admin-demo.dev.js';
			}

			$args = true;

			$wp_ver = function_exists( 'get_bloginfo' ) ? get_bloginfo( 'version' ) : '1.0';

			// if wp version is not null and is greater than 6.3
			if ( version_compare( $wp_ver, '6.3', '>=' ) ) {
				$args = array(
					'in_footer' => true,
					'strategy'  => 'defer',
				);
			}

			wp_enqueue_script( 'ht-ctc-admin-demo-js', plugins_url( "new/admin/admin_demo/$js", HT_CTC_PLUGIN_FILE ), array( 'jquery' ), HT_CTC_VERSION, $args );

			$this->admin_demo_var();
		}

		/**
		 * Load CSS at bottom of admin pages.
		 *
		 * @return void
		 */
		public function load_css_bottom() {

			$os = get_option( 'ht_ctc_othersettings' );

			$css           = 'admin-demo.css';
			$animation_css = 'admin-demo-animations.css';

			if ( defined( 'HT_CTC_DEBUG_MODE' ) ) {
				$css           = 'dev/admin-demo.dev.css';
				$animation_css = 'dev/admin-demo-animations.dev.css';
			}

			wp_enqueue_style( 'ht-ctc-admin-demo-css', plugins_url( "new/admin/admin_demo/$css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );

			// other settings page
			if ( 'click-to-chat-other-settings' === $this->get_page ) {
				wp_enqueue_style( 'ht-ctc-admin-demo-animations-css', plugins_url( "new/admin/admin_demo/$animation_css", HT_CTC_PLUGIN_FILE ), '', HT_CTC_VERSION );
			}
		}

		/**
		 * Localize admin demo variables for JavaScript.
		 *
		 * @return void
		 */
		public function admin_demo_var() {

			$options = get_option( 'ht_ctc_chat_options' );

			$number = isset( $options['number'] ) ? esc_attr( $options['number'] ) : '';

			if ( class_exists( 'HT_CTC_Formatting' ) && method_exists( 'HT_CTC_Formatting', 'wa_number' ) ) {
				$number = HT_CTC_Formatting::wa_number( $number );
			}

			$pre_filled = isset( $options['pre_filled'] ) ? esc_attr( $options['pre_filled'] ) : '';

			$url_target_d = isset( $options['url_target_d'] ) ? esc_attr( $options['url_target_d'] ) : '_blank';

			$url_structure_m = isset( $options['url_structure_m'] ) ? esc_attr( $options['url_structure_m'] ) : '';
			$url_structure_d = isset( $options['url_structure_d'] ) ? esc_attr( $options['url_structure_d'] ) : '';

			$custom_url_d = isset( $options['custom_url_d'] ) ? esc_attr( $options['custom_url_d'] ) : '';
			$custom_url_m = isset( $options['custom_url_m'] ) ? esc_attr( $options['custom_url_m'] ) : '';

			$site = HT_CTC_BLOG_NAME;

			$m1 = __( 'No Demo for click: WhatsApp Number is empty', 'click-to-chat-for-whatsapp' );
			$m2 = __( 'No Demo for click: URL Target: same tab', 'click-to-chat-for-whatsapp' );

			$demo_var = array(
				'number'          => $number,
				'pre_filled'      => $pre_filled,
				'url_target_d'    => $url_target_d,
				'url_structure_m' => $url_structure_m,
				'url_structure_d' => $url_structure_d,
				'custom_url_d'    => $custom_url_d,
				'custom_url_m'    => $custom_url_m,
				'site'            => $site,
				'm1'              => $m1,
				'm2'              => $m2,
			);

			wp_localize_script( 'ht-ctc-admin-demo-js', 'ht_ctc_admin_demo_var', $demo_var );
		}


		/**
		 * Load styles.
		 *
		 * Main page: load all styles.
		 * Other settings: load only desktop selected style.
		 */
		public function load_styles() {

			$options       = get_option( 'ht_ctc_chat_options' );
			$othersettings = get_option( 'ht_ctc_othersettings' );

			$styles = array(
				'1',
				'2',
				'3',
				'3_1',
				'4',
				'5',
				'6',
				'7',
				'7_1',
				'8',
				'99',
			);

			// ctc, ctc customize styles - load all styles. And in ctc other settings load only desktop selected style.
			if ( 'click-to-chat-other-settings' === $this->get_page || 'click-to-chat-greetings' === $this->get_page ) {
				$style_desktop = ( isset( $options['style_desktop'] ) ) ? esc_attr( $options['style_desktop'] ) : '4';
				$styles        = array(
					$style_desktop,
				);
			}

			// in styles
			$call_to_action = isset( $options['call_to_action'] ) ? esc_attr( $options['call_to_action'] ) : '';
			if ( '' === $call_to_action ) {
				$call_to_action = 'WhatsApp us';
			}

			$type      = 'chat';
			$is_mobile = '';
			$side_2    = 'right';

			/*
			 * .ctc_demo_load_styles parent..
			 *      greetings..
			 *      styles..
			 */
			?>
		<div class="ctc_demo_load" style="position:fixed; bottom:50px; right:50px; z-index:9999;">
			<?php
			// // greetings (to load all greetings)
			// include_once HT_CTC_PLUGIN_DIR .'new/tools/demo/demo-greetings.php';

			$notification_count = ( isset( $othersettings['notification_count'] ) ) ? esc_attr( $othersettings['notification_count'] ) : '1';
			$cs_link            = admin_url( 'admin.php?page=click-to-chat-customize-styles' );
			$os_link            = admin_url( 'admin.php?page=click-to-chat-other-settings' );

			// load all styles
			foreach ( $styles as $style ) {
				$class = "ctc_demo_style ctc_demo_style_$style ht_ctc_animation ht_ctc_entry_animation";
				?>
			<div class="<?php echo esc_attr( $class ); ?>" style="display: none; cursor: pointer;">
				<?php
				// no duplicate as in greetings, other settings page as only one style is loaded
				// if ( 'click-to-chat-greetings' === $this->get_page ) {
				// greetings dialog
				// $this->add_greetings();
				// }
				?>
			<div class="ht_ctc_style ht_ctc_chat_style">
				<?php
				if ( 'click-to-chat-other-settings' === $this->get_page ) {
					?>
					<span class="ctc_ad_notification" style="display:none; padding:0px; margin:0px; position:relative; float:right; z-index:9999999;">
						<span class="ctc_ad_badge" style="position: absolute; top: -11px; right: -11px; font-size:12px; font-weight:600; height:22px; width:22px; box-sizing:border-box; border-radius:50%;border:2px solid #ffffff; background:#ff4c4c; color:#ffffff; display:flex; justify-content:center; align-items:center;"><?php echo esc_html( $notification_count ); ?></span>
					</span>
					<?php
				}
				// no need to santize_file_name. its not user input
				$style = sanitize_file_name( $style );
				$path  = plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/styles/style-' . $style . '.php';
				include $path;
				?>
			</div>
			</div>
				<?php
			}
			?>
		</div>

			<?php
			/**
			 * Ctc_menu_at_demo
			 *  .ctc_ad_links - displays customize styles and other settings links
			 *  .ctc_ad_page_link - other settings pages links
			 *  .ctc_no_demo_notice - displays no demo notice for some features - e..g. customize styles . s1 add icon, ...
			 *  .ctc_demo_messages - displays demo messages - e.g. for no demo for click, same tab., ..
			 */
			?>
		<div class="ctc_menu_at_demo" style="position:fixed; bottom:4px; right:4px; z-index:99999999;">

			<p class="description ctc_ad_links ctc_init_display_none">
				<span class="ctc_ad_page_link"><a target="_blank" href="<?php echo esc_url( $cs_link ); ?>">Customize Styles</a> |</span>
				<span class="ctc_ad_page_link"><a target="_blank" href="<?php echo esc_url( $os_link ); ?>">Animations, Notification badge</a> |</span>

				<span class="ctc_ad_show_hide_demo ctc_ad_show_demo ctc_init_display_none"><a target="_blank"><?php esc_html_e( 'Show Demo', 'click-to-chat-for-whatsapp' ); ?></a></span>
				<span class="ctc_ad_show_hide_demo ctc_ad_hide_demo"><a target="_blank"><?php esc_html_e( 'Hide Demo', 'click-to-chat-for-whatsapp' ); ?></a></span>
			</p>
			<a href="https://holithemes.com/plugins/click-to-chat/admin-live-preview-messages/#no-live-preview/" target="_blank" class="description ctc_no_demo_notice ctc_init_display_none">No live demo for this feature</a>
			<a href="https://holithemes.com/plugins/click-to-chat/admin-live-preview-messages/" target="_blank" class="description ctc_demo_messages ctc_init_display_none"></a>
		</div>
			<?php
		}


		/**
		 * Add greetings demo functionality.
		 *
		 * @return void
		 */
		public function add_greetings() {

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

			// ht_ctc_greetings_options
			$greetings = get_option( 'ht_ctc_greetings_options', $greetings_fallback_values );
			// ht_ctc_greetings_settings
			$greetings_settings = get_option( 'ht_ctc_greetings_settings', $g_settings_fallback_values );

			$ht_ctc_greetings                   = array();
			$demo_page                          = 'yes';
			$ht_ctc_greetings['header_content'] = ( isset( $greetings['header_content'] ) ) ? esc_attr( $greetings['header_content'] ) : '';
			$ht_ctc_greetings['main_content']   = ( isset( $greetings['main_content'] ) ) ? esc_attr( $greetings['main_content'] ) : '';
			$ht_ctc_greetings['bottom_content'] = ( isset( $greetings['bottom_content'] ) ) ? esc_attr( $greetings['bottom_content'] ) : '';
			$ht_ctc_greetings['call_to_action'] = ( isset( $greetings['call_to_action'] ) ) ? esc_attr( $greetings['call_to_action'] ) : '';

			$ht_ctc_greetings['is_opt_in'] = ( isset( $greetings_settings['is_opt_in'] ) ) ? esc_attr( $greetings_settings['is_opt_in'] ) : '';
			$ht_ctc_greetings['opt_in']    = ( isset( $greetings_settings['opt_in'] ) ) ? esc_attr( $greetings_settings['opt_in'] ) : '';

			if ( '' === $ht_ctc_greetings['call_to_action'] ) {
				$ht_ctc_greetings['call_to_action'] = 'WhatsApp';
			}

			$g_templates = array(
				'greetings-1' => plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/greetings-1.php',
				'greetings-2' => plugin_dir_path( HT_CTC_PLUGIN_FILE ) . 'new/inc/greetings/greetings-2.php',
			);

			// add hook to add more greetings templates
			$g_templates = apply_filters( 'ht_ctc_fh_admin_demo_greetings_templates', $g_templates );

			$g_size = ( isset( $greetings_settings['g_size'] ) ) ? esc_attr( $greetings_settings['g_size'] ) : 's';

			$min_width        = '300px';
			$ctc_m_full_width = '';

			// If g_size is small no additional styling needed.
			if ( 'm' === $g_size ) {
				$min_width        = '330px';
				$ctc_m_full_width = 'ctc_m_full_width';
			} elseif ( 'l' === $g_size ) {
				$min_width        = '360px';
				$ctc_m_full_width = 'ctc_m_full_width';
			}

			// is rtl page
			$rtl_page = '';
			if ( function_exists( 'is_rtl' ) && is_rtl() ) {
				$rtl_page = 'yes';
			}

			$box_layout_bg_color = '';
			// if ( 'greetings-1' === $ht_ctc_greetings['greetings_template'] || 'greetings-2' === $ht_ctc_greetings['greetings_template'] ) {
			// } else {
			// $box_layout_bg_color = 'background-color: #ffffff;';
			// }

			$g_box_classes        = '';
			$g_box_class_template = '';

			// change positon at greetings page is not available.
			$g_position_r_l = 'right';

			$box_shadow = '0px 1px 9px 0px rgba(0,0,0,.14)';
			// if ( 'greetings-2' === $ht_ctc_greetings['greetings_template'] ) {
			// $box_shadow = '0px 0px 5px 1px rgba(0,0,0,.14)';
			// }

			$g_close_button_position = ( 'yes' === $rtl_page ) ? 'left' : 'right';
			$g_close_button_styles   = "position:absolute; top:0; $g_close_button_position:0; cursor:pointer; padding:5px; margin:4px; border-radius:50%; background-color: unset !important; z-index: 9999; line-height: 1;";

			foreach ( $g_templates as $template => $path ) {
				if ( is_file( $path ) ) {

					$g_box_class_template = " template-$template";

					?>
				<div class="ctc_demo_greetings <?php echo esc_attr( 'ctc_demo_greetings_' . $template ); ?> <?php echo esc_attr( $ctc_m_full_width ); ?>" style="position: relative; bottom: 18px; cursor: auto;" >

					<div class="ht_ctc_chat_greetings_box <?php echo esc_attr( $g_box_classes ); ?> <?php echo esc_attr( $g_box_class_template ); ?>" style="position: absolute; bottom: 0px; <?php echo esc_attr( $g_position_r_l ); ?>: 0px; min-width: <?php echo esc_attr( $min_width ); ?>; max-width: 420px; ">

						<div class="ht_ctc_chat_greetings_box_layout" style="max-height: 84vh; overflow-y:auto; <?php echo esc_attr( $box_layout_bg_color ); ?> box-shadow: <?php echo esc_attr( $box_shadow ); ?>; border-radius:8px;clear:both;">

							<span style="<?php echo esc_attr( $g_close_button_styles ); ?>" class="ctc_greetings_close_btn">
								<svg style="color:lightgray; background-color: unset !important; border-radius:50%;" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
									<path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
								</svg>
							</span>

							<div class="ctc_greetings_template">
								<?php include $path; ?>
							</div>
						</div>
					</div>
				</div>
					<?php
				}
			}
		}
	}

	new HT_CTC_Admin_Demo();

} // END class_exists check
