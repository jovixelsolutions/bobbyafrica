<?php
/**
 * Locale-format normalisation helper.
 *
 * Provides a single source of truth for converting locale strings between
 * BCP 47 (hyphen-separated, e.g. "en-US") and OpenGraph (underscore-separated,
 * e.g. "en_US") representations. Before this helper existed, each of the three
 * translation providers carried its own identical `format_locale()` method and
 * consumers (schema, Open Graph) converted inline at the emission site.
 *
 * Standards referenced:
 *   - BCP 47 (RFC 5646) — hyphen-separated language tag. Used by:
 *       HTML `lang` attribute, XML `xml:lang`, sitemap `xhtml:link hreflang`,
 *       schema.org `inLanguage`.
 *   - OpenGraph Protocol — underscore-separated locale. Used by:
 *       `og:locale`, `og:locale:alternate`.
 *
 * Callers that need additional processing (Facebook's 2-letter padding
 * and supported-locale whitelist, schema-specific filters) should apply
 * those steps AFTER calling this helper.
 *
 * @package surerank
 * @since   1.7.2
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Locale Formatter
 *
 * @since 1.7.2
 */
final class Locale_Formatter {

	/**
	 * Private constructor — this is a static-only utility.
	 */
	private function __construct() {
	}

	/**
	 * Normalise a locale string to BCP 47 (hyphen-separated).
	 *
	 * Input tolerances:
	 *   'en_US'      -> 'en-US'
	 *   'en-US'      -> 'en-US'
	 *   'en'         -> 'en'
	 *   ''           -> ''
	 *   ' en_US '    -> 'en-US' (leading/trailing whitespace trimmed)
	 *
	 * Used for: sitemap `hreflang` attributes, schema.org `inLanguage`,
	 * HTML `lang` attribute, XML `xml:lang`.
	 *
	 * @since 1.7.2
	 * @param string $locale Locale in any of the supported input forms.
	 * @return string BCP 47 locale string (hyphen-separated).
	 */
	public static function to_bcp47( string $locale ): string {
		return str_replace( '_', '-', trim( $locale ) );
	}

	/**
	 * Normalise a locale string to OpenGraph form (underscore-separated).
	 *
	 * Input tolerances:
	 *   'en-US'      -> 'en_US'
	 *   'en_US'      -> 'en_US'
	 *   'en'         -> 'en'
	 *   ''           -> ''
	 *   ' en-US '    -> 'en_US' (leading/trailing whitespace trimmed)
	 *
	 * Used for: `og:locale`, `og:locale:alternate`.
	 *
	 * Consumers that need Facebook's supported-locale whitelist or
	 * 2-letter-to-region padding (e.g. `en` -> `en_US`) should apply
	 * those steps AFTER this conversion.
	 *
	 * @since 1.7.2
	 * @param string $locale Locale in any of the supported input forms.
	 * @return string OpenGraph locale string (underscore-separated).
	 */
	public static function to_opengraph( string $locale ): string {
		return str_replace( '-', '_', trim( $locale ) );
	}
}
