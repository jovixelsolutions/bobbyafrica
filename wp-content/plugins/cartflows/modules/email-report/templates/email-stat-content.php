<?php
/**
 * Template Name: Email stats block — Top wins + One thing to try.
 *
 * Expected variables (passed from Cartflows_Admin_Report_Emails::get_email_content()):
 * - $top_wins   (array) Highlight bullets (empty on Free / no data).
 * - $weekly_tip (array) Rotating tip with 'headline' and 'body' keys.
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defensive defaults so the template renders without notices if a variable is missing.
$top_wins   = isset( $top_wins ) ? $top_wins : array();
$weekly_tip = isset( $weekly_tip ) ? $weekly_tip : array(
	'headline' => '',
	'body'     => '',
);
?>

<?php if ( ! empty( $top_wins ) ) : ?>
<!-- Top wins & highlights -->
<tr>
	<td class="px-lg modern-sans" style="padding:32px 40px 0 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif;">
		<p style="margin:0; font-size:16px; font-weight:700; color:#0f172a; letter-spacing:-0.01em;">
			<?php echo esc_html__( 'Top wins & highlights', 'cartflows' ); ?>
		</p>
	</td>
</tr>
<tr>
	<td class="px-lg modern-sans" style="padding:14px 40px 0 40px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:14px; color:#334155; line-height:1.5;">
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
			<?php
			$last_index = count( $top_wins ) - 1;
			foreach ( $top_wins as $index => $win ) :
				$label    = isset( $win['label'] ) ? $win['label'] : '';
				$detail   = isset( $win['detail'] ) ? $win['detail'] : '';
				$is_last  = ( $index === $last_index );
				$text_pad = $is_last ? '0' : '0 0 8px 0';
				?>
				<tr>
					<td valign="middle" style="padding:0 6px 0 0;">
						<span style="display:block; width:6px; height:6px; background-color:#f06434; border-radius:50%; line-height:6px; font-size:6px;"></span>
					</td>
					<td valign="middle" style="padding:<?php echo esc_attr( $text_pad ); ?>;">
						<?php if ( '' !== $label ) : ?>
							<strong style="color:#0f172a;"><?php echo esc_html( $label ); ?></strong>
						<?php endif; ?>
						<?php echo wp_kses_post( $detail ); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</td>
</tr>
<?php endif; ?>

<?php if ( ! empty( $weekly_tip['headline'] ) ) : ?>
<!-- One thing to try (callout card) -->
<tr>
	<td class="px-lg" style="padding:32px 40px 0 40px;">
		<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#fff4eb; border-radius:12px;">
			<tr>
				<td style="padding:24px 24px 24px 28px; border-left:3px solid #f06434; border-top-left-radius:12px; border-bottom-left-radius:12px;">
					<p class="modern-sans" style="margin:0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:11px; font-weight:700; color:#c2410c; text-transform:uppercase; letter-spacing:1.4px;">
						<?php echo esc_html__( 'One thing to try this week', 'cartflows' ); ?>
					</p>
					<p class="modern-sans" style="margin:10px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:16px; font-weight:700; color:#0f172a; line-height:1.4; letter-spacing:-0.01em;">
						<?php echo esc_html( $weekly_tip['headline'] ); ?>
					</p>
					<p class="modern-sans" style="margin:8px 0 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Helvetica Neue',Helvetica,Arial,sans-serif; font-size:14px; color:#475569; line-height:1.6;">
						<?php echo wp_kses_post( $weekly_tip['body'] ); ?>
					</p>
				</td>
			</tr>
		</table>
	</td>
</tr>
<?php endif; ?>
