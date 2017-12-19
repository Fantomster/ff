<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\TouchSpin;
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.views.order.product_details', ['ru'=>'Подробности о товаре']) ?></h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="text-bolt" style="margin-top: 0px;margin-bottom:0;">
                        <?= $baseProduct->product ?>
                    </h4> 
                        <small><?= Yii::t('message', 'frontend.views.order.art_three', ['ru'=>'Артикул:']) ?>
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
                        <?= $price ?> <?= $currencySymbol ?> <?= Yii::t('message', 'frontend.views.order.for_one', ['ru'=>'за 1']) ?> <?= Yii::t('app', $baseProduct->ed) ?>
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
                        <h5><?= Yii::t('message', 'frontend.views.order.shortly', ['ru'=>'КОРОТКО О ТОВАРЕ']) ?></h5>
                        <small><?= Yii::t('message', 'frontend.views.order.manufacturer', ['ru'=>'Производитель:']) ?>
                            <b>
                            <?= $baseProduct->brand ?>
                            </b>
                        </small><br>
                        <small><?= Yii::t('message', 'frontend.views.order.measure', ['ru'=>'Единица измерения:']) ?>
                            <b>
                            <?= Yii::t('app', $baseProduct->ed) ?>
                            </b>
                        </small><br>
                        <small><?= Yii::t('message', 'frontend.views.order.weight', ['ru'=>'Вес:']) ?>
                            <b>
                            <?= $baseProduct->weight ?>
                            </b>
                        </small><br>
                        <small><?= Yii::t('message', 'frontend.views.order.frequency', ['ru'=>'Кратность:']) ?>
                            <b>
                            <?= $baseProduct->units ?>
                            </b>
                        </small><br>
                    </div>   
                    <div class="col-md-6">
                        <h5><?= Yii::t('message', 'frontend.views.order.delivery_conditions', ['ru'=>'УСЛОВИЯ ДОСТАВКИ']) ?></h5>
                        <small><?= Yii::t('message', 'frontend.views.order.delivery_price', ['ru'=>'Стоимость доставки:']) ?> <b><?= $vendor->delivery->delivery_charge ?></b></small><br>
                        <small><?= Yii::t('message', 'frontend.views.order.free_delivery_price', ['ru'=>'Стоимость заказа для бесплатной доставки у поставщика:']) ?> <b><?= $vendor->delivery->min_free_delivery_charge ?></b></small><br>
                        <small><?= Yii::t('message', 'frontend.views.order.min_order_price', ['ru'=>'Минимальная стоимость заказа:']) ?><b><?= $vendor->delivery->min_order_price ?></b></small><br>
                        <!--<small>Адрес самовывоза: <b>0.00</b></small><br>-->
                        <small><?= Yii::t('message', 'frontend.views.order.delivery_days', ['ru'=>'Дни доставки:']) ?> <b><?= $vendor->delivery->getDaysString() ?></b></small>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h5><?= Yii::t('message', 'frontend.views.order.comment', ['ru'=>'КОММЕНТАРИЙ']) ?></h5>
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
            'forcestepdivisibility' => (isset($baseProduct->units) && ($baseProduct->units) && (floor($baseProduct->units) == $baseProduct->units)) ? 'floor' : 'none',
            'buttonup_class' => 'btn btn-default',
            'buttondown_class' => 'btn btn-default',
            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
        ],
        'options' => ['class' => 'quantity form-control width100'],
    ])
    ?>
    <?= Html::a('<i class="fa fa-shopping-cart m-r-xs" style="margin-top:-3px;"></i>&nbsp;&nbsp;' . Yii::t('message', 'frontend.views.order.add_to_basket_two', ['ru'=>'Добавить в корзину']) . ' ', "#",
            ['id' => '#add', 'class' => 'btn btn-success add-to-cart', 'data' => [
                'dismiss' => 'modal',
                'id' => $productId,
                'cat' => $catId,
                ]]) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> <?= Yii::t('message', 'frontend.views.order.cancel_six', ['ru'=>'Отмена']) ?></a>
</div>