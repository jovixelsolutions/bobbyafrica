<?php
/**
 * Squirrly SEO Importer Class
 *
 * Handles importing SEO data from the Squirrly SEO plugin into SureRank.
 *
 * @package SureRank\Inc\Importers
 * @since   1.6.6
 */

namespace SureRank\Inc\Importers\Squirrly;

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
 * Class Squirrly
 *
 * Implements Squirrly SEO → SureRank migration.
 */
class Squirrly extends BaseImporter {

	use Logger;

	// -------------------------------------------------------------------------
	// Importer interface – identity methods
	// -------------------------------------------------------------------------

	/**
	 * Get the source plugin name.
	 *
	 * @return string
	 */
	public function get_plugin_name(): string {
		return Constants::PLUGIN_NAME;
	}

	/**
	 * Get the source plugin file path (relative to wp-content/plugins/).
	 *
	 * @return string
	 */
	public function get_plugin_file(): string {
		return Constants::PLUGIN_FILE;
	}

	/**
	 * Check whether Squirrly SEO is currently active.
	 *
	 * Squirrly defines SQ_OPTION in its config/config.php which is loaded on
	 * every request when the plugin is active.
	 *
	 * @return bool
	 */
	public function is_plugin_active(): bool {
		return defined( 'SQ_OPTION' );
	}

	// -------------------------------------------------------------------------
	// Detection overrides (qss table replaces wp_postmeta prefix scan)
	// -------------------------------------------------------------------------

