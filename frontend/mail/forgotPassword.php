<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \amnah\yii2\user\models\User $user
 * @var \amnah\yii2\user\models\UserToken $userToken
 */
?>

<h3><?= $subject ?></h3>

<p><?= Yii::t("user", "Пройдите по ссылке для установки нового пароля:") ?></p>

<p><?= Url::toRoute(["/user/reset", "token" => $userToken->token], true); ?></p>
