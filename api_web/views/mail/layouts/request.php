<?php

use yii\helpers\Html;
use yii\helpers\Url;
/**
 * @var \yii\web\View $this
 * @var \yii\mail\BaseMessage $content
 */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="utf-8"> <!-- utf-8 works for most cases -->
		<meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
		<title>MixCart</title> <!-- The title tag shows in email notifications, like Android 4.4. -->
		<?php $this->head() ?>
                <!-- Web Font / @font-face : BEGIN -->
		<!-- NOTE: If web fonts are not required, lines 9 - 26 can be safely removed. -->
		
		<!-- Desktop Outlook chokes on web font references and defaults to Times New Roman, so we force a safe fallback font. -->
		<!--[if mso]>
		<style>
			* {
				font-family: 'Open Sans', sans-serif !important;
			}
		</style>
		<![endif]-->
		
		<!-- All other clients get the webfont reference; some will render the font and others will silently fail to the fallbacks. More on that here: http://stylecampaign.com/blog/2015/02/webfont-support-in-email/ -->
		<!--[if !mso]><!-->
		<!-- insert web font reference, eg: <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,800" rel="stylesheet"> -->
		<!--<![endif]-->
		<!-- Web Font / @font-face : END -->
		
		<!-- CSS Reset -->
		<style type="text/css">
				/* What it does: Remove spaces around the email design added by some email clients. */
				/* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
		html,
		body {
			margin: 0 auto !important;
		padding: 0 !important;
		height: 100% !important;
		width: 100% !important;
		}
		
		/* What it does: Stops email clients resizing small text. */
		* {
		-ms-text-size-adjust: 100%;
		-webkit-text-size-adjust: 100%;
		}
		
		/* What is does: Centers email on Android 4.4 */
		div[style*="margin: 16px 0"] {
		margin:0 !important;
		}
		
		/* What it does: Stops Outlook from adding extra spacing to tables. */
		table,
		td {
		mso-table-lspace: 0pt !important;
		mso-table-rspace: 0pt !important;
		}
		
		/* What it does: Fixes webkit padding issue. Fix for Yahoo mail table alignment bug. Applies table-layout to the first 2 tables then removes for anything nested deeper. */
		table {
		border-spacing: 0 !important;
		border-collapse: collapse !important;
		table-layout: fixed !important;
		Margin: 0 auto !important;
		}
		table table table {
		table-layout: auto;
		}
		
		/* What it does: Uses a better rendering method when resizing images in IE. */
		img {
		-ms-interpolation-mode:bicubic;
		}
		
		/* What it does: A work-around for iOS meddling in triggered links. */
		.mobile-link--footer a,
		a[x-apple-data-detectors] {
		color:inherit !important;
		text-decoration: underline !important;
		}
		
		</style>
		
		<!-- Progressive Enhancements -->
		<style>
		
		/* What it does: Hover styles for buttons */
		.button-td,
		.button-a {
		transition: all 100ms ease-in;
		}
		.button-td:hover,
		.button-a:hover {
		background: #33363b !important;
		border-color: #33363b !important;
		}
		/* Media Queries */
		@media screen and (max-width: 600px) {
		.email-container {
		width: 100% !important;
		margin: auto !important;
		}
		/* What it does: Forces elements to resize to the full width of their container. Useful for resizing images beyond their max-width. */
		.fluid,
		.fluid-centered {
		max-width: 100% !important;
		height: auto !important;
		Margin-left: auto !important;
		Margin-right: auto !important;
		}
		/* And center justify these ones. */
		.fluid-centered {
		Margin-left: auto !important;
		Margin-right: auto !important;
		}
		/* What it does: Forces table cells into full-width rows. */
		.stack-column,
		.stack-column-center {
		display: block !important;
		width: 100% !important;
		max-width: 100% !important;
		direction: ltr !important;
		}
		/* And center justify these ones. */
		.stack-column-center {
		text-align: center !important;
		}
		
		/* What it does: Generic utility class for centering. Useful for images, buttons, and nested tables. */
		.center-on-narrow {
		text-align: center !important;
		display: block !important;
		Margin-left: auto !important;
		Margin-right: auto !important;
		float: none !important;
		}
		table.center-on-narrow {
		display: inline-block !important;
		}
		
		}
		</style>
	</head>
	<body bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; background-image: url('https://app.mixcart.ru/img/pattern.png'); margin: 0; padding: 0;">
            <?php $this->beginBody() ?>
            <div style="width: 600px; box-shadow: 0px 0px 18px 0px rgba(0, 0, 0, 0.32) !important; -webkit-box-shadow:0px 0px 18px 0px rgba(0, 0, 0, 0.32) !important; -moz-box-shadow:0px 0px 18px 0px rgba(0, 0, 0, 0.32) !important; margin: 40px auto; padding: 15px;border: 1px solid #e4e4e4;background:#ffffff">
		<!-- Visually Hidden Preheader Text : BEGIN -->
		<div style="display:none;font-size:1px;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;mso-hide:all;font-family: sans-serif;">
			
		</div>
		<!-- Visually Hidden Preheader Text : END -->
		<!-- Email Header : BEGIN -->
        
                <?= $content ?>                        
                <!-- Email Footer : END -->
		</div>
            <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>