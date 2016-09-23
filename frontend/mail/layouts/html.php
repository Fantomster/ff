<?php

use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \yii\mail\BaseMessage $content
 */
?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta name="viewport" content="width=device-width" />

        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>

    <body bgcolor="#FFFFFF" style="font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -webkit-text-size-adjust: none; width: 100% !important; background-image: url('http://f-keeper.ru/mail/img/pattern.png'); margin: 0; padding: 0;">
        <?php $this->beginBody() ?>
        <div style="width: 600px; box-shadow: 0px 0px 18px 0px rgba(0, 0, 0, 0.32) !important; margin: 40px auto; padding: 0;">
            <!-- HEADER -->
            <table style="display: table; border-spacing: 0px; width: 100%; margin: 0; padding: 0;">
                <tr style="margin: 0; padding: 0;">
                    <td style="margin: 0; padding: 0;"></td>
                    <td style="display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">

                        <div style="max-width: 600px; display: block; background-color: #fff; margin: 0 auto; padding: 15px;">
                            <table style="display: table; border-spacing: 0px; width: 100%; margin: 0; padding: 0;">
                                <tr style="margin: 0; padding: 0;">
                                    <td style="margin: 0; padding: 0;"><img src="http://f-keeper.ru/mail/img/logo.png" style="max-width: 100%; margin: 0; padding: 0;" /></td>
                                    <td style="margin: 0; padding: 0;">
                                        <ul style="font-weight: normal; font-size: 14px; line-height: 1.6; text-align: right; margin: 0 0 10px; padding: 0;">
                                            <li style="display: inline-block; list-style-position: inside; margin: 8px 0 0 5px; padding: 0;"><a href="#d41d8cd98f00b204e9800998ecf8427e" style="color: #3f3e3e; margin: 0; padding: 0;">Вход / Регистрация</a></li>
                                            <li style="display: inline-block; list-style-position: inside; margin: 8px 0 0 5px; padding: 0;"><a href="#d41d8cd98f00b204e9800998ecf8427e" style="color: #3f3e3e; margin: 0; padding: 0;">О компании</a></li>
                                        </ul>
                                    </td>

                                </tr>
                            </table>
                        </div>

                    </td>
                    <td style="margin: 0; padding: 0;"></td>
                </tr>
            </table><!-- /HEADER -->


            <!-- BODY -->
            <table style="display: table; border-spacing: 0px; width: 100%; margin: 0; padding: 0;">
                <tr style="margin: 0; padding: 0;">
                    <td style="margin: 0; padding: 0;"></td>
                    <td bgcolor="#FFFFFF" style="display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">

                        <div style="max-width: 600px; display: block; background-color: #fff; margin: 0 auto; padding: 15px;">
                            <table style="display: table; border-spacing: 0px; width: 100%; margin: 0; padding: 0;">
                                <tr style="margin: 0; padding: 0;">
                                    <td style="margin: 0; padding: 0;">
                                        <?= $content ?>					
                                    </td>
                                </tr>
                            </table>
                        </div>

                    </td>
                    <td style="margin: 0; padding: 0;"></td>
                </tr>
            </table><!-- /BODY -->
            <!-- FOOTER -->
            <!-- social & contact -->
            <table width="100%" style="display: table; border-spacing: 0px; margin: 0; padding: 0;">
                <tr style="margin: 0; padding: 0;">
                    <td style="margin: 0; padding: 0;">
                        <div style="margin: 0; padding: 0;">
                            <ul style="width: 100%; text-align: center; font-weight: normal; font-size: 14px; line-height: 1.6; list-style-type: none; margin: 0 0 10px; padding: 0;">
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><img src="http://f-keeper.ru/mail/img/phone.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;">8-499-404-10-18</a></li>
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><img src="http://f-keeper.ru/mail/img/mail.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;">info@f-keeper.ru</a></li>
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><img src="http://f-keeper.ru/mail/img/web.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;">http://f-keeper.ru</a></li>
                            </ul>

                            <ul style="width: 100%; text-align: center; font-weight: normal; font-size: 14px; line-height: 1.6; list-style-type: none; margin: 0 0 10px; padding: 0;">
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;"><img src="http://f-keeper.ru/mail/img/yout.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /></a></li>
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;"><img src="http://f-keeper.ru/mail/img/insta.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /></a></li>
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;"><img src="http://f-keeper.ru/mail/img/face.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /></a></li>
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;"><img src="http://f-keeper.ru/mail/img/twit.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /></a></li>
                                <li style="display: inline-block; text-align: center; list-style-position: inside; margin: 0 0 0 5px; padding: 0;"><a href="http://f-keeper.ru/mail/hero.html" style="color: #3f3e3e; margin: 0; padding: 0;"><img src="http://f-keeper.ru/mail/img/vk.png" alt="" style="max-width: 100%; margin: 0; padding: 0 5px 0 0;" /></a></li>
                            </ul>
                        </div>
                        <span style="display: block; clear: both; margin: 0; padding: 0;"></span>	
                    </td>
                </tr>
            </table><!-- /social & contact -->
            <table style="display: table; border-spacing: 0px; width: 100%; clear: both !important; margin: 0; padding: 0;">

                <tr style="margin: 0; padding: 0;">
                    <td style="margin: 0; padding: 0;"></td>
                    <td style="display: block !important; max-width: 600px !important; clear: both !important; margin: 0 auto; padding: 0;">

                        <!-- content -->
                        <div style="max-width: 600px; display: block; background-color: #fff; margin: 0 auto; padding: 15px;">
                            <table style="display: table; border-spacing: 0px; width: 100%; margin: 0; padding: 0;">
                                <tr style="margin: 0; padding: 0;">
                                    <td align="center" style="margin: 0; padding: 0;">
                                        <p style="font-size: 11px; font-weight: normal; line-height: 1.6; margin: 0 0 10px; padding: 0;">
                                            © 2016 F-Keeper — ООО «Онлайн Маркет»
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div><!-- /content -->

                    </td>
                    <td style="margin: 0; padding: 0;"></td>
                </tr>
            </table><!-- /FOOTER -->



        </div>
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>