<?php
/**
 * Common Meta Data
 *
 * This file will handle functionality to print meta_data in frontend for different requests.
 *
 * @package surerank
 * @since 0.0.1
 */

namespace SureRank\Inc\Frontend;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SureRank\Inc\Functions\Helper;
use SureRank\Inc\Functions\Settings;
use SureRank\Inc\Functions\Validate;
use SureRank\Inc\Meta_Variables\Post;
use SureRank\Inc\Meta_Variables\Site;
use SureRank\Inc\Meta_Variables\Term;
use SureRank\Inc\ThirdPartyIntegrations\Multilingual\Translation_Manager;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Facebook SEO
 * This class will handle functionality to print meta_data in frontend for different requests.
 *
 * @since 1.0.0
 */
class Facebook {

	use Get_Instance;

	public const FACEBOOK_LOCALES = [
		'af_ZA', // Afrikaans.
		'ak_GH', // Akan.
		'am_ET', // Amharic.
		'ar_AR', // Arabic.
		'as_IN', // Assamese.
		'ay_BO', // Aymara.
		'az_AZ', // Azerbaijani.
		'be_BY', // Belarusian.
		'bg_BG', // Bulgarian.
		'bp_IN', // Bhojpuri.
		'bn_IN', // Bengali.
		'br_FR', // Breton.
		'bs_BA', // Bosnian.
		'ca_ES', // Catalan.
		'cb_IQ', // Sorani Kurdish.
		'ck_US', // Cherokee.
		'co_FR', // Corsican.
		'cs_CZ', // Czech.
		'cx_PH', // Cebuano.
		'cy_GB', // Welsh.
		'da_DK', // Danish.
		'de_DE', // German.
		'el_GR', // Greek.
		'en_GB', // English (UK).
		'en_PI', // English (Pirate).
		'en_UD', // English (Upside Down).
		'en_US', // English (US).
		'em_ZM',
		'eo_EO', // Esperanto.
		'es_ES', // Spanish (Spain).
		'es_LA', // Spanish.
		'es_MX', // Spanish (Mexico).
		'et_EE', // Estonian.
		'eu_ES', // Basque.
		'fa_IR', // Persian.
		'fb_LT', // Leet Speak.
		'ff_NG', // Fulah.
		'fi_FI', // Finnish.
		'fo_FO', // Faroese.
		'fr_CA', // French (Canada).
		'fr_FR', // French (France).
		'fy_NL', // Frisian.
		'ga_IE', // Irish.
		'gl_ES', // Galician.
		'gn_PY', // Guarani.
		'gu_IN', // Gujarati.
		'gx_GR', // Classical Greek.
		'ha_NG', // Hausa.
		'he_IL', // Hebrew.
		'hi_IN', // Hindi.
		'hr_HR', // Croatian.
		'hu_HU', // Hungarian.
		'ht_HT', // Haitian Creole.
		'hy_AM', // Armenian.
		'id_ID', // Indonesian.
		'ig_NG', // Igbo.
		'is_IS', // Icelandic.
		'it_IT', // Italian.
		'ik_US',
		'iu_CA',
		'ja_JP', // Japanese.
		'ja_KS', // Japanese (Kansai).
		'jv_ID', // Javanese.
		'ka_GE', // Georgian.
		'kk_KZ', // Kazakh.
		'km_KH', // Khmer.
		'kn_IN', // Kannada.
		'ko_KR', // Korean.
		'ks_IN', // Kashmiri.
		'ku_TR', // Kurdish (Kurmanji).
		'ky_KG', // Kyrgyz.
		'la_VA', // Latin.
		'lg_UG', // Ganda.
		'li_NL', // Limburgish.
		'ln_CD', // Lingala.
		'lo_LA', // Lao.
		'lt_LT', // Lithuanian.
		'lv_LV', // Latvian.
		'mg_MG', // Malagasy.
		'mi_NZ', // Maori.
		'mk_MK', // Macedonian.
		'ml_IN', // Malayalam.
		'mn_MN', // Mongolian.
		'mr_IN', // Marathi.
		'ms_MY', // Malay.
		'mt_MT', // Maltese.
		'my_MM', // Burmese.
		'nb_NO', // Norwegian (bokmal).
		'nd_ZW', // Ndebele.
		'ne_NP', // Nepali.
		'nl_BE', // Dutch (Belgie).
		'nl_NL', // Dutch.
		'nn_NO', // Norwegian (nynorsk).
		'nr_ZA', // Southern Ndebele.
		'ns_ZA', // Northern Sotho.
		'ny_MW', // Chewa.
		'om_ET', // Oromo.
		'or_IN', // Oriya.
		'pa_IN', // Punjabi.
		'pl_PL', // Polish.
		'ps_AF', // Pashto.
		'pt_BR', // Portuguese (Brazil).
		'pt_PT', // Portuguese (Portugal).
		'qc_GT', // Quiché.
		'qu_PE', // Quechua.
		'qr_GR',
		'qz_MM', // Burmese (Zawgyi).
		'rm_CH', // Romansh.
		'ro_RO', // Romanian.
		'ru_RU', // Russian.
		'rw_RW', // Kinyarwanda.
		'sa_IN', // Sanskrit.
		'sc_IT', // Sardinian.
		'se_NO', // Northern Sami.
		'si_LK', // Sinhala.
		'su_ID', // Sundanese.
		'sk_SK', // Slovak.
		'sl_SI', // Slovenian.
		'sn_ZW', // Shona.
		'so_SO', // Somali.
		'sq_AL', // Albanian.
		'sr_RS', // Serbian.
		'ss_SZ', // Swazi.
		'st_ZA', // Southern Sotho.
		'sv_SE', // Swedish.
		'sw_KE', // Swahili.
		'sy_SY', // Syriac.
		'sz_PL', // Silesian.
		'ta_IN', // Tamil.
		'te_IN', // Telugu.
		'tg_TJ', // Tajik.
		'th_TH', // Thai.
		'tk_TM', // Turkmen.
		'tl_PH', // Filipino.
		'tl_ST', // Klingon.
		'tn_BW', // Tswana.
		'tr_TR', // Turkish.
		'ts_ZA', // Tsonga.
		'tt_RU', // Tatar.
		'tz_MA', // Tamazight.
		'uk_UA', // Ukrainian.
		'ur_PK', // Urdu.
		'uz_UZ', // Uzbek.
		've_ZA', // Venda.
		'vi_VN', // Vietnamese.
		'wo_SN', // Wolof.
		'xh_ZA', // Xhosa.
		'yi_DE', // Yiddish.
		'yo_NG', // Yoruba.
		'zh_CN', // Simplified Chinese (China).
		'zh_HK', // Traditional Chinese (Hong Kong).
		'zh_TW', // Traditional Chinese (Taiwan).
		'zu_ZA', // Zulu.
		'zz_TR', // Zazaki.
	];

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'surerank_print_meta', [ $this, 'open_graph_tags' ], 1, 1 );
		add_action( 'surerank_print_meta', [ $this, 'facebook_tags' ], 1, 1 );
	}

	/**
	 * Catch some weird locales served out by WP that are not easily doubled up.
	 *
	 * @param string $locale Current site locale.
	 *
	 * @return string
	 */
	public static function sanitize( $locale ) {
		$fix_locales = [
			'ca' => 'ca_ES',
			'en' => 'en_US',
			'el' => 'el_GR',
			'et' => 'et_EE',
			'ja' => 'ja_JP',
			'sq' => 'sq_AL',
			'uk' => 'uk_UA',
			'vi' => 'vi_VN',
			'zh' => 'zh_CN',
			'te' => 'te_IN',
			'ur' => 'ur_PK',
			'cy' => 'cy_GB',
			'eu' => 'eu_ES',
			'th' => 'th_TH',
			'af' => 'af_ZA',
			'hy' => 'hy_AM',
			'gu' => 'gu_IN',
			'kn' => 'kn_IN',
			'mr' => 'mr_IN',
			'kk' => 'kk_KZ',
			'lv' => 'lv_LV',
			'sw' => 'sw_KE',
			'tl' => 'tl_PH',
			'ps' => 'ps_AF',
			'as' => 'as_IN',
		];

		if ( isset( $fix_locales[ $locale ] ) ) {
			$locale = $fix_locales[ $locale ];
		}

		// Convert locales like "es" to "es_ES", in case that works for the given locale (sometimes it does).
		if ( 2 === strlen( $locale ) ) {
			$locale = self::join( $locale );
		}

		return $locale;
	}

	/**
	 * Normalize a locale into og:locale shape (language_REGION).
	 *
	 * Single-pass normalizer that accepts every shape WordPress + multilingual
	 * plugins produce and returns a valid ISO-ish language_REGION string:
	 *
	 *  - Canonical xx_YY / xxx_YYY      → pass-through (bn_BD, pt_BR, ckb_IQ)
	 *  - Hyphen separator (hreflang)    → underscore (fr-CA → fr_CA)
	 *  - BCP 47 with script subtag      → drop script (zh_Hant_HK → zh_HK)
	 *  - Regional/formal subtag         → keep first valid region (de_CH_formal → de_CH)
	 *  - Non-alpha region (UN M.49)     → lang-only shape (es_419 → es_ES)
	 *  - Mixed case (EN_us)             → normalized case (en_US)
	 *  - Language-only (en, ceb)        → duplicated shape (en_EN, ceb_CEB)
	 *  - Empty / malformed              → en_US fallback
	 *
	 * The legacy Facebook whitelist check is intentionally dropped: modern
	 * Open Graph consumers (X/Twitter, LinkedIn, WhatsApp, Slack, Telegram,
	 * Meta itself) accept ISO locales broadly, and coercing through the
	 * whitelist was silently mangling real WordPress locales such as bn_BD
	 * into bn_IN.
	 *
	 * @param mixed $locale Locale string (tolerates non-string input).
	 *
	 * @return string
	 */
	public static function validate( $locale ) {
		if ( ! is_string( $locale ) ) {
			return 'en_US';
		}

		$locale = trim( str_replace( '-', '_', $locale ) );

		if ( '' === $locale ) {
			return 'en_US';
		}

		$parts = array_values(
			array_filter(
				explode( '_', $locale ),
				static fn( string $part ): bool => '' !== $part
			)
		);

		if ( empty( $parts ) ) {
			return 'en_US';
		}

		$lang = strtolower( $parts[0] );

		if ( 1 !== preg_match( '/^[a-z]{2,3}$/', $lang ) ) {
			return 'en_US';
		}

		$count = count( $parts );

		for ( $i = 1; $i < $count; $i++ ) {
			$region = strtoupper( $parts[ $i ] );

			if ( 1 === preg_match( '/^[A-Z]{2,3}$/', $region ) ) {
				return $lang . '_' . $region;
			}
		}

		return $lang . '_' . strtoupper( $lang );
	}

	/**
	 * Add meta data
	 *
	 * @param array<string, mixed> $meta_data Meta Data.
	 * @since 1.0.0
	 * @return void
	 */
	public function facebook_tags( $meta_data ) {
		if ( apply_filters( 'surerank_disable_facebook_tags', false ) ) {
			return;
		}
		$global_meta = Settings::get();
		$global_meta = $this->add_times( $global_meta );

		$facebook_meta_keys = [
			'facebook_page_url'        => 'publisher',
			'facebook_author_fallback' => 'author',
			'facebook_published_time'  => 'published_time',
			'facebook_modified_time'   => 'modified_time',
		];
		// Loop through facebook_meta_keys and add to meta_data_array if valid.
		foreach ( $facebook_meta_keys as $key => $value ) {
			if ( ! empty( $global_meta[ $key ] ) && Validate::not_empty( $global_meta[ $key ] ) ) {
				Meta_Data::get_instance()->meta_html_template( 'article:' . $value, $global_meta[ $key ], 'property' );
			}
		}
	}

	/**
	 * Prepare facebook meta data.
	 *
	 * @param array<string, mixed> $meta_data facebook meta data will be array and will contain image, title and description.
	 * @since 1.0.0
	 * @return void
	 */
	public function open_graph_tags( $meta_data ) {
		if ( apply_filters( 'surerank_disable_open_graph_tags', false ) ) {
			return;
		}

		$image = Image::get_instance();
		$image->get( $meta_data, 'facebook_image_url' );

		$this->add_common_tags( $meta_data );

		// Add product-specific Open Graph tags if applicable.
		if ( Helper::is_product() ) {
			$this->add_product_tags( $meta_data );
		}
	}

	/**
	 * Get URL.
	 * If it is not home page then get the post URL else get the site URL.
	 *
	 * @return string|false
	 * @since 1.0.0
	 */
	public function get_url() {
		if ( is_home() ) {
			return Site::get_instance()->get_site_url();
		}
		if ( is_singular() ) {
			return Post::get_instance()->get_permalink();
		}
		if ( is_category() || is_tax() || is_tag() ) {
			return Term::get_instance()->get_permalink();
		}
			return Site::get_instance()->get_site_url();
	}

	/**
	 * Output the locale, doing some conversions to make sure the proper Facebook locale is outputted.
	 *
	 * @see  http://www.facebook.com/translations/FacebookLocales.xml for the list of supported locales
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/article/
	 *
	 * @return string
	 */
	public function get_locale() {
		$locale = get_locale();

		/**
		 * Filter the locale used for og:locale meta tag.
		 *
		 * Allows multilingual plugins and developers to override the locale
		 * per page. Translation plugins like Polylang and WPML typically filter
		 * WordPress's get_locale() already, but this provides an additional
		 * hook specifically for SureRank's OG output.
		 *
		 * @since 1.7.2
		 * @param string $locale The locale string (e.g., 'en_US', 'fr_FR').
		 */
		$locale = apply_filters( 'surerank_og_locale', $locale );

		$locale = self::sanitize( $locale );
		return self::validate( $locale );
	}

	/**
	 * Add published and modified times to the global meta data.
	 *
	 * @param array<string, mixed> $global_meta Global Meta Data.
	 * @since 1.0.0
	 * @return array<string, mixed>
	 */
	private function add_times( $global_meta ) {
		if ( ! is_array( $global_meta ) ) {
			return $global_meta; // bailed.
		}

		global $post;

		if ( empty( $post ) || ! is_a( $post, 'WP_Post' ) ) {
			return $global_meta;
		}

		if ( empty( $global_meta['facebook_published_time'] ) ) {
			$global_meta['facebook_published_time'] = get_the_date( 'c', $post );
		}

		if ( empty( $global_meta['facebook_modified_time'] ) ) {
			$global_meta['facebook_modified_time'] = get_post_modified_time( 'c', true, $post );
		}

		return $global_meta;
	}

	/**
	 * Join locale to make full locale.
	 *
	 * @param string $locale Locale to join.
	 *
	 * @return string
	 */
	private static function join( $locale ) {
		return strtolower( $locale ) . '_' . strtoupper( $locale );
	}

	/**
	 * Add common tags.
	 *
	 * @param array<string, mixed> $meta_data Meta Data.
	 * @since 1.0.0
	 * @return void
	 */
	private function add_common_tags( $meta_data ) {
		$current_locale = $this->get_locale();

		$common_tags = [
			'og:url'       => $this->get_url(),
			'og:site_name' => Site::get_instance()->get_site_name(),
			'og:locale'    => $current_locale,
			'og:type'      => $this->get_type(),
		];

		foreach ( $common_tags as $key => $value ) {
			Meta_Data::get_instance()->meta_html_template( $key, $value, 'property' );
		}

		$this->add_locale_alternates( $current_locale );
		$this->add_dynamic_tags( $meta_data );
	}

	/**
	 * Output og:locale:alternate tags for other language versions.
	 *
	 * Uses the multilingual provider to discover translations and outputs
	 * og:locale:alternate for each, as defined by the Open Graph Protocol.
	 *
	 * @since 1.7.2
	 * @param string $current_locale The current page's og:locale value.
	 * @return void
	 */
	private function add_locale_alternates( $current_locale ) {
		if ( ! is_singular() && ! is_front_page() ) {
			return;
		}

		$provider = Translation_Manager::get_instance()->get_provider();

		if ( ! $provider ) {
			return;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return;
		}

		$translations = $provider->get_translations( $post_id, strval( get_post_type( $post_id ) ) );

		/**
		 * Filter the alternate locales for og:locale:alternate output.
		 *
		 * @since 1.7.2
		 * @param array<string, array{url: string, locale: string}> $translations Translation data.
		 * @param int   $post_id Current post ID.
		 */
		$translations = apply_filters( 'surerank_og_locale_alternates', $translations, $post_id );

		if ( empty( $translations ) ) {
			return;
		}

		$current_prefix = strtolower( substr( (string) $current_locale, 0, 2 ) );

		foreach ( $translations as $lang_code => $translation ) {
			if ( empty( $translation['locale'] ) ) {
				continue;
			}

			// Convert hreflang-style locale (en-US) back to OG-style (en_US).
			$alt_locale = str_replace( '-', '_', $translation['locale'] );
			$alt_locale = self::sanitize( $alt_locale );
			// Intentionally no validate() — it may coerce locales FB doesn't list
			// (e.g. bn_BD) to en_US. The OG spec does not restrict
			// og:locale:alternate to Facebook's whitelist.

			if ( ! $alt_locale || $alt_locale === $current_locale ) {
				continue;
			}

			// Skip alternates that share a language prefix with the primary locale.
			// e.g. primary bn_IN + alt bn_BD would be redundant since the validate()
			// prefix-match fallback may pick a sibling locale for the primary.
			$alt_prefix = strtolower( substr( $alt_locale, 0, 2 ) );

			if ( '' !== $current_prefix && $alt_prefix === $current_prefix ) {
				continue;
			}

			Meta_Data::get_instance()->meta_html_template( 'og:locale:alternate', $alt_locale, 'property' );
		}
	}

	/**
	 * Add dynamic tags.
	 *
	 * @param array<string, mixed> $meta_data Meta Data.
	 * @since 1.0.0
	 * @return void
	 */
	private function add_dynamic_tags( $meta_data ) {
		$facebook_meta_keys = [
			'facebook_title'        => 'title',
			'facebook_description'  => 'description',
			'facebook_image_url'    => 'image',
			'facebook_image_width'  => 'image:width',
			'facebook_image_height' => 'image:height',
		];

		foreach ( $facebook_meta_keys as $key => $value ) {
			if ( ! empty( $meta_data[ $key ] ) && Validate::not_empty( $meta_data[ $key ] ) ) {
				Meta_Data::get_instance()->meta_html_template( 'og:' . $value, $meta_data[ $key ], 'property' );
			}
		}
	}

	/**
	 * Add product tags.
	 *
	 * @param array<string, mixed> $meta_data Meta Data.
	 * @since 1.0.0
	 * @return void
	 */
	private function add_product_tags( $meta_data ) {
		$product_tags = [
			'product:price:amount'   => $meta_data['product_price'] ?? null,
			'product:price:currency' => $meta_data['product_currency'] ?? null,
			'product:availability'   => $meta_data['product_availability'] ?? null,
		];

		foreach ( $product_tags as $key => $value ) {
			if ( Validate::not_empty( $value ) ) {
				Meta_Data::get_instance()->meta_html_template( $key, $value, 'property' );
			}
		}
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	private function get_type() {
		if ( is_front_page() || is_home() ) {
			return 'website';
		}

		if ( is_author() ) {
			return 'profile';
		}

		return Helper::is_product() ? 'product' : 'article';
	}

}
