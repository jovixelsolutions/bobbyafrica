<?php
/**
 * Translation Manager
 *
 * Orchestrates translation provider detection and manages multilingual sitemap data.
 *
 * @package surerank
 * @since 1.6.3
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureRank\Inc\Traits\Get_Instance;

/**
 * Translation Manager Class
 *
 * @since 1.6.3
 */
class Translation_Manager {

	use Get_Instance;

	/**
	 * Active translation provider
	 *
	 * @since 1.6.3
	 * @var Provider|null
	 */
	private $provider = null;

	/**
	 * Translation cache for batch processing
	 *
	 * @since 1.6.3
	 * @var array<int, array<string, mixed>>
	 */
	private static $translation_cache = [];

	/**
	 * Term translation cache for batch processing
	 *
	 * @since 1.6.4
	 * @var array<int, array<string, mixed>>
	 */
	private static $term_translation_cache = [];

	/**
	 * Constructor
	 *
	 * @since 1.6.3
	 */
	public function __construct() {
		$this->provider = $this->detect_active_provider();

		if ( ! $this->provider ) {
			return;
		}

		$this->init_hooks();
	}

	/**
	 * Get the active translation provider.
	 *
	 * Exposes the singleton's detected provider so callers (e.g. frontend
	 * Open Graph output) can reuse it without re-running detection or
	 * instantiating a second provider object.
	 *
	 * @since 1.7.2
	 * @return Provider|null
	 */
	public function get_provider(): ?Provider {
		return $this->provider;
	}

	/**
	 * Add translation data to post data during batch processing
	 *
	 * @since 1.6.3
	 * @param array<string, mixed> $post_data Post data.
	 * @param \WP_Post             $post Post object.
	 * @return array<string, mixed>|array<int, array<string, mixed>>
	 */
	public function add_translation_data( $post_data, $post ) {
		if ( ! $this->provider || ! $post instanceof \WP_Post ) {
			return $post_data;
		}

		// Skip posts that are not in the default language to prevent duplicates,
		// unless the post has no default-language counterpart (secondary-language-only content).
		$post_language = $this->provider->get_post_language( $post->ID );
		$default_lang  = $this->provider->get_default_language();

		if ( $post_language && $default_lang && $post_language !== $default_lang ) {
			// Check if a default-language counterpart exists.
			$default_post_id = $this->provider->get_translated_post_id( $post->ID, $default_lang );

			if ( $default_post_id && $default_post_id !== $post->ID ) {
				// Default-language post exists and will carry the translations.
				return [];
			}

			// No default-language counterpart -- treat this as a standalone entry.
		}

		if ( ! isset( self::$translation_cache[ $post->ID ] ) ) {
			self::$translation_cache[ $post->ID ] = $this->provider->get_translations( $post->ID, $post->post_type );
		}

		$translations = self::$translation_cache[ $post->ID ];

		if ( empty( $translations ) ) {
			return $post_data;
		}

		$post_data['translations']     = $translations;
		$post_data['default_language'] = $default_lang;

		if ( count( $translations ) <= 1 ) {
			return $post_data;
		}

		$entries = [ $post_data ];

		foreach ( $translations as $lang_code => $translation ) {
			if ( $lang_code === $default_lang ) {
				continue;
			}

			if ( $translation['url'] === $post_data['link'] ) {
				continue;
			}

			$translated_post_data = [
				'id'               => $post->ID,
				'title'            => $this->get_translated_title( $post->ID, $lang_code ),
				'link'             => $translation['url'],
				'post_type'        => $post->post_type,
				'updated'          => $post_data['updated'],
				'images'           => $post_data['images'] ?? 0,
				'images_data'      => $post_data['images_data'] ?? [],
				'translations'     => $translations,
				'default_language' => $default_lang,
			];

			// Copy additional fields if they exist (e.g., news data).
			if ( isset( $post_data['is_news'] ) ) {
				$translated_post_data['is_news'] = $post_data['is_news'];
			}

			if ( isset( $post_data['news_data'] ) ) {
				$translated_post_data['news_data'] = $post_data['news_data'];
			}

			$entries[] = $translated_post_data;
		}

		return $entries;
	}

