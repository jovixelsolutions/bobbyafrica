<?php
/**
 * Get Available Options Ability
 *
 * @package modern-cart
 */

namespace ModernCart\Inc\Abilities\Settings;

use ModernCart\Inc\Abilities\Abstract_Ability;
use ModernCart\Inc\Abilities\Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Get_Available_Options
 *
 * Returns all valid enumerable choices for Modern Cart settings fields.
 */
class Get_Available_Options extends Abstract_Ability {

	/**
	 * Configure the ability.
	 *
	 * @return void
	 */
	public function configure() {
		$this->id           = 'moderncart/get-available-options';
		$this->category     = 'moderncart';
		$this->label        = __( 'Get Available Setting Options', 'modern-cart' );
		$this->description  = __( 'Returns all valid enumerable choices for Modern Cart settings fields, including cart styles, theme variants, image sizes, coupon field modes, floating cart positions, and alignment values. Use this before calling update-settings to discover which values are valid for dropdown/enum-type fields.', 'modern-cart' );
		$this->capability   = 'manage_options';
		$this->instructions = __( 'Call this before update-settings whenever you need to set a dropdown or enum field. The response lists every valid option value and label for fields like cart_style, cart_theme_style, enable_coupon_field, floating_cart_position, and cart_header_text_alignment.', 'modern-cart' );
	}

