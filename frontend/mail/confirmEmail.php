<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \amnah\yii2\user\models\User $user
 * @var \amnah\yii2\user\models\Profile $profile
 * @var \amnah\yii2\user\models\UserToken $userToken
 */
?>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
    <img src="http://f-keeper.ru/mail/img/header-bg.jpg" style="max-width: 100%; margin: 0; padding: 0;" />
</p>
<h3 style="font-family: 'HelveticaNeue-Light', 'Helvetica Neue Light', 'Helvetica Neue', Helvetica, Arial, 'Lucida Grande', sans-serif; line-height: 1.1; color: #3f3e3e; font-weight: 500; font-size: 27px; margin: 0 0 15px; padding: 0;">
    Здравствуйте, <small style="font-size: 60%; color: #787878; line-height: 0; text-transform: none; margin: 0; padding: 0;"><?= $profile->full_name ?></small>
</h3>
<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">
    Вы зарегистрировали организацию <u style="margin: 0; padding: 0;">"<?= $user->organization->name ?>"</u> в сервисе <a href="<?= Url::toRoute(["/site/index"], true); ?>" style="color: #84bf76; margin: 0; padding: 0;">F-keeper</a>
</p>

<p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;">Для завершения регистрации, пожалуйста, пройдите по следующей ссылке:</p>
<br style="margin: 0; padding: 0;" />
<div style="text-align: center; width: 100%; margin: 0; padding: 0;" align="center">
    <a href="<?= Url::toRoute(["/user/confirm", "token" => $userToken->token], true); ?>" style="color: #FFF; text-decoration: none; font-weight: bold; text-align: center; cursor: pointer; display: block !important; border-radius: 4px; background-image: none !important; background-color: #84bf76; margin: 0 0 10px; padding: 10px 16px;">Подтвердить</a>
</div>