<?php
/**
 * Hreflang Generator
 *
 * Adds hreflang annotations to XML sitemap URLs.
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
 * Hreflang Generator Class
 *
 * @since 1.6.3
 */
class Hreflang_Generator {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.6.3
	 */
	public function __construct() {
		add_filter( 'surerank_sitemap_url_element', [ $this, 'add_hreflang_links' ], 10, 3 );
	}

	/**
	 * Add hreflang links to URL element
	 *
	 * @since 1.6.3
	 * @param \DOMElement          $url_element URL element.
	 * @param array<string, mixed> $url URL data from cache.
	 * @param \DOMDocument         $dom DOM document.
	 * @return \DOMElement
	 */
	public function add_hreflang_links( $url_element, $url, $dom ) {
		if ( empty( $url['translations'] ) || ! is_array( $url['translations'] ) ) {
			return $url_element;
		}

		foreach ( $url['translations'] as $lang_code => $translation ) {
			if ( empty( $translation['url'] ) || empty( $translation['locale'] ) ) {
				continue;
			}

			/**
			 * Filter hreflang URL before output in sitemap.
			 *
			 * Use this to normalize URLs (trailing slashes, protocol) to ensure
			 * consistency between SureRank's sitemap hreflang and the translation
			 * plugin's HTML head hreflang output.
			 *
			 * @since 1.7.2
			 * @param string $url The hreflang URL.
			 * @param string $lang_code The language code.
			 * @param string $locale The formatted locale string.
			 */
			$hreflang_url = apply_filters( 'surerank_hreflang_url', $translation['url'], $lang_code, $translation['locale'] );

			$link = $dom->createElement( 'xhtml:link' );
			$link->setAttribute( 'rel', 'alternate' );
			$link->setAttribute( 'hreflang', esc_attr( $translation['locale'] ) );
			$link->setAttribute( 'href', esc_url( $hreflang_url ) );

			$url_element->appendChild( $link );
		}

		if ( ! empty( $url['default_language'] ) && isset( $url['translations'][ $url['default_language'] ] ) ) {
			$default_translation = $url['translations'][ $url['default_language'] ];

			/** This filter is documented above. */
			$default_url = apply_filters( 'surerank_hreflang_url', $default_translation['url'], $url['default_language'], 'x-default' );

			$link = $dom->createElement( 'xhtml:link' );
			$link->setAttribute( 'rel', 'alternate' );
			$link->setAttribute( 'hreflang', 'x-default' );
			$link->setAttribute( 'href', esc_url( $default_url ) );

			$url_element->appendChild( $link );
		}

		return $url_element;
	}
}
