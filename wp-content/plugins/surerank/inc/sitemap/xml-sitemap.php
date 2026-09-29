<?php
/**
 * Common Meta Data
 *
 * This file handles functionality to generate sitemap in frontend.
 *
 * @package surerank
 * @since 0.0.1
 */

namespace SureRank\Inc\Sitemap;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Functions\Cache;
use SureRank\Inc\Functions\Compat;
use SureRank\Inc\Functions\Cron;
use SureRank\Inc\Functions\Settings;
use SureRank\Inc\Traits\Get_Instance;

/**
 * XML Sitemap
 * Handles functionality to generate XML sitemaps.
 *
 * @since 1.0.0
 */
class Xml_Sitemap extends Sitemap {

	use Get_Instance;

	/**
	 * Staleness threshold for self-healing rebuilds — 2 days.
	 *
	 * Not a filter: historical tuning happens in code, not via site
	 * configuration, so a filter hook without customer evidence is a
	 * speculative extension point.
	 *
	 * @since 1.7.2
	 */
	private const STALE_REBUILD_THRESHOLD = 2 * DAY_IN_SECONDS;
	/**
	 * Sitemap slug to be used across the class.
	 *
	 * @var string
	 */
	private static $sitemap_slug = 'sitemap_index';

	/**
	 * Constructor
	 *
	 * Sets up the sitemap functionality if XML sitemaps are enabled in settings.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct() {

		add_filter(
			'surerank_flush_rewrite_settings',
			[ $this, 'flush_settings' ],
			10,
			1
		);

		if ( ! Settings::get( 'enable_xml_sitemap' ) ) {
			return;
		}

		add_filter( 'wp_sitemaps_enabled', '__return_false' );
		add_action( 'template_redirect', [ $this, 'template_redirect' ] );
		add_action( 'parse_query', [ $this, 'parse_query' ], 1 );
	}

	/**
	 * Array of settings to flush rewrite rules on update settings
	 *
	 * @param array<string, mixed> $settings Existing settings to flush.
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	public function flush_settings( $settings ) {
		$settings[] = 'enable_xml_sitemap';
		$settings[] = 'enable_xml_image_sitemap';
		return $settings;
	}

	/**
	 * Returns the sitemap slug.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public static function get_slug(): string {
		$sitemap_slug = apply_filters( 'surerank_sitemap_slug', self::$sitemap_slug );
		$sitemap_slug = empty( $sitemap_slug ) ? self::$sitemap_slug : $sitemap_slug;
		return $sitemap_slug . '.xml';
	}

	/**
	 * Redirects default WordPress sitemap requests to custom sitemap URLs.
	 *
	 * Uses HTTP 302 (not 301) because 301s are aggressively cached by
	 * browsers, proxies, and CDNs. If SureRank's sitemap URL ever changes
	 * (via filter, plugin uninstall, or another SEO plugin taking over), a
	 * cached 301 would continue redirecting users to a URL that may no
	 * longer exist. 302 avoids that long-tail cache invalidation hazard.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function template_redirect() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$current_url = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$current_url = explode( '/', $current_url );
		$last_url    = end( $current_url );

		$sitemap = [
			'sitemap.xml',
			'wp-sitemap.xml',
			'index.xml',
		];

		if ( in_array( $last_url, $sitemap, true ) ) {
			wp_safe_redirect( '/' . self::get_slug(), 302 );
			exit;
		}
	}

	/**
	 * Parses custom query variables and triggers sitemap generation.
	 *
	 * @param \WP_Query $query Current query object.
	 * @since 1.0.0
	 * @return void
	 */
	public function parse_query( \WP_Query $query ) {
		if ( ! $query->is_main_query() && ! is_admin() ) {
			return;
		}

		$type  = sanitize_text_field( get_query_var( 'surerank_sitemap' ) );
		$style = sanitize_text_field( get_query_var( 'surerank_sitemap_type' ) );

		if ( ! $type && ! $style ) {
			return;
		}

		if ( $style ) {
			Utils::output_stylesheet( $style );
		}

		$page      = absint( get_query_var( 'surerank_sitemap_page' ) ) ? absint( get_query_var( 'surerank_sitemap_page' ) ) : 1;
		$threshold = apply_filters( 'surerank_sitemap_threshold', 200 );

		do_action( 'surerank_sitemap_before_generation', $type, $page, $threshold );

		$this->generate_sitemap( $type, $page, $threshold );

		do_action( 'surerank_sitemap_after_generation', $type, $page, $threshold );
	}

