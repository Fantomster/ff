<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\touchspin\TouchSpin;
use kartik\form\ActiveForm;


$Url = Url::to(['order/ajax-show-products', 'order_id'=>$order->id]);

$this->registerJs('
     $(document).on("click", ".add-to-cart", function(e) {
                e.preventDefault();
                quantity = $(this).parent().parent().find(".quantity").val();
                $.post(
                    "' . Url::to(['/order/ajax-add-to-order']) . '",
                    {"product_id": $(this).data("id"), "quantity": quantity, "order_id": '.$order->id.', "cat_id": $(this).data("cat")}
                ).done(function(result) {
                   $.pjax.reload({container:\'#AjaxProductList\', timeout: 16000, url:"'.$Url.'"}); 
                });
            });

    $(document).on("change", "#productssearch-sort", function() {
        var sort = $(this).val();
            $.pjax({
             type: "GET",
             push: false,
             timeout: 10000,
             url: "' . $Url . '",
             container: "#AjaxProductList",
             data: {
                    sort: sort,
                   }
   }).done(function() { console.log(222); });
    });
    ', \yii\web\View::POS_READY);
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span></button>
    <h4 class="modal-title"><?= $order->vendor->name ?></h4>
    <div class="products-header">
        <?php
        $form = ActiveForm::begin([
                    'options' => [
                        'id' => 'searchProductForm',
                        'role' => 'search',
                        'action' => ['order/ajax-show-products', 'order_id' => $order->id]
                    ],
        ]);
        ?>

        <?=
                $form->field($productsSearchModel, 'searchString', [
                    'addon' => [
                        'append' => [
                            'content' => '<a class="btn-xs btnSubmit" data-target-form="#searchProductForm"><i class="fa fa-search"></i></a>',
                            'options' => [
                                'class' => 'append',
                            ],
                        ],
                    ],
                    'options' => [
                        'class' => "form-group",
                    ],
                ])
                ->textInput([
                    'id' => 'searchString',
                    'class' => 'form-control',
                    'placeholder' => Yii::t('message', 'frontend.views.order.guides.search', ['ru'=>'Поиск по названию'])])
                ->label(false)
        ?>

        <?=
        $form->field($productsSearchModel, 'sort', [
            'options' => [
                'id' => 'alSortSelect',
                'class' => "form-group"
            ],
        ])
            ->dropDownList([
                '1' => Yii::t('app', 'frontend.views.guides.sort_by', ['ru' => 'Сортировка по']),
                'id 3' => Yii::t('app', 'frontend.views.guides.sort_by_time_asc', ['ru' => 'Порядку добавления по возрастанию']),
                'id 4' => Yii::t('app', 'frontend.views.guides.sort_by_time_desc', ['ru' => 'Порядку добавления по убыванию']),
                'product 3' => Yii::t('app', 'frontend.views.guides.sort_by_name_asc', ['ru' => 'Наименованию по возрастанию']),
                'product 4' => Yii::t('app', 'frontend.views.guides.sort_by_name_desc', ['ru' => 'Наименованию по убыванию']),
            ], [
                'options' => [$params['sort'] ?? 1 => ['selected' => true], '1' => ['disabled' => true]]])
            ->label(false)
        ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">   
            <?php
            Pjax::begin(['formSelector' => '#searchProductForm', 'enablePushState' => false, 'id' => 'AjaxProductList', 'timeout' => 30000, 'clientOptions' => ['url' => Url::to(['/order/ajax-show-products', 'order_id' => $order->id])]]);
            ?>
            <?php
            $form = ActiveForm::begin([
                        'options' => [
                            'id' => 'gridForm',
                        ],
            ]);
            ?>
            <?=
            GridView::widget([
                'dataProvider' => $productsDataProvider,
                'filterModel' => $productsSearchModel,
                'filterPosition' => false,
                'summary' => '',
                'showHeader' => false,
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    [
                        'format' => 'raw',
                        'attribute' => 'baseProduct.product',
                        'value' => function($data) {
                            return "<div class='guid_block_create_title'><p>" . $data["product"] . "</p></div>"
                                    . "<div class='guid_block_create_counts'><p>" . $data["name"] . "</p></div>";
                        },
                        'contentOptions' => ['style' => 'width: 40%;'],
                    ],
                    ['format' => 'raw',
                        'attribute' => 'price',
                        'value' => function($data) {
                            return $data["price"] . ' ' . $data["symbol"] . '/' . $data["ed"];
                        },
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    [
                        'attribute' => 'quantity',
                        'content' => function($data) {
                            $units = $data["units"];
                            return TouchSpin::widget([
                                        'name' => 'GuideProduct[' . $data["cbg_id"] . ']',
                                        'pluginOptions' => [
                                            'initval' => 0, //0.100,
                                            'min' => 0, //(isset($units) && ($units > 0)) ? $units : 0.001,
                                            'max' => PHP_INT_MAX,
                                            'step' => (isset($units) && ($units)) ? $units : 1,
                                            'decimals' => (empty($units) || (fmod($units, 1) > 0)) ? 3 : 0,
                                            'forcestepdivisibility' => (isset($units) && $units && (floor($units) == $units)) ? 'floor' : 'none',
                                            'buttonup_class' => 'btn btn-default',
                                            'buttondown_class' => 'btn btn-default',
                                            'buttonup_txt' => '<i class="glyphicon glyphicon-plus-sign"></i>',
                                            'buttondown_txt' => '<i class="glyphicon glyphicon-minus-sign"></i>'
                                        ],
                                        'options' => ['class' => 'quantity form-control '],
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    [
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Html::button('<i class="fa fa-shopping-cart"> <span class="circe_font">' . Yii::t('message', 'frontend.views.order.guides.in_basket_two', ['ru'=>'В корзину']) . ' </span></i>', [
                                        'class' => 'add-to-cart btn btn-md btn-success pull-right disabled',
                                        'data-id' => $data["cbg_id"],
                                        'data-cat' => $data["cat_id"],
                                        'title' => Yii::t('message', 'frontend.views.order.guides.add_in_basket', ['ru'=>'Добавить в корзину']),
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 10%;'],
                    ],
                ],
            ]);
            ?>
            <?php ActiveForm::end(); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
