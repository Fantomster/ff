<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Modal;

$this->registerJs('
    $("document").ready(function(){
        $("#vendorInfo").data("bs.modal", null);
        var justSubmitted = false;
        $("body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
        $("body").on("hidden.bs.modal", "#vendorInfo", function() {
                $(this).data("bs.modal", null);
            });
    });
        ');
?>

<section class="content-header">
    <h1>
        <i class="fa fa-home"></i>  Ваши поставщики
        <small>Подключенные Вами поставщики и информация о них</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'Список ваших поставщиков',
        ],
    ])
    ?>
</section>
<section class="content">

    <div class="box box-info order-history">
        <div class="box-body">
            <?php
            Pjax::begin(['enablePushState' => false, 'id' => 'vendor-list',]);
            $form = ActiveForm::begin([
                        'options' => [
                            'data-pjax' => true,
                            'id' => 'search-form',
                            //'class' => "navbar-form",
                            'role' => 'search',
                        ],
                        'enableClientValidation' => false,
                        'method' => 'get',
            ]);
            ?>
            <div class="row">
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?=
                            $form->field($searchModel, 'searchString', [
                                'addon' => [
                                    'prepend' => [
                                        'content' => '<i class="fa fa-search"></i>',
                                    ],
                                ],
                                'options' => [
                                    'class' => "margin-right-15 form-group",
                                ],
                            ])
                            ->textInput([
                                'id' => 'searchString',
                                'class' => 'form-control',
                                'placeholder' => 'Поиск'])
                            ->label('Статус', ['style' => 'color:#555'])
                    ?>
                </div>

                <div class="col-lg-5 col-md-6 col-sm-6"> 
                    <?= Html::label('Начальная дата / Конечная дата', null, ['style' => 'color:#555']) ?>
                    <div class="form-group" style="width: 300px; height: 44px;">
                        <?=
                        DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'date_from',
                            'attribute2' => 'date_to',
                            'options' => ['placeholder' => 'Дата', 'id' => 'dateFrom'],
                            'options2' => ['placeholder' => 'Конечная дата', 'id' => 'dateTo'],
                            'separator' => '-',
                            'type' => DatePicker::TYPE_RANGE,
                            'pluginOptions' => [
                                'format' => 'dd.mm.yyyy', //'d M yyyy',//
                                'autoclose' => true,
                                'endDate' => "0d",
                            ]
                        ])
                        ?>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <div class="row">
                <div class="col-md-12">
                    <?=
                    GridView::widget([
                        'id' => 'orderHistory',
                        'dataProvider' => $dataProvider,
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                        'filterModel' => $searchModel,
                        'filterPosition' => false,
                        'summary' => '',
                        'options' => ['class' => 'table-responsive'],
                        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'value' => 'id',
                                'label' => '№',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'name',
                                'value' => function ($data) {
                                    $link = Html::a($data["name"], ['organization/ajax-show-vendor', 'id' => $data["id"]], [
                                                'data' => [
                                                    'target' => '#vendorInfo',
                                                    'toggle' => 'modal',
                                                    'backdrop' => 'static',
                                                ]
                                    ]);
                                    return $link;
                                },
                                        'label' => 'Имя поставщика',
                                    ],
                                    [
                                        'attribute' => 'clientCount',
                                        'value' => 'clientCount',
                                        'label' => 'Кол-во ресторанов',
                                    ],
                                    [
                                        'attribute' => 'orderCount',
                                        'value' => 'orderCount',
                                        'label' => 'Кол-во заказов',
                                    ],
                                    [
                                        'attribute' => 'orderSum',
                                        'value' => 'orderSum',
                                        'label' => 'Сумма заказов',
                                    ],
                                    [
                                        'format' => 'raw',
                                        'attribute' => 'created_at',
                                        'value' => function($data) {
                                            $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                        },
                                        'label' => 'Дата регистрации',
                                    ],
                                    [
                                        'attribute' => 'contact_name',
                                        'value' => 'contact_name',
                                        'label' => 'Контакт',
                                    ],
                                    [
                                        'attribute' => 'phone',
                                        'value' => 'phone',
                                        'label' => 'Телефон',
                                    ],
                                ],
                            ]);
                            ?>
                        </div></div>
                    <?php Pjax::end() ?>
                    <!-- /.table-responsive -->
                </div>
                <!-- /.box-body -->
            </div>
            <?php
            Modal::begin([
                'id' => 'vendorInfo',
            ]);
            ?>
            <?php Modal::end(); ?>
</section>