	/**
	 * Generates the appropriate sitemap based on the requested type.
	 *
	 * Dispatch order, each exiting on success:
	 *   1. Live cache (the authoritative sitemap)
	 *   2. Stale-while-revalidate from the rebuild backup (sitemap.old/)
	 *   3. Miss response (503 + valid empty <sitemapindex/>)
	 *
	 * Stale-while-revalidate ensures Googlebot and other crawlers see the
	 * last known-good sitemap with HTTP 200 during a rebuild window rather
	 * than 503. That materially reduces the chance of a rebuild triggering
	 * transient sitemap errors in Google Search Console.
	 *
	 * @param string $type Sitemap type requested.
	 * @param int    $page Current page number for paginated sitemaps.
	 * @param int    $threshold Threshold for splitting sitemaps.
	 * @since 1.0.0
	 * @return void
	 */
	public function generate_sitemap( string $type, int $page, $threshold ): void {

		// Self-healing: if the 6-hourly cron has silently stopped firing
		// and the sitemap is stale past the threshold, schedule a one-shot
		// recovery rebuild. Called once (not per dispatch branch) to avoid
		// the duplicate option-read on cache-miss hits.
		$this->maybe_schedule_stale_rebuild();

		// 1. Live cache — exits on success, falls through on miss.
		if ( '1' === $type ) {
			$sitemap_index = Cache::get_file( 'sitemap/sitemap_index.json' );
			if ( $sitemap_index ) {
				$sitemap = json_decode( $sitemap_index, true );
				if ( is_array( $sitemap ) ) {
					$this->sitemapindex( $sitemap );
				}
			}
		} else {
			$this->generate_main_sitemap( $type, $page, $threshold );
		}

		// 2. Stale-while-revalidate: serve the rebuild backup if available.
		if ( Cache::has_rebuild_backup( 'sitemap' ) ) {
			$this->serve_from_backup( $type, $page );
		}

		// 3. Miss — cache absent or unreadable. Emit a valid 503.
		$this->send_miss_response();
	}

	/**
	 * Generates the main sitemap for a specific type, page, and offset.
	 *
	 * Returns (without exiting) when the cache is missing, so the caller
	 * (generate_sitemap) can fall through to stale or miss handling.
	 * Exits on success.
	 *
	 * @param string $type Post type or taxonomy.
	 * @param int    $page Current page number.
	 * @param int    $offset Number of posts to retrieve.
	 * @since 1.0.0
	 * @return void
	 */
	public function generate_main_sitemap( string $type, int $page, int $offset = 1000 ) {
		remove_all_actions( 'parse_query' );

		if ( ! Cache::file_exists( 'sitemap/sitemap_index.json' ) ) {
			return;
		}

		$prefix_param = sanitize_text_field( get_query_var( 'surerank_prefix' ) );
		$sitemap      = $this->get_sitemap_from_cache( $type, $page, $prefix_param );
		$this->generate_main_sitemap_xml( $sitemap );
	}

	/**
	 * Emit a minimal valid <sitemapindex/> XML with HTTP 503 + Retry-After.
	 *
	 * Called when the pre-built sitemap cache is missing (e.g., first install
	 * before the first rebuild completes, or after a failed rebuild). Googlebot
	 * treats 503 with Retry-After as a transient condition and does not remove
	 * URLs from its index. Critically, this path MUST exit so WordPress does
	 * not fall through to its default query dispatch and render the blog page.
	 *
	 * Retry-After seconds are filterable via `surerank_sitemap_miss_retry_after`
	 * (default 300 seconds / 5 minutes).
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public function send_miss_response(): void {
		$retry_after = (int) apply_filters( 'surerank_sitemap_miss_retry_after', 300 );

		$this->send_xml_response(
			$this->render_miss_response(),
			503,
			[
				'Retry-After'  => (string) max( 1, $retry_after ),
				'X-Robots-Tag' => 'noindex',
			]
		);
	}

	/**
	 * Build a minimal valid <sitemapindex/> document for the miss response.
	 *
	 * Pure: returns a string; does not send headers or exit.
	 *
	 * @since 1.7.2
	 * @return string
	 */
	public function render_miss_response(): string {
		return Utils::sitemap_index( [] );
	}

	/**
	 * Outputs the sitemap index as XML.
	 *
	 * @param array<string, mixed>|array<int, string> $sitemap Sitemap index data.
	 * @since 1.0.0
	 * @return void
	 */
	public function sitemapindex( array $sitemap ) {
		$this->send_xml_response( Utils::sitemap_index( $sitemap ) );
	}

	/**
	 * Outputs the main sitemap as XML.
	 *
	 * @param array<string, mixed>|array<int, string> $sitemap Sitemap data for main sitemap.
	 * @since 1.0.0
	 * @return void
	 */
	public function generate_main_sitemap_xml( array $sitemap ) {
		$this->send_xml_response( Utils::sitemap_main( $sitemap ) );
	}

	/**
	 * Get sitemap url
	 *
	 * @return string
	 */
	public function get_sitemap_url() {
		return home_url( self::get_slug() );
	}

