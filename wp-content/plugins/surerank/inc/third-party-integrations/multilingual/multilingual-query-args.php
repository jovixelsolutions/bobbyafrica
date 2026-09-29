<?php
/**
 * Multilingual Query Args
 *
 * Builds WP_Query arg supplements for multilingual-aware post queries.
 * Polylang and WPML both honour the 'lang' WP_Query parameter:
 *   - empty string  → include all languages (for corpus/index builds)
 *   - language code → restrict to that language (for per-post suggestions)
 *
 * Returns an empty array when no translation provider is active,
 * preserving identical behaviour on single-language sites.
 *
 * @package surerank
 * @since 1.7.2
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multilingual Query Args
 *
 * @since 1.7.2
 */
final class Multilingual_Query_Args {

	/**
	 * Private constructor — static utility class.
	 */
	private function __construct() {
	}

	/**
	 * Return query args that include posts from ALL languages.
	 *
	 * Use for background corpus/index builds that must cover every language.
	 *
	 * @since 1.7.2
	 * @return array<string, string>
	 */
	public static function all_languages(): array {
		if ( ! Translation_Manager::get_instance()->get_provider() ) {
			return [];
		}

		return [ 'lang' => '' ];
	}

	/**
	 * Return query args scoped to the language of a specific post.
	 *
	 * Use when suggestions must be restricted to the same language as the post.
	 *
	 * @since 1.7.2
	 * @param int $post_id Post whose language should constrain the query.
	 * @return array<string, string>
	 */
	public static function for_post( int $post_id ): array {
		$provider = Translation_Manager::get_instance()->get_provider();

		if ( ! $provider || $post_id <= 0 ) {
			return [];
		}

		$lang = $provider->get_post_language( $post_id );

		return $lang ? [ 'lang' => $lang ] : [];
	}
}
