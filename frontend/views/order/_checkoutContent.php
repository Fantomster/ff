<?php

use yii\data\ArrayDataProvider;
use kartik\grid\GridView;
use kartik\editable\Editable;

$dataProvider = new ArrayDataProvider([
    'key' => 'id',
    'allModels' => $content,
        ]);

$any = current($content);

echo GridView::widget([
    'id' => isset($any) ? 'orderContent' . $any['order_id'] : '',
    'dataProvider' => $dataProvider,
    'summary' => '',
    //'tableOptions' => ['class' => 'table no-margin table-hover'],
    'tableOptions' => ['class'=>'table table-bordered table-striped dataTable'],
    'options' => ['class' => 'table-responsive'],
    'panel' => false,
    'bootstrap' => false,
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product_name',
            'value' => function($data) {
                return "<div class='grid-prod'>" . $data['product_name'] . "</div><div class='grid-article'>артикул: "
                        . $data['article'] . "</div><div>"
                        . $data['price'] . ' руб / ' . $data['units'] . "</div>";
            },
            'label' => 'Название продукта',
        ],
        [
            'class' => 'kartik\grid\EditableColumn',
            'attribute' => 'quantity',
            'readonly' => false,
            'content' => function($data) {
                return '<div class="text_content">' . htmlentities($data['quantity']) . '</div>';
            },
            'editableOptions' => function ($model, $key, $index) {
                return [
                    'header' => 'Количество',
                    'inputType' => Editable::INPUT_SPIN,
                    'asPopover' => false,
                    'options' => [
                        'id' => 'posQtty' . $model['id'],
                        'options' => [
                            'id' => 'posQttyIn' . $model['id'],
                        ],
                        'pluginOptions' => [
                            'initval' => 'quantity',
                            'min' => 1,
                            'max' => PHP_INT_MAX,
                            'step' => 1,
                            'decimals' => 0,
                            'buttonup_class' => 'btn btn-primary',
                            'buttondown_class' => 'btn btn-info',
                            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                        ],
                    ],
                    'pluginEvents' => [
                        "editableSuccess" => "function(event, val, form, data) { "
                        . "$('#orderTotal' + data.orderId).html(data.orderTotal); "
                        . "$('#total' + data.positionId).html(data.positionTotal); "
                        . "}",
                    ],
                ];
            },
                ],
                [
                    'format' => 'raw',
                    'value' => function($data) {
                        $total = $data['price'] * $data['quantity'];
                        return "<span id=total$data[id]>$total</span> руб";
                    },
                ],
            ]
        ]);
        