<?php
/**
 * @var $user \common\models\User
 */
?>
<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="100%"
                   style="max-width: 600px; min-width: 320px; background: #ffffff;">
                <tr>
                    <td align="center" valign="middle" width="28">
                        <a href="tel:84994041018" target="_blank" style="text-decoration: none;">
                            <img src="https://image.ibb.co/niX3xH/icon_phone.png" alt="phone" width="16" height="16"
                                 border="0" style="border:0; outline:none; text-decoration:none; display:block;">
                        </a>
                    </td>
                    <td align="left" valign="middle"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                        <a href="tel:84994041018" target="_blank"
                           style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;text-decoration: none;color: #2a2c2e;">
                            <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><span
                                style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;">8-499-404-10-18</span></font>
                        </a>
                    </td>
                    <td align="center" valign="middle" width="33">
                        <a href="mailto:info@mixcart.ru" target="_blank" style="text-decoration: none;">
                            <img src="https://image.ibb.co/fWh8Wc/icon_mail.png" alt="phone" width="22" height="16"
                                 border="0" style="border:0; outline:none; text-decoration:none; display:block;">
                        </a>
                    </td>
                    <td align="left" valign="middle"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                        <a href="mailto:info@mixcart.ru" target="_blank"
                           style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;text-decoration: none;color: #2a2c2e;">
                            <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><span
                                style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;">info@mixcart.ru</span></font>
                        </a>
                    </td>
                    <td align="center" valign="middle" width="30">
                        <a href="<?= \Yii::$app->urlManagerFrontend->baseUrl ?>" target="_blank" style="text-decoration: none;">
                            <img src="https://image.ibb.co/iib2rc/icon_web.png" alt="phone" width="19" height="19"
                                 border="0" style="border:0; outline:none; text-decoration:none; display:block;">
                        </a>
                    </td>
                    <td align="left" valign="middle"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                        <a href="<?= \Yii::$app->urlManagerFrontend->baseUrl ?>" target="_blank"
                           style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;text-decoration: none;color: #2a2c2e;">
                            <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><span
                                style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><?= \Yii::$app->urlManagerFrontend->baseUrl ?></span></font>
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 200px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="80"
                   style="max-width: 80px; background: #ffffff;">
                <tr>
                    <td align="center" valign="middle" height="70">
                        <a href="https://www.facebook.com/mixcartru/"><img
                                src="https://preview.ibb.co/b0fgKx/fb_logo.png" alt="Facebook Logo" width="30"
                                height="30" style="border:0; outline:none; text-decoration:none;"></a>
                    </td>
                    <td align="center" valign="middle" height="70">
                        <a href="https://www.instagram.com/mixcart_ru/?hl=ru"><img
                                src="https://preview.ibb.co/h2tXsH/instagram_logo.png" alt="Facebook Logo"
                                width="25" height="25"
                                style="border:0; outline:none; text-decoration:none;"></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="500"
                   style="max-width: 500px; min-width: 320px; background: #ffffff;">
                       <?php if (!empty($user) && $user->subscribe == 1): ?>
                    <tr>
                        <td align="center" valign="middle" height="20"
                            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;">
                            <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;margin: 0;">
                                <a href="<?= \Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/site/unsubscribe", "token" => $user->access_token]) ?>"
                                    target="_blank"
                                    style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;text-decoration: underline;">
                               <?= Yii::t('app', 'common.mail.layouts.unsubscribe', ['ru' => 'Отписаться']) ?></a> <?= Yii::t('app', 'common.mail.layouts.from_this_mailing', ['ru' => 'от этой рассылки']) ?>
                            </p>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td align="center" valign="middle" height="70"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;">
                        <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;">
                        <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;">
                            &copy; <?= date('Y') ?> MixCart — ООО «Онлайн Маркет»
                        </span>
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>