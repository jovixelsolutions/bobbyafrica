<?php
/**
 * Template Name: Email Content Section
 *
 * @package Cart Abandonment Recovery
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tbody data-block-id="7" class="mceWrapper">
	<tr>
	<td style="background-color: transparent" valign="top" align="center" class="mceSectionBody">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 660px" role="presentation">
			<tbody>
				<tr>
					<td style="background-color: #ffffff" valign="top" class="mceWrapperInner">
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" data-block-id="6">
							<tbody>
								<tr class="mceRow">
									<td style="background-position: center; background-repeat: no-repeat; background-size: cover;" valign="top">
										<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
											<tbody>
												<tr>
													<td style="padding-top: 0; padding-bottom: 0;" valign="top" class="mceColumn" id="mceColumnId--13" data-block-id="-13" colspan="12" width="100%">
														<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
								<tbody>
									<?php
										require CARTFLOWS_CA_DIR . 'modules/weekly-email-report/templates/email-recovery-stat.php';
									?>

									<tr>
										<td valign="top" class="mceGutterContainer" id="gutterContainerId-39">
											<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate;" role="presentation">
											<tbody>
												<tr>
												<td style="padding-top: 12px; padding-bottom: 34px; padding-right: 0; padding-left: 0; border: 0; border-radius: 0;" valign="top" class="mceLayoutContainer" id="b39">
													<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" data-block-id="39" id="section_2a41c321110a465df6011183bbcddf40" class="mceLayout">
													<tbody>
														<tr class="mceRow">
														<td style="background-position: center; background-repeat: no-repeat; background-size: cover;" valign="top">
															<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
															<tbody>
																<tr>
																<td valign="top" class="mceColumn" id="mceColumnId--17" data-block-id="-17" colspan="12" width="100%">
																	<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
																	<tbody>
																		<tr>
																		<td style="border: 0; border-radius: 0;" valign="top" align="center" id="b-9">
																			<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" data-block-id="-9">
																			<tbody>
																				<tr class="mceRow">
																				<td style="background-position: center; background-repeat: no-repeat; background-size: cover;" valign="top">
																					<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
																					<tbody>
																						<tr>
																						<td valign="top" class="mceColumn" id="mceColumnId--22" data-block-id="-22" colspan="12" width="100%">
																							<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
																							<tbody>
																								<tr>
																								<td style="border: 0; border-radius: 0;" valign="top" id="b44">
																									<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" data-block-id="44">
																									<tbody>
																										<tr class="mceRow">
																										<td style="background-position: center; background-repeat: no-repeat; background-size: cover;" valign="top">
																											<table border="0" cellpadding="0" cellspacing="24" width="100%" role="presentation">
																											<tbody>
																												<tr>
																												<td style="padding-top: 0; padding-bottom: 0;" valign="top" class="mceColumn" id="mceColumnId-41" data-block-id="41" colspan="12" width="100%">
																													<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
																													<tbody>
																														<tr>
																														<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0;" valign="top" class="mceGutterContainer" id="gutterContainerId-36">
																															<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate;" role="presentation">
																															<tbody>
																																<tr>
																																<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0; border: 0; border-radius: 0;" valign="top" id="b36">
																																	<table width="100%" style="border: 0; background-color: transparent; border-radius: 0; border-collapse: separate;">
																																	<tbody>
																																		<tr>
																																		<td style="padding-left: 24px; padding-right: 24px; padding-top: 12px; padding-bottom: 12px;" class="mceTextBlockContainer">
																																			<div data-block-id="36" class="mceText" id="d36" style="width: 100%;">
																																			<h3 style="text-align: left;">
																																				<span style="font-size: 16px;"><?php echo esc_html__( 'Top Wins &amp; Highlights:', 'woo-cart-abandonment-recovery' ); ?></span>
																																			</h3>
																																			<ul class="last-child">
																																				<li style="text-align: left;">
																																				<p class="" style="text-align: left;">
																																					<strong><?php echo esc_html__( 'Top recovered product:', 'woo-cart-abandonment-recovery' ); ?></strong>
																																						<?php
																																						echo sprintf(
																																							/* translators: %1$s: product name, %2$s: total frequency  %3$s: total amount*/
																																							esc_html__( '"%1$s" - %2$s recovered orders → %3$s recovered', 'woo-cart-abandonment-recovery' ),
																																							esc_html( $report_details['top_recovered_product']['product_name'] ),
																																							esc_html( $report_details['top_recovered_product']['total_frequency'] ),
																																							wp_kses_post( wc_price( $report_details['top_recovered_product']['total_amount'] ) )
																																						);
																																						?>
																																				</p>
																																				</li>
																																				<?php
																																				if ( isset( $report_details['top_performing_template'] ) && ! empty( $report_details['top_performing_template'] ) ) {
																																					ob_start();
																																					?>
																																						<li style="text-align: left;">
																																						<p class="" style="text-align: left;">
																																							<strong><?php echo esc_html__( 'Best campaign: ', 'woo-cart-abandonment-recovery' ); ?></strong>
																																							<?php
																																							echo sprintf(
																																							/* translators: %1$s: template name, %2$s: recovery rate  %3$s: sent count*/
																																								esc_html__( '"%1$s" - recovery rate %2$s (sent to %3$s carts)', 'woo-cart-abandonment-recovery' ),
																																								esc_html( $report_details['top_performing_template']['template_name'] ),
																																								esc_html( $report_details['top_performing_template']['recovery_rate'] ) . '%',
																																								esc_html( $report_details['top_performing_template']['sent_count'] )
																																							);
																																							?>
																																						</p>
																																						</li>
																																						<?php
																																							echo wp_kses_post( ob_get_clean() );
																																				}
																																				?>
																																				<!-- <li style="text-align: left;">
																																				<p class="" style="text-align: left;">
																																					<strong>Channel performance:</strong>
																																				</p>
																																				<ul>
																																					<li style="text-align: left;">
																																					<p class="" style="text-align: left;">
																																						Email recovered <strong>78%</strong> of recovered revenue;
																																					</p>
																																					</li>
																																					<li style="text-align: left;">
																																					<p class="" style="text-align: left;">
																																						SMS recovered <strong>22%</strong>.
																																					</p>
																																					</li>
																																				</ul>
																																				</li> -->
																																			</ul>
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
																														<tr>
																														<td style="background-color: transparent; padding-top: 12px; padding-bottom: 12px; padding-right: 24px; padding-left: 24px; border: 0; border-radius: 0;" valign="top" class="mceDividerBlockContainer" id="b61">
																															<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: transparent; width: 100%;" role="presentation" class="mceDividerContainer" data-block-id="61">
																															<tbody>
																																<tr>
																																<td style="min-width: 100%; border-top-width: 2px; border-top-style: solid; border-top-color: #ebebeb; line-height: 0; font-size: 0;" valign="top" class="mceDividerBlock">
																																	&nbsp;
																																</td>
																																</tr>
																															</tbody>
																															</table>
																														</td>
																														</tr>
																														<tr>
																														<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0;" valign="top" class="mceGutterContainer" id="gutterContainerId-57">
																															<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate;" role="presentation">
																															<tbody>
																																<tr>
																																<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0; border: 0; border-radius: 0;" valign="top" id="b57">
																																	<table width="100%" style="border: 0; background-color: transparent; border-radius: 0; border-collapse: separate;">
																																	<tbody>
																																		<tr>
																																		<td style="padding-left: 24px; padding-right: 24px; padding-top: 12px; padding-bottom: 12px;" class="mceTextBlockContainer">
																																			<div data-block-id="57" class="mceText" id="d57" style="width: 100%;">
																																			<h3 style="text-align: left;">
																																				<span style="font-size: 16px;">Quick Links:</span>
																																			</h3>
																																			<ul class="last-child">
																																				<li>
																																				<p class="" style="text-align: left;">
																																					<a href="<?php echo esc_url( $dashboard_link ); ?>" target="_blank">View Full Recovery Report</a>
																																				</p>
																																				</li>
																																				<li>
																																				<p style="text-align: left;">
																																					<a href="<?php echo esc_url( $followup_report_page_link ); ?>" target="_blank">Edit Recovery Flow</a>
																																				</p>
																																				</li>
																																				<li>
																																				<p style="text-align: left;">
																																					<a href="<?php echo esc_url( $sms_recovery_link ); ?>" target="_blank">Add SMS Recovery</a>
																																				</p>
																																				</li>
																																				<li>
																																				<p style="text-align: left;">
																																					<a href="https://cartflows.com/support" target="_blank">Get Help</a>
																																				</p>
																																				</li>
																																				<li>
																																				<p style="text-align: left;">
																																					<a href="https://cartflows.com/products-to-scale-growth" target="_blank">Discover More Products</a>
																																				</p>
																																				</li>
																																			</ul>
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
																														<tr>
																														<td style="background-color: transparent; padding-top: 12px; padding-bottom: 12px; padding-right: 24px; padding-left: 24px; border: 0; border-radius: 0;" valign="top" class="mceDividerBlockContainer" id="b59">
																															<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: transparent; width: 100%;" role="presentation" class="mceDividerContainer" data-block-id="59">
																															<tbody>
																																<tr>
																																<td style="min-width: 100%; border-top-width: 2px; border-top-style: solid; border-top-color: #ebebeb; line-height: 0; font-size: 0;" valign="top" class="mceDividerBlock">
																																	&nbsp;
																																</td>
																																</tr>
																															</tbody>
																															</table>
																														</td>
																														</tr>
																														<tr>
																														<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0;" valign="top" class="mceGutterContainer" id="gutterContainerId-58">
																															<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate;" role="presentation">
																															<tbody>
																																<tr>
																																<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0; border: 0; border-radius: 0;" valign="top" id="b58">
																																	<table width="100%" style="border: 0; background-color: transparent; border-radius: 0; border-collapse: separate;">
																																	<tbody>
																																		<tr>
																																		<td style="padding-left: 24px; padding-right: 24px; padding-top: 12px; padding-bottom: 12px;" class="mceTextBlockContainer">
																																			<div data-block-id="58" class="mceText" id="d58" style="width: 100%;">
																																			<p style="text-align: left;">
																																				Thanks,
																																			</p>
																																			<p style="text-align: left;" class="last-child">
																																				The Cart Abandonment Recovery Team
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
																													</tbody>
																													</table>
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
																							</tbody>
																							</table>
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
																	</tbody>
																	</table>
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
											</tbody>
											</table>
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
		</tbody>
	</table>
		<!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]-->
	</td>
	</tr>
</tbody>