	/**
	 * Schedule a one-shot recovery rebuild when the last successful
	 * rebuild is older than STALE_REBUILD_THRESHOLD.
	 *
	 * Gated on `manage_options` (not `is_admin()`, which is true for
	 * any authenticated admin-ajax call and so not a security
	 * boundary) or cron context. No-op on first install (let
	 * Cron::ensure_cron_scheduled bootstrap instead) and when a
	 * rebuild is already queued.
	 *
	 * @since 1.7.2
	 * @return void
	 */
	protected function maybe_schedule_stale_rebuild(): void {
		if ( ! current_user_can( 'manage_options' ) && ! wp_doing_cron() ) {
			return;
		}

		if ( wp_next_scheduled( Cron::SITEMAP_CRON_EVENT ) ) {
			return;
		}

		$last = (int) get_option( 'surerank_sitemap_last_successful_rebuild', 0 );
		if ( $last <= 0 || ( time() - $last ) < self::STALE_REBUILD_THRESHOLD ) {
			return;
		}

		// Respect the probe rate limit (false) so a blackholing loopback
		// doesn't freeze admin page rendering for 5 seconds.
		Compat::refresh_loopback_probe( false );

		wp_schedule_single_event(
			time() + MINUTE_IN_SECONDS,
			Cron::SITEMAP_CRON_EVENT,
			[ 'yes' ]
		);
	}

	/**
	 * Attempt to serve the request from the rebuild backup (sitemap.old/).
	 *
	 * Called when the live cache is unavailable but an atomic rebuild is
	 * in progress and the previous generation's cache is preserved. Returns
	 * (without exiting) when no matching data is in the backup either, so
	 * the caller can continue to the miss handler.
	 *
	 * @param string $type Sitemap type requested.
	 * @param int    $page Current page number.
	 * @since 1.7.2
	 * @return void
	 */
	protected function serve_from_backup( string $type, int $page ): void {
		if ( '1' === $type ) {
			$stale = Cache::read_rebuild_backup( 'sitemap/sitemap_index.json' );
			if ( $stale ) {
				$sitemap = json_decode( $stale, true );
				if ( is_array( $sitemap ) ) {
					$this->sitemapindex( $sitemap );
				}
			}
			return;
		}

		$prefix_param = sanitize_text_field( get_query_var( 'surerank_prefix' ) );
		$sitemap      = $this->get_sitemap_from_cache( $type, $page, $prefix_param, true );
		if ( ! empty( $sitemap ) ) {
			$this->generate_main_sitemap_xml( $sitemap );
		}
	}

	/**
	 * Send an XML response with the given body, status, and optional extra headers.
	 *
	 * Centralizes transport (headers + echo + exit) so render methods can stay pure.
	 *
	 * @param string               $xml          XML body to emit.
	 * @param int                  $status       HTTP status code. Defaults to 200.
	 * @param array<string,string> $extra_headers Optional additional response headers.
	 * @since 1.7.2
	 * @return void
	 */
	protected function send_xml_response( string $xml, int $status = 200, array $extra_headers = [] ): void {
		if ( ! headers_sent() ) {
			http_response_code( $status );
		}

		Utils::output_headers();

		foreach ( $extra_headers as $name => $value ) {
			if ( ! headers_sent() ) {
				header( "{$name}: {$value}" );
			}
		}

		echo $xml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- XML is safely generated by Utils::sitemap_index/sitemap_main

		exit;
	}

	/**
	 * Get sitemap from cache
	 *
	 * @param string $type Sitemap type.
	 * @param int    $page Page number.
	 * @param string $prefix_param Prefix name.
	 * @param bool   $from_backup When true, read chunks from the rebuild
	 *                            backup (sitemap.old/) for stale-while-revalidate.
	 * @return array<string, mixed>|array<int, string>
	 */
	private function get_sitemap_from_cache( string $type, int $page, string $prefix_param, bool $from_backup = false ) {
		// Calculate which chunks belong to this page based on threshold and chunk size.
		$sitemap_threshold = apply_filters( 'surerank_sitemap_threshold', 200 );
		$chunk_size        = apply_filters( 'surerank_sitemap_json_chunk_size', 20 );

		$chunks_per_sitemap = (int) ceil( $sitemap_threshold / $chunk_size );
		$start_chunk        = ( $page - 1 ) * $chunks_per_sitemap + 1;
		$end_chunk          = $page * $chunks_per_sitemap;

		$combined_sitemap = [];
		for ( $chunk_number = $start_chunk; $chunk_number <= $end_chunk; $chunk_number++ ) {
			$chunk_file      = $prefix_param . '-' . $type . '-chunk-' . $chunk_number . '.json';
			$cache_path      = 'sitemap/' . $chunk_file;
			$cache_file_data = $from_backup
				? Cache::read_rebuild_backup( $cache_path )
				: Cache::get_file( $cache_path );

			if ( ! $cache_file_data ) {
				continue;
			}

			$chunk_data = json_decode( $cache_file_data, true );
			if ( is_array( $chunk_data ) ) {
				$combined_sitemap = array_merge( $combined_sitemap, $chunk_data );
			}
		}

		return $combined_sitemap;
	}
}
