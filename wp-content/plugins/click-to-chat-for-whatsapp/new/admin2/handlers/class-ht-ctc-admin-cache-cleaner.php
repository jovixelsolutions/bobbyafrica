<?php
/**
 * Admin2 cache cleaner.
 *
 * After CTC settings are saved (signaled by update_option_ht_ctc_admin_pages),
 * flush caches owned by any popular caching/performance plugin that may be
 * holding stale front-end HTML containing the chat widget.
 *
 * Split out of HT_CTC_Admin_Core_Hooks so adding/removing a cache plugin
 * integration only touches one file.
 *
 * ONE REGISTRY. providers() is the single list of supported cache plugins, and
 * each entry knows how to DETECT itself and how to FLUSH itself. The save hook
 * flushes every detected provider; detected() answers "which of these is
 * actually installed here" for anything that needs to report or act on that
 * without repeating the checks.
 *
 * Keeping detection and flushing in one entry is the point: as two separate
 * lists they drift, and a provider added to one but not the other either never
 * gets flushed or never gets reported — silently, in both directions.
 *
 * The list is closed on purpose — adding support for another cache plugin is
 * an edit to providers() below, not something a site can inject at runtime.
 *
 * @package Click_To_Chat
 * @subpackage Admin2
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Admin_Cache_Cleaner' ) ) {

	/**
	 * Cache plugin integration.
	 */
	class HT_CTC_Admin_Cache_Cleaner {

		/**
		 * Constructor: hook the cache flush.
		 *
		 * The following hooks were inherited from the legacy Admin 2019 architecture.
		 * In Admin 2019, these pages had isolated save flows that did not trigger the global 'ht_ctc_admin_pages' update.
		 * In Admin 2026, the REST API centralizes saving and always triggers 'after_save_settings'
		 * via 'ht_ctc_ah_admin_after_save_settings', which updates 'ht_ctc_admin_pages'.
		 * Hooking only 'update_option_ht_ctc_admin_pages' here avoids multiple cache flushes per save.
		 */
		public function __construct() {
			add_action( 'update_option_ht_ctc_admin_pages', array( $this, 'clear_cache' ) );

			// // Legacy redundant hooks (Admin 2019). Kept commented for history.
			// add_action( 'update_option_ht_ctc_cs_options', array( $this, 'clear_cache' ) );
			// add_action( 'update_option_ht_ctc_greetings_settings', array( $this, 'clear_cache' ) );
		}

		/**
		 * Supported caching / performance plugins.
		 *
		 * Each entry:
		 *   label  — human name, for anything that reports what was found.
		 *   detect — callable returning true when that plugin is active here.
		 *   flush  — callable that purges its cache. Only ever called when
		 *            `detect` returned true, so it can assume its API exists.
		 *
		 * Host-level integrations (WP Engine, SiteGround) live here too: from
		 * this plugin's point of view they are the same thing — something else
		 * is holding the rendered page.
		 *
		 * @return array<string,array> Provider id => definition.
		 */
		public static function providers() {

			$providers = array(
				'wp_super_cache'   => array(
					'label'  => 'WP Super Cache',
					'detect' => function () {
						return function_exists( 'wp_cache_clear_cache' );
					},
					'flush'  => function () {
						wp_cache_clear_cache();
					},
				),
				'w3_total_cache'   => array(
					'label'  => 'W3 Total Cache',
					'detect' => function () {
						return function_exists( 'w3tc_pgcache_flush' );
					},
					'flush'  => function () {
						w3tc_pgcache_flush();
					},
				),
				'wp_fastest_cache' => array(
					'label'  => 'WP Fastest Cache',
					'detect' => function () {
						return function_exists( 'wpfc_clear_all_cache' );
					},
					'flush'  => function () {
						wpfc_clear_all_cache();
					},
				),
				'autoptimize'      => array(
					'label'  => 'Autoptimize',
					'detect' => function () {
						return class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' );
					},
					'flush'  => function () {
						autoptimizeCache::clearall();
					},
				),
				'wp_rocket'        => array(
					'label'  => 'WP Rocket',
					'detect' => function () {
						return function_exists( 'rocket_clean_domain' );
					},
					'flush'  => function () {
						rocket_clean_domain();
					},
				),
				'wpengine'         => array(
					'label'  => 'WP Engine',
					'detect' => function () {
						return class_exists( 'WpeCommon' );
					},
					// Named method rather than a closure: WP Engine is the one
					// provider with two caches to purge, and its body is longer
					// than a closure should be.
					'flush'  => array( __CLASS__, 'flush_wpengine' ),
				),
				'sg_optimizer'     => array(
					'label'  => 'SG Optimizer (SiteGround)',
					'detect' => function () {
						return function_exists( 'sg_cachepress_purge_cache' );
					},
					'flush'  => function () {
						sg_cachepress_purge_cache();
					},
				),
				'litespeed_cache'  => array(
					'label'  => 'LiteSpeed Cache',
					'detect' => function () {
						return class_exists( 'LiteSpeed_Cache_API' ) && method_exists( 'LiteSpeed_Cache_API', 'purge_all' );
					},
					'flush'  => function () {
						LiteSpeed_Cache_API::purge_all();
					},
				),
				'cache_enabler'    => array(
					'label'  => 'Cache Enabler',
					'detect' => function () {
						return class_exists( 'Cache_Enabler' ) && method_exists( 'Cache_Enabler', 'clear_total_cache' );
					},
					'flush'  => function () {
						Cache_Enabler::clear_total_cache();
					},
				),
				'comet_cache'      => array(
					'label'  => 'Comet Cache',
					'detect' => function () {
						return class_exists( 'comet_cache' ) && method_exists( 'comet_cache', 'clear' );
					},
					'flush'  => function () {
						comet_cache::clear();
					},
				),
				'hummingbird'      => array(
					'label'  => 'Hummingbird',
					'detect' => function () {
						return class_exists( '\Hummingbird\WP_Hummingbird' ) && method_exists( '\Hummingbird\WP_Hummingbird', 'flush_cache' );
					},
					'flush'  => function () {
						\Hummingbird\WP_Hummingbird::flush_cache();
					},
				),
				'wp_optimize'      => array(
					'label'  => 'WP-Optimize',
					'detect' => function () {
						return function_exists( 'wpo_cache_flush' );
					},
					'flush'  => function () {
						wpo_cache_flush();
					},
				),
			);

			return $providers;
		}

		/**
		 * Purge both of WP Engine's caches.
		 *
		 * @return void
		 */
		public static function flush_wpengine() {
			if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
				WpeCommon::purge_memcached();
			}
			if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
				WpeCommon::purge_varnish_cache();
			}
		}

		/**
		 * Cache plugins that are actually active on this site.
		 *
		 * Read-only: answers "what is installed here" without touching any of
		 * it. purge_all() does its own detection inline rather than calling
		 * this, so neither method builds the provider list twice.
		 *
		 * @return array<string,string> Provider id => label.
		 */
		public static function detected() {
			$found = array();

			foreach ( self::providers() as $id => $provider ) {
				if ( call_user_func( $provider['detect'] ) ) {
					$found[ $id ] = $provider['label'];
				}
			}

			return $found;
		}

		/**
		 * Purge every detected cache provider.
		 *
		 * One pass over the list: detect, then flush the ones that answered yes.
		 *
		 * Each flush is guarded. The callables reach into other plugins'
		 * functions, and a plugin that changes or breaks its own API would
		 * otherwise take down the whole purge — this runs on every settings
		 * save, so the eleven providers after the broken one must still get
		 * their chance. A failure is logged, not surfaced: the save itself
		 * succeeded, and a stale cache is a far smaller problem than an admin
		 * screen that appears to have failed.
		 *
		 * @return array<string,string> Provider id => label, for each one flushed.
		 */
		public static function purge_all() {
			$purged = array();

			foreach ( self::providers() as $id => $provider ) {
				if ( ! call_user_func( $provider['detect'] ) ) {
					continue;
				}

				try {
					call_user_func( $provider['flush'] );
					$purged[ $id ] = $provider['label'];
				} catch ( Throwable $e ) {
					// class_exists guard on purpose: this is the safety net, so
					// it must not be the thing that fatals. (Throwable is PHP 7+;
					// on older PHP the catch simply never matches.)
					if ( class_exists( 'HT_CTC_Utils' ) ) {
						HT_CTC_Utils::debug_log(
							'cache purge failed',
							array(
								'provider' => $id,
								'error'    => $e->getMessage(),
							)
						);
					}
				}
			}

			return $purged;
		}

		/**
		 * Attempt to clear caches from popular caching/performance plugins.
		 *
		 * Hook callback (update_option_ht_ctc_admin_pages) — kept as an instance
		 * method so the existing add_action() signature is unchanged.
		 *
		 * @return void
		 */
		public function clear_cache() {
			self::purge_all();

			// WordPress object cache flush.
			//
			// todo: verify whether this call is actually necessary and remove it if not.
			//
			// Reasoning to remove it:
			// - update_option() already invalidates the cache entry for the updated option
			// via wp_cache_delete()/wp_cache_set() in the 'options' / 'alloptions' groups,
			// so option reads after a save will see the new value without a global flush.
			// - The page/CDN cache plugins handled above (WP Super Cache, W3TC, WP Rocket,
			// LiteSpeed, etc.) take care of the user-facing HTML cache that actually
			// contains the rendered widget.
			// - wp_cache_flush() wipes the ENTIRE object cache (Redis / Memcached). On a
			// busy site this can cause a cache stampede / temporary 503s as every page
			// refills its cached queries simultaneously. This handler fires after EVERY
			// settings save (via update_option_ht_ctc_admin_pages → clear_cache).
			//
			// Reasons it might still be needed (verify before removing):
			// - Some object-cache implementations key the chat widget HTML or computed
			// option bundles outside the 'options' group; a targeted invalidation may
			// not catch them.
			// - Custom hosts / drop-ins with non-standard caching semantics.
			//
			// Action item: confirm on staging that removing this does not leave stale widget
			// output, then drop it (or replace with targeted wp_cache_delete() calls).
			// wp_cache_flush() commented out to prevent 503 errors/cache stampedes on busy sites.
			// if ( function_exists( 'wp_cache_flush' ) ) {
			// wp_cache_flush();
			// }
		}
	}
} // END class_exists check
