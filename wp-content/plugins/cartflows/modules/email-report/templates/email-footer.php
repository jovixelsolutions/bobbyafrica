<?php
/**
 * Template Name: Email Footer — social icons + auto-generated note + unsubscribe.
 * Rendered outside the white card.
 *
 * Expected variables (passed from Cartflows_Admin_Report_Emails::get_email_content()):
 * - $facebook_icon    (string) Absolute URL of the Facebook icon image.
 * - $twitter_icon     (string) Absolute URL of the Twitter icon image.
 * - $youtube_icon     (string) Absolute URL of the YouTube icon image.
 * - $store_name       (string) Store name pulled from get_bloginfo( 'name' ).
 * - $unsubscribe_link (string) Unsubscribe URL with HMAC token.
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defensive defaults so the template renders without notices if a variable is missing.
$facebook_icon    = isset( $facebook_icon ) ? $facebook_icon : '';
$twitter_icon     = isset( $twitter_icon ) ? $twitter_icon : '';
$youtube_icon     = isset( $youtube_icon ) ? $youtube_icon : '';
$store_name       = isset( $store_name ) ? $store_name : '';
$unsubscribe_link = isset( $unsubscribe_link ) ? $unsubscribe_link : '';
?>
<table role="presentation" class="container" align="center" border="0" cellpadding="0" cellspacing="0" width="640" style="max-width:640px;">

	<!-- Social icons -->
	<tr>
		<td align="center" style="padding:24px 24px 0 24px;">
			<table role="presentation" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td style="padding:0 8px;">
						<a href="<?php echo esc_url( 'https://www.facebook.com/groups/cartflows/' ); ?>" target="_blank">
							<img alt="<?php echo esc_attr__( 'Facebook', 'cartflows' ); ?>" src="<?php echo esc_url( $facebook_icon ); ?>" width="22" height="22" style="display:block; border:0;" />
						</a>
					</td>
					<td style="padding:0 8px;">
						<a href="<?php echo esc_url( 'https://twitter.com/cartflows' ); ?>" target="_blank">
							<img alt="<?php echo esc_attr__( 'Twitter', 'cartflows' ); ?>" src="<?php echo esc_url( $twitter_icon ); ?>" width="22" height="22" style="display:block; border:0;" />
						</a>
					</td>
					<td style="padding:0 8px;">
						<a href="<?php echo esc_url( 'https://www.youtube.com/channel/UCEdXT5pEI_Vbd5te5v7sOpQ' ); ?>" target="_blank">
							<img alt="<?php echo esc_attr__( 'YouTube', 'cartflows' ); ?>" src="<?php echo esc_url( $youtube_icon ); ?>" width="22" height="22" style="display:block; border:0;" />
						</a>
					</td>
				</tr>
			</table>
		</td>
	</tr>

	<!-- Footer auto-generated text -->
	<tr>
		<td align="center" class="modern-sans" style="padding:18px 24px 8px 24px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; color:#94a3b8; line-height:1.7;">
			<?php
			echo sprintf(
				/* translators: %s: store name with link to home URL. */
				wp_kses_post( __( 'This email was auto-generated and sent from %s.', 'cartflows' ) ),
				'<a href="' . esc_url( home_url() ) . '" target="_blank" style="color:#94a3b8; text-decoration:underline;">' . esc_html( $store_name ) . '</a>'
			);
			?>
		</td>
	</tr>

	<!-- Unsubscribe -->
	<tr>
		<td align="center" class="modern-sans" style="padding:0 24px 24px 24px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:12px; color:#94a3b8; line-height:1.7;">
			<a href="<?php echo esc_url( $unsubscribe_link ); ?>" target="_blank" rel="noopener" style="color:#94a3b8; text-decoration:underline;">
				<?php echo esc_html__( 'Unsubscribe', 'cartflows' ); ?>
			</a>
		</td>
	</tr>

</table>
