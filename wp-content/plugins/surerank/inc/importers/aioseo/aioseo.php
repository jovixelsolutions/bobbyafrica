<?php
/**
 * AIOSEO Importer Class
 *
 * Handles importing data from All in One SEO plugin.
 *
 * @package SureRank\Inc\Importers
 * @since   1.7.0
 */

namespace SureRank\Inc\Importers\Aioseo;

use Exception;
use SureRank\Inc\API\Onboarding;
use SureRank\Inc\Functions\Settings;
use SureRank\Inc\Importers\BaseImporter;
use SureRank\Inc\Importers\ImporterUtils;
use SureRank\Inc\Traits\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements AIOSEO to SureRank migration.
 *
 * @since 1.7.0
 */
class Aioseo extends BaseImporter {

	use Logger;

	/**
	 * AIOSEO global robots settings.
	 *
	 * @since 1.7.0
	 *
	 * @var array<string, string>
	 */
	private array $aioseo_global_robots = [];

	/**
	 * AIOSEO options from database.
	 *
	 * @since 1.7.0
	 *
	 * @var array<string, mixed>
	 */
	private array $aioseo_options = [];

	/**
	 * Get the source plugin name.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public function get_plugin_name(): string {
		return Constants::PLUGIN_NAME;
	}

	/**
	 * Get the source plugin file.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	public function get_plugin_file(): string {
		if ( defined( 'AIOSEO_FILE' ) ) {
			return plugin_basename( AIOSEO_FILE );
		}
		return Constants::PLUGIN_FILE;
	}

	/**
	 * Check if AIOSEO plugin is active.
	 *
	 * @since 1.7.0
	 *
	 * @return bool
	 */
	public function is_plugin_active(): bool {
		return defined( 'AIOSEO_VERSION' ) || function_exists( 'aioseo' );
	}

