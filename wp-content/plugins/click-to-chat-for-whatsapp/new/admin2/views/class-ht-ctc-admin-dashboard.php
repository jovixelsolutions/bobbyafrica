<?php
/**
 * Admin Dashboard View (Panels)
 *
 * @package Click_To_Chat
 * @subpackage Administration
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Dashboard' ) ) {

	/**
	 * Admin Dashboard Class.
	 *
	 * Renders the Admin 2026 shell, including the left sidebar navigation,
	 * settings panels, and right sidebar widgets.
	 */
	class HT_CTC_Admin_Dashboard {

		/**
		 * Display the admin dashboard panels.
		 */
		public static function display() {

			$os = HT_CTC_Utils::get_option( 'ht_ctc_othersettings' );

			$is_group_enabled = isset( $os['enable_group'] ) ? true : false;
			$is_share_enabled = isset( $os['enable_share'] ) ? true : false;

			HT_CTC_Utils::load_file( 'new/admin2/views/class-ht-ctc-admin-settings-fields.php' );

			// Initially load general settings fields. Remaining tabs load dynamically via REST API.
			$general_settings_fields = HT_CTC_Admin_Settings_Fields::general_settings();

			$main_nav_items  = self::build_main_nav_items();
			$settings_panels = self::build_settings_panels();

			self::render_sidebar( $main_nav_items, $is_group_enabled, $is_share_enabled );
			self::render_main_content( $settings_panels, $general_settings_fields );
			self::render_right_sidebar();
		}

		/**
		 * Build the left-sidebar main navigation items.
		 *
		 * Each item is one entry in the top-level menu. `drill-down` items open a
		 * nested menu (defined in render_sidebar()) instead of switching tabs.
		 *
		 * @return array
		 */
		private static function build_main_nav_items() {
			$main_nav_items = array(
				array(
					'tab'    => 'general-settings',
					'icon'   => 'dashicons-admin-generic',
					'label'  => 'General',
					'active' => true,
				),
				array(
					'tab'   => 'greetings-settings',
					'icon'  => 'dashicons-format-chat',
					'label' => 'Greetings',
				),
				array(
					'tab'   => 'display-settings',
					'icon'  => 'dashicons-desktop',
					'label' => __( 'Display', 'click-to-chat-for-whatsapp' ),
				),
				array(
					'tab'   => 'analytics-settings',
					'icon'  => 'dashicons-chart-bar',
					'label' => __( 'Analytics', 'click-to-chat-for-whatsapp' ),
				),
			);

			if ( class_exists( 'WooCommerce' ) ) {
				$main_nav_items[] = array(
					'type'   => 'drill-down',
					'target' => 'woo-menu',
					'icon'   => 'dashicons-cart',
					'label'  => 'WooCommerce',
				);
			}

			// WooCommerce is handled via a drill-down menu.
			$main_nav_items[] = array(
				'tab'   => 'advanced-settings',
				'icon'  => 'dashicons-admin-settings',
				'label' => 'Advanced',
			);

			if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) {
				$main_nav_items[] = array(
					'tab'         => 'pro-features',
					'icon'        => 'dashicons-star-filled',
					'label'       => 'Upgrade to Pro',
					'icon_style'  => 'color: #eab308;',
					'label_style' => 'color: #eab308; font-weight: 600;',
				);
			}

			return apply_filters( 'ht_ctc_fh_admin_nav_main_menu_items', $main_nav_items );
		}

		/**
		 * Build the settings-panel definitions rendered in the main content area.
		 *
		 * Each panel is rendered as a `<section class="settings-panel">` container.
		 * Field definitions are dynamically rendered by App.js either from the inline
		 * general settings payload or fetched on demand/preloaded via REST API.
		 *
		 * Array keys:
		 *   'id'     - Section element ID (e.g. 'general-settings').
		 *   'group'  - Primary settings group (renders as data-group).
		 *   'groups' - (Optional) Comma-separated auxiliary/contextual groups to preload (data-groups).
		 *   'title'  - Heading text.
		 *   'desc'   - Description text.
		 *   'active' - (Optional) Set true if panel is active on initial load.
		 *
		 * @return array
		 */
		private static function build_settings_panels() {
			$settings_panels = array(
				array(
					'id'     => 'general-settings',
					'group'  => 'general_settings',
					'groups' => 'contextual_styles',
					'title'  => 'General ' . __( 'Settings', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Your ' . __( 'WhatsApp Number', 'click-to-chat-for-whatsapp' ) . ' and ' . __( 'Pre-filled Message', 'click-to-chat-for-whatsapp' ),
					'active' => true,
				),
				array(
					'id'     => 'greetings-settings',
					'group'  => 'greetings_settings',
					'groups' => 'contextual_greetings,contextual_styles',
					'title'  => 'Greetings ' . __( 'Settings', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Add a popup dialog that engages visitors before they start chatting',
				),
				array(
					'id'    => 'display-settings',
					'group' => 'display_settings',
					'title' => __( 'Display Settings', 'click-to-chat-for-whatsapp' ),
					'desc'  => 'Control where and when the chat widget appears',
				),
				array(
					'id'    => 'analytics-settings',
					'group' => 'analytics_settings',
					'title' => sprintf( '%1$s %2$s', __( 'Analytics', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ),
					'desc'  => 'Track chat interactions in Google Analytics and Meta Pixel',
				),
			);

			if ( class_exists( 'WooCommerce' ) ) {
				$settings_panels[] = array(
					'id'     => 'woo-add-whatsapp-settings',
					'group'  => 'woo_add_whatsapp_settings',
					'groups' => 'contextual_styles',
					'title'  => __( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ),
					'desc'   => 'Add WhatsApp in WooCommerce Pages (single product, Shop)',
				);
				$settings_panels[] = array(
					'id'     => 'woo-overwrite-settings',
					'group'  => 'woo_overwrite_settings',
					'groups' => 'contextual_styles',
					'title'  => 'WooCommerce Overwrite Settings',
					'desc'   => 'Overwrite Settings for WooCommerce Pages',
				);
			}

			$settings_panels[] = array(
				'id'    => 'advanced-settings',
				'group' => 'advanced_settings',
				'title' => sprintf( '%1$s %2$s', __( 'Advanced', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ),
				'desc'  => vsprintf( '%1$s, %2$s, and %3$s options', array( __( 'Animations', 'click-to-chat-for-whatsapp' ), __( 'Entry Effects', 'click-to-chat-for-whatsapp' ), __( 'Notification Badge', 'click-to-chat-for-whatsapp' ) ) ),
			);
			$settings_panels[] = array(
				'id'    => 'support-settings',
				'group' => 'support_settings',
				'title' => sprintf( '%1$s & %2$s', str_replace( 'Contact ', '', __( 'Contact Support', 'click-to-chat-for-whatsapp' ) ), __( 'FAQ', 'click-to-chat-for-whatsapp' ) ),
				'desc'  => 'Find answers to common questions, and troubleshoot issues',
			);

			$settings_panels[] = array(
				'id'    => 'group-settings',
				'group' => 'group_settings',
				'title' => __( 'Group Settings', 'click-to-chat-for-whatsapp' ),
				'desc'  => 'Configure the WhatsApp group widget for your site',
			);

			$settings_panels[] = array(
				'id'    => 'share-settings',
				'group' => 'share_settings',
				'title' => sprintf( '%1$s %2$s', __( 'Share', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ),
				'desc'  => 'Let visitors share your page directly via WhatsApp',
			);

			return apply_filters( 'ht_ctc_fh_admin_settings_panels', $settings_panels );
		}

		/**
		 * Render the left sidebar (main menu + drill-down sub-menus).
		 *
		 * Group/Share drill-down items have init-display gating tied to the
		 * `enable_group` / `enable_share` settings; the JS-side `data-watch`
		 * keeps them in sync after toggle without a page reload.
		 *
		 * @param array $main_nav_items     Top-level menu items from build_main_nav_items().
		 * @param bool  $is_group_enabled   Whether the group feature is enabled.
		 * @param bool  $is_share_enabled   Whether the share feature is enabled.
		 * @return void
		 */
		private static function render_sidebar( $main_nav_items, $is_group_enabled, $is_share_enabled ) {
			?>
			<!-- Sidebar - menu -->
			<aside id="sidebar" class="sidebar">
				<div class="sidebar-header">
					<div class="sidebar-title"><?php echo esc_html__( 'Settings', 'click-to-chat-for-whatsapp' ); ?></div>
					<button type="button" id="close-sidebar" class="close-sidebar"
						aria-label="Close settings menu">
						<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
					</button>
				</div>
				<?php // Navigation items map via data-tab to matching section IDs. ?>
				<nav class="sidebar-nav">
					<div class="sidebar-menus-container">
						<div id="main-menu" class="sidebar-menu active">
							<ul>
								<?php
								foreach ( $main_nav_items as $item ) {
									$active_class = ! empty( $item['active'] ) ? ' active' : '';
									$icon_style   = ! empty( $item['icon_style'] ) ? $item['icon_style'] : '';
									$label_style  = ! empty( $item['label_style'] ) ? $item['label_style'] : '';

									if ( ! empty( $item['type'] ) && 'drill-down' === $item['type'] ) {
										?>
										<li>
											<button type="button" class="drill-down-btn" data-target="<?php echo esc_attr( $item['target'] ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $item['label'] ) ); ?>">
												<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" style="<?php echo esc_attr( $icon_style ); ?>"></span>
												<span style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $item['label'] ); ?></span>
											</button>
										</li>
										<?php
									} else {
										?>
										<li>
											<button type="button" class="nav-item<?php echo esc_attr( $active_class ); ?>" data-tab="<?php echo esc_attr( $item['tab'] ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $item['label'] ) ); ?>">
												<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" style="<?php echo esc_attr( $icon_style ); ?>"></span>
												<span style="<?php echo esc_attr( $label_style ); ?>"><?php echo esc_html( $item['label'] ); ?></span>
											</button>
										</li>
										<?php
									}
								}
								?>
							<?php /* Group/Share nav items are part of the same <ul> above */ ?>
								<li data-watch="#enable_group" data-show-when="1"
								<?php
								if ( ! $is_group_enabled ) {
									echo 'class="ctc_init_display_none"'; }
								?>
								>
									<button type="button" class="drill-down-btn" data-target="group-menu" title="<?php esc_attr_e( 'Group Settings', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-groups"></span>
										<span>Group</span>
									</button>
								</li>
								<li data-watch="#enable_share" data-show-when="1"
								<?php
								if ( ! $is_share_enabled ) {
									echo 'class="ctc_init_display_none"'; }
								?>
								>
									<button type="button" class="drill-down-btn" data-target="share-menu" title="<?php esc_attr_e( 'Share', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-share"></span>
										<span><?php esc_html_e( 'Share', 'click-to-chat-for-whatsapp' ); ?></span>
									</button>
								</li>
							</ul>
						</div>

						<?php if ( class_exists( 'WooCommerce' ) ) { ?>
						<!-- WooCommerce Drill-Down Menu -->
						<div id="woo-menu" class="sidebar-menu">
							<button type="button" class="drill-down-back-btn" data-target="main-menu" title="Back to Main">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								<span>Back to Main</span>
							</button>
							<ul>
								<li>
									<button type="button" class="nav-item" data-tab="woo-add-whatsapp-settings" title="<?php esc_attr_e( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-plus"></span>
										<span><?php esc_html_e( 'Add WhatsApp', 'click-to-chat-for-whatsapp' ); ?></span>
									</button>
								</li>
								<li>
									<button type="button" class="nav-item" data-tab="woo-overwrite-settings" title="Overwrite settings">
										<span class="dashicons dashicons-edit"></span>
										<span>Overwrite settings</span>
									</button>
								</li>
							</ul>
						</div>
						<?php } ?>

						<!-- Group Drill-Down Menu -->
						<div id="group-menu" class="sidebar-menu" data-watch="#enable_group" data-show-when="1">
							<button type="button" class="drill-down-back-btn" data-target="main-menu" title="Back to Main">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								<span>Back to Main</span>
							</button>
							<ul>
								<li>
									<button type="button" class="nav-item" data-tab="group-settings" title="<?php esc_attr_e( 'Group Settings', 'click-to-chat-for-whatsapp' ); ?>">
										<span class="dashicons dashicons-groups"></span>
										<span><?php esc_html_e( 'Group Settings', 'click-to-chat-for-whatsapp' ); ?></span>
									</button>
								</li>
							</ul>
						</div>

						<!-- Share Drill-Down Menu -->
						<div id="share-menu" class="sidebar-menu" data-watch="#enable_share" data-show-when="1">
							<button type="button" class="drill-down-back-btn" data-target="main-menu" title="Back to Main">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								<span>Back to Main</span>
							</button>
							<ul>
								<li>
									<button type="button" class="nav-item" data-tab="share-settings" title="<?php echo esc_attr( sprintf( '%1$s %2$s', __( 'Share', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ) ); ?>">
										<span class="dashicons dashicons-share"></span>
										<span><?php echo esc_html( sprintf( '%1$s %2$s', __( 'Share', 'click-to-chat-for-whatsapp' ), __( 'Settings', 'click-to-chat-for-whatsapp' ) ) ); ?></span>
									</button>
								</li>
							</ul>
						</div>

					</div>
				</nav>
			</aside>
			<?php
		}

		/**
		 * Render the main content area (settings panel sections + skeleton placeholders).
		 *
		 * The `general-settings` panel is hydrated inline via window.ht_ctc_fields_general_settings;
		 * other panels fetch their field definitions on first tab activation.
		 *
		 * @param array $settings_panels         Panels from build_settings_panels().
		 * @param array $general_settings_fields Inline payload for the General tab.
		 * @return void
		 */
		private static function render_main_content( $settings_panels, $general_settings_fields ) {
			?>
			<!-- Main Content -->
			<main class="main-content">

				<?php
				settings_errors();
				?>

				<script>
					window.ht_ctc_fields_general_settings = <?php echo wp_json_encode( $general_settings_fields ); ?>;
				</script>

				<?php
				// Remaining panels are hydrated dynamically by App.loadTabSettings().
				foreach ( $settings_panels as $panel ) {
					$active_class = ! empty( $panel['active'] ) ? ' active' : '';

					// Comma-separated auxiliary / contextual field groups to preload alongside the tab.
					$extra_groups = ! empty( $panel['groups'] ) ? (string) $panel['groups'] : '';
					?>
					<section id="<?php echo esc_attr( $panel['id'] ); ?>" class="settings-panel<?php echo esc_attr( $active_class ); ?>" data-group="<?php echo esc_attr( $panel['group'] ); ?>" data-groups="<?php echo esc_attr( $extra_groups ); ?>" data-loaded="false">
						<div class="panel-header">
							<div class="panel-title"><?php echo esc_html( $panel['title'] ); ?></div>
							<p><?php echo esc_html( $panel['desc'] ); ?></p>
						</div>
						<div class="fields-container">
							<div class="ctc-loading-skeleton">
								<div class="ctc-skeleton-row"></div>
								<div class="ctc-skeleton-row"></div>
								<div class="ctc-skeleton-row"></div>
							</div>
						</div>
					</section>
					<?php
				}
				?>

				<?php if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) { ?>
				<section id="pro-features" class="settings-panel" data-group="pro_features" data-loaded="false">
					<div class="fields-container ctc-pro-features-wrapper" style="padding:0;">
						<!-- Loading placeholder -->
						<div class="ctc-loading-skeleton" style="padding: 30px;">
							<div class="ctc-skeleton-header"></div>
							<div class="ctc-skeleton-row" style="height: 120px; margin-top: 20px;"></div>
							<div class="ctc-skeleton-row" style="height: 120px;"></div>
							<div class="ctc-skeleton-row" style="height: 120px;"></div>
						</div>
					</div>
				</section>
				<?php } ?>
			</main>
			<?php
		}

		/**
		 * Render the right sidebar (help, feedback, and preview tabs).
		 *
		 * @return void
		 */
		private static function render_right_sidebar() {
			?>
			<!-- Right Sidebar -->
			<aside id="right-sidebar" class="right-sidebar">
				<div class="right-sidebar-header">
					<div class="sidebar-tabs" role="tablist" aria-label="Sidebar panels">
						<button type="button" id="sidebar-tabbtn-help" class="sidebar-tab-btn active" data-sidebar-tab="help" title="Support" aria-label="Support" role="tab" aria-controls="sidebar-tab-help" aria-selected="true" tabindex="0">
							<span class="dashicons dashicons-sos" aria-hidden="true"></span>
							<span class="sidebar-tab-label">Support</span>
						</button>
						<button type="button" id="sidebar-tabbtn-feedback" class="sidebar-tab-btn" data-sidebar-tab="feedback" title="Feedback" aria-label="Feedback" role="tab" aria-controls="sidebar-tab-feedback" aria-selected="false" tabindex="-1">
							<span class="dashicons dashicons-megaphone" aria-hidden="true"></span>
							<span class="sidebar-tab-label">Feedback</span>
						</button>
						<button type="button" id="sidebar-tabbtn-preview" class="sidebar-tab-btn" data-sidebar-tab="preview" title="Preview" aria-label="Preview" role="tab" aria-controls="sidebar-tab-preview" aria-selected="false" tabindex="-1">
							<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
							<span class="sidebar-tab-label">Preview</span>
						</button>
					</div>
				</div>

				<div class="right-sidebar-content">

					<!-- Help & Contact Tab -->
					<div id="sidebar-tab-help" class="sidebar-tab-content active" role="tabpanel" aria-labelledby="sidebar-tabbtn-help" tabindex="0">
						<div class="sidebar-widget">
							<div class="widget-header">
								<span class="dashicons dashicons-email"></span>
								<h3><?php esc_html_e( 'Contact Us', 'click-to-chat-for-whatsapp' ); ?></h3>
							</div>
							<div class="widget-body">
								<p>Got a question? 😊 We’d love to hear from you!</p>
								<?php
								if ( defined( 'HT_CTC_PRO_VERSION' ) ) {
									$support_url = HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/support/' );
								} else {
									$support_url = 'https://wordpress.org/support/plugin/click-to-chat-for-whatsapp/#new-topic-0';
								}
								?>
								<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" class="widget-btn widget-btn-outline"><?php esc_html_e( 'Contact Support', 'click-to-chat-for-whatsapp' ); ?></a>
							</div>
						</div>

						<?php if ( ! defined( 'HT_CTC_PRO_VERSION' ) ) { ?>
						<div class="sidebar-widget feature-widget ctc-pro-promo">
							<div class="widget-header">
								<span class="dashicons dashicons-star-filled ctc-pro-icon"></span>
								<h3 class="ctc-pro-title">More in PRO</h3>
							</div>
							<div class="widget-body">
								<?php
								// Features promoted in the sidebar PRO widget, filtered by active tab.
								$pro_features = array(
									array(
										'tabs'  => 'default general-settings',
										'icon'  => 'dashicons-groups',
										'title' => 'Multi-Agent Support',
										'desc'  => 'Agents with a photo, a role, their own number and their own weekly hours.',
										'key'   => 'multi_agent',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/multi-agent/',
									),
									array(
										'tabs'  => 'default display-settings woo-add-whatsapp-settings woo-overwrite-settings',
										'icon'  => 'dashicons-clock',
										'title' => __( 'Business Hours', 'click-to-chat-for-whatsapp' ),
										'desc'  => 'Several time slots a day. When you close, switch to an offline number.',
										'key'   => 'business_hours',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/docs/business-hours-online-offline/',
									),
									array(
										'tabs'  => 'greetings-settings',
										'icon'  => 'dashicons-forms',
										'title' => 'Form Filling',
										'desc'  => 'Name, email or phone before the chat opens — 8 field types.',
										'key'   => 'greetings_form',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/greetings-form/',
									),
									array(
										'tabs'  => 'greetings-settings',
										'icon'  => 'dashicons-calendar-alt',
										'title' => 'Date & Time Picker',
										'desc'  => 'A field in the greetings form — visitors pick a date and time that suits them.',
										'key'   => 'greetings_scheduler',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/docs/greetings-form/',
									),
									array(
										'tabs'  => 'greetings-settings',
										'icon'  => 'dashicons-controls-play',
										'title' => 'Auto-Open Triggers',
										'desc'  => 'Open the greeting after a delay, or at a scroll percentage.',
										'key'   => 'greetings_actions',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/greetings-actions/',
									),
									array(
										'tabs'  => 'display-settings',
										'icon'  => 'dashicons-admin-site-alt3',
										'title' => 'Country-Based Display',
										'desc'  => 'Show or hide the chat widget by each visitor\'s country.',
										'key'   => 'country_display',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/display-based-on-country/',
									),
									array(
										'tabs'  => 'display-settings',
										'icon'  => 'dashicons-admin-users',
										'title' => 'Visitor Targeting & Delays',
										'desc'  => 'Choose who sees it by login status, and delay it by seconds or by scroll depth.',
										'key'   => 'schedule_triggers',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/display/',
									),
									array(
										'tabs'  => 'analytics-settings',
										'icon'  => 'dashicons-chart-line',
										'title' => 'Google Ads Conversion',
										'desc'  => 'Send a conversion with your conversion ID and label whenever someone clicks to chat.',
										'key'   => 'google_ads',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/google-ads-conversion/',
									),
									array(
										'tabs'  => 'analytics-settings',
										'icon'  => 'dashicons-facebook',
										'title' => 'Meta Conversions API',
										'desc'  => 'Send click events from your server, so a blocked browser pixel does not lose the conversion.',
										'key'   => 'meta_capi',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/pricing/',
									),
									array(
										'tabs'  => 'analytics-settings advanced-settings',
										'icon'  => 'dashicons-cloud',
										'title' => 'Dynamic Values',
										'desc'  => 'Any URL parameter or cookie — [gclid], [utm_source] — in your webhook and events.',
										'key'   => 'webhooks',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/webhooks/',
									),
									array(
										'tabs'  => 'advanced-settings woo-add-whatsapp-settings woo-overwrite-settings',
										'icon'  => 'dashicons-admin-page',
										'title' => 'Page-Level Overrides',
										'desc'  => 'One page with its own style, greeting, custom link or delay.',
										'key'   => 'page_level',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/change-values-at-page-level/',
									),
									array(
										'tabs'  => 'general-settings',
										'icon'  => 'dashicons-randomize',
										'title' => 'Random & Sequential Numbers',
										'desc'  => 'Spread chats over several numbers, at random or in rotation.',
										'key'   => 'random_number',
										'url'   => 'https://holithemes.com/plugins/click-to-chat/random-number/',
									),
								);
								?>
								<ul class="ctc-pro-feature-list">
									<?php
									// Initial render shows General tab features prior to client-side tab activation.
									foreach ( $pro_features as $pro_feature ) {
										$ctc_pro_show = in_array( 'general-settings', explode( ' ', $pro_feature['tabs'] ), true );
										$ctc_pro_link = HT_CTC_Utils::pro_url( 'sidebar', $pro_feature['key'], $pro_feature['url'] );
										?>
									<li data-tabs="<?php echo esc_attr( $pro_feature['tabs'] ); ?>" <?php echo ( $ctc_pro_show ) ? '' : 'hidden'; ?>>
										<div class="ctc-pro-feature-header">
											<span class="dashicons <?php echo esc_attr( $pro_feature['icon'] ); ?> ctc-pro-icon-small" aria-hidden="true"></span>
											<a href="<?php echo esc_url( $ctc_pro_link ); ?>" target="_blank" rel="noopener" class="ctc-pro-feature-link">
												<strong><?php echo esc_html( $pro_feature['title'] ); ?></strong>
												<span class="dashicons dashicons-external" aria-hidden="true"></span>
												<span class="screen-reader-text">(opens in a new tab)</span>
											</a>
										</div>
										<p class="ctc-pro-feature-desc"><?php echo esc_html( $pro_feature['desc'] ); ?></p>
									</li>
										<?php } ?>
								</ul>
								<?php
								$upgrade_url = HT_CTC_Utils::pro_url( 'sidebar' );
								?>
								<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener" class="widget-btn widget-btn-primary ctc-btn-gold">Upgrade to PRO <span class="dashicons dashicons-external" aria-hidden="true"></span><span class="screen-reader-text">(opens in a new tab)</span></a>
								<a href="#pro-features" class="ctc-pro-see-all">See all PRO features <span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span></a>
							</div>
						</div>
						<?php } ?>
					</div>

					<!-- Feedback Tab -->
					<div id="sidebar-tab-feedback" class="sidebar-tab-content" role="tabpanel" aria-labelledby="sidebar-tabbtn-feedback" tabindex="0">
						<div class="sidebar-widget feature-widget ctc-feedback-widget">
							<div class="widget-header">
								<span class="dashicons dashicons-megaphone"></span>
								<h3>Feedback & Ideas</h3>
							</div>
							<div class="widget-body">
								<p>You help shape what this plugin becomes. Tell us what to build next — or anything else on your mind:</p>
								<ul class="ctc-feedback-prompts">
									<li><span class="ctc-feedback-emoji" aria-hidden="true">🧭</span> What we should build next</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">💡</span> An idea or feature you wish existed</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">🤔</span> Something confusing or unclear</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">🐛</span> Something not working (a bug)</li>
									<li><span class="ctc-feedback-emoji" aria-hidden="true">💬</span> Just casual feedback — we love it</li>
								</ul>
								<p class="ctc-feedback-note">No idea is too small. Every message is read by our team. 😊</p>
								<a href="<?php echo esc_url( HT_CTC_Utils::doc_url( 'https://holithemes.com/plugins/click-to-chat/support/' ) ); ?>" target="_blank" class="widget-btn widget-btn-primary">Share Your Idea</a>
							</div>
						</div>
					</div>

					<!-- Preview Tab -->
					<div id="sidebar-tab-preview" class="sidebar-tab-content" role="tabpanel" aria-labelledby="sidebar-tabbtn-preview" tabindex="0">
						<div class="sidebar-widget feature-widget preview-widget">
							<div class="widget-header">
								<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
								<h3>Live Preview</h3>
								<label class="switch" title="Show Preview">
									<input type="checkbox" checked id="ctc-preview-toggle" data-ctc-no-track="true" aria-label="Show Preview">
									<span class="slider"></span>
								</label>
							</div>
							<div class="widget-body">
								<p id="ctc-preview-note" class="help-text" aria-live="polite"></p>
								<p class="help-text preview-hint">Shows the widget at its configured position — updates as you edit, before saving.</p>
								<button type="button" id="ctc-preview-site-view" class="widget-btn widget-btn-outline" data-ctc-no-track="true">
									<span class="dashicons dashicons-admin-site-alt3" aria-hidden="true"></span>
									View on my site
								</button>
							</div>
						</div>
					</div>
					<?php do_action( 'ht_ctc_ah_admin_right_sidebar_panels' ); ?>

				</div>
			</aside>
			<?php
		}
	}
}
