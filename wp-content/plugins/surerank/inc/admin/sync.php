<?php
/**
 * Admin Sync
 *
 * @since 1.2.0
 * @package surerank
 */

namespace SureRank\Inc\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\BatchProcess\Cleanup;
use SureRank\Inc\BatchProcess\Process;
use SureRank\Inc\BatchProcess\Sync_Posts;
use SureRank\Inc\BatchProcess\Sync_Taxonomies;
use SureRank\Inc\Functions\Cache;
use SureRank\Inc\Functions\Compat;
use SureRank\Inc\Functions\Helper;
use SureRank\Inc\Schema\Helper as Schema_Helper;
use SureRank\Inc\Sitemap\Checksum;
use SureRank\Inc\Sitemap\Utils;
use SureRank\Inc\Traits\Get_Instance;
use SureRank\Inc\Traits\Logger;
use WP_CLI;

/**
 * Admin Sync
 *
 * @since 1.2.0
 */
class Sync {
	use Get_Instance;
	use Logger;

	/**
	 * All processes.
	 *
	 * @since 1.2.0
	 * @var object Class object.
	 * @access public
	 */
	public static $processes;

	/**
	 * Constructor
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function __construct() {
		add_action( 'surerank_start_building_cache', [ $this, 'start_building_cache' ], 10, 1 );
		add_action( 'surerank_batch_process_complete', [ $this, 'batch_process_complete' ] );
		add_filter( 'surerank_dashboard_localization_vars', [ $this, 'add_localization_vars' ] );
	}

	/**
	 * Add localization variables.
	 *
	 * @since 1.4.3
	 * @param array<string, mixed> $vars Localization variables.
	 * @return array<string, mixed> Localization variables.
	 */
	public function add_localization_vars( $vars ) {
		$vars['crons_available']    = Helper::are_crons_available();
		$vars['sitemap_cpts']       = array_keys( Sync::get_instance()->get_included_post_types() );
		$vars['sitemap_taxonomies'] = array_map(
			static function( $taxonomy ) {
				return $taxonomy['slug'];
			},
			Sync::get_instance()->get_included_taxonomies()
		);

		return $vars;
	}

	/**
	 * Batch Process Complete.
	 *
	 * Finalizes an in-progress sitemap rebuild by atomically discarding
	 * the preserved backup (sitemap.old/). At this point sitemap/ contains
	 * the freshly-built cache and is the authoritative source.
	 *
	 * Also records a timestamp of the last successful rebuild, used by
	 * the staleness-based self-healing retry introduced in PR 2 and by
	 * the sitemap health surface exposed via WP_Site_Health.
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public function batch_process_complete() {
		Checksum::get_instance()->update_cache_checksum( Checksum::get_instance()->get_checksum() );

		Cache::commit_atomic_rebuild( 'sitemap' );

		update_option( 'surerank_sitemap_last_successful_rebuild', time(), false );
	}

	/**
	 * Start Building the cache.
	 *
	 * @param string $force Force flag to regenerate cache.
	 * @since 1.2.0
	 * @return void
	 */
	public function start_building_cache( $force = '' ) {

		if ( empty( $force ) && ! $this->should_initiate_batch_process() ) {
			if ( defined( 'WP_CLI' ) ) {
				WP_CLI::line( 'Checksum are matching, no data available to sync.' );
			} else {
				self::log( 'Checksum are matching, no data available to sync.' );
			}
			return;
		}

		// Get the singleton instance of Process.
		self::$processes = Process::get_instance();

		// Rotate the current sitemap cache to a backup slot so a failed
		// rebuild never leaves the site without a sitemap. The backup is
		// discarded by batch_process_complete() on success, or rolled
		// back by abort_atomic_rebuild() on fatal failure. See #2361.
		//
		// Fallback: on hosts where native rename() is unavailable (non-direct
		// filesystem transports), atomic rotation will fail. In that case,
		// fall back to the pre-atomic clear_all() behaviour so the site can
		// still regenerate its sitemap — just without stale-while-revalidate.
		if ( ! Cache::begin_atomic_rebuild( 'sitemap' ) ) {
			self::log( 'Atomic rebuild unavailable; falling back to cache clear.' );
			Cache::clear_all();
		}

		$classes = $this->generate_classes();

		if ( defined( 'WP_CLI' ) ) {
			$this->run_batch_synchronously(
				$classes,
				static function ( string $message ): void {
					WP_CLI::line( $message );
				}
			);
		} elseif ( ! Compat::is_loopback_ok() ) {
			// Loopback to admin-ajax.php is blocked on this environment
			// (WP Ghost / iThemes / Wordfence / WAF / host restrictions).
			// Dispatch would silently drop the queue; run the batch
			// synchronously in the current cron tick instead so the cache
			// still rebuilds. See #2361 PR 2 + Compat::is_loopback_ok().
			self::log( 'admin-ajax loopback unavailable; running sitemap batch synchronously.' );
			$this->run_batch_synchronously( $classes );
		} else {
			// Add all classes to batch queue.
			foreach ( $classes as $key => $class ) {
				if ( method_exists( self::$processes, 'push_to_queue' ) ) {
					self::$processes->push_to_queue( $class );
				}
			}

			if ( method_exists( self::$processes, 'save' ) ) {
				// Dispatch Queue.
				self::$processes->save()->dispatch();
			}
		}
	}

