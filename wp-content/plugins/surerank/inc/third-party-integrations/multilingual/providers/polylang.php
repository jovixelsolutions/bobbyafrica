<?php
/**
 * Polylang Translation Provider
 *
 * Handles translation data retrieval for Polylang plugin.
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
 * Polylang Provider Class
 *
 * @since 1.6.3
 */
class Polylang implements Provider {

	/**
	 * Get translation URLs for a single post
	 *
	 * @since 1.6.3
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 * @return array<string, array{url: string, locale: string}>
	 */
	public function get_translations( int $post_id, string $post_type ): array {
		if ( ! function_exists( 'pll_get_post_translations' ) ) {
			return [];
		}

		$translation_ids = pll_get_post_translations( $post_id );
		$translations    = [];

		if ( empty( $translation_ids ) || ! is_array( $translation_ids ) ) {
			return [];
		}

		foreach ( $translation_ids as $lang_code => $translated_id ) {
			if ( ! function_exists( 'PLL' ) || ! PLL()->model ) {
				continue;
			}

			$language = PLL()->model->get_language( $lang_code );

			if ( ! $language || ! $language->active ) {
				continue;
			}

			$url = get_permalink( $translated_id );

			if ( ! $url ) {
				continue;
			}

			$translations[ $lang_code ] = [
				'url'    => $url,
				'locale' => Locale_Formatter::to_bcp47( $language->locale ),
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
		if ( ! function_exists( 'pll_default_language' ) ) {
			return '';
		}

		return pll_default_language();
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
		if ( ! function_exists( 'pll_get_post_translations' ) ) {
			return false;
		}

		$translations = pll_get_post_translations( $post_id );

		return isset( $translations[ $language ] ) && $translations[ $language ] !== $post_id;
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
		if ( ! function_exists( 'pll_get_post_translations' ) ) {
			return null;
		}

		$translations = pll_get_post_translations( $post_id );

		return isset( $translations[ $language ] ) ? (int) $translations[ $language ] : null;
	}

	/**
	 * Get the language of a post
	 *
	 * @since 1.6.3
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function get_post_language( int $post_id ): string {
		if ( ! function_exists( 'pll_get_post_language' ) ) {
			return '';
		}

		$language = pll_get_post_language( $post_id );

		return $language ? (string) $language : '';
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
		if ( ! function_exists( 'pll_get_term_translations' ) ) {
			return [];
		}

		$translation_ids = pll_get_term_translations( $term_id );
		$translations    = [];

		if ( empty( $translation_ids ) || ! is_array( $translation_ids ) ) {
			return [];
		}

		foreach ( $translation_ids as $lang_code => $translated_id ) {
			if ( ! function_exists( 'PLL' ) || ! PLL()->model ) {
				continue;
			}

			$language = PLL()->model->get_language( $lang_code );

			if ( ! $language || ! $language->active ) {
				continue;
			}

			$url = get_term_link( (int) $translated_id, $taxonomy );

			if ( is_wp_error( $url ) || ! $url ) {
				continue;
			}

			$translations[ $lang_code ] = [
				'url'    => $url,
				'locale' => Locale_Formatter::to_bcp47( $language->locale ),
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
		if ( ! function_exists( 'pll_get_term_language' ) ) {
			return '';
		}

		$language = pll_get_term_language( $term_id );

		return $language ? (string) $language : '';
	}

}
