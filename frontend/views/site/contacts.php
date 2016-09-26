<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Контакты';
?>
<main class="content content-inner ">
    <div class="contact__inner">
        <h2>Контакты</h2>
        <span>Рады видеть наших клиентов и партнеров<br/>в нашем офисе</span>
        <div class="container-fluid">
            <div class="col-md-4">
                <div class="inside__contact">
                    <b>Адрес </b>
                    <span>Москва ул. Оршанская 5.</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="inside__contact">
                    <b>Телефон</b>
                    <span>8-499-404-10-18</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="inside__contact">
                    <b>Почта</b>
                    <span><a href="mailto:info@f-keeper.ru">info@f-keeper.ru</a></span>
                </div>
            </div>
        </div>
    </div>
    <div class="map">
        <script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=5zZGbc_VLkf2216n4n4sHLLyGd5r1lQK&amp;width=100%&amp;height=720&amp;lang=ru_RU&amp;sourceType=constructor&amp;scroll=true"></script>
    </div>
</main><!-- .content -->