<?php

use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \yii\mail\BaseMessage $content
 */
?>
<?php $this->beginPage() ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width"/>

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>

    <body bgcolor="#FFFFFF"
          style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; background-image: url('https://app.mixcart.ru/img/pattern.png'); margin: 0; padding: 0;">
    <?php $this->beginBody() ?>
    <div style="width: 600px; box-shadow: 0px 0px 18px 0px rgba(0, 0, 0, 0.32) !important; -webkit-box-shadow:0px 0px 18px 0px rgba(0, 0, 0, 0.32) !important; -moz-box-shadow:0px 0px 18px 0px rgba(0, 0, 0, 0.32) !important; margin: 40px auto; padding: 0;border: 1px solid #e4e4e4;">
        <!-- HEADER -->
        <table cellpadding="0" cellspacing="0" border="0" width="100%"
               style="min-width: 340px;line-height: normal;background: #f0f4f2;padding: 16px 0 0;">
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" border="0" width="680"
                           style="border-radius: 4px 4px 0 0;max-width: 680px; min-width: 320px; background: #ffffff;">
                        <tr>
                            <td align="center" valign="top" style="padding: 0 20px;">
                                <table cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td valign="middle" height="120">
                                            <img src="https://static.mixcart.ru/mix_cart_bezfona.png" alt="logo"
                                                 width="120" border="0"
                                                 style="border:0; outline:none; text-decoration:none; display:block;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td valign="top"
                                            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #8c8f8d;">
                                            <?= $content ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td height="30"></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <!-- FOOTER -->
        <table cellpadding="0" cellspacing="0" border="0" width="100%"
               style="min-width: 340px;line-height: normal;background: #f0f4f2;border-top: 2px solid #f0f4f2;">
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" border="0" width="680"
                           style="background: #ffffff; max-width: 680px; min-width: 320px;">
                        <tr>
                            <td align="center" valign="top">
                                <table cellspacing="0" cellspacing="0" width="500"
                                       style="background: #ffffff; max-width: 500px;">
                                    <tr>
                                        <td height="20"></td>
                                    </tr>
                                    <tr>
                                        <td align="center" valign="middle" width="28">
                                            <a href="tel:84994041018" target="_blank" style="text-decoration: none;">
                                                <img src="https://mixcart.ru/img-host/phone.png" alt="Phone" width="18"
                                                     height="18" border="0"
                                                     style="border:0; outline:none; text-decoration:none; display:block;">
                                            </a>
                                        </td>
                                        <td align="left" valign="middle"
                                            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                                            <a href="tel:84994041018" target="_blank"
                                               style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;text-decoration: none;color: #2a2c2e;">
                                                <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><?= Yii::t('app', 'common.mail.layouts.phone', ['ru' => '8-499-404-10-18']) ?></span>
                                            </a>
                                        </td>
                                        <td align="center" valign="middle" width="33">
                                            <a href="mailto:info@mixcart.ru" target="_blank"
                                               style="text-decoration: none;">
                                                <img src="https://mixcart.ru/img-host/mail.png" alt="Mail" width="24"
                                                     height="18" border="0"
                                                     style="border:0; outline:none; text-decoration:none; display:block;">
                                            </a>
                                        </td>
                                        <td align="left" valign="middle"
                                            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                                            <a href="mailto:info@mixcart.ru" target="_blank"
                                               style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;text-decoration: none;color: #2a2c2e;">
                                                <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><?= Yii::t('app', 'common.mail.layouts.infoemail') ?></span>
                                            </a>
                                        </td>
                                        <td align="center" valign="middle" width="30">
                                            <a href="https://mixcart.ru/" target="_blank"
                                               style="text-decoration: none;">
                                                <img src="https://mixcart.ru/img-host/web.png" alt="Web" width="21"
                                                     height="21" border="0"
                                                     style="border:0; outline:none; text-decoration:none; display:block;">
                                            </a>
                                        </td>
                                        <td align="left" valign="middle"
                                            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                                            <a href="<?= Yii::$app->params['staticUrl'][Yii::$app->language]['home'] ?>"
                                               target="_blank"
                                               style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;text-decoration: none;color: #2a2c2e;">
                                                <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;"><?= Yii::$app->params['shortHome'] ?></span>
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" width="100%"
               style="min-width: 200px;line-height: normal;background: #f0f4f2;">
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" border="0" width="680" height="70"
                           style="max-width: 680px; background: #ffffff;">
                        <tr>
                            <td align="center" valign="middle">
                                <table cellspacing="0" cellspacing="0" width="70" style="max-width: 70px;">
                                    <tr>
                                        <td align="left">
                                            <a href="https://www.facebook.com/mixcartru/" target="_blank"><img
                                                        src="https://mixcart.ru/img-host/facebook.png"
                                                        alt="Facebook Logo" width="25" height="25"
                                                        style="border:0; outline:none; text-decoration:none;"></a>
                                        </td>
                                        <td align="right">

                                            <a href="https://www.instagram.com/mixcart_ru/?hl=ru" target="_blank"><img
                                                        src="https://mixcart.ru/img-host/instagram.png"
                                                        alt="Facebook Logo" width="25" height="25"
                                                        style="border:0; outline:none; text-decoration:none;"></a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <table cellpadding="0" cellspacing="0" border="0" width="100%"
               style="min-width: 340px;line-height: normal;background: #f0f4f2;padding: 0 0 16px;">
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" border="0" width="680"
                           style="border-radius: 0 0 4px 4px;background: #ffffff; max-width: 680px; min-width: 320px;">
                        <tr>
                            <td align="center" valign="middle" height="20"
                                style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;">
                                <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;"><a
                                            href="<?= \Yii::$app->urlManagerFrontend->baseUrl ?>/" target="_blank"
                                            style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;text-decoration: underline;"><?= Yii::t('app', 'common.mail.layouts.unsubscribe', ['ru' => 'Отписаться']) ?></a> <?= Yii::t('app', 'common.mail.layouts.from_this_mailing', ['ru' => 'от этой рассылки']) ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" valign="middle" height="70"
                                style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;">
                                <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 14px;font-weight: 300;color: #6a6a6a;">&copy; 2018 MixCart — ООО «Онлайн Маркет»</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <?php $this->endBody() ?>
    </body>
    </html>
<?php $this->endPage() ?>