<?php
/**
 * Admin2 PRO upsell notice.
 *
 * Renders the "Upgrade to PRO" banner on admin pages, plus:
 *   - the stylesheet for the banner
 *   - inline JS that handles the dismiss button
 *   - the AJAX handler that persists dismissal to ht_ctc_notices
 *
 * Only shown when PRO has never been installed, the user hasn't dismissed it,
 * and at least 5 days have passed since first install.
 *
 * Split out of HT_CTC_Admin_Core_Hooks so PRO-upsell concerns live in one file.
 *
 * @package Click_To_Chat
 * @subpackage Admin2
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Upgrade_Notice' ) ) {

	/**
	 * PRO upsell admin notice.
	 */
	class HT_CTC_Admin_Upgrade_Notice {

		/**
		 * Wait time before showing the banner after first install (seconds).
		 */
		const WAIT_TIME = 432000; // 5 * 24 * 60 * 60.

		/**
		 * Constructor: register the dismiss AJAX handler and the banner (if eligible).
		 */
		public function __construct() {
			add_action( 'wp_ajax_ht_ctc_admin_dismiss_notices', array( $this, 'dismiss_notices' ) );

			// Admin screens only. This class is constructed from HT_CTC::init(), outside
			// is_admin() — its siblings have to be, because the REST save path fires
			// 'ht_ctc_ah_admin_after_save_settings' and REST requests are not admin
			// requests. Without this guard, should_show() charged every public page view
			// two DB queries (ht_ctc_pro_plugin_details / ht_ctc_notices are absent on
			// most installs, and an absent option costs a query) for a banner only
			// wp-admin can render. Ajax is excluded too: none of the hooks below fire
			// there, and the dismiss handler above is registered either way.
			if ( ! is_admin() || wp_doing_ajax() ) {
				return;
			}

			if ( $this->should_show() ) {
				add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
				add_action( 'admin_footer', array( $this, 'admin_upgrade_notice_scripts' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_upgrade_notice_styles' ) );
			}

			// (for testing) shows the upgrade notice on every admin screen, bypassing the
			// not-yet-installed / dismissed / 5-day conditions above. Keep them commented -
			// if you uncomment the lines below, add a 'todo(release):' so they cannot ship enabled.
			// add_action( 'admin_notices', array( $this, 'upgrade_notice' ) );
			// add_action( 'admin_footer', array( $this, 'admin_upgrade_notice_scripts' ) );
			// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_upgrade_notice_styles' ) );
		}

		/**
		 * Whether the PRO upsell banner should be shown on the current request.
		 *
		 * @return bool
		 */
		private function should_show() {

			$ht_ctc_pro_plugin_details = HT_CTC_Utils::get_option( 'ht_ctc_pro_plugin_details' );
			if ( isset( $ht_ctc_pro_plugin_details['version'] ) ) {
				return false;
			}

			$ht_ctc_notices = HT_CTC_Utils::get_option( 'ht_ctc_notices' );
			if ( isset( $ht_ctc_notices['pro_banner'] ) ) {
				return false;
			}

			$ht_ctc_plugin_details = HT_CTC_Utils::get_option( 'ht_ctc_plugin_details' );
			$first_install_time    = ( isset( $ht_ctc_plugin_details['first_install_time'] ) ) ? esc_attr( $ht_ctc_plugin_details['first_install_time'] ) : 1;

			return ( time() - $first_install_time ) > self::WAIT_TIME;
		}

		/**
		 * Whether the current user is someone this banner should be shown to.
		 *
		 * The admin_notices hook fires for anyone who can reach an admin screen, so
		 * without this an editor or a shop manager gets a PRO pitch they cannot act on -
		 * and cannot get rid of either, because dismiss_notices() requires this
		 * same capability. Kept identical to the one that handler checks: a banner
		 * someone can see but never dismiss is worse than no banner.
		 *
		 * Checked here in the render callbacks rather than in should_show(), which
		 * runs at plugin load - too early for capabilities to be resolved.
		 *
		 * @return bool
		 */
		private function user_can_see() {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Enqueue the upgrade to PRO notice stylesheet (only when the banner is shown).
		 *
		 * @return void
		 */
		public function enqueue_upgrade_notice_styles() {

			if ( ! $this->user_can_see() ) {
				return;
			}

			$css = defined( 'HT_CTC_DEBUG_MODE' ) ? 'dev/admin-notice/upgrade-banner.css' : 'min/admin-notice/upgrade-banner.css';

			wp_enqueue_style(
				'ht-ctc-upgrade-banner',
				plugins_url( "new/admin2/assets/$css", HT_CTC_PLUGIN_FILE ),
				array(),
				HT_CTC_VERSION
			);
		}

		/**
		 * Render the PRO upsell banner.
		 *
		 * One specific promise, the features that back it up, and two ways
		 * forward: the pricing page, or the PRO tab inside this admin for anyone not
		 * ready to leave the site yet. The tab link carries ?tab= rather than a hash
		 * because Interface.initNavigation() gives the query parameter priority.
		 *
		 * @return void
		 */
		public function upgrade_notice() {

			if ( ! $this->user_can_see() ) {
				return;
			}

			// Tagged so this banner shows up in campaign reports like every other
			// PRO call to action. See HT_CTC_Utils::pro_url().
			$upgrade_url = HT_CTC_Utils::pro_url( 'banner' );

			/*
			 * Chips, so each one has to earn its two or three words: the noun
			 * alone ("Multi-Agent") tells someone who already has the free
			 * plugin nothing they can weigh. Every one is PRO-only - checked
			 * against the PRO plugin, not against a feature name.
			 */
			$features = array(
				'Multi-agent routing',
				'Lead capture forms',
				'Date & time picker',
				'Business hours',
				'Conversion tracking',
			);
			?>
		<div class="notice is-dismissible ht-ctc-notice ht-ctc-notice-pro-banner" data-db="pro_banner">
			<div class="ht-ctc-pro-inner">
				<div class="ht_ctc_pro_icon_box" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/><path d="M8 12h.01M12 12h.01M16 12h.01"/></svg>
				</div>
				<div class="ht-ctc-pro-content">
					<p class="ht-ctc-pro-eyebrow">
						<span class="ht-ctc-pro-plugin">Click to Chat</span>
						<span class="pro-badge"><?php esc_html_e( 'PRO', 'click-to-chat-for-whatsapp' ); ?></span>
					</p>
					<p class="ht-ctc-pro-title">
						Do more with every chat
					</p>
					<p class="ht-ctc-pro-desc">
						Route visitors to the right agent, capture visitors details before the chat opens, and see which campaigns actually produce conversations.
					</p>
					<ul class="ht-ctc-pro-features">
						<?php
						foreach ( $features as $ctc_feature ) {
							printf( '<li>%s</li>', esc_html( $ctc_feature ) );
						}
						?>
					</ul>
				</div>
				<div class="ht-ctc-pro-actions">
					<a href="<?php echo esc_url( $upgrade_url ); ?>" target="_blank" rel="noopener" class="button button-upgrade">
						Upgrade to PRO
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
					</a>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat&tab=pro-features' ) ); ?>" class="ht-ctc-pro-secondary">
						See all PRO features
					</a>
					<button type="button" class="button-dismiss button-dismiss-text">Maybe later</button>
				</div>
			</div>
		</div>
			<?php
		}

		/**
		 * Inline JS to handle dismissing the PRO notice.
		 *
		 * @return void
		 */
		public function admin_upgrade_notice_scripts() {

			if ( ! $this->user_can_see() ) {
				return;
			}
			?>
		<script>
			(function () {

				if (document.readyState === "complete" || document.readyState === "interactive") {
					ready();
				} else {
					document.addEventListener("DOMContentLoaded", ready);
				}

				function serialize(obj) {
					return Object.keys(obj).reduce(function (a, k) {
						a.push(k + '=' + encodeURIComponent(obj[k]));
						return a;
					}, []).join('&');
				}

				function ready() {
					setTimeout(function () {
						const buttons = document.querySelectorAll(".ht-ctc-notice-pro-banner .notice-dismiss, .ht-ctc-notice-pro-banner .button-dismiss");
						for (let i = 0; i < buttons.length; i++) {
							buttons[i].addEventListener('click', function (e) {
								e.preventDefault();

								var element = e.target.closest('.is-dismissible');
								var db = (element.hasAttribute('data-db')) ? element.getAttribute('data-db') : 'fallback';

								const http = new XMLHttpRequest();
								http.open('POST', ajaxurl, true);
								http.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=UTF-8");
								http.send(serialize({
									'action': 'ht_ctc_admin_dismiss_notices',
									'db': db,
									'nonce': <?php echo wp_json_encode( wp_create_nonce( 'ht-ctc-notices' ) ); ?>
								}));

								element.remove();
							});
						}
					}, 1000);
				}

			})();
		</script>
			<?php
		}

		/**
		 * AJAX: Dismiss admin notices.
		 *
		 * @return void Sends JSON success and exits.
		 */
		public function dismiss_notices() {

			// Verify the request is genuine (nonce / CSRF) before any authorization or work.
			check_ajax_referer( 'ht-ctc-notices', 'nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'message' => 'Unauthorized' ) );
			}

			$post_data = ( $_POST ) ? map_deep( wp_unslash( $_POST ), 'sanitize_text_field' ) : array();

			$db_key = ( isset( $post_data['db'] ) ) ? esc_attr( $post_data['db'] ) : '';

			// Only known notice keys may be dismissed. Bail before touching the DB so a
			// valid nonce can't be replayed with an arbitrary/'fallback' key to spam writes.
			$db_key_values = array(
				'pro_banner',
			);

			if ( '' === $db_key || ! in_array( $db_key, $db_key_values, true ) ) {
				wp_send_json_error( array( 'message' => 'Invalid notice key' ) );
			}

			$time      = time();
			$db_values = get_option( 'ht_ctc_notices', array() );

			$update_values = is_array( $db_values ) ? $db_values : array();

			$update_values['version']             = HT_CTC_VERSION;
			$update_values[ $db_key ]             = $time;
			$update_values[ "{$db_key}_version" ] = HT_CTC_VERSION;

			update_option( 'ht_ctc_notices', $update_values );

			wp_send_json_success();
		}
	}
} // END class_exists check
