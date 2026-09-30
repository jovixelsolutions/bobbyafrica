<?php
/**
 * Template Name: Email Quick Links + Sign-off (Pro).
 * Included for Pro users from email-body.php.
 *
 * Expected variables (passed from Cartflows_Admin_Report_Emails::get_email_content()):
 * - $quick_links (array)  Map of link label => URL.
 * - $sign_off    (string) Team sign-off name.
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defensive defaults so the template renders without notices if a variable is missing.
$quick_links = isset( $quick_links ) ? $quick_links : array();
$sign_off    = isset( $sign_off ) ? $sign_off : '';
?>
<!-- Quick links -->
<tr>
	<td class="px-lg modern-sans" style="padding:32px 40px 0 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif;">
		<p style="margin:0 0 14px 0; font-size:16px; font-weight:700; color:#0f172a; letter-spacing:-0.01em;">
			<?php echo esc_html__( 'Quick links', 'cartflows' ); ?>
		</p>
		<div style="font-size:0; line-height:0;" class="chip-row">
			<?php foreach ( $quick_links as $label => $url ) : ?>
				<a href="<?php echo esc_url( $url ); ?>" target="_blank" style="display:inline-block; margin:0 8px 8px 0; padding:7px 13px; background-color:#fff4eb; border:1px solid #fde8d8; border-radius:999px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:11px; font-weight:500; color:#c2410c; text-decoration:none; line-height:1; white-space:nowrap;"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>
	</td>
</tr>

<!-- Sign-off -->
<tr>
	<td class="px-lg modern-sans" style="padding:36px 40px 40px 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:14px; color:#475569; line-height:1.6;">
		<p style="margin:0;"><?php echo esc_html__( 'Thanks,', 'cartflows' ); ?></p>
		<p style="margin:2px 0 0 0; color:#0f172a; font-weight:600;"><?php echo esc_html( $sign_off ); ?></p>
	</td>
</tr>
