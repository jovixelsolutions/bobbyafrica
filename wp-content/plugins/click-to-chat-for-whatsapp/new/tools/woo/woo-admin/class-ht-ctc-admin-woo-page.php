<?php
/**
 * WooCommerce settings page - admin
 *
 * @package Click_To_Chat
 * @subpackage admin
 * @since 3.3.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Woo_Page' ) ) {

	/**
	 * Handles WooCommerce settings page administration.
	 */
	class HT_CTC_Admin_Woo_Page {

		/**
		 * Initialize the WooCommerce admin page.
		 */
		public function __construct() {
			$this->start();
		}

		/**
		 * Start the admin page by registering hooks.
		 */
		public function start() {
			add_action( 'admin_menu', array( $this, 'menu' ) );
			add_action( 'admin_init', array( $this, 'settings' ) );
		}

		/**
		 * Register the WooCommerce submenu page.
		 */
		public function menu() {

			add_submenu_page(
				'click-to-chat',
				'WooCommerce page',
				'WooCommerce',
				'manage_options',
				'click-to-chat-woocommerce',
				array( $this, 'settings_page' )
			);
		}

		/**
		 * Render the WooCommerce settings page.
		 */
		public function settings_page() {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			?>

		<div class="wrap ctc-admin-woo-page">

			<?php settings_errors(); ?>

			<!-- full row -->
			<div class="row" style="display:flex; flex-wrap:wrap;">

				<div class="col s12 m12 l12 xl9 options">
					<form action="options.php" method="post" class="">
						<?php settings_fields( 'ht_ctc_woo_page_settings_fields' ); ?>
						<?php do_settings_sections( 'ht_ctc_woo_page_settings_sections_do' ); ?>
						<?php submit_button(); ?>
					</form>
				</div>

				<!-- sidebar content -->
				<div class="col s12 m12 l7 xl3 ht-ctc-admin-sidebar ht-ctc-admin-woo-sidebar sticky-sidebar">
					<?php // include_once HT_CTC_PLUGIN_DIR .'new/admin/admin_commons/admin-sidebar-content.php'; ?>
				</div>
				
			</div>

			<!-- new row - After settings page  -->
			<div class="row">
			</div>

		</div>

			<?php
		}


		/**
		 * Register WooCommerce settings, sections and fields.
		 */
		public function settings() {

			// WooCommerce chat feautes
			register_setting( 'ht_ctc_woo_page_settings_fields', 'ht_ctc_woo_options', array( $this, 'options_sanitize' ) );

			// if ( isset($_GET) && isset($_GET['page']) && 'click-to-chat-woocommerce' === $_GET['page'] ) {
			add_settings_section( 'ht_ctc_woo_page_settings_sections_add', '', array( $this, 'chat_settings_section_cb' ), 'ht_ctc_woo_page_settings_sections_do' );
			add_settings_field( 'ctc_woocommerce', __( 'WooCommerce', 'click-to-chat-for-whatsapp' ), array( $this, 'ctc_woocommerce_cb' ), 'ht_ctc_woo_page_settings_sections_do', 'ht_ctc_woo_page_settings_sections_add' );
			// }
		}


		/**
		 * Output section header for WooCommerce chat settings.
		 */
		public function chat_settings_section_cb() {
			?>
		<h1 id="woo_settings">Click to Chat - WooCommerce</h1>
		<p class="description">
			<strong>Overwrite</strong>: 
			<a class="open_tab" data-tab="overwrite_tab-1" href="#overwrite_tab-1">Single product</a> | <a class="open_tab" data-tab="overwrite_tab-2" href="#overwrite_tab-2">Shop, Cart, Checkout, Account</a> <br>
			<strong>Add WhatsApp</strong>:
			<a class="open_tab" data-tab="add_whatsapp_tab-1" href="#add_whatsapp_tab-1">Single product</a> | <a class="open_tab" data-tab="add_whatsapp_tab-2" href="#add_whatsapp_tab-2">Shop</a> | <a class="open_tab" data-tab="add_whatsapp_tab-3" href="#add_whatsapp_tab-3">Advanced</a> 
		</p>
		<br>
			<?php
			do_action( 'ht_ctc_ah_admin' );
		}

		/**
		 * Single product pages
		 *
		 * @var [woo_is_single] - floating style for single product pages
		 */
		public function ctc_woocommerce_cb() {

			$woo_options = get_option( 'ht_ctc_woo_options' );
			$chat        = get_option( 'ht_ctc_chat_options' );
			$dbrow       = 'ht_ctc_woo_options';

			/**
			 * Single product page..
			 */
			// pre filled
			$woo_pre_filled = ( isset( $woo_options['woo_pre_filled'] ) ) ? esc_attr( $woo_options['woo_pre_filled'] ) : '';
			$pf_placeholder = "Hello {site} \nLike to buy {product}, {url}";
			// call to action
			$woo_call_to_action     = ( isset( $woo_options['woo_call_to_action'] ) ) ? esc_attr( $woo_options['woo_call_to_action'] ) : '';
			$ctc_placeholder        = 'Buy {product}';
			$single_ctc_placeholder = 'WhatsApp Order';

			// Add styles at woo page position
			$woo_position          = ( isset( $woo_options['woo_position'] ) ) ? esc_attr( $woo_options['woo_position'] ) : '';
			$woo_style             = ( isset( $woo_options['woo_style'] ) ) ? esc_attr( $woo_options['woo_style'] ) : '8';
			$woo_single_block_type = ( isset( $woo_options['woo_single_block_type'] ) ) ? esc_attr( $woo_options['woo_single_block_type'] ) : 'inline-block';

			$woo_places = array(
				'select'                                   => '-- Select --',
				'woocommerce_before_main_content'          => 'Before Main Content',
				'woocommerce_before_single_product'        => 'Before Product',
				'woocommerce_before_single_product_summary' => 'Before Product Summary',
				'woocommerce_single_product_summary'       => 'Product Summary',
				'woocommerce_before_add_to_cart_form'      => 'Before Add to Cart Form',
				'woocommerce_before_add_to_cart_button'    => 'Before Cart Button',
				'woocommerce_after_add_to_cart_button'     => 'After Cart Button',
				'woocommerce_after_add_to_cart_form'       => 'After Add to Cart Form',
				'woocommerce_after_single_product'         => 'After Product',
				'woocommerce_after_single_product_summary' => 'After Product Summary',
			);

			// $woo_places = apply_filters( 'ht_ctc_fh_admin_woo_places', $woo_places );

			$woo_single_position_center = ( isset( $woo_options['woo_single_position_center'] ) ) ? esc_attr( $woo_options['woo_single_position_center'] ) : '';
			$woo_single_layout_cart_btn = ( isset( $woo_options['woo_single_layout_cart_btn'] ) ) ? esc_attr( $woo_options['woo_single_layout_cart_btn'] ) : '';
			$woo_single_margin_top      = ( isset( $woo_options['woo_single_margin_top'] ) ) ? esc_attr( $woo_options['woo_single_margin_top'] ) : '';
			$woo_single_margin_right    = ( isset( $woo_options['woo_single_margin_right'] ) ) ? esc_attr( $woo_options['woo_single_margin_right'] ) : '';
			$woo_single_margin_bottom   = ( isset( $woo_options['woo_single_margin_bottom'] ) ) ? esc_attr( $woo_options['woo_single_margin_bottom'] ) : '';
			$woo_single_margin_left     = ( isset( $woo_options['woo_single_margin_left'] ) ) ? esc_attr( $woo_options['woo_single_margin_left'] ) : '';
			$woo_single_margin_unit     = ( isset( $woo_options['woo_single_margin_unit'] ) ) ? esc_attr( $woo_options['woo_single_margin_unit'] ) : 'px';

			/**
			 * Woo - shop page
			 */
			$woo_shop_style           = ( isset( $woo_options['woo_shop_style'] ) ) ? esc_attr( $woo_options['woo_shop_style'] ) : '1';
			$woo_shop_pre_filled      = ( isset( $woo_options['woo_shop_pre_filled'] ) ) ? esc_attr( $woo_options['woo_shop_pre_filled'] ) : '';
			$woo_shop_call_to_action  = ( isset( $woo_options['woo_shop_call_to_action'] ) ) ? esc_attr( $woo_options['woo_shop_call_to_action'] ) : '';
			$woo_shop_layout_cart_btn = ( isset( $woo_options['woo_shop_layout_cart_btn'] ) ) ? esc_attr( $woo_options['woo_shop_layout_cart_btn'] ) : '';
			$woo_shop_add_whatsapp    = ( isset( $woo_options['woo_shop_add_whatsapp'] ) ) ? esc_attr( $woo_options['woo_shop_add_whatsapp'] ) : '';

			$woo_shop_position_center = ( isset( $woo_options['woo_shop_position_center'] ) ) ? esc_attr( $woo_options['woo_shop_position_center'] ) : '';
			$woo_shop_margin_top      = ( isset( $woo_options['woo_shop_margin_top'] ) ) ? esc_attr( $woo_options['woo_shop_margin_top'] ) : '';
			$woo_shop_margin_right    = ( isset( $woo_options['woo_shop_margin_right'] ) ) ? esc_attr( $woo_options['woo_shop_margin_right'] ) : '';
			$woo_shop_margin_bottom   = ( isset( $woo_options['woo_shop_margin_bottom'] ) ) ? esc_attr( $woo_options['woo_shop_margin_bottom'] ) : '';
			$woo_shop_margin_left     = ( isset( $woo_options['woo_shop_margin_left'] ) ) ? esc_attr( $woo_options['woo_shop_margin_left'] ) : '';
			$woo_shop_margin_unit     = ( isset( $woo_options['woo_shop_margin_unit'] ) ) ? esc_attr( $woo_options['woo_shop_margin_unit'] ) : 'px';

			?>

		<!-- overwrite settings -->
		<div class="margin_bottom_15"><strong class="description ht_ctc_subtitle">Overwrite Settings for WooCommerce Pages</strong></div>

		<div class="row">

			<div class="col s12">
				<ul class="tabs tabs-fixed-width">
					<li class="tab col s3 md_tab_li"><a href="#overwrite_tab-1"><?php esc_html_e( 'Single Product Pages', 'click-to-chat-for-whatsapp' ); ?></a></li>
					<li class="tab col s3 md_tab_li"><a href="#overwrite_tab-2"><?php esc_html_e( 'Shop, cart, checkout, Account', 'click-to-chat-for-whatsapp' ); ?></a></li>
				</ul>
			</div>

			<!-- overwrite: single product page -->
			<div id="overwrite_tab-1" class="col s12 md_tab">
				<div class="ctc_md_tab">
					<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/woocommerce-single-product-pages/"><?php esc_html_e( 'WooCommerce Single Product pages', 'click-to-chat-for-whatsapp' ); ?></a></p>
					<p class="description">Variables: {product}, {price}, {regular_price}, {sku}, {site}, {url}, {title} </p>
					<p class="description">{{price}}: Formatted price with currency sign (e.g., $1,480,000.00).</p>
					<p class="description">Leave blank to get value from main settings</p>
					<br><br>

					<!-- prefilled message -->
					<div class="row ctc_side_by_side">
						<div class="input-field col s12 md_tab">
							<textarea name="ht_ctc_woo_options[woo_pre_filled]" id="woo_pre_filled" class="materialize-textarea input-margin" style="min-height: 64px;" placeholder="<?php echo esc_attr( $pf_placeholder ); ?>"><?php echo esc_textarea( $woo_pre_filled ); ?></textarea>
							<label for="woo_pre_filled"><?php esc_html_e( 'Pre-filled message', 'click-to-chat-for-whatsapp' ); ?></label>
						</div>
					</div>


					<!-- Call to Action -->
					<div class="row ctc_side_by_side">
						<div class="input-field col s12 md_tab">
							<input name="ht_ctc_woo_options[woo_call_to_action]" value="<?php echo esc_attr( $woo_call_to_action ); ?>" id="woo_call_to_action" type="text" class="input-margin" placeholder="<?php echo esc_attr( $ctc_placeholder ); ?>">
							<label for="woo_call_to_action"><?php esc_html_e( 'Call to Action', 'click-to-chat-for-whatsapp' ); ?></label>
						</div>
					</div>

					<?php
					do_action( 'ht_ctc_ah_admin_after_woo_overwrite_single_settings' );
					?>
						
					<br><br>
				</div>
			</div>

			<!-- overwrite: shop, cart, checkout, account - page level settings -->
			<div id="overwrite_tab-2" class="col s12 md_tab">
				<div class="ctc_md_tab">
					<!-- Page Level settings - for WooCommerce pages -->
					<p class="description">
						<span>At <a target="_blank" href="https://holithemes.com/plugins/click-to-chat/change-values-at-page-level/">Page Level Settings</a> can overwrite: Number, Call to Action, Prefilled Message, display settings. 
						(<a target="_blank" href="https://holithemes.com/plugins/click-to-chat/pricing/">PRO</a>: Greetings, Style, Time Delay, Scroll Delay)
						</span>
					</p>
					<br>
					<?php
					$admin_url = admin_url();

					if ( function_exists( 'wc_get_page_id' ) ) {
						$shop_page_id   = wc_get_page_id( 'shop' );
						$shop_admin_url = "{$admin_url}post.php?post={$shop_page_id}&action=edit";

						$cart_page_id   = wc_get_page_id( 'cart' );
						$cart_admin_url = "{$admin_url}post.php?post={$cart_page_id}&action=edit";

						$checkout_page_id   = wc_get_page_id( 'checkout' );
						$checkout_admin_url = "{$admin_url}post.php?post={$checkout_page_id}&action=edit";

						$myaccount_page_id   = wc_get_page_id( 'myaccount' );
						$myaccount_admin_url = "{$admin_url}post.php?post={$myaccount_page_id}&action=edit";
						?>
						<p class="description"><a target="_blank" href="<?php echo esc_url( $shop_admin_url ); ?>">Edit Shop Page</a> </p>
						<p class="description"><a target="_blank" href="<?php echo esc_url( $cart_admin_url ); ?>">Edit Cart Page</a> </p>
						<p class="description"><a target="_blank" href="<?php echo esc_url( $checkout_admin_url ); ?>">Edit Checkout Page</a> </p>
						<p class="description"><a target="_blank" href="<?php echo esc_url( $myaccount_admin_url ); ?>">Edit My Account Page</a> </p>
						<?php
					}
					?>
					<br><br>
				</div>
			</div>


		</div>


		<br><br>
		<!-- Add WhatsApp -->
		<div class="margin_bottom_15"><strong class="description ht_ctc_subtitle">Add WhatsApp</strong></div>
		<div class="row">
			<div class="col s12">
				<ul class="tabs tabs-fixed-width">
					<li class="tab col s3 md_tab_li"><a href="#add_whatsapp_tab-1"><?php esc_html_e( 'Single Product Pages', 'click-to-chat-for-whatsapp' ); ?></a></li>
					<li class="tab col s3 md_tab_li"><a href="#add_whatsapp_tab-2"><?php esc_html_e( 'Shop Page', 'click-to-chat-for-whatsapp' ); ?></a></li>
					<li class="tab col s3 md_tab_li"><a href="#add_whatsapp_tab-3"><?php esc_html_e( 'Advanced', 'click-to-chat-for-whatsapp' ); ?></a></li>
				</ul>
			</div>

			<div id="add_whatsapp_tab-1" class="col s12 md_tab">
				<div class="ctc_md_tab">
					<!-- Add button/icon -->
					<p class="description" style="margin-bottom:15px;"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/add-whatsapp-in-woocommerce-single-product-pages/"><?php esc_html_e( 'Add WhatsApp in WooCommerce Single Product pages', 'click-to-chat-for-whatsapp' ); ?></a></p>

					<div class="row ctc_side_by_side">
						<div class="col s6" style="padding-top: 14px;">
							<p><?php esc_html_e( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ); ?>:</p>
						</div>
						<div class="input-field col s6">
							<select name="<?php echo esc_attr( $dbrow ); ?>[woo_position]" class="woo_single_position_select">
								<?php
								foreach ( $woo_places as $key => $value ) {
									?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php echo ( $key === $woo_position ) ? 'SELECTED' : ''; ?> ><?php echo esc_html( $value ); ?></option>
									<?php
								}
								?>
							</select>
							<!-- <p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/add-whatsapp-in-woocommerce-single-product-pages/#pro_block">Positions</a></p> -->
						</div>
					</div>

					<!-- style -->
					<div class="row ctc_init_display_none woo_single_position_settings ctc_side_by_side">
						<div class="col s6" style="padding-top: 14px;">
							<p><?php esc_html_e( 'Select Style', 'click-to-chat-for-whatsapp' ); ?></p>
						</div>
						<div class="input-field col s6">
							<!-- Todo: test might be string.. where using ===. -->
							<select name="<?php echo esc_attr( $dbrow ); ?>[woo_style]" class="woo_single_style_select">
									<option value="1" <?php echo ( '1' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-1', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="2" <?php echo ( '2' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-2', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="3" <?php echo ( '3' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-3', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="3_1" <?php echo ( '3_1' === $woo_style ) ? 'SELECTED' : ''; ?> >&emsp;<?php esc_html_e( 'Style-3 Extend', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="4" <?php echo ( '4' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-4', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="5" <?php echo ( '5' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-5', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="7" <?php echo ( '7' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-7', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="7_1" <?php echo ( '7_1' === $woo_style ) ? 'SELECTED' : ''; ?> >&emsp;<?php esc_html_e( 'Style-7 Extend', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="8" <?php echo ( '8' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-8', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="99" <?php echo ( '99' === $woo_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Add your own image / GIF (Style-99)', 'click-to-chat-for-whatsapp' ); ?></option>
							</select>
							<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/list-of-styles/"><?php esc_html_e( 'List of Styles', 'click-to-chat-for-whatsapp' ); ?></a> &emsp; | &emsp; <span><a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-customize-styles' ) ); ?>">Customize the styles</a></span> </p>
							<p class="description"><strong>Recommended Styles: 1, 4, 8</strong></p>
						</div>
					</div>

					<p class="description ctc_init_display_none woo_single_position_settings"><a  class="open_tab" data-tab="overwrite_tab-1" href="#overwrite_tab-1" style="margin-bottom: 15px;">Prefilled, Call to action</a></p>

					<p class="description ctc_init_display_none woo_single_position_settings">These styles and their position appears based on how the Theme is developed. </p>
					<br>

					<details open class="ctc_details ctc_init_display_none woo_single_position_settings">
						<summary style="padding: 5px; background-color: #eeeeee; cursor: pointer; width: fit-content;">Adjust settings compatible with the theme design</summary>

						<!-- style 1, 8 - add to cart layout -->
						<div class="woo_single_position_settings_cart_layout" style="display: none;">
							<div class="row ctc_side_by_side">
								<div class="col s6" style="padding-top: 14px;">
									<p>Button layout <br><span style="font-size:0.8em;">WhatsApp button looks like 'Add to Cart' button</span></p>
								</div>
								<div class="input-field col s6">
									<label>
										<input name="<?php echo esc_attr( $dbrow ); ?>[woo_single_layout_cart_btn]" type="checkbox" value="1" <?php checked( $woo_single_layout_cart_btn, 1 ); ?> id="woo_single_layout_cart_btn" />
										<span>Displays like 'Add to Cart' button</span>
									</label>
								</div>
							</div>
						</div>

						<!-- display - center -->
						<div class="row ctc_side_by_side woo_single_position_settings" style="display: none;">
							<div class="col s6" style="padding-top: 14px;">
								<p><?php esc_html_e( 'Display Center', 'click-to-chat-for-whatsapp' ); ?></p>
							</div>
							<div class="input-field col s6">
								<label>
									<input name="<?php echo esc_attr( $dbrow ); ?>[woo_single_position_center]" type="checkbox" value="1" <?php checked( $woo_single_position_center, 1 ); ?> id="woo_single_position_center" />
									<span><?php esc_html_e( 'Display center within available space', 'click-to-chat-for-whatsapp' ); ?></span>
								</label>
							</div>
						</div>
						
						<!-- display: block, inline-block ..  -->
						<div class="row ctc_side_by_side ctc_init_display_none woo_single_position_settings">
							<div class="col s6" style="padding-top: 14px;">
								<p><?php esc_html_e( 'Display Block Type', 'click-to-chat-for-whatsapp' ); ?></p>
							</div>
							<div class="input-field col s6">
								<select name="<?php echo esc_attr( $dbrow ); ?>[woo_single_block_type]" class="">
									<option value="block" <?php echo ( 'block' === $woo_single_block_type ) ? 'SELECTED' : ''; ?> >block</option>
									<option value="inline" <?php echo ( 'inline' === $woo_single_block_type ) ? 'SELECTED' : ''; ?> >inline</option>
									<option value="inline-block" <?php echo ( 'inline-block' === $woo_single_block_type ) ? 'SELECTED' : ''; ?> >inline-block</option>
								</select>
								<p class="woo_single_position_center_checked_content" style="display: none;">Recommended type: 'block'</p>
							</div>
						</div>
						
						<!-- margin -->
						<div class="row ctc_side_by_side ctc_init_display_none woo_single_position_settings">
							<div class="col s6" style="padding-top: 14px;">
								<p><?php esc_html_e( 'Spacing (Margin)', 'click-to-chat-for-whatsapp' ); ?></p>
							</div>
							<div class="input-field col s6">
								<div  style="display: flex; margin-bottom: 1px;">
									<input name="ht_ctc_woo_options[woo_single_margin_top]" value="<?php echo esc_attr( $woo_single_margin_top ); ?>" id="woo_single_margin_top" type="text" style="display:inline; margin-right:4px;" class="input-margin tooltipped" placeholder="Top" data-position="top" data-tooltip="<?php esc_attr_e( 'Top', 'click-to-chat-for-whatsapp' ); ?>">
									<input name="ht_ctc_woo_options[woo_single_margin_bottom]" value="<?php echo esc_attr( $woo_single_margin_bottom ); ?>" id="woo_single_margin_bottom" type="text" style="display:inline; margin-right:8px;" class="input-margin tooltipped" placeholder="Bottom" data-position="bottom" data-tooltip="<?php esc_attr_e( 'Bottom', 'click-to-chat-for-whatsapp' ); ?>">
									<input name="ht_ctc_woo_options[woo_single_margin_left]" value="<?php echo esc_attr( $woo_single_margin_left ); ?>" id="woo_single_margin_left" type="text" style="display:inline; margin-right:4px; margin-left:4px;" class="input-margin tooltipped" placeholder="Left" data-position="left" data-tooltip="<?php esc_attr_e( 'Left', 'click-to-chat-for-whatsapp' ); ?>">
									<input name="ht_ctc_woo_options[woo_single_margin_right]" value="<?php echo esc_attr( $woo_single_margin_right ); ?>" id="woo_single_margin_right" type="text" style="display:inline; " class="input-margin tooltipped" placeholder="Right" data-position="right" data-tooltip="<?php esc_attr_e( 'Right', 'click-to-chat-for-whatsapp' ); ?>">
								</div>
								<span class="helper-text">Top, Bottom, Left, Right <span> E.g. 10px, 50%</span> </span>
							</div>
						</div>

					</details>
					

					
				</div>
			</div>

			<div id="add_whatsapp_tab-2" class="col s12 md_tab">
				<div class="ctc_md_tab">
					<!-- woo shop page -->
					<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/whatsapp-chat-in-woocommerce-shop-page/"><?php esc_html_e( 'WooCommerce Shop page', 'click-to-chat-for-whatsapp' ); ?></a></p>

					<!-- At WooCommerce shop pages, loop.. -->
					<div class="row ctc_side_by_side">
						<div class="col s6" style="padding-top: 14px;">
							<p><?php esc_html_e( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ); ?>:</p>
						</div>
						<div class="input-field col s6">
							<label>
								<input name="<?php echo esc_attr( $dbrow ); ?>[woo_shop_add_whatsapp]" type="checkbox" value="1" <?php checked( $woo_shop_add_whatsapp, 1 ); ?> id="woo_shop_add_whatsapp" />
								<span><?php esc_html_e( 'At Products - Archive, Shop Page', 'click-to-chat-for-whatsapp' ); ?></span>
							</label>
						</div>
					</div>
					
					<!-- prefilled message -->
					<div class="row ctc_side_by_side ctc_init_display_none woo_shop_add_whatsapp_settings">
						<div class="input-field col s12">
							<textarea name="ht_ctc_woo_options[woo_shop_pre_filled]" id="woo_shop_pre_filled" class="materialize-textarea input-margin" style="min-height: 84px;" placeholder="<?php echo esc_attr( $pf_placeholder ); ?>"><?php echo esc_textarea( $woo_shop_pre_filled ); ?></textarea>
							<label for="woo_shop_pre_filled"><?php esc_html_e( 'Pre-filled message', 'click-to-chat-for-whatsapp' ); ?></label>
							<p class="description">pre-filled, call-to-action: if blank, get values from page-level settings if not from the main settings</p>
						</div>
					</div>


					<!-- Call to Action -->
					<div class="row ctc_side_by_side ctc_init_display_none woo_shop_add_whatsapp_settings">
						<div class="input-field col s12">
							<input name="ht_ctc_woo_options[woo_shop_call_to_action]" value="<?php echo esc_attr( $woo_shop_call_to_action ); ?>" id="woo_shop_call_to_action" type="text" class="input-margin" placeholder="<?php echo esc_attr( $single_ctc_placeholder ); ?>">
							<label for="woo_shop_call_to_action"><?php esc_html_e( 'Call to Action', 'click-to-chat-for-whatsapp' ); ?></label>
						</div>
					</div>

					<!-- style -->
					<div class="row ctc_side_by_side ctc_init_display_none woo_shop_add_whatsapp_settings">
						<div class="col s6" style="padding-top: 14px;">
							<p><?php esc_html_e( 'Select Style', 'click-to-chat-for-whatsapp' ); ?></p>
						</div>
						<div class="input-field col s6">
							<?php
							// the output: string.. where using ===. '1', '2', '3', '3_1', '4', '5', '7', '7_1', '8', '99'
							?>
							<select name="<?php echo esc_attr( $dbrow ); ?>[woo_shop_style]" class="woo_shop_style">
									<option value="1" <?php echo ( '1' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-1', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="2" <?php echo ( '2' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-2', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="3" <?php echo ( '3' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-3', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="3_1" <?php echo ( '3_1' === $woo_shop_style ) ? 'SELECTED' : ''; ?> >&emsp;<?php esc_html_e( 'Style-3 Extend', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="4" <?php echo ( '4' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-4', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="5" <?php echo ( '5' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-5', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="7" <?php echo ( '7' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-7', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="7_1" <?php echo ( '7_1' === $woo_shop_style ) ? 'SELECTED' : ''; ?> >&emsp;<?php esc_html_e( 'Style-7 Extend', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="8" <?php echo ( '8' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Style-8', 'click-to-chat-for-whatsapp' ); ?></option>
									<option value="99" <?php echo ( '99' === $woo_shop_style ) ? 'SELECTED' : ''; ?> ><?php esc_html_e( 'Add your own image / GIF (Style-99)', 'click-to-chat-for-whatsapp' ); ?></option>
							</select>
							<p class="description"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/list-of-styles/"><?php esc_html_e( 'List of Styles', 'click-to-chat-for-whatsapp' ); ?></a> &emsp; | &emsp; <span><a target="_blank" href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-customize-styles' ) ); ?>">Customize the styles</a></span> </p>
							<p class="description"><b>Recommended Styles: 1, 8</b></p>
						</div>
					</div>

					<details open class="ctc_details ctc_init_display_none woo_shop_add_whatsapp_settings">
						<summary style="padding: 5px; background-color: #eeeeee; cursor: pointer; width: fit-content;">Adjust settings compatible with the theme design</summary>

						<!-- style 1, 8 - shop - add to cart layout -->
						<div class="woo_shop_cart_layout" style="display: ;">
							<div class="row ctc_side_by_side">
								<div class="col s6" style="padding-top: 14px;">
									<p>Button layout <br><span style="font-size:0.8em;">WhatsApp button looks like 'Add to Cart' button</span></p>
								</div>
								<div class="input-field col s6">
									<label>
										<input name="<?php echo esc_attr( $dbrow ); ?>[woo_shop_layout_cart_btn]" type="checkbox" value="1" <?php checked( $woo_shop_layout_cart_btn, 1 ); ?> id="woo_shop_layout_cart_btn" />
										<span>Displays like 'Add to Cart' button</span>
									</label>
								</div>
							</div>
						</div>

						<!-- display - center -->
						<div class="row ctc_side_by_side ctc_init_display_none woo_shop_add_whatsapp_settings">
							<div class="col s6" style="padding-top: 14px;">
								<p><?php esc_html_e( 'Display Center', 'click-to-chat-for-whatsapp' ); ?></p>
							</div>
							<div class="input-field col s6">
								<label>
									<input name="<?php echo esc_attr( $dbrow ); ?>[woo_shop_position_center]" type="checkbox" value="1" <?php checked( $woo_shop_position_center, 1 ); ?> id="woo_shop_position_center" />
									<span><?php esc_html_e( 'Display center', 'click-to-chat-for-whatsapp' ); ?></span>
								</label>
							</div>
						</div>

						<!-- margin -->
						<div class="row ctc_side_by_side ctc_init_display_none woo_shop_add_whatsapp_settings">
							<div class="col s6" style="padding-top: 14px;">
								<p><?php esc_html_e( 'Spacing (Margin)', 'click-to-chat-for-whatsapp' ); ?></p>
							</div>
							<div class="input-field col s6">
								<div  style="display: flex; margin-bottom: 1px;">
									<input name="ht_ctc_woo_options[woo_shop_margin_top]" value="<?php echo esc_attr( $woo_shop_margin_top ); ?>" id="woo_shop_margin_top" type="text" style="display:inline; margin-right:4px;" class="input-margin tooltipped" placeholder="Top" data-position="top" data-tooltip="<?php esc_attr_e( 'Top', 'click-to-chat-for-whatsapp' ); ?>">
									<input name="ht_ctc_woo_options[woo_shop_margin_bottom]" value="<?php echo esc_attr( $woo_shop_margin_bottom ); ?>" id="woo_shop_margin_bottom" type="text" style="display:inline; margin-right:8px;" class="input-margin tooltipped" placeholder="Bottom" data-position="bottom" data-tooltip="<?php esc_attr_e( 'Bottom', 'click-to-chat-for-whatsapp' ); ?>">
									<input name="ht_ctc_woo_options[woo_shop_margin_left]" value="<?php echo esc_attr( $woo_shop_margin_left ); ?>" id="woo_shop_margin_left" type="text" style="display:inline; margin-right:4px;" class="input-margin tooltipped" placeholder="Left" data-position="left" data-tooltip="<?php esc_attr_e( 'Left', 'click-to-chat-for-whatsapp' ); ?>">
									<input name="ht_ctc_woo_options[woo_shop_margin_right]" value="<?php echo esc_attr( $woo_shop_margin_right ); ?>" id="woo_shop_margin_right" type="text" style="display:inline;" class="input-margin tooltipped" placeholder="Right" data-position="right" data-tooltip="<?php esc_attr_e( 'Right', 'click-to-chat-for-whatsapp' ); ?>">
								</div>
								<p class="helper-text">Top, Bottom, Left, Right <span> E.g. 10px, 50%</span> </p>
							</div>
						</div>
						<br>

						<br>
						<p class="description ctc_init_display_none woo_shop_add_whatsapp_settings"><a target="_blank" href="https://holithemes.com/plugins/click-to-chat/whatsapp-chat-in-woocommerce-shop-page/"><?php esc_html_e( 'WooCommerce Shop page', 'click-to-chat-for-whatsapp' ); ?></a></p>
					</details>
					<br><br>
				</div>
			</div>

			<div id="add_whatsapp_tab-3" class="col s12 md_tab">
				<div class="ctc_md_tab">
						<?php

						if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
							?>
						<p class="description">
							PRO: Apply business hours settings to WhatsApp added in WooCommerce Pages (single product, Shop).
						</p>
							<?php
						}

						do_action( 'ht_ctc_ah_admin_after_woo_settings' );
						?>
					<br><br>
				</div>
			</div>


		</div>
		


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

			$editor = array();
			$editor = apply_filters( 'ht_ctc_fh_greetings_setting_editor_values', $editor );

			// formatting api - emoji ..
			include_once HT_CTC_PLUGIN_DIR . 'new/admin/admin_commons/ht-ctc-admin-formatting.php';

			$new_input = array();

			foreach ( $input as $key => $value ) {
				if ( isset( $input[ $key ] ) ) {

					if ( 'woo_pre_filled' === $key || 'woo_shop_pre_filled' === $key ) {
						if ( function_exists( 'sanitize_textarea_field' ) ) {
							$new_input[ $key ] = sanitize_textarea_field( $input[ $key ] );
						} else {
							$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
						}
					} elseif ( in_array( $key, $editor, true ) ) {
						// editor
						if ( ! empty( $input[ $key ] ) && '' !== $input[ $key ] && function_exists( 'ht_ctc_wp_sanitize_text_editor' ) ) {
							$new_input[ $key ] = ht_ctc_wp_sanitize_text_editor( $input[ $key ] );
						} else {
							// save field even if the value is empty..
							$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
						}
					} elseif ( 'woo_single_margin_top' === $key || 'woo_single_margin_bottom' === $key || 'woo_single_margin_left' === $key || 'woo_single_margin_right' === $key || 'woo_shop_margin_top' === $key || 'woo_shop_margin_bottom' === $key || 'woo_shop_margin_left' === $key || 'woo_shop_margin_right' === $key ) {
						$input[ $key ] = str_replace( ' ', '', $input[ $key ] );
						if ( is_numeric( $input[ $key ] ) ) {
							$input[ $key ] = $input[ $key ] . 'px';
						}
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
					} else {
						$new_input[ $key ] = sanitize_text_field( $input[ $key ] );
					}
				}
			}

			// l10n
			foreach ( $input as $key => $value ) {
				if ( 'woo_pre_filled' === $key || 'woo_call_to_action' === $key || 'woo_shop_pre_filled' === $key || 'woo_shop_call_to_action' === $key ) {
					do_action( 'wpml_register_single_string', 'Click to Chat for WhatsApp', $key, $input[ $key ] );
				}
			}

			do_action( 'ht_ctc_ah_admin_after_sanitize' );

			return $new_input;
		}
	}

	new HT_CTC_Admin_Woo_Page();

} // END class_exists check
