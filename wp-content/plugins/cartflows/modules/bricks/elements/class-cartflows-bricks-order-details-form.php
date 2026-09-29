<?php
/**
 * Bricks Classes.
 *
 * @package cartflows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;   // Exit if accessed directly.
}

/**
 * Order Details Form Widget
 *
 * @since 1.6.15
 */
class Cartflows_Bricks_Order_Details_Form extends \Bricks\Element {
	/**
	 * Category of the element.
	 *
	 * @var instance
	 */
	public $category = 'CartFlows';

	/**
	 * Name of the element.
	 *
	 * @var instance
	 */

	public $name = 'bricks-order-details-form';

	/**
	 * Icon of the element.
	 *
	 * @var instance
	 */
	public $icon = 'fa-solid fa-star';

	/**
	 * Label of the element.
	 *
	 * @var instance
	 */
	public function get_label() {
		return esc_html__( 'Order Details Form', 'cartflows' );
	}

	/**
	 * Retrieve Widget Keywords.
	 *
	 * @since 1.6.15
	 * @access public
	 *
	 * @return string Widget keywords.
	 */
	public function get_keywords() {
		return array( 'cartflows', 'order details', 'form' );
	}

	/**
	 * Retrieve the list of scripts the widget depended on.
	 *
	 * Used to set scripts dependencies for the widget.
	 *
	 * @return void
	 */
	public function set_control_groups() {
		$this->control_groups['section_thankyou_fields']                 = array(
			'title' => esc_html__( 'Global', 'cartflows' ),
			'tab'   => 'content',
		);
		$this->control_groups['order_details_form_spacing_styling']      = array(
			'title' => esc_html__( 'Spacing', 'cartflows' ),
			'tab'   => 'content',
		);
		$this->control_groups['order_details_form_heading_styling']      = array(
			'title' => esc_html__( 'Heading', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['order_details_form_general_styling']      = array(
			'title' => esc_html__( 'General', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['order_details_form_order_review_styling'] = array(
			'title' => esc_html__( 'Order Overview', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['section_downloads_styling']               = array(
			'title' => esc_html__( 'Downloads', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['section_order_details_styling']           = array(
			'title' => esc_html__( 'Order Details', 'cartflows' ),
			'tab'   => 'style',
		);
		$this->control_groups['section_customer_details_styling']        = array(
			'title' => esc_html__( 'Customer Details', 'cartflows' ),
			'tab'   => 'style',
		);
	}

	/**
	 * Register Order Details Form controls.
	 */
	public function set_controls() {

		// Content Tab.
		$this->register_thankyou_controls();

		// Style Tab.
		$this->register_spacing_controls();
		$this->register_heading_style_controls();
		$this->register_general_style_controls();
		$this->register_section_order_review_style_controls();
		$this->register_section_downloads_style_controls();
		$this->register_section_order_details_style_controls();
		$this->register_section_customer_details_style_controls();
	}

	/**
	 * Register Thank you page Controls.
	 */
	public function register_thankyou_controls() {

		$this->controls['layout'] = array(
			'label'         => esc_html__( 'Layout', 'cartflows' ),
			'type'          => 'select',
			'options'       => array(
				'legacy-tq-layout' => esc_html__( 'Layout 1', 'cartflows' ),
				'modern-tq-layout' => esc_html__( 'Layout 2', 'cartflows' ),
				'tq-layout-3'      => esc_html__( 'Layout 3', 'cartflows' ),
			),
			'default'       => 'legacy-tq-layout',
			'reload'        => true,
			'reloadScripts' => true,
			'group'         => 'section_thankyou_fields',
		);

		$this->controls['thankyou_text'] = array(
			'label'       => esc_html__( 'Thank You Text', 'cartflows' ),
			'type'        => 'text',
			'placeholder' => esc_html__( 'Thank you. Your order has been received.', 'cartflows' ),
			'label_block' => true,
			'group'       => 'section_thankyou_fields',
			'description' => esc_html__( 'The Thank You Text is only applicable for the old layout.', 'cartflows' ),
		);

		$this->controls['show_order_overview'] = array(
			'label'   => esc_html__( 'Order Overview', 'cartflows' ),
			'type'    => 'checkbox',
			'default' => true,
			'group'   => 'section_thankyou_fields',
		);
		
		$this->controls['show_order_details'] = array(
			'label'   => esc_html__( 'Order Details', 'cartflows' ),
			'type'    => 'checkbox',
			'default' => true,
			'group'   => 'section_thankyou_fields',
		);

		$this->controls['show_billing_address'] = array(
			'label'   => esc_html__( 'Billing Address', 'cartflows' ),
			'type'    => 'checkbox',
			'default' => true,
			'group'   => 'section_thankyou_fields',
		);

		$this->controls['show_shipping_address'] = array(
			'label'   => esc_html__( 'Shipping Address', 'cartflows' ),
			'type'    => 'checkbox',
			'default' => true,
			'group'   => 'section_thankyou_fields',
		);
	}

	/**
	 * Register spacing Styling Controls.
	 */
	public function register_spacing_controls() {

		$this->controls['heading_spacing'] = array(
			'label' => esc_html__( 'Heading Bottom Spacing', 'cartflows' ),
			'type'  => 'number',
			'group' => 'order_details_form_spacing_styling',
			'css'   => array(
				array(
					'property' => 'margin-bottom',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received',
				),
			),
		);
		
		$this->controls['sections_spacing'] = array(
			'label' => esc_html__( 'Spacing Between Sections', 'cartflows' ),
			'type'  => 'number',
			'group' => 'order_details_form_spacing_styling',
			'css'   => array(
				array(
					'property' => 'margin-bottom',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order ul.order_details',

				),
				array(
					'property' => 'margin-bottom',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order .woocommerce-order-details',
				),
				array(
					'property' => 'margin-bottom',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order-details.mollie-instructions',
				),
			),
		);
	}

	/**
	 * Register heading Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	public function register_heading_style_controls() {

		$this->controls['heading_typography'] = array(
			'label' => esc_html__( 'Typography', 'cartflows' ),
			'type'  => 'typography',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order .woocommerce-thankyou-order-received',
				),
			),
			'group' => 'order_details_form_heading_styling',
		);

		$this->controls['heading_background_color'] = array(
			'label' => esc_html__( 'Background Color', 'cartflows' ),
			'type'  => 'background',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order .woocommerce-thankyou-order-received',
				),
			),
			'group' => 'order_details_form_heading_styling',
		);
	}
	

	/**
	 * Register General Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	public function register_general_style_controls() {

		$this->controls['section_all_background_color'] = array(
			'label' => esc_html__( 'Background Color', 'cartflows' ),
			'type'  => 'background',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-thankyou-order-received',
				),
			),
			'group' => 'order_details_form_general_styling',
		);

		
		$this->controls['section_heading_typography'] = array(
			'label' => esc_html__( 'Sections Heading Typography', 'cartflows' ),
			'type'  => 'typography',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order h2',
				),
			),
			'group' => 'order_details_form_general_styling',
		);

		$this->controls['section_text_typography'] = array(
			'label' => esc_html__( 'Sections Text Typography', 'cartflows' ),
			'type'  => 'typography',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-modern-tq-layout .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li p, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-tq-layout-3 .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li p',
				),
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order-downloads table.shop_table',
				),
			),
			'group' => 'order_details_form_general_styling',
		);
		
		$this->controls['section_all_background_color'] = array(
			'label' => esc_html__( 'Sections Background Color', 'cartflows' ),
			'type'  => 'background',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order',
				),
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order-details',
				),
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order-downloads',
				),
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order ul.order_details',
				),
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order .woocommerce-customer-details',
				),
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order .woocommerce-bacs-bank-details',
				),
			),
			'group' => 'order_details_form_general_styling',
		);
	}

	/**
	 * Register Section Order Review Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	public function register_section_order_review_style_controls() {

		$this->controls['section_order_review_typography'] = array(
			'label' => esc_html__( 'Typography', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'order_details_form_order_review_styling',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-modern-tq-layout .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li p, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-tq-layout-3 .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details li p',
				),
			),

		);

		$this->controls['section_order_review_background_color'] = array(
			'label' => esc_html__( 'Background Color', 'cartflows' ),
			'type'  => 'color',
			'group' => 'order_details_form_order_review_styling',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-overview.woocommerce-thankyou-order-details.order_details',
				),
			),
		);
	}

	/**
	 * Register Section Order downloads Styling Controls.
	 *
	 * @since 1.6.15
	 * @access protected
	 */
	public function register_section_downloads_style_controls() {

		$this->controls['section_downloads_heading_typography'] = array(
			'label' => esc_html__( 'Heading Typography', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'section_downloads_styling',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order h2.woocommerce-order-downloads__title',
				),
				array(
					'property' => 'font',
					'selector' => ' .cartflows-bricks__order-details-form .woocommerce-order .woocommerce-order-downloads h2.woocommerce-order-downloads__title',
				),
			),

		);

		$this->controls['section_downloads_text_typography'] = array(
			'label' => esc_html__( 'Text Typography', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'section_downloads_styling',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order .woocommerce-order-downloads table.shop_table',
				),
			),

		);

		$this->controls['section_downloads_background_color'] = array(
			'label' => esc_html__( 'Background Color', 'cartflows' ),
			'type'  => 'color',
			'group' => 'section_downloads_styling',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .woocommerce-order .woocommerce-order-downloads',
				),
			),
		);
	}

	/**
	 * Register Section Order Details Styling Controls.
	 */
	public function register_section_order_details_style_controls() {


		$this->controls['section_order_details_heading_typography'] = array(
			'label' => esc_html__( 'Heading Typography', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'section_order_details_styling',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-order-details__title',
				),
			),

		);
		

		$this->controls['section_order_details_text_typography'] = array(
			'label' => esc_html__( 'Text Typography', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'section_order_details_styling',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details .woocommerce-table',
				),
			),

		);

		$this->controls['section_order_details_background_color'] = array(
			'label' => esc_html__( 'Background Color', 'cartflows' ),
			'type'  => 'color',
			'group' => 'section_order_details_styling',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-order-details',
				),
			),
		);
	}