	/**
	 * Run the batch synchronously, one class after another, within the
	 * current PHP process.
	 *
	 * Used by the WP-CLI path and by the synchronous fallback the next
	 * commit wires into start_building_cache() when the admin-ajax
	 * loopback has been detected as blocked.
	 *
	 * The logger argument is passed so CLI calls can emit to WP_CLI::line
	 * and non-CLI callers can route the same messages through self::log
	 * (or a test double). Extracted here to keep the WP-CLI path and the
	 * fallback path strictly identical — drift between them would have
	 * been hard to spot.
	 *
	 * @param array<int, object> $classes Batch classes to run; each must expose import().
	 * @param callable|null      $logger  Function called with each status message.
	 *                                    Defaults to self::log().
	 * @since 1.7.2
	 * @return void
	 */
	public function run_batch_synchronously( array $classes, ?callable $logger = null ): void {
		$log = $logger ?? static function ( string $message ): void {
			self::log( $message );
		};

		// Sync fallback on large sites (10k+ URLs) writes thousands of
		// chunk files in-request. Raise the two PHP ceilings most likely
		// to cut the rebuild short mid-flight and leave sitemap.old/
		// permanently stuck as the live cache.
		wp_raise_memory_limit( 'admin' );
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 0 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- safe_mode hosts silently refuse; we don't want a warning here.
		}

		$log( 'Batch Process Started..' );

		try {
			foreach ( $classes as $class ) {
				if ( is_object( $class ) && method_exists( $class, 'import' ) ) {
					$class->import();
				}
			}
		} catch ( \Throwable $e ) {
			// Release the atomic-rebuild slot so a future rebuild
			// can begin; otherwise `sitemap.old/` lingers and
			// Cache::has_rebuild_backup() keeps reporting the
			// site as mid-rebuild indefinitely.
			Cache::abort_atomic_rebuild( 'sitemap' );
			$log( 'Batch process failed: ' . $e->getMessage() );
			return;
		}

