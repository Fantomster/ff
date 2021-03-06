<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = Yii::t('message', 'frontend.views.site.contacts', ['ru'=>'Контакты']);
?>
<main class="content content-inner ">
    <div class="contact__inner">
        <h2><?= Yii::t('message', 'frontend.views.site.contacts_two', ['ru'=>'Контакты']) ?></h2>
        <span><?= Yii::t('message', 'frontend.views.site.we_glad', ['ru'=>'Рады видеть наших клиентов и партнеров<br/>в нашем офисе']) ?></span>
        <div class="container-fluid">
            <div class="col-md-4">
                <div class="inside__contact">
                    <b><?= Yii::t('message', 'frontend.views.site.address_two', ['ru'=>'Адрес']) ?> </b>
                    <span><?= Yii::t('message', 'frontend.views.site.moscow', ['ru'=>'Москва, ул.Привольная, 70']) ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="inside__contact">
                    <b><?= Yii::t('message', 'frontend.views.site.phone', ['ru'=>'Телефон']) ?></b>
                    <span><?= Yii::t('message', 'frontend.views.site.phone_number', ['ru'=>'8-499-404-10-18']) ?></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="inside__contact">
                    <b><?= Yii::t('message', 'frontend.views.site.post', ['ru'=>'Почта']) ?></b>
                    <span><a href="mailto:info@mixcart.ru">info@mixcart.ru</a></span>
                </div>
            </div>
        </div>
    </div>
    <div class="map">
        <script type="text/javascript" charset="utf-8" async src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=zsxY6q3M7ehFAJKZ8tviP4FWNB_Swd72&amp;width=100%25&amp;height=720&amp;lang=ru_RU&amp;sourceType=constructor&amp;scroll=true"></script>
    </div>
</main><!-- .content -->