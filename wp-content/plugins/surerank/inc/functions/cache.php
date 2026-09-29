<?php
/**
 * Cache Functions
 *
 * @package surerank
 * @since 1.2.0
 */

namespace SureRank\Inc\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Traits\Get_Instance;

/**
 * Cache class for file storage operations
 *
 * @since 1.2.0
 */
class Cache {

	use Get_Instance;

	/**
	 * Cache directory path
	 *
	 * @var string
	 */
	private static $cache_dir = '';

	/**
	 * Initialize cache directory
	 *
	 * @since 1.2.0
	 * @return void
	 */
	public static function init() {
		self::$cache_dir = wp_upload_dir()['basedir'] . '/surerank/';

		// Create cache directory if it doesn't exist.
		if ( ! file_exists( self::$cache_dir ) ) {
			$result = wp_mkdir_p( self::$cache_dir );
		}
	}

	/**
	 * Store data to file
	 *
	 * @param string $filename The filename to store data.
	 * @param string $data The data to store.
	 * @since 1.2.0
	 * @return bool True on success, false on failure
	 */
	public static function store_file( string $filename, string $data ): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		// Sanitize filename and prevent directory traversal attacks.
		$filename = self::sanitize_filename( $filename );

		$filepath = self::$cache_dir . $filename;

