<?php
/**
 * Ability: wcar/get-dashboard-stats — Retrieve dashboard summary statistics.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Inc\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Wcar_Ability_Get_Dashboard_Stats.
 *
 * Returns aggregated cart abandonment, recovery, and lost order stats for a
 * given date range (defaults to the last 7 days).
 */
class Wcar_Ability_Get_Dashboard_Stats extends Wcar_Abstract_Ability {

	/**
	 * Configure ability properties.
	 *
	 * @return void
	 */
	protected function configure(): void {
		$this->id          = 'wcar/get-dashboard-stats';
		$this->label       = __( 'Get Dashboard Stats', 'woo-cart-abandonment-recovery' );
		$this->description = __( 'Returns aggregated cart abandonment metrics — recoverable, recovered, and lost order counts and revenue, plus recovery rate — for a given date range. Defaults to last 7 days.', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * Plain-text guidance for the AI on when and how to use this ability.
	 *
	 * @return string
	 */
	public function get_instructions(): string {
		return __( 'Use to answer questions about overall cart abandonment performance, revenue at risk, or recovery success. Defaults to last 7 days. When the user specifies a time period, translate it to start_date and end_date in YYYY-MM-DD format. The recovery_rate is the percentage of tracked orders that were recovered.', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * JSON Schema for input parameters.
	 *
	 * @return array
	 */
	public function get_input_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'start_date' => [
					'type'        => 'string',
					'description' => __( 'Start date in YYYY-MM-DD format (defaults to 7 days ago).', 'woo-cart-abandonment-recovery' ),
				],
				'end_date'   => [
					'type'        => 'string',
					'description' => __( 'End date in YYYY-MM-DD format (defaults to today).', 'woo-cart-abandonment-recovery' ),
				],
			],
		];
	}

	/**
	 * JSON Schema for the output returned by this ability.
	 *
	 * @return array
	 */
	public function get_output_schema(): array {
		return [
			'type'       => 'object',
			'properties' => [
				'recoverable_orders'  => [ 'type' => 'integer' ],
				'recovered_orders'    => [ 'type' => 'integer' ],
				'lost_orders'         => [ 'type' => 'integer' ],
				'recoverable_revenue' => [ 'type' => 'number' ],
				'recovered_revenue'   => [ 'type' => 'number' ],
				'recovery_rate'       => [ 'type' => 'number' ],
				'period'              => [
					'type'       => 'object',
					'properties' => [
						'start' => [ 'type' => 'string' ],
						'end'   => [ 'type' => 'string' ],
					],
				],
			],
		];
	}

	/**
	 * Execute: compute and return dashboard stats.
	 *
	 * @param array $args Input arguments.
	 * @return array
	 */
	public function execute( array $args ): array {
		$end_date   = ! empty( $args['end_date'] ) ? sanitize_text_field( $args['end_date'] ) : gmdate( 'Y-m-d' );
		$start_date = ! empty( $args['start_date'] ) ? sanitize_text_field( $args['start_date'] ) : gmdate( 'Y-m-d', strtotime( '-7 days' ) );

		$helper = wcf_ca()->helper;

		$abandoned_report = $helper->get_report_by_type( $start_date, $end_date, WCF_CART_ABANDONED_ORDER );
		$recovered_report = $helper->get_report_by_type( $start_date, $end_date, WCF_CART_COMPLETED_ORDER );
		$lost_report      = $helper->get_report_by_type( $start_date, $end_date, WCF_CART_LOST_ORDER );

		$recoverable_orders  = isset( $abandoned_report['no_of_orders'] ) ? (int) $abandoned_report['no_of_orders'] : 0;
		$recovered_orders    = isset( $recovered_report['no_of_orders'] ) ? (int) $recovered_report['no_of_orders'] : 0;
		$lost_orders         = isset( $lost_report['no_of_orders'] ) ? (int) $lost_report['no_of_orders'] : 0;
		$recoverable_revenue = isset( $abandoned_report['revenue'] ) ? (float) $abandoned_report['revenue'] : 0.0;
		$recovered_revenue   = isset( $recovered_report['revenue'] ) ? (float) $recovered_report['revenue'] : 0.0;

		$total_orders  = $recoverable_orders + $recovered_orders;
		$recovery_rate = $total_orders > 0 ? round( ( $recovered_orders / $total_orders ) * 100, 2 ) : 0.0;

		return $this->success(
			[
				'recoverable_orders'  => $recoverable_orders,
				'recovered_orders'    => $recovered_orders,
				'lost_orders'         => $lost_orders,
				'recoverable_revenue' => $recoverable_revenue,
				'recovered_revenue'   => $recovered_revenue,
				'recovery_rate'       => $recovery_rate,
				'period'              => [
					'start' => $start_date,
					'end'   => $end_date,
				],
			]
		);
	}
}
