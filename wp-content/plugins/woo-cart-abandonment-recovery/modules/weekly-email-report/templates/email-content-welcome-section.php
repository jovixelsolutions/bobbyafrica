<?php
/**
 * Template Name: Email Content Welcome user section
 *
 * @package Cart Abandonment Recovery
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<!-- Welcome user. -->
<tbody data-block-id="3" class="mceWrapper">
	<tr>
	<td style="background-color: transparent; padding-top: 40px" valign="top" align="center" class="mceSectionHeader">
		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 660px" role="presentation">
			<tbody>
				<tr>
					<td style="background-color: #ffffff" valign="top" class="mceWrapperInner">
						<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation" data-block-id="2">
							<tbody>
								<tr class="mceRow">
									<td style="background-position: center; background-repeat: no-repeat; background-size: cover;" valign="top">
										<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
											<tbody>
												<tr>
													<td style="padding-top: 0; padding-bottom: 0;" valign="top" class="mceColumn" id="mceColumnId--12" data-block-id="-12" colspan="12" width="100%">
														<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation">
															<tbody>
																<?php
																	require CARTFLOWS_CA_DIR . 'modules/weekly-email-report/templates/email-header.php';
																?>
																<tr>
																	<td style="background-color: transparent; padding-top: 24px; padding-bottom: 24px; padding-right: 24px; padding-left: 24px; border: 0; border-radius: 0;" valign="top" class="mceImageBlockContainer" align="center" id="b1">
																		<a href="https://cartflows.com/" style="display: block" target="_blank" data-block-id="1">
																			<table align="center" border="0" cellpadding="0" cellspacing="0" width="28.999999999999996%" style="border-collapse: separate; margin: 0; vertical-align: top; max-width: 28.999999999999996%; width: 28.999999999999996%; height: auto;" role="presentation" data-testid="image-1">
																				<tbody>
																					<tr>
																						<td style="border: 0; border-radius: 0; margin: 0;" valign="top">
																							<img alt="" src="<?php echo esc_url( $car_logo ); ?>" width="177.48" height="auto" style="display: block; max-width: 100%; height: auto; border-radius: 0;" class="imageDropZone mceLogo" />
																						</td>
																					</tr>
																				</tbody>
																			</table>
																		</a>
																	</td>
																</tr>
																<tr>
																	<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0;" valign="top" class="mceGutterContainer" id="gutterContainerId-5">
																		<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: separate;" role="presentation">
																			<tbody>
																				<tr>
																					<td style="padding-top: 0; padding-bottom: 0; padding-right: 0; padding-left: 0; border: 0; border-radius: 0;" valign="top" id="b5">
																						<table width="100%" style="border: 0; background-color: transparent; border-radius: 0; border-collapse: separate;">
																							<tbody>
																								<tr>
																									<td style="padding-left: 24px; padding-right: 24px; padding-top: 12px; padding-bottom: 12px;" class="mceTextBlockContainer">
																										<div data-block-id="5" class="mceText" id="d5" style="width: 100%;">
																											<p style="text-align: left;" class="last-child">
																												<?php
																													echo esc_html(
																														sprintf(
																															/* translators: %s: The user name. */
																															__( "Hi %s, here's a quick report on how your recovery flows performed.", 'woo-cart-abandonment-recovery' ),
																															$user_name
																														)
																													);
																													?>
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
<!-- Welcome message End -->
<?php
