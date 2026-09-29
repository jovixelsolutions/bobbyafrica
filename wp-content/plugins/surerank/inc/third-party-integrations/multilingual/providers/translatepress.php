<?php
/**
 * TranslatePress Translation Provider
 *
 * Handles translation data retrieval for TranslatePress plugin.
 *
 * @package surerank
 * @since 1.6.3
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual\Providers;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureRank\Inc\ThirdPartyIntegrations\Multilingual\Locale_Formatter;
use SureRank\Inc\ThirdPartyIntegrations\Multilingual\Provider;

/**
 * TranslatePress Provider Class
 *
 * @since 1.6.3
 */
class Translatepress implements Provider {

	/**
	 * URL converter component
	 *
	 * @since 1.6.3
	 * @var object|null
	 */
	private $url_converter = null;

	/**
	 * Settings
	 *
	 * @since 1.6.3
	 * @var array<string, mixed>
	 */
	private $settings = [];

	/**
	 * Constructor
	 *
	 * @since 1.6.3
	 */
	public function __construct() {
		if ( ! class_exists( 'TRP_Translate_Press' ) ) {
			return;
		}

		$trp = \TRP_Translate_Press::get_trp_instance();

		if ( $trp ) {
			$this->url_converter = $trp->get_component( 'url_converter' );
			$settings_component  = $trp->get_component( 'settings' );

			if ( $settings_component ) {
				$this->settings = $settings_component->get_settings();
			}
		}
	}

	/**
	 * Get translation URLs for a single post
	 *
	 * @since 1.6.3
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 * @return array<string, array{url: string, locale: string}>
	 */
	public function get_translations( int $post_id, string $post_type ): array {
		$url       = get_permalink( $post_id );
		$languages = $this->get_published_languages();

		if ( ! $url || empty( $languages ) || ! $this->url_converter ) {
			return [];
		}

		if ( $this->is_path_excluded( $url ) ) {
			return [];
		}

		$translations = [];

		foreach ( $languages as $lang_code ) {
			if ( ! method_exists( $this->url_converter, 'get_url_for_language' ) ) {
				continue;
			}

			$translated_url = $this->url_converter->get_url_for_language( $lang_code, $url, '' );

			if ( ! $translated_url ) {
				continue;
			}

			$translations[ $lang_code ] = [
				'url'    => $translated_url,
				'locale' => Locale_Formatter::to_bcp47( $lang_code ),
			];
		}

		return $translations;
	}

	/**
	 * Batch fetch translations for multiple posts
	 *
	 * @since 1.6.3
	 * @param array<int> $post_ids Array of post IDs.
	 * @param string     $post_type Post type.
	 * @return array<int, array<string, array{url: string, locale: string}>>
	 */
	public function get_translations_batch( array $post_ids, string $post_type ): array {
		$results = [];

		foreach ( $post_ids as $post_id ) {
			$results[ $post_id ] = $this->get_translations( $post_id, $post_type );
		}

		return $results;
	}

	/**
	 * Get default site language
	 *
	 * @since 1.6.3
	 * @return string
	 */
	public function get_default_language(): string {
		return $this->settings['default-language'] ?? '';
	}

	/**
	 * Check if translation is available for post
	 *
	 * @since 1.6.3
	 * @param int    $post_id Post ID.
	 * @param string $language Language code.
	 * @return bool
	 */
	public function is_translation_available( int $post_id, string $language ): bool {
		$languages = $this->get_published_languages();
		return in_array( $language, $languages, true );
	}

	/**
	 * Get translated post ID
	 *
	 * @since 1.6.3
	 * @param int    $post_id Post ID.
	 * @param string $language Language code.
	 * @return int|null
	 */
	public function get_translated_post_id( int $post_id, string $language ): ?int {
		return $post_id;
	}

	/**
	 * Get the language of a post
	 *
	 * @since 1.6.3
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function get_post_language( int $post_id ): string {
		// TranslatePress doesn't have language per post - it uses URL-based language switching.
		// All posts exist in the default language, translations are URL-based.
		return $this->get_default_language();
	}

	/**
	 * Get translation URLs for a single term
	 *
	 * @since 1.6.4
	 * @param int    $term_id Term ID.
	 * @param string $taxonomy Taxonomy name.
	 * @return array<string, array{url: string, locale: string}>
	 */
	public function get_term_translations( int $term_id, string $taxonomy ): array {
		$url       = get_term_link( $term_id, $taxonomy );
		$languages = $this->get_published_languages();

		if ( is_wp_error( $url ) || ! $url || empty( $languages ) || ! $this->url_converter ) {
			return [];
		}

		if ( $this->is_path_excluded( $url ) ) {
			return [];
		}

		$translations = [];

		foreach ( $languages as $lang_code ) {
			if ( ! method_exists( $this->url_converter, 'get_url_for_language' ) ) {
				continue;
			}

			$translated_url = $this->url_converter->get_url_for_language( $lang_code, $url, '' );

			if ( ! $translated_url ) {
				continue;
			}

			$translations[ $lang_code ] = [
				'url'    => $translated_url,
				'locale' => Locale_Formatter::to_bcp47( $lang_code ),
			];
		}

		return $translations;
	}

