<?php

use yii\helpers\Url;

/**
 * @var $userToken \common\models\UserToken
 */
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%"
       style="background: #ffffff; min-width: 340px; font-size: 1px; line-height: normal;">
    <tbody>
    <tr>
        <td align="center" valign="top">
            <table cellpadding="0" cellspacing="0" border="0" width="580"
                   style="max-width: 580px; min-width: 320px; background: #ffffff;padding-top: 30px;padding-bottom: 30px;">
                <tbody>
                <tr>
                    <td style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                            <?= Yii::t('app', 'common.mail.confirm_email.hello', ['ru' => 'Здравствуйте,']) ?>
                        </p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;color: #2a2c2e;">
                            <?= Yii::t('app', 'common.mail.confirm_email.full_text_mobile', [
                                'ru' => "Благодарим Вас за регистрацию в MixCart! Перед началом работы необходимо подтвердить этот адрес электронной почты. Для этого введите PIN <b>{pin}</b> в мобильном приложении или пройдите по ссылке:",
                                'pin' => $userToken->pin
                            ]) ?>
                        </p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-weight: 600;text-decoration: underline;color: #00b66b;">
                            <a href="<?= \Yii::$app->urlManagerFrontEnd->createUrl(["/user/confirm", "type" => $user->organization->type_id, "token" => $userToken->token]) ?>"
                               target="_blank"
                               style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-weight: 600;text-decoration: underline;color: #00b66b;">
                                <?= Yii::t('app', 'common.mail.confirm_email.confirm', ['ru' => 'Подтвердить']) ?>
                            </a>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<?=$this->renderAjax('@common/mail/layouts/mail_footer', ['user' => $user ?? null])?>
