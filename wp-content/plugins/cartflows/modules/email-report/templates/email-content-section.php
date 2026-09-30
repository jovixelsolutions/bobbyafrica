<?php
/**
 * Template Name: Email content section.
 *
 * Expected variables (passed from Cartflows_Admin_Report_Emails::get_email_content()):
 * - $user_name          (string)     Recipient display name.
 * - $from_date          (string)     7-day window start label (e.g. "May 27").
 * - $to_date            (string)     7-day window end label (e.g. "Jun 3").
 * - $total_revenue      (string)     Weekly revenue HTML from wc_price().
 * - $total_orders       (int|string) Weekly order count.
 * - $total_visits       (int|string) Weekly visits count (Pro only; "0" on Free).
 * - $order_bump_revenue (string)     Bump revenue HTML from wc_price() (Pro only).
 * - $offers_revenue     (string)     Offers revenue HTML from wc_price() (Pro only).
 * - $last_month_revenue (string)     30-day revenue HTML from wc_price().
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defensive defaults so the template renders without notices if a variable is missing.
$user_name          = isset( $user_name ) ? $user_name : '';
$from_date          = isset( $from_date ) ? $from_date : '';
$to_date            = isset( $to_date ) ? $to_date : '';
$total_revenue      = isset( $total_revenue ) ? $total_revenue : '';
$total_orders       = isset( $total_orders ) ? $total_orders : 0;
$total_visits       = isset( $total_visits ) ? $total_visits : 0;
$order_bump_revenue = isset( $order_bump_revenue ) ? $order_bump_revenue : '';
$offers_revenue     = isset( $offers_revenue ) ? $offers_revenue : '';
$last_month_revenue = isset( $last_month_revenue ) ? $last_month_revenue : '';
?>
<tr>
	<td class="px-lg modern-sans" style="padding:24px 40px 0 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif;">
		<p style="margin:0; font-size:22px; font-weight:700; color:#0f172a; line-height:1.3; letter-spacing:-0.01em;">
			<?php
			/* translators: %s: user display name. */
			echo sprintf( esc_html__( 'Hi %s', 'cartflows' ), esc_html( $user_name ) );
			?>
		</p>
		<p style="margin:8px 0 0 0; font-size:14px; color:#64748b; line-height:1.6;">
			<?php echo esc_html__( 'Quick report on how your store performed this week.', 'cartflows' ); ?>
		</p>
	</td>
</tr>

<!-- Section: Last 7 Days -->
<tr>
	<td class="px-lg" style="padding:36px 40px 0 40px;">
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td valign="bottom" class="modern-sans" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; font-weight:700; color:#0f172a; letter-spacing:-0.01em;">
					<?php echo esc_html__( 'Last 7 days', 'cartflows' ); ?>
				</td>
				<td valign="bottom" align="right" class="modern-sans" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#94a3b8;">
					<?php echo esc_html( $from_date . ' – ' . $to_date ); ?>
				</td>
			</tr>
		</table>
	</td>
</tr>

<!-- 2-column hero KPI cards: Total revenue + Orders placed -->
<tr>
	<td class="px-lg" style="padding:16px 40px 0 40px;">
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="stack" valign="top" width="50%" style="padding-right:8px;">
					<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fff8f3; border:1px solid #fde8d8; border-radius:12px;">
						<tr>
							<td align="center" style="padding:28px 20px;">
								<p class="hero-num modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:36px; font-weight:700; color:#0f172a; line-height:1; letter-spacing:-0.025em;">
									<?php echo wp_kses_post( $total_revenue ); ?>
								</p>
								<p class="modern-sans" style="margin:10px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#64748b; line-height:1.4;">
									<?php echo esc_html__( 'Total revenue', 'cartflows' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</td>
				<td class="stack stack-last" valign="top" width="50%" style="padding-left:8px;">
					<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fff8f3; border:1px solid #fde8d8; border-radius:12px;">
						<tr>
							<td align="center" style="padding:28px 20px;">
								<p class="hero-num modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:36px; font-weight:700; color:#0f172a; line-height:1; letter-spacing:-0.025em;">
									<?php echo esc_html( number_format_i18n( (int) $total_orders ) ); ?>
								</p>
								<p class="modern-sans" style="margin:10px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#64748b; line-height:1.4;">
									<?php echo esc_html__( 'Orders placed', 'cartflows' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>

<!-- 3-column KPI cards: Total visits + Order bumps + Offers revenue -->
<tr>
	<td class="px-lg" style="padding:16px 40px 0 40px;">
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
				<td class="stack" valign="top" width="33.33%" style="padding-right:6px;">
					<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fafafa; border:1px solid #ececec; border-radius:12px;">
						<tr>
							<td align="center" style="padding:22px 16px;">
								<p class="kpi-num modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:28px; font-weight:700; color:#0f172a; line-height:1; letter-spacing:-0.02em;">
									<?php echo esc_html( number_format_i18n( (int) $total_visits ) ); ?>
								</p>
								<p class="modern-sans" style="margin:8px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; color:#64748b; line-height:1.4;">
									<?php echo esc_html__( 'Total visits', 'cartflows' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</td>
				<td class="stack" valign="top" width="33.33%" style="padding:0 6px;">
					<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fafafa; border:1px solid #ececec; border-radius:12px;">
						<tr>
							<td align="center" style="padding:22px 16px;">
								<p class="kpi-num modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:28px; font-weight:700; color:#0f172a; line-height:1; letter-spacing:-0.02em;">
									<?php echo wp_kses_post( $order_bump_revenue ); ?>
								</p>
								<p class="modern-sans" style="margin:8px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; color:#64748b; line-height:1.4;">
									<?php echo esc_html__( 'Order bumps', 'cartflows' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</td>
				<td class="stack stack-last" valign="top" width="33.33%" style="padding-left:6px;">
					<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fafafa; border:1px solid #ececec; border-radius:12px;">
						<tr>
							<td align="center" style="padding:22px 16px;">
								<p class="kpi-num modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:28px; font-weight:700; color:#0f172a; line-height:1; letter-spacing:-0.02em;">
									<?php echo wp_kses_post( $offers_revenue ); ?>
								</p>
								<p class="modern-sans" style="margin:8px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; color:#64748b; line-height:1.4;">
									<?php echo esc_html__( 'Offers revenue', 'cartflows' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</td>
</tr>

<!-- Section: Last 30 Days -->
<tr>
	<td class="px-lg" style="padding:32px 40px 0 40px;">
		<p class="modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:15px; font-weight:700; color:#0f172a; letter-spacing:-0.01em;">
			<?php echo esc_html__( 'Last 30 days', 'cartflows' ); ?>
		</p>
	</td>
</tr>

<!-- Full-width 30-day card -->
<tr>
	<td class="px-lg" style="padding:16px 40px 0 40px;">
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fff8f3; border:1px solid #fde8d8; border-radius:12px;">
			<tr>
				<td align="center" style="padding:32px 24px;">
					<p class="hero-num modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:40px; font-weight:700; color:#0f172a; line-height:1; letter-spacing:-0.025em;">
						<?php echo wp_kses_post( $last_month_revenue ); ?>
					</p>
					<p class="modern-sans" style="margin:10px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:13px; color:#64748b; line-height:1.4;">
						<?php echo esc_html__( 'Total revenue', 'cartflows' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</td>
</tr>

<!-- Divider -->
<tr>
	<td class="px-lg" style="padding:36px 40px 0 40px;">
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr><td height="1" bgcolor="#eeeae3" style="line-height:1px; font-size:1px;">&nbsp;</td></tr>
		</table>
	</td>
</tr>
