<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \common\models\User $user
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */
?>

<h3><?= $subject ?></h3>

<p><?= Yii::t("user", "$restaurant invites you to use f-keeper. Please follow next link:") ?></p>

<p><?= Url::toRoute(["/user/accept-restaurants-invite", "token" => $userToken->token], true); ?></p>