	/**
	 * Get the language of a term
	 *
	 * @since 1.6.4
	 * @param int $term_id Term ID.
	 * @return string
	 */
	public function get_term_language( int $term_id ): string {
		// TranslatePress uses URL-based language switching.
		// All terms exist in the default language.
		return $this->get_default_language();
	}

	/**
	 * Check if a URL's path is excluded from translation based on TranslatePress settings.
	 *
	 * Respects TP's "Do not translate certain paths" setting stored under
	 * trp_advanced_settings.translateable_content (option: exclude|include, paths: \n-delimited string).
	 * Supports the {{home}} token and trailing /* wildcard matcher as documented by TP.
	 *
	 * @since 1.7.2
	 * @param string $url The URL to check.
	 * @return bool True if the path should be excluded from translation entries.
	 */
	private function is_path_excluded( string $url ): bool {
		$relative_path = wp_make_link_relative( $url );

		/**
		 * Filter whether a URL should be excluded from TranslatePress sitemap translations.
		 *
		 * @since 1.7.2
		 * @param bool|null $excluded Null to use default logic, true to exclude, false to include.
		 * @param string    $url The full URL being checked.
		 * @param string    $relative_path The relative path of the URL.
		 */
		$excluded = apply_filters( 'surerank_translatepress_exclude_from_sitemap', null, $url, $relative_path );

		if ( null !== $excluded ) {
			return (bool) $excluded;
		}

		$config = $this->settings['trp_advanced_settings']['translateable_content'] ?? null;

		if ( ! is_array( $config ) || empty( $config['option'] ) || empty( $config['paths'] ) || ! is_string( $config['paths'] ) ) {
			return false;
		}

		$mode  = $config['option'];
		$paths = $this->parse_translation_paths( $config['paths'] );

		if ( empty( $paths ) ) {
			return false;
		}

		$matched = $this->url_matches_any_path( $url, $paths );

		// 'exclude' mode: excluded when matched.
		// 'include' mode (whitelist): excluded when NOT matched.
		if ( 'include' === $mode ) {
			return ! $matched;
		}

		return $matched;
	}

	/**
	 * Parse TP's newline-delimited paths string into a clean array.
	 *
	 * @since 1.7.2
	 * @param string $raw Raw paths string from TP settings.
	 * @return array<int, string>
	 */
	private function parse_translation_paths( string $raw ): array {
		$lines = explode( "\n", str_replace( "\r", '', $raw ) );
		$lines = array_map( 'trim', $lines );
		return array_values( array_filter( $lines, static fn( $line ) => '' !== $line ) );
	}

	/**
	 * Check if a URL matches any of the configured TP paths.
	 *
	 * Supports {{home}} token (front page) and trailing /* wildcard (prefix match).
	 * Mirrors TP's matching semantics from trp_dntcp_get_paths() in
	 * translatepress-multilingual/includes/advanced-settings/do-not-translate-certain-paths.php.
	 *
	 * @since 1.7.2
	 * @param string             $url   Absolute URL being checked.
	 * @param array<int, string> $paths Configured TP paths.
	 * @return bool True when the URL matches any pattern.
	 */
	private function url_matches_any_path( string $url, array $paths ): bool {
		$relative = wp_make_link_relative( $url );
		$relative = '/' . ltrim( $relative, '/' );
		$is_home  = in_array( rtrim( $relative, '/' ), [ '', '/index.php' ], true );

		foreach ( $paths as $pattern ) {
			if ( '{{home}}' === $pattern ) {
				if ( $is_home ) {
					return true;
				}
				continue;
			}

			$pattern = '/' . ltrim( wp_make_link_relative( $pattern ), '/' );

			// Trailing /* wildcard -> prefix match on parent directory.
			if ( '/*' === substr( $pattern, -2 ) ) {
				$prefix = rtrim( substr( $pattern, 0, -1 ), '/' );

				if ( '' === $prefix || 0 === strpos( rtrim( $relative, '/' ), $prefix ) ) {
					return true;
				}
				continue;
			}

			$pattern_norm  = rtrim( $pattern, '/' );
			$relative_norm = rtrim( $relative, '/' );

			if ( $relative_norm === $pattern_norm ) {
				return true;
			}

			if ( '' !== $pattern_norm && 0 === strpos( $relative_norm, $pattern_norm . '/' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get published languages
	 *
	 * @since 1.6.3
	 * @return array<string>
	 */
	private function get_published_languages(): array {
		return isset( $this->settings['publish-languages'] ) && is_array( $this->settings['publish-languages'] )
			? $this->settings['publish-languages']
			: [];
	}

}
