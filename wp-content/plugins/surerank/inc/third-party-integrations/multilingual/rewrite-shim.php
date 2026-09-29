<?php
/**
 * Rewrite-rule shim for multilingual plugins.
 *
 * Wraps SureRank's call to flush_rewrite_rules() with a detach/reattach
 * of Polylang and WPML term-query filters. Mirrors the pattern used by
 * SEOPress in its advanced rewriting options
 * (wp-seopress/inc/functions/options-advanced-rewriting.php) so that
 * custom post type and taxonomy registrations settle on their native
 * per-language slugs instead of being coerced to the default language
 * when a flush is triggered from admin context.
 *
 * Closes #2372.
 *
 * @package surerank
 * @since 1.7.2
 */

namespace SureRank\Inc\ThirdPartyIntegrations\Multilingual;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rewrite Shim
 *
 * @since 1.7.2
 */
class Rewrite_Shim {

	/**
	 * Filter names that translation plugins hook to inject language context
	 * into term/post/link/rewrite resolution. Detaching these for the duration
	 * of a flush prevents per-language CPT/taxonomy registrations from being
	 * collapsed into the default-language slug.
	 *
	 * Term-side hooks: `terms_clauses`, `get_terms_args`, `category_link`,
	 * `term_link`, `pre_term_slug`. Polylang (`PLL_CRUD_Terms`, `PLL_Model`,
	 * `PLL_Filters_Links`) and WPML (`SitePress`, `WPML_Tax_Permalink_Filters`)
	 * both register here. `pre_term_slug` is where Polylang's
	 * `PLL_CRUD_Terms::set_pre_term_slug` injects the default-language term
	 * name during slug generation.
	 *
	 * Post-side hooks: `post_link` (posts), `page_link` (pages),
	 * `post_type_link` (custom post types). These do not fire during
	 * `flush_rewrite_rules()` itself (that invokes `generate_rewrite_rules`
	 * and `rewrite_rules_array` only), but third-party code hooked into
	 * `generate_rewrite_rules` may call `get_permalink()`, which triggers
	 * these filters. Detaching them keeps that indirect path language-neutral
	 * during the flush window.
	 *
	 * `rewrite_rules_array` is intentionally NOT in this list. Polylang's
	 * `PLL_Links_Directory::rewrite_rules` *injects* per-language prefixes
	 * via that filter; detaching it would strip language prefixes from the
	 * flushed rule set — the opposite of the intent here. This matches the
	 * SEOPress pattern this shim is modelled on.
	 *
	 * @since 1.7.2
	 */
	private const TARGET_FILTERS = [
		'terms_clauses',
		'get_terms_args',
		'category_link',
		'term_link',
		'pre_term_slug',
		'post_link',
		'page_link',
		'post_type_link',
	];

	/**
	 * Execute a flush_rewrite_rules() call with multilingual filters
	 * temporarily detached.
	 *
	 * Uses try/finally so filters are reattached even if the flush throws.
	 * On non-multilingual sites this is equivalent to flush_rewrite_rules().
	 *
	 * @since 1.7.2
	 * @return void
	 */
	public static function safe_flush(): void {
		// Fast path for single-language sites: skip the $wp_filter scan entirely when neither Polylang nor WPML is loaded.
		if ( ! self::is_multilingual_plugin_active() ) {
			flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
			return;
		}

		$detached = self::detach_multilingual_filters();

		try {
			flush_rewrite_rules(); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules
		} finally {
			self::reattach_filters( $detached );
		}
	}

	/**
	 * Detect whether Polylang or WPML is loaded on this request.
	 *
	 * @since 1.7.2
	 * @return bool
	 */
	private static function is_multilingual_plugin_active(): bool {
		return defined( 'POLYLANG_VERSION' )
			|| defined( 'POLYLANG_BASENAME' )
			|| defined( 'ICL_SITEPRESS_VERSION' )
			|| defined( 'WPML_PLUGIN_BASENAME' );
	}

	/**
	 * Detach Polylang and WPML callbacks on the target filters.
	 *
	 * Returns a list of callbacks that were removed so they can be
	 * reattached by {@see reattach_filters()}.
	 *
	 * @since 1.7.2
	 * @return array<int, array{hook: string, callback: mixed, priority: int, accepted_args: int}>
	 */
	private static function detach_multilingual_filters(): array {
		global $wp_filter;

		$detached = [];

		if ( ! is_array( $wp_filter ) && ! ( $wp_filter instanceof \ArrayAccess ) ) {
			return $detached;
		}

		foreach ( self::TARGET_FILTERS as $hook ) {
			if ( ! isset( $wp_filter[ $hook ] ) ) {
				continue;
			}

			$hook_obj = $wp_filter[ $hook ];

			if ( ! isset( $hook_obj->callbacks ) || ! is_array( $hook_obj->callbacks ) ) {
				continue;
			}

			foreach ( $hook_obj->callbacks as $priority => $callbacks ) {
				if ( ! is_array( $callbacks ) ) {
					continue;
				}

				foreach ( $callbacks as $cb ) {
					if ( ! isset( $cb['function'] ) || ! self::is_multilingual_callback( $cb['function'] ) ) {
						continue;
					}

					$accepted_args = isset( $cb['accepted_args'] ) ? (int) $cb['accepted_args'] : 1;

					remove_filter( $hook, $cb['function'], (int) $priority );

					$detached[] = [
						'hook'          => $hook,
						'callback'      => $cb['function'],
						'priority'      => (int) $priority,
						'accepted_args' => $accepted_args,
					];
				}
			}
		}

		return $detached;
	}

