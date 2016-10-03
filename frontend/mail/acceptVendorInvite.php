<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \common\models\User $vendor
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */
?>

<h3><?= $subject ?></h3>

<p><?= Yii::t("user", "$vendor invites you to use f-keeper.") ?></p>

<p><?= Url::toRoute(["/user/accept-vendor-invite", "token" => $userToken->token], true); ?></p>