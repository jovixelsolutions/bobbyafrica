<?php
/**
 * WP Site Health integration.
 *
 * Surfaces two SureRank health checks in Tools → Site Health so admins
 * can see at a glance whether the sitemap pipeline is healthy and
 * whether the admin-ajax loopback is reachable. Both signals were
 * previously invisible to users.
 *
 * @package SureRank\Inc\Admin
 * @since 1.7.2
 */

namespace SureRank\Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Functions\Compat;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Site Health integration class.
 *
 * @since 1.7.2
 */
class Site_Health {

	use Get_Instance;

	/**
	 * Threshold after which the sitemap is considered stale and the
	 * freshness check flips to critical. Mirrors Xml_Sitemap's own
	 * self-heal threshold so the UX story is consistent.
	 */
	private const STALE_THRESHOLD = 2 * DAY_IN_SECONDS;

	/**
	 * Constructor: register on site_status_tests.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		add_filter( 'site_status_tests', [ $this, 'register_tests' ] );
	}

	/**
	 * Add SureRank's tests to the Site Health registry.
	 *
	 * @param array{direct?: array<string, mixed>, async?: array<string, mixed>} $tests WP Site Health tests.
	 * @since 1.7.2
	 * @return array<string, mixed>
	 */
	public function register_tests( $tests ) {
		if ( ! is_array( $tests ) ) {
			return $tests;
		}

		if ( ! isset( $tests['direct'] ) ) {
			$tests['direct'] = [];
		}

		$tests['direct']['surerank_sitemap_freshness'] = [
			'label' => __( 'SureRank sitemap rebuild is recent', 'surerank' ),
			'test'  => [ $this, 'test_sitemap_freshness' ],
		];

		$tests['direct']['surerank_loopback'] = [
			'label' => __( 'SureRank admin-ajax loopback is reachable', 'surerank' ),
			'test'  => [ $this, 'test_loopback' ],
		];

		return $tests;
	}

	/**
	 * Test: was the sitemap cache rebuilt recently?
	 *
	 * Status tiers
	 *  good       — last rebuild within 12 hours (2× the 6h cron cadence)
	 *  recommended — last rebuild within the stale threshold (default 2 days)
	 *  critical   — no rebuild yet, or rebuild older than stale threshold
	 *
	 * @since 1.7.2
	 * @return array<string, mixed>
	 */
	public function test_sitemap_freshness() {
		$last = (int) get_option( 'surerank_sitemap_last_successful_rebuild', 0 );

		if ( $last <= 0 ) {
			return $this->build_result(
				'critical',
				__( 'SureRank has not yet rebuilt the sitemap cache on this site.', 'surerank' ),
				__( 'The first rebuild runs automatically via WP-Cron. If your site has been active for more than a few hours and this message persists, WP-Cron may not be firing.', 'surerank' ),
				'surerank_sitemap_freshness'
			);
		}

		$age = time() - $last;

		if ( $age < 12 * HOUR_IN_SECONDS ) {
			return $this->build_result(
				'good',
				__( 'SureRank rebuilt the sitemap cache recently.', 'surerank' ),
				sprintf(
					/* translators: %s: human-readable time, e.g. "3 hours ago". */
					__( 'Last successful rebuild: %s. SureRank normally refreshes the cache every six hours.', 'surerank' ),
					human_time_diff( $last )
				),
				'surerank_sitemap_freshness'
			);
		}

		if ( $age < self::STALE_THRESHOLD ) {
			return $this->build_result(
				'recommended',
				__( 'SureRank has not rebuilt the sitemap cache in over twelve hours.', 'surerank' ),
				sprintf(
					/* translators: %s: human-readable time, e.g. "14 hours ago". */
					__( 'Last successful rebuild: %s. This is still within the stale threshold; the next scheduled cron tick should update it.', 'surerank' ),
					human_time_diff( $last )
				),
				'surerank_sitemap_freshness'
			);
		}

		return $this->build_result(
			'critical',
			__( 'SureRank has not rebuilt the sitemap cache in over the stale threshold.', 'surerank' ),
			sprintf(
				/* translators: %s: human-readable time. */
				__( 'Last successful rebuild: %s. SureRank will attempt a rebuild on the next admin page visit; if the problem persists, WP-Cron or the admin-ajax loopback may be unavailable.', 'surerank' ),
				human_time_diff( $last )
			),
			'surerank_sitemap_freshness'
		);
	}

	/**
	 * Test: is the admin-ajax loopback reachable?
	 *
	 * A failing loopback triggers SureRank's synchronous rebuild fallback
	 * (added in #2361 PR 2), so the status is "recommended" rather than
	 * "critical" — things still work, just slower.
	 *
	 * @since 1.7.2
	 * @return array<string, mixed>
	 */
	public function test_loopback() {
		if ( Compat::is_loopback_ok() ) {
			return $this->build_result(
				'good',
				__( 'SureRank can reach admin-ajax.php via a loopback request.', 'surerank' ),
				__( 'Background sitemap rebuilds run asynchronously.', 'surerank' ),
				'surerank_loopback'
			);
		}

		return $this->build_result(
			'recommended',
			__( 'SureRank cannot reach admin-ajax.php via a loopback request.', 'surerank' ),
			__( 'This is usually caused by a security plugin renaming the admin path, a firewall blocking self-requests, or a host that disables loopbacks. SureRank automatically falls back to running rebuilds synchronously; everything still works, but each rebuild takes slightly longer. See the compatibility guide for details.', 'surerank' ),
			'surerank_loopback'
		);
	}

	/**
	 * Build a Site Health result array with SureRank branding defaults.
	 *
	 * @param string $status      good | recommended | critical.
	 * @param string $label       Short headline.
	 * @param string $description Longer explanation.
	 * @param string $test        Test identifier (used by WP to deduplicate).
	 * @since 1.7.2
	 * @return array<string, mixed>
	 */
	private function build_result( string $status, string $label, string $description, string $test ): array {
		return [
			'label'       => $label,
			'status'      => $status,
			'badge'       => [
				'label' => 'SureRank',
				'color' => 'blue',
			],
			'description' => wp_kses_post( wpautop( $description ) ),
			'actions'     => '',
			'test'        => $test,
		];
	}
}
