<?php
/**
 * Abilities Register — registers all WCAR abilities with the WordPress Abilities API.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

namespace WCAR\Inc\Abilities;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Wcar_Abilities_Register.
 *
 * Singleton that hooks into the WordPress Abilities API lifecycle to register
 * the 'wcar' category, all five WCAR abilities, and exposes them through the
 * MCP adapter server config.
 */
class Wcar_Abilities_Register {

	/**
	 * Singleton instance.
	 *
	 * @var Wcar_Abilities_Register|null
	 */
	private static ?Wcar_Abilities_Register $instance = null;

	/**
	 * Registered ability IDs.
	 *
	 * @var string[]
	 */
	private array $ability_ids = [
		'wcar/get-settings',
		'wcar/get-setting',
		'wcar/update-setting',
		'wcar/get-dashboard-stats',
		'wcar/get-product-stats',
	];

	/**
	 * Private constructor — use get_instance().
	 */
	private function __construct() {
		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
		add_filter( 'mcp_adapter_default_server_config', [ $this, 'add_to_mcp_config' ] );
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return Wcar_Abilities_Register
	 */
	public static function get_instance(): Wcar_Abilities_Register {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Register the 'wcar' category with the Abilities API.
	 *
	 * @return void
	 */
	public function register_category(): void {
		if ( ! function_exists( 'wp_register_ability_category' ) ) {
			return;
		}

		wp_register_ability_category(
			'wcar',
			[
				'label'       => __( 'Cart Abandonment Recovery', 'woo-cart-abandonment-recovery' ),
				'description' => __( 'Abilities for managing Cart Abandonment Recovery settings and statistics.', 'woo-cart-abandonment-recovery' ),
			]
		);
	}

	/**
	 * Instantiate and register all WCAR abilities.
	 *
	 * @return void
	 */
	public function register_abilities(): void {
		$abilities = [
			new Wcar_Ability_Get_Settings(),
			new Wcar_Ability_Get_Setting(),
			new Wcar_Ability_Update_Setting(),
			new Wcar_Ability_Get_Dashboard_Stats(),
			new Wcar_Ability_Get_Product_Stats(),
		];

		foreach ( $abilities as $ability ) {
			$ability->register();
		}
	}

	/**
	 * Append WCAR ability IDs to the MCP adapter default server config.
	 *
	 * @param array $config MCP server config array.
	 * @return array
	 */
	public function add_to_mcp_config( array $config ): array {
		if ( ! isset( $config['tools'] ) || ! is_array( $config['tools'] ) ) {
			$config['tools'] = [];
		}

		$config['tools'] = array_merge( $config['tools'], $this->ability_ids );

		return $config;
	}
}