		$log( 'Batch Process Complete!' );
	}

	/**
	 * Prepare cache.
	 *
	 * @since 1.4.3
	 * @return array<int, object>
	 */
	public function generate_classes() {
		$classes    = [];
		$chunk_size = apply_filters( 'surerank_sitemap_json_chunk_size', 20 );
		$classes    = array_merge( $classes, $this->create_post_type_sync_classes( $chunk_size ) );
		$classes    = array_merge( $classes, $this->create_taxonomy_sync_classes( $chunk_size ) );
		$classes    = apply_filters( 'surerank_batch_process_classes', $classes );
		return array_merge( $classes, $this->create_cleanup_class() );
	}

	/**
	 * Check if batch process should be initiated.
	 *
	 * @since 1.2.0
	 * @return bool
	 */
	public function should_initiate_batch_process() {
		$data_updates_checksum = Checksum::get_instance()->get_checksum();
		$sync_process_checksum = Checksum::get_instance()->get_cache_checksum();

		if ( empty( $data_updates_checksum ) ) {
			// 1st time sync, no previous checksum available.
			Checksum::get_instance()->update_checksum();
			return true;
		}

		if ( empty( $sync_process_checksum ) ) {
			return true;
		}

		if ( $data_updates_checksum === $sync_process_checksum ) {
			// Checksums match but the cache is missing or incomplete — a
			// previous rebuild must have failed or been interrupted after
			// the sync-process checksum was written but before the cache
			// was fully populated. Without this guard the site would be
			// stuck serving the miss handler forever (because the next
			// rebuild would short-circuit on matching checksums). See #2361.
			//
			// Note: during an in-flight atomic rebuild the live index is
			// intentionally absent. The transient lock acquired by
			// begin_atomic_rebuild() prevents a second rebuild from
			// starting and discarding the backup in that window.
			if ( ! Cache::file_exists( 'sitemap/sitemap_index.json' ) ) {
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Get all post types.
	 *
	 * @return array<string, mixed>|array<int, string>
	 */
	public function get_included_post_types() {
		return apply_filters(
			'surerank_sitemap_enabled_cpts',
			Helper::get_public_cpts()
		);
	}

	/**
	 * Get all taxonomies.
	 *
	 * @return array<string, mixed>|array<int, string>
	 */
	public function get_included_taxonomies() {
		return apply_filters(
			'surerank_sitemap_enabled_taxonomies',
			Schema_Helper::get_instance()->get_taxonomies(
				[
					'public' => true,
				]
			)
		);
	}

	/**
	 * Get count of indexable posts for a specific post type
	 *
	 * @param string $post_type The post type.
	 * @since 1.4.3
	 * @return int
	 */
	public function get_indexable_posts_count( string $post_type ): int {
		$args = [
			'post_type'           => $post_type,
			'post_status'         => 'publish',
			'posts_per_page'      => 1,
			'fields'              => 'ids',
			'no_found_rows'       => false,
			'ignore_sticky_posts' => true,
			'meta_query'          => Utils::get_indexable_meta_query( $post_type ), //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		];

		$query = new \WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Get count of indexable terms for a specific taxonomy
	 *
	 * @param string $taxonomy The taxonomy name.
	 * @since 1.2.0
	 * @return int
	 */
	public function get_indexable_terms_count( string $taxonomy ) {
		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'number'     => 0,
			'fields'     => 'count',
			'meta_query' => Utils::get_indexable_meta_query( $taxonomy ), //phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		];

		$count = get_terms( $args );
		if ( is_wp_error( $count ) ) {
			return 0;
		}

		return (int) $count;
	}

	/**
	 * Finalize cache generation process.
	 *
	 * @since 1.4.3
	 * @return void
	 */
	public function finalize_cache_generation(): void {
		$cleanup = Cleanup::get_instance();
		$cleanup->import();
		$this->batch_process_complete();
	}

	/**
	 * Create taxonomy sync classes
	 *
	 * @param int $chunk_size Chunk size for pagination.
	 * @return array<int, object>
	 */
	private function create_taxonomy_sync_classes( int $chunk_size ) {
		$classes    = [];
		$taxonomies = $this->get_included_taxonomies();

		if ( empty( $taxonomies ) ) {
			return $classes;
		}

		$excluded_taxonomies = [ 'post_format' ];

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! isset( $taxonomy['slug'] ) ) {
				continue; // Skip if not a valid taxonomy slug.
			}

			if ( in_array( $taxonomy['slug'], $excluded_taxonomies, true ) ) {
				continue;
			}

			$indexable_count = $this->get_indexable_terms_count( $taxonomy['slug'] );
			$classes         = array_merge( $classes, $this->create_sync_classes( $indexable_count, $chunk_size, $taxonomy['slug'], 'taxonomy' ) );
		}

		return $classes;
	}

	/**
	 * Create cleanup class
	 * /**
	 *
	 * @return array<int, object>
	 */
	private function create_cleanup_class() {
		return [ Cleanup::get_instance() ];
	}

	/**
	 * Create post type sync classes
	 *
	 * @param int $chunk_size Chunk size for pagination.
	 * @return array<int, object>
	 */
	private function create_post_type_sync_classes( int $chunk_size ) {
		$classes = [];
		$cpts    = $this->get_included_post_types();

		if ( empty( $cpts ) ) {
			return $classes;
		}

		$excluded_cpts = [ 'attachment' ];

		foreach ( $cpts as $cpt ) {
			if ( ! isset( $cpt->name ) ) {
				continue; // Skip if not a valid post type object.
			}

			if ( in_array( $cpt->name, $excluded_cpts, true ) ) {
				continue;
			}

			$indexable_count = $this->get_indexable_posts_count( $cpt->name );
			$classes         = array_merge( $classes, $this->create_sync_classes( $indexable_count, $chunk_size, $cpt->name, 'post' ) );
		}

		return $classes;
	}

	/**
	 * Create sync classes for given count and parameters.
	 *
	 * @param int    $indexable_count Number of indexable items.
	 * @param int    $chunk_size Chunk size for pagination.
	 * @param string $name Taxonomy or post type name.
	 * @param string $type Type of sync class (taxonomy or post).
	 * @return array<int, object>
	 */
	private function create_sync_classes( int $indexable_count, int $chunk_size, string $name, string $type ) {
		$classes = [];

		if ( $indexable_count <= 0 ) {
			return $classes;
		}

		$no_of_times = (int) ceil( $indexable_count / $chunk_size );

		for ( $i = 1; $i <= $no_of_times; $i++ ) {
			$offset = ( $i - 1 ) * $chunk_size;

			if ( $type === 'taxonomy' ) {
				$classes[] = new Sync_Taxonomies( $offset, $name, $chunk_size );
			} else {
				$classes[] = new Sync_Posts( $offset, $name, $chunk_size );
			}
		}

		return $classes;
	}
}
