<?php
/**
 * Frequently Bought Together module loader.
 *
 * Mirrors the layout of modules/order-bump/class-cartflows-pro-order-bump.php — defines
 * a DIR constant, eagerly requires the module's classes, hosts the static data-helper
 * API used by every read/write in the feature, and kicks off via get_instance().
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * FBT module bootstrap + data helper API.
 *
 * The static methods on this class (get_settings, update_settings, is_enabled,
 * get_product_ids) are the single sanitisation gate for the entire FBT feature.
 * Callers must never touch get_post_meta / update_post_meta for the FBT blob
 * directly — go through these helpers so the shape stays consistent.
 *
 * @since 3.1.4
 */
class Cartflows_Fbt {

	/**
	 * Post meta key that stores the entire FBT settings blob for a product.
	 *
	 * @var string
	 */
	const META_KEY = '_cartflows_fbt';

	/**
	 * Queryable analytics mirror. Present only while the widget is configured;
	 * its value is that product's Auto Suggest companion count.
	 *
	 * @var string
	 */
	const AI_COUNT_META_KEY = '_cartflows_fbt_ai_count';

	/**
	 * Free-tier default cap on accepted products. Effectively unlimited — sanity ceiling only.
	 *
	 * @var int
	 */
	const FREE_MAX_PRODUCTS = 999;

	/**
	 * Returns the effective cap for the current tier via the cartflows_fbt_max_products filter.
	 *
	 * @param int $product_id Product ID for context; may be 0.
	 * @return int
	 */
	public static function max_products( $product_id = 0 ) {
		$value = (int) apply_filters( 'cartflows_fbt_max_products', self::FREE_MAX_PRODUCTS, (int) $product_id );
		return max( 1, $value );
	}

	/**
	 * Whitelist for the 'enabled' field.
	 *
	 * @var array<int, string>
	 */
	const ENABLED_VALUES = array( 'yes', 'no' );

	/**
	 * Free-tier whitelist for the 'source' field. Pro appends 'ai' + 'mixed' via cartflows_fbt_source_values.
	 *
	 * @var array<int, string>
	 */
	const SOURCE_VALUES = array( 'manual' );

	/**
	 * Whitelist for the 'selection' field (multi-select vs radio-style).
	 *
	 * @var array<int, string>
	 */
	const SELECTION_VALUES = array( 'multiple', 'single' );

	/**
	 * Whitelist for the 'position' field — 10 WC single-product page positions.
	 *
	 * @var array<int, string>
	 */
	const POSITION_VALUES = array(
		'after_atc_button',
		'below_title',
		'below_price',
		'below_summary',
	);

	/**
	 * Returns the effective 'source' whitelist for the current tier via the cartflows_fbt_source_values filter.
	 *
	 * @return array<int, string>
	 */
	public static function source_values() {
		$values = apply_filters( 'cartflows_fbt_source_values', self::SOURCE_VALUES );
		return is_array( $values ) ? array_values( array_unique( array_map( 'strval', $values ) ) ) : self::SOURCE_VALUES;
	}

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance;

	/**
	 * Initiator.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — defines constants + loads the module's classes.
	 */
	public function __construct() {
		$this->define_constants();
		$this->load_files();
	}

	/**
	 * Defines the module's path + URL constants.
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'CARTFLOWS_FBT_DIR', CARTFLOWS_DIR . 'modules/frequently-bought-together/' );
		define( 'CARTFLOWS_FBT_URL', CARTFLOWS_URL . 'modules/frequently-bought-together/' );
	}

	/**
	 * Requires the sub-classes that make up the module.
	 *
	 * @return void
	 */
	public function load_files() {

		// Product-editor tab, panel and save hook.
		require_once CARTFLOWS_FBT_DIR . 'classes/class-cartflows-fbt-product-meta.php';

		// Single-product widget renderer and frontend add-to-cart AJAX.
		require_once CARTFLOWS_FBT_DIR . 'classes/class-cartflows-fbt-frontend.php';

		// BSF Analytics stat builders for the FBT payload.
		require_once CARTFLOWS_FBT_DIR . 'classes/class-cartflows-fbt-analytics.php';
	}

