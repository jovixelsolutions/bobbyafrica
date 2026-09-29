<?php
/**
 * Ability: wcar/get-product-stats — Retrieve product-level abandonment statistics.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Inc\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Wcar_Ability_Get_Product_Stats.
 *
 * Returns top N abandoned products by delegating to
 * Cartflows_Ca_Helper::get_top_products_by_type() — no duplicated query logic.
 */
class Wcar_Ability_Get_Product_Stats extends Wcar_Abstract_Ability {

	/**
	 * Configure ability properties.
	 *
	 * @return void
	 */
	protected function configure(): void {
		$this->id          = 'wcar/get-product-stats';
		$this->label       = __( 'Get Product Stats', 'woo-cart-abandonment-recovery' );
		$this->description = __( 'Returns the top N abandoned products ranked by abandonment frequency, with total abandoned cart value per product. Defaults to last 7 days, top 10 products.', 'woo-cart-abandonment-recovery' );
	}

	/**
	 * Plain-text guidance for the AI on when and how to use this ability.
	 *
	 * @return string
	 */
	public function get_instructions(): string {
		return __( 'Use when the user asks which products are most abandoned or wants to prioritize recovery campaigns. Results are sorted by times_abandoned descending. Use the limit parameter to control how many products are returned. Combine with wcar/get-dashboard-stats for a full performance picture.', 'woo-cart-abandonment-recovery' );
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
				'limit'      => [
					'type'        => 'integer',
					'description' => __( 'Maximum number of products to return (default 10).', 'woo-cart-abandonment-recovery' ),
					'default'     => 10,
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
				'products' => [
					'type'  => 'array',
					'items' => [
						'type'       => 'object',
						'properties' => [
							'product_id'      => [ 'type' => 'integer' ],
							'product_name'    => [ 'type' => 'string' ],
							'times_abandoned' => [ 'type' => 'integer' ],
							'total_value'     => [ 'type' => 'number' ],
						],
					],
				],
				'period'   => [
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
	 * Execute: return top abandoned products via the shared helper method.
	 *
	 * @param array $args Input arguments.
	 * @return array
	 */
	public function execute( array $args ): array {
		$end_date   = ! empty( $args['end_date'] ) ? sanitize_text_field( $args['end_date'] ) : gmdate( 'Y-m-d' );
		$start_date = ! empty( $args['start_date'] ) ? sanitize_text_field( $args['start_date'] ) : gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		$limit      = isset( $args['limit'] ) ? absint( $args['limit'] ) : 10;

		if ( 0 === $limit ) {
			$limit = 10;
		}

		$raw      = wcf_ca()->helper->get_top_products_by_type( $start_date, $end_date, WCF_CART_ABANDONED_ORDER, $limit );
		$products = [];

		foreach ( $raw as $item ) {
			$products[] = [
				'product_id'      => $item['product_id'],
				'product_name'    => $item['product_name'],
				'times_abandoned' => $item['total_frequency'],
				'total_value'     => round( (float) $item['total_amount'], 2 ),
			];
		}

		return $this->success(
			[
				'products' => $products,
				'period'   => [
					'start' => $start_date,
					'end'   => $end_date,
				],
			]
		);
	}
}
