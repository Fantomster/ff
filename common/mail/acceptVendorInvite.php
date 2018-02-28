<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \common\models\User $vendor
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */

?>

<p><img src="https://app.mixcart.ru/images/invite-to-client.jpg" style="width: 100%;" alt=""/></p>

<h3 style="font-weight: 500;font-size: 27px;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.hello', ['ru'=>'Здравствуйте']) ?>!</h3>
<p><?= Yii::t('app', 'common.mail.accept_vendor_invite.we', ['ru'=>'Мы, {vendor}, стали использовать очень удобный инструмент для автоматизации работы с вами', 'vendor'=>$vendor]) ?>.
    <br />
    <?= Yii::t('app', 'common.mail.accept_vendor_invite.i_invite', ['ru'=>'Я приглашаю Вас присоединиться и начать использовать данный сервис. Использование этого инструмента значительно упростит нашу с вами работу']) ?>.</p>

<br />
<div style="width: 100%; text-align: center;">
    <a href="<?= Yii::$app->urlManagerFrontend->createAbsoluteUrl(["/user/register"]) ?>"
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


<h5 align="center" style="font-weight: 900;font-size: 17px;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.abilities', ['ru'=>'В MixCart очень широкие возможности для ресторанов, о некоторых из них несколько слов ниже']) ?>.</h5>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Market Place</h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.prices', ['ru'=>'Прайсы и каталоги']) ?></h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.communications', ['ru'=>'Коммуникации']) ?></h6>
        </th>
    </tr>
    <tr align="center">
        <td><img src="https://app.mixcart.ru/img/ico1-1.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico2-1.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico3-1.png" alt="" /></td>
    </tr>



</table>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.actions', ['ru'=>'Акции и распродажи']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.commercial', ['ru'=>'Реклама']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.rating', ['ru'=>'Оценки и отзывы']) ?></h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="https://app.mixcart.ru/img/ico4-1.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico5-1.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico2.png" alt="" /></td>
    </tr>


</table>

<br /><br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.anal', ['ru'=>'Аналитика']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"><?= Yii::t('app', 'common.mail.accept_vendor_invite.tenders', ['ru'=>'Участие в тендерах']) ?></h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"header><?= Yii::t('app', 'common.mail.accept_vendor_invite.processing', ['ru'=>'Обработка заказов']) ?></h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="https://app.mixcart.ru/img/ico4.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico8.png" alt="" /></td>
        <td><img src="https://app.mixcart.ru/img/ico9.png" alt="" /></td>
    </tr>

</table>