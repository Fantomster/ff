<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
        <title>MixCart</title>
        <!--[if mso]>
        <style type="text/css">
            p {
                margin-top: 10px;
                margin-bottom: 10px;
            }
        </style>
        <![end if]-->
    </head>
    <body>
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
            <tr>
                <td align="center" valign="top">
                    <table cellpadding="0" cellspacing="0" border="0" width="680" style="max-width: 580px; min-width: 320px; background: #ffffff;">
                        <tr>
                            <td style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.demo.greetings', ['ru' => 'Здравствуйте!']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.demo.paragraph1', ['ru' => 'Я уверена, что наш способ управлять закупками — самый удобный и простой. Но не верьте словам, лучше попробуйте сами или посмотрите!']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.demo.paragraph2', ['ru' => 'Я предлагаю провести бесплатную демонстрацию решения на вашей территории - займет 10-15 минут и рассказать, как MixCart будет полезен в Вашем конкретном случае.']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.demo.paragraph3', ['ru' => 'Сообщите, в какое время с Вами можно связаться и обговорить детали.']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.demo.farewell', ['ru' => 'Отличного дня,']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;"><?= Yii::t('app', 'common.mail.demo.manager_name', ['ru' => 'Ольга Захряпина']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;"><?= Yii::t('app', 'common.mail.demo.manager_title', ['ru' => 'Менеджер по развитию']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;">
                                    <?php if ($user->language == 'ru') { ?>
                                        <a href="https://mixcart.ru/" target="_blank" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">https://mixcart.ru/</a>
                                    <?php } else { ?>
                                        <a href="https://mix-cart.com/" target="_blank" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">https://mix-cart.com/</a>
                                    <?php } ?>
                                </p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;margin: 0;"><?= Yii::t('app', 'common.mail.demo.manager_phone', ['ru' => '8-499-404-10-18']) ?></p>
                                <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;"><?= Yii::t('app', 'common.mail.demo.ps', ['ru' => 'P.S. Мы постоянно добавляем новые возможности, так что следите за новостями в']) ?> <a href="https://blog.mixcart.ru/" target="_blank" style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                                        <?= Yii::t('app', 'common.mail.demo.link_text', ['ru' => 'блоге']) ?></a>!</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>