	/**
	 * Detect whether Squirrly has SEO data for the given post.
	 *
	 * Overrides BaseImporter::detect_post() because Squirrly's primary data
	 * store is the qss table, not wp_postmeta. The base class checks for a
	 * meta_key prefix in wp_postmeta which would miss most Squirrly data.
	 *
	 * @param int $post_id Post ID.
	 * @return array{success: bool, message: string}
	 */
	public function detect_post( int $post_id ): array {
		$seo_data = Constants::get_seo_data( $post_id, false );

		if ( ! empty( $seo_data ) ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: 1: plugin name, 2: post ID.
					__( '%1$s data detected for post %2$d.', 'surerank' ),
					$this->get_plugin_name(),
					$post_id
				),
				true
			);
		}

		// No data found – mark as migrated so this post is skipped on future runs.
		ImporterUtils::update_surerank_migrated( $post_id );

		return ImporterUtils::build_response(
			sprintf(
				// translators: 1: plugin name, 2: post ID.
				__( 'No %1$s data found for post %2$d.', 'surerank' ),
				$this->get_plugin_name(),
				$post_id
			),
			false,
			[],
			true
		);
	}

	/**
	 * Detect whether Squirrly has SEO data for the given term.
	 *
	 * Squirrly stores zero term data in wp_termmeta; all term SEO lives in qss.
	 *
	 * @param int $term_id Term ID.
	 * @return array{success: bool, message: string}
	 */
	public function detect_term( int $term_id ): array {
		$seo_data = Constants::get_seo_data( $term_id, true );

		if ( ! empty( $seo_data ) ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: 1: plugin name, 2: term ID.
					__( '%1$s data detected for term %2$d.', 'surerank' ),
					$this->get_plugin_name(),
					$term_id
				),
				true
			);
		}

		ImporterUtils::update_surerank_migrated( $term_id, false );

		return ImporterUtils::build_response(
			sprintf(
				// translators: 1: plugin name, 2: term ID.
				__( 'No %1$s data found for term %2$d.', 'surerank' ),
				$this->get_plugin_name(),
				$term_id
			),
			false,
			[],
			true
		);
	}

	// -------------------------------------------------------------------------
	// Per-item import methods
	// -------------------------------------------------------------------------

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
	 * Import general SEO settings (title, description, canonical, focus keyword) for a post.
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
	 * Import social metadata (Open Graph + Twitter Card) for a post.
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

	// -------------------------------------------------------------------------
	// Global settings import
	// -------------------------------------------------------------------------

	/**
	 * Import global settings from sq_options into SureRank.
	 *
	 * Reads the JSON-encoded sq_options option and maps:
	 *  - Social profile URLs (Facebook, Twitter, etc.)
	 *  - Feature flags (sitemap, Open Graph, Twitter Cards)
	 *  - Meta Templates: post-type title / description patterns → page_title / page_description
	 *  - Homepage title / description patterns
	 *  - Organisation / site identity for schema
	 *
	 * @return array{success: bool, message: string}
	 */
	public function import_global_settings(): array {
		$sq_options = Constants::get_sq_options();

		if ( empty( $sq_options ) ) {
			return ImporterUtils::build_response(
				__( 'No Squirrly SEO global settings found to import.', 'surerank' ),
				false
			);
		}

		$this->source_settings   = $sq_options;
		$this->surerank_settings = Settings::get();

		$this->update_social_settings();
		$this->update_global_flags();
		$this->update_meta_template_settings();
		$this->update_homepage_settings();
		$this->update_global_robot_instructions();
		$this->update_site_details();
		$this->update_robots_txt_settings();
		$this->update_sitemap_settings();

		try {
			ImporterUtils::update_global_settings( $this->surerank_settings );
			return ImporterUtils::build_response(
				__( 'Squirrly SEO global settings imported successfully.', 'surerank' ),
				true
			);
		} catch ( Exception $e ) {
			self::log(
				sprintf(
					/* translators: %s: error message */
					__( 'Error importing Squirrly SEO global settings: %s', 'surerank' ),
					$e->getMessage()
				)
			);
			return ImporterUtils::build_response( $e->getMessage(), false );
		}
	}

	// -------------------------------------------------------------------------
	// Batch ID gathering – overrides (qss table instead of wp_postmeta prefix)
	// -------------------------------------------------------------------------

	/**
	 * Return a paginated list of post IDs that have Squirrly SEO data.
	 *
	 * Overrides BaseImporter::get_count_and_posts() because the base
	 * implementation uses a wp_postmeta LIKE query against the plugin's meta_key
	 * prefix, which would only find the small subset of posts with _sq_title
	 * postmeta and miss every post stored only in the qss table.
	 *
	 * Two sources are combined:
	 *  1. qss table   – all rows whose `post` column has an empty taxonomy field
	 *  2. wp_postmeta – any remaining posts with _sq_title (legacy / fallback)
	 *
	 * @param array<string> $post_types Valid public post type names.
	 * @param int           $batch_size Maximum IDs to return per page.
	 * @param int           $offset     Number of IDs to skip (for pagination).
	 * @return array{total_items: int, post_ids: array<int>}
	 */
	public function get_count_and_posts( $post_types, $batch_size, $offset ): array {
		$all_ids     = $this->build_squirrly_post_ids( $post_types );
		$total_items = count( $all_ids );
		$post_ids    = array_slice( $all_ids, $offset, $batch_size );

		return [
			'total_items' => $total_items,
			'post_ids'    => $post_ids,
		];
	}

	/**
	 * Return a paginated list of term IDs that have Squirrly SEO data.
	 *
	 * Overrides BaseImporter::get_count_and_terms() because Squirrly stores
	 * zero term SEO data in wp_termmeta; all term data is in the qss table.
	 *
	 * @param array<string>         $taxonomies         Public taxonomy slug list.
	 * @param array<string, object> $taxonomies_objects Taxonomy objects (unused; kept for interface compat).
	 * @param int                   $batch_size         Maximum IDs to return.
	 * @param int                   $offset             Pagination offset.
	 * @return array{total_items: int, term_ids: array<int>}
	 */
	public function get_count_and_terms( $taxonomies, $taxonomies_objects, $batch_size, $offset ): array {
		$all_ids     = $this->build_squirrly_term_ids( $taxonomies );
		$total_items = count( $all_ids );
		$term_ids    = array_slice( $all_ids, $offset, $batch_size );

		return [
			'total_items' => $total_items,
			'term_ids'    => $term_ids,
		];
	}

	// -------------------------------------------------------------------------
	// BaseImporter abstract method implementations
	// -------------------------------------------------------------------------

	/**
	 * Get the not-allowed post/term types for this importer.
	 *
	 * @return array<string>
	 */
	protected function get_not_allowed_types(): array {
		return Constants::NOT_ALLOWED_TYPES;
	}

	/**
	 * Get source SEO data for a post or term from the qss table.
	 *
	 * Called by BaseImporter::import_post() / import_term() before the
	 * import_post_* methods are invoked, populating $this->source_meta.
	 *
	 * @param int    $id          Post or term ID.
	 * @param bool   $is_taxonomy Whether it is a taxonomy term.
	 * @param string $type        Post type / taxonomy slug (kept for interface compat; unused).
	 * @return array<string, mixed>
	 */
	protected function get_source_meta_data( int $id, bool $is_taxonomy, string $type = '' ): array {
		return Constants::get_seo_data( $id, $is_taxonomy );
	}

	/**
	 * Return the wp_postmeta key prefix for the BaseImporter has_source_meta() helper.
	 *
	 * Used only by the base-class detect_post() which this class overrides, so
	 * it effectively acts as a fallback identifier for the postmeta path.
	 *
	 * @return string
	 */
	protected function get_meta_key_prefix(): string {
		return Constants::META_KEY_PREFIX;
	}

	/**
	 * Meta keys to exclude when scanning wp_postmeta (none for Squirrly).
	 *
	 * @return array<string>
	 */
	protected function get_excluded_meta_keys(): array {
		return [];
	}

	// -------------------------------------------------------------------------
	// Private per-item import helpers
	// -------------------------------------------------------------------------

	/**
	 * Import noindex / nofollow robots directives for a post or term.
	 *
	 * Squirrly stores these as integer flags (0/1) in the qss.seo object,
	 * while SureRank stores them as 'yes'/'no' string values.
	 *
	 * @param int  $id          Post or term ID.
	 * @param bool $is_taxonomy Whether it is a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_robots( int $id, bool $is_taxonomy ): array {
		try {
			$noindex  = (int) ( $this->source_meta['noindex'] ?? 0 );
			$nofollow = (int) ( $this->source_meta['nofollow'] ?? 0 );

			// Write 'yes' only when Squirrly explicitly noindexed/nofollowed the item.
			// An empty string lets SureRank fall back to the global robots settings at
			// runtime (e.g. "noindex all attachments"), which is the correct behaviour
			// when Squirrly had no per-item override. Storing 'no' (non-empty) would
			// bypass the global fallback branch in robots.php::print_meta().
			$this->default_surerank_meta['post_no_index']  = $noindex ? 'yes' : '';
			$this->default_surerank_meta['post_no_follow'] = $nofollow ? 'yes' : '';

			return ImporterUtils::build_response(
				sprintf(
					// translators: %1$s: type (post|term), %2$d: ID.
					__( 'Meta-robots imported for %1$s %2$d.', 'surerank' ),
					$is_taxonomy ? 'term' : 'post',
					$id
				),
				true
			);
		} catch ( Exception $e ) {
			self::log(
				sprintf(
					/* translators: 1: ID, 2: type, 3: error message */
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
	 * Import title, description, canonical URL and focus keyword for a post or term.
	 *
	 * Squirrly {{variable}} placeholders are converted to SureRank %variable%
	 * equivalents.  The first comma-separated keyword is used as focus_keyword.
	 *
	 * @param int  $id          Post or term ID.
	 * @param bool $is_taxonomy Whether it is a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_general_settings( int $id, bool $is_taxonomy ): array {
		$sep      = $this->get_separator();
		$imported = false;

		$mapping = [
			'title'       => 'page_title',
			'description' => 'page_description',
		];

		foreach ( $mapping as $sq_key => $sr_key ) {
			$value = (string) ( $this->source_meta[ $sq_key ] ?? '' );
			if ( '' !== $value ) {
				$this->default_surerank_meta[ $sr_key ] = Constants::replace_placeholders( $value, $sep );
				$imported                               = true;
			}
		}

		// Canonical URLs are absolute strings — never run through placeholder replacement.
		$canonical = esc_url_raw( (string) ( $this->source_meta['canonical'] ?? '' ) );
		if ( '' !== $canonical ) {
			$this->default_surerank_meta['canonical_url'] = $canonical;
			$imported                                     = true;
		}

		// Map the first comma-separated keyword to SureRank's focus_keyword field.
		$keywords = (string) ( $this->source_meta['keywords'] ?? '' );
		if ( '' !== $keywords ) {
			$first = strtok( $keywords, ',' );
			if ( false !== $first && '' !== trim( $first ) ) {
				$this->default_surerank_meta['focus_keyword'] = trim( $first );
				$imported                                     = true;
			}
		}

		if ( $imported ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: %1$s: type (post|term), %2$d: ID.
					__( 'General settings imported for %1$s %2$d.', 'surerank' ),
					$is_taxonomy ? 'term' : 'post',
					$id
				),
				true
			);
		}

		return ImporterUtils::build_response(
			sprintf(
				// translators: %1$s: type (post|term), %2$d: ID.
				__( 'No general settings to import for %1$s %2$d.', 'surerank' ),
				$is_taxonomy ? 'term' : 'post',
				$id
			),
			false
		);
	}

	/**
	 * Import Open Graph and Twitter Card metadata for a post or term.
	 *
	 * Squirrly stores OG/Twitter images as plain URL strings in og_media / tw_media
	 * (normalised by parse_seo_column() from SQ_Models_Domain_Sq::toArray()).
	 * Social fields are pre-cleared so SureRank falls back to the post
	 * title/description at runtime when Squirrly had no explicit social values set.
	 *
	 * @param int  $id          Post or term ID.
	 * @param bool $is_taxonomy Whether it is a taxonomy term.
	 * @return array{success: bool, message: string}
	 */
	private function import_social( int $id, bool $is_taxonomy ): array {
		// Clear defaults populated by Settings::prep_post_meta() so SureRank
		// inherits from page_title / page_description at display time when
		// Squirrly had no explicit social values for this post.
		$clear_fields = [ 'facebook_title', 'facebook_description', 'twitter_title', 'twitter_description', 'fallback_image' ];
		foreach ( $clear_fields as $field ) {
			$this->default_surerank_meta[ $field ] = '';
		}

		$sep      = $this->get_separator();
		$imported = false;

		// --- Open Graph ---
		$og_string_map = [
			'og_title'       => 'facebook_title',
			'og_description' => 'facebook_description',
		];
		foreach ( $og_string_map as $sq_key => $sr_key ) {
			$value = (string) ( $this->source_meta[ $sq_key ] ?? '' );
			if ( '' !== $value ) {
				$this->default_surerank_meta[ $sr_key ] = Constants::replace_placeholders( $value, $sep );
				$imported                               = true;
			}
		}

		// og_media is a plain URL string per SQ_Models_Domain_Sq::toArray().
		$og_image = esc_url_raw( (string) ( $this->source_meta['og_media'] ?? '' ) );
		if ( '' !== $og_image ) {
			$this->default_surerank_meta['facebook_image_url'] = $og_image;
			$imported = true;
		}

		// --- Twitter Card ---
		$tw_string_map = [
			'tw_title'       => 'twitter_title',
			'tw_description' => 'twitter_description',
		];
		foreach ( $tw_string_map as $sq_key => $sr_key ) {
			$value = (string) ( $this->source_meta[ $sq_key ] ?? '' );
			if ( '' !== $value ) {
				$this->default_surerank_meta[ $sr_key ] = Constants::replace_placeholders( $value, $sep );
				$imported                               = true;
			}
		}

		// tw_media is a plain URL string per SQ_Models_Domain_Sq::toArray().
		$tw_image = esc_url_raw( (string) ( $this->source_meta['tw_media'] ?? '' ) );
		if ( '' !== $tw_image ) {
			$this->default_surerank_meta['twitter_image_url'] = $tw_image;
			$imported = true;
		}

		if ( ! empty( $this->source_meta['tw_type'] ) ) {
			$this->default_surerank_meta['twitter_card_type'] = (string) $this->source_meta['tw_type'];
			$imported = true;
		}

		// Set twitter_same_as_facebook based on whether Squirrly had any
		// Twitter-specific data. When true, SureRank inherits OG values for
		// Twitter at display time. When false, any Twitter-specific fields take effect.
		$has_twitter_specific = '' !== $tw_image
			|| '' !== (string) ( $this->source_meta['tw_title'] ?? '' )
			|| '' !== (string) ( $this->source_meta['tw_description'] ?? '' );

		$this->default_surerank_meta['twitter_same_as_facebook'] = ! $has_twitter_specific;

		// When Twitter-specific title/desc exists but no Twitter image was set,
		// carry the OG image forward so the image is not lost.
		if ( $has_twitter_specific && '' === $tw_image && '' !== $og_image ) {
			$this->default_surerank_meta['twitter_image_url'] = $og_image;
		}

		// Resolve WordPress attachment IDs for any imported image URLs so the
		// admin UI MediaPreview component can display the correct thumbnails.
		if ( ! empty( $this->default_surerank_meta['facebook_image_url'] ) ) {
			$fb_id = attachment_url_to_postid( $this->default_surerank_meta['facebook_image_url'] );
			if ( $fb_id ) {
				$this->default_surerank_meta['facebook_image_id'] = $fb_id;
			}
		}
		if ( ! empty( $this->default_surerank_meta['twitter_image_url'] ) ) {
			$tw_id = attachment_url_to_postid( $this->default_surerank_meta['twitter_image_url'] );
			if ( $tw_id ) {
				$this->default_surerank_meta['twitter_image_id'] = $tw_id;
			}
		}

		if ( $imported ) {
			return ImporterUtils::build_response(
				sprintf(
					// translators: %1$s: type (post|term), %2$d: ID.
					__( 'Social metadata imported for %1$s %2$d.', 'surerank' ),
					$is_taxonomy ? 'term' : 'post',
					$id
				),
				true
			);
		}

		return ImporterUtils::build_response(
			sprintf(
				// translators: %1$s: type (post|term), %2$d: ID.
				__( 'No social metadata to import for %1$s %2$d.', 'surerank' ),
				$is_taxonomy ? 'term' : 'post',
				$id
			),
			false
		);
	}

	// -------------------------------------------------------------------------
	// Private global-settings helpers
	// -------------------------------------------------------------------------

	/**
	 * Map sq_options.socials keys into SureRank global settings and social_profiles.
	 *
	 * Squirrly nests all social account data under a `socials` key inside
	 * sq_options, e.g. { "facebook_site": "https://...", "twitter": "@handle" }.
	 *
	 * @return void
	 */
	private function update_social_settings(): void {
		$socials = $this->source_settings['socials'] ?? [];

		if ( ! is_array( $socials ) ) {
			return;
		}

		// Map direct settings (twitter_card_type, twitter_profile_username, facebook_page_url).
		foreach ( Constants::get_social_settings_mapping() as $sq_key => $sr_key ) {
			if ( ! empty( $socials[ $sq_key ] ) ) {
				$value = (string) $socials[ $sq_key ];

				// SureRank's frontend prepends '@' when rendering twitter:site, so strip
				// any leading '@' from Squirrly's handle to prevent '@@username' output.
				if ( 'twitter_profile_username' === $sr_key ) {
					$value = ltrim( $value, '@' );
				}

				$this->surerank_settings[ $sr_key ] = $value;
			}
		}

		// Map social platform URLs into SureRank's social_profiles array.
		$profile_map = [
			'facebook_site' => 'facebook',
			'twitter'       => 'twitter',
			'instagram_url' => 'instagram',
			'linkedin_url'  => 'linkedin',
			'youtube_url'   => 'youtube',
			'pinterest_url' => 'pinterest',
		];

		foreach ( $profile_map as $sq_key => $sr_profile ) {
			if ( ! empty( $socials[ $sq_key ] ) ) {
				$this->surerank_settings['social_profiles'][ $sr_profile ] = (string) $socials[ $sq_key ];
			}
		}
	}

	/**
	 * Map sq_options top-level flags (sitemap, OG, Twitter Cards, fallback image).
	 *
	 * Boolean flags (sq_auto_*) are cast to integers (1/0) to match SureRank's
	 * storage format.
	 *
	 * @return void
	 */
	private function update_global_flags(): void {
		$bool_keys = [ 'sq_auto_sitemap', 'sq_auto_facebook', 'sq_auto_twitter' ];

		foreach ( Constants::get_global_settings_mapping() as $sq_key => $sr_key ) {
			if ( ! isset( $this->source_settings[ $sq_key ] ) ) {
				continue;
			}

			$value = $this->source_settings[ $sq_key ];

			$this->surerank_settings[ $sr_key ] = in_array( $sq_key, $bool_keys, true )
				? ( (bool) $value ? 1 : 0 )
				: $value;
		}
	}

	/**
	 * Map the Squirrly homepage automation pattern to SureRank homepage settings.
	 *
	 * @return void
	 */
	private function update_homepage_settings(): void {
		$patterns = $this->source_settings['patterns'] ?? [];

		if ( empty( $patterns['home'] ) || ! is_array( $patterns['home'] ) ) {
			return;
		}

		$home = $patterns['home'];
		$sep  = $this->get_separator( $home['sep'] ?? null );

		if ( ! empty( $home['title'] ) ) {
			$this->surerank_settings['home_page_title'] = Constants::replace_placeholders(
				(string) $home['title'],
				$sep
			);
		}

		if ( ! empty( $home['description'] ) ) {
			$this->surerank_settings['home_page_description'] = Constants::replace_placeholders(
				(string) $home['description'],
				$sep
			);
		}

		// Map noindex flag if set.
		if ( ! empty( $home['noindex'] ) ) {
			if ( ! in_array( 'noindex', $this->surerank_settings['home_page_robots']['general'] ?? [], true ) ) {
				$this->surerank_settings['home_page_robots']['general'][] = 'noindex';
			}
		}

		// Map nofollow flag if set.
		if ( ! empty( $home['nofollow'] ) ) {
			if ( ! in_array( 'nofollow', $this->surerank_settings['home_page_robots']['general'] ?? [], true ) ) {
				$this->surerank_settings['home_page_robots']['general'][] = 'nofollow';
			}
		}
	}

	/**
	 * Map Squirrly Automation noindex / nofollow defaults to SureRank global robot instructions.
	 *
	 * Squirrly stores per-type defaults in sq_options.patterns, where each pattern entry may include
	 * `noindex` and `nofollow` integer flags. SureRank stores global robot instructions in two arrays:
	 *   - no_index
	 *   - no_follow
	 *
	 * For each supported post-type / taxonomy / archive type, this method keeps SureRank arrays in sync:
	 *   - truthy Squirrly flag  => ensure type exists in target array
	 *   - falsy Squirrly flag   => ensure type is removed from target array
	 *
	 * @return void
	 */
	private function update_global_robot_instructions(): void {
		$patterns = $this->source_settings['patterns'] ?? [];

		if ( ! is_array( $patterns ) || empty( $patterns ) ) {
			return;
		}

		if ( ! isset( $this->surerank_settings['no_index'] ) || ! is_array( $this->surerank_settings['no_index'] ) ) {
			$this->surerank_settings['no_index'] = [];
		}

		if ( ! isset( $this->surerank_settings['no_follow'] ) || ! is_array( $this->surerank_settings['no_follow'] ) ) {
			$this->surerank_settings['no_follow'] = [];
		}

		foreach ( $patterns as $pattern_type => $pattern_config ) {
			if ( ! is_string( $pattern_type ) || ! is_array( $pattern_config ) ) {
				continue;
			}

			$surerank_type = $this->normalize_squirrly_pattern_type( $pattern_type );
			if ( '' === $surerank_type ) {
				continue;
			}

			$this->sync_robot_type_in_global_settings( 'no_index', $surerank_type, ! empty( $pattern_config['noindex'] ) );
			$this->sync_robot_type_in_global_settings( 'no_follow', $surerank_type, ! empty( $pattern_config['nofollow'] ) );
		}
	}

	/**
	 * Resolve a Squirrly patterns key to a SureRank-supported page type slug.
	 *
	 * Examples:
	 *  - tag            -> post_tag
	 *  - tax-product_cat -> product_cat
	 *  - post/page/product/category/search/archive -> unchanged
	 *
	 * Unsupported pseudo-types (e.g. home, profile, 404, custom) return an empty string.
	 *
	 * @param string $pattern_type Squirrly pattern key.
	 * @return string
	 */
	private function normalize_squirrly_pattern_type( string $pattern_type ): string {
		$type = trim( $pattern_type );
		if ( '' === $type ) {
			return '';
		}

		if ( 'tag' === $type ) {
			$type = 'post_tag';
		}

		if ( 0 === strpos( $type, 'tax-' ) ) {
			$type = substr( $type, 4 );
		}

		if ( in_array( $type, Constants::NOT_ALLOWED_TYPES, true ) ) {
			return '';
		}

		$special_supported = [ 'author', 'date', 'search', 'archive', 'category', 'post_tag', 'post_format' ];

		if ( in_array( $type, $special_supported, true ) ) {
			return $type;
		}

		if ( post_type_exists( $type ) || taxonomy_exists( $type ) ) {
			return $type;
		}

		return '';
	}

	/**
	 * Ensure a type is present/absent in a specific global robots settings array.
	 *
	 * @param string $settings_key Global settings key: no_index or no_follow.
	 * @param string $type         SureRank page type slug.
	 * @param bool   $enabled      Whether robots directive is enabled for this type.
	 * @return void
	 */
	private function sync_robot_type_in_global_settings( string $settings_key, string $type, bool $enabled ): void {
		if ( ! isset( $this->surerank_settings[ $settings_key ] ) || ! is_array( $this->surerank_settings[ $settings_key ] ) ) {
			$this->surerank_settings[ $settings_key ] = [];
		}

		$is_present = in_array( $type, $this->surerank_settings[ $settings_key ], true );

		if ( $enabled && ! $is_present ) {
			$this->surerank_settings[ $settings_key ][] = $type;
			return;
		}

		if ( ! $enabled && $is_present ) {
			$this->surerank_settings[ $settings_key ] = array_values(
				array_diff( $this->surerank_settings[ $settings_key ], [ $type ] )
			);
		}
	}

	/**
	 * Map Squirrly organisation / site identity data to SureRank onboarding schema.
	 *
	 * @return void
	 */
	private function update_site_details(): void {
		$jsonld = $this->source_settings['sq_jsonld'] ?? [];

		if ( empty( $jsonld ) || ! is_array( $jsonld ) ) {
			return;
		}

		$site_data = [];

		if ( ! empty( $jsonld['name'] ) ) {
			$site_data['website_name'] = (string) $jsonld['name'];
		}

		// Logo may be nested as { "url": "https://..." }.
		$logo_url = esc_url_raw( (string) ( $jsonld['logo']['url'] ?? '' ) );
		if ( ! empty( $logo_url ) ) {
			$site_data['website_logo'] = $logo_url;
		}

		$jsonld_type = strtolower( (string) ( $this->source_settings['sq_jsonld_type'] ?? '' ) );
		if ( ! empty( $jsonld_type ) ) {
			$is_person                      = 'person' === $jsonld_type;
			$site_data['organization_type'] = $is_person ? 'Person' : 'Organization';
			$site_data['website_type']      = $is_person ? 'person' : 'organization';
		}

		if ( ! empty( $site_data ) ) {
			Onboarding::update_common_onboarding_data( $site_data );
		}
	}

	/**
	 * Migrate Squirrly robots.txt content to the SureRank robots.txt option.
	 *
	 * Squirrly stores the custom robots.txt as an array of strings (one entry
	 * per line) under sq_options.sq_robots_permission, gated by sq_auto_robots.
	 * SureRank stores the merged content as a single newline-delimited string in
	 * the surerank_robots_txt_content option (not part of the settings array).
	 *
	 * @return void
	 */
	private function update_robots_txt_settings(): void {
		if ( empty( $this->source_settings['sq_auto_robots'] ) ) {
			return;
		}

		$robots_permission = $this->source_settings['sq_robots_permission'] ?? [];

		if ( ! is_array( $robots_permission ) || empty( $robots_permission ) ) {
			return;
		}

		$robots_permission = array_filter( $robots_permission, 'is_string' );

		if ( empty( $robots_permission ) ) {
			return;
		}

		$robots_content = implode( "\n", $robots_permission );
		update_option( SURERANK_ROBOTS_TXT_CONTENT, sanitize_textarea_field( $robots_content ), false );
	}

	/**
	 * Map Squirrly patterns.post to SureRank Meta Templates (page_title / page_description).
	 *
	 * SureRank uses a single global page_title / page_description pair as the
	 * default template for all post types.  Squirrly stores per-type automation
	 * patterns under sq_options.patterns keyed by type name.  The 'post' pattern
	 * is the canonical source for the generic template because it is Squirrly's
	 * closest equivalent to "the default template applied to regular content".
	 *
	 * The separator stored in patterns.post.sep (e.g. 'sc-pipe') is resolved to
	 * the actual character and written to the SureRank global 'separator' setting
	 * so that SureRank renders titles consistently with how Squirrly did.
	 *
	 * @return void
	 */
	private function update_meta_template_settings(): void {
		$patterns = $this->source_settings['patterns'] ?? [];

		if ( ! is_array( $patterns ) ) {
			return;
		}

		$post_pattern = $patterns['post'] ?? [];

		if ( empty( $post_pattern ) || ! is_array( $post_pattern ) ) {
			return;
		}

		$sep = $this->get_separator( $post_pattern['sep'] ?? null );

		if ( ! empty( $post_pattern['title'] ) ) {
			$this->surerank_settings['page_title'] = Constants::replace_placeholders(
				(string) $post_pattern['title'],
				$sep
			);
		}

		if ( ! empty( $post_pattern['description'] ) ) {
			$this->surerank_settings['page_description'] = Constants::replace_placeholders(
				(string) $post_pattern['description'],
				$sep
			);
		}

		// Sync the global separator character so SureRank formats all titles
		// consistently with Squirrly's configured separator.
		if ( ! empty( $post_pattern['sep'] ) ) {
			$this->surerank_settings['separator'] = $sep;
		}
	}

	/**
	 * Map Squirrly image sitemap flag to SureRank settings.
	 *
	 * Squirrly stores per-type visibility flags in sq_options.sq_sitemap_show.
	 * The 'images' key controls whether images are included in the XML sitemap.
	 *
	 * @return void
	 */
	private function update_sitemap_settings(): void {
		$sq_sitemap_show_raw = $this->source_settings['sq_sitemap_show'] ?? null;
		$sq_sitemap_show     = is_object( $sq_sitemap_show_raw )
			? (array) $sq_sitemap_show_raw
			: ( is_array( $sq_sitemap_show_raw ) ? $sq_sitemap_show_raw : [] );

		if ( array_key_exists( 'images', $sq_sitemap_show ) ) {
			$this->surerank_settings['enable_xml_image_sitemap'] = ! empty( $sq_sitemap_show['images'] );
		}
	}

	// -------------------------------------------------------------------------
	// Private batch-ID building helpers
	// -------------------------------------------------------------------------

	/**
	 * Build a deduplicated, non-migrated list of post IDs that have Squirrly data.
	 *
	 * Merges IDs from two sources:
	 *  1. qss table   – reads the `post` column (a plain PHP serialised array per row)
	 *                   then validates each ID against wp_posts in a single batch query.
	 *                   This is necessary because Squirrly stores internal type names
	 *                   ('home', 'shop') that differ from the real WP post_type.
	 *  2. wp_postmeta – posts with _sq_title (legacy / fallback path).
	 *
	 * @param array<string> $post_types Valid public post types to include.
	 * @return array<int>
	 */
	private function build_squirrly_post_ids( array $post_types ): array {
		global $wpdb;

		if ( empty( $post_types ) ) {
			return [];
		}

		$qss_ids = [];

		// --- Source 1: qss table ---
		// The qss.post column stores a plain PHP serialised array (not a domain object),
		// so maybe_unserialize() always returns an array regardless of whether Squirrly
		// is active. extract_qss_post_fields() handles this via its is_array() branch.
		$qss_entries   = Constants::get_qss_post_entries();
		$candidate_ids = array_filter(
			array_column( $qss_entries, 'id' ),
			static fn( $id ) => $id > 0
		);

		if ( ! empty( $candidate_ids ) ) {
			// Batch-fetch the actual WP post_type for each candidate in a single query.
			// Squirrly's internal names ('home', 'shop') differ from real WP post types,
			// so we cannot trust the post_type stored in qss.post directly.
			$id_placeholders = implode( ',', array_fill( 0, count( $candidate_ids ), '%d' ) );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$type_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT ID, post_type FROM {$wpdb->posts}
					 WHERE ID IN ({$id_placeholders})
					   AND post_status NOT IN ('auto-draft', 'trash', 'inherit')",
					array_values( $candidate_ids )
				),
				ARRAY_A
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

			$id_to_type = array_column( $type_rows, 'post_type', 'ID' );

			foreach ( $candidate_ids as $post_id ) {
				$actual_type = $id_to_type[ $post_id ] ?? '';
				if ( $actual_type && in_array( $actual_type, $post_types, true ) ) {
					$qss_ids[] = (int) $post_id;
				}
			}
		}

		// --- Source 2: wp_postmeta _sq_title fallback ---
		$posttype_placeholders = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$meta_ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT DISTINCT p.ID
				 FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm
				         ON p.ID = pm.post_id AND pm.meta_key = %s
				 WHERE p.post_type IN ({$posttype_placeholders})
				   AND p.post_status != 'auto-draft'",
				array_merge( [ '_sq_title' ], $post_types )
			)
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$meta_ids = array_map( 'intval', $meta_ids ? $meta_ids : [] );

		// --- Merge, deduplicate, filter migrated ---
		$all_ids = array_unique( array_merge( $qss_ids, $meta_ids ) );

		if ( empty( $all_ids ) ) {
			return [];
		}

		return $this->exclude_migrated_ids( $all_ids, true );
	}

	/**
	 * Build a deduplicated, non-migrated list of term IDs that have Squirrly data.
	 *
	 * All Squirrly term SEO lives exclusively in the qss table.  The qss.post column
	 * stores a plain PHP serialised array whose `taxonomy` key holds the real WP
	 * taxonomy slug (e.g. 'post_tag', 'category', 'product_cat') — so the filter
	 * against $taxonomies works directly without any extra lookup.
	 *
	 * @param array<string> $taxonomies Valid public taxonomy slugs.
	 * @return array<int>
	 */
	private function build_squirrly_term_ids( array $taxonomies ): array {
		$all_ids = [];

		foreach ( Constants::get_qss_term_entries() as $entry ) {
			if ( in_array( $entry['taxonomy'], $taxonomies, true ) ) {
				$all_ids[] = $entry['term_id'];
			}
		}

		$all_ids = array_unique( $all_ids );

		if ( empty( $all_ids ) ) {
			return [];
		}

		return $this->exclude_migrated_ids( $all_ids, false );
	}

	/**
	 * Remove IDs that already carry the surerank_migration meta flag.
	 *
	 * @param array<int> $ids     Post or term IDs to check.
	 * @param bool       $is_post True for posts (uses wp_postmeta), false for terms (wp_termmeta).
	 * @return array<int>
	 */
	private function exclude_migrated_ids( array $ids, bool $is_post ): array {
		global $wpdb;

		if ( empty( $ids ) ) {
			return [];
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
		$params       = array_merge( [ 'surerank_migration' ], $ids );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( $is_post ) {
			$migrated = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta}
					 WHERE meta_key = %s AND post_id IN ({$placeholders})",
					$params
				)
			);
		} else {
			$migrated = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"SELECT term_id FROM {$wpdb->termmeta}
					 WHERE meta_key = %s AND term_id IN ({$placeholders})",
					$params
				)
			);
		}
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$migrated_ids = array_map( 'intval', $migrated ? $migrated : [] );

		return array_values( array_diff( $ids, $migrated_ids ) );
	}

	/**
	 * Resolve the separator character to use for placeholder replacement.
	 *
	 * Squirrly stores a separator code (e.g. 'sc-dash') per pattern entry.
	 * An explicit override value takes priority; otherwise the separator is
	 * looked up from the Constants::SEPARATOR_MAP with a '-' default.
	 *
	 * @param string|null $override Explicit separator code or character from the pattern entry.
	 * @return string Resolved separator character.
	 */
	private function get_separator( ?string $override = null ): string {
		if ( null !== $override && '' !== $override ) {
			// May be a code like 'sc-pipe' or already a resolved character.
			return Constants::SEPARATOR_MAP[ $override ] ?? $override;
		}

		return '-';
	}
}
