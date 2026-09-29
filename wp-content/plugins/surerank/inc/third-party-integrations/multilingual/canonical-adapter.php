<?php
/**
 * Multilingual Canonical Adapter
 *
 * Hooks `surerank_canonical_url` to correct domain-mapped canonical URLs
 * when WPML "different domain per language" mode is active.
 *
 * @package surerank
 * @since 1.7.2
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureRank\Inc\Traits\Get_Instance;

/**
 * Canonical Adapter
 *
 * @since 1.7.2
 */
class Canonical_Adapter {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.7.2
	 */
	public function __construct() {
		add_filter( 'surerank_canonical_url', [ $this, 'fix_wpml_domain_canonical' ], 10, 2 );
	}

	/**
	 * Correct canonical URL for WPML "different domain per language" mode.
	 *
	 * In WPML domain-per-language mode (language_negotiation_type === 1),
	 * `home_url()` and `get_permalink()` return the base domain URL rather than
	 * the language-mapped domain. WPML provides `wpml_home_url` and
	 * `wpml_permalink` filters that return the correctly mapped URL.
	 *
	 * @since 1.7.2
	 * @param string                    $url       Current canonical URL.
	 * @param array<string, mixed>|null $meta_data Post meta data for this request.
	 * @return string
	 */
	public function fix_wpml_domain_canonical( string $url, $meta_data ): string {
		if ( ! $this->is_wpml_domain_mode() ) {
			return $url;
		}

		// When a manual canonical is set by the user, honour it as-is.
		if ( is_array( $meta_data ) && ! empty( $meta_data['canonical_url'] ) ) {
			return $url;
		}

		if ( is_home() || is_front_page() ) {
			return (string) apply_filters( 'wpml_home_url', $url );
		}

		if ( is_singular() ) {
			$post_id = get_queried_object_id();
			if ( $post_id ) {
				return (string) apply_filters( 'wpml_permalink', get_permalink( $post_id ), null );
			}
		}

		return $url;
	}

	/**
	 * Detect WPML "different domain per language" mode.
	 *
	 * WPML language_negotiation_type values: 1 = different domain, 2 = subdirectory, 3 = subdomain.
	 *
	 * @since 1.7.2
	 * @return bool
	 */
	private function is_wpml_domain_mode(): bool {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
			return false;
		}

		return 1 === (int) apply_filters( 'wpml_setting', null, 'language_negotiation_type' );
	}
}
