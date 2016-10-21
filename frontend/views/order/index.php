<?php

use yii\helpers\Url;
use yii\grid\GridView;
use common\models\Order;
use common\models\Organization;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;

$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        $(".box-body").on("change", "#statusFilter", function() {
            $("#search-form").submit();
        });
        $(".box-body").on("change", "#orgFilter", function() {
            $("#search-form").submit();
        });
        $(".box-body").on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#search-form").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
        $(".box-body").on("click", "td", function (e) {
            var id = $(this).parent("tr").data("id");
            location.href = "' . Url::to(['order/view']) . '&id=" + id;
        });
    });
        ');
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
?>

<section class="content-header">
    <h1>
        <i class="fa fa-history"></i>  История заказов
        <small>Список всех созданных заказов</small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            'История заказов',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info order-history">
        <div class="box-body">

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status new"><?= $newCount ?></span>
                        <span class="info-box-text">Новые</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status processing"><?= $processingCount ?></span>
                        <span class="info-box-text">Выполняются</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status done"><?= $fulfilledCount ?></span>
                        <span class="info-box-text">Завершено</span>
                    </div>
                </div>    
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-total-price">
                    <div class="info-box-content">
                        <span class="info-box-number"><?= isset($totalPrice) ? $totalPrice : '0' ?> <i class="fa fa-fw fa-rub"></i></span>
                        <span class="info-box-text">Всего выполнено на сумму</span>
                    </div>
                </div>    
            </div>
            <div style="clear: both;">
            </div>
        </div>
        <!-- /.box-body -->
    </div>
    <div class="box box-info order-history">
        <div class="box-body">
            <?php
            Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]);
            $form = ActiveForm::begin([
                        'options' => [
                            'data-pjax' => true,
                            'id' => 'search-form',
                            'class' => "navbar-form",
                            'role' => 'search',
                        ],
                        'enableClientValidation' => false,
                        'method' => 'get',
            ]);
            ?>
            <?=
                    $form->field($searchModel, 'status')
                    ->dropDownList(['0' => 'Все', '1' => 'Новый', '2' => 'Отменен', '3' => 'Выполняется', '4' => 'Завершен'], ['id' => 'statusFilter'])
                    ->label('Статус')
            ?>
            <?php
            if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                echo $form->field($searchModel, 'vendor_id')
                        ->dropDownList($organization->getSuppliers('', true), ['id' => 'orgFilter'])
                        ->label('Поставщики');
            } else {
                echo $form->field($searchModel, 'client_id')
                        ->dropDownList($organization->getClients(), ['id' => 'orgFilter'])
                        ->label('Рестораны');
            }
            ?>
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
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true,
                        'endDate' => "0d",
                    ]
                ])
                ?>
            </div>
            <?php ActiveForm::end(); ?>
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
                    $organization->type_id == Organization::TYPE_RESTAURANT ? [
                        'attribute' => 'vendor.name',
                        'value' => 'vendor.name',
                        'label' => 'Поставщик',
                        //'headerOptions' => ['class'=>'sorting',],
                            ] : [
                        'attribute' => 'client.name',
                        'value' => 'client.name',
                        'label' => 'Ресторан',
                            ],
                    [
                        'attribute' => 'createdByProfile.full_name',
                        'value' => 'createdByProfile.full_name',
                        'label' => 'Заказ создал',
                    ],
                    [
                        'attribute' => 'acceptedByProfile.full_name',
                        'value' => 'acceptedByProfile.full_name',
                        'label' => 'Заказ принял',
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'total_price',
                        'value' => function($data) {
                            return "<b>$data->total_price</b><i class='fa fa-fw fa-rub'></i>";
                        },
                        'label' => 'Сумма',
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'created_at',
                        'value' => function($data) {
                            $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
                            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                        },
                        'label' => 'Дата создания',
                    ],
                    [
                        'format' => 'raw',
                        'attribute' => 'status',
                        'value' => function($data) {
                            switch ($data->status) {
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
                            return '<span class="status ' . $statusClass . '"><i class="fa fa-circle-thin"></i> ' . Order::statusText($data->status) . '</span>'; //fa fa-circle-thin
                        },
                        'label' => 'Статус',
                    ],
                ],
                'rowOptions' => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
            ]);
            ?>
            <?php Pjax::end() ?>
            <!-- /.table-responsive -->
        </div>
        <!-- /.box-body -->
    </div>
</section>
