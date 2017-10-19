<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \common\models\User $vendor
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */
?>

<p><img src="https://mixcart.ru/images/invite-to-vendor.jpg" style="width: 100%;" alt=""/></p>

<h3 style="font-weight: 500;font-size: 27px;">Здравствуйте, <small style="font-size: 60%;color: #787878;line-height: 0;text-transform: none;"><?= $vendor->profile->full_name ?>.</small></h3>
<p>Наш ресторан, <u><?= $restaurant ?></u>, стал использовать очень удобный инструмент для автоматизации работы с вами. 
    <br />
    Я приглашаю Вас, <u><?= $vendor->organization->name ?></u>, присоединиться, и начать использовать данный сервис. Использование этого инструмента значительно упростит нашу с вами работу.</p>

<p>При отправке этого письма, автоматически был создан аккаунт для вас в <a href="https://mixcart.ru" style="color: #84bf76;">MixCart</a>, подтвердите пожалуйста получение данного приглашения, перейдя подтверждаю получение приглашения.</p>
<br />
<div style="width: 100%; text-align: center;">
    <a href="<?= Url::toRoute(["/user/reset", "token" => $userToken->token], true); ?>"
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
    width: 80%;">Принять приглашение</a>
</div>

<br /><br />


<h5 align="center" style="font-weight: 900;font-size: 17px;">В MixCart очень широкие возможности для поставщиков, о некоторых из них несколько слов ниже.</h5>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Каталог поставщиков</h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Оценки, отзывы</h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Закупка в 2 клика</h6>
        </th>
    </tr>
    <tr align="center">
        <td><img src="https://mixcart.ru/img/ico1.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico2.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico3.png" alt="" /></td>
    </tr>



</table>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Подробная аналитика</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">История заказов</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Распродажи поставщиков</h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="https://mixcart.ru/img/ico4.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico5.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico6.png" alt="" /></td>
    </tr>


</table>

<br /><br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Выставление лимитов</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Размещение тендеров</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"header>Коммуникации в одном месте</h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="https://mixcart.ru/img/ico7.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico8.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico9.png" alt="" /></td>
    </tr>

</table>