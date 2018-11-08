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
                            <?= Yii::t('app', 'common.mail.confirm_email.full_text', [
                                'ru' => 'Благодарим Вас за регистрацию в MixCart! Перед началом работы необходимо подтвердить этот адрес электронной почты. Для этого пройдите по ссылке:'
                            ]) ?>
                        </p>
                        <p style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-weight: 600;text-decoration: underline;color: #00b66b;">
                            <a href="<?= Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/user/confirm", "type" => $user->organization->type_id, "token" => $userToken->token]) ?>"
                               target="_blank"
                               style="font-family: 'Open Sans', Arial, sans-serif;font-size: 16px;font-weight: 600;text-decoration: underline;color: #00b66b;">
                                <?= Yii::t('app', 'common.mail.confirm_email.confirm', ['ru' => 'Подтвердить']) ?>
                            </a>
                        </p>
                        <?= isset($newPassword) ? Yii::t('app', 'franchise.views.site.user.default.password', ['ru' => 'Пароль']) . ": <b>" . $newPassword . "</b>" : "" ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

<?= $this->renderAjax('layouts/mail_footer', ['user' => $user ?? null]) ?>