	/**
	 * Reattach callbacks that were removed by detach_multilingual_filters().
	 *
	 * @since 1.7.2
	 * @param array<int, array{hook: string, callback: mixed, priority: int, accepted_args: int}> $detached Detached callbacks.
	 * @return void
	 */
	private static function reattach_filters( array $detached ): void {
		foreach ( $detached as $entry ) {
			add_filter( $entry['hook'], $entry['callback'], $entry['priority'], $entry['accepted_args'] );
		}
	}

	/**
	 * Determine whether the given callback belongs to Polylang or WPML.
	 *
	 * @since 1.7.2
	 * @param mixed $callback Callback value from $wp_filter.
	 * @return bool
	 */
	private static function is_multilingual_callback( $callback ): bool {
		// Array callbacks: [ $object_or_class, 'method' ].
		if ( is_array( $callback ) && isset( $callback[0] ) ) {
			$owner = $callback[0];

			$class_name = is_object( $owner ) ? get_class( $owner ) : (string) $owner;

			return self::class_looks_multilingual( $class_name );
		}

		// Object-as-callable (closure or __invoke). For closures we inspect
		// the bound instance; for invokables we check the class name directly.
		if ( is_object( $callback ) ) {
			if ( $callback instanceof \Closure ) {
				try {
					$ref   = new \ReflectionFunction( $callback );
					$bound = $ref->getClosureThis();
					if ( $bound ) {
						return self::class_looks_multilingual( get_class( $bound ) );
					}
					// Static closures: fall back to the declaring scope.
					$scope = $ref->getClosureScopeClass();
					if ( $scope ) {
						return self::class_looks_multilingual( $scope->getName() );
					}
				} catch ( \ReflectionException $e ) {
					// Unable to introspect — treat as unknown.
					return false;
				}
				return false;
			}
			return self::class_looks_multilingual( get_class( $callback ) );
		}

		// String callback: either 'global_function' or 'Class::method' form.
		if ( is_string( $callback ) ) {
			if ( strpos( $callback, '::' ) !== false ) {
				$class_part = strstr( $callback, '::', true );
				return is_string( $class_part )
					&& self::class_looks_multilingual( $class_part );
			}

			// Match Polylang/WPML global helpers by their canonical prefixes,
			// preserving original case so we don't collide with unrelated
			// snake_case functions that happen to contain these substrings.
			if ( strpos( $callback, 'pll_' ) === 0 ) {
				return true;
			}
			if ( strpos( $callback, 'icl_' ) === 0 ) {
				return true;
			}
			if ( strpos( $callback, 'wpml_' ) === 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Classify a class name as Polylang, WPML, or neither.
	 *
	 * Polylang ships classes with both the `PLL_` prefix (legacy) and the
	 * `Polylang\` namespace (modern). WPML ships classes with the `WPML_`
	 * prefix (legacy, most current filters), the `WPML\` namespace (modern
	 * refactors), and the unprefixed `SitePress` root class. TranslatePress
	 * does not hook any of the filters this shim targets, so its class
	 * prefix is intentionally omitted.
	 *
	 * Trailing backslash handling: PHP's `get_class()` returns fully-qualified
	 * class names without a leading slash (e.g. `WPML\Something\Foo`), so the
	 * `strpos === 0` checks match both legacy underscore-prefixed and modern
	 * namespace-prefixed forms.
	 *
	 * Prefix matches require a delimiter (`_` or `\`) or an exact match so
	 * unrelated third-party classes like `PolylangHelperExtension` or
	 * `SitePressReviewWidget` aren't swept up.
	 *
	 * @since 1.7.2
	 * @param string $class_name Fully-qualified class name (from get_class()).
	 * @return bool True when the class belongs to Polylang or WPML.
	 */
	private static function class_looks_multilingual( string $class_name ): bool {
		if ( '' === $class_name ) {
			return false;
		}

		// Polylang: legacy `PLL_*` prefix or modern `Polylang\` namespace.
		// A bare `Polylang` prefix match is intentionally omitted — it matched
		// unrelated third-party classes such as `PolylangHelperExtension`.
		if ( strpos( $class_name, 'PLL_' ) === 0 ) {
			return true;
		}
		if ( strpos( $class_name, 'Polylang\\' ) === 0 ) {
			return true;
		}

		// WPML: legacy `WPML_*` prefix, modern `WPML\` namespace,
		// and the unprefixed `SitePress` root class.
		if ( strpos( $class_name, 'WPML_' ) === 0 ) {
			return true;
		}
		if ( strpos( $class_name, 'WPML\\' ) === 0 ) {
			return true;
		}
		// SitePress match requires a boundary so unrelated classes like
		// `SitePressReviewWidget` are not swept up.
		if ( 'SitePress' === $class_name
			|| strpos( $class_name, 'SitePress_' ) === 0
			|| strpos( $class_name, 'SitePress\\' ) === 0
		) {
			return true;
		}

		return false;
	}
}
