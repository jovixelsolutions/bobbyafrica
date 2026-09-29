<?php
/**
 * Multilingual Integration Loader
 *
 * Initializes multilingual sitemap support for translation plugins.
 *
 * @package surerank
 * @since 1.6.3
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use SureRank\Inc\Functions\Cron;
use SureRank\Inc\Sitemap\Checksum;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Multilingual Init Class
 *
 * @since 1.6.3
 */
class Init {

	use Get_Instance;

	/**
	 * Constructor
	 *
	 * @since 1.6.3
	 */
	public function __construct() {
		add_action( 'activated_plugin', [ $this, 'on_plugin_activation' ], 10, 1 );
		add_action( 'deactivated_plugin', [ $this, 'on_plugin_deactivation' ], 10, 1 );

		if ( ! $this->has_translation_plugin() ) {
			return;
		}

		$this->init_classes();
	}

	/**
	 * Handle plugin activation
	 *
	 * @since 1.6.3
	 * @param string $plugin Plugin basename.
	 * @return void
	 */
	public function on_plugin_activation( $plugin ) {
		if ( $this->is_translation_plugin( $plugin ) ) {
			$this->trigger_sitemap_regeneration();
		}
	}

	/**
	 * Handle plugin deactivation
	 *
	 * @since 1.6.3
	 * @param string $plugin Plugin basename.
	 * @return void
	 */
	public function on_plugin_deactivation( $plugin ) {
		if ( $this->is_translation_plugin( $plugin ) ) {
			$this->trigger_sitemap_regeneration();
		}
	}

	/**
	 * Register SureRank settings as translatable options with Polylang.
	 *
	 * This allows Polylang's String Translation screen to display and
	 * translate SureRank settings per language.
	 *
	 * @since 1.6.6
	 * @return void
	 */
	public function register_polylang_string_translations() {
		if ( ! class_exists( 'PLL_Translate_Option' ) ) {
			return;
		}

		new \PLL_Translate_Option(
			SURERANK_SETTINGS,
			[ '*' => 1 ],
			[ 'context' => 'SureRank' ]
		);
	}

	/**
	 * Ensure sitemap WP_Query / get_terms calls return content in all languages.
	 *
	 * Polylang and WPML both filter admin-context queries by active language.
	 * We add provider-specific query args so the sitemap sees every language.
	 *
	 * - Polylang: 'lang' => '' disables language filtering for this query.
	 * - WPML: 'wpml_language' => 'all' is WPML's documented query arg for
	 *   cross-language retrieval. Avoids the nuclear 'suppress_filters' flag
	 *   which would also disable unrelated plugins' posts_where/posts_join
	 *   and cache plugin hooks that run on the same query.
	 *
	 * @since 1.7.2
	 * @param array<string, mixed> $args          WP_Query / get_terms args.
	 * @param string               $post_type_or_taxonomy Post type or taxonomy being queried.
	 * @return array<string, mixed>
	 */
	public function ensure_all_languages_in_query( $args, $post_type_or_taxonomy ) {
		unset( $post_type_or_taxonomy );

		if ( function_exists( 'pll_default_language' ) ) {
			$args['lang'] = '';
		}

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$args['wpml_language'] = 'all';
		}

		return $args;
	}

	/**
	 * Check if translation plugin is active
	 *
	 * @since 1.6.3
	 * @return bool
	 */
	private function has_translation_plugin(): bool {
		return defined( 'ICL_SITEPRESS_VERSION' ) ||
			function_exists( 'pll_default_language' ) ||
			class_exists( 'TRP_Translate_Press' );
	}

	/**
	 * Initialize classes
	 *
	 * @since 1.6.3
	 * @return void
	 */
	private function init_classes() {
		Translation_Manager::get_instance();
		Hreflang_Generator::get_instance();
		Canonical_Adapter::get_instance();

		if ( function_exists( 'pll_default_language' ) ) {
			$this->register_polylang_string_translations();
		}

		// Ensure sitemap queries return posts and terms in all languages.
		add_filter( 'surerank_sitemap_posts_cache_args', [ $this, 'ensure_all_languages_in_query' ], 10, 2 );
		add_filter( 'surerank_sitemap_taxonomies_cache_args', [ $this, 'ensure_all_languages_in_query' ], 10, 2 );
	}

	/**
	 * Check if plugin is a translation plugin
	 *
	 * @since 1.6.3
	 * @param string $plugin Plugin basename.
	 * @return bool
	 */
	private function is_translation_plugin( $plugin ) {
		$translation_plugins = [
			'sitepress-multilingual-cms/sitepress.php',
			'polylang/polylang.php',
			'polylang-pro/polylang.php',
			'translatepress-multilingual/index.php',
		];

		return in_array( $plugin, $translation_plugins, true );
	}

	/**
	 * Trigger sitemap regeneration
	 *
	 * @since 1.6.3
	 * @return void
	 */
	private function trigger_sitemap_regeneration() {

		if ( class_exists( 'SureRank\\Inc\\Sitemap\\Checksum' ) ) {
			Checksum::get_instance()->update_checksum();
		}

		if ( class_exists( 'SureRank\\Inc\\Functions\\Cron' ) ) {
			wp_schedule_single_event( time() + 10, Cron::SITEMAP_CRON_EVENT, [ 'yes' ] );
		}
	}
}
