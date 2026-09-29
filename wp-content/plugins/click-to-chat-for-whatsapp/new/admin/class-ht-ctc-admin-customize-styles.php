<?php
/**
 * Customize Styles  ( cs )
 *
 * @package Admin
 * @since 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Customize_Styles' ) ) {

	/**
	 * Admin Customize Styles page.
	 *
	 * Handles the settings UI for style variants.
	 *
	 * @since 2.0
	 */
	class HT_CTC_Admin_Customize_Styles {

		/**
		 * Controls visibility of the "Display all Styles" checkbox.
		 *
		 * @var string 'show' or 'hide'
		 */
		public $display_all_styles_checkbox = 'show';



		/**
		 * Register the Customize submenu page.
		 *
		 * @return void
		 */
		public function menu() {

			add_submenu_page(
				'click-to-chat',
				'Customize',
				'Customize',
				'manage_options',
				'click-to-chat-customize-styles',
				array( $this, 'settings_page' )
			);
		}

		/**
		 * Render the settings page markup.
		 *
		 * @return void
		 */
		public function settings_page() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			?>

		<div class="wrap ctc-admin-customize-styles">

			<?php settings_errors(); ?>

			<div class="row" style="display:flex; flex-wrap:wrap;">
				<div class="col s12 m12 xl8 options">
					<form action="options.php" method="post" class="">
						<?php settings_fields( 'ht_ctc_cs_page_settings_fields' ); ?>
						<?php do_settings_sections( 'ht_ctc_cs_page_settings_sections_do' ); ?>
						<?php submit_button(); ?>
					</form>
				</div>
				<div class="col s12 m12 xl4 ht-ctc-admin-sidebar">
				</div>
			</div>

		</div>

			<?php
		}



		/**
		 * Register settings, sections, and fields for Customize Styles.
		 *
		 * @return void
		 */
		public function settings() {

			$ht_ctc_othersettings = get_option( 'ht_ctc_othersettings' );
			$ht_ctc_chat          = get_option( 'ht_ctc_chat_options' );
			$ht_ctc_cs            = get_option( 'ht_ctc_cs_options' );

			// @uses for register_setting, add_settings_field
			$styles_list = array(
				'ht_ctc_s1',
				'ht_ctc_s2',
				'ht_ctc_s3',
				'ht_ctc_s3_1',
				'ht_ctc_s4',
				'ht_ctc_s5',
				'ht_ctc_s6',
				'ht_ctc_s7',
				'ht_ctc_s7_1',
				'ht_ctc_s8',
				'ht_ctc_s99',
			);

			// Display all - if group or share enabled or display_allstyles option is checked.
			if ( isset( $ht_ctc_othersettings['enable_group'] ) || isset( $ht_ctc_othersettings['enable_share'] ) ) {
				// load all styles
				$this->display_all_styles_checkbox = 'hide';

			} elseif ( ! isset( $ht_ctc_cs['display_allstyles'] ) ) {
				// only chat enabled.

				$style_d = ( isset( $ht_ctc_chat['style_desktop'] ) ) ? esc_attr( $ht_ctc_chat['style_desktop'] ) : '';
				$style_m = ( isset( $ht_ctc_chat['style_mobile'] ) ) ? esc_attr( $ht_ctc_chat['style_mobile'] ) : '';

				// $styles_list redefined..
				$styles_list = array();

				if ( '' !== $style_d ) {
					array_push( $styles_list, "ht_ctc_s$style_d" );
				}

				if ( ! isset( $ht_ctc_chat['same_settings'] ) && '' !== $style_m && $style_d !== $style_m ) {
					array_push( $styles_list, "ht_ctc_s$style_m" );
				}

				// // woo style and if not match with style desktop, mobile.
				// $woo = get_option('ht_ctc_woo_options');
				// $woo_style = (isset($woo['woo_style'])) ? esc_attr($woo['woo_style']) : '';
				// if ( '' !== $woo_style && $style_d !== $woo_style && $style_m !== $woo_style ) {
				// array_push($styles_list, "ht_ctc_s$woo_style");
				// }

			}

			// register_setting
			foreach ( $styles_list as $s ) {

				register_setting(
					'ht_ctc_cs_page_settings_fields',
					$s,
					array( $this, 'options_sanitize' )
				);

			}

			register_setting( 'ht_ctc_cs_page_settings_fields', 'ht_ctc_cs_options', array( $this, 'options_sanitize' ) );

			// check for options.php, _GET page = click-to-chat-customize-styles
			$get_url = false;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking current admin page only.
			if ( ( isset( $_GET ) && isset( $_GET['page'] ) ) && 'click-to-chat-customize-styles' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
				$get_url = true;
			}

			$options_page = false;
			// if request url have options.php .. (or if requesturl is not set.. or empty ) then $options_page = true
			if ( isset( $_SERVER['REQUEST_URI'] ) && ! empty( $_SERVER['REQUEST_URI'] ) ) {
				$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
				if ( false !== strpos( $request_uri, 'options.php' ) ) {
					// if options.php page
					$options_page = true;
				}
			} else {
				$options_page = true;
			}

			// if its - options.php page or _GET page = click-to-chat-customize-styles - load settings fields.. (or if request url is not set or empty - no risk)
			if ( true === $options_page || true === $get_url ) {

				add_settings_section( 'ht_ctc_cs_settings_sections_add', '', array( $this, 'main_settings_section_cb' ), 'ht_ctc_cs_page_settings_sections_do' );

				// add_settings_field
				foreach ( $styles_list as $s ) {

					$name = str_replace( 'ht_ctc_s', 'Style ', $s );

					add_settings_field(
						$s,
						$name,
						array( $this, "{$s}_cb" ),
						'ht_ctc_cs_page_settings_sections_do',
						'ht_ctc_cs_settings_sections_add'
					);

				}

				add_settings_field( 'ht_ctc_cs', '', array( $this, 'ht_ctc_cs_cb' ), 'ht_ctc_cs_page_settings_sections_do', 'ht_ctc_cs_settings_sections_add' );

			}
		}

		/**
		 * Output main settings section wrapper.
		 *
		 * @return void
		 */
		public function main_settings_section_cb() {
			?>
		<h1>Customize</h1>
		<!-- styles -->
		<style id="ht-ctc-admin-cs">
			.ht_ctc_display_none {
				display: none;
			}
		</style>
			<?php
		}

		// display all styles
		// count - updates each time, uses at clear cache
		/**
		 * Render the "Display all Styles" checkbox and hidden fields.
		 *
		 * @return void
		 */
		public function ht_ctc_cs_cb() {

			$options = get_option( 'ht_ctc_cs_options' );
			$dbrow   = 'ht_ctc_cs_options';

			// increase count to update row each time when save changes, to use at clear cache..
			$count = ( isset( $options['count'] ) ) ? esc_attr( $options['count'] ) : '1';
			++$count;

			$display_allstyles = ( isset( $options['display_allstyles'] ) ) ? esc_attr( $options['display_allstyles'] ) : '';

			$hide_checkbox = '';
			if ( isset( $this->display_all_styles_checkbox ) && 'hide' === $this->display_all_styles_checkbox ) {
				$hide_checkbox = 'ctc_init_display_none';
			}

			?>
		<!-- not make empty table -->
		<input name="<?php echo esc_attr( $dbrow ); ?>[count]" value="<?php echo esc_attr( $count ); ?>" type="hidden" class="hide" >

		<!-- display all styles -->
		<div class="display_all_styles_checkbox <?php echo esc_attr( $hide_checkbox ); ?>">
			<p>
				<label class="ctc_checkbox_label">
					<input name="<?php echo esc_attr( $dbrow ); ?>[display_allstyles]" type="checkbox" value="1" <?php checked( $display_allstyles, 1 ); ?> id="display_allstyles" />
					<span><?php esc_html_e( 'Display all Styles', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
				<p class="display_allstyles_description description" style="display: none;">&emsp;&emsp;Save Changes</p>
			</p> 
		</div>
			<?php
		}


		/**
		 * Style 1: Default theme button.
		 */
		public function ht_ctc_s1_cb() {

			$options = get_option( 'ht_ctc_s1' );
			$dbrow   = 'ht_ctc_s1';

			$s1_text_color = ( isset( $options['s1_text_color'] ) ) ? esc_attr( $options['s1_text_color'] ) : '';
			$s1_bg_color   = ( isset( $options['s1_bg_color'] ) ) ? esc_attr( $options['s1_bg_color'] ) : '';
			$s1_icon_color = ( isset( $options['s1_icon_color'] ) ) ? esc_attr( $options['s1_icon_color'] ) : '';
			$s1_icon_size  = ( isset( $options['s1_icon_size'] ) ) ? esc_attr( $options['s1_icon_size'] ) : '16';

			$s1_m_fullwidth_checkbox = ( isset( $options['s1_m_fullwidth'] ) ) ? esc_attr( $options['s1_m_fullwidth'] ) : '';
			$s1_add_icon_checkbox    = ( isset( $options['s1_add_icon'] ) ) ? esc_attr( $options['s1_add_icon'] ) : '';

			$s1_m_fullwidth_css = '';
			if ( '1' !== $s1_m_fullwidth_checkbox ) {
				$s1_m_fullwidth_css = 'display:none;';
			}

			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s1" data-collapsible="accordion" data-style='1'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 1', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<p class="description"><?php esc_html_e( 'Style-1', 'click-to-chat-for-whatsapp' ); ?>: <?php esc_html_e( 'button that appears like themes button', 'click-to-chat-for-whatsapp' ); ?></p>
		<br><br>

		<!-- Text color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[s1_text_color]" value="<?php echo esc_attr( $s1_text_color ); ?>" type="text" data-update-type='color' data-update-selector='.ctc_s_1 .ctc_cta'>
			</div>
		</div>

		<!-- background color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[s1_bg_color]" value="<?php echo esc_attr( $s1_bg_color ); ?>" type="text" data-update-type='background-color' data-update-selector='.ctc_s_1'>
			</div>
		</div>

		<!-- Add icon -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Add Icon', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<p>
					<label>
						<input name="<?php echo esc_attr( $dbrow ); ?>[s1_add_icon]" type="checkbox" value="1" <?php checked( $s1_add_icon_checkbox, 1 ); ?> class="s1_add_icon ctc_no_demo" id="s1_add_icon"/>
						<span><?php esc_html_e( 'Add Icon', 'click-to-chat-for-whatsapp' ); ?></span>
					</label>
				</p>
			</div>
		</div>

		<!-- Icon color -->
		<div class="row s1_icon_settings">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[s1_icon_color]" value="<?php echo esc_attr( $s1_icon_color ); ?>" type="text" data-default-color="#25D366" data-update-type='fill' data-update-selector='.ctc_s_1 svg path'>
			</div>
		</div>

		<!-- icon size -->
		<div class="row s1_icon_settings">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[s1_icon_size]" value="<?php echo esc_attr( $s1_icon_size ); ?>" id="s1_icon_size" type="text" class="s1_icon_size ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_1 svg'>
				<label for="s1_icon_size"><?php esc_html_e( 'Icon Size', 'click-to-chat-for-whatsapp' ); ?> (e.g. 15px)</label>
			</div>
		</div>

		<!-- Full Width on Mobile -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6 cs_m_fullwidth">
				<p>
					<label>
						<input name="<?php echo esc_attr( $dbrow ); ?>[s1_m_fullwidth]" type="checkbox" value="1" <?php checked( $s1_m_fullwidth_checkbox, 1 ); ?> class="ctc_no_demo" id="s1_m_fullwidth" />
						<span><?php esc_html_e( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ); ?></span>
					</label>
				</p>

				<p class="m_fullwidth_description description" style="<?php echo esc_attr( $s1_m_fullwidth_css ); ?>">
					Set position at <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat#position_to_place' ) ); ?>" target="_blank"> Click to Chat → Position to Place (Mobile)</a>
				</p>
			</div>

			<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/style-1/"><?php esc_html_e( 'Style-1', 'click-to-chat-for-whatsapp' ); ?></a></p>
		</div>
		</li>
		</ul>
		
			<?php
		}



		/**
		 * Style 2: WhatsApp iOS-style icon.
		 */
		public function ht_ctc_s2_cb() {

			$options     = get_option( 'ht_ctc_s2' );
			$dbrow       = 'ht_ctc_s2';
			$style       = 's2';
			$s2_img_size = ( isset( $options['s2_img_size'] ) ) ? esc_attr( $options['s2_img_size'] ) : '';

			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s2" data-collapsible="accordion" data-style='2'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 2', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">


		<!-- img size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[s2_img_size]" value="<?php echo esc_attr( $s2_img_size ); ?>" id="s2_img_size" type="text" class="ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_2 svg'>
				<label for="s2_img_size"><?php esc_html_e( 'Image Size', 'click-to-chat-for-whatsapp' ); ?> (e.g. 50px)</label>
			</div>
		</div>

			<?php
			$select_cta_type = ( isset( $options['cta_type'] ) ) ? esc_attr( $options['cta_type'] ) : '';

			$cta_textcolor = ( isset( $options['cta_textcolor'] ) ) ? esc_attr( $options['cta_textcolor'] ) : '';
			$cta_bgcolor   = ( isset( $options['cta_bgcolor'] ) ) ? esc_attr( $options['cta_bgcolor'] ) : '';
			$cta_font_size = ( isset( $options['cta_font_size'] ) ) ? esc_attr( $options['cta_font_size'] ) : '';

			?>

		<h5 style="display: flex;">Call to Action </h5>
		<hr>
		<!-- call to action - hover / show / hide -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[cta_type]" class="select_cta_type ctc_oninput" data-update-type='cta' data-update-selector='.ctc_s_2 .ctc_cta'>
					<option value="hover" <?php selected( $select_cta_type, 'hover' ); ?>>On Hover</option>
					<option value="show" <?php selected( $select_cta_type, 'show' ); ?>>Show</option>
					<option value="hide" <?php selected( $select_cta_type, 'hide' ); ?>>Hide</option>
					<?php
					if ( 's7' === $style ) {
						?>
					<option value="inside" <?php selected( $select_cta_type, 'inside' ); ?>>Inside padding</option>
						<?php
					}
					?>
				</select>
			</div>
		</div>

		<!-- call to action - Text color -->
		<div class="row cta_textcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_textcolor]" data-default-color="#ffffff" value="<?php echo esc_attr( $cta_textcolor ); ?>" type="text" data-update-type='color' data-update-selector='.ctc_s_2 .ctc_cta'>
			</div>
		</div>

		<!-- call to action - background color -->
		<div class="row cta_bgcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_bgcolor]" data-default-color="#25D366" value="<?php echo esc_attr( $cta_bgcolor ); ?>" id="cta_bgcolor" type="text" data-update-type='background-color' data-update-selector='.ctc_s_2 .ctc_cta'>
			</div>
		</div>

		<!-- font size -->
		<div class="row cta_font_size cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Font Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[cta_font_size]" value="<?php echo esc_attr( $cta_font_size ); ?>" id="s2_cta_font_size" type="text" class="ctc_oninput" data-update-type='font-size' data-update-selector='.ctc_s_2 .ctc_cta'>
				<label for="s2_cta_font_size"><?php esc_html_e( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>


		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}



		/**
		 * Style 3: WhatsApp Android-style icon.
		 */
		public function ht_ctc_s3_cb() {

			$options = get_option( 'ht_ctc_s3' );
			$dbrow   = 'ht_ctc_s3';
			$style   = 's3';

			$select_s3_type = ( isset( $options['s3_type'] ) ) ? esc_attr( $options['s3_type'] ) : '1';

			$s3_img_size = ( isset( $options['s3_img_size'] ) ) ? esc_attr( $options['s3_img_size'] ) : '';

			$s3_extend_img_size = ( isset( $options['s3_extend_img_size'] ) ) ? esc_attr( $options['s3_extend_img_size'] ) : '';
			$s3_padding         = ( isset( $options['s3_padding'] ) ) ? esc_attr( $options['s3_padding'] ) : '';

			$s3_bg_color       = ( isset( $options['s3_bg_color'] ) ) ? esc_attr( $options['s3_bg_color'] ) : '';
			$s3_bg_color_hover = ( isset( $options['s3_bg_color_hover'] ) ) ? esc_attr( $options['s3_bg_color_hover'] ) : '';

			$select_cta_type = ( isset( $options['cta_type'] ) ) ? esc_attr( $options['cta_type'] ) : '';
			$cta_textcolor   = ( isset( $options['cta_textcolor'] ) ) ? esc_attr( $options['cta_textcolor'] ) : '';
			$cta_bgcolor     = ( isset( $options['cta_bgcolor'] ) ) ? esc_attr( $options['cta_bgcolor'] ) : '';
			$cta_font_size   = ( isset( $options['cta_font_size'] ) ) ? esc_attr( $options['cta_font_size'] ) : '';
			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s3" data-collapsible="accordion" data-style='3'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 3', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- img size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s3[s3_img_size]" value="<?php echo esc_attr( $s3_img_size ); ?>" id="s3_img_size" type="text" class="ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_3 svg'>
				<label for="s3_img_size"><?php esc_html_e( 'Image Size (Default: 50px )', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>


		<h5 style="display: flex;">Call to Action </h5>
		<!-- call to action - hover / show / hide -->
		<hr>
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[cta_type]" class="select_cta_type ctc_oninput" data-update-type='cta' data-update-selector='.ctc_s_3 .ctc_cta'>
					<option value="hover" <?php selected( $select_cta_type, 'hover' ); ?>>On Hover</option>
					<option value="show" <?php selected( $select_cta_type, 'show' ); ?>>Show</option>
					<option value="hide" <?php selected( $select_cta_type, 'hide' ); ?>>Hide</option>
					<?php
					if ( 's7' === $style ) {
						?>
					<option value="inside" <?php selected( $select_cta_type, 'inside' ); ?>>Inside padding</option>
						<?php
					}
					?>
				</select>
			</div>
		</div>

		<!-- call to action - Text color -->
		<div class="row cta_textcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_textcolor]" data-default-color="#ffffff" value="<?php echo esc_attr( $cta_textcolor ); ?>" type="text" data-update-type='color' data-update-selector='.ctc_s_3 .ctc_cta'>
			</div>
		</div>

		<!-- call to action - background color -->
		<div class="row cta_bgcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_bgcolor]" data-default-color="#25D366" value="<?php echo esc_attr( $cta_bgcolor ); ?>" id="cta_bgcolor" type="text" data-update-type='background-color' data-update-selector='.ctc_s_3 .ctc_cta'>
			</div>
		</div>

		<!-- font size -->
		<div class="row cta_font_size cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Font Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[cta_font_size]" value="<?php echo esc_attr( $cta_font_size ); ?>" id="s3_cta_font_size" type="text" class="ctc_oninput" data-update-type='font-size' data-update-selector='.ctc_s_3 .ctc_cta'>
				<label for="s3_cta_font_size"><?php esc_html_e( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}


		/**
		 * Style 3_1: Extended full icon.
		 */
		public function ht_ctc_s3_1_cb() {

			$options        = get_option( 'ht_ctc_s3_1' );
			$dbrow          = 'ht_ctc_s3_1';
			$style          = 's3';
			$select_s3_type = ( isset( $options['s3_type'] ) ) ? esc_attr( $options['s3_type'] ) : '1';

			$s3_img_size = ( isset( $options['s3_img_size'] ) ) ? esc_attr( $options['s3_img_size'] ) : '';

			$s3_extend_img_size = ( isset( $options['s3_extend_img_size'] ) ) ? esc_attr( $options['s3_extend_img_size'] ) : '';
			$s3_padding         = ( isset( $options['s3_padding'] ) ) ? esc_attr( $options['s3_padding'] ) : '';

			$s3_bg_color       = ( isset( $options['s3_bg_color'] ) ) ? esc_attr( $options['s3_bg_color'] ) : '';
			$s3_bg_color_hover = ( isset( $options['s3_bg_color_hover'] ) ) ? esc_attr( $options['s3_bg_color_hover'] ) : '';

			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s3_1" data-collapsible="accordion" data-style='3_1'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 3 Extend', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">


		<!-- img size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s3_1[s3_img_size]" value="<?php echo esc_attr( $s3_img_size ); ?>" id="s3_1_img_size" type="text" class="ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_3_1 svg'>
				<label for="s3_1_img_size"><?php esc_html_e( 'Image Size (Default: 40px )', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

			
		<!-- Padding -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Padding', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s3_1[s3_padding]" value="<?php echo esc_attr( $s3_padding ); ?>" id="s3_padding" type="text" class="ctc_oninput" data-update-type='padding' data-update-selector='.ctc_s_3_1 .ht_ctc_padding'>
				<label for="s3_padding"><?php esc_html_e( 'Padding (Default: 20px )', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- background color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="ht_ctc_s3_1[s3_bg_color]" data-default-color="#25D366" value="<?php echo esc_attr( $s3_bg_color ); ?>" id="s3_1_bg_color" type="text" data-update-type='background-color' data-update-selector='.ctc_s_3_1 .ht_ctc_padding'>
			</div>
		</div>

		<!-- background color hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="ht_ctc_s3_1[s3_bg_color_hover]" data-default-color="#25D366" value="<?php echo esc_attr( $s3_bg_color_hover ); ?>" id="s3_1_bg_color_hover" type="text">
				<p class="description"><?php esc_html_e( 'E.g. ', 'click-to-chat-for-whatsapp' ); ?> #25D366, #20b038</p>
			</div>
		</div>
			<?php

			// shadow
			?>
		<div class="s3_box_shadow">
			<?php
			if ( isset( $options['s3_box_shadow'] ) ) {
				?>
		<p>
			<label class="ctc_checkbox_label">
				<input name="ht_ctc_s3_1[s3_box_shadow]" type="checkbox" value="1" <?php checked( $options['s3_box_shadow'], 1 ); ?> id="s3_box_shadow" class=""/>
				<span><?php esc_html_e( 'Shadow', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</p>
				<?php
			} else {
				?>
			<p>
				<label>
					<input name="ht_ctc_s3_1[s3_box_shadow]" type="checkbox" value="1" id="s3_box_shadow" class=""/>
					<span><?php esc_html_e( 'Shadow', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
			</p>
				<?php
			}
			?>
		</div>
			<?php

			// shadow on hover
			?>
		<div class="s3_box_shadow_hover ctc_init_display_none">
			<?php
			if ( isset( $options['s3_box_shadow_hover'] ) ) {
				?>
		<p>
			<label>
				<input name="ht_ctc_s3_1[s3_box_shadow_hover]" type="checkbox" value="1" <?php checked( $options['s3_box_shadow_hover'], 1 ); ?> id="s3_box_shadow_hover" class=""/>
				<span><?php esc_html_e( 'Shadow on Hover only', 'click-to-chat-for-whatsapp' ); ?></span>
			</label>
		</p>
				<?php
			} else {
				?>
			<p>
				<label>
					<input name="ht_ctc_s3_1[s3_box_shadow_hover]" type="checkbox" value="1" id="s3_box_shadow_hover" class=""/>
					<span><?php esc_html_e( 'Shadow on Hover only', 'click-to-chat-for-whatsapp' ); ?></span>
				</label>
			</p>
				<?php
			}
			?>
		</div>
		<br>

			<?php
			$select_cta_type = ( isset( $options['cta_type'] ) ) ? esc_attr( $options['cta_type'] ) : '';

			$cta_textcolor = ( isset( $options['cta_textcolor'] ) ) ? esc_attr( $options['cta_textcolor'] ) : '';
			$cta_bgcolor   = ( isset( $options['cta_bgcolor'] ) ) ? esc_attr( $options['cta_bgcolor'] ) : '';
			$cta_font_size = ( isset( $options['cta_font_size'] ) ) ? esc_attr( $options['cta_font_size'] ) : '';

			?>

		<h5 style="display: flex;">Call to Action </h5>
		<hr>
		<!-- call to action - hover / show / hide -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[cta_type]" class="select_cta_type ctc_oninput" data-update-type='cta' data-update-selector='.ctc_s_3_1 .ctc_cta'>
					<option value="hover" <?php selected( $select_cta_type, 'hover' ); ?>>On Hover</option>
					<option value="show" <?php selected( $select_cta_type, 'show' ); ?>>Show</option>
					<option value="hide" <?php selected( $select_cta_type, 'hide' ); ?>>Hide</option>
					<?php
					if ( 's7' === $style ) {
						?>
					<option value="inside" <?php selected( $select_cta_type, 'inside' ); ?>>Inside padding</option>
						<?php
					}
					?>
				</select>
			</div>
		</div>

		<!-- call to action - Text color -->
		<div class="row cta_textcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Call to Action - Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_textcolor]" data-default-color="#ffffff" value="<?php echo esc_attr( $cta_textcolor ); ?>" type="text" data-update-type='color' data-update-selector='.ctc_s_3_1 .ctc_cta'>
			</div>
		</div>

		<!-- call to action - background color -->
		<div class="row cta_bgcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Call to Action - Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_bgcolor]" data-default-color="#25D366" value="<?php echo esc_attr( $cta_bgcolor ); ?>" id="cta_bgcolor" type="text" data-update-type='background-color' data-update-selector='.ctc_s_3_1 .ctc_cta'>
			</div>
		</div>

		<!-- font size -->
		<div class="row cta_font_size cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Font Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[cta_font_size]" value="<?php echo esc_attr( $cta_font_size ); ?>" id="s3_1_cta_font_size" type="text" class="ctc_oninput" data-update-type='font-size' data-update-selector='.ctc_s_3_1 .ctc_cta'>
				<label for="s3_1_cta_font_size"><?php esc_html_e( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>


		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}



		/**
		 * Style 4: Chip.
		 */
		public function ht_ctc_s4_cb() {

			$options                = get_option( 'ht_ctc_s4' );
			$s4_text_color          = ( isset( $options['s4_text_color'] ) ) ? esc_attr( $options['s4_text_color'] ) : '';
			$s4_bg_color            = ( isset( $options['s4_bg_color'] ) ) ? esc_attr( $options['s4_bg_color'] ) : '';
			$s4_img_url             = ( isset( $options['s4_img_url'] ) ) ? esc_attr( $options['s4_img_url'] ) : '';
			$s4_img_size            = ( isset( $options['s4_img_size'] ) ) ? esc_attr( $options['s4_img_size'] ) : '';
			$select_s4_img_position = ( isset( $options['s4_img_position'] ) ) ? esc_attr( $options['s4_img_position'] ) : '';
			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s4" data-collapsible="accordion" data-style='4'>
		<li>
		<div class="collapsible-header">Style 4
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- text color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="ht_ctc_s4[s4_text_color]" data-default-color="#7f7d7d" value="<?php echo esc_attr( $s4_text_color ); ?>" id="s4_text_color" type="text" data-update-type='color' data-update-selector='.ctc_s_4'>
			</div>
		</div>

		<!-- background color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s4_bg_color" class="ht-ctc-color" data-default-color="#e4e4e4" name="ht_ctc_s4[s4_bg_color]" value="<?php echo esc_attr( $s4_bg_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='background-color' data-update-selector='.ctc_s_4'>
			</div>
		</div>

		<!-- Image position -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Position', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="ht_ctc_s4[s4_img_position]" class="select-2 s4_img_position">
					<option value="left" <?php selected( $select_s4_img_position, 'left' ); ?>>Left</option>
					<option value="right" <?php selected( $select_s4_img_position, 'right' ); ?>>Right</option>
				</select>
			</div>
		</div>

		<!-- image url -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image URL', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s4[s4_img_url]" value="<?php echo esc_attr( $s4_img_url ); ?>" id="s4_img_url" type="text" class="ctc_no_demo" >
				<label for="s4_img_url"><?php esc_html_e( 'Image URL(leave blank for default image)', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- img size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s4[s4_img_size]" value="<?php echo esc_attr( $s4_img_size ); ?>" id="s4_img_size" type="text" class="ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_4 svg'>
				<label for="s4_img_size"><?php esc_html_e( 'Image Size (default 32px)', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( '(possible, keep the value less then or equal to 32px)', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>


		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}



		/**
		 * Style 5: Chip with image and content.
		 */
		public function ht_ctc_s5_cb() {

			$options                = get_option( 'ht_ctc_s5' );
			$s5_line_1              = ( isset( $options['s5_line_1'] ) ) ? esc_attr( $options['s5_line_1'] ) : '';
			$s5_line_2              = ( isset( $options['s5_line_2'] ) ) ? esc_attr( $options['s5_line_2'] ) : '';
			$s5_line_1_color        = ( isset( $options['s5_line_1_color'] ) ) ? esc_attr( $options['s5_line_1_color'] ) : '';
			$s5_line_2_color        = ( isset( $options['s5_line_2_color'] ) ) ? esc_attr( $options['s5_line_2_color'] ) : '';
			$s5_background_color    = ( isset( $options['s5_background_color'] ) ) ? esc_attr( $options['s5_background_color'] ) : '';
			$s5_border_color        = ( isset( $options['s5_border_color'] ) ) ? esc_attr( $options['s5_border_color'] ) : '';
			$s5_img                 = ( isset( $options['s5_img'] ) ) ? esc_attr( $options['s5_img'] ) : '';
			$s5_img_height          = ( isset( $options['s5_img_height'] ) ) ? esc_attr( $options['s5_img_height'] ) : '';
			$s5_img_width           = ( isset( $options['s5_img_width'] ) ) ? esc_attr( $options['s5_img_width'] ) : '';
			$s5_content_height      = ( isset( $options['s5_content_height'] ) ) ? esc_attr( $options['s5_content_height'] ) : '';
			$s5_content_width       = ( isset( $options['s5_content_width'] ) ) ? esc_attr( $options['s5_content_width'] ) : '';
			$select_s5_img_position = ( isset( $options['s5_img_position'] ) ) ? esc_attr( $options['s5_img_position'] ) : '';
			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s5" data-collapsible="accordion" data-style='5'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 5', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- s5_line_1 -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Line 1', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s5[s5_line_1]" value="<?php echo esc_attr( $s5_line_1 ); ?>" id="s5_line_1" type="text" class="ctc_no_demo" >
				<label for="s5_line_1"><?php esc_html_e( 'Line 1', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- s5_line_2 -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Line 2', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s5[s5_line_2]" value="<?php echo esc_attr( $s5_line_2 ); ?>" id="s5_line_2" type="text" class="ctc_oninput" data-update-type='text' data-update-selector='.ctc_s_5 .description'>
				<label for="s5_line_2"><?php esc_html_e( 'Line 2', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- s5_line_1_color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Line 1 - Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="ht_ctc_s5[s5_line_1_color]" data-default-color="#000000" value="<?php echo esc_attr( $s5_line_1_color ); ?>" id="s5_line_1_color" type="text" data-update-type='color' data-update-selector='.ctc_s_5 .heading'>
			</div>
		</div>

		<!-- s5_line_2_color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Line 2 - Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="ht_ctc_s5[s5_line_2_color]" data-default-color="#000000" value="<?php echo esc_attr( $s5_line_2_color ); ?>" id="s5_line_2_color" type="text" data-update-type='color' data-update-selector='.ctc_s_5 .description'>
			</div>
		</div>

		<!-- s5_background_color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Content Box Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="ht_ctc_s5[s5_background_color]" data-default-color="#ffffff" value="<?php echo esc_attr( $s5_background_color ); ?>" id="s5_background_color" type="text" data-update-type='background-color' data-update-selector='.ctc_s_5 .ctc_cta_stick'>
			</div>
		</div>

		<!-- s5_border_color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Content Box Border Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="ht_ctc_s5[s5_border_color]" data-default-color="#dddddd" value="<?php echo esc_attr( $s5_border_color ); ?>" id="s5_border_color" type="text" data-update-type='border-color' data-update-selector='.ctc_s_5 .ctc_cta_stick'>
			</div>
		</div>

		<!-- s5_img -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image URL', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s5[s5_img]" value="<?php echo esc_attr( $s5_img ); ?>" id="s5_img" type="text" class="ctc_no_demo" >
				<label for="s5_img">Leave blank for default image</label>
			</div>
		</div>

		<!-- s5_img_height -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Height', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s5[s5_img_height]" value="<?php echo esc_attr( $s5_img_height ); ?>" id="s5_img_height" type="text" class="ctc_oninput" data-update-type='height' data-update-selector='.ctc_s_5 .s5_img'>
				<label for="s5_img_height"><?php esc_html_e( 'Image Height', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g.', 'click-to-chat-for-whatsapp' ); ?> 70px</p>
			</div>
		</div>

		<!-- s5_img_width -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Width', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s5[s5_img_width]" value="<?php echo esc_attr( $s5_img_width ); ?>" id="s5_img_width" type="text" class="ctc_oninput" data-update-type='width' data-update-selector='.ctc_s_5 .s5_img'>
				<label for="s5_img_width"><?php esc_html_e( 'Image Width', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g.', 'click-to-chat-for-whatsapp' ); ?> 70px</p>
			</div>
		</div>

		<!-- s5_content_height -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Content Box Height', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s5[s5_content_height]" value="<?php echo esc_attr( $s5_content_height ); ?>" id="s5_content_height" type="text" class="ctc_oninput"  data-update-type='height' data-update-selector='.ctc_s_5 .s5_content'>
				<label for="s5_content_height"><?php esc_html_e( 'Content Box Height', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g.', 'click-to-chat-for-whatsapp' ); ?> 70px</p>
			</div>
		</div>

		<!-- s5_content_width -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Content Box Width', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s5[s5_content_width]" value="<?php echo esc_attr( $s5_content_width ); ?>" id="s5_content_width" type="text" class="ctc_oninput"  data-update-type='width' data-update-selector='.ctc_s_5 .s5_content'>
				<label for="s5_content_width"><?php esc_html_e( 'Content Box Width', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g.', 'click-to-chat-for-whatsapp' ); ?> 270px, 100%</p>
			</div>
		</div>

		<!-- s5_img_position -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Image Position', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="ht_ctc_s5[s5_img_position]" class="select-2 ctc_no_demo">
					<option value="right" <?php selected( $select_s5_img_position, 'right' ); ?>><?php esc_html_e( 'Right', 'click-to-chat-for-whatsapp' ); ?></option>
					<option value="left" <?php selected( $select_s5_img_position, 'left' ); ?>><?php esc_html_e( 'Left', 'click-to-chat-for-whatsapp' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'If style position/located: Right to screen then select Right, if Left to screen then select Left', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>


		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}




		/**
		 * Style 6: Plain link.
		 */
		public function ht_ctc_s6_cb() {

			$options                     = get_option( 'ht_ctc_s6' );
			$s6_txt_color                = ( isset( $options['s6_txt_color'] ) ) ? esc_attr( $options['s6_txt_color'] ) : '';
			$s6_txt_color_on_hover       = ( isset( $options['s6_txt_color_on_hover'] ) ) ? esc_attr( $options['s6_txt_color_on_hover'] ) : '';
			$text_decoration_value       = ( isset( $options['s6_txt_decoration'] ) ) ? esc_attr( $options['s6_txt_decoration'] ) : '';
			$text_decoration_hover_value = ( isset( $options['s6_txt_decoration_on_hover'] ) ) ? esc_attr( $options['s6_txt_decoration_on_hover'] ) : '';
			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s6" data-collapsible="accordion" data-style='6'>
		<li>
		<div class="collapsible-header">Style 6
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- text color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s6_txt_color" class="ht-ctc-color" name="ht_ctc_s6[s6_txt_color]" value="<?php echo esc_attr( $s6_txt_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='color' data-update-selector='.ctc_s_6'>
			</div>
		</div>


		<!-- text color on hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s6_txt_color_on_hover" class="ht-ctc-color" name="ht_ctc_s6[s6_txt_color_on_hover]" value="<?php echo esc_attr( $s6_txt_color_on_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>

		<!-- Text Decoration -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Decoration', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select id="s6_txt_decoration" name="ht_ctc_s6[s6_txt_decoration]" class="select-2 ctc_no_demo">
					<option value="initial" <?php selected( $text_decoration_value, 'initial' ); ?>>initial</option>
					<option value="underline" <?php selected( $text_decoration_value, 'underline' ); ?>>underline</option>
					<option value="overline" <?php selected( $text_decoration_value, 'overline' ); ?>>overline</option>
					<option value="line-through" <?php selected( $text_decoration_value, 'line-through' ); ?>>line-through</option>
					<option value="inherit" <?php selected( $text_decoration_value, 'inherit' ); ?>>inherit</option>
				</select>
			</div>
		</div>

		<!-- Text Decoration when hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Decoration when Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select id="s6_txt_decoration_on_hover" name="ht_ctc_s6[s6_txt_decoration_on_hover]" class="select-2 ctc_no_demo">
					<option value="initial" <?php selected( $text_decoration_hover_value, 'initial' ); ?>>initial</option>
					<option value="underline" <?php selected( $text_decoration_hover_value, 'underline' ); ?>>underline</option>
					<option value="overline" <?php selected( $text_decoration_hover_value, 'overline' ); ?>>overline</option>
					<option value="line-through" <?php selected( $text_decoration_hover_value, 'line-through' ); ?>>line-through</option>
					<option value="inherit" <?php selected( $text_decoration_hover_value, 'inherit' ); ?>>inherit</option>
				</select>
			</div>
		</div>

		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}


		/**
		 * Style 7: Plain link variation.
		 */
		public function ht_ctc_s7_cb() {

			$options = get_option( 'ht_ctc_s7' );
			$dbrow   = 'ht_ctc_s7';
			$style   = 's7';

			$s7_icon_size          = ( isset( $options['s7_icon_size'] ) ) ? esc_attr( $options['s7_icon_size'] ) : '';
			$s7_icon_color         = ( isset( $options['s7_icon_color'] ) ) ? esc_attr( $options['s7_icon_color'] ) : '';
			$s7_icon_color_hover   = ( isset( $options['s7_icon_color_hover'] ) ) ? esc_attr( $options['s7_icon_color_hover'] ) : '';
			$s7_border_size        = ( isset( $options['s7_border_size'] ) ) ? esc_attr( $options['s7_border_size'] ) : '';
			$s7_border_color       = ( isset( $options['s7_border_color'] ) ) ? esc_attr( $options['s7_border_color'] ) : '';
			$s7_border_color_hover = ( isset( $options['s7_border_color_hover'] ) ) ? esc_attr( $options['s7_border_color_hover'] ) : '';
			$s7_border_radius      = ( isset( $options['s7_border_radius'] ) ) ? esc_attr( $options['s7_border_radius'] ) : '';

			$select_cta_type = ( isset( $options['cta_type'] ) ) ? esc_attr( $options['cta_type'] ) : '';

			$cta_textcolor       = ( isset( $options['cta_textcolor'] ) ) ? esc_attr( $options['cta_textcolor'] ) : '';
			$cta_textcolor_hover = ( isset( $options['cta_textcolor_hover'] ) ) ? esc_attr( $options['cta_textcolor_hover'] ) : '';
			$cta_bgcolor         = ( isset( $options['cta_bgcolor'] ) ) ? esc_attr( $options['cta_bgcolor'] ) : '';
			$cta_bgcolor_hover   = ( isset( $options['cta_bgcolor_hover'] ) ) ? esc_attr( $options['cta_bgcolor_hover'] ) : '';
			$cta_font_size       = ( isset( $options['cta_font_size'] ) ) ? esc_attr( $options['cta_font_size'] ) : '';

			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s7" data-collapsible="accordion" data-style='7'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 7', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- s7_icon_size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s7[s7_icon_size]" value="<?php echo esc_attr( $s7_icon_size ); ?>" id="s7_icon_size" type="text" class="ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_7 svg'>
				<label for="s7_icon_size"><?php esc_html_e( 'Icon Size', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g.', 'click-to-chat-for-whatsapp' ); ?> 20px</p>
			</div>
		</div>

		<!-- s7_icon_color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_icon_color" class="ht-ctc-color" data-default-color="#ffffff" name="ht_ctc_s7[s7_icon_color]" value="<?php echo esc_attr( $s7_icon_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='fill' data-update-selector='.ctc_s_7 svg path'>
			</div>
		</div>

		<!-- s7_icon_color_hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_icon_color_hover" class="ht-ctc-color" data-default-color="#ffffff" name="ht_ctc_s7[s7_icon_color_hover]" value="<?php echo esc_attr( $s7_icon_color_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>

		<!-- s7_border_size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Border Padding Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s7[s7_border_size]" value="<?php echo esc_attr( $s7_border_size ); ?>" id="s7_border_size" type="text" class="ctc_oninput" data-update-type='padding' data-update-selector='.ctc_s_7 .ctc_s_7_icon_padding'>
				<label for="s7_border_size"><?php esc_html_e( 'Border Padding Size', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g. 12px', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>

		<!-- s7_border_color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_border_color" class="ht-ctc-color" data-default-color="#25d366" name="ht_ctc_s7[s7_border_color]" value="<?php echo esc_attr( $s7_border_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='background-color' data-update-selector='.ctc_s_7 .ctc_s_7_icon_padding'>
			</div>
		</div>

		<!-- s7_border_color_hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_border_color_hover" class="ht-ctc-color" data-default-color="#25d366" name="ht_ctc_s7[s7_border_color_hover]" value="<?php echo esc_attr( $s7_border_color_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>

		<!-- s7_border_radius -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Border radius', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s7[s7_border_radius]" value="<?php echo esc_attr( $s7_border_radius ); ?>" id="s7_border_radius" type="text" class="ctc_oninput" data-update-type='border-radius' data-update-selector='.ctc_s_7 .ctc_s_7_icon_padding'>
				<label for="s7_border_radius"><?php esc_html_e( 'Border radius', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g. 10px, 50% ( for round border add 50% )', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>
		<br>
		<p class="description"><?php esc_html_e( 'To display icon only - clear background-color. (May need to change icon color to display in plain background)', 'click-to-chat-for-whatsapp' ); ?> </p>

		<br><br>

		<h5 style="display: flex;">Call to Action </h5>
		<hr>
		<!-- call to action - hover / show / hide -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[cta_type]" class="select_cta_type ctc_oninput" data-update-type='cta' data-update-selector='.ctc_s_7 .ctc_cta'>
					<option value="hover" <?php selected( $select_cta_type, 'hover' ); ?>>On Hover</option>
					<option value="show" <?php selected( $select_cta_type, 'show' ); ?>>Show</option>
					<option value="hide" <?php selected( $select_cta_type, 'hide' ); ?>>Hide</option>
				</select>
			</div>
		</div>
		<!-- call to action - Text color -->
		<div class="row cta_textcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_textcolor]" data-default-color="#ffffff" value="<?php echo esc_attr( $cta_textcolor ); ?>" type="text" data-update-type='color' data-update-selector='.ctc_s_7 .ctc_cta_stick'>
			</div>
		</div>

		<!-- call to action - background color -->
		<div class="row cta_bgcolor cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input class="ht-ctc-color" name="<?php echo esc_attr( $dbrow ); ?>[cta_bgcolor]" data-default-color="#25d366" value="<?php echo esc_attr( $cta_bgcolor ); ?>" id="cta_bgcolor" type="text" data-update-type='background-color' data-update-selector='.ctc_s_7 .ctc_cta_stick'>
			</div>
		</div>

		<!-- font size -->
		<div class="row cta_font_size cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Font Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[cta_font_size]" value="<?php echo esc_attr( $cta_font_size ); ?>" id="s7_cta_font_size" type="text" class="" >
				<label for="s7_cta_font_size"><?php esc_html_e( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ); ?></label>
				<span class="helper-text"><?php esc_html_e( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ); ?></span>
			</div>
		</div>

		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}


		/**
		 * Style 7_1: Extended plain link variation.
		 */
		public function ht_ctc_s7_1_cb() {

			$options = get_option( 'ht_ctc_s7_1' );
			$dbrow   = 'ht_ctc_s7_1';
			$style   = 's7';

			$s7_icon_size        = ( isset( $options['s7_icon_size'] ) ) ? esc_attr( $options['s7_icon_size'] ) : '';
			$s7_icon_color       = ( isset( $options['s7_icon_color'] ) ) ? esc_attr( $options['s7_icon_color'] ) : '';
			$s7_icon_color_hover = ( isset( $options['s7_icon_color_hover'] ) ) ? esc_attr( $options['s7_icon_color_hover'] ) : '';
			$s7_border_size      = ( isset( $options['s7_border_size'] ) ) ? esc_attr( $options['s7_border_size'] ) : '';
			$s7_bgcolor          = ( isset( $options['s7_bgcolor'] ) ) ? esc_attr( $options['s7_bgcolor'] ) : '';
			$s7_bgcolor_hover    = ( isset( $options['s7_bgcolor_hover'] ) ) ? esc_attr( $options['s7_bgcolor_hover'] ) : '';
			$cta_font_size       = ( isset( $options['cta_font_size'] ) ) ? esc_attr( $options['cta_font_size'] ) : '';

			$select_cta_type = ( isset( $options['cta_type'] ) ) ? esc_attr( $options['cta_type'] ) : '';
			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s7_1" data-collapsible="accordion" data-style='7_1'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 7 Extend', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- s7_1 call to action - hover / show  -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Display - Call to Action', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="<?php echo esc_attr( $dbrow ); ?>[cta_type]" class="select_cta_type ctc_oninput" data-update-type='cta' data-update-selector='.ctc_s_7_1 .ctc_cta'>
					<option value="hover" <?php selected( $select_cta_type, 'hover' ); ?>>On Hover</option>
					<option value="show" <?php selected( $select_cta_type, 'show' ); ?>>Show</option>
				</select>
			</div>
		</div>


		<!-- s7_icon_size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[s7_icon_size]" value="<?php echo esc_attr( $s7_icon_size ); ?>" id="s7_1_icon_size" type="text" class="ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_7_1 svg'>
				<label for="s7_1_icon_size"><?php esc_html_e( 'Icon Size', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g.', 'click-to-chat-for-whatsapp' ); ?> 20px</p>
			</div>
		</div>

		<!-- s7_border_size icon padding size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Border Padding Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[s7_border_size]" value="<?php echo esc_attr( $s7_border_size ); ?>" id="s7_1_border_size" type="text" class="ctc_oninput" data-update-type='padding' data-update-selector='.ctc_s_7_1 .ctc_s_7_icon_padding'>
				<label for="s7_1_border_size"><?php esc_html_e( 'Border Padding Size', 'click-to-chat-for-whatsapp' ); ?></label>
				<p class="description"><?php esc_html_e( 'E.g. 12px', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
		</div>

		<!-- s7_icon_color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon,Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_1_icon_color" class="ht-ctc-color" data-default-color="#ffffff" name="<?php echo esc_attr( $dbrow ); ?>[s7_icon_color]" value="<?php echo esc_attr( $s7_icon_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='fill' data-update-selector='.ctc_s_7_1 svg path' data-update-2-type='color' data-update-2-selector='.ctc_s_7_1 .ctc_s_7_1_cta'>
			</div>
		</div>

		<!-- s7_icon_color_hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon,Text Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_1_icon_color_hover" class="ht-ctc-color" data-default-color="#f4f4f4" name="<?php echo esc_attr( $dbrow ); ?>[s7_icon_color_hover]" value="<?php echo esc_attr( $s7_icon_color_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>

		<!-- s7_bgcolor -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_1_bgcolor" class="ht-ctc-color" data-default-color="#25d366" name="<?php echo esc_attr( $dbrow ); ?>[s7_bgcolor]" value="<?php echo esc_attr( $s7_bgcolor ); ?>" type="text" style="height: 1.375rem;" data-update-type='background-color' data-update-selector='.ctc_s_7_1'>
			</div>
		</div>

		<!-- s7_bgcolor_hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s7_1_bgcolor_hover" class="ht-ctc-color" data-default-color="#25d366" name="<?php echo esc_attr( $dbrow ); ?>[s7_bgcolor_hover]" value="<?php echo esc_attr( $s7_bgcolor_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>

		<!-- font size -->
		<div class="row cta_font_size cta_stick">
			<div class="col s6">
				<p><?php esc_html_e( 'Font Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="<?php echo esc_attr( $dbrow ); ?>[cta_font_size]" value="<?php echo esc_attr( $cta_font_size ); ?>" id="s7_1_cta_font_size" type="text" class="ctc_oninput" data-update-type='font-size' data-update-selector='.ctc_s_7_1 .ctc_cta'>
				<label for="s7_1_cta_font_size"><?php esc_html_e( 'Font Size (e.g. 15px)', 'click-to-chat-for-whatsapp' ); ?></label>
				<span class="helper-text"><?php esc_html_e( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ); ?></span>
			</div>
		</div>


		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}



		/**
		 * Style 8: Button with icon.
		 */
		public function ht_ctc_s8_cb() {

			$options = get_option( 'ht_ctc_s8' );
			$dbrow   = 'ht_ctc_s8';

			$s8_txt_color            = ( isset( $options['s8_txt_color'] ) ) ? esc_attr( $options['s8_txt_color'] ) : '';
			$s8_txt_color_on_hover   = ( isset( $options['s8_txt_color_on_hover'] ) ) ? esc_attr( $options['s8_txt_color_on_hover'] ) : '';
			$s8_bg_color             = ( isset( $options['s8_bg_color'] ) ) ? esc_attr( $options['s8_bg_color'] ) : '';
			$s8_bg_color_on_hover    = ( isset( $options['s8_bg_color_on_hover'] ) ) ? esc_attr( $options['s8_bg_color_on_hover'] ) : '';
			$s8_icon_color           = ( isset( $options['s8_icon_color'] ) ) ? esc_attr( $options['s8_icon_color'] ) : '';
			$s8_icon_color_on_hover  = ( isset( $options['s8_icon_color_on_hover'] ) ) ? esc_attr( $options['s8_icon_color_on_hover'] ) : '';
			$icon_position_value     = ( isset( $options['s8_icon_position'] ) ) ? esc_attr( $options['s8_icon_position'] ) : '';
			$s8_text_size            = ( isset( $options['s8_text_size'] ) ) ? esc_attr( $options['s8_text_size'] ) : '';
			$s8_icon_size            = ( isset( $options['s8_icon_size'] ) ) ? esc_attr( $options['s8_icon_size'] ) : '';
			$s8_btn_size             = ( isset( $options['s8_btn_size'] ) ) ? esc_attr( $options['s8_btn_size'] ) : '';
			$s8_m_fullwidth_checkbox = ( isset( $options['s8_m_fullwidth'] ) ) ? esc_attr( $options['s8_m_fullwidth'] ) : '';

			$s8_m_fullwidth_css = '';
			if ( '1' !== $s8_m_fullwidth_checkbox ) {
				$s8_m_fullwidth_css = 'display:none;';
			}

			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s8" data-collapsible="accordion" data-style='8'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Style 8', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- text color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s8_txt_color" class="ht-ctc-color" data-default-color="#ffffff" name="ht_ctc_s8[s8_txt_color]" value="<?php echo esc_attr( $s8_txt_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='color' data-update-selector='.ctc_s_8 .s8_span'>
			</div>
		</div>

		<!-- text color on hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s8_txt_color_on_hover" class="ht-ctc-color" data-default-color="#ffffff" name="ht_ctc_s8[s8_txt_color_on_hover]" value="<?php echo esc_attr( $s8_txt_color_on_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>

		<!-- background color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s8_bg_color" class="ht-ctc-color" data-default-color="#26a69a" name="ht_ctc_s8[s8_bg_color]" value="<?php echo esc_attr( $s8_bg_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='background-color' data-update-selector='.ctc_s_8 .s_8'>
			</div>
		</div>

		<!-- background color on hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Background Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s8_bg_color_on_hover" class="ht-ctc-color" data-default-color="#26a69a" name="ht_ctc_s8[s8_bg_color_on_hover]" value="<?php echo esc_attr( $s8_bg_color_on_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>

		<!-- icon color -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Color', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s8_icon_color" class="ht-ctc-color" data-default-color="#ffffff" name="ht_ctc_s8[s8_icon_color]" value="<?php echo esc_attr( $s8_icon_color ); ?>" type="text" style="height: 1.375rem;" data-update-type='fill' data-update-selector='.ctc_s_8 svg path'>
			</div>
		</div>

		<!-- icon color on hover -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Color on Hover', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input id="s8_icon_color_on_hover" class="ht-ctc-color" data-default-color="#ffffff" name="ht_ctc_s8[s8_icon_color_on_hover]" value="<?php echo esc_attr( $s8_icon_color_on_hover ); ?>" type="text" style="height: 1.375rem;">
			</div>
		</div>



		<!-- icon position - left/right -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Icon Position', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="ht_ctc_s8[s8_icon_position]" class="select-2 ctc_no_demo">
					<option value="left" <?php selected( $icon_position_value, 'left' ); ?>>Left</option>
					<option value="right" <?php selected( $icon_position_value, 'right' ); ?>>Right</option>
					<option value="hide" <?php selected( $icon_position_value, 'hide' ); ?>>Hide</option>
				</select>
				<!-- <label>Icon Position</label> -->
			</div>
		</div>


		<!-- Text Size -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Text Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s8[s8_text_size]" value="<?php echo esc_attr( $s8_text_size ); ?>" id="s8_text_size" type="text" class="ctc_oninput" data-update-type='font-size' data-update-selector='.ctc_s_8 .s8_span'>
				<label for="s8_text_size"><?php esc_html_e( 'Text Size  -  E.g. 12px', 'click-to-chat-for-whatsapp' ); ?></label>
				<span class="helper-text"><?php esc_html_e( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ); ?></span>
			</div>
		</div>

		<!-- Icon Size -->
		<div class="row">
			<div class="col s6">
				<p>Icon Size</p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s8[s8_icon_size]" value="<?php echo esc_attr( $s8_icon_size ); ?>" id="s8_icon_size" type="text" class="ctc_oninput" data-update-type='height' data-update-type-2='width' data-update-selector='.ctc_s_8 svg'>
				<label for="s8_icon_size"><?php esc_html_e( 'Icon Size  -  E.g. 16px', 'click-to-chat-for-whatsapp' ); ?></label>
				<span class="helper-text"><?php esc_html_e( 'Leave blank for default settings', 'click-to-chat-for-whatsapp' ); ?></span>
			</div>
		</div>

		<!-- button size - btn, btn-large -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Button Size', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<select name="ht_ctc_s8[s8_btn_size]" class="select-2 ctc_no_demo">
					<option value="btn" <?php selected( $s8_btn_size, 'btn' ); ?>><?php esc_html_e( 'Normal', 'click-to-chat-for-whatsapp' ); ?></option>
					<option value="btn-large" <?php selected( $s8_btn_size, 'btn-large' ); ?>><?php esc_html_e( 'Large', 'click-to-chat-for-whatsapp' ); ?></option>
				</select>
			</div>
		</div>

		<!-- Full Width on Mobile -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6 cs_m_fullwidth">
				<p>
					<label>
						<input name="<?php echo esc_attr( $dbrow ); ?>[s8_m_fullwidth]" type="checkbox" value="1" <?php checked( $s8_m_fullwidth_checkbox, 1 ); ?> class="ctc_no_demo" id="s8_m_fullwidth" />
						<span><?php esc_html_e( 'Full Width on Mobile', 'click-to-chat-for-whatsapp' ); ?></span>
					</label>
				</p>

				<p class="m_fullwidth_description description" style="<?php echo esc_attr( $s8_m_fullwidth_css ); ?>">
					Set position at <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat#position_to_place' ) ); ?>" target="_blank"> Click to Chat → Position to Place (Mobile)</a>
				</p>
			</div>

			<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/style-8/"><?php esc_html_e( 'Style-8', 'click-to-chat-for-whatsapp' ); ?></a></p>
		</div>
		</li>
		</ul>
		
			<?php
		}


		/**
		 * Style 99: Custom image.
		 */
		public function ht_ctc_s99_cb() {

			$options                 = get_option( 'ht_ctc_s99' );
			$s_99_dekstop_img_url    = ( isset( $options['s99_dekstop_img_url'] ) ) ? esc_attr( $options['s99_dekstop_img_url'] ) : '';
			$s_99_mobile_img_url     = ( isset( $options['s99_mobile_img_url'] ) ) ? esc_attr( $options['s99_mobile_img_url'] ) : '';
			$s_99_desktop_img_height = ( isset( $options['s99_desktop_img_height'] ) ) ? esc_attr( $options['s99_desktop_img_height'] ) : '';
			$s_99_desktop_img_width  = ( isset( $options['s99_desktop_img_width'] ) ) ? esc_attr( $options['s99_desktop_img_width'] ) : '';
			$s_99_mobile_img_height  = ( isset( $options['s99_mobile_img_height'] ) ) ? esc_attr( $options['s99_mobile_img_height'] ) : '';
			$s_99_mobile_img_width   = ( isset( $options['s99_mobile_img_width'] ) ) ? esc_attr( $options['s99_mobile_img_width'] ) : '';
			?>
		<ul class="collapsible ht_ctc_customize_style ht_ctc_s99" data-collapsible="accordion" data-style='99'>
		<li>
		<div class="collapsible-header"><?php esc_html_e( 'Add your own image / GIF (Style-99)', 'click-to-chat-for-whatsapp' ); ?>
			<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="collapsible-body">

		<!-- Image URL - Desktop -->
		<div class="row">
			<!-- <div class="col s6">
				<p>Image URL</p>
			</div> -->
			<div class="input-field col s12">
				<input name="ht_ctc_s99[s99_dekstop_img_url]" value="<?php echo esc_attr( $s_99_dekstop_img_url ); ?>" id="s99_dekstop_img_url" type="text" class="ctc_no_demo" >
				<label for="s99_dekstop_img_url"><?php esc_html_e( 'Image URL - Desktop', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- Image URL - Mobile -->
		<div class="row">
			<!-- <div class="col s6">
				<p>Image URL</p>
			</div> -->
			<div class="input-field col s12">
				<input name="ht_ctc_s99[s99_mobile_img_url]" value="<?php echo esc_attr( $s_99_mobile_img_url ); ?>" id="s99_mobile_img_url" type="text" class="ctc_no_demo" >
				<label for="s99_mobile_img_url"><?php esc_html_e( 'Image URL - Mobile', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- Desktop - Image Height -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Desktop - Image Height', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s99[s99_desktop_img_height]" value="<?php echo esc_attr( $s_99_desktop_img_height ); ?>" id="s99_desktop_img_height" type="text" class="ctc_no_demo" >
				<label for="s99_desktop_img_height"><?php esc_html_e( 'Desktop - Image Height', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- Desktop - Image Width -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Desktop - Image Width', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s99[s99_desktop_img_width]" value="<?php echo esc_attr( $s_99_desktop_img_width ); ?>" id="s99_desktop_img_width" type="text" class="ctc_no_demo" >
				<label for="s99_desktop_img_width"><?php esc_html_e( 'Desktop - Image Width', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- Mobile - Image Height -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Mobile - Image Height', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s99[s99_mobile_img_height]" value="<?php echo esc_attr( $s_99_mobile_img_height ); ?>" id="s99_mobile_img_height" type="text" class="ctc_no_demo" >
				<label for="s99_mobile_img_height"><?php esc_html_e( 'Mobile - Image Height', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<!-- Mobile - Image Width -->
		<div class="row">
			<div class="col s6">
				<p><?php esc_html_e( 'Mobile - Image Width', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
			<div class="input-field col s6">
				<input name="ht_ctc_s99[s99_mobile_img_width]" value="<?php echo esc_attr( $s_99_mobile_img_width ); ?>" id="s99_mobile_img_width" type="text" class="ctc_no_demo" >
				<label for="s99_mobile_img_width"><?php esc_html_e( 'Mobile - Image Width', 'click-to-chat-for-whatsapp' ); ?></label>
			</div>
		</div>

		<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/style-99/">Add your own image / GIF (Style-99)</a></p>

		</div>
		</div>
		</li>
		</ul>
		
			<?php
		}








		/**
		 * Sanitize each setting field as needed
		 *
		 * @param array $input Contains all settings fields as array keys.
		 */
		public function options_sanitize( $input ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( 'not allowed to modify - please contact admin ' );
			}

			$new_input = array();

			// add px suffix, remove spaces
			$add_suffix = array(
				's1_icon_size',
				's2_img_size',
				's3_img_size',
				's3_padding',
				's4_img_size',
				's5_img_height',
				's5_img_width',
				's5_content_height',
				's5_content_width',
				's7_icon_size',
				's7_border_size',
				's7_border_radius',
				's8_text_size',
				's8_icon_size',
				's99_desktop_img_height',
				's99_desktop_img_width',
				's99_mobile_img_height',
				's99_mobile_img_width',
				'cta_font_size',
			);

			foreach ( $input as $key => $value ) {
				if ( isset( $input[ $key ] ) ) {

					if ( in_array( $key, $add_suffix, true ) ) {

						$input[ $key ] = str_replace( ' ', '', $input[ $key ] );

						if ( is_numeric( $input[ $key ] ) ) {
							$input[ $key ] = $input[ $key ] . 'px';
						}
						if ( 's5_img_height' === $key || 's5_img_width' === $key || 's5_content_height' === $key ) {
							$input[ $key ] = ( '' === $input[ $key ] ) ? '70px' : $input[ $key ];
						}
						if ( 's5_content_width' === $key ) {
							$input[ $key ] = ( '' === $input[ $key ] ) ? '270px' : $input[ $key ];
						}
						if ( 's7_icon_size' === $key ) {
							$input[ $key ] = ( '' === $input[ $key ] ) ? '24px' : $input[ $key ];
						}
						if ( 's7_border_size' === $key ) {
							$input[ $key ] = ( '' === $input[ $key ] ) ? '12px' : $input[ $key ];
						}
						if ( 's7_border_radius' === $key ) {
							$input[ $key ] = ( '' === $input[ $key ] ) ? '4px' : $input[ $key ];
						}
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
					} else {
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
					}
				}
			}

			return $new_input;
		}
	}

	$ht_ctc_admin_customize_styles = new HT_CTC_Admin_Customize_Styles();

	add_action( 'admin_menu', array( $ht_ctc_admin_customize_styles, 'menu' ) );
	add_action( 'admin_init', array( $ht_ctc_admin_customize_styles, 'settings' ) );

} // END class_exists check
