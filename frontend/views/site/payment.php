<?php
/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Оплата';
?>
<section class="tarif">
    <div class="p_wrapper">
        <h2 class="block_title">ПОДКЛЮЧЕНИЕ И АБОНЕНТСКАЯ ПЛАТА</h2>
        <p class="subtitle">Функции сервиса доступны в течении оплаченного периода</p>
        <div class="tarif_w">
            <!-- tarif_coll__wrap -->
            <div class="tarif_coll__wrap">

                <div class="tarif_coll">
                    <div class="tarif__item">
                        <div class="tarif__item__head">
                            <span class="paket">тарификация</span>
                            <span class="name">РЕСТОРАН</span>
                        </div>
                        <div class="tarif__item__row">
                            <span class="price_text">стоимость подключения</span>
                            <span class="srok">единовременно</span>
                            <span class="price">10 000 Р</span>
                        </div>
                        <div class="tarif__item__row">
                            <span class="price_text">абонентская плата</span>
                            <span class="srok">период 30 календарных дней</span>
                            <span class="price">2 940 Р</span>
                        </div>
                    </div>
                </div>

                <div class="tarif_coll">
                    <div class="tarif__item">
                        <div class="tarif__item__head">
                            <span class="paket">тарификация</span>
                            <span class="name">ПОСТАВЩИК</span>
                        </div>
                        <div class="tarif__item__row">
                            <span class="price_text">стоимость подключения</span>
                            <span class="srok">единовременно</span>
                            <span class="price">30 000 Р</span>
                        </div>
                        <div class="tarif__item__row">
                            <span class="price_text">абонентская плата</span>
                            <span class="srok">период 30 календарных дней</span>
                            <span class="price">5 000 Р</span>
                        </div>
                    </div>
                </div>

            </div>
            <!-- end tarif_coll__wrap -->
        </div>
    </div>
</section>
