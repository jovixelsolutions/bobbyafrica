<?php
/**
 * Template Name: Email Header
 *
 * @package Cart Abandonment Recovery
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<tr>
	<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0;" valign="top" class="mceGutterContainer" id="gutterContainerId-45">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate;" role="presentation">
			<tbody>
				<tr>
					<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0; border: 0; border-radius: 0;" valign="top" id="b45">
						<table width="100%" style="border: 0; background-color: #f06434; border-radius: 0; border-collapse: separate;">
							<tbody>
								<tr>
									<td style="padding-left: 24px; padding-right: 24px; padding-top: 10px; padding-bottom: 10px;" class="mceTextBlockContainer">
										<div data-block-id="45" class="mceText" id="d45" style="width: 100%;">
											<p class="last-child">
												<span style="color: rgb(255, 255, 255);"><span style="font-size: 14px;">
													<?php
														echo sprintf(
															/* translators: %s: Total revenue. */
															esc_html__(
																'Your Weekly Cart Abandonment Recovery Report - %s Recovered',
																'woo-cart-abandonment-recovery'
															),
															wp_kses_post( wc_price( $report_details['recovered_revenue'] ) )
														);
														?>
												</span></span>
											</p>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	</td>
</tr>

<?php
