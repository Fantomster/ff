<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \common\models\User $vendor
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */
?>

<p><img src="http://f-keeper.ru/img/header-bg-client.jpg" style="width: 100%;"/></p>

<h3 style="font-weight: 500;font-size: 27px;">Здравствуйте, <small style="font-size: 60%;color: #787878;line-height: 0;text-transform: none;"><?= $client->profile->full_name ?>.</small></h3>
<p>Наш ресторан, <u><?= $vendor ?></u>, стал использовать очень удобный инструмент для автоматизации работы с вами. 
    <br />
    Я приглашаю Вас, <u><?= $client->organization->name ?></u>, присоединиться, и начать использовать данный сервис. Использование этого инструмента значительно упростит нашу с вами работу.</p>

<p>При отправке этого письма, автоматически был создан аккаунт для вас в <a href="http://f-keeper.ru" style="color: #84bf76;">F-keeper</a>, подтвердите пожалуйста получение данного приглашения, перейдя подтверждаю получение приглашения.</p>
<br />
<div style="width: 100%; text-align: center;">
    <a href="<?= Url::toRoute(["/user/register"], true) ?>"
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


<h5 align="center" style="font-weight: 900;font-size: 17px;">В f-keeper очень широкие возможности для ресторанов, о некоторых из них несколько слов ниже.</h5>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Market Place</h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Прайсы и каталоги</h6>
        </th>
        <th width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Коммуникации</h6>
        </th>
    </tr>
    <tr align="center">
        <td><img src="http://f-keeper.ru/img/ico1-1.png" /></td>
        <td><img src="http://f-keeper.ru/img/ico2-1.png" /></td>
        <td><img src="http://f-keeper.ru/img/ico3-1.png" /></td>
    </tr>



</table>
<br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Акции и распродажи</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Реклама</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Оценки и отзывы</h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="http://f-keeper.ru/img/ico4-1.png" /></td>
        <td><img src="http://f-keeper.ru/img/ico5-1.png" /></td>
        <td><img src="http://f-keeper.ru/img/ico2.png" /></td>
    </tr>


</table>

<br /><br /><br />
<table style="display: table;border-spacing: 0px;border-color: grey;width: 100%;">
    <tr align="center">
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Аналитика</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;">Участие в тендерах</h6>
        </td>
        <td width="33%">
            <h6 style="font-weight: 900;font-size: 14px;color: #787878;"header>Обработка заказов</h6>
        </td>
    </tr>
    <tr align="center">
        <td><img src="http://f-keeper.ru/img/ico4.png" /></td>
        <td><img src="http://f-keeper.ru/img/ico8.png" /></td>
        <td><img src="http://f-keeper.ru/img/ico9.png" /></td>
    </tr>

</table>