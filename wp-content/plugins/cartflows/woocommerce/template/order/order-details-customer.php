<?php
/**
 * Order Customer Details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-details-customer.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 */

defined( 'ABSPATH' ) || exit;

$show_shipping = ! wc_ship_to_billing_address_only() && $order->needs_shipping_address();
$thankyou_id = wcf()->flow->get_thankyou_page_id( $order );
$thankyou_layout = wcf()->options->get_thankyou_meta_value( $thankyou_id, 'wcf-tq-layout' );
?>
<section class="woocommerce-customer-details">
<?php if('modern-tq-layout' === $thankyou_layout || 'tq-layout-3' === $thankyou_layout) : ?>
    <div class="customer-details-box">
        <h2 class="woocommerce-customer-details__title woocommerce-column__title"><?php esc_html_e( 'Customer Details', 'woocommerce' ); ?></h2>
        <table class="customer-details-table woocommerce-table woocommerce-table--customer-details">
            <tr>
                <td><?php esc_html_e( 'Email:', 'woocommerce' ); ?><p><?php echo esc_html( $order->get_billing_email() ); ?></p></td>
                <td><?php esc_html_e( 'Phone:', 'woocommerce' ); ?><p><?php echo esc_html( $order->get_billing_phone() ); ?></p></td>
            </tr>
            <tr class="cartflows-customer-details-table-address">
                <td class="woocommerce-column--billing-address"><?php esc_html_e( 'Billing Address:', 'woocommerce' ); ?><p><?php echo wp_kses_post( nl2br( $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) ) ); ?></p></td>
                <td class="woocommerce-column--shipping-address"><?php esc_html_e( 'Shipping Address:', 'woocommerce' ); ?><p><?php echo wp_kses_post( nl2br( $order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ) ) ); ?></p></td>
            </tr>
        </table>
    </div>


<?php
else: 
    if ( $show_shipping ) : ?>

	<section class="woocommerce-columns woocommerce-columns--2 woocommerce-columns--addresses col2-set addresses">
		<div class="woocommerce-column woocommerce-column--1 woocommerce-column--billing-address col-1">

	<?php endif; ?>

	<h2 class="woocommerce-column__title"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>

	<address>
		<?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>

		<?php if ( $order->get_billing_phone() ) : ?>
			<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_billing_phone() ); ?></p>
		<?php endif; ?>

		<?php if ( $order->get_billing_email() ) : ?>
			<p class="woocommerce-customer-details--email"><?php echo esc_html( $order->get_billing_email() ); ?></p>
		<?php endif; ?>

		<?php
			/**
			 * Action hook fired after an address in the order customer details.
			 *
			 * @since 8.7.0
			 * @param string $address_type Type of address (billing or shipping).
			 * @param WC_Order $order Order object.
			 */
			do_action( 'woocommerce_order_details_after_customer_address', 'billing', $order );
		?>
	</address>

	<?php if ( $show_shipping ) : ?>

		</div><!-- /.col-1 -->

		<div class="woocommerce-column woocommerce-column--2 woocommerce-column--shipping-address col-2">
			<h2 class="woocommerce-column__title"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>
			<address>
				<?php echo wp_kses_post( $order->get_formatted_shipping_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>

				<?php if ( $order->get_shipping_phone() ) : ?>
					<p class="woocommerce-customer-details--phone"><?php echo esc_html( $order->get_shipping_phone() ); ?></p>
				<?php endif; ?>

				<?php
					/**
					 * Action hook fired after an address in the order customer details.
					 *
					 * @since 8.7.0
					 * @param string $address_type Type of address (billing or shipping).
					 * @param WC_Order $order Order object.
					 */
					do_action( 'woocommerce_order_details_after_customer_address', 'shipping', $order );
				?>
			</address>
		</div><!-- /.col-2 -->

	</section><!-- /.col2-set -->

	<?php endif; 
    endif; 
    ?>

	<?php do_action( 'woocommerce_order_details_after_customer_details', $order ); ?>

</section>