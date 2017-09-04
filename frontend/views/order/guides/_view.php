<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\grid\GridView;
use kartik\touchspin\TouchSpin;
use kartik\form\ActiveForm;
?>

<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span></button>
    <h4 class="modal-title"><?= $guide->name ?></h4>
    <span>Вы можете управлять гидом в данном заказе</span>
    <div class="guid-header">
        <?php
        $form = ActiveForm::begin([
                    'options' => [
                        'id' => 'searchProductForm',
                        'role' => 'search',
                        'action' => ['order/ajax-show-guide', 'id' => $guide->id]
                    ],
        ]);
        ?>
        <?=
                $form->field($guideSearchModel, 'searchString', [
                    'addon' => [
                        'append' => [
                            'content' => '<a class="btn-xs"><i class="fa fa-search"></i></a>',
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
                    'placeholder' => 'Поиск по названию'])
                ->label(false)
        ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">   
            <?php
            Pjax::begin(['formSelector' => '#searchProductForm', 'enablePushState' => false, 'id' => 'guideProductList', 'timeout' => 30000, 'clientOptions' => ['url' => Url::to(['/order/ajax-show-guide', 'id' => $guide->id])]]);
            ?>
            <?=
            GridView::widget([
                'dataProvider' => $guideDataProvider,
                'filterModel' => $guideSearchModel,
                'filterPosition' => false,
                'summary' => '',
                'showHeader' => false,
                'tableOptions' => ['class' => 'table table-hover'],
                'columns' => [
                    [
                        'format' => 'raw',
                        'attribute' => 'baseProduct.product',
                        'value' => function($data) {
                            return "<div class='guid_block_create_title'><p>" . $data->baseProduct->product . "</p></div>"
                                    . "<div class='guid_block_create_counts'><p>" . $data->baseProduct->vendor->name . "</p></div>";
                        },
                        'contentOptions' => ['style' => 'width: 40%;'],
                    ],
                    ['format' => 'raw',
                        'attribute' => 'price',
                        'value' => function($data) {
                            return $data->price . ' РУБ/' . $data->baseProduct->ed;
                        },
                        'contentOptions' => ['style' => 'width: 20%;'],
                    ],
                    [
                        'attribute' => 'quantity',
                        'content' => function($data) {
                            $units = $data->baseProduct->units;
                            return TouchSpin::widget([
                                        'name' => '',
                                        'pluginOptions' => [
                                            'initval' => 0.100,
                                            'min' => (isset($units) && ($units > 0)) ? $units : 0.001,
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
                        'value' => function($data) {
                            return Html::button('<i class="fa fa-comment"> Комментарий</i>', [
                                        'class' => 'add-note btn btn-md btn-gray pull-right',
                                        'data' => [
                                            'id' => $data->cbg_id,
                                            'url' => Url::to(['order/ajax-set-note', 'product_id' => $data->cbg_id]),
                                            'toggle' => "tooltip",
                                            'placement' => "bottom",
                                            'original-title' => $data->note,
                                        ],
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 5%;'],
                    ],
                    [
                        'format' => 'raw',
                        'value' => function ($data) {
                            return Html::button('<i class="fa fa-shopping-cart"> В корзину</i>', [
                                        'class' => 'add-to-cart btn btn-md btn-success pull-right',
                                        'data-id' => $data->cbg_id,
                                        'data-cat' => $data->baseProduct->cat_id,
                                        'title' => 'Добавить в корзину',
                            ]);
                        },
                        'contentOptions' => ['style' => 'width: 5%;'],
                    ],
                ],
            ]);
            ?>
            <?php Pjax::end(); ?>
            <button style="margin-top: 20px;margin-right: 7px;" class="btn btn-md btn-success pull-right add-guide-to-cart" data-id="<?=$guide->id ?>"><i class="fa fa-paper-plane-o"></i> Добавить все</button> 
        </div>
    </div>
</div>
