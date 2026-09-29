<?php
/**
 * Slim SEO Importer Class
 *
 * Handles importing data from Slim SEO plugin.
 *
 * @package SureRank\Inc\Importers
 * @since   1.7.0
 */

namespace SureRank\Inc\Importers\Slimseo;

use Exception;
use SureRank\Inc\Functions\Settings;
use SureRank\Inc\Importers\BaseImporter;
use SureRank\Inc\Importers\ImporterUtils;
use SureRank\Inc\Traits\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements Slim SEO → SureRank migration.
 */
class SlimSeo extends BaseImporter {

	use Logger;

	/**
	 * Get plugin name.
	 *
	 * @return string
	 */
	public function get_plugin_name(): string {
		return Constants::PLUGIN_NAME;
	}

	/**
	 * Get plugin file.
	 *
	 * @return string
	 */
	public function get_plugin_file(): string {
		return Constants::PLUGIN_FILE;
	}

	/**
	 * Check if Slim SEO plugin is active.
	 *
	 * @return bool
	 */
	public function is_plugin_active(): bool {
		return defined( 'SLIM_SEO_VER' );
	}

	/**
	 * Detect Slim SEO data for post.
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function detect_post( int $post_id ): array {
		$meta = Constants::get_slim_seo_meta( $post_id, false );

		if ( ! empty( $meta ) ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: %d: post ID.
					__( 'Slim SEO data detected for post %d.', 'surerank' ),
					$post_id
				),
				true
			);
		}

		ImporterUtils::update_surerank_migrated( $post_id );

		return ImporterUtils::build_response(
			sprintf(
				// translators: %d: post ID.
				__( 'No Slim SEO data found for post %d.', 'surerank' ),
				$post_id
			),
			false,
			[],
			true
		);
	}

	/**
	 * Detect Slim SEO data for term.
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function detect_term( int $term_id ): array {
		$meta = Constants::get_slim_seo_meta( $term_id, true );

		if ( ! empty( $meta ) ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: %d: term ID.
					__( 'Slim SEO data detected for term %d.', 'surerank' ),
					$term_id
				),
				true
			);
		}

		ImporterUtils::update_surerank_migrated( $term_id, false );

		return ImporterUtils::build_response(
			sprintf(
				// translators: %d: term ID.
				__( 'No Slim SEO data found for term %d.', 'surerank' ),
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
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_post_meta_robots( int $post_id ): array {
		return $this->import_robots( $post_id, false );
	}

	/**
	 * Import meta-robots settings for a term.
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_term_meta_robots( int $term_id ): array {
		return $this->import_robots( $term_id, true );
	}

	/**
	 * Import general SEO settings for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_post_general_settings( int $post_id ): array {
		return $this->import_general_settings( $post_id, false );
	}

	/**
	 * Import general SEO settings for a term.
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_term_general_settings( int $term_id ): array {
		return $this->import_general_settings( $term_id, true );
	}

	/**
	 * Import social metadata for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_post_social( int $post_id ): array {
		return $this->import_social( $post_id, false );
	}

	/**
	 * Import social metadata for a term.
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function import_term_social( int $term_id ): array {
		return $this->import_social( $term_id, true );
	}

	/**
	 * {@inheritDoc}
	 */
	public function import_global_settings(): array {
		try {
			$this->source_settings   = Constants::get_global_settings();
			$this->surerank_settings = Settings::get();

			if ( empty( $this->source_settings ) ) {
				return ImporterUtils::build_response(
					__( 'No Slim SEO global settings found to import.', 'surerank' ),
					false
				);
			}

			$this->update_home_settings();
			$this->update_author_settings();
			$this->update_social_settings();
			$this->update_feature_settings();
			$this->update_noindex_settings();

			// Allow pro plugin to migrate CPT, archive, and taxonomy title/desc (Extended Meta Templates).
			$this->surerank_settings = apply_filters( 'surerank_slimseo_global_settings', $this->surerank_settings, $this->source_settings );

			ImporterUtils::update_global_settings( $this->surerank_settings );

			return ImporterUtils::build_response(
				__( 'Slim SEO global settings imported successfully.', 'surerank' ),
				true
			);
		} catch ( Exception $e ) {
			self::log(
				sprintf(
					/* translators: %s: error message. */
					__( 'Error importing Slim SEO global settings: %s', 'surerank' ),
					$e->getMessage()
				)
			);
			return ImporterUtils::build_response( $e->getMessage(), false );
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_not_allowed_types(): array {
		return Constants::NOT_ALLOWED_TYPES;
	}

	/**
	 * Get the source meta data for a post or term.
	 *
	 * @param int    $id          The ID of the post or term.
	 * @param bool   $is_taxonomy Whether it is a taxonomy.
	 * @param string $type        The type of post or term.
	 * @return array<string, mixed>
	 */
	protected function get_source_meta_data( int $id, bool $is_taxonomy, string $type = '' ): array {
		return Constants::get_slim_seo_meta( $id, $is_taxonomy );
	}

	/**
	 * Get the meta key prefix for the importer.
	 *
	 * @return string
	 */
	protected function get_meta_key_prefix(): string {
		return Constants::META_KEY_PREFIX;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function get_excluded_meta_keys(): array {
		return Constants::EXCLUDED_META_KEYS;
	}

	/**
	 * Import robots settings for a post or term.
	 *
	 * @param int  $id          Post or Term ID.
	 * @param bool $is_taxonomy Whether the ID is for a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_robots( int $id, bool $is_taxonomy ): array {
		try {
			$slim_seo_meta = Constants::get_slim_seo_meta( $id, $is_taxonomy );
			$noindex       = isset( $slim_seo_meta['noindex'] ) ? (int) $slim_seo_meta['noindex'] : 0;

			if ( $noindex === 1 ) {
				$this->default_surerank_meta['post_no_index'] = 'yes';

				return ImporterUtils::build_response(
					sprintf(
						// translators: %s: type, %d: ID.
						__( 'Meta-robots imported for %1$s %2$d.', 'surerank' ),
						$is_taxonomy ? 'term' : 'post',
						$id
					),
					true
				);
			}

			return ImporterUtils::build_response(
				sprintf(
					// translators: %s: type, %d: ID.
					__( 'No meta-robots settings to import for %1$s %2$d.', 'surerank' ),
					$is_taxonomy ? 'term' : 'post',
					$id
				),
				false
			);
		} catch ( Exception $e ) {
			self::log(
				sprintf(
					/* translators: 1: ID (used second), 2: type (used first), 3: error message. */
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
	 * Import general settings for a post or term.
	 *
	 * @param int  $id          Post or Term ID.
	 * @param bool $is_taxonomy Whether the ID is for a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_general_settings( int $id, bool $is_taxonomy ): array {
		$slim_seo_meta = Constants::get_slim_seo_meta( $id, $is_taxonomy );
		$imported      = false;

		// Map title, description, and canonical.
		foreach ( Constants::META_MAPPING as $slim_key => $surerank_key ) {
			if ( isset( $slim_seo_meta[ $slim_key ] ) && ! empty( $slim_seo_meta[ $slim_key ] ) ) {
				// Skip images for general settings (handled in social).
				if ( in_array( $slim_key, [ 'facebook_image', 'twitter_image' ], true ) ) {
					continue;
				}
				$value = (string) $slim_seo_meta[ $slim_key ];
				if ( in_array( $slim_key, [ 'title', 'description' ], true ) ) {
					$separator = isset( $this->surerank_settings['separator'] ) && is_string( $this->surerank_settings['separator'] ) ? $this->surerank_settings['separator'] : ' - ';
					$value     = Constants::replace_placeholders( $value, $separator );
				}
				$this->default_surerank_meta[ $surerank_key ] = $value;
				$imported                                     = true;
			}
		}

		if ( $imported ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: %s: type, %d: ID.
					__( 'General settings imported for %1$s %2$d.', 'surerank' ),
					$is_taxonomy ? 'term' : 'post',
					$id
				),
				true
			);
		}

		return ImporterUtils::build_response(
			sprintf(
				// translators: %s: type, %d: ID.
				__( 'No general settings to import for %1$s %2$d.', 'surerank' ),
				$is_taxonomy ? 'term' : 'post',
				$id
			),
			false
		);
	}

	/**
	 * Import social metadata for a post or term.
	 *
	 * @param int  $id          Post or Term ID.
	 * @param bool $is_taxonomy Whether the ID is for a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_social( int $id, bool $is_taxonomy ): array {
		// Clear social fields that were populated by Settings::prep_post_meta() with global defaults.
		// This ensures proper fallback behavior: if Slim SEO didn't have explicit values,
		// SureRank should also leave them empty to fall back to page_title/description at runtime.
		$social_fields_to_clear = [
			'facebook_title',
			'facebook_description',
			'twitter_title',
			'twitter_description',
			'fallback_image',
		];

		foreach ( $social_fields_to_clear as $field ) {
			$this->default_surerank_meta[ $field ] = '';
		}

		$slim_seo_meta = Constants::get_slim_seo_meta( $id, $is_taxonomy );
		$imported      = false;

		// Import Facebook and Twitter images.
		if ( isset( $slim_seo_meta['facebook_image'] ) && ! empty( $slim_seo_meta['facebook_image'] ) ) {
			$this->default_surerank_meta['facebook_image_url'] = $slim_seo_meta['facebook_image'];
			$imported = true;
		}

		$has_twitter_image = isset( $slim_seo_meta['twitter_image'] ) && ! empty( $slim_seo_meta['twitter_image'] );

		if ( $has_twitter_image ) {
			$this->default_surerank_meta['twitter_image_url'] = $slim_seo_meta['twitter_image'];
			$imported = true;
		}

		// If Twitter image exists, Twitter is using its own image (not same as Facebook).
		// If no Twitter image, default to using Facebook settings.
		$this->default_surerank_meta['twitter_same_as_facebook'] = ! $has_twitter_image;

		if ( $imported ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: %s: type, %d: ID.
					__( 'Social metadata imported for %1$s %2$d.', 'surerank' ),
					$is_taxonomy ? 'term' : 'post',
					$id
				),
				true
			);
		}

		return ImporterUtils::build_response(
			sprintf(
				// translators: %s: type, %d: ID.
				__( 'No social metadata to import for %1$s %2$d.', 'surerank' ),
				$is_taxonomy ? 'term' : 'post',
				$id
			),
			false
		);
	}

	/**
	 * Update home page settings from Slim SEO.
	 *
	 * @return void
	 */
	private function update_home_settings(): void {
		// Slim SEO stores home settings directly under 'home' key.
		if ( isset( $this->source_settings['home'] ) && is_array( $this->source_settings['home'] ) ) {
			$home_settings = $this->source_settings['home'];

			$separator = isset( $this->surerank_settings['separator'] ) && is_string( $this->surerank_settings['separator'] ) ? $this->surerank_settings['separator'] : ' - ';

			// Migrate home page title.
			if ( isset( $home_settings['title'] ) && ! empty( $home_settings['title'] ) ) {
				$title                                      = Constants::replace_placeholders( (string) $home_settings['title'], $separator );
				$this->surerank_settings['home_page_title'] = $title;
			}

			// Migrate home page description.
			if ( isset( $home_settings['description'] ) && ! empty( $home_settings['description'] ) ) {
				$description                                      = Constants::replace_placeholders( (string) $home_settings['description'], $separator );
				$this->surerank_settings['home_page_description'] = $description;
			}

			// Migrate homepage Facebook image.
			if ( isset( $home_settings['facebook_image'] ) && ! empty( $home_settings['facebook_image'] ) ) {
				$this->surerank_settings['home_page_facebook_image_url'] = $home_settings['facebook_image'];
			}

			// Migrate homepage Twitter image.
			if ( isset( $home_settings['twitter_image'] ) && ! empty( $home_settings['twitter_image'] ) ) {
				$this->surerank_settings['home_page_twitter_image_url'] = $home_settings['twitter_image'];
			}

			// Slim SEO doesn't have separate social titles/descriptions for homepage.
			// Use the general home title/description for both Facebook and Twitter.
			if ( isset( $title ) && ! empty( $title ) ) {
				$this->surerank_settings['home_page_facebook_title'] = $title;
				$this->surerank_settings['home_page_twitter_title']  = $title;
			}

			if ( isset( $description ) && ! empty( $description ) ) {
				$this->surerank_settings['home_page_facebook_description'] = $description;
				$this->surerank_settings['home_page_twitter_description']  = $description;
			}
		}
	}

	/**
	 * Update noindex settings for post types, archives, and taxonomies from Slim SEO.
	 *
	 * @return void
	 */
	private function update_noindex_settings(): void {
		$this->surerank_settings['no_index'] = [];

		foreach ( $this->post_types as $post_type ) {
			if ( isset( $this->source_settings[ $post_type ]['noindex'] ) && ! empty( $this->source_settings[ $post_type ]['noindex'] ) ) {
				$this->surerank_settings['no_index'][] = $post_type;
			}

			$archive_key = $post_type . '_archive';
			if ( isset( $this->source_settings[ $archive_key ]['noindex'] ) && ! empty( $this->source_settings[ $archive_key ]['noindex'] ) ) {
				$this->surerank_settings['no_index'][] = $archive_key;
			}
		}

		foreach ( $this->taxonomies as $taxonomy ) {
			$taxonomy_name = $taxonomy->name;
			if ( isset( $this->source_settings[ $taxonomy_name ]['noindex'] ) && ! empty( $this->source_settings[ $taxonomy_name ]['noindex'] ) ) {
				$this->surerank_settings['no_index'][] = $taxonomy_name;
			}
		}
	}

	/**
	 * Update author settings from Slim SEO.
	 *
	 * @return void
	 */
	private function update_author_settings(): void {
		// Slim SEO stores author settings directly under 'author' key.
		if ( isset( $this->source_settings['author'] ) && is_array( $this->source_settings['author'] ) ) {
			$author_settings = $this->source_settings['author'];

			if ( isset( $author_settings['title'] ) && ! empty( $author_settings['title'] ) ) {
				$separator                                       = isset( $this->surerank_settings['separator'] ) && is_string( $this->surerank_settings['separator'] ) ? $this->surerank_settings['separator'] : ' - ';
				$this->surerank_settings['author_archive_title'] = Constants::replace_placeholders( (string) $author_settings['title'], $separator );
			}

			if ( isset( $author_settings['description'] ) && ! empty( $author_settings['description'] ) ) {
				$separator = isset( $this->surerank_settings['separator'] ) && is_string( $this->surerank_settings['separator'] ) ? $this->surerank_settings['separator'] : ' - ';
				$this->surerank_settings['author_archive_description'] = Constants::replace_placeholders( (string) $author_settings['description'], $separator );
			}
		}
	}

	/**
	 * Update social settings from Slim SEO.
	 *
	 * @return void
	 */
	private function update_social_settings(): void {
		foreach ( Constants::SOCIAL_SETTINGS_MAPPING as $slim_key => $surerank_key ) {
			// Special handling for fallback_image since both default_facebook_image
			// and default_twitter_image map to it. Prioritize Facebook image.
			if ( 'fallback_image' === $surerank_key ) {
				// Skip if already set by default_facebook_image.
				if ( isset( $this->surerank_settings['fallback_image'] ) && 'default_facebook_image' !== $slim_key ) {
					continue;
				}
			}

			if ( isset( $this->source_settings[ $slim_key ] ) && ! empty( $this->source_settings[ $slim_key ] ) ) {
				$this->surerank_settings[ $surerank_key ] = $this->source_settings[ $slim_key ];
			}
		}
	}

	/**
	 * Update feature toggles from Slim SEO.
	 *
	 * Migrates Slim SEO's 'features' array to SureRank's feature toggle settings.
	 *
	 * @return void
	 */
	private function update_feature_settings(): void {
		if ( ! isset( $this->source_settings['features'] ) || ! is_array( $this->source_settings['features'] ) ) {
			return;
		}

		$features = $this->source_settings['features'];

		// Map each feature to its SureRank setting.
		foreach ( Constants::FEATURE_MAPPING as $slim_feature => $surerank_setting ) {
			// Only migrate if feature is enabled in Slim SEO.
			if ( in_array( $slim_feature, $features, true ) ) {
				$this->surerank_settings[ $surerank_setting ] = true;
			} else {
				$this->surerank_settings[ $surerank_setting ] = false;
			}
		}
	}
}