	/**
	 * Register Section Customer Details Styling Controls.
	 */
	public function register_section_customer_details_style_controls() {
		
		$this->controls['section_customer_details_heading_typography'] = array(
			'label' => esc_html__( 'Heading Typography', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'section_customer_details_styling',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details .woocommerce-column__title',
				),
			),

		);

		$this->controls['section_customer_details_text_typography'] = array(
			'label' => esc_html__( 'Text Typography', 'cartflows' ),
			'type'  => 'typography',
			'group' => 'section_customer_details_styling',
			'css'   => array(
				array(
					'property' => 'font',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order .woocommerce-customer-details address, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-modern-tq-layout .woocommerce-customer-details tr *, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-tq-layout-3 .woocommerce-customer-details tr *',
				),
			),

		);

		$this->controls['section_customer_details_background_color'] = array(
			'label' => esc_html__( 'Background Color', 'cartflows' ),
			'type'  => 'color',
			'group' => 'section_customer_details_styling',
			'css'   => array(
				array(
					'property' => 'background-color',
					'selector' => '.cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order:not(.wcf-modern-tq-layout) .woocommerce-customer-details, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-modern-tq-layout .woocommerce-customer-details .customer-details-box, .cartflows-bricks__order-details-form .wcf-thankyou-wrap .woocommerce-order.wcf-tq-layout-3 .woocommerce-customer-details .customer-details-box',
				),
			),
		);
	}

	/**
	 * Render Order Details Form output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 * 
	 * @return void
	 */
	public function render() {
		$this->set_attribute( '_root', 'data-element-id', $this->id );
		/* Add bricks setting options to filters */
		$this->dynamic_option_filters();
		$order_overview = 'no';

		if ( isset( $this->settings['show_order_overview'] ) && true === $this->settings['show_order_overview'] ) {
			$order_overview = 'yes';
		}

		$order_details = 'no';
		if ( isset( $this->settings['show_order_details'] ) && true === $this->settings['show_order_details'] ) {
			$order_details = 'yes';
		}
		
		$shipping_address = 'no';
		if ( isset( $this->settings['show_shipping_address'] ) && true === $this->settings['show_shipping_address'] ) {
			$shipping_address = 'yes';
		}

		$billing_address = 'no';
		if ( isset( $this->settings['show_billing_address'] ) && true === $this->settings['show_billing_address'] ) {
			$billing_address = 'yes';
		}



		?>
		<div <?php echo $this->render_attributes( '_root' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> > 
			<div class = "cartflows-bricks__order-details-form cartflows-bricks__display-order-overview-<?php echo esc_attr( $order_overview ); ?> cartflows-bricks__display-order-details-<?php echo esc_attr( $order_details ); ?> cartflows-bricks__display-billing-address-<?php echo esc_attr( $billing_address ); ?> cartflows-bricks__display-shipping-address-<?php echo esc_attr( $shipping_address ); ?>">
				<?php echo do_shortcode( '[cartflows_order_details]' ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Dynamic options of elementor and add filters.
	 *
	 * @return void
	 */
	public function dynamic_option_filters() {

		if ( ! empty( $this->settings['thankyou_text'] ) ) {

			add_filter(
				'cartflows_thankyou_meta_wcf-tq-text',
				function ( $text ) {

					$text = $this->settings['thankyou_text'];

					return $text;
				},
				10,
				1
			);
		}

		if ( ! empty( $this->settings['layout'] ) ) {

			add_filter(
				'cartflows_thankyou_meta_wcf-tq-layout',
				function ( $text ) {

					$text = $this->settings['layout'];

					return $text;
				},
				10,
				1
			);
		}
	}
}
