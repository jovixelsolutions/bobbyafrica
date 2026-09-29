<?php
/**
 * Admin Hooks.
 * Other functions and features related to admin screens.
 *
 * Admin notices: display guidance when required settings are missing.
 *
 * @since 2.7
 * @package Click_To_Chat
 * @subpackage Admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Hooks' ) ) {

	/**
	 * Admin-side utilities and notices for Click to Chat.
	 */
	class HT_CTC_Admin_Hooks {

		/**
		 * Constructor: wire hooks and AJAX.
		 *
		 * @return void
		 */
		public function __construct() {
			$this->admin_hooks();
			$this->ajax();
		}

		/**
		 * Register admin AJAX handlers.
		 *
		 * @return void
		 */
		public function ajax() {

			add_action( 'wp_ajax_ht_ctc_admin_dismiss_notices', array( $this, 'dismiss_notices' ) );
		}

		/**
		 * Register admin-side hooks and notices.
		 *
		 * @return void
		 */
		public function admin_hooks() {

				// If it's a Click to Chat admin page.
			add_action( 'load-toplevel_page_click-to-chat', array( $this, 'load_ctc_admin_page' ) );
			add_action( 'load-click-to-chat_page_click-to-chat-customize-styles', array( $this, 'load_ctc_admin_page' ) );
			add_action( 'load-click-to-chat_page_click-to-chat-greetings', array( $this, 'load_ctc_admin_page' ) );
			add_action( 'load-click-to-chat_page_click-to-chat-other-settings', array( $this, 'load_ctc_admin_page' ) );
			add_action( 'load-click-to-chat_page_click-to-chat-woocommerce', array( $this, 'load_ctc_admin_page' ) );

			add_action( 'ht_ctc_ah_admin_scripts_start', array( $this, 'dequeue' ) );
			add_action( 'ht_ctc_ah_admin_scripts_start_woo_page', array( $this, 'woo_dequeue' ) );

				// Admin notices.
			$this->admin_notice();

				// ht_ctc_ah_admin.
			add_action( 'ht_ctc_ah_admin_after_sanitize', array( $this, 'after_sanitize' ) );

				// Clear cache.
			add_action( 'update_option_ht_ctc_admin_pages', array( $this, 'clear_cache' ) );
				// Clear cache - customize styles.
			add_action( 'update_option_ht_ctc_cs_options', array( $this, 'clear_cache' ) );
				// Clear cache - greetings settings page.
			add_action( 'update_option_ht_ctc_greetings_settings', array( $this, 'clear_cache' ) );

				// add_action( 'admin_notices', array( $this, 'cache_clear_notice') );.
		}


			// It's Click to Chat - admin page.
		/**
		 * Fires when a Click to Chat admin screen loads.
		 * Ensures required DB defaults are in place.
		 *
		 * @return void
		 */
		public function load_ctc_admin_page() {

			do_action( 'ht_ctc_ah_admin_its_ctc_admin_page' );

			/**
			 * When the user enters any Click to Chat admin page,
			 * ensure required options are initialized.
			 *
			 * DB: group, share, styles (style-2 adds while active).
			 * Loads only if styles are not defined; checked using s1.
			 *
			 * DB and DB2 will also run when version changes
			 * from class-ht-ctc-register.php -> version_changed().
			 */
			$s1 = get_option( 'ht_ctc_s1' );

			if ( ! isset( $s1['s1_text_color'] ) ) {
				include_once HT_CTC_PLUGIN_DIR . '/new/admin/db/class-ht-ctc-db2.php';
			}
		}

		// Clear cache marker is updated after sanitize on plugin admin pages.
		/**
		 * After settings save, bump a counter to trigger cache clears.
		 *
		 * @return void
		 */
		public function after_sanitize() {

			$ht_ctc_admin_pages = get_option( 'ht_ctc_admin_pages' );

			$count = ( isset( $ht_ctc_admin_pages['count'] ) ) ? esc_attr( $ht_ctc_admin_pages['count'] ) : '1';
			// to make this settings will always update to work for clear cache
			++$count;

			$values = array(
				'count' => $count,
			);

			update_option( 'ht_ctc_admin_pages', $values );
		}


		/**
		 * Conditionally display admin notices on missing settings and promos.
		 *
		 * @return void
		 */
		public function admin_notice() {

			// Admin notices
			// if number blank
			$ht_ctc_chat_options       = get_option( 'ht_ctc_chat_options' );
			$ht_ctc_notices            = get_option( 'ht_ctc_notices' );
			$ht_ctc_pro_plugin_details = get_option( 'ht_ctc_pro_plugin_details' );

			$load_pro_notice_scripts = 'no';

			if ( isset( $ht_ctc_chat_options['number'] ) ) {
				if ( '' === $ht_ctc_chat_options['number'] ) {
					add_action( 'admin_notices', array( $this, 'ifnumberblank' ) );
				}
			}

			$ht_ctc_othersettings = get_option( 'ht_ctc_othersettings' );

			// if group id blank
			if ( isset( $ht_ctc_othersettings['enable_group'] ) ) {
				$ht_ctc_group = get_option( 'ht_ctc_group' );

				if ( isset( $ht_ctc_group['group_id'] ) ) {
					if ( '' === $ht_ctc_group['group_id'] ) {
						add_action( 'admin_notices', array( $this, 'ifgroupblank' ) );
					}
				}
			}

			// if share_text blank
			if ( isset( $ht_ctc_othersettings['enable_share'] ) ) {
				$ht_ctc_share = get_option( 'ht_ctc_share' );

				if ( isset( $ht_ctc_share['share_text'] ) ) {
					if ( '' === $ht_ctc_share['share_text'] ) {
						add_action( 'admin_notices', array( $this, 'ifshareblank' ) );
					}
				}
			}

			/*
			 * Pro notice.
			 *
			 * Not closed/dismissed the pro notice.
			 * Not yet installed once.
			 * After 5 days of first install.
			 */
			// display pro banner only if pro plugin is not yet installed once
			if ( ! isset( $ht_ctc_pro_plugin_details['version'] ) ) {

				if ( ! isset( $ht_ctc_notices['pro_banner'] ) ) {

					$time = time();

					// 5 days
					$wait_time = ( 5 * 24 * 60 * 60 );

					$ht_ctc_plugin_details = get_option( 'ht_ctc_plugin_details' );
					$first_install_time    = ( isset( $ht_ctc_plugin_details['first_install_time'] ) ) ? esc_attr( $ht_ctc_plugin_details['first_install_time'] ) : 1;

					$diff_time = $time - $first_install_time;

					if ( $diff_time > $wait_time ) {
						add_action( 'admin_notices', array( $this, 'pro_notice' ) );
						$load_pro_notice_scripts = 'yes';
					}
				}

				// load pro notice scripts
				if ( 'yes' === $load_pro_notice_scripts ) {
					add_action( 'admin_footer', array( $this, 'admin_pro_notice_scripts' ) );
				}
			}

			// to-do: comment this lines..
			// add_action('admin_notices', array( $this, 'pro_notice') );.
			// add_action('admin_footer', array( $this, 'admin_pro_notice_scripts') );.

			/**
			 * Plugin update notice.
			 *
			 * Useful if there is an important release.
			 */
			// $update_plugins = get_site_transient( 'update_plugins' );
			// if ( isset($update_plugins->response) ) {
			// if ( isset($update_plugins->response['click-to-chat/click-to-chat.php']) ) {
			// add_action('admin_notices', array( $this, 'plugin_update_notice') );.
			// }
			// }

			// $update_plugins = get_site_transient( 'update_plugins' );
			// if ( isset($update_plugins->response) ) {
			// if ( isset($update_plugins->response['click-to-chat-pro/click-to-chat-pro.php']) ) {
			// add_action('admin_notices', array( $this, 'plugin_update_notice') );.
			// }
			// }
		}


		/**
		 * Show a plugin update notice (when enabled).
		 *
		 * @return void
		 */
		public function plugin_update_notice() {
			?>
		<div class="notice notice-warning is-dismissible">
			<p>Click to Chat plugin has an update available.</p>
		</div>
			<?php
		}

		/**
		 * Admin notice when WhatsApp number is not configured.
		 *
		 * @return void
		 */
		public function ifnumberblank() {
			?>
		<div class="notice notice-info is-dismissible">
			<p><?php esc_html_e( 'Click to Chat is almost ready', 'click-to-chat-for-whatsapp' ); ?>. <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat' ) ); ?>"><?php esc_html_e( 'Add WhatsApp Number', 'click-to-chat-for-whatsapp' ); ?></a> <?php esc_html_e( 'and let visitors chat', 'click-to-chat-for-whatsapp' ); ?>.</p>
			<!-- <p>Click to Chat is almost ready. <a href="<?php // echo admin_url('admin.php?page=click-to-chat'); ?>">Add WhatsApp Number</a> to display the chat options and let visitors chat.</p> -->
		</div>
			<?php
		}

		/**
		 * Admin notice when Group ID is not configured.
		 *
		 * @return void
		 */
		public function ifgroupblank() {
			?>
		<div class="notice notice-info is-dismissible">
			<p><?php esc_html_e( 'Click to Chat is almost ready', 'click-to-chat-for-whatsapp' ); ?>. <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-group-feature' ) ); ?>"><?php esc_html_e( 'Add WhatsApp Group ID', 'click-to-chat-for-whatsapp' ); ?></a> <?php esc_html_e( 'to let visitors join in your WhatsApp Group', 'click-to-chat-for-whatsapp' ); ?>.</p>
		</div>
			<?php
		}

		/**
		 * Admin notice when Share Text is not configured.
		 *
		 * @return void
		 */
		public function ifshareblank() {
			?>
		<div class="notice notice-info is-dismissible">
			<p><?php esc_html_e( 'Click to Chat is almost ready', 'click-to-chat-for-whatsapp' ); ?>. <a href="<?php echo esc_url( admin_url( 'admin.php?page=click-to-chat-share-feature' ) ); ?>"><?php esc_html_e( 'Add Share Text', 'click-to-chat-for-whatsapp' ); ?></a> <?php esc_html_e( 'to let vistiors Share your Webpages', 'click-to-chat-for-whatsapp' ); ?>.</p>
		</div>
			<?php
		}

			/* Pro notice. */
		/**
		 * Render the PRO upsell banner.
		 *
		 * @return void
		 */
		public function pro_notice() {
			?>
		<div class="notice notice-info is-dismissible ht-ctc-notice-pro-banner" data-db="pro_banner" style="display:flex; flex-direction:column; padding:14px; border-radius:5px;">
			<p style="margin:0; font-size:1.4rem; color:#1d2327; font-weight:600;">Click to Chat - PRO</p>
			<p style="margin:0 0 2px;">
				<p class="description">Form Filling, Multi-Agent, Random Number, Webhook Integration, Google Ads Conversion Tracking.</p>
				<p class="description">Customize chat display based on visitor's country, business hours (schedule), time delay, scroll behavior, login status, and more.</p>
			</p>
				<!-- WooCommerce integration -->
			<p>
			<a class="button button-primary" style="padding:2px 15px;" href="https://holithemes.com/plugins/click-to-chat/pricing/" target="_blank">Get PRO Now</a>
			<br>
			<a class="button-dismiss" style="text-decoration: none; margin: 0 2px;" href="#">Dismiss</a>
			</p>
		</div>
			<?php
		}



		/**
		 * Inline JS to handle dismissing the PRO notice.
		 *
		 * @return void
		 */
		public function admin_pro_notice_scripts() {
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

			/* Dismiss notice handler. */
		/**
		 * AJAX: Dismiss admin notices.
		 *
		 * @return void Sends JSON success and exits.
		 */
		public function dismiss_notices() {

			check_ajax_referer( 'ht-ctc-notices', 'nonce' );

			$time = time();

			// map_deep may not required. instead call post of db directly and sanitize.
			$post_data = ( $_POST ) ? map_deep( $_POST, 'sanitize_text_field' ) : '';

			$db_key = ( isset( $post_data['db'] ) ) ? esc_attr( $post_data['db'] ) : '';

			// update/add at db..
			$values        = array(
				'version' => HT_CTC_VERSION,
			);
			$update_values = array();
			$db_values     = get_option( 'ht_ctc_notices', array() );

			if ( is_array( $db_values ) ) {
				$update_values = array_merge( $values, $db_values );
			}

			// update to latest values
			$update_values['version'] = HT_CTC_VERSION;

			// db_key santized. but to avoid unwanted values to save in db.
			$db_key_values = array(
				'pro_banner',
			);

			// Add data.
			if ( '' !== $db_key && in_array( $db_key, $db_key_values, true ) ) {

				$update_values[ $db_key ] = $time;

				// @since 4.3. key with current version
				$db_key_version                   = "{$db_key}_version";
				$update_values[ $db_key_version ] = HT_CTC_VERSION;
			}
			update_option( 'ht_ctc_notices', $update_values );

			wp_send_json_success();

			// this wont run
			wp_die();
		}


			/* Dequeue hooks on Click to Chat admin pages. */
		/**
		 * Dequeue conflicting scripts/styles on CTC admin screens (special mode).
		 *
		 * @return void
		 */
		public function dequeue() {

			// As now only if in &special mode
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only reading data, no state change.
			if ( isset( $_GET ) && isset( $_GET['special'] ) ) {

				add_action( 'wp_print_scripts', array( $this, 'dequeue_scripts' ) );

				// &special&nocss
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Debug parameter triggers display changes only.
				if ( isset( $_GET['nocss'] ) ) {
					// add_action( 'wp_print_scripts', [$this, 'dequeue_styles'] );.
					add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_styles' ), 99 );
				}
			}
		}

			/* Runs on Click to Chat - Woo admin page. */
		/**
		 * Dequeue WooCommerce assets on our admin screens (special mode).
		 *
		 * @return void
		 */
		public function woo_dequeue() {
			add_action( 'wp_print_scripts', array( $this, 'dequeue_scripts' ) );
		}

		// dequeue scripts to avioid conflicts..
		/**
		 * Callback to dequeue scripts.
		 *
		 * @return void
		 */
		public function dequeue_scripts() {

			global $wp_scripts;
			$scripts = array();

			foreach ( $wp_scripts->queue as $handle ) {
				// $scripts[] = $wp_scripts->registered[$handle];
				$scripts[ $handle ] = $wp_scripts->registered[ $handle ]->src;
			}

			$plugin     = '/plugins/';
			$ctc_plugin = '/plugins/click-to-chat';

			foreach ( $scripts as $handle => $src ) {

				if ( false === strpos( $src, $ctc_plugin ) ) {
					// exclude click to chat plugin

					if ( false !== strpos( $src, $plugin ) ) {
						wp_dequeue_script( $handle );
					}
				}
			}
		}


		// dequeue scripts to avioid conflicts..
		/**
		 * Callback to dequeue styles.
		 *
		 * @return void
		 */
		public function dequeue_styles() {

			global $wp_styles;

			$styles = array();

			foreach ( $wp_styles->queue as $handle ) {
				$styles[ $handle ] = $wp_styles->registered[ $handle ]->src;
			}

			$plugin     = '/plugins/';
			$ctc_plugin = '/plugins/click-to-chat';

			foreach ( $styles as $handle => $src ) {

				if ( false === strpos( $src, $ctc_plugin ) ) {
					// exclude click to chat plugin

					if ( false !== strpos( $src, $plugin ) ) {
						wp_dequeue_style( $handle );
					}
				}
			}
		}




		// clear cache after save settings.
		/**
		 * Attempt to clear caches from popular caching/performance plugins.
		 *
		 * @return void
		 */
		public function clear_cache() {

			// $cleared = []; // To log which cache systems were cleared

			// WP Super Cache
			if ( function_exists( 'wp_cache_clear_cache' ) ) {
				wp_cache_clear_cache();
				// $cleared[] = 'WP Super Cache';
			}

			// W3 Total Cache
			if ( function_exists( 'w3tc_pgcache_flush' ) ) {
				w3tc_pgcache_flush();
				// w3tc_flush_all();
			}

			// WP Fastest Cache
			if ( function_exists( 'wpfc_clear_all_cache' ) ) {
				wpfc_clear_all_cache();
				// wpfc_clear_all_cache(true);
			}

			// Autoptimize
			if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
				autoptimizeCache::clearall();
			}

			// WP Rocket
			if ( function_exists( 'rocket_clean_domain' ) ) {
				rocket_clean_domain();
				// rocket_clean_minify();
			}

			// WPEngine
			if ( class_exists( 'WpeCommon' ) ) {
				if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
					WpeCommon::purge_memcached();
				}
				if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
					WpeCommon::purge_varnish_cache();
				}
			}

			// SG Optimizer by SiteGround
			if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
				sg_cachepress_purge_cache();
			}

			// LiteSpeed Cache
			if ( class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' ) ) {
				LiteSpeed_Cache_API::purge_all();
			}

			// Cache Enabler
			if ( class_exists( 'Cache_Enabler' ) && method_exists( 'Cache_Enabler', 'clear_total_cache' ) ) {
				Cache_Enabler::clear_total_cache();
			}

			// // Pagely
			// if ( class_exists('PagelyCachePurge') && method_exists('PagelyCachePurge','purgeAll') ) {
			// https://wordpress.org/support/topic/the-plugin-is-attempting-to-do-a-cache-purge/
			// PagelyCachePurge::purgeAll();
			// }

			// Comet Cache
			if ( class_exists( 'comet_cache' ) && method_exists( 'comet_cache', 'clear' ) ) {
				comet_cache::clear();
			}

			// Hummingbird
			if ( class_exists( '\Hummingbird\WP_Hummingbird' ) && method_exists( '\Hummingbird\WP_Hummingbird', 'flush_cache' ) ) {
				\Hummingbird\WP_Hummingbird::flush_cache();
			}

			// WP-Optimize
			if ( function_exists( 'wpo_cache_flush' ) ) {
				wpo_cache_flush();
			}

			// Cachify
			// if ( function_exists( 'cachify_flush_cache' ) ) {
			// cachify_flush_cache();
			// }

			// Breeze
			// if ( class_exists( 'Breeze_PurgeCache' ) && method_exists( 'Breeze_PurgeCache', 'breeze_clear_cache' ) ) {
			// Breeze_PurgeCache::breeze_clear_cache();
			// }

			// Swift Performance
			// if ( class_exists( 'Swift_Performance_Cache' ) && method_exists( 'Swift_Performance_Cache', 'clear_cache' ) ) {
			// Swift_Performance_Cache::clear_cache();
			// }

			// Cloudflare (via WP Cloudflare Super Page Cache plugin)
			// if ( function_exists( 'wp_cloudflare_purge_cache' ) ) {
			// wp_cloudflare_purge_cache();
			// }

			// Pantheon Edge Cache
			// if ( function_exists( 'pantheon_wp_clear_edge_all' ) ) {
			// pantheon_wp_clear_edge_all();
			// }

			// Optional: ZenCache (old Comet Cache name)
			// if ( class_exists( 'zencache' ) && method_exists( 'zencache', 'clear' ) ) {
			// zencache::clear();
			// }

			// Redis Object Cache (optional)
			// if ( class_exists( 'RedisObjectCache' ) && method_exists( 'RedisObjectCache', 'flush_all' ) ) {
			// RedisObjectCache::flush_all();
			// }

			// clear cache
			if ( function_exists( 'wp_cache_flush' ) ) {
				wp_cache_flush();
			}

			// // Show admin notice after clearing
			// set_transient( 'ht_ctc_cache_cleared_notice', 1, 30 );
		}


		/**
		 * Cache clear notice
		 * stub. not called.
		 * similar we can adming notice if mulitlingual plugin is active then add notice like clear/update string translations.
		 */
		/**
		 * Admin notice after caches are cleared (stub).
		 *
		 * @return void
		 */
		public function cache_clear_notice() {
			if ( get_transient( 'ht_ctc_cache_cleared_notice' ) ) {
				?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'If updates are not reflected, please clear your site, server and CDN cache.', 'click-to-chat-for-whatsapp' ); ?></p>
			</div>
				<?php
				delete_transient( 'ht_ctc_cache_cleared_notice' );
			}
		}
	}

	new HT_CTC_Admin_Hooks();

} // END class_exists check
