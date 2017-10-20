<?php

use yii\helpers\Url;

/**
 * @var string $subject
 * @var \common\models\User $vendor
 * @var \amnah\yii2\user\models\UserToken $userToken
 * @var string $restaurant
 */

?>

<p><img src="https://mixcart.ru/img/invite-to-client.jpg" style="width: 100%;"/></p>

<h3 style="font-weight: 500;font-size: 27px;">Здравствуйте!</h3>
<p>Мы, <u><?= $we ?></u>, стали использовать очень удобный инструмент для автоматизации работы с вами. 
    <br />
    MixCart это инструмент для автоматизации процесса взаимодействия между поставщиком и рестораном. Рестораны создают заказы, в несколько кликов. Поставщики получают и обрабатывают заказы. Обработка всех заказов, происходит в одном месте. Минимум человеческого фактора. MixCart, сокращает время на обработку заказов в несколько раз. Уменьшает количество возвратов и ошибок.

</p>

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
    width: 80%;">Принять приглашение</a>
</div>

<br /><br />


<h5 align="center" style="font-weight: 900;font-size: 17px;">В MixCart очень широкие возможности для ресторанов и поставщиков, о некоторых из них несколько слов ниже.</h5>
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
        <td><img src="https://mixcart.ru/img/ico1-1.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico2-1.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico3-1.png" alt="" /></td>
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
        <td><img src="https://mixcart.ru/img/ico4-1.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico5-1.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico2.png" alt="" /></td>
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
        <td><img src="https://mixcart.ru/img/ico4.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico8.png" alt="" /></td>
        <td><img src="https://mixcart.ru/img/ico9.png" alt="" /></td>
    </tr>

</table>