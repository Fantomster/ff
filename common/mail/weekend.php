<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="680"
                   style="max-width: 680px; min-width: 320px; background: #ffffff;">
                <tr>
                    <td align="center" valign="top">
                        <tr>
                            <td align="center" valign="top" height="450"
                                style="background-image: url('https://static.mixcart.ru/banner_img.png');background-repeat: no-repeat;background-position: 0 0;background-size: cover;font-family: 'Open Sans', Arial, sans-serif;">
                                <span style="height: 22px;display: block;width: 100%;"></span>
                                <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 40px;font-weight: bold;text-transform: uppercase;color: #2a2c2e;">
                                    <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 40px;font-weight: bold;text-transform: uppercase;color: #2a2c2e;">
                                        <?= Yii::t('app', 'common.mail.weekend.thank_you', ['ru' => 'Спасибо,']) ?>
                                    </span>
                                    <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 40px;font-weight: bold;text-transform: uppercase;color: #2a2c2e;display: block;width: 100%;">
                                        <?= Yii::t('app', 'common.mail.weekend.that_you_are_with_us', ['ru' => 'Что вы с нами!']) ?>
                                    </span>
                                </font>
                            </td>
                        </tr>
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
            <table cellpadding="0" cellspacing="0" border="0" width="580"
                   style="max-width: 580px; min-width: 320px; background: #ffffff;">
                <tr>
                    <td valign="middle" align="center" height="80" style="font-family: 'Open Sans', Arial, sans-serif;">
                        <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 30px;color: #00B66B;">
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 30px;color: #00B66B;">
                                <?= Yii::t('app', 'common.mail.weekend.what_date', ['ru' => 'Вы знаете, что сегодня за дата?']) ?>
                            </span>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td valign="bottom" height="55" style="font-family: 'Open Sans', Arial, sans-serif;">
                        <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 18px;font-weight: 600;color: #2a2c2e;">
                            <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 18px;font-weight: 600;margin: 0;">
                                <?= Yii::t('app', 'common.mail.weekend.dialog_message_1', ['ru' => '- Что?']) ?>
                            </p>
                            <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 18px;font-weight: 600;margin: 0;">
                                <?= Yii::t('app', 'common.mail.weekend.dialog_message_2', ['ru' => '- Не говорите, что вы забыли!']) ?>
                            </p>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td valign="middle" height="25">
                        <hr align="left" style="border-width: 2px;border-color: #00B66B;border-style: solid;width: 92px;"/>
                    </td>
                </tr>
                <tr>
                    <td valign="middle" height="120"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;">
                        <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;line-height: 37px;color: #2a2c2e;">
                            <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;line-height: 37px;margin: 0;">
                                <?= Yii::t('app', 'common.mail.weekend.together_time', ['ru' => 'Мы вместе уже 604800 секунд! Это ровно одна неделя!']) ?>
                            </p>
                            <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;line-height: 37px;margin: 0;">
                                <?= Yii::t('app', 'common.mail.weekend.together_time_2', ['ru' => 'Неважно, если вы забыли - мы все равно вас ценим!']) ?>
                            </p>
                        </font>
                    </td>
                </tr>
                <tr>
                    <td height="60"
                        style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-style: italic;color: #666769;">
                        <font style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-style: italic;color: #666769;">
                            <span style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-style: italic;color: #666769;">
                                <?= Yii::t('app', 'common.mail.weekend.team', ['ru' => 'Команда']) ?> MixCart
                            </span>
                            <img src="https://static.mixcart.ru/icon_heart.png" alt="heart" width="20" height="17" border="0" style="border:0; outline:none; text-decoration:none;padding-left: 5px;">
                        </font>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?= $this->renderAjax('layouts/mail_footer', ['user' => $user ?? null]) ?>