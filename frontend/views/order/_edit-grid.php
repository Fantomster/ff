<?php

//use kartik\grid\GridView;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use kartik\widgets\TouchSpin;
use kartik\form\ActiveForm;
use common\models\Order;

$form = ActiveForm::begin([
            'id' => 'editOrder',
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
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable order-table'],
    'options' => ['class' => 'table-responsive'],
//    'panel' => false,
//    'bootstrap' => false,
//    'resizableColumns' => false,
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
            'label' => 'Товар',
        ],
        [
            'attribute' => 'quantity',
            'content' => function($data) {
                return TouchSpin::widget([
                            'name' => "OrderContent[$data->id][quantity]",
                            'pluginOptions' => [
                                'initval' => $data->quantity,
                                'min' => (isset($data->units) && ($data->units)) ? $data->units : 0.001,
                                'max' => PHP_INT_MAX,
                                'step' => (isset($data->units) && ($data->units)) ? $data->units : 1,
                                'decimals' => (empty($data->units) || (fmod($data->units, 1) > 0)) ? 3 : 0,
                                'forcestepdivisibility' => (isset($data->units) && ($data->units)) ? 'floor' : 'none',
                                'buttonup_class' => 'btn btn-default',
                                'buttondown_class' => 'btn btn-default',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                            'options' => ['class' => 'viewData', 'id' => 'qnty' . $data->id],
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
                                        'buttonup_class' => 'btn btn-default',
                                        'buttondown_class' => 'btn btn-default',
                                        'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                        'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                                    ],
                                    'options' => ['class' => 'viewData'],
                        ]);
                    },
                            'contentOptions' => ['class' => 'width150'],
                                ] : [ 'format' => 'raw',
                            'attribute' => 'price',
                            'value' => function($data) {
                                return '<b>' . $data->price . '</b> <i class="fa fa-fw fa-rub"></i>';
                            },
                            'label' => 'Цена',
                            'contentOptions' => ['class' => 'width150'],
                                ],
                        [
                            'format' => 'raw',
                            'attribute' => 'total',
                            'value' => function($data) {
                                return '<b>' . $data->total . '</b> <i class="fa fa-fw fa-rub"></i>';
                            },
                            'label' => 'Сумма',
                            'contentOptions' => ['class' => 'width150'],
                        ],
                        [
                            'format' => 'raw',
                            'value' => function($data) {
                                return '<a href="#" class="deletePosition btn btn-outline-danger" data-target="#qnty' . $data->id . '" title="Удалить позицию"><i class="fa fa-trash m-r-xxs"></i></a>';
                            },
                            'contentOptions' => ['class' => 'text-center', 'style' => 'width: 50px;'],
                        ],
                    ],
                ]);
                $discountTypes = Order::discountDropDown();
                if ($priceEditable) {
                    //editable discount
                    ?>
                    <table class="table table-bordered table-striped dataTable tbl-discount">
<!--                        <tr>
                            <th>
                                Стоимость заказа
                            </th><td>
                                <?= '<b>' . 'скоро будет' . '</b> <i class="fa fa-fw fa-rub"></i>' ?>
                            </td>
                        </tr>-->
                        <tr>
                            <th>
                                <?= $form->field($order, 'discount_type')->dropDownList($discountTypes)->label(false) ?>
                            </th><td>
                                <?= $form->field($order, 'discount')->label(false) ?>
                            </td>
                        </tr><tr>
                            <th>
                                Стоимость доставки
                            </th><td>
                                <?= '<b>' . $order->calculateDelivery() . '</b> <i class="fa fa-fw fa-rub"></i>' ?>
                            </td>
                        </tr><tr>
                            <th>
                                Итого
                            </th><td>
                                <?= '<b>' . $order->total_price . '</b> <i class="fa fa-fw fa-rub"></i>' ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                } else {
                    //show discount
                    ?>
                    <table class="table dataTable tbl-discount">
<!--                        <tr>
                            <th>
                                Стоимость заказа
                            </th><td>
                                <?= '<b>' . 'скоро будет' . '</b> <i class="fa fa-fw fa-rub"></i>' ?>
                            </td>
                        </tr>-->
                        <tr>
                            <th>
                                <?= ($order->discount_type) ? $discountTypes[$order->discount_type] : 'Скидка' ?>
                            </th><td>
                                <?= ($order->discount) ? $order->discount : '-' ?>
                            </td>
                        </tr><tr>
                            <th>
                                Стоимость доставки
                            </th><td>
                                <?= '<b>' . $order->calculateDelivery() . '</b> <i class="fa fa-fw fa-rub"></i>' ?>
                            </td>
                        </tr><tr>
                            <th>
                                Итого
                            </th><td>
                                <?= '<b>' . $order->total_price . '</b> <i class="fa fa-fw fa-rub"></i>' ?>
                            </td>
                        </tr>
                    </table>
                    <?php
                }
                echo Html::submitButton('<i class="icon fa fa-save"></i> Сохранить', ['class' => 'btn btn-success pull-right']);
                ActiveForm::end();
                