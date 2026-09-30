<?php
/**
 * Template Name: Email body.
 *
 * Expected variables (passed from Cartflows_Admin_Report_Emails::get_email_content()):
 * - $preheader (string) Preheader text shown in inbox previews.
 *
 * @package CartFlows
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Defensive defaults so the template renders without notices if a variable is missing.
$preheader = isset( $preheader ) ? $preheader : '';

ob_start();
?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="color-scheme" content="light" />
		<meta name="supported-color-schemes" content="light" />
		<title><?php echo esc_html__( 'CartFlows Weekly Report', 'cartflows' ); ?></title>
		<!--[if gte mso 15]>
		<xml>
			<o:OfficeDocumentSettings>
				<o:AllowPNG/>
				<o:PixelsPerInch>96</o:PixelsPerInch>
			</o:OfficeDocumentSettings>
		</xml>
		<![endif]-->
		<style>
			body, table, td, p, a, li, blockquote { -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
			body { margin: 0; padding: 0; background: #f4f1ec; }
			table { border-collapse: collapse; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
			img { border: 0; display: block; -ms-interpolation-mode: bicubic; }
			p { margin: 0; padding: 0; }
			a { color: #f06434; text-decoration: none; }
			a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; }

			.modern-sans {
				font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Helvetica Neue', Helvetica, Arial, sans-serif;
			}

			@media only screen and (max-width: 640px) {
				.container { width: 100% !important; max-width: 100% !important; }
				.px-lg { padding-left: 24px !important; padding-right: 24px !important; }
				.stack { display: block !important; width: 100% !important; padding: 0 0 12px 0 !important; }
				.stack-last { padding-bottom: 0 !important; }
				.hero-num { font-size: 32px !important; }
				.kpi-num { font-size: 26px !important; }
				.chip-row { text-align: center !important; }
			}
		</style>
	</head>
	<body style="margin:0; padding:0; background-color:#f4f1ec;">

		<div style="display:none; max-height:0; overflow:hidden; mso-hide:all; font-size:1px; line-height:1px; color:#f4f1ec;">
			<?php echo esc_html( $preheader ); ?>
		</div>

		<center style="width:100%; background-color:#f4f1ec;">
			<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f4f1ec;">
				<tr>
					<td align="center" valign="top" style="padding:36px 16px;">

						<!--[if (gte mso 9)|(IE)]><table align="center" border="0" cellspacing="0" cellpadding="0" width="640"><tr><td><![endif]-->
						<table role="presentation" class="container" align="center" border="0" cellpadding="0" cellspacing="0" width="640" style="max-width:640px; background-color:#ffffff; border-radius:16px; overflow:hidden;">
							<?php
								require CARTFLOWS_DIR . 'modules/email-report/templates/email-header.php';
								require CARTFLOWS_DIR . 'modules/email-report/templates/email-content-section.php';
								require CARTFLOWS_DIR . 'modules/email-report/templates/email-stat-content.php';

							if ( ! _is_cartflows_pro() ) {
								include CARTFLOWS_DIR . 'modules/email-report/templates/email-cf-pro-block.php';
							} else {
								include CARTFLOWS_DIR . 'modules/email-report/templates/email-other-product-block.php';
							}
							?>
						</table>
						<!--[if (gte mso 9)|(IE)]></td></tr></table><![endif]-->

						<?php require CARTFLOWS_DIR . 'modules/email-report/templates/email-footer.php'; ?>

					</td>
				</tr>
			</table>
		</center>

	</body>
</html>
<?php
return ob_get_clean();
