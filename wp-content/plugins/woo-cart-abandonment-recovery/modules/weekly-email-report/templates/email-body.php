<?php
/**
 * Template Name: Email Body
 *
 * @package Cart Abandonment Recovery
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
ob_start();
?>

<html
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:v="urn:schemas-microsoft-com:vml"
	xmlns:o="urn:schemas-microsoft-com:office:office"
>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?php echo esc_html__( 'Cart Abandonment Recovery Weekly Report', 'woo-cart-abandonment-recovery' ); ?></title>
		<link rel="preconnect" href="https://fonts.googleapis.com/" />
		<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
		<style>
			img{-ms-interpolation-mode:bicubic}table,td{mso-table-lspace:0pt;mso-table-rspace:0pt}.mceStandardButton,.mceStandardButton td,.mceStandardButton td a{mso-hide:all!important}p,a,li,td,blockquote{mso-line-height-rule:exactly}p,a,li,td,body,table,blockquote{-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}.mcnPreviewText{display:none!important}.bodyCell{margin:0 auto;padding:0;width:100%}.ExternalClass,.ExternalClass p,.ExternalClass td,.ExternalClass div,.ExternalClass span,.ExternalClass font{line-height:100%}.ReadMsgBody,.ExternalClass{width:100%}a[x-apple-data-detectors]{color:inherit!important;text-decoration:none!important;font-size:inherit!important;font-family:inherit!important;font-weight:inherit!important;line-height:inherit!important}body{height:100%;margin:0;padding:0;width:100%;background:#fff}p{margin:0;padding:0}table{border-collapse:collapse}td,p,a{word-break:break-word}h1,h2,h3,h4,h5,h6{display:block;margin:0;padding:0}img,a img{border:0;height:auto;outline:none;text-decoration:none}a[href^="tel"],a[href^="sms"]{color:inherit;cursor:default;text-decoration:none}.mceColumn .mceButtonLink,.mceColumn-1 .mceButtonLink,.mceColumn-2 .mceButtonLink,.mceColumn-3 .mceButtonLink,.mceColumn-4 .mceButtonLink{min-width:30px}div[contenteditable="true"]{outline:0}.mceImageBorder{display:inline-block}.mceImageBorder img{border:0!important}body,#bodyTable{background-color:rgb(254,241,236)}.mceText,.mcnTextContent,.mceLabel{font-family:Lato,"Helvetica Neue",Helvetica,Arial,sans-serif;color:rgb(75,85,99)}.mceText h3,.mceText h4,.mceText p,.mceText ul,.mceText label,.mceText input{margin-bottom:0}.mceSpacing-12 .mceInput+.mceErrorMessage{margin-top:-6px}.mceSpacing-24 .mceInput+.mceErrorMessage{margin-top:-12px}.mceInput{background-color:transparent;border:2px solid rgb(208,208,208);width:60%;color:rgb(77,77,77);display:block}.mceInput[type=radio],.mceInput[type=checkbox]{float:left;margin-right:12px;display:inline;width:auto!important}.mceLabel>.mceInput{margin-bottom:0;margin-top:2px}.mceLabel{display:block}.mceText p,.mcnTextContent p{color:rgb(75,85,99);font-family:Lato,"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:16px;font-weight:400;line-height:1.5;mso-line-height-alt:150%;text-align:center;letter-spacing:0;direction:ltr;margin:0}.mceText h3,.mcnTextContent h3{color:#000;font-family:Lato,"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:20px;font-weight:700;line-height:1.5;mso-line-height-alt:150%;text-align:center;letter-spacing:0;direction:ltr}.mceText h4,.mcnTextContent h4{color:rgb(31,41,55);font-family:Lato,"Helvetica Neue",Helvetica,Arial,sans-serif;font-size:16px;font-weight:700;line-height:1.5;mso-line-height-alt:150%;text-align:center;letter-spacing:0;direction:ltr}.mceText a,.mcnTextContent a{color:rgb(240,100,52);font-style:normal;font-weight:400;text-decoration:underline;direction:ltr}#d9 p,#d9 h1,#d9 h2,#d9 h3,#d9 h4,#d9 ul{text-align:center}
			@media only screen and (max-width:480px){body,table,td,p,a,li,blockquote{-webkit-text-size-adjust:none!important}body{width:100%!important;min-width:100%!important}body.mobile-native{-webkit-user-select:none;user-select:none;transition:transform .2s ease-in;transform-origin:top center}colgroup{display:none}.mceLogo img,.mceImage img,.mceSocialFollowIcon img{height:auto!important}.mceWidthContainer{max-width:660px!important}.mceColumn,.mceColumn-2{display:block!important;width:100%!important}.mceColumn-forceSpan{display:table-cell!important;width:auto!important}.mceColumn-forceSpan .mceButton a{min-width:0!important}.mceReverseStack{display:table;width:100%}.mceColumn-1{display:table-footer-group;width:100%!important}.mceColumn-3{display:table-header-group;width:100%!important}.mceColumn-4{display:table-caption;width:100%!important}.mceKeepColumns .mceButtonLink{min-width:0}.mceBlockContainer,.mceSpacing-24{padding-right:16px!important;padding-left:16px!important}.mceBlockContainerE2E{padding-right:0;padding-left:0}.mceImage,.mceLogo{width:100%!important;height:auto!important}.mceText img{max-width:100%!important}.mceFooterSection .mceText,.mceFooterSection .mceText p{font-size:16px!important;line-height:140%!important}.mceText p{margin:0;font-size:16px!important;line-height:1.5!important;mso-line-height-alt:150%}.mceText h3{font-size:20px!important;line-height:1.5!important;mso-line-height-alt:150%}.mceText h4{font-size:16px!important;line-height:1.5!important;mso-line-height-alt:150%}.bodyCell{padding-left:16px!important;padding-right:16px!important}.mceDividerContainer{width:100%!important}}
			@media only screen and (max-width:640px){.mceClusterLayout td{padding:4px!important}}
		</style>
	</head>
	<body>
		<center>
			<table
				border="0"
				cellpadding="0"
				cellspacing="0"
				height="100%"
				width="100%"
				id="bodyTable"
				style="background-color: rgb(254, 241, 236)"
			>
				<tbody>
					<tr>
						<td class="bodyCell" align="center" valign="top">
							<table
								id="root"
								border="0"
								cellpadding="0"
								cellspacing="0"
								width="100%"
							>
								<?php
									require CARTFLOWS_CA_DIR . 'modules/weekly-email-report/templates/email-content-welcome-section.php'; 

									require CARTFLOWS_CA_DIR . 'modules/weekly-email-report/templates/email-content-section.php';

									require CARTFLOWS_CA_DIR . 'modules/weekly-email-report/templates/email-footer.php';
								?>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</center>
	</body>
</html>


<?php
return ob_get_clean();
