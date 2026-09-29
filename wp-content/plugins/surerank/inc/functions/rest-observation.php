<?php
/**
 * Rest_Observation
 *
 * Records whether SureRank's REST API is reachable from real save
 * attempts. Two writers:
 *
 * - Rest_Observation::mark_blocked() is called from the AJAX fallback
 *   handlers in inc/ajax/save-endpoints.php. Reaching an AJAX handler
 *   means the JS middleware fell back from REST after a transport
 *   failure, which is evidence REST is blocked.
 * - Rest_Observation::mark_reachable() is called from the REST save
 *   endpoints (inc/api/post.php, inc/api/term.php, inc/api/admin.php)
 *   on a successful save. Reaching REST at all is evidence REST works.
 *
 * Both writers are gated by a short transient lock so burst saves on
 * editor-heavy sites do not hammer wp_options. Only state changes are
 * written; repeat writes of the same value short-circuit.
 *
 * The stored option is consumed by Rest_Site_Health to surface REST
 * reachability in Tools -> Site Health.
 *
 * @package SureRank\Inc\Functions
 * @since 1.7.2
 */

namespace SureRank\Inc\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * REST reachability observation writer.
 *
 * @since 1.7.2
 */
class Rest_Observation {

	/**
	 * Option name storing the last observed reachability state.
	 * Values: 'yes' | 'no'. Absence means "not yet observed" and
	 * Rest_Site_Health treats that as healthy (no signal to surface).
	 */
	public const OPTION_NAME = 'surerank_rest_ok';

	/**
	 * Transient lock key that rate-limits writes. A brief lock is
	 * enough to collapse burst-save storms into one write per site
	 * per window without blocking the actual save path.
	 */
	private const LOCK_KEY = 'surerank_rest_ok_write_lock';

	/**
	 * Window (seconds) during which a second write of the same class
	 * is skipped.
	 */
	private const LOCK_TTL = 60;

	/**
	 * Record that REST is currently blocked (AJAX fallback just ran).
	 *
	 * @return void
	 */
	public static function mark_blocked(): void {
		self::maybe_write( 'no' );
	}

	/**
	 * Record that REST is currently reachable (a REST save just
	 * completed successfully).
	 *
	 * @return void
	 */
	public static function mark_reachable(): void {
		self::maybe_write( 'yes' );
	}

	/**
	 * Write the given state if it differs from what's stored AND no
	 * write has happened in the last LOCK_TTL seconds.
	 *
	 * Not security-sensitive; a benign TOCTOU race between two
	 * concurrent writes converges on the correct value within one
	 * subsequent save.
	 *
	 * @param string $value Either 'yes' or 'no'.
	 * @return void
	 */
	private static function maybe_write( string $value ): void {
		$current = get_option( self::OPTION_NAME, null );

		if ( $current === $value ) {
			return;
		}

		if ( false !== get_transient( self::LOCK_KEY ) ) {
			return;
		}

		set_transient( self::LOCK_KEY, 1, self::LOCK_TTL );
		update_option( self::OPTION_NAME, $value, false );
	}
}
