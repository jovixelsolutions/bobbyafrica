<?php
/**
 * WP Site Health integration for REST reachability.
 *
 * Surfaces a single test in Tools → Site Health showing whether
 * /wp-json/ is reachable on this environment.
 *
 * Signal source: the apiFetch middleware in
 * src/functions/api-fetch-middleware.js writes
 * `surerank_rest_ok = 'no'` through the AJAX fallback handlers when a
 * save falls back from REST, and the REST save endpoints themselves
 * write `'yes'` on successful completion (both via
 * SureRank\Inc\Functions\Rest_Observation). This removes the need for
 * a server-to-self weekly probe and an anonymous ping endpoint — the
 * signal is drawn from real user save activity, so it's fresh, has no
 * week-long staleness lag, and adds zero public attack surface.
 *
 * When no save has happened yet (fresh install), the option is absent
 * and we report a "good / not yet observed" status rather than
 * implying REST is broken.
 *
 * @package SureRank\Inc\Admin
 * @since 1.7.2
 */

namespace SureRank\Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Functions\Rest_Observation;
use SureRank\Inc\Traits\Get_Instance;

/**
 * REST reachability Site Health surface.
 *
 * @since 1.7.2
 */
class Rest_Site_Health {

	use Get_Instance;

	private const BADGE_LABEL = 'SureRank';
	private const BADGE_COLOR = 'blue';

	/**
	 * Constructor: register on site_status_tests.
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		add_filter( 'site_status_tests', [ $this, 'register_tests' ] );
	}

	/**
	 * Register the REST reachability test.
	 *
	 * @param mixed $tests Existing tests — normally an array keyed by 'direct'/'async', but any
	 *                     third-party filter higher in the chain may return a non-array, so we
	 *                     guard defensively before touching it.
	 * @since 1.7.2
	 * @return mixed
	 */
	public function register_tests( $tests ) {
		if ( ! is_array( $tests ) ) {
			return $tests;
		}

		if ( ! isset( $tests['direct'] ) || ! is_array( $tests['direct'] ) ) {
			$tests['direct'] = [];
		}

		$tests['direct']['surerank_rest_reachable'] = [
			'label' => __( 'SureRank REST API is reachable', 'surerank' ),
			'test'  => [ $this, 'test_rest' ],
		];

		return $tests;
	}

	/**
	 * Test: was the most recent SureRank save successful via REST?
	 *
	 * Reads the `surerank_rest_ok` option which the apiFetch
	 * middleware keeps up to date. Three outcomes:
	 *
	 * - good (not yet observed) — no save attempts have happened yet.
	 *   Cannot claim REST is broken without evidence.
	 * - good (observed reachable) — last observed save went via REST.
	 * - recommended (observed blocked) — last observed save had to
	 *   fall back to admin-ajax.php. Site still works, but eliminating
	 *   the fallback round-trip is worth flagging.
	 *
	 * Not critical because the JS middleware falls back to AJAX
	 * transparently; blocked REST does not break functionality.
	 *
	 * @since 1.7.2
	 * @return array<string, mixed>
	 */
	public function test_rest() {
		$observed = get_option( Rest_Observation::OPTION_NAME, null );

		if ( null === $observed ) {
			return $this->build_result(
				'good',
				__( 'SureRank REST reachability has not been measured yet.', 'surerank' ),
				__( 'No save attempts have happened yet. SureRank will surface any REST-reachability issues here after your first save.', 'surerank' ),
				'surerank_rest_reachable'
			);
		}

		if ( 'yes' === $observed ) {
			return $this->build_result(
				'good',
				__( 'SureRank can reach its REST endpoint over /wp-json/.', 'surerank' ),
				__( 'Save requests use the REST API directly; no fallback is needed.', 'surerank' ),
				'surerank_rest_reachable'
			);
		}

		return $this->build_result(
			'recommended',
			__( 'SureRank\'s REST endpoint is not reachable.', 'surerank' ),
			__( 'A security plugin or firewall appears to be blocking /wp-json/. SureRank automatically falls back to admin-ajax.php for save requests, so editing continues to work. To eliminate the fallback round-trip, consult your security plugin or firewall documentation for allowlisting the /wp-json/surerank/ prefix.', 'surerank' ),
			'surerank_rest_reachable'
		);
	}

	/**
	 * Build a Site Health result array.
	 *
	 * @param string $status      good | recommended | critical.
	 * @param string $label       Short headline.
	 * @param string $description Longer explanation.
	 * @param string $test        Test identifier.
	 * @since 1.7.2
	 * @return array<string, mixed>
	 */
	private function build_result( string $status, string $label, string $description, string $test ): array {
		return [
			'label'       => $label,
			'status'      => $status,
			'badge'       => [
				'label' => self::BADGE_LABEL,
				'color' => self::BADGE_COLOR,
			],
			'description' => wp_kses_post( wpautop( $description ) ),
			'actions'     => '',
			'test'        => $test,
		];
	}
}
