<?php
use yii\helpers\Url;
if (empty($toFrontEnd)) {
    $toFrontEnd = false;
}
?>
<p style="line-height: 1.6; margin: 0 0 10px; padding: 0;"><img src="https://mixcart.ru/img/immotion1.jpg" style="max-width: 100%; margin: 0; padding: 0;" alt="" /></p>
<h3 style="line-height: 1.1; color: #3f3e3e; font-weight: 500; font-size: 27px; margin: 0 0 15px; padding: 0;">
    <?= Yii::t('app', 'common.mail.confirm_email.hello', ['ru'=>'Здравствуйте,']) ?> <small style="font-size: 60%; color: #787878; line-height: 0; text-transform: none; margin: 0; padding: 0;"><?= $profile->full_name ?></small>
</h3>
<p style="line-height: 1.6; margin: 0 0 10px; padding: 0;">
    <?= Yii::t('app', 'common.mail.confirm_email.you_registered', ['ru'=>'Вы зарегистрировались в сервисе']) ?> <a href="<?= Yii::$app->params['staticUrl']['home'] ?>" style="color: #84bf76; margin: 0; padding: 0;">MixCart</a>
</p>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;"><?= Yii::t('app', 'common.mail.confirm_email.go_to', ['ru'=>'Для завершения регистрации, пожалуйста, пройдите по следующей ссылке:']) ?></p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <a href="<?= $toFrontEnd ? Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/user/confirm", "type" => $user->organization->type_id, "token" => $userToken->token]) : Url::toRoute(["/user/confirm", "type" => $user->organization->type_id, "token" => $userToken->token], true); ?>" 
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
    width: 80%;"><?= Yii::t('app', 'common.mail.confirm_email.confirm', ['ru'=>'Подтвердить']) ?></a>
</div>
<?php
if($toFrontEnd)
{
?>
<br style="margin: 0; padding: 0;" />
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;"><?= Yii::t('app', 'common.mail.confirm_email.mobile_code', ['ru'=>'Или введите код в мобильном приложении:']) ?> <b><?=$userToken->pin?></b></p>
<?php } ?>