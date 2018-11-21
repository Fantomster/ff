<?php
use yii\helpers\Url;
?>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;"><?= Yii::t('app', 'common.mail.login_token.go_to', ['ru'=>'Пройдите по ссылке для установки нового пароля:']) ?></p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <a href="<?= Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/user/login-callback", "token" => $userToken->token]); ?>"
       style="text-decoration: none;
    color: #FFF;
    background-color: #84bf76;
    padding: 10px 16px;
    font-weight: bold;
    margin-right: 10px;
    text-align: center;
    cursor: pointer;
    display: inline-block;
    border-radius: 4px;
    width: 80%;"><?= Yii::t('app', 'common.mail.login_token.confirm', ['ru'=>'Подтвердить']) ?></a>
</div>