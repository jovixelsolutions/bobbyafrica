<?php
/**
 * Phone field (intl-tel-input) — locator for the vendored library.
 *
 * Single locator for the vendored intl-tel-input library files and version details.
 * Provides a unified API for the admin UIs and front-end phone field components to
 * resolve library URLs, versions, and localized interface strings.
 *
 * Note: `intlTelInput.esm.js` is an ES module and should be dynamically imported
 * (import()) rather than registered as a classic script.
 *
 * This class registers and enqueues nothing directly; consumers retrieve URLs via
 * assets() and handle enqueuing and script loading according to their needs.
 *
 * @package Click_To_Chat
 * @since 4.42
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Phone_Field' ) ) {

	/**
	 * Locates the vendored intl-tel-input files and reports its version.
	 */
	class HT_CTC_Phone_Field {

		/**
		 * Vendored library name.
		 *
		 * @var string
		 */
		const LIBRARY = 'intl-tel-input';

		/**
		 * Version of the vendored library.
		 *
		 * Matches the copy in intl-tel-input/. The library embeds its own version,
		 * so it can always be confirmed with:
		 * `grep 'version:' intl-tel-input/js/intlTelInput.esm.js`
		 *
		 * Set by the vendoring tooling in the plugin's development repository,
		 * which reads it from that same source — never typed by hand.
		 *
		 * @var string
		 */
		const VERSION = '29.1.2';

		/**
		 * Contract version for consumers of assets().
		 *
		 * Bump ONLY on a breaking change to the returned array (a key removed
		 * or its meaning changed). Adding a key is not breaking.
		 *
		 * @var int
		 */
		const API = 1;



		/**
		 * Describe the vendored library: version plus URLs for every file.
		 *
		 * Minified variants are used unless HT_CTC_DEBUG_MODE is defined, so a
		 * consumer never has to repeat that decision.
		 *
		 * @since 4.42
		 *
		 * @return array {
		 *     @type string $library  Library name — 'intl-tel-input'.
		 *     @type string $version  Vendored library version, e.g. '29.1.2'.
		 *     @type int    $api      Contract version of this array shape.
		 *     @type bool   $min      Whether the URLs point at minified files.
		 *     @type string $js       ES-module entry. import() it; do NOT enqueue.
		 *     @type string $utils    Lazy utils module (library `loadUtils` option).
		 *     @type string $css      Stylesheet URL.
		 *     @type string $img      Flag-sprite directory URL (referenced by the CSS).
		 *     @type string $dir_url  Base URL of the vendored directory.
		 * }
		 *
		 * Every URL is a BARE path — no `?ver=`. The consumer that puts a file on
		 * the page adds the cache-buster: enqueue it and WP stamps it; import() it
		 * or build a raw <link> and you must append `?ver=` . HT_CTC_VERSION
		 * yourself (these files change on plugin releases — a bare URL leaves the
		 * browser serving the previous release's copy).
		 */
		public static function assets() {

			$min     = defined( 'HT_CTC_DEBUG_MODE' ) ? '' : '.min';
			$dir_url = plugins_url( 'new/tools/phone-field/intl-tel-input/', HT_CTC_PLUGIN_FILE );

			$assets = array(
				'library' => self::LIBRARY,
				'version' => self::VERSION,
				'api'     => self::API,
				'min'     => ( '' !== $min ),

				'js'      => $dir_url . "js/intlTelInput.esm{$min}.js",

				// utils.js is shipped pre-compiled by upstream; no .min variant needed.
				'utils'   => $dir_url . 'js/utils.js',
				'css'     => $dir_url . "css/intlTelInput{$min}.css",
				'img'     => $dir_url . 'img/',

				'dir_url' => $dir_url,
			);

			/**
			 * Filter the vendored phone-field asset URLs.
			 *
			 * Lets a site point the library elsewhere (a CDN, a shared copy)
			 * without touching plugin files. Keys are the contract — a filter
			 * that drops one breaks its consumers.
			 *
			 * @since 4.42
			 *
			 * @param array $assets Asset descriptor, see the return doc above.
			 */
			return apply_filters( 'ht_ctc_fh_phone_field_assets', $assets );
		}


		/**
		 * List the language codes for which we ship pre-compiled interface strings.
		 *
		 * SCOPE — this list governs the field's own chrome (search box, ARIA
		 * labels) and NOTHING ELSE. Country names come from the browser's
		 * Intl.DisplayNames, which knows hundreds of languages this list has never
		 * heard of, so gating country names on it would throw away translations we
		 * already get for free. See locale() for the split.
		 *
		 * A flat literal on purpose. Reading the codes back out of
		 * locale-strings.php would keep them in step automatically, but it would
		 * also pull that whole table into memory just to draw a <select>.
		 *
		 * RULE: every code here must be a key in locale-strings.php. Nothing
		 * enforces that, so it is on whoever regenerates that file. A stale entry
		 * costs an English search box beside translated country names, not an error.
		 *
		 * Callers supply their own labels; `Intl.DisplayNames( code,
		 * { type: 'language' } )` in the browser beats shipping a table of names.
		 *
		 * @since 4.43
		 *
		 * @return string[] Lowercase codes, e.g. 'mr', 'pt', 'zh-hk'.
		 */
		public static function locales() {

			$locales = array(
				'ar',
				'bg',
				'bn',
				'ca',
				'cs',
				'da',
				'de',
				'el',
				'en',
				'es',
				'et',
				'fa',
				'fi',
				'fil',
				'fr',
				'he',
				'hi',
				'hr',
				'hu',
				'id',
				'it',
				'ja',
				'kn',
				'ko',
				'lt',
				'lv',
				'mr',
				'ms',
				'nl',
				'no',
				'pl',
				'pt',
				'ro',
				'ru',
				'sk',
				'sl',
				'sr',
				'sv',
				'sw',
				'ta',
				'te',
				'th',
				'tr',
				'uk',
				'ur',
				'vi',
				'zh',
				'zh-hk',
			);

			/**
			 * Filter the offered phone-field languages.
			 *
			 * @since 4.43
			 *
			 * @param string[] $locales Codes offered in the settings dropdowns.
			 */
			return apply_filters( 'ht_ctc_fh_phone_field_locales', $locales );
		}

		/**
		 * Resolve a WordPress locale into the two things the phone field needs.
		 *
		 * THE ONE PLACE that turns a WP locale into anything. Every consumer reads
		 * this; nobody re-derives. The field previously carried five near-copies of
		 * this logic (two admin trees x two call sites, plus PRO), each subtly
		 * different, and every locale bug we have had came from one of them:
		 *
		 *   - `str_replace` of a SINGLE '_' left 'de-CH_informal', which Intl
		 *     rejects. intl-tel-input catches that and falls through to an empty
		 *     name for EVERY country — a dropdown of flags and dial codes.
		 *   - `substr( $locale, 0, 2 )` truncated three-letter codes onto unrelated
		 *     real languages: tah (Tahitian) -> ta (Tamil), fil (Filipino) -> fi
		 *     (Finnish), roh (Romansh) -> ro (Romanian). Confidently wrong, and
		 *     nothing errored.
		 *
		 * The two return values answer two DIFFERENT questions, and keeping them
		 * apart is the point of this function:
		 *
		 *   'tag'     — for the BROWSER. Drives countryNameLocale, DisplayNames and
		 *               PluralRules. Unbounded: any language CLDR knows is fair
		 *               game, so this is never checked against locales().
		 *   'strings' — for US. Selects our own pre-compiled interface strings, so
		 *               it is necessarily one of locales(), or '' when we ship none
		 *               (English included — the library's own defaults are English).
		 *
		 * Matching is on WHOLE SUBTAGS, most specific first. That is the entire
		 * defence against the truncation bug: 'tah' is one subtag and simply misses,
		 * where cutting two characters off it hits Tamil.
		 *
		 * Variants and anything past the region are dropped from 'tag' rather than
		 * validated. WordPress appends 'formal', 'informal', 'ao90'; some are legal
		 * BCP-47 variants and some are not, and none of them change a country name.
		 * Not emitting them means never having to decide.
		 *
		 * @since 4.43
		 *
		 * @param string|null $wp_locale WordPress locale. Defaults to get_user_locale().
		 * @return array {
		 *     @type string $tag     BCP-47 language[-script][-region], e.g. 'de-ch'. Never empty.
		 *     @type string $strings A code from locales(), or '' for English/unknown.
		 * }
		 */
		public static function locale( $wp_locale = null ) {

			if ( null === $wp_locale ) {
				$wp_locale = function_exists( 'get_user_locale' ) ? get_user_locale() : 'en_US';
			}

			$lang = strtolower( str_replace( '_', '-', (string) $wp_locale ) );

			// Must start with a 2-3 letter language subtag.
			if ( ! preg_match( '/^[a-z]{2,3}(?:-[a-z0-9]+)*$/', $lang ) ) {
				return array(
					'tag'     => 'en',
					'strings' => '',
				);
			}

			/*
			 * 1. Build BCP-47 tag for browser (language[-script][-region]).
			 * Extract language (2-3 letters), optional script (4 letters), optional region (2 letters or 3 digits).
			 *
			 * Examples of how the regex parses inputs (based on debug logs):
			 * - 'en'             => $m = ["en", "en"]
			 *                       (Language: 'en')
			 * - 'en-us'          => $m = ["en-us", "en", "", "us"]
			 *                       (Language: 'en', Script: none, Region: 'us')
			 * - 'zh-hant-tw'     => $m = ["zh-hant-tw", "zh", "hant", "tw"]
			 *                       (Language: 'zh', Script: 'hant', Region: 'tw')
			 * - 'de-ch-informal' => $m = ["de-ch", "de", "", "ch"]
			 *                       (Language: 'de', Script: none, Region: 'ch'. 'informal' is ignored)
			 *
			 * The (?=-|$) after each optional group is load-bearing: it makes the
			 * group match a WHOLE subtag. Without it, [a-z]{4} takes the first four
			 * letters of a longer one — art_xemoji became 'art-xemo' and
			 * ca_valencia became 'ca-vale'. That is a character-count truncation of
			 * a subtag, i.e. the same defect as substr( $locale, 0, 2 ) wearing a
			 * regex, and just as quiet: 'art-xemo' is structurally valid BCP-47, so
			 * Intl accepts it and silently resolves to en-US.
			 */
			$tag = 'en';
			if ( preg_match( '/^([a-z]{2,3})(?:-([a-z]{4})(?=-|$))?(?:-([a-z]{2}|[0-9]{3})(?=-|$))?/', $lang, $m ) ) {
				$tag = $m[1];
				if ( ! empty( $m[2] ) ) {
					$tag .= '-' . $m[2];
				}
				if ( ! empty( $m[3] ) ) {
					$tag .= '-' . $m[3];
				}
			}

			/*
			 * 2. Find matching interface strings code from locales() by trimming whole subtags at hyphens.
			 * E.g., 'de-ch-informal' -> 'de-ch' -> 'de'.
			 */
			$strings = '';
			$offered = self::locales();
			$curr    = $lang;

			while ( '' !== $curr ) {
				if ( in_array( $curr, $offered, true ) ) {
					$strings = $curr;
					break;
				}
				$pos = strrpos( $curr, '-' );
				if ( false === $pos ) {
					break;
				}
				$curr = substr( $curr, 0, $pos );
			}

			// English needs nothing — the library's built-in strings are English.
			if ( 'en' === $strings ) {
				$strings = '';
			}

			return array(
				'tag'     => $tag,
				'strings' => $strings,
			);
		}

		/**
		 * Retrieve localized interface strings for the library's `uiTranslations`.
		 *
		 * Reads pre-compiled translation maps from `locale-strings.php` based on language tag.
		 * Returns an array of translated strings or an empty array for English/unknown locales.
		 *
		 * @since 4.42
		 *
		 * @param string $language Language code: 'mr', 'pt_BR', 'zh-HK', 'en_US'.
		 * @return array Strings for the library's `uiTranslations`, or empty.
		 */
		public static function locale_strings( $language ) {

			static $all = null;

			// locale() owns every WP-locale-to-code decision; this only looks up.
			$resolved = self::locale( $language );
			$code     = $resolved['strings'];

			if ( '' === $code ) {
				return array();
			}

			if ( null === $all ) {
				$file = HT_CTC_PLUGIN_DIR . 'new/tools/phone-field/locale-strings.php';
				$all  = file_exists( $file ) ? include $file : array();

				if ( ! is_array( $all ) ) {
					$all = array();
				}
			}

			$raw = isset( $all[ $code ] ) ? $all[ $code ] : null;

			if ( empty( $raw ) || ! is_array( $raw ) ) {
				return array();
			}

			// If already associative (legacy/un-indexed structure), return directly.
			if ( isset( $raw['selectedCountryAriaLabel'] ) ) {
				return $raw;
			}

			$keys = array(
				'selectedCountryAriaLabel',
				'noCountrySelected',
				'countryListAriaLabel',
				'searchPlaceholder',
				'clearSearchAriaLabel',
				'searchEmptyState',
			);

			$result = array();
			foreach ( $keys as $i => $key ) {
				if ( isset( $raw[ $i ] ) ) {
					$result[ $key ] = $raw[ $i ];
				}
			}

			if ( isset( $raw[6] ) && is_array( $raw[6] ) ) {
				$aria = $raw[6];
				// Re-populate exact[0] from searchEmptyState if exact[0] was omitted.
				if ( ! isset( $aria['exact'][0] ) && isset( $result['searchEmptyState'] ) ) {
					if ( ! isset( $aria['exact'] ) ) {
						$aria['exact'] = array();
					}
					$aria['exact'][0] = $result['searchEmptyState'];
					ksort( $aria['exact'] );
				}
				$result['searchSummaryAria'] = $aria;
			}

			return $result;
		}
	}
}

if ( ! defined( 'HT_CTC_PHONE_FIELD_VERSION' ) ) {
	// Version of the vendored library itself — for "is it new enough?" checks.
	define( 'HT_CTC_PHONE_FIELD_VERSION', HT_CTC_Phone_Field::VERSION );
}

if ( ! defined( 'HT_CTC_PHONE_FIELD_API' ) ) {
	// Presence of this constant IS the feature gate for PRO. See the class doc.
	define( 'HT_CTC_PHONE_FIELD_API', HT_CTC_Phone_Field::API );
}
