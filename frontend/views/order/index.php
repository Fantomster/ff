<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use common\models\Order;
use common\models\Organization;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;

$this->title = 'Заказы';
$urlExport = Url::to(['/order/export-to-xls']);
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
            if($(this).find("input").hasClass("checkbox-export")){
                return true;
            }
            if ($(this).find("a").hasClass("reorder") || 
                $(this).find("a").hasClass("complete")
            ){
                return true;
            }
            
            var url = $(this).parent("tr").data("url");
            if (url !== undefined) {
                location.href = url;
            }
        });

        $(document).on("click", ".reorder, .complete", function(e) {
            e.preventDefault();
            clicked = $(this);
            swal({
                title: clicked.data("original-title") + "?",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Да",
                cancelButtonText: "Отмена",
                showLoaderOnConfirm: true,
            }).then(function() {
                document.location = clicked.data("url")
            });
        });
    });
    $(document).on("click", ".export-to-xls", function(e) {
        if($("#orderHistory").yiiGridView("getSelectedRows").length > 0){
            window.location.href = "' . $urlExport . '?selected=" +  $("#orderHistory").yiiGridView("getSelectedRows");  
        }
    });
');
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-history"></i>  Заказы
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

            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status new"><?= $newCount ?></span>
                        <span class="info-box-text">Новые</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status processing"><?= $processingCount ?></span>
                        <span class="info-box-text">Выполняются</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status done"><?= $fulfilledCount ?></span>
                        <span class="info-box-text">Завершено</span>
                    </div>
                </div>    
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
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
                            $form->field($searchModel, 'status')
                            ->dropDownList(['0' => 'Все', '1' => 'Новый', '2' => 'Отменен', '3' => 'Выполняется', '4' => 'Завершен'], ['id' => 'statusFilter'])
                            ->label('Статус', ['class' => 'label', 'style' => 'color:#555'])
                    ?>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?php
                    if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                        echo $form->field($searchModel, 'vendor_id')
                                ->dropDownList($organization->getSuppliers('', true), ['id' => 'orgFilter'])
                                ->label('Поставщики', ['class' => 'label', 'style' => 'color:#555']);
                    } else {
                        echo $form->field($searchModel, 'client_id')
                                ->dropDownList($organization->getClients(), ['id' => 'orgFilter'])
                                ->label('Рестораны', ['class' => 'label', 'style' => 'color:#555']);
                    }
                    ?>
                </div>
                <div class="col-lg-5 col-md-6 col-sm-6"> 
                        <?= Html::label('Начальная дата / Конечная дата', null, ['class' => 'label', 'style' => 'color:#555']) ?>
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
                <div class="col-lg-5 col-md-6 col-sm-6">
                    
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?php if($organization->type_id == Organization::TYPE_SUPPLIER ){ ?>
            <?= Html::submitButton('<i class="fa fa-file-excel-o"></i> отчет xls', ['class' => 'btn btn-success export-to-xls']) ?>
            <?php }?>
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
                                'visible'=> ( $organization->type_id == Organization::TYPE_SUPPLIER ) ? true : false,
                                'class' => 'yii\grid\CheckboxColumn',
                                'contentOptions'   =>   ['class' => 'small_cell_checkbox'],
                                'checkboxOptions' => function($model, $key, $index, $widget){
                                    return ['value' => $model['id'],'class'=>'checkbox-export'];
                                }
                            ],
                            [
                                'attribute' => 'id',
                                'value' => 'id',
                                'label' => '№',
                                'contentOptions'   =>   ['class' => 'small_cell_id'],
                            ],
                            $organization->type_id == Organization::TYPE_RESTAURANT ? [
                                'attribute' => 'vendor.name',
                                'value' => 'vendor.name',
                                'contentOptions'   =>   ['class' => 'small_cell_supp'],
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
                                'contentOptions'   =>   ['class' => 'small_cell_sozdal'],
                            ],
                            [
                                'attribute' => 'acceptedByProfile.full_name',
                                'value' => 'acceptedByProfile.full_name',
                                'label' => 'Заказ принял',
                                'contentOptions'   =>   ['class' => 'small_cell_prinyal'],
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'total_price',
                                'value' => function($data) {
                                    return "<b>$data->total_price</b><i class='fa fa-fw fa-rub'></i>";
                                },
                                'label' => 'Сумма',
                                'contentOptions'   =>   ['class' => 'small_cell_sum'],
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function($data) {
                                    $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => 'Дата создания',
                                'contentOptions'   =>   ['class' => 'small_cell_date'],
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
                                    return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>'; //<i class="fa fa-circle-thin"></i> 
                                },
                                'label' => 'Статус',
                                'contentOptions'   =>   ['class' => 'small_cell_status'],
                            ],
                            [
                                'format' => 'raw',
                                'visible' => ($organization->type_id == Organization::TYPE_RESTAURANT),
                                'value' => function($data) {
                                    switch ($data->status) {
                                        case Order::STATUS_DONE:
                                        case Order::STATUS_REJECTED:
                                        case Order::STATUS_CANCELLED:
                                            return Html::a('Повторить', '#', [
                                                        'class' => 'reorder btn btn-outline-processing',
                                                        'data' => [
                                                            'toggle' => 'tooltip',
                                                            'original-title' => 'Повторить заказ',
                                                            'url' => Url::to(['order/repeat', 'id' => $data->id])
                                                        ],
                                            ]);
                                            break;
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                        case Order::STATUS_PROCESSING:
                                            if ($data->isObsolete) {
                                                return Html::a('Завершить', '#', [
                                                            'class' => 'complete btn btn-outline-success',
                                                            'data' => [
                                                                'toggle' => 'tooltip',
                                                                'original-title' => 'Завершить заказ',
                                                                'url' => Url::to(['order/complete-obsolete', 'id' => $data->id])
                                                            ],
                                                ]);
                                            }
                                            break;
                                    }
                                    return '';
                                },
                                'contentOptions' => ['class' => 'text-center'],
                                'headerOptions' => ['style' => 'width: 20px;']
                            ],
                        ],
                        'rowOptions' => function ($model, $key, $index, $grid) {
                            return ['data-url' => Url::to(['order/view', 'id' => $model->id])];
                        },
                    ]);
                    ?>
                </div></div>
<?php Pjax::end() ?>
            <!-- /.table-responsive -->
        </div>
        <!-- /.box-body -->
    </div>
</section>
