<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\TouchSpin;
?>
<!--<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Подробности о товаре</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-4">
            <img src="<?= $baseProduct->imageUrl ?>" width="176px" height="119px" />
        </div>
        <div class="col-md-8">
            <div>Наименование: <?= $baseProduct->product ?></div>
            <div>Поставщик: <?= $vendor->name ?></div>
            <div>Артикул: <?= $baseProduct->article ?></div>
            <div>Цена: <?= $price ?> <i class="fa fa-fw fa-rub"></i><?= $baseProduct->units ? " / $baseProduct->ed" : '' ?></div>
            <?php if ($baseProduct->units) { ?>
                <div>Кратность: <?= $baseProduct->units ?></div>
            <?php } ?>
        </div>
    </div>
    <?php if ($baseProduct->note) { ?>
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-12">
            Комментарий: <?= $baseProduct->note ?>
        </div>
    </div> 
    <?php } ?>
</div>
<div class="modal-footer form-inline">
    <?=
    TouchSpin::widget([
        'name' => '',
        'id' => 'addQuantity',
        'pluginOptions' => [
            'initval' => 1,
            'min' => (isset($baseProduct->units) && ($baseProduct->units)) ? $baseProduct->units : 0.001,
            'max' => PHP_INT_MAX,
            'step' => (isset($baseProduct->units) && ($baseProduct->units)) ? $baseProduct->units : 1,
            'decimals' => 1,
            'forcestepdivisibility' => (isset($baseProduct->units) && ($baseProduct->units)) ? 'floor' : 'none',
            'buttonup_class' => 'btn btn-default',
            'buttondown_class' => 'btn btn-default',
            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
        ],
        'options' => ['class' => 'quantity form-control width100'],
    ])
    ?>
    <?= Html::a('<i class="fa fa-shopping-cart m-r-xs" style="margin-top:-3px;"></i>&nbsp;&nbsp;Добавить в корзину', "#", 
            ['id' => '#add', 'class' => 'btn btn-success add-to-cart', 'data' => [
                'dismiss' => 'modal',
                'id' => $productId,
                'cat' => $catId,
                ]]) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Закрыть</a>
</div>-->
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Подробности о товаре</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="text-bolt" style="margin-top: 0px;margin-bottom:0;">
                        <?= $baseProduct->product ?>
                    </h4> 
                        <small>Артикул: 
                            <b>
                            <?= $baseProduct->article ?>
                            </b>
                        </small>
                </div>
                <div class="col-md-4 text-center">
                    <h5 style="color:#ababac;font-style:italic;"><?= $vendor->name ?></h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8 no-padding">
                    <div class="col-md-12">
                        <hr>
                        <h3>
                        <?= $price ?> <i class="fa fa-fw fa-rub" style="font-size:20px"></i> за <?= $baseProduct->units ? $baseProduct->units : 1 ?> <?= $baseProduct->ed ?>
                        </h3>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                <img src="<?= $baseProduct->imageUrl ?>" width="176px" height="119px" />
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 no-padding">
                    <div class="col-md-6">
                        <h5>КОРОТКО О ТОВАРЕ</h5>
                        <small>Производитель: 
                            <b>
                            <?= $baseProduct->brand ?>
                            </b>
                        </small><br>
                        <small>Единица измерения: 
                            <b>
                            <?= $baseProduct->ed ?>
                            </b>
                        </small><br>
                        <small>Вес: 
                            <b>
                            <?= $baseProduct->weight ?>
                            </b>
                        </small><br>
                        <small>Кратность: 
                            <b>
                            <?= $baseProduct->units ?>
                            </b>
                        </small><br>
                        <!--small>Бренд: <b>НАТС</b></small><br-->
                        <!--<small>Условия доставки: <b>За 12 дней</b></small>-->
                    </div>   
                    <div class="col-md-6">
                        <h5>УСЛОВИЯ ДОСТАВКИ</h5>
                        <small>Стоимость доставки: <b><?= $vendor->delivery->delivery_charge ?></b></small><br>
                        <small>Стоимость заказа для бесплатной доставки у поставщика: <b><?= $vendor->delivery->min_free_delivery_charge ?></b></small><br>
                        <small>Минимальная стоимость заказа:<b><?= $vendor->delivery->min_order_price ?></b></small><br>
                        <!--<small>Адрес самовывоза: <b>0.00</b></small><br>-->
                        <small>Дни доставки: <b><?= $vendor->delivery->getDaysString() ?></b></small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h5>КОММЕНТАРИЙ</h5>
                    <small>
                    <?= $baseProduct->note ?> 
                    </small>
                </div>
            </div>
        </div>
    </div> 
</div>
<div class="modal-footer form-inline">
    <?=
    TouchSpin::widget([
        'name' => '',
        'id' => 'addQuantity',
        'pluginOptions' => [
            'initval' => 1,
            'min' => (isset($baseProduct->units) && ($baseProduct->units)) ? $baseProduct->units : 0.001,
            'max' => PHP_INT_MAX,
            'step' => (isset($baseProduct->units) && ($baseProduct->units)) ? $baseProduct->units : 1,
            'decimals' => 1,
            'forcestepdivisibility' => (isset($baseProduct->units) && ($baseProduct->units)) ? 'floor' : 'none',
            'buttonup_class' => 'btn btn-default',
            'buttondown_class' => 'btn btn-default',
            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
        ],
        'options' => ['class' => 'quantity form-control width100'],
    ])
    ?>
    <?= Html::a('<i class="fa fa-shopping-cart m-r-xs" style="margin-top:-3px;"></i>&nbsp;&nbsp;Добавить в корзину', "#", 
            ['id' => '#add', 'class' => 'btn btn-success add-to-cart', 'data' => [
                'dismiss' => 'modal',
                'id' => $productId,
                'cat' => $catId,
                ]]) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Отмена</a>
</div>