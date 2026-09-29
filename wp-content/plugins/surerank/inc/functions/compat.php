<?php
/**
 * Environment-compatibility probes.
 *
 * SureRank depends on two canonical WordPress URL paths working:
 *
 *  - /wp-cron.php          — drives scheduled events (already probed today
 *                            by Helper::are_crons_available()).
 *  - /wp-admin/admin-ajax.php — receives the async background-process
 *                            loopback requests that rebuild the sitemap.
 *
 * Managed hosts, security plugins (WP Ghost, iThemes, Wordfence), and WAF
 * rules routinely block or rename these paths. When that happens, the
 * sitemap rebuild pipeline stalls silently. This class probes both paths
 * and exposes the result as two discrete autoloaded options:
 *
 *  - surerank_cron_test_ok       (existing; set by Helper::are_crons_available)
 *  - surerank_loopback_ok        (new; set by this class)
 *
 * Callers use is_loopback_ok() to decide whether to dispatch the batch
 * asynchronously or fall back to a synchronous in-cron-tick execution
 * path.
 *
 * Why separate options instead of a single array option:
 *  - WordPress autoloading is per-option; a compound option forces both
 *    fields into every request even if only one is read.
 *  - Concurrent probes writing a compound option race; discrete options
 *    with update_option() do not.
 *  - Grep-ability: `surerank_loopback_ok` is easy to find.
 *
 * @package SureRank\Inc\Functions
 * @since 1.7.2
 */

namespace SureRank\Inc\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Traits\Get_Instance;

/**
 * Compat class.
 *
 * @since 1.7.2
 */
class Compat {

	use Get_Instance;

	/**
	 * Autoloaded option storing the cached admin-ajax loopback probe
	 * result. Values: 'yes', 'no', or missing (not yet probed).
	 */
	public const LOOPBACK_OPTION = 'surerank_loopback_ok';

	/**
	 * Timestamp (seconds) of the last probe run. Used to throttle
	 * refreshes on-demand so a misbehaving caller can't force a probe
	 * on every request.
	 */
	public const LAST_PROBED_OPTION = 'surerank_compat_last_probed';

	/**
	 * Minimum time between on-demand re-probes. A full re-probe normally
	 * happens on the weekly cron hook registered below; callers that
	 * force a refresh outside that cadence are rate-limited to this
	 * interval to keep hot paths cheap.
	 */
	public const REPROBE_INTERVAL = HOUR_IN_SECONDS;

	/**
	 * Cron hook used for the weekly re-probe. Separate from the sitemap
	 * cron hook so probe failures do not interfere with sitemap work.
	 */
	public const WEEKLY_CRON_HOOK = 'surerank_compat_weekly_probe';

	/**
	 * AJAX action used for the loopback probe. Handler returns a tiny
	 * "ok" payload; used to prove that /wp-admin/admin-ajax.php is
	 * reachable from the server to itself.
	 */
	public const PING_ACTION = 'surerank_compat_ping';

	/**
	 * Constructor: register hooks.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		add_action(
			self::WEEKLY_CRON_HOOK,
			static function (): void {
				self::refresh_loopback_probe();
			}
		);
		add_action( 'wp_ajax_' . self::PING_ACTION, [ self::class, 'ajax_ping' ] );
		add_action( 'wp_ajax_nopriv_' . self::PING_ACTION, [ self::class, 'ajax_ping' ] );
		add_action( 'init', [ self::class, 'ensure_weekly_probe_scheduled' ] );
	}

	/**
	 * Remove scheduled hooks and cached probe state. Intended for use
	 * from plugin deactivation / uninstall paths.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public static function teardown(): void {
		$timestamp = wp_next_scheduled( self::WEEKLY_CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::WEEKLY_CRON_HOOK );
		}

		delete_option( self::LOOPBACK_OPTION );
		delete_option( self::LAST_PROBED_OPTION );
	}

	/**
	 * Ensure the weekly re-probe is scheduled. Runs on init; idempotent.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public static function ensure_weekly_probe_scheduled(): void {
		if ( wp_next_scheduled( self::WEEKLY_CRON_HOOK ) ) {
			return;
		}

		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'weekly', self::WEEKLY_CRON_HOOK );
	}

	/**
	 * Whether the admin-ajax loopback is known-reachable.
	 *
	 * Reads the cached probe result. If the option has never been set
	 * (fresh install or cache wiped), returns true optimistically so
	 * the existing async code path runs unchanged. The first weekly
	 * probe will populate the flag; Sync::start_building_cache can
	 * also trigger a refresh lazily.
	 *
	 * @since 1.7.2
	 * @return bool
	 */
	public static function is_loopback_ok(): bool {
		$value = get_option( self::LOOPBACK_OPTION, '' );
		if ( 'no' === $value ) {
			return false;
		}

		return true;
	}

	/**
	 * Force a re-probe of the admin-ajax loopback.
	 *
	 * Rate-limited to self::REPROBE_INTERVAL to protect against storms:
	 * a caller that invokes this on every request still pays only one
	 * probe per hour. Pass $force=true to bypass the rate limit — used
	 * by the weekly cron and by plugin activation.
	 *
	 * @param bool $force Bypass the rate limit.
	 * @since 1.7.2
	 * @return bool The fresh probe result.
	 */
	public static function refresh_loopback_probe( bool $force = false ): bool {
		if ( ! $force ) {
			$last = (int) get_option( self::LAST_PROBED_OPTION, 0 );
			if ( $last > 0 && ( time() - $last ) < self::REPROBE_INTERVAL ) {
				return self::is_loopback_ok();
			}
		}

		$result = self::probe_loopback(
			admin_url( 'admin-ajax.php' ) . '?action=' . self::PING_ACTION
		);

		update_option( self::LOOPBACK_OPTION, $result ? 'yes' : 'no' );
		update_option( self::LAST_PROBED_OPTION, time(), false );

		return $result;
	}

	/**
	 * Pure probe: send a loopback GET to the given URL and return
	 * whether the response indicates success (2xx).
	 *
	 * Does not read or write any options. Callers that want to cache
	 * the result are responsible for storing it.
	 *
	 * @param string $url     Absolute URL to probe.
	 * @param int    $timeout Seconds.
	 * @since 1.7.2
	 * @return bool
	 */
	public static function probe_loopback( string $url, int $timeout = 5 ): bool {
		$args = apply_filters(
			'surerank_compat_probe_args',
			[
				'timeout'   => $timeout, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
				'blocking'  => true,
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
			]
		);

		// wp_remote_get (not wp_safe_remote_get): the probe targets an
		// admin URL derived from home_url(), which on staging/local
		// installs can resolve to a private IP. wp_safe_remote_get
		// would reject those and produce false-negative probe results.
		// Core's own Site Health loopback test (`can_perform_loopback`)
		// uses the unsafe variant for the same reason.
		$response = wp_remote_get( esc_url_raw( $url ), $args ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.wp_remote_get_wp_remote_get -- intentional: self-loopback to admin URL which may be private on staging.

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );

		return $code >= 200 && $code < 300;
	}

	/**
	 * AJAX handler for the loopback probe. Returns a minimal OK payload.
	 *
	 * Anonymous (registered for both priv and nopriv) because the probe
	 * loopback is not authenticated and the handler needs to respond
	 * regardless of caller state. The payload is trivial; no DB reads,
	 * no option writes, no hooks beyond what wp_send_json_success fires.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public static function ajax_ping(): void {
		wp_send_json_success(
			[
				'ok'        => true,
				'timestamp' => time(),
			]
		);
	}
}
