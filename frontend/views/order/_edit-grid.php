<?php

//use kartik\grid\GridView;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\TouchSpin;
use kartik\form\ActiveForm;
use common\models\Order;

$currencySymbol = $order->currency->symbol;

$form = ActiveForm::begin([
            'id' => 'editOrder',
            'enableAjaxValidation' => false,
            'options' => [
                'data-pjax' => false,
            ],
            'method' => 'post',
            'action' => Url::to(['order/edit', 'id' => $order->id]),
        ]);

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable order-table'],
    'options' => ['class' => 'table-responsive'],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product.product',
            'value' => function($data) {
                $note = isset($data->comment) ? "<div class='grid-article'>" . Yii::t('message', 'frontend.views.order.article', ['ru'=>'Заметка:']) .' '. $data->comment . "</div>" : "";
                return "<div class='grid-prod'>" . Html::decode(Html::decode($data->product_name)) . "</div>"
                        . "<div class='grid-article'>" . Yii::t('message', 'frontend.views.order.', ['ru'=>'Артикул:']) . "  <span>"
                        . $data->article . "</span></div>" . $note;
            },
            'label' => Yii::t('message', 'frontend.views.order.good', ['ru'=>'Товар']),
        ],
        [
            'attribute' => 'quantity',
            'content' => function($data) {
                return TouchSpin::widget([
                            'name' => "OrderContent[$data->id][quantity]",
                            'pluginOptions' => [
                                'initval' => $data->quantity,
                                'min' => 0.001,
                                'max' => PHP_INT_MAX,
                                'decimals' => 3,
                                'forcestepdivisibility' => 'none',
                                'buttonup_class' => 'btn btn-default btn-sm',
                                'buttondown_class' => 'btn btn-default btn-sm',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                            'options' => ['class' => 'view-data', 'id' => 'qnty' . $data->id],
                        ]) . Html::hiddenInput("OrderContent[$data->id][id]", $data->id);
            },
            'contentOptions' => ['class' => 'width150'],
        ],
        ($priceEditable) ?
                [
            'attribute' => 'price',
            'content' => function($data) {
                return TouchSpin::widget([
                            'name' => "OrderContent[$data->id][price]",
                            'pluginOptions' => [
                                'initval' => $data->price,
                                'min' => 0,
                                'max' => PHP_INT_MAX,
                                'step' => 1,
                                'decimals' => 2,
                                'forcestepdivisibility' => 'none',
                                'buttonup_class' => 'btn btn-default btn-sm',
                                'buttondown_class' => 'btn btn-default btn-sm',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                            'options' => ['class' => 'view-data'],
                ]);
            },
            'contentOptions' => ['class' => 'width150'],
                ] : ['format' => 'raw',
            'attribute' => 'price',
            'value' => function($data) use ($currencySymbol) {
                return '<b>' . $data->price . ' '. $currencySymbol . '</b>';
            },
            'label' => Yii::t('message', 'frontend.views.order.price_three', ['ru'=>'Цена']),
            'contentOptions' => ['class' => 'width150'],
                ],
        [
            'format' => 'raw',
            'attribute' => 'total',
            'value' => function($data) use ($currencySymbol) {
                return '<b>' . $data->total . ' '. $currencySymbol . '</b>';
            },
            'label' => Yii::t('message', 'frontend.views.order.sum_two', ['ru'=>'Сумма']),
            'contentOptions' => ['class' => 'width150'],
        ],
        [
            'format' => 'raw',
            'value' => function($data) {
                return '<a href="#" class="deletePosition btn btn-outline-danger btn-sm" data-target="#qnty' . $data->id . '" title="' . Yii::t('message', 'frontend.views.order.del_position', ['ru'=>'Удалить позицию']) . ' "><i class="fa fa-trash m-r-xxs"></i></a>';
            },
            'contentOptions' => ['class' => 'text-center', 'style' => 'width: 50px;'],
        ],
    ],
]);
$discountTypes = $order->discountDropDown();
if ($priceEditable) {
    //editable discount
    ?>
    <div class="order-total">
        <div class="row">
            <div class="col-xs-4"><hr></div>
            <div class="col-xs-8"></div>
        </div>
        <?php if (!empty($order->comment)) { ?>
            <div class="row">
                <div class="col-xs-4"><span><?= Yii::t('message', 'frontend.views.order.order_comment_three', ['ru'=>'Комментарий к заказу']) ?></span></div>
                <div class="col-xs-8"><?= $order->comment ?></div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-xs-4">
                <?= $form->field($order, 'discount_type')->dropDownList($discountTypes)->label(false) ?>
            </div>
            <div class="col-xs-8">
                <?= $form->field($order, 'discount')->label(false) ?>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-4"><hr></div>
            <div class="col-xs-8"></div>
        </div>
        <div class="row">
            <div class="col-xs-4 ">
                <span><?= Yii::t('message', 'frontend.views.order.delivery_price_two', ['ru'=>'Стоимость доставки:']) ?></span>
            </div>
            <div class="col-xs-8">
                <span><?= $order->calculateDelivery() . ' ' . $currencySymbol ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-4">
                <span><?= Yii::t('message', 'frontend.views.order.total', ['ru'=>'Итого:']) ?></span>
            </div>
            <div class="col-xs-8">
                <span><?= $order->total_price . ' ' . $currencySymbol ?></span>
            </div>
        </div>
    </div>
    <?php
} else {
    //show discount
    ?>
    <div class="order-total">
        <div class="row">
            <div class="col-xs-4"><hr></div>
            <div class="col-xs-8"></div>
        </div>
        <?php if (!empty($order->comment)) { ?>
            <div class="row">
                <div class="col-xs-4"><span><?= Yii::t('message', 'frontend.views.order.order_comment_four', ['ru'=>'Комментарий к заказу']) ?></span></div>
                <div class="col-xs-8"><?= $order->comment ?></div>
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-xs-4">
                <span><?= ($order->discount_type) ? $discountTypes[$order->discount_type] : Yii::t('message', 'frontend.views.order.discount', ['ru'=>'Скидка']) ?></span>
            </div>
            <div class="col-xs-8">
                <span><?= ($order->discount) ? $order->discount : '-' ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-4 ">
                <span><?= Yii::t('message', 'frontend.views.order.delivery_price_three', ['ru'=>'Стоимость доставки:']) ?></span>
            </div>
            <div class="col-xs-8">
                <span><?= $order->calculateDelivery() . ' ' . $currencySymbol ?></span>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-4">
                <span><?= Yii::t('message', 'frontend.views.order.total_two', ['ru'=>'Итого:']) ?></span>
            </div>
            <div class="col-xs-8">
                <span><?= $order->total_price . ' ' . $currencySymbol ?></span>
            </div>
        </div>
    </div>
    <?php
}
echo Html::button('<span><i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.order.save_six', ['ru'=>'Сохранить']) . ' </span>', [
    'class' => 'btn btn-success pull-right btnSave',
    'data-loading-text' => "<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> " . Yii::t('message', 'frontend.views.order.saving_three', ['ru'=>'Сохраняем...']),
]);
echo $canRepeatOrder ? Html::a('<i class="icon fa fa-refresh"></i> ' . Yii::t('message', 'frontend.views.order.repeat_two', ['ru'=>'Повторить заказ']), ['order/repeat', 'id' => $order->id], [
            'class' => 'btn btn-default pull-right',
            'style' => 'margin-right: 7px;'
        ]) : "";
ActiveForm::end();