	/**
	 * Invalidate post cache when translation changes
	 *
	 * @since 1.6.3
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function invalidate_post_cache( $post_id ) {
		if ( isset( self::$translation_cache[ $post_id ] ) ) {
			unset( self::$translation_cache[ $post_id ] );
		}

		do_action( 'surerank_schedule_sitemap_generation' );
	}

	/**
	 * Add translation data to term data during batch processing
	 *
	 * @since 1.6.4
	 * @param array<string, mixed> $term_data Term data.
	 * @param \WP_Term             $term Term object.
	 * @return array<string, mixed>|array<int, array<string, mixed>>
	 */
	public function add_term_translation_data( $term_data, $term ) {
		if ( ! $this->provider || ! $term instanceof \WP_Term ) {
			return $term_data;
		}

		// Skip terms that are not in the default language to prevent duplicates,
		// unless the term has no default-language counterpart (secondary-language-only content).
		$term_language = $this->provider->get_term_language( $term->term_id );
		$default_lang  = $this->provider->get_default_language();

		if ( $term_language && $default_lang && $term_language !== $default_lang ) {
			// Check if a default-language counterpart exists via term translations.
			$term_translations = $this->provider->get_term_translations( $term->term_id, $term->taxonomy );

			if ( isset( $term_translations[ $default_lang ] ) ) {
				// Default-language term exists and will carry the translations.
				return [];
			}

			// No default-language counterpart -- treat this as a standalone entry.
		}

		if ( ! isset( self::$term_translation_cache[ $term->term_id ] ) ) {
			self::$term_translation_cache[ $term->term_id ] = $this->provider->get_term_translations( $term->term_id, $term->taxonomy );
		}

		$translations = self::$term_translation_cache[ $term->term_id ];

		if ( empty( $translations ) ) {
			return $term_data;
		}

		$term_data['translations']     = $translations;
		$term_data['default_language'] = $default_lang;

		if ( count( $translations ) <= 1 ) {
			return $term_data;
		}

		$entries = [ $term_data ];

		foreach ( $translations as $lang_code => $translation ) {
			if ( $lang_code === $default_lang ) {
				continue;
			}

			if ( $translation['url'] === $term_data['link'] ) {
				continue;
			}

			$translated_term_data = [
				'id'               => $term->term_id,
				'name'             => $this->get_translated_term_name( $term->term_id, $term->taxonomy ),
				'slug'             => $term->slug,
				'link'             => $translation['url'],
				'taxonomy'         => $term->taxonomy,
				'description'      => $term->description,
				'count'            => $term->count,
				'updated'          => $term_data['updated'],
				'translations'     => $translations,
				'default_language' => $default_lang,
			];

			$entries[] = $translated_term_data;
		}

		return $entries;
	}

	/**
	 * Invalidate term cache when translation changes
	 *
	 * @since 1.6.4
	 * @param int $term_id Term ID.
	 * @return void
	 */
	public function invalidate_term_cache( $term_id ) {
		if ( isset( self::$term_translation_cache[ $term_id ] ) ) {
			unset( self::$term_translation_cache[ $term_id ] );
		}

		do_action( 'surerank_schedule_sitemap_generation' );
	}

	/**
	 * Initialize hooks
	 *
	 * @since 1.6.3
	 * @return void
	 */
	private function init_hooks() {
		add_filter( 'surerank_sitemap_sync_posts_post_data', [ $this, 'add_translation_data' ], 10, 2 );
		add_filter( 'surerank_sitemap_sync_terms_term_data', [ $this, 'add_term_translation_data' ], 10, 2 );

		$this->init_plugin_specific_hooks();
	}

	/**
	 * Initialize plugin-specific invalidation hooks
	 *
	 * @since 1.6.3
	 * @return void
	 */
	private function init_plugin_specific_hooks() {
		add_action( 'wpml_after_save_post', [ $this, 'invalidate_post_cache' ] );
		add_action( 'pll_save_post', [ $this, 'invalidate_post_cache' ] );
		add_action( 'trp_update_translation', [ $this, 'invalidate_post_cache' ] );

		add_action( 'wpml_after_save_term', [ $this, 'invalidate_term_cache' ] );
		add_action( 'pll_save_term', [ $this, 'invalidate_term_cache' ] );
	}

	/**
	 * Detect active translation provider
	 *
	 * @since 1.6.3
	 * @return Provider|null
	 */
	private function detect_active_provider() {
		if ( defined( 'ICL_SITEPRESS_VERSION' ) && class_exists( 'SitePress' ) ) {
			return new Providers\Wpml();
		}

		if ( function_exists( 'pll_default_language' ) && function_exists( 'PLL' ) ) {
			return new Providers\Polylang();
		}

		if ( class_exists( 'TRP_Translate_Press' ) ) {
			return new Providers\Translatepress();
		}

		return null;
	}

	/**
	 * Get translated title
	 *
	 * @since 1.6.3
	 * @param int    $post_id Post ID.
	 * @param string $lang_code Language code.
	 * @return string
	 */
	private function get_translated_title( $post_id, $lang_code ) {
		if ( ! $this->provider ) {
			return get_the_title( $post_id );
		}

		$translated_id = $this->provider->get_translated_post_id( $post_id, $lang_code );

		if ( $translated_id && $translated_id !== $post_id ) {
			return get_the_title( $translated_id );
		}

		return get_the_title( $post_id );
	}

	/**
	 * Get translated term name
	 *
	 * @since 1.6.3
	 * @param int    $term_id Term ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return string
	 */
	private function get_translated_term_name( $term_id, $taxonomy ) {
		$term = get_term( $term_id, $taxonomy );
		return $term instanceof \WP_Term ? $term->name : '';
	}
}
