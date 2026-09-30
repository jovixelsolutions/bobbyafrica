<?php
/**
 * Template Name: Email Header.
 *
 * Expected variables (passed from Cartflows_Admin_Report_Emails::get_email_content()):
 * - $cf_logo       (string) Absolute URL of the CartFlows email logo image.
 * - $total_revenue (string) Formatted weekly revenue (wc_price() HTML output).
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defensive defaults so the template renders without notices if a variable is missing.
$cf_logo       = isset( $cf_logo ) ? $cf_logo : '';
$total_revenue = isset( $total_revenue ) ? $total_revenue : '';
?>
<tr>
	<td align="center" class="modern-sans" style="background-color:#f06434; padding:16px 24px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:14px; font-weight:600; color:#ffffff; letter-spacing:0.2px; line-height:1.4;">
		<?php
		/* translators: %s: Total revenue earned in the last 7 days, formatted with currency. */
		echo sprintf( esc_html__( 'Your weekly CartFlows report — %s earned', 'cartflows' ), wp_kses_post( $total_revenue ) );
		?>
	</td>
</tr>
<tr>
	<td align="center" style="padding:32px 24px 8px 24px;">
		<a href="<?php echo esc_url( 'https://cartflows.com/' ); ?>" target="_blank">
			<img src="<?php echo esc_url( $cf_logo ); ?>" alt="<?php echo esc_attr__( 'CartFlows', 'cartflows' ); ?>" width="156" style="display:block; max-width:156px; height:auto; border:0;" />
		</a>
	</td>
</tr>