	/**
	 * Get input schema.
	 *
	 * @return array<string, mixed>
	 */
	public function get_input_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'groups' => array(
					'type'        => 'array',
					'description' => __( 'Optional list of setting groups to include. Defaults to all groups.', 'modern-cart' ),
					'items'       => array(
						'type' => 'string',
						'enum' => array( 'moderncart_setting', 'moderncart_cart', 'moderncart_floating', 'moderncart_appearance' ),
					),
				),
			),
			'required'   => array(),
		);
	}

	/**
	 * Execute the ability.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 * @return array<string, mixed>|\WP_Error
	 */
	public function execute( $args ) {
		$all_options = array(
			'moderncart_setting'    => array(
				'enable_moderncart'        => array(
					'label'       => __( 'Enable Modern Cart', 'modern-cart' ),
					'description' => __( 'Controls where Modern Cart appears on your site.', 'modern-cart' ),
					'type'        => 'string',
					'options'     => array(
						array(
							'value' => 'all',
							'label' => __( 'Entire Website', 'modern-cart' ),
						),
						array(
							'value' => 'wc_pages',
							'label' => __( 'WooCommerce Pages Only', 'modern-cart' ),
						),
						array(
							'value' => 'disabled',
							'label' => __( 'Disabled', 'modern-cart' ),
						),
					),
				),
				'enable_powered_by'        => array(
					'label'       => __( 'Show Powered By', 'modern-cart' ),
					'description' => __( 'Show or hide the "Powered by Modern Cart" attribution.', 'modern-cart' ),
					'type'        => 'boolean',
					'options'     => array(
						array(
							'value' => true,
							'label' => __( 'Yes', 'modern-cart' ),
						),
						array(
							'value' => false,
							'label' => __( 'No', 'modern-cart' ),
						),
					),
				),
				'enable_ajax_add_to_cart'  => array(
					'label'   => __( 'AJAX Add to Cart', 'modern-cart' ),
					'type'    => 'boolean',
					'options' => array(
						array(
							'value' => true,
							'label' => __( 'Enabled', 'modern-cart' ),
						),
						array(
							'value' => false,
							'label' => __( 'Disabled', 'modern-cart' ),
						),
					),
				),
				'enable_free_shipping_bar' => array(
					'label'   => __( 'Free Shipping Bar', 'modern-cart' ),
					'type'    => 'boolean',
					'options' => array(
						array(
							'value' => true,
							'label' => __( 'Show', 'modern-cart' ),
						),
						array(
							'value' => false,
							'label' => __( 'Hide', 'modern-cart' ),
						),
					),
				),
				'enable_express_checkout'  => array(
					'label'   => __( 'Express Checkout', 'modern-cart' ),
					'type'    => 'boolean',
					'options' => array(
						array(
							'value' => true,
							'label' => __( 'Enabled', 'modern-cart' ),
						),
						array(
							'value' => false,
							'label' => __( 'Disabled', 'modern-cart' ),
						),
					),
				),
			),
			'moderncart_cart'       => array(
				'cart_style'          => array(
					'label'       => __( 'Cart Style', 'modern-cart' ),
					'description' => __( 'Primary cart display mode. popup requires Modern Cart Pro.', 'modern-cart' ),
					'type'        => 'string',
					'options'     => array(
						array(
							'value' => 'slideout',
							'label' => __( 'Slide-out (Side Cart)', 'modern-cart' ),
						),
						array(
							'value' => 'popup',
							'label' => __( 'Popup (requires Pro)', 'modern-cart' ),
						),
					),
				),
				'cart_theme_style'    => array(
					'label'   => __( 'Cart Theme Style', 'modern-cart' ),
					'type'    => 'string',
					'options' => array(
						array(
							'value' => 'style1',
							'label' => __( 'Style 1 (Default)', 'modern-cart' ),
						),
						array(
							'value' => 'style2',
							'label' => __( 'Style 2', 'modern-cart' ),
						),
						array(
							'value' => 'style3',
							'label' => __( 'Style 3', 'modern-cart' ),
						),
					),
				),
				'product_image_size'  => array(
					'label'   => __( 'Product Image Size', 'modern-cart' ),
					'type'    => 'string',
					'options' => array(
						array(
							'value' => 'thumbnail',
							'label' => __( 'Thumbnail', 'modern-cart' ),
						),
						array(
							'value' => 'medium',
							'label' => __( 'Medium (Default)', 'modern-cart' ),
						),
						array(
							'value' => 'large',
							'label' => __( 'Large', 'modern-cart' ),
						),
					),
				),
				'enable_coupon_field' => array(
					'label'   => __( 'Coupon Field', 'modern-cart' ),
					'type'    => 'string',
					'options' => array(
						array(
							'value' => 'show',
							'label' => __( 'Always Visible', 'modern-cart' ),
						),
						array(
							'value' => 'minimize',
							'label' => __( 'Collapsible (Default)', 'modern-cart' ),
						),
						array(
							'value' => 'hide',
							'label' => __( 'Hidden', 'modern-cart' ),
						),
					),
				),
				'section_styling'     => array(
					'label'   => __( 'Section Styling', 'modern-cart' ),
					'type'    => 'string',
					'options' => array(
						array(
							'value' => 'accordian',
							'label' => __( 'Accordion (Default)', 'modern-cart' ),
						),
						array(
							'value' => 'default',
							'label' => __( 'Always Expanded', 'modern-cart' ),
						),
					),
				),
			),
			'moderncart_floating'   => array(
				'floating_cart_position' => array(
					'label'   => __( 'Floating Cart Position', 'modern-cart' ),
					'type'    => 'string',
					'options' => array(
						array(
							'value' => 'bottom-right',
							'label' => __( 'Bottom Right (Default)', 'modern-cart' ),
						),
						array(
							'value' => 'bottom-left',
							'label' => __( 'Bottom Left', 'modern-cart' ),
						),
						array(
							'value' => 'top-right',
							'label' => __( 'Top Right', 'modern-cart' ),
						),
						array(
							'value' => 'top-left',
							'label' => __( 'Top Left', 'modern-cart' ),
						),
					),
				),
			),
			'moderncart_appearance' => array(
				'cart_header_text_alignment' => array(
					'label'   => __( 'Cart Header Text Alignment', 'modern-cart' ),
					'type'    => 'string',
					'options' => array(
						array(
							'value' => 'left',
							'label' => __( 'Left', 'modern-cart' ),
						),
						array(
							'value' => 'center',
							'label' => __( 'Center (Default)', 'modern-cart' ),
						),
						array(
							'value' => 'right',
							'label' => __( 'Right', 'modern-cart' ),
						),
					),
				),
			),
		);

		$requested_groups = ! empty( $args['groups'] ) && is_array( $args['groups'] )
			? $args['groups']
			: array_keys( $all_options );

		$result = array();
		foreach ( $requested_groups as $group ) {
			if ( isset( $all_options[ $group ] ) ) {
				$result[ $group ] = $all_options[ $group ];
			}
		}

		return Response::success( $result );
	}
}