	/**
	 * Detect whether the source plugin has data for the given post.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function detect_post( int $post_id ): array {
		$tables = Constants::tables_exist();

		if ( $tables['posts'] ) {
			global $wpdb;
			$exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->prefix}aioseo_posts WHERE post_id = %d",
					$post_id
				)
			);

			if ( $exists ) {
				return ImporterUtils::build_response(
					sprintf(
						/* translators: %d: post ID */
						__( 'AIOSEO data detected for post %d.', 'surerank' ),
						$post_id
					),
					true
				);
			}
		}

		return parent::detect_post( $post_id );
	}

	/**
	 * Detect whether the source plugin has data for the given term.
	 *
	 * @since 1.7.0
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function detect_term( int $term_id ): array {
		$term = \get_term( $term_id );

		if ( ! $term || \is_wp_error( $term ) ) {
			return ImporterUtils::build_response(
				sprintf(
					/* translators: %d: term ID */
					__( 'Invalid term ID %d.', 'surerank' ),
					$term_id
				),
				false,
				[],
				true
			);
		}

		$this->type = $term->taxonomy && in_array( $term->taxonomy, array_keys( $this->taxonomies ), true ) ? $term->taxonomy : '';

		$tables = Constants::tables_exist();

		if ( $tables['terms'] ) {
			global $wpdb;
			$exists = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT term_id FROM {$wpdb->prefix}aioseo_terms WHERE term_id = %d",
					$term_id
				)
			);

			if ( $exists ) {
				return ImporterUtils::build_response(
					sprintf(
						/* translators: %d: term ID */
						__( 'AIOSEO data detected for term %d.', 'surerank' ),
						$term_id
					),
					true
				);
			}
		}

		$meta          = get_term_meta( $term_id );
		$excluded_keys = $this->get_excluded_meta_keys();

		if ( $this->has_source_meta( $meta, $excluded_keys ) ) {
			return ImporterUtils::build_response(
				sprintf(
					/* translators: %d: term ID */
					__( 'AIOSEO data detected for term %d.', 'surerank' ),
					$term_id
				),
				true
			);
		}

		ImporterUtils::update_surerank_migrated( $term_id, false );

		return ImporterUtils::build_response(
			sprintf(
				/* translators: %d: term ID */
				__( 'No AIOSEO data found for term %d.', 'surerank' ),
				$term_id
			),
			false,
			[],
			true
		);
	}

	/**
	 * Import meta-robots settings for a post.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_post_meta_robots( int $post_id ): array {
		return $this->import_post_taxo_robots( $post_id, false );
	}

	/**
	 * Import meta-robots settings for a term.
	 *
	 * @since 1.7.0
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_term_meta_robots( int $term_id ): array {
		return $this->import_post_taxo_robots( $term_id, true );
	}

	/**
	 * Import general SEO settings for a post.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_post_general_settings( int $post_id ): array {
		return $this->import_post_taxo_general_settings( $post_id, false );
	}

	/**
	 * Import general SEO settings for a term.
	 *
	 * @since 1.7.0
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_term_general_settings( int $term_id ): array {
		return $this->import_post_taxo_general_settings( $term_id, true );
	}

	/**
	 * Import social metadata for a post.
	 *
	 * @since 1.7.0
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_post_social( int $post_id ): array {
		return $this->import_post_taxo_social( $post_id, false );
	}

	/**
	 * Import social metadata for a term.
	 *
	 * @since 1.7.0
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_term_social( int $term_id ): array {
		return $this->import_post_taxo_social( $term_id, true );
	}

	/**
	 * Import global settings from AIOSEO.
	 *
	 * @since 1.7.0
	 *
	 * @return array{success: bool, message: string}
	 */
	public function import_global_settings(): array {
		$this->aioseo_options = Constants::get_aioseo_options();

		if ( empty( $this->aioseo_options ) ) {
			return ImporterUtils::build_response(
				__( 'No AIOSEO global settings found to import.', 'surerank' ),
				false
			);
		}

		$this->surerank_settings = Settings::get();

		$this->update_global_robot_settings();
		$this->update_robot_settings();
		$this->update_homepage_robots();
		$this->update_description_and_title();
		$this->update_archive_settings();
		$this->update_twitter_card_type();
		$this->update_social_profiles();
		$this->update_sitemap_settings();
		$this->update_site_details();
		$this->update_webmaster_tools();

		try {
			ImporterUtils::update_global_settings( $this->surerank_settings );
			return ImporterUtils::build_response(
				__( 'AIOSEO global settings imported successfully.', 'surerank' ),
				true
			);
		} catch ( Exception $e ) {
			self::log(
				sprintf(
					/* translators: %s: error message */
					__( 'Error importing AIOSEO global settings: %s', 'surerank' ),
					$e->getMessage()
				)
			);
			return ImporterUtils::build_response( $e->getMessage(), false );
		}
	}

	/**
	 * Get count and posts for migration.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string> $post_types Post types to check.
	 * @param int           $batch_size Number of posts per batch.
	 * @param int           $offset     Offset for pagination.
	 * @return array{total_items: int, post_ids: array<int>}
	 */
	public function get_count_and_posts( $post_types, $batch_size, $offset ) {
		global $wpdb;

		$tables = Constants::tables_exist();

		if ( ! $tables['posts'] ) {
			$result             = parent::get_count_and_posts( $post_types, $batch_size, $offset );
			$result['post_ids'] = array_map( 'intval', $result['post_ids'] );
			return $result;
		}

		$placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		$post_types_values = array_values( $post_types );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$total_query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT ap.post_id)
			FROM {$wpdb->prefix}aioseo_posts ap
			INNER JOIN {$wpdb->posts} p ON ap.post_id = p.ID
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'surerank_migration'
			WHERE p.post_type IN ({$placeholders})
			AND p.post_status != 'auto-draft'
			AND pm.meta_id IS NULL",
			...$post_types_values
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

		$total_items = (int) $wpdb->get_var( $total_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$ids_query = $wpdb->prepare(
			"SELECT DISTINCT ap.post_id
			FROM {$wpdb->prefix}aioseo_posts ap
			INNER JOIN {$wpdb->posts} p ON ap.post_id = p.ID
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'surerank_migration'
			WHERE p.post_type IN ({$placeholders})
			AND p.post_status != 'auto-draft'
			AND pm.meta_id IS NULL
			ORDER BY ap.post_id
			LIMIT %d OFFSET %d",
			...array_merge( $post_types_values, [ $batch_size, $offset ] )
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber

		$post_ids = $wpdb->get_col( $ids_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return [
			'total_items' => $total_items,
			'post_ids'    => array_map( 'intval', $post_ids ),
		];
	}

	/**
	 * Get count and terms for migration.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string>               $taxonomies         Taxonomies to check.
	 * @param array<string, \WP_Taxonomy> $taxonomies_objects Taxonomy objects.
	 * @param int                         $batch_size         Number of terms per batch.
	 * @param int                         $offset             Offset for pagination.
	 * @return array{total_items: int, term_ids: array<int>}
	 */
	public function get_count_and_terms( $taxonomies, $taxonomies_objects, $batch_size, $offset ) {
		global $wpdb;

		$tables = Constants::tables_exist();

		if ( ! $tables['terms'] ) {
			$parent_result = parent::get_count_and_terms( $taxonomies, $taxonomies_objects, $batch_size, $offset );
			return [
				'total_items' => (int) ( $parent_result['total_items'] ?? 0 ),
				'term_ids'    => array_map( 'intval', $parent_result['term_ids'] ?? [] ),
			];
		}

		$taxonomies_values = array_values( $taxonomies );
		$placeholders      = implode( ',', array_fill( 0, count( $taxonomies_values ), '%s' ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$total_query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT at.term_id)
			FROM {$wpdb->prefix}aioseo_terms at
			INNER JOIN {$wpdb->term_taxonomy} tt ON at.term_id = tt.term_id
			LEFT JOIN {$wpdb->termmeta} tm ON at.term_id = tm.term_id AND tm.meta_key = 'surerank_migration'
			WHERE tt.taxonomy IN ({$placeholders})
			AND tm.meta_id IS NULL",
			...$taxonomies_values
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

		$total_items = (int) $wpdb->get_var( $total_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		$ids_query = $wpdb->prepare(
			"SELECT DISTINCT at.term_id
			FROM {$wpdb->prefix}aioseo_terms at
			INNER JOIN {$wpdb->term_taxonomy} tt ON at.term_id = tt.term_id
			LEFT JOIN {$wpdb->termmeta} tm ON at.term_id = tm.term_id AND tm.meta_key = 'surerank_migration'
			WHERE tt.taxonomy IN ({$placeholders})
			AND tm.meta_id IS NULL
			ORDER BY at.term_id
			LIMIT %d OFFSET %d",
			...array_merge( $taxonomies_values, [ $batch_size, $offset ] )
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber

		$term_ids = $wpdb->get_col( $ids_query ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared

		return [
			'total_items' => $total_items,
			'term_ids'    => array_map( 'intval', $term_ids ),
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.7.0
	 */
	protected function get_not_allowed_types(): array {
		return Constants::NOT_ALLOWED_TYPES;
	}

	/**
	 * Get the source meta data for a post or term.
	 *
	 * @since 1.7.0
	 *
	 * @param int    $id          The ID of the post or term.
	 * @param bool   $is_taxonomy Whether it is a taxonomy.
	 * @param string $type        The type of post or term.
	 * @return array<string, mixed>
	 */
	protected function get_source_meta_data( int $id, bool $is_taxonomy, string $type = '' ): array {
		return Constants::aioseo_meta_data( $id, $is_taxonomy, $type );
	}

	/**
	 * Get the meta key prefix for the importer.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	protected function get_meta_key_prefix(): string {
		return Constants::META_KEY_PREFIX;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.7.0
	 */
	protected function get_excluded_meta_keys(): array {
		return Constants::EXCLUDED_META_KEYS;
	}

	/**
	 * Import meta-robots settings for a post or term.
	 *
	 * @since 1.7.0
	 *
	 * @param int  $id          Post or Term ID.
	 * @param bool $is_taxonomy Whether the ID is for a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_post_taxo_robots( int $id, bool $is_taxonomy = false ): array {
		try {
			if ( ! empty( $this->source_meta['robots_default'] ) ) {
				return ImporterUtils::build_response(
					sprintf(
						/* translators: 1: type (post/term), 2: ID */
						__( 'Using default robots for %1$s %2$d.', 'surerank' ),
						$is_taxonomy ? 'term' : 'post',
						$id
					),
					true
				);
			}

			$robot_data = Constants::get_mapped_robots( $this->source_meta );

			foreach ( $robot_data as $key => $value ) {
				if ( isset( Constants::ROBOTS_MAPPING[ $key ] ) ) {
					$this->default_surerank_meta[ Constants::ROBOTS_MAPPING[ $key ] ] = $value;
				}
			}

			return ImporterUtils::build_response(
				sprintf(
					/* translators: 1: type (post/term), 2: ID */
					__( 'Meta-robots imported for %1$s %2$d.', 'surerank' ),
					$is_taxonomy ? 'term' : 'post',
					$id
				),
				true
			);
		} catch ( Exception $e ) {
			self::log(
				sprintf(
					/* translators: 1: ID, 2: type (post/term), 3: error message */
					__( 'Error importing meta-robots for %2$s %1$d: %3$s', 'surerank' ),
					$id,
					$is_taxonomy ? 'term' : 'post',
					$e->getMessage()
				)
			);
			return ImporterUtils::build_response( $e->getMessage(), false );
		}
	}

	/**
	 * Import general SEO settings for a post or term.
	 *
	 * @since 1.7.0
	 *
	 * @param int  $id          Post or Term ID.
	 * @param bool $is_taxonomy Whether the ID is for a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_post_taxo_general_settings( int $id, bool $is_taxonomy = false ): array {
		$page_title_description = Constants::get_page_title_description( $this->type, $is_taxonomy );
		$separator              = $this->source_meta['separator'] ?? '-';

		$imported = false;

		if ( ! empty( $this->source_meta['title'] ) ) {
			$this->default_surerank_meta['page_title'] = Constants::replace_placeholders(
				$this->source_meta['title'],
				$separator
			);
			$imported                                  = true;
		} elseif ( ! empty( $page_title_description['page_title'] ) ) {
			$this->default_surerank_meta['page_title'] = Constants::replace_placeholders(
				$page_title_description['page_title'],
				$separator
			);
			$imported                                  = true;
		}

		if ( ! empty( $this->source_meta['description'] ) ) {
			$this->default_surerank_meta['page_description'] = Constants::replace_placeholders(
				$this->source_meta['description'],
				$separator
			);
			$imported                                        = true;
		} elseif ( ! empty( $page_title_description['page_description'] ) ) {
			$this->default_surerank_meta['page_description'] = Constants::replace_placeholders(
				$page_title_description['page_description'],
				$separator
			);
			$imported                                        = true;
		}

		if ( ! empty( $this->source_meta['canonical_url'] ) ) {
			$this->default_surerank_meta['canonical_url'] = esc_url_raw( $this->source_meta['canonical_url'] );
			$imported                                     = true;
		}

		$message = $imported
			/* translators: 1: type (post/term), 2: ID */
			? __( 'General settings imported for %1$s %2$d.', 'surerank' )
			/* translators: 1: type (post/term), 2: ID */
			: __( 'No general settings to import for %1$s %2$d.', 'surerank' );

		return ImporterUtils::build_response(
			sprintf(
				$message,
				$is_taxonomy ? 'term' : 'post',
				$id
			),
			$imported
		);
	}

	/**
	 * Import social metadata for a post or term.
	 *
	 * @since 1.7.0
	 *
	 * @param int  $id          Post or Term ID.
	 * @param bool $is_taxonomy Whether the ID is for a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_post_taxo_social( int $id, bool $is_taxonomy = false ): array {
		$twitter_use_og = ! empty( $this->source_meta['twitter_use_og'] );
		$this->default_surerank_meta['twitter_same_as_facebook'] = $twitter_use_og;

		$imported       = false;
		$separator      = $this->source_meta['separator'] ?? '-';
		$social_mapping = Constants::get_social_mapping();

		foreach ( $social_mapping as $aioseo_key => $surerank_data ) {
			$surerank_key = $surerank_data[1];

			if ( ! empty( $this->source_meta[ $aioseo_key ] ) ) {
				$value = $this->source_meta[ $aioseo_key ];

				if ( strpos( $surerank_key, 'image' ) === false ) {
					$value = Constants::replace_placeholders( $value, $separator );
				}

				$this->default_surerank_meta[ $surerank_key ] = $value;
				$imported                                     = true;
			}
		}

		$message = $imported
			/* translators: 1: type (post/term), 2: ID */
			? __( 'Social metadata imported for %1$s %2$d.', 'surerank' )
			/* translators: 1: type (post/term), 2: ID */
			: __( 'No social metadata to import for %1$s %2$d.', 'surerank' );

		return ImporterUtils::build_response(
			sprintf(
				$message,
				$is_taxonomy ? 'term' : 'post',
				$id
			),
			$imported
		);
	}

	/**
	 * Update global robot settings.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_global_robot_settings(): void {
		$global_robots = $this->aioseo_options['searchAppearance']['advanced']['globalRobotsMeta'] ?? [];

		$this->aioseo_global_robots = Constants::GLOBAL_ROBOTS;

		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $robot ) {
			if ( isset( $global_robots[ $robot ] ) ) {
				$this->aioseo_global_robots[ $robot ] = ! empty( $global_robots[ $robot ] ) ? 'yes' : 'no';
			}
		}
	}

	/**
	 * Update robot settings for post types and taxonomies.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_robot_settings(): void {
		// Get dynamic options - AIOSEO stores post type/taxonomy settings in aioseo_options_dynamic.
		$dynamic_options   = Constants::get_aioseo_dynamic_options();
		$search_appearance = $dynamic_options['searchAppearance'] ?? [];

		foreach ( $this->post_types as $post_type ) {
			$pt_settings = $search_appearance['postTypes'][ $post_type ]['advanced']['robotsMeta'] ?? [];
			$this->process_robot_settings_for_type( $pt_settings, $post_type );
		}

		foreach ( $this->taxonomies as $taxonomy => $object ) {
			$tax_settings = $search_appearance['taxonomies'][ $taxonomy ]['advanced']['robotsMeta'] ?? [];
			$this->process_robot_settings_for_type( $tax_settings, $taxonomy );
		}

		$this->migrate_redirect_attachment_setting( $search_appearance );
	}

	/**
	 * Migrate redirect attachment pages setting.
	 *
	 * AIOSEO stores this at searchAppearance.postTypes.attachment.redirectAttachmentUrls
	 * Values: 'attachment' (redirect to parent), 'attachment_url' (redirect to file), 'disabled' (no redirect)
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $search_appearance AIOSEO search appearance settings.
	 * @return void
	 */
	private function migrate_redirect_attachment_setting( array $search_appearance ): void {
		$attachment_settings = $search_appearance['postTypes']['attachment'] ?? [];
		$redirect_setting    = $attachment_settings['redirectAttachmentUrls'] ?? '';

		// SureRank: true = redirect to parent, false = no redirect.
		if ( ! empty( $redirect_setting ) ) {
			$this->surerank_settings['redirect_attachment_pages_to_post_parent'] = 'disabled' !== $redirect_setting;
		}
	}

	/**
	 * Process robot settings for a specific type.
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $robot_settings Robot settings from AIOSEO.
	 * @param string               $type           Post type or taxonomy name.
	 * @return void
	 */
	private function process_robot_settings_for_type( array $robot_settings, string $type ): void {
		if ( ! empty( $robot_settings['default'] ) ) {
			$robot_rules = $this->aioseo_global_robots;
		} else {
			$robot_rules = Constants::GLOBAL_ROBOTS;
			foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $robot ) {
				if ( isset( $robot_settings[ $robot ] ) ) {
					$robot_rules[ $robot ] = ! empty( $robot_settings[ $robot ] ) ? 'yes' : 'no';
				}
			}
		}

		$robot_mapping = [
			'noindex'   => 'no_index',
			'nofollow'  => 'no_follow',
			'noarchive' => 'no_archive',
		];

		foreach ( $robot_mapping as $aioseo_key => $surerank_key ) {
			if ( ! isset( $this->surerank_settings[ $surerank_key ] ) || ! is_array( $this->surerank_settings[ $surerank_key ] ) ) {
				$this->surerank_settings[ $surerank_key ] = [];
			}

			$is_present = in_array( $type, $this->surerank_settings[ $surerank_key ], true );

			if ( 'yes' === $robot_rules[ $aioseo_key ] && ! $is_present ) {
				$this->surerank_settings[ $surerank_key ][] = $type;
			} elseif ( 'no' === $robot_rules[ $aioseo_key ] && $is_present ) {
				$this->surerank_settings[ $surerank_key ] = array_values(
					array_diff( $this->surerank_settings[ $surerank_key ], [ $type ] )
				);
			}
		}
	}

	/**
	 * Update homepage robots.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_homepage_robots(): void {
		$home_robots = $this->aioseo_options['searchAppearance']['advanced']['globalRobotsMeta'] ?? [];

		if ( ! isset( $this->surerank_settings['home_page_robots']['general'] ) ) {
			$this->surerank_settings['home_page_robots']['general'] = [];
		}

		foreach ( [ 'noindex', 'nofollow', 'noarchive' ] as $robot ) {
			$value    = ! empty( $home_robots[ $robot ] ) ? 'yes' : 'no';
			$in_array = in_array( $robot, $this->surerank_settings['home_page_robots']['general'], true );

			if ( 'yes' === $value && ! $in_array ) {
				$this->surerank_settings['home_page_robots']['general'][] = $robot;
			} elseif ( 'no' === $value && $in_array ) {
				$this->surerank_settings['home_page_robots']['general'] = array_values(
					array_diff( $this->surerank_settings['home_page_robots']['general'], [ $robot ] )
				);
			}
		}
	}

	/**
	 * Update description and title settings.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_description_and_title(): void {
		$global    = $this->aioseo_options['searchAppearance']['global'] ?? [];
		$separator = $global['separator'] ?? '-';

		if ( ! empty( $global['siteTitle'] ) ) {
			$this->surerank_settings['home_page_title'] = Constants::replace_placeholders(
				$global['siteTitle'],
				$separator
			);
		}

		if ( ! empty( $global['metaDescription'] ) ) {
			$this->surerank_settings['home_page_description'] = Constants::replace_placeholders(
				$global['metaDescription'],
				$separator
			);
		}

		$fb_general = $this->aioseo_options['social']['facebook']['general'] ?? [];

		if ( ! empty( $fb_general['defaultImagePosts'] ) ) {
			$this->surerank_settings['fallback_image'] = $fb_general['defaultImagePosts'];
		}

		$fb_home = $this->aioseo_options['social']['facebook']['homePage'] ?? [];

		if ( ! empty( $fb_home['title'] ) ) {
			$this->surerank_settings['home_page_facebook_title'] = Constants::replace_placeholders(
				$fb_home['title'],
				$separator
			);
		}

		if ( ! empty( $fb_home['description'] ) ) {
			$this->surerank_settings['home_page_facebook_description'] = Constants::replace_placeholders(
				$fb_home['description'],
				$separator
			);
		}

		if ( ! empty( $fb_home['image'] ) ) {
			$this->surerank_settings['home_page_facebook_image_url'] = $fb_home['image'];
		}

		$twitter_home    = $this->aioseo_options['social']['twitter']['homePage'] ?? [];
		$twitter_general = $this->aioseo_options['social']['twitter']['general'] ?? [];

		// When "Use Data from Facebook Tab" is enabled (useOgData), Twitter homePage
		// fields are empty and AIOSEO inherits from Facebook. Fall back accordingly.
		$use_og_data = ! empty( $twitter_general['useOgData'] );

		$twitter_title = ! empty( $twitter_home['title'] ) ? $twitter_home['title'] : ( $use_og_data ? ( $fb_home['title'] ?? '' ) : '' );
		$twitter_desc  = ! empty( $twitter_home['description'] ) ? $twitter_home['description'] : ( $use_og_data ? ( $fb_home['description'] ?? '' ) : '' );
		$twitter_image = ! empty( $twitter_home['image'] ) ? $twitter_home['image'] : ( $use_og_data ? ( $fb_home['image'] ?? '' ) : '' );

		if ( ! empty( $twitter_title ) ) {
			$this->surerank_settings['home_page_twitter_title'] = Constants::replace_placeholders(
				$twitter_title,
				$separator
			);
		}

		if ( ! empty( $twitter_desc ) ) {
			$this->surerank_settings['home_page_twitter_description'] = Constants::replace_placeholders(
				$twitter_desc,
				$separator
			);
		}

		if ( ! empty( $twitter_image ) ) {
			$this->surerank_settings['home_page_twitter_image_url'] = $twitter_image;
		}

		// Get dynamic options (contains post type/taxonomy templates).
		// AIOSEO stores these in a separate option called 'aioseo_options_dynamic'.
		$dynamic_options   = Constants::get_aioseo_dynamic_options();
		$search_appearance = $dynamic_options['searchAppearance'] ?? [];
		$post_types        = $search_appearance['postTypes'] ?? [];

		foreach ( $post_types as $post_type => $settings ) {
			if ( ! empty( $settings['title'] ) ) {
				$this->surerank_settings[ "{$post_type}_page_title" ] = Constants::replace_placeholders(
					$settings['title'],
					$separator
				);
			}
			if ( ! empty( $settings['metaDescription'] ) ) {
				$this->surerank_settings[ "{$post_type}_page_description" ] = Constants::replace_placeholders(
					$settings['metaDescription'],
					$separator
				);
			}
		}

		$taxonomies = $search_appearance['taxonomies'] ?? [];

		foreach ( $taxonomies as $taxonomy => $settings ) {
			if ( ! empty( $settings['title'] ) ) {
				$this->surerank_settings[ "{$taxonomy}_taxonomy_title" ] = Constants::replace_placeholders(
					$settings['title'],
					$separator
				);
			}
			if ( ! empty( $settings['metaDescription'] ) ) {
				$this->surerank_settings[ "{$taxonomy}_taxonomy_description" ] = Constants::replace_placeholders(
					$settings['metaDescription'],
					$separator
				);
			}
		}

		$archives = $this->aioseo_options['searchAppearance']['archives'] ?? [];

		if ( ! empty( $archives['author']['title'] ) ) {
			$this->surerank_settings['author_archive_title'] = Constants::replace_placeholders(
				$archives['author']['title'],
				$separator
			);
		}
		if ( ! empty( $archives['author']['metaDescription'] ) ) {
			$this->surerank_settings['author_archive_description'] = Constants::replace_placeholders(
				$archives['author']['metaDescription'],
				$separator
			);
		}
		if ( ! empty( $archives['date']['title'] ) ) {
			$this->surerank_settings['date_archive_title'] = Constants::replace_placeholders(
				$archives['date']['title'],
				$separator
			);
		}
		if ( ! empty( $archives['date']['metaDescription'] ) ) {
			$this->surerank_settings['date_archive_description'] = Constants::replace_placeholders(
				$archives['date']['metaDescription'],
				$separator
			);
		}

		// Import CPT archive title/description from dynamic options.
		$cpt_archives = $search_appearance['archives'] ?? [];

		foreach ( $cpt_archives as $post_type => $settings ) {
			if ( ! empty( $settings['title'] ) ) {
				$this->surerank_settings[ "cpt_{$post_type}_archive_title" ] = Constants::replace_placeholders(
					$settings['title'],
					$separator
				);
			}
			if ( ! empty( $settings['metaDescription'] ) ) {
				$this->surerank_settings[ "cpt_{$post_type}_archive_description" ] = Constants::replace_placeholders(
					$settings['metaDescription'],
					$separator
				);
			}
		}
	}

	/**
	 * Update archive settings.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_archive_settings(): void {
		$archives = $this->aioseo_options['searchAppearance']['archives'] ?? [];

		if ( isset( $archives['author']['show'] ) ) {
			$this->surerank_settings['author_archive'] = $archives['author']['show'] ? 1 : 0;
		}

		if ( isset( $archives['date']['show'] ) ) {
			$this->surerank_settings['date_archive'] = $archives['date']['show'] ? 1 : 0;
		}
	}

	/**
	 * Update Twitter card type.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_twitter_card_type(): void {
		$twitter_settings = $this->aioseo_options['social']['twitter']['general'] ?? [];

		if ( ! empty( $twitter_settings['defaultCardType'] ) ) {
			$card_type                                    = $twitter_settings['defaultCardType'];
			$this->surerank_settings['twitter_card_type'] = 'summary' === $card_type ? 'summary' : 'summary_large_image';
		}
	}

	/**
	 * Update social profiles.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_social_profiles(): void {
		$profiles = $this->aioseo_options['social']['profiles'] ?? [];
		$urls     = $profiles['urls'] ?? [];

		// When "Use the same username" is enabled, AIOSEO stores only the username
		// and constructs full URLs dynamically. Resolve them here before mapping.
		$same_username = $profiles['sameUsername'] ?? [];

		if ( ! empty( $same_username['enable'] ) && ! empty( $same_username['username'] ) ) {
			$username  = $same_username['username'];
			$included  = $same_username['included'] ?? [];
			$base_urls = [
				'facebookPageUrl' => 'https://facebook.com/',
				'twitterUrl'      => 'https://x.com/',
				'instagramUrl'    => 'https://instagram.com/',
				'tiktokUrl'       => 'https://tiktok.com/@',
				'pinterestUrl'    => 'https://pinterest.com/',
				'youtubeUrl'      => 'https://youtube.com/',
				'linkedinUrl'     => 'https://linkedin.com/in/',
				'yelpPageUrl'     => 'https://yelp.com/biz/',
				'blueskyUrl'      => 'https://bsky.app/profile/',
			];

			foreach ( $base_urls as $platform_key => $base_url ) {
				if ( in_array( $platform_key, $included, true ) ) {
					$urls[ $platform_key ] = $base_url . $username;
				}
			}
		}

		if ( ! empty( $urls['facebookPageUrl'] ) ) {
			$this->surerank_settings['facebook_page_url'] = $urls['facebookPageUrl'];
		}

		if ( ! empty( $urls['twitterUrl'] ) ) {
			$this->surerank_settings['twitter_profile_username'] = $urls['twitterUrl'];
		}

		// Direct mapping for AIOSEO URL keys → SureRank social_profiles keys.
		$social_mapping = [
			'instagramUrl' => 'instagram',
			'pinterestUrl' => 'pinterest',
			'youtubeUrl'   => 'youtube',
			'linkedinUrl'  => 'linkedin',
			'tiktokUrl'    => 'tiktok',
			'yelpPageUrl'  => 'yelp',
			'blueskyUrl'   => 'bluesky',
		];

		if ( ! isset( $this->surerank_settings['social_profiles'] ) ) {
			$this->surerank_settings['social_profiles'] = [];
		}

		foreach ( $social_mapping as $aioseo_key => $surerank_key ) {
			if ( ! empty( $urls[ $aioseo_key ] ) ) {
				$this->surerank_settings['social_profiles'][ $surerank_key ] = $urls[ $aioseo_key ];
			}
		}

		// Import additionalUrls via domain-based matching for platforms like
		// WhatsApp (wa.me), Telegram (t.me), etc.
		if ( ! empty( $profiles['additionalUrls'] ) ) {
			$additional = array_filter( array_map( 'trim', explode( "\n", $profiles['additionalUrls'] ) ) );

			// Ensure special platform keys exist for domain-based matching.
			// get_mapped_social_profiles() only iterates over existing keys,
			// so platforms like whatsapp/telegram must be pre-initialized.
			$special_platforms = [ 'whatsapp', 'telegram', 'bluesky' ];
			foreach ( $special_platforms as $platform ) {
				if ( ! isset( $this->surerank_settings['social_profiles'][ $platform ] ) ) {
					$this->surerank_settings['social_profiles'][ $platform ] = '';
				}
			}

			$this->surerank_settings['social_profiles'] = ImporterUtils::get_mapped_social_profiles(
				$additional,
				$this->surerank_settings['social_profiles']
			);
		}
	}

	/**
	 * Update sitemap settings.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_sitemap_settings(): void {
		$sitemap = $this->aioseo_options['sitemap']['general'] ?? [];

		if ( isset( $sitemap['enable'] ) ) {
			$this->surerank_settings['enable_xml_sitemap'] = $sitemap['enable'] ? 1 : 0;
		}

		// Migrate image sitemap setting (AIOSEO uses inverted "excludeImages").
		$advanced = $sitemap['advancedSettings'] ?? [];
		if ( isset( $advanced['excludeImages'] ) ) {
			$this->surerank_settings['enable_xml_image_sitemap'] = empty( $advanced['excludeImages'] );
		}

		// Migrate sitemap post types exclusions.
		$this->migrate_sitemap_post_types( $sitemap );

		// Migrate sitemap taxonomies exclusions.
		$this->migrate_sitemap_taxonomies( $sitemap );
	}

	/**
	 * Migrate sitemap post types exclusions.
	 *
	 * AIOSEO uses inclusion model (select what to include).
	 * SureRank uses exclusion model (include all, select what to exclude).
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $sitemap AIOSEO sitemap settings.
	 * @return void
	 */
	private function migrate_sitemap_post_types( array $sitemap ): void {
		$post_types_settings = $sitemap['postTypes'] ?? [];

		if ( ! empty( $post_types_settings['all'] ) ) {
			$this->surerank_settings['sitemap_excluded_post_types'] = [];
			return;
		}

		$included = $post_types_settings['included'] ?? [];

		if ( empty( $included ) || ! is_array( $included ) ) {
			return;
		}

		$all_public = get_post_types( [ 'public' => true ], 'names' );

		$excluded = array_values( array_diff( $all_public, $included ) );

		$this->surerank_settings['sitemap_excluded_post_types'] = $excluded;
	}

	/**
	 * Migrate sitemap taxonomies exclusions.
	 *
	 * AIOSEO uses inclusion model (select what to include).
	 * SureRank uses exclusion model (include all, select what to exclude).
	 *
	 * @since 1.7.0
	 *
	 * @param array<string, mixed> $sitemap AIOSEO sitemap settings.
	 * @return void
	 */
	private function migrate_sitemap_taxonomies( array $sitemap ): void {
		$taxonomies_settings = $sitemap['taxonomies'] ?? [];

		// If all taxonomies are included, exclude nothing.
		if ( ! empty( $taxonomies_settings['all'] ) ) {
			$this->surerank_settings['sitemap_excluded_taxonomies'] = [];
			return;
		}

		$included = $taxonomies_settings['included'] ?? [];

		if ( empty( $included ) || ! is_array( $included ) ) {
			return;
		}

		$all_public = get_taxonomies( [ 'public' => true ], 'names' );

		$excluded = array_values( array_diff( $all_public, $included ) );

		$this->surerank_settings['sitemap_excluded_taxonomies'] = $excluded;
	}

	/**
	 * Update site details (organization/person info).
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_site_details(): void {
		$schema = $this->aioseo_options['searchAppearance']['global']['schema'] ?? [];

		$site_represents = $schema['siteRepresents'] ?? 'organization';

		$website_name = $schema['websiteName'] ?? '';

		// AIOSEO uses '#site_title' as a placeholder meaning "use the WordPress site title".
		// Resolve it to the current blogname so onboarding/schema data keeps a valid website name instead of persisting the literal placeholder or an empty string.
		if ( '#site_title' === trim( $website_name ) ) {
			$website_name = (string) get_option( 'blogname', '' );
		}

		$site_data = [
			'website_name'      => $website_name,
			'organization_type' => 'organization' === $site_represents ? 'Organization' : 'Person',
			'website_logo'      => $schema['organizationLogo'] ?? $schema['personLogo'] ?? '',
			'website_type'      => $site_represents,
		];

		Onboarding::update_common_onboarding_data( $site_data );
	}

	/**
	 * Update webmaster tools verification codes.
	 *
	 * @since 1.7.0
	 *
	 * @return void
	 */
	private function update_webmaster_tools(): void {
		$webmaster = $this->aioseo_options['webmasterTools'] ?? [];

		$mapping = [
			'google'    => 'google_verify',
			'bing'      => 'bing_verify',
			'baidu'     => 'baidu_verify',
			'yandex'    => 'yandex_verify',
			'pinterest' => 'pinterest_verify',
		];

		foreach ( $mapping as $aioseo_key => $surerank_key ) {
			if ( ! empty( $webmaster[ $aioseo_key ] ) ) {
				$this->surerank_settings[ $surerank_key ] = $webmaster[ $aioseo_key ];
			}
		}
	}
}
