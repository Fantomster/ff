<?php

use yii\helpers\Html;

/**
 * @var string $subject
 * @var \common\models\User $vendor
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */

?>

<p><img src="https://app.mixcart.ru/images/invite-to-client.jpg" style="max-width: 100%; min-width: 100%;" width="952px" height="334px" alt="invite"/></p>

<h3 style="font-weight: 500;font-size: 27px;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.hello', ['ru'=>'Здравствуйте']) ?>!</h3>
<p><?= Yii::t('app', 'common.mail.accept_active_vendor_invite.we', ['ru'=>'{vendor}, уже сделал свою работу легче с помощью {link} и приглашает вас работать вместе с ним.', 'vendor'=>$vendor, 'link' =>Html::a('MixCart', Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/client/suppliers"]))]); ?>
    <br />
    <?= Yii::t('app', 'common.mail.accept_active_vendor_invite.i_invite', ['ru'=>'Сервис автоматизации закупок для ресторанов MixCart позволит вам делать закупки легко и быстро, попробуйте!']) ?>.</p>
<br />
<div style="width: 100%; text-align: center;">
    <a href="<?= Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/client/suppliers"]) ?>"
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
    width: 80%;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.confirm', ['ru'=>'Принять приглашение']) ?></a>
</div>
<br /><br />