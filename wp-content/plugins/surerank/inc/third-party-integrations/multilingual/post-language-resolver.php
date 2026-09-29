<?php
/**
 * Post Language Resolver
 *
 * Resolves the language of a post or term for AI/external calls,
 * normalised to BCP 47 format with fallback to site locale.
 *
 * @package surerank
 * @since 1.7.2
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Language Resolver
 *
 * @since 1.7.2
 */
final class Post_Language_Resolver {

	/**
	 * Private constructor — static utility class.
	 */
	private function __construct() {
	}

	/**
	 * Return the BCP 47 language code for a post.
	 *
	 * Falls back to site locale when no multilingual provider is active
	 * or when the post has no language assigned.
	 *
	 * @since 1.7.2
	 * @param int $post_id Post ID (use 0 for site-level fallback).
	 * @return string BCP 47 language tag, e.g. 'es', 'de', 'en-US'.
	 */
	public static function for_id( int $post_id ): string {
		$provider = Translation_Manager::get_instance()->get_provider();

		if ( $provider && $post_id > 0 ) {
			$lang = $provider->get_post_language( $post_id );
			if ( $lang ) {
				return Locale_Formatter::to_bcp47( $lang );
			}
		}

		return Locale_Formatter::to_bcp47( get_locale() );
	}

	/**
	 * Return the BCP 47 language code for a taxonomy term.
	 *
	 * Falls back to site locale when no multilingual provider is active
	 * or when the term has no language assigned.
	 *
	 * @since 1.7.2
	 * @param int $term_id Term ID (use 0 for site-level fallback).
	 * @return string BCP 47 language tag, e.g. 'es', 'de', 'en-US'.
	 */
	public static function for_term_id( int $term_id ): string {
		$provider = Translation_Manager::get_instance()->get_provider();

		if ( $provider && $term_id > 0 ) {
			$lang = $provider->get_term_language( $term_id );
			if ( $lang ) {
				return Locale_Formatter::to_bcp47( $lang );
			}
		}

		return Locale_Formatter::to_bcp47( get_locale() );
	}
}