		// Create directory if it doesn't exist.
		$dir = dirname( $filepath );
		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		// Use WordPress filesystem API. for better security.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem->put_contents( $filepath, $data, FS_CHMOD_FILE );
	}

	/**
	 * Get data from file
	 *
	 * @param string $filename The filename to retrieve data from.
	 * @since 1.2.0
	 * @return string|false File contents on success, false on failure
	 */
	public static function get_file( string $filename ) {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		// Sanitize filename to prevent directory traversal attacks.
		$filename = self::sanitize_filename( $filename );

		$filepath = self::$cache_dir . $filename;

		if ( ! file_exists( $filepath ) ) {
			return false;
		}

		// Use WordPress filesystem API.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem->get_contents( $filepath );
	}

	/**
	 * Delete cache file
	 *
	 * @param string $filename The filename to delete.
	 * @since 1.2.0
	 * @return bool True on success, false on failure
	 */
	public static function delete_file( string $filename ): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		// Sanitize filename and prevent directory traversal attacks.
		$filename = self::sanitize_filename( $filename );

		$filepath = self::$cache_dir . $filename;

		if ( ! file_exists( $filepath ) ) {
			return false;
		}

		// Use WordPress filesystem API.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem->delete( $filepath );
	}

	/**
	 * Clear all cache files
	 *
	 * @since 1.2.0
	 * @return bool True on success, false on failure
	 */
	public static function clear_all(): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		if ( ! file_exists( self::$cache_dir ) ) {
			return true;
		}

		// Use WordPress filesystem API.
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		return $wp_filesystem->delete( self::$cache_dir, true );
	}

	/**
	 * Get cache file path
	 *
	 * @param string $filename The filename.
	 * @since 1.2.0
	 * @return string Full file path
	 */
	public static function get_file_path( string $filename ): string {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		// Sanitize filename to prevent directory traversal attacks.
		$filename = self::sanitize_filename( $filename );

		return self::$cache_dir . $filename;
	}

	/**
	 * Begin an atomic rebuild of a prefix directory.
	 *
	 * Rotates the current live directory ({prefix}/) to a backup slot
	 * ({prefix}.old/) and creates an empty live directory for the new
	 * rebuild to write into. The backup is used for stale-while-revalidate
	 * reads during rebuild and is removed by commit_atomic_rebuild() on
	 * success or restored by abort_atomic_rebuild() on failure.
	 *
	 * This call is the only operation that moves directories; the rebuild
	 * itself continues to use the normal store_file() / get_file() API
	 * against {prefix}/ with no changes.
	 *
	 * @param string $prefix Top-level cache prefix (e.g. "sitemap").
	 * @since 1.7.2
	 * @return bool True on success, false if rotation failed.
	 */
	public static function begin_atomic_rebuild( string $prefix ): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		$prefix = self::sanitize_prefix( $prefix );
		if ( '' === $prefix ) {
			return false;
		}

		// Acquire a lock to prevent concurrent rebuilds from racing and
		// destroying both the live and backup directories. The lock is
		// released by commit_atomic_rebuild() or abort_atomic_rebuild().
		$lock_key = 'surerank_rebuild_lock_' . $prefix;
		if ( false !== get_transient( $lock_key ) ) {
			return false;
		}
		set_transient( $lock_key, time(), 10 * MINUTE_IN_SECONDS );

		$current = self::$cache_dir . $prefix;
		$backup  = self::$cache_dir . $prefix . '.old';

		// An existing backup can mean two different things:
		//
		// (a) Live has content (sitemap_index.json present): the previous rebuild
		// succeeded and committed. The backup is leftover from that cycle and
		// is safe to delete before we rotate the good live cache into the new
		// backup slot.
		//
		// (b) Live is empty or missing: the previous async dispatch likely failed
		// silently (loopback blocked, PHP timeout, etc.) and never populated
		// the live dir. The backup is still the last known-good cache. Preserve
		// it for stale-while-revalidate; just clean up the empty live dir and
		// create a fresh one without touching the backup.
		if ( file_exists( $backup ) ) {
			$live_index = $current . '/sitemap_index.json';
			if ( file_exists( $live_index ) ) {
				// (a) Previous rebuild completed — remove the now-superseded backup.
				if ( ! self::recursive_remove( $backup ) ) {
					delete_transient( $lock_key );
					return false;
				}
			} else {
				// (b) Previous dispatch failed — live is empty. Keep the backup as
				// the stale-while-revalidate source; only clean and recreate live.
				if ( file_exists( $current ) ) {
					self::recursive_remove( $current );
				}
				wp_mkdir_p( $current );
				return true;
			}
		}

		// Rotate current to backup if it exists. First-ever rebuild has
		// no current directory yet, which is fine. rename() is used here
		// (not WP_Filesystem::move) because atomic swap semantics matter:
		// move() falls back to copy+delete on non-direct transports, which
		// is not atomic. Both $current and $backup are inside
		// wp_upload_dir()['basedir'], so this is within the allowlist for
		// direct filesystem operations.
		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_rename,WordPress.PHP.NoSilencedErrors.Discouraged -- native rename() required for atomic swap; both paths are inside uploads.
		if ( file_exists( $current ) && ! @rename( $current, $backup ) ) {
			delete_transient( $lock_key );
			return false;
		}

		// Create an empty live directory for the rebuild to populate.
		wp_mkdir_p( $current );

		return true;
	}

	/**
	 * Commit a successful atomic rebuild.
	 *
	 * Removes the backup. The new cache built in {prefix}/ during the
	 * rebuild is already in place and becomes the authoritative cache.
	 *
	 * @param string $prefix Top-level cache prefix (e.g. "sitemap").
	 * @since 1.7.2
	 * @return bool True if the backup was removed (or never existed).
	 */
	public static function commit_atomic_rebuild( string $prefix ): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		$prefix = self::sanitize_prefix( $prefix );
		if ( '' === $prefix ) {
			return false;
		}

		delete_transient( 'surerank_rebuild_lock_' . $prefix );

		$backup = self::$cache_dir . $prefix . '.old';

		if ( ! file_exists( $backup ) ) {
			return true;
		}

		return self::recursive_remove( $backup );
	}

	/**
	 * Abort a rebuild and roll back to the previous cache.
	 *
	 * Removes the partially-written live directory and restores the
	 * backup to its original position. Used when a rebuild fails
	 * mid-flight and we want the previous known-good cache to serve
	 * subsequent requests.
	 *
	 * @param string $prefix Top-level cache prefix (e.g. "sitemap").
	 * @since 1.7.2
	 * @return bool True if rollback succeeded or no backup existed.
	 */
	public static function abort_atomic_rebuild( string $prefix ): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		$prefix = self::sanitize_prefix( $prefix );
		if ( '' === $prefix ) {
			return false;
		}

		delete_transient( 'surerank_rebuild_lock_' . $prefix );

		$current = self::$cache_dir . $prefix;
		$backup  = self::$cache_dir . $prefix . '.old';

		// If the partial rebuild cannot be cleared (open file handles on
		// Windows, permission flip mid-flight), do not attempt the
		// rename — a half-rolled-back state is worse than a failed abort
		// the caller can retry.
		if ( file_exists( $current ) && ! self::recursive_remove( $current ) ) {
			return false;
		}

		if ( ! file_exists( $backup ) ) {
			return true;
		}

		// phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.file_ops_rename,WordPress.PHP.NoSilencedErrors.Discouraged -- native rename() required for atomic swap; both paths are inside uploads.
		return @rename( $backup, $current );
	}

	/**
	 * Whether a backup from an in-progress or failed rebuild is available.
	 *
	 * Used by the sitemap miss handler to serve stale-while-revalidate
	 * responses instead of a 503 when the live cache is absent or
	 * incomplete.
	 *
	 * @param string $prefix Top-level cache prefix (e.g. "sitemap").
	 * @since 1.7.2
	 * @return bool
	 */
	public static function has_rebuild_backup( string $prefix ): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		$prefix = self::sanitize_prefix( $prefix );
		if ( '' === $prefix ) {
			return false;
		}

		return file_exists( self::$cache_dir . $prefix . '.old' );
	}

	/**
	 * Read a file from the backup directory of an in-progress rebuild.
	 *
	 * Translates a live path like "sitemap/sitemap_index.json" to its
	 * backup counterpart "sitemap.old/sitemap_index.json" and returns
	 * its contents, or false if unavailable.
	 *
	 * @param string $filename The live filename (e.g. "sitemap/sitemap_index.json").
	 * @since 1.7.2
	 * @return string|false
	 */
	public static function read_rebuild_backup( string $filename ): string|false {
		$pos = strpos( $filename, '/' );
		if ( false === $pos ) {
			return false;
		}

		$backup_filename = substr( $filename, 0, $pos ) . '.old' . substr( $filename, $pos );
		return self::get_file( $backup_filename );
	}

	/**
	 * Check if cache file exists
	 *
	 * @param string $filename The filename to check.
	 * @since 1.2.0
	 * @return bool True if file exists, false otherwise
	 */
	public static function file_exists( string $filename ): bool {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		// Sanitize filename to prevent directory traversal attacks.
		$filename = self::sanitize_filename( $filename );

		return file_exists( self::$cache_dir . $filename );
	}

	/**
	 * Get all files from cache directory
	 *
	 * @since 1.2.0
	 * @param string $directory Optional directory name to scan (e.g., 'sitemap', 'metadata').
	 * @return array<string> Array of filenames in the cache directory
	 */
	public static function get_all_files( string $directory = '' ) {
		if ( empty( self::$cache_dir ) ) {
			self::init();
		}

		$target_dir = self::$cache_dir;
		if ( ! empty( $directory ) ) {
			// Sanitize directory name and prevent directory traversal.
			$directory  = self::sanitize_filename( $directory );
			$target_dir = self::$cache_dir . $directory . '/';
		}

		if ( ! file_exists( $target_dir ) ) {
			return [];
		}

		$files = scandir( $target_dir );
		if ( false === $files ) {
			return [];
		}

		$json_files = array_filter(
			$files,
			static function( $file ) {
				return $file !== '.' && $file !== '..' && pathinfo( $file, PATHINFO_EXTENSION ) === 'json';
			}
		);

		return array_values( $json_files );
	}

	/**
	 * Update sitemap index when a new chunk is created
	 *
	 * @param string $type Content type (post, category, etc.).
	 * @param int    $chunk_number The chunk number.
	 * @param int    $url_count Number of URLs in this chunk.
	 * @since 1.2.0
	 * @return void
	 */
	public static function update_sitemap_index( string $type, int $chunk_number, int $url_count ) {

		$sitemap_threshold = apply_filters( 'surerank_sitemap_threshold', 200 );
		$chunk_size        = apply_filters( 'surerank_sitemap_json_chunk_size', 20 );

		$chunks_per_sitemap   = (int) ceil( $sitemap_threshold / $chunk_size );
		$sitemap_index_number = (int) ceil( $chunk_number / $chunks_per_sitemap );

		$sitemap_index_filename = 'sitemap/' . $type . '-sitemap-' . $sitemap_index_number . '.json';
		$sitemap_index_data     = self::get_sitemap_index_data( $sitemap_index_filename, $type, $sitemap_index_number );

		$sitemap_index_data['updated_at'] = current_time( 'c' );

		self::update_unified_sitemap_index( $sitemap_index_filename );
	}

	/**
	 * Sanitize a prefix used by the atomic-rebuild helpers.
	 *
	 * Allows only alphanumerics, underscores, and hyphens. Prevents
	 * callers from passing values that would escape the cache root.
	 *
	 * @param string $prefix The prefix to sanitize.
	 * @since 1.7.2
	 * @return string
	 */
	private static function sanitize_prefix( string $prefix ): string {
		return (string) preg_replace( '/[^A-Za-z0-9_-]/', '', $prefix );
	}

	/**
	 * Recursively remove a directory and its contents without following
	 * symlinks. Symlinked entries are unlinked at the link node itself,
	 * so an attacker who plants a symlink inside sitemap.old/ cannot
	 * use commit/abort to wipe files outside the cache root.
	 *
	 * @param string $dir Absolute path to the directory to remove.
	 * @since 1.7.2
	 * @return bool
	 */
	private static function recursive_remove( string $dir ): bool {
		global $wp_filesystem;
		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		if ( ! $wp_filesystem instanceof \WP_Filesystem_Base ) {
			return false;
		}

		// If the target itself is a symlink, unlink the link node only.
		if ( is_link( $dir ) ) {
			return (bool) $wp_filesystem->delete( $dir, false, 'f' );
		}

		if ( ! $wp_filesystem->is_dir( $dir ) ) {
			return false;
		}

		$list = $wp_filesystem->dirlist( $dir );
		if ( ! is_array( $list ) ) {
			return false;
		}

		foreach ( $list as $name => $info ) {
			$path = rtrim( $dir, '/\\' ) . '/' . $name;

			if ( is_link( $path ) ) {
				$wp_filesystem->delete( $path, false, 'f' );
				continue;
			}

			if ( 'd' === $info['type'] ) {
				self::recursive_remove( $path );
				continue;
			}

			$wp_filesystem->delete( $path, false, 'f' );
		}

		return (bool) $wp_filesystem->delete( $dir, false, 'd' );
	}

	/**
	 * Sanitize filename to prevent directory traversal attacks.
	 *
	 * Normalizes separators and rejects any path containing a `..`
	 * segment. Empty-string result signals callers to treat the path
	 * as missing: `file_exists`/`get_file`/`store_file` all resolve
	 * against the cache directory itself which fails safely (reads
	 * return false, writes to a directory path fail).
	 *
	 * @param string $filename The filename to sanitize.
	 * @since 1.2.0
	 * @return string Sanitized filename, or empty string on traversal.
	 */
	private static function sanitize_filename( string $filename ): string {
		$filename = wp_normalize_path( $filename );
		$filename = ltrim( $filename, '/' );

		// Reject any path containing `..` anywhere. A segment-equality
		// check on `..` alone would miss variants like `....` (two
		// overlapping parent refs) or `...//foo` that still escape the
		// cache root after normalisation. strpos catches all of them.
		if ( false !== strpos( $filename, '..' ) ) {
			return '';
		}

		return $filename;
	}

	/**
	 * Get sitemap index data, create if it doesn't exist
	 *
	 * @param string $filename Sitemap index filename.
	 * @param string $type Content type.
	 * @param int    $index_number Sitemap index number.
	 * @since 1.2.0
	 * @return array<string, mixed> Sitemap index data
	 */
	private static function get_sitemap_index_data( string $filename, string $type, int $index_number ): array {
		$existing_data = self::get_file( $filename );

		if ( $existing_data ) {
			$decoded_data = json_decode( $existing_data, true );
			if ( $decoded_data && is_array( $decoded_data ) ) {
				return $decoded_data;
			}
		}

		$sitemap_threshold = apply_filters( 'surerank_sitemap_threshold', 200 );

		return [
			'type'         => $type,
			'index_number' => $index_number,
		];
	}

	/**
	 * Update unified sitemap index
	 *
	 * @param string $sitemap_filename The sitemap filename that was just created/updated.
	 * @since 1.2.0
	 * @return void
	 */
	private static function update_unified_sitemap_index( string $sitemap_filename ) {
		$unified_index_filename = 'sitemap/sitemap_index.json';
		$unified_index_data     = self::get_unified_sitemap_index_data( $unified_index_filename );

		$xml_filename = str_replace( '.json', '.xml', $sitemap_filename );
		$xml_filename = str_replace( 'sitemap/', '', $xml_filename );
		$sitemap_url  = home_url( $xml_filename );

		$sitemap_exists = false;
		foreach ( $unified_index_data as &$sitemap_entry ) {
			if ( $sitemap_entry['link'] === $sitemap_url ) {
				$sitemap_entry['updated'] = current_time( 'c' );
				$sitemap_exists           = true;
				break;
			}
		}

		if ( ! $sitemap_exists ) {
			$unified_index_data[] = [
				'link'    => $sitemap_url,
				'updated' => current_time( 'c' ),
			];
		}

		usort(
			$unified_index_data,
			static function( $a, $b ) {
				return strnatcmp( $a['link'], $b['link'] );
			}
		);

		$json_data = wp_json_encode( $unified_index_data, JSON_PRETTY_PRINT );
		if ( $json_data ) {
			self::store_file( $unified_index_filename, $json_data );
		}
	}

	/**
	 * Get unified sitemap index data, create if it doesn't exist
	 *
	 * @param string $filename Unified sitemap index filename.
	 * @since 1.2.0
	 * @return array<string, mixed> Unified sitemap index data
	 */
	private static function get_unified_sitemap_index_data( string $filename ) {
		$existing_data = self::get_file( $filename );

		if ( $existing_data ) {
			$decoded_data = json_decode( $existing_data, true );
			if ( $decoded_data && is_array( $decoded_data ) ) {
				return $decoded_data;
			}
		}

		return [];
	}
}
