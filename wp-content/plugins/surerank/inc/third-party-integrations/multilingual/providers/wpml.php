<?php
/**
 * WPML Translation Provider
 *
 * Handles translation data retrieval for WPML plugin.
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
 * WPML Provider Class
 *
 * @since 1.6.3
 */
class Wpml implements Provider {

	/**
	 * Get translation URLs for a single post
	 *
	 * @since 1.6.3
	 * @param int    $post_id Post ID.
	 * @param string $post_type Post type.
	 * @return array<string, array{url: string, locale: string}>
	 */
	public function get_translations( int $post_id, string $post_type ): array {
		$languages    = $this->get_active_languages();
		$translations = [];

		foreach ( $languages as $lang_code => $language_data ) {
			$translated_id = apply_filters( 'wpml_object_id', $post_id, $post_type, true, $lang_code );

			if ( ! $translated_id ) {
				continue;
			}

			$url = get_permalink( $translated_id );

			if ( ! $url ) {
				continue;
			}

			$translations[ $lang_code ] = [
				'url'    => apply_filters( 'wpml_permalink', $url, $lang_code, true ),
				'locale' => Locale_Formatter::to_bcp47( $language_data['default_locale'] ?? $lang_code ),
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
		return apply_filters( 'wpml_default_language', '' );
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
		$translated_id = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), true, $language );
		return ! empty( $translated_id ) && $translated_id !== $post_id;
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
		$translated_id = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), true, $language );
		return $translated_id ? (int) $translated_id : null;
	}

	/**
	 * Get the language of a post
	 *
	 * @since 1.6.3
	 * @param int $post_id Post ID.
	 * @return string
	 */
	public function get_post_language( int $post_id ): string {
		$language_info = apply_filters( 'wpml_post_language_details', null, $post_id );

		if ( ! $language_info || ! isset( $language_info['language_code'] ) ) {
			return '';
		}

		return (string) $language_info['language_code'];
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
		$languages    = $this->get_active_languages();
		$translations = [];

		foreach ( $languages as $lang_code => $language_data ) {
			$translated_id = apply_filters( 'wpml_object_id', $term_id, $taxonomy, true, $lang_code );

			if ( ! $translated_id ) {
				continue;
			}

			$url = get_term_link( (int) $translated_id, $taxonomy );

			if ( is_wp_error( $url ) || ! $url ) {
				continue;
			}

			$translations[ $lang_code ] = [
				'url'    => apply_filters( 'wpml_permalink', $url, $lang_code, true ),
				'locale' => Locale_Formatter::to_bcp47( $language_data['default_locale'] ?? $lang_code ),
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
		$term = get_term( $term_id );

		if ( ! $term instanceof \WP_Term ) {
			return '';
		}

		$element_type  = apply_filters( 'wpml_element_type', $term->taxonomy );
		$language_info = apply_filters(
			'wpml_element_language_details',
			null,
			[
				'element_id'   => $term_id,
				'element_type' => $element_type,
			]
		);

		if ( ! $language_info || ! isset( $language_info->language_code ) ) {
			return '';
		}

		return (string) $language_info->language_code;
	}

	/**
	 * Get active languages
	 *
	 * @since 1.6.3
	 * @return array<string, array<string, mixed>>
	 */
	private function get_active_languages(): array {
		return apply_filters( 'wpml_active_languages', [], [ 'skip_missing' => true ] );
	}

}
