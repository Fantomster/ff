<?php

use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\TouchSpin;
use kartik\form\ActiveForm;
use common\models\Order;

$form = ActiveForm::begin([
            'id' => 'generalSettings',
            'enableAjaxValidation' => false,
            'options' => [
                'data-pjax' => true,
            ],
            'method' => 'post',
            'action' => Url::to(['order/view', 'id' => $order->id]),
        ]);
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'filterPosition' => false,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
    'options' => ['class' => 'table-responsive'],
    'panel' => false,
    'bootstrap' => false,
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product.product',
            'value' => function($data) {
                $note = ""; //$data->product->note ? "<div class='grid-article'>Заметка: " . $data->product->note . "</div>" : ""; 
                return "<div class='grid-prod'>" . $data->product_name . "</div>"
                        . "<div class='grid-article'>Артикул: <span>"
                        . $data->article . "</span></div>" . $note;
            },
            'label' => 'Название продукта',
        ],
        [
            'attribute' => 'quantity',
            'content' => function($data) {
                return TouchSpin::widget([
                            'name' => "OrderContent[$data->id][quantity]",
                            'pluginOptions' => [
                                'initval' => $data->quantity,
                                'min' => 1,
                                'max' => PHP_INT_MAX,
                                'step' => 1,
                                'decimals' => 0,
                                'buttonup_class' => 'btn btn-default',
                                'buttondown_class' => 'btn btn-default',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                            'options' => ['class' => 'viewData'],
                        ]) . Html::hiddenInput("OrderContent[$data->id][id]", $data->id);
            },
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
                                        'step' => 0.01,
                                        'decimals' => 2,
                                        'buttonup_class' => 'btn btn-default',
                                        'buttondown_class' => 'btn btn-default',
                                        'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                        'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                                    ],
                                    'options' => ['class' => 'viewData'],
                                ]);
                    },
                                ] : [ 'format' => 'raw',
                            'attribute' => 'price',
                            'value' => function($data) {
                                return $data->price . ' <i class="fa fa-fw fa-rub"></i>';
                            },
                            'label' => 'Цена',
                                ],
                        [
                            'format' => 'raw',
                            'attribute' => 'total',
                            'value' => function($data) {
                                return $data->total . ' <i class="fa fa-fw fa-rub"></i>';
                            },
                            'label' => 'Общая стоимость',
                        ],
                    ],
                ]);
                            $discountTypes = Order::discountDropDown();
                if ($priceEditable) {
                    //editable discount
                    ?>
<div class="pull-right">
<table class="table table-bordered table-striped dataTable" style="width: 400px">
    <tr>
        <td>
            <?= $form->field($order, 'discount_type')->dropDownList($discountTypes)->label(false) ?>
        </td><td>
            <?= $form->field($order, 'discount')->label(false) ?>
        </td>
    </tr><tr>
        <td>
            Стоимость доставки
        </td><td>
            <?= $order->calculateDelivery() . ' <i class="fa fa-fw fa-rub"></i>' ?>
        </td>
    </tr><tr>
        <td>
            Стоимость заказа
        </td><td>
            <?= $order->total_price . ' <i class="fa fa-fw fa-rub"></i>' ?>
        </td>
    </tr>
</table>
                    <?php

                } else {
                    //show discount
                    ?>
    <div>
                    <?php
                }
                echo Html::submitButton('Сохранить', ['class' => 'btn btn-success pull-right'])."</div>";
                ActiveForm::end();
                