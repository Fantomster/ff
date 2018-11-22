<?php

/**
 * @var string $subject
 * @var \common\models\User $vendor
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */
?>

<p><img src="https://app.mixcart.ru/images/invite-to-vendor.jpg" style="width: 100%;" alt=""/></p>

<h3 style="font-weight: 500;font-size: 27px;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.helo', ['ru'=>'Здравствуйте']) ?>, <small style="font-size: 60%;color: #787878;line-height: 0;text-transform: none;"><?= $vendor->profile->full_name ?>.</small></h3>
<p><?= Yii::t('app', 'common.mail.accept_restaurant_invite.rest', ['ru'=>'Наш ресторан, {rest}, стал использовать очень удобный инструмент для автоматизации работы с вами', 'rest'=>$restaurant]) ?>.
    <br />
    <?= Yii::t('app', 'common.mail.accept_restaurant_invite.i_invite', ['ru'=>'Я приглашаю Вас, {org}, присоединиться, и начать использовать данный сервис. Использование этого инструмента значительно упростит нашу с вами работу', 'org'=>$vendor->organization->name]) ?>.</p>

<p><?= Yii::t('app', 'common.mail.accept_restaurant_invite.sending_email', ['ru'=>'При отправке этого письма, автоматически был создан аккаунт для вас в <a href="'.Yii::$app->params['staticUrl'][Yii::$app->language]['home'].'" style="color: #84bf76;">MixCart</a>, подтвердите пожалуйста получение данного приглашения, перейдя подтверждаю получение приглашения']) ?>.</p>
<br />
<div style="width: 100%; text-align: center;">
    <a href="<?= Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/"]); ?>"
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
    width: 80%;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.accept_invitation', ['ru'=>'Принять приглашение']) ?></a>
    <p style="font-weight: normal; font-size: 14px; line-height: 1.6; margin: 0 0 10px; padding: 0;"><?= Yii::t('api_web', 'common.mail.accept_restaurant_invite.new_account', ['ru'=>'Ваша учетная запись']) ?>:</p>
        <p><?= Yii::t('api_web', 'common.mail.accept_restaurant_invite.username', ['ru'=>'Логин (email)']) ?> : <b><?=$vendor->email?></b></p>
        <p><?= Yii::t('api_web', 'common.mail.accept_restaurant_invite.password', ['ru'=>'Пароль']) ?> : <b><?=$vendor->newPassword?></b></p>
    </div>
</div>

<br /><br />


<h5 align="center" style="font-weight: 900;font-size: 17px;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.abilities', ['ru'=>'В MixCart очень широкие возможности для поставщиков, о некоторых из них несколько слов ниже']) ?>.</h5>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.vendors_catalog', ['ru'=>'Каталог поставщиков']) ?></h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.ratings', ['ru'=>'Оценки, отзывы']) ?></h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.two_clicks', ['ru'=>'Закупка в 2 клика']) ?></h6>
        </th>
    </tr>
    <tr align="center">
        <td><img src="https://app.mixcart.ru/img/ico1.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico2.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico3.png" alt="" /></td>
    </tr>



</table>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.anal', ['ru'=>'Подробная аналитика']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.order_history', ['ru'=>'История заказов']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.vendors_sales', ['ru'=>'Распродажи поставщиков']) ?></h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="https://app.mixcart.ru/img/ico4.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico5.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico6.png" alt="" /></td>
    </tr>


</table>

<br /><br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.limits_set', ['ru'=>'Выставление лимитов']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_restaurant_invite.tenders', ['ru'=>'Размещение тендеров']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"header><?= Yii::t('app', 'common.mail.accept_restaurant_invite.one_place_communications', ['ru'=>'Коммуникации в одном месте']) ?></h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="https://app.mixcart.ru/img/ico7.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico8.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico9.png" alt="" /></td>
    </tr>

</table>
