<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Modal;
use common\models\Order;

$this->registerJs('
    $("document").ready(function(){
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
        $(".box-body").on("change", "#statusFilter", function() {
            $("#searchForm").submit();
        });
        $("body").on("change keyup paste cut", "#searchString", function() {
                if (timer) {
                    clearTimeout(timer);
                }
                timer = setTimeout(function() {
                    $("#searchForm").submit();
                }, 700);
            });
    });
        ');
?>

<section class="content-header">
    <h1>
        <i class="fa fa-home"></i>  Заказы ваших ресторанов
        <small>Список заказов подключенных вами ресторанов и их статус</small>
    </h1>
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
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?=
                            $form->field($searchModel, 'status')
                            ->dropDownList(['0' => 'Все', '1' => 'Новый', '2' => 'Отменен', '3' => 'Выполняется', '4' => 'Завершен'], ['id' => 'statusFilter'])
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
            <?php
            ActiveForm::end();
            Pjax::begin(['formSelector' => 'form', 'enablePushState' => false, 'id' => 'vendor-list', 'timeout' => 5000]);
            ?>
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
                                'attribute' => 'clientName',
                                'value' => 'client.name',
                                'label' => 'Ресторан',
                            ],
                            [
                                'attribute' => 'vendorName',
                                'value' => 'vendor.name',
                                'label' => 'Поставщик',
                            ],
                            [
                                'attribute' => 'clientManager',
                                'value' => 'createdByProfile.full_name',
                                'label' => 'Заказ создал',
                            ],
                            [
                                'attribute' => 'vendorManager',
                                'value' => 'acceptedByProfile.full_name',
                                'label' => 'Заказ принял',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'total_price',
                                'value' => function($data) {
                                    return (float)$data['total_price'] . '<i class="fa fa-fw fa-rub"></i>';
                                },
                                'label' => 'Сумма',
                                'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold'],        
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function($data) {
                                    $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => 'Дата создания',
                            ],
                            [
                                'attribute' => 'status',
                                'label'=>'Статус',
                                'format' => 'raw',
                                'value' => function($data) {
                                    $statusClass = "";
                                    switch ($data['status']) {
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                            $statusClass = 'new';
                                            break;
                                        case Order::STATUS_PROCESSING:
                                            $statusClass = 'processing';
                                            break;
                                        case Order::STATUS_DONE:
                                            $statusClass = 'done';
                                            break;
                                        case Order::STATUS_REJECTED:
                                        case Order::STATUS_CANCELLED:
                                            $statusClass = 'cancelled';
                                            break;
                                    }
                                    return '<span class="status ' . $statusClass . '"><i class="fa fa-circle-thin"></i> ' . Order::statusText($data['status']) . '</span>';//fa fa-circle-thin
                                },
                            ]
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
