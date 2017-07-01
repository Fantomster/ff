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
        $("#clientInfo").data("bs.modal", null);
        var justSubmitted = false;
        var timer;
        $("body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#searchForm").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
        $("body").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
        $("body").on("hidden.bs.modal", "#clientInfo", function() {
                $(this).data("bs.modal", null);
            });
        $("body").on("click", "td", function (e) {
            if ($(this).find("a").hasClass("stats")) {
                document.location = $(this).find("a").attr("href");
                return false;
            }
            var url = $(this).parent("tr").data("url");
            if (url !== undefined) {
                $("#clientInfo").modal({backdrop:"static",toggle:"modal"}).load(url);
            }
        });
    });
        ');
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
?>

<section class="content-header">
    <h1>
        <i class="fa fa-home"></i>  Ваши рестораны
        <small>Подключенные Вами рестораны и информация о них</small>
    </h1>
    <?=
    ''
//    Breadcrumbs::widget([
//        'options' => [
//            'class' => 'breadcrumb',
//        ],
//        'links' => [
//            'Список ваших ресторанов',
//        ],
//    ])
    ?>
</section>
<section class="content">

    <div class="box box-info order-history">
        <div class="box-body">
            <?php
            $form = ActiveForm::begin([
                        'options' => [
                            'id' => 'searchForm',
                            //'class' => "navbar-form",
                            'role' => 'search',
                        ],
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
                            ->label('Поиск', ['style' => 'color:#555'])
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
            <?php
            ActiveForm::end();
            Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'client-list', 'timeout' => 5000]);
            ?>
            <div class="row">
                <div class="col-md-12">
                    <?=
                    GridView::widget([
                        'id' => 'clientsList',
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
                                'value' => function($data) {
                                    if($data['self_registered']==\common\models\FranchiseeAssociate::SELF_REGISTERED){
                                        return "<i title='Клиент самостоятельно зарегистрировался' class='fa fa-bolt text-success' aria-hidden='true'></i>" . $data['name'];
                                    }
                                    return $data['name'];
                                },
                                'label' => 'Имя ресторана',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'vendorCount',
                                'value' => function ($data) {
                                    $progress = $data["vendorCount"] > 0 ? round($data["vendorCount_prev30"] * 100 / $data["vendorCount"], 2) : 0;
//                                            if ($progress > 0) {
                                    $divider = '<i class="fa fa-caret-up"></i>';
                                    //                                          }
                                    $class = "text-red";
                                    if ($progress > 20) {
                                        $class = "text-green";
                                    } elseif ($progress > 0) {
                                        $class = " text-orange";
                                    }
                                    return $data["vendorCount"] . " <span class='description-percentage $class'>$divider $progress%";
                                },
                                'label' => 'Кол-во поставщиков',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'orderCount',
                                'value' => function ($data) {
                                    $progress = $data["orderCount"] > 0 ? round($data["orderCount_prev30"] * 100 / $data["orderCount"], 2) : 0;
//                                            if ($progress > 0) {
                                    $divider = '<i class="fa fa-caret-up"></i>';
                                    //                                          }
                                    $class = "text-red";
                                    if ($progress > 20) {
                                        $class = "text-green";
                                    } elseif ($progress > 0) {
                                        $class = " text-orange";
                                    }
                                    return $data["orderCount"] . " <span class='description-percentage $class'>$divider $progress%";
                                },
                                'label' => 'Кол-во заказов',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'orderSum',
                                'value' => function ($data) {
                                    $progress = $data["orderSum"] > 0 ? round($data["orderSum_prev30"] * 100 / $data["orderSum"], 2) : 0;
//                                            if ($progress > 0) {
                                    $divider = '<i class="fa fa-caret-up"></i>';
                                    //                                          }
                                    $class = "text-red";
                                    if ($progress > 20) {
                                        $class = "text-green";
                                    } elseif ($progress > 0) {
                                        $class = " text-orange";
                                    }
                                    return ($data["orderSum"] ? $data["orderSum"] : 0) . " руб. <span class='description-percentage $class'>$divider $progress%";
                                },
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
                            [
                                'format' => 'raw',
                                'value' => function($data) {
                                    return Html::a('<i class="fa fa-signal"></i>', ['analytics/client-stats', 'id' => $data["id"]], ['class' => 'stats']);
                                },
                                    ],
                                ],
                                'rowOptions' => function ($model, $key, $index, $grid) {
                            return ['data-url' => Url::to(['organization/ajax-show-client', 'id' => $model["id"]])];
                        },
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
                'id' => 'clientInfo',
            ]);
            ?>
            <?php Modal::end(); ?>
</section>
