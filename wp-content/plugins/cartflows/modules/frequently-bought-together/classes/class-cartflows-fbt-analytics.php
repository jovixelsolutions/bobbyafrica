<?php
/**
 * Frequently Bought Together — BSF Analytics stat builders.
 *
 * Owns every FBT query that feeds the analytics payload so Cartflows_Analytics
 * stays a thin assembler. All values are aggregate — no product IDs are sent.
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Fbt_Analytics.
 *
 * @since 3.1.4
 */
class Cartflows_Fbt_Analytics {

	/**
	 * Numeric pointers for the analytics payload.
	 *
	 * @since 3.1.4
	 * @return array{fbt_products_configured: string, fbt_ai_products: string}
	 */
	public static function get_numeric_stats() {

		global $wpdb;

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Runs once per analytics send, behind the daily transient.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) AS configured, COALESCE( SUM( pm.meta_value ), 0 ) AS ai_total
				FROM $wpdb->postmeta pm
				INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
				WHERE pm.meta_key = %s
					AND p.post_type = 'product'
					AND p.post_status = 'publish'",
				Cartflows_Fbt::AI_COUNT_META_KEY
			),
			ARRAY_A
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

		return array(
			'fbt_products_configured' => strval( absint( $row['configured'] ?? 0 ) ),
			'fbt_ai_products'         => strval( absint( $row['ai_total'] ?? 0 ) ),
		);
	}

	/**
	 * Attributed order count and revenue for one day.
	 *
	 * Only companion lines carry _wcf_fbt_parent_id, so the main product a shopper
	 * was already buying is excluded — this measures what the widget actually sold.
	 *
	 * @since 3.1.4
	 * @param string $date Date in Y-m-d format.
	 * @return array{fbt_orders: int, fbt_revenue: string}
	 */
	public static function get_daily_stats( $date ) {

		global $wpdb;

		$empty = array(
			'fbt_orders'  => 0,
			'fbt_revenue' => '0.00',
		);

		if ( ! class_exists( 'WooCommerce' ) ) {
			return $empty;
		}

		// HPOS relocates the order record only; order items keep their own tables.
		// Same helper wcf()->utils resolves to, so this shares one source of truth with Cartflows_Analytics.
		$hpos = Cartflows_Utils::get_instance()->is_hpos_enabled();

		$order_table    = $hpos ? $wpdb->prefix . 'wc_orders' : $wpdb->posts;
		$order_table_id = $hpos ? 'id' : 'ID';
		$date_key       = $hpos ? 'date_created_gmt' : 'post_date';
		$status_key     = $hpos ? 'status' : 'post_status';
		$type_key       = $hpos ? 'type' : 'post_type';
		$items_table    = $wpdb->prefix . 'woocommerce_order_items';
		$itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';

		//phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching -- Runs twice per analytics send, behind the daily transient.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT oi.order_id) AS order_count,
						COALESCE( SUM( line.meta_value + 0 ), 0 ) AS revenue
				FROM $items_table oi
				INNER JOIN $itemmeta_table fbt
					ON oi.order_item_id = fbt.order_item_id AND fbt.meta_key = '_wcf_fbt_parent_id'
				INNER JOIN $itemmeta_table line
					ON oi.order_item_id = line.order_item_id AND line.meta_key = '_line_total'
				WHERE oi.order_item_type = 'line_item'
					AND oi.order_id IN (
						SELECT o.$order_table_id
						FROM $order_table o
						WHERE o.$type_key = 'shop_order'
							AND o.$status_key IN ('wc-completed', 'wc-processing')
							AND o.$date_key >= %s
							AND o.$date_key <= %s
					)",
				$date . ' 00:00:00',
				$date . ' 23:59:59'
			),
			ARRAY_A
		);
		//phpcs:enable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching

		if ( ! is_array( $row ) ) {
			return $empty;
		}

		// Money is fixed-decimal: the payload is form-encoded, where a float would ship as "0" or "25.5".
		return array(
			'fbt_orders'  => absint( $row['order_count'] ),
			'fbt_revenue' => number_format( (float) $row['revenue'], 2, '.', '' ),
		);
	}

}
