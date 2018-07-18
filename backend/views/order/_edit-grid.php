<?php

use kartik\grid\GridView;
//use yii\grid\GridView;
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
                'data-pjax' => true,
            ],
            'method' => 'post',
        ]);
?>
<div class="row">
    <div class="col-md-6">
        <?=
                $form->field($order, 'status', ['options' => ['class' => 'form-group form-inline']])
                ->dropDownList(Order::getStatusList(true), ['style' => 'margin-left: 5px;'])
                ->label(null, ['style' => 'margin-top: 5px;'])
        ?>
    </div>
    <div class="col-md-6">
        <?php
        if (isset($order->assignment)) {
            echo $form->field($order->assignment, 'is_processed', ['options' => ['class' => 'form-group form-inline']])
                    ->checkbox();
        }
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <?=
        GridView::widget([
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
                        return
                                Html::tag('a', Html::decode(Html::decode($data->product_name)), ['href' => '/goods/' . $data->product_id]) .
                                Html::tag('p', \Yii::t('app', 'Артикул') . ': ' . $data->article, [
                                    'style' => 'line-height: 1;font-size: 11px;color: #999C9E;'
                                ]) .
                                Html::tag('p', isset($data->comment) ? $data->comment : '', [
                                    'style' => 'line-height: 1;font-size: 11px;color: #999C9E;'
                        ]);
                    },
                    'label' => Yii::t('message', 'frontend.views.order.good', ['ru' => 'Товар']),
                ],
                [
                    'attribute' => 'quantity',
                    'content' => function($data) {
                        return TouchSpin::widget([
                                    'id' => 'quantity' . $data->id,
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
                                    'options' => ['class' => 'quantity view-data', 'id' => 'qnty' . $data->id],
                                ]) . Html::hiddenInput("OrderContent[$data->id][id]", $data->id);
                    },
                    'contentOptions' => ['style' => 'width:180px;'],
                ],
                [
                    'attribute' => 'price',
                    'content' => function($data) {
                        return TouchSpin::widget([
                                    'id' => 'price' . $data->id,
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
                                    'options' => ['class' => 'price view-data'],
                        ]);
                    },
                    'contentOptions' => ['style' => 'width:180px;'],
                ],
                [
                    'format' => 'raw',
                    'attribute' => 'total',
                    'value' => function($data) use ($currencySymbol) {
                        return '<b>' . $data->total . ' ' . $currencySymbol . '</b>';
                    },
                    'label' => Yii::t('message', 'frontend.views.order.sum_two', ['ru' => 'Сумма']),
                    'contentOptions' => ['style' => 'width:180px;'],
                ],
                [
                    'format' => 'raw',
                    'value' => function($data) {
                        return '<a href="#" class="deletePosition btn btn-outline-danger btn-sm" data-target="#qnty' . $data->id . '" title="' . Yii::t('message', 'frontend.views.order.del_position', ['ru' => 'Удалить позицию']) . ' "><i class="fa fa-trash m-r-xxs"></i></a>';
                    },
                    'contentOptions' => ['class' => 'text-center', 'style' => 'width: 50px;'],
                ],
            ],
        ])
        ?>
    </div>
</div>

<?php
ActiveForm::end();