	/**
	 * Returns the empty-but-valid FBT settings shape.
	 *
	 * Callers can safely destructure the return value — every key is present.
	 *
	 * @return array{enabled: string, source: string, product_ids: array<int, int>, add_separately: string, selection: string, position: string, custom_qty: string, product_qty: array<int, int>, ai_product_ids: array<int, int>}
	 */
	public static function get_defaults() {
		return array(
			'enabled'        => 'no',
			'source'         => '',
			'product_ids'    => array(),
			'add_separately' => 'yes',
			'selection'      => 'multiple',
			'position'       => 'after_atc_button',
			'custom_qty'     => 'no',
			'product_qty'    => array(),
			'ai_product_ids' => array(),
		);
	}

	/**
	 * Reads the FBT settings blob for a product with defaults merged in.
	 *
	 * @param int $product_id WC product post ID.
	 * @return array{enabled: string, source: string, product_ids: array<int, int>, add_separately: string, selection: string, position: string, custom_qty: string, product_qty: array<int, int>, ai_product_ids: array<int, int>}
	 */
	public static function get_settings( $product_id ) {

		// Guard against zero / negative IDs so a bad caller can't trigger a get_post_meta( 0 ) scan.
		if ( $product_id <= 0 ) {
			return self::get_defaults();
		}

		// get_post_meta returns '' for an unset key; only an array is a real value.
		$stored = get_post_meta( $product_id, self::META_KEY, true );
		if ( ! is_array( $stored ) ) {
			return self::get_defaults();
		}

		// array_merge preserves keys the caller stored while filling any gaps with defaults.
		$merged = array_merge( self::get_defaults(), $stored );

		// Coerce sub-fields to their expected types before returning.
		$merged['enabled']        = in_array( $merged['enabled'], self::ENABLED_VALUES, true ) ? $merged['enabled'] : 'no';
		$merged['source']         = in_array( $merged['source'], self::source_values(), true ) ? $merged['source'] : '';
		$merged['product_ids']    = is_array( $merged['product_ids'] ) ? array_values( array_map( 'absint', $merged['product_ids'] ) ) : array();
		$merged['add_separately'] = in_array( $merged['add_separately'], self::ENABLED_VALUES, true ) ? $merged['add_separately'] : 'yes';
		$merged['selection']      = in_array( $merged['selection'], self::SELECTION_VALUES, true ) ? $merged['selection'] : 'multiple';
		$merged['position']       = in_array( $merged['position'], self::POSITION_VALUES, true ) ? $merged['position'] : 'after_atc_button';
		$merged['custom_qty']     = in_array( $merged['custom_qty'], self::ENABLED_VALUES, true ) ? $merged['custom_qty'] : 'no';
		$merged['product_qty']    = self::coerce_qty_map( $merged['product_qty'] );
		$merged['ai_product_ids'] = is_array( $merged['ai_product_ids'] ) ? array_values( array_unique( array_filter( array_map( 'absint', $merged['ai_product_ids'] ) ) ) ) : array();

		return $merged;
	}

	/**
	 * Normalises a product_id => qty map: absint keys, max(1, absint) values, no zeros.
	 *
	 * @param mixed $raw Whatever was stored in product_qty.
	 * @return array<int, int>
	 */
	private static function coerce_qty_map( $raw ) {

		if ( ! is_array( $raw ) ) {
			return array();
		}

		$out = array();
		foreach ( $raw as $id => $qty ) {
			$id  = absint( $id );
			$qty = absint( $qty );
			if ( $id > 0 && $qty > 0 ) {
				$out[ $id ] = $qty;
			}
		}
		return $out;
	}

	/**
	 * Merges a partial update into the stored blob and writes it back.
	 *
	 * All sanitisation happens here — callers pass raw values. Missing keys in the
	 * partial are left untouched on the stored side.
	 *
	 * @param int                  $product_id WC product post ID.
	 * @param array<string, mixed> $partial    Any subset of the settings keys.
	 * @return bool True when the write persists or when the write is a no-op equal to the current value.
	 */
	public static function update_settings( $product_id, $partial ) {

		// Bad IDs never write.
		if ( $product_id <= 0 ) {
			return false;
		}

		// Read the current shape as the baseline for the merge.
		$current = self::get_settings( $product_id );

		// Sanitise every field the caller supplied — anything else stays as-is.
		$clean = self::sanitize_partial( $partial );

		// Merge over the current blob; array_merge is fine because every key is top-level scalar/array.
		$next = array_merge( $current, $clean );

		// Keep the analytics mirror in step with the blob it summarises.
		if ( 'yes' === $next['enabled'] && ! empty( $next['product_ids'] ) ) {
			update_post_meta( $product_id, self::AI_COUNT_META_KEY, count( (array) $next['ai_product_ids'] ) );
		} else {
			delete_post_meta( $product_id, self::AI_COUNT_META_KEY );
		}

		// update_post_meta returns true on change, false when value is unchanged or write fails; treat unchanged as success.
		$result = update_post_meta( $product_id, self::META_KEY, $next );
		if ( false === $result ) {
			return $next === $current;
		}
		return true;
	}

	/**
	 * Convenience — hot-path check on the frontend enqueue guard.
	 *
	 * @param int $product_id WC product post ID.
	 * @return bool
	 */
	public static function is_enabled( $product_id ) {
		$settings = self::get_settings( $product_id );
		return 'yes' === $settings['enabled'];
	}

	/**
	 * Convenience — the ordered accepted product ID list.
	 *
	 * @param int $product_id WC product post ID.
	 * @return array<int, int>
	 */
	public static function get_product_ids( $product_id ) {
		$settings = self::get_settings( $product_id );
		return $settings['product_ids'];
	}

	/**
	 * Sanitises every recognised key in an incoming partial payload.
	 *
	 * Any key not in the whitelist is dropped. Silent drop is intentional — callers
	 * shouldn't be pushing arbitrary keys through this helper.
	 *
	 * @param array<string, mixed> $partial Raw partial payload.
	 * @return array<string, mixed>
	 */
	private static function sanitize_partial( array $partial ) {

		$clean = array();

		// Enabled — whitelist to yes/no.
		if ( array_key_exists( 'enabled', $partial ) ) {
			$clean['enabled'] = in_array( $partial['enabled'], self::ENABLED_VALUES, true ) ? (string) $partial['enabled'] : 'no';
		}

		// Source — whitelist via filterable tier list; Pro appends 'ai' + 'mixed'.
		if ( array_key_exists( 'source', $partial ) ) {
			$clean['source'] = in_array( $partial['source'], self::source_values(), true ) ? (string) $partial['source'] : '';
		}

		// Product IDs — absint + dedupe + cap via the filterable tier limit.
		if ( array_key_exists( 'product_ids', $partial ) && is_array( $partial['product_ids'] ) ) {
			$ids                  = array_values( array_unique( array_filter( array_map( 'absint', $partial['product_ids'] ) ) ) );
			$clean['product_ids'] = array_slice( $ids, 0, self::max_products() );
		}

		// Add-separately toggle — yes/no.
		if ( array_key_exists( 'add_separately', $partial ) ) {
			$clean['add_separately'] = in_array( $partial['add_separately'], self::ENABLED_VALUES, true ) ? (string) $partial['add_separately'] : 'yes';
		}

		// Selection mode — multiple/single.
		if ( array_key_exists( 'selection', $partial ) ) {
			$clean['selection'] = in_array( $partial['selection'], self::SELECTION_VALUES, true ) ? (string) $partial['selection'] : 'multiple';
		}

		// Position — one of the 10 WC hook positions.
		if ( array_key_exists( 'position', $partial ) ) {
			$clean['position'] = in_array( $partial['position'], self::POSITION_VALUES, true ) ? (string) $partial['position'] : 'after_atc_button';
		}

		// Custom-qty toggle — yes/no.
		if ( array_key_exists( 'custom_qty', $partial ) ) {
			$clean['custom_qty'] = in_array( $partial['custom_qty'], self::ENABLED_VALUES, true ) ? (string) $partial['custom_qty'] : 'no';
		}

		// Per-product qty map — sparse, absint keys + values.
		if ( array_key_exists( 'product_qty', $partial ) ) {
			$clean['product_qty'] = self::coerce_qty_map( $partial['product_qty'] );
		}

		// AI-sourced companion IDs — provenance only, same cap as product_ids.
		if ( array_key_exists( 'ai_product_ids', $partial ) && is_array( $partial['ai_product_ids'] ) ) {
			$ai_ids                  = array_values( array_unique( array_filter( array_map( 'absint', $partial['ai_product_ids'] ) ) ) );
			$clean['ai_product_ids'] = array_slice( $ai_ids, 0, self::max_products() );
		}

		return $clean;
	}
}

/**
 * Kick off the module immediately on file include.
 */
Cartflows_Fbt::get_instance();
