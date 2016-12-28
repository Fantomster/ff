<?php

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use kartik\widgets\TouchSpin;
use yii\helpers\Html;
use yii\helpers\Url;

$dataProvider = new ArrayDataProvider([
    'key' => 'id',
    'allModels' => $content,
        ]);

$any = current($content);

echo GridView::widget([
    'id' => isset($any) ? 'orderContent' . $any['order_id'] : '',
    'dataProvider' => $dataProvider,
    'summary' => '',
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
    'options' => ['class' => 'table-responsive'],
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product_name',
            'value' => function($data) {
                return "<div class='grid-prod'>" . $data['product_name'] . "</div><div class='grid-article'>Артикул: "
                        . $data['article'] . "</div><div>"
                        . $data['price'] . ' <i class="fa fa-fw fa-rub"></i></div>';
            },
            'label' => 'Название продукта',
        ],
        [
            'format' => 'raw',
            'value' => function($data) {
                return TouchSpin::widget([
                            'name' => "OrderContent[" . $data["id"] . "][quantity]",
                            'pluginOptions' => [
                                'initval' => $data["quantity"],
                                'min' => (isset($data['units']) && ($data['units'])) ? $data['units'] : 0.001,
                                'max' => PHP_INT_MAX,
                                'step' => (isset($data['units']) && ($data['units'])) ? $data['units'] : 1,
                                'decimals' => (!isset($data["units"]) || (fmod($data["units"], 1) > 0)) ? 3 : 0,
                                'forcestepdivisibility' => (isset($data['units']) && ($data['units'])) ? 'floor' : 'none',
                                'buttonup_class' => 'btn btn-default',
                                'buttondown_class' => 'btn btn-default',
                                'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                            ],
                            'options' => ['class' => 'quantity form-control '],
                ]) . Html::hiddenInput("OrderContent[$data[id]][id]", $data["id"]);
                // return Html::textInput('', 1, ['class' => 'quantity form-control']);
            },
                    'label' => 'Количество',
                    'contentOptions' => ['class' => 'width150'],
                    'headerOptions' => ['class' => 'width150']
                ],
                [
                    'format' => 'raw',
                    'header' => 'Цена',
                    'value' => function ($data) {
                        $total = $data['price'] * $data['quantity'];
                        return "<span id=total$data[id]>$total</span> " . '<i class="fa fa-fw fa-rub"></i>';
                    },
                    'headerOptions' => ['class' => 'width100']
                ],
                [
                    'format' => 'raw',
                    'header' => 'Кратность',
                    'value' => function ($data) {
                        return $data["units"];
                    },
                    'headerOptions' => ['class' => 'width70']
                ],
                [
                    'format' => 'raw',
                    'value' => function($data) use ($vendor_id) {
                        $btnNote = Html::a('<i class="fa fa-comment m-r-xs"></i> <span class="hidden-fk">Комментарий к товару</span>', Url::to(['order/ajax-set-note', 'product_id' => $data['product_id']]), [
                                    'class' => 'add-note btn btn-default margin-right-5',
                                    'data' => [
                                        'id' => $data['product_id'],
                                        'target' => "#changeNote",
                                        'toggle' => "modal",
                                        'backdrop' => "static",
                                        'internal' => "1",
                                    ],
                        ]);
                        $btnDelete = Html::a('<i class="fa fa-trash m-r-xxs"></i> <span class="hidden-fk">Удалить</span>', '#', [
                                    'class' => 'btn btn-outline-danger remove',
                                    'data-product_id' => $data['product_id'],
                                    'data-vendor_id' => $vendor_id,
                                    'data-internal' => '1',
                        ]);
                        return '<div class="pull-right">' . $btnNote . $btnDelete . '</div>';
                    },
                            'headerOptions' => ['class' => 'checkout-action'],
                        ],
                    ]
                ]);
                