<?php
use yii\data\ArrayDataProvider;
use kartik\grid\GridView;
use kartik\editable\Editable;
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
    //'tableOptions' => ['class' => 'table no-margin table-hover'],
    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable'],
    'options' => ['class' => 'table-responsive'],
    'panel' => false,
    'bootstrap' => false,
    'columns' => [
        [
            'format' => 'raw',
            'attribute' => 'product_name',
            'value' => function($data) {
                return "<div class='grid-prod'>" . $data['product_name'] . "</div><div class='grid-article'>Артикул: "
                        . $data['article'] . "</div><div>"
                        . $data['price'] . ' <i class="fa fa-fw fa-rub"></i> / ' . $data['units'] . "</div>";
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
                            'buttonup_class' => 'btn btn-default',
                            'buttondown_class' => 'btn btn-default',
                            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                        ],
                    ],
                    'submitButton' => [
                        'class' => 'btn btn-sm btn-success kv-editable-submit',
                    ],
                    'pluginEvents' => [
                        "editableSuccess" => "function(event, val, form, data) { "
                        //  . '$.pjax.reload({container: "#checkout"});'
                        . "$('#orderTotal' + data.orderId).html(data.orderTotal); "
                        . "$('#total' + data.positionId).html(data.positionTotal); "
                        . "$('.total-cart span').html(data.totalCart);"
                        . "}",
                    ],
                ];
            },
                ],
                [
                    'format' => 'raw',
                    'header' => 'Цена',
                    'value' => function ($data) use ($vendor_id) {
                                $total = $data['price'] * $data['quantity'];
                        $btnNote = Html::a('<i class="fa fa-comment m-r-xs"></i> Заметка', Url::to(['order/ajax-set-note', 'product_id' => $data['product_id']]), [
                                    'class' => 'add-note btn btn-default margin-right-5',
                                    'data' => [
                                        'id' => $data['product_id'],
                                        'target' => "#changeNote",
                                        'toggle' => "modal",
                                        'backdrop' => "static",
                                    ],
                        ]);
                        $btnDelete = Html::a('<i class="fa fa-trash m-r-xxs"></i> Удалить', '#', [
                                    'class' => 'btn btn-outline-danger remove',
                                    'data-product_id' => $data['product_id'],
                                    'data-vendor_id' => $vendor_id,
                        ]);
                        return "<span id=total$data[id]>$total</span> " . '<i class="fa fa-fw fa-rub"></i> <div class="pull-right">' . $btnNote . $btnDelete . '</div>';
                    },
//                            'contentOptions' => ['class' => 'text-center'],
//                            'headerOptions' => ['style' => 'width:200px']
                        ],
                    ]
                ]);
                