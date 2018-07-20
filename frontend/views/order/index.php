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
use common\models\Role;

$this->title = Yii::t('message', 'frontend.views.order.order_four', ['ru' => 'Заказы']);
$urlExport = Url::to(['/order/export-to-xls']);
$urlReport = Url::to(['/order/grid-report']);
$urlSaveSelected = Url::to(['/order/save-selected-orders']);
$urlGetVSDList = Url::to(['/order/ajax-get-vsd-list']);
$js = <<<JS

    $("document").ready(function(){
        var justSubmitted = false;
        $(".box-body").on("change", "#statusFilter", function() {
            $("#search-form").submit();
        });
        $(".box-body").on("change", "#ordersearch-vendor_id", function() {
            $("#search-form").submit();
        });
        $(".box-body").on("change", "#ordersearch-client_id", function() {
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
            if($(this).hasClass("completeEdi")){
                var title = "' . Yii::t('app', 'frontend.views.order.complete_edi', ['ru' => 'Внимание, данные о фактическом приеме товара будут направлены ПОСТАВЩИКУ! Вы подтверждаете, корректность данных?']) . ' ";
            }else{
                var title = clicked.data("original-title") + "?";
            }
            swal({
                title: title,
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "' . Yii::t('message', 'frontend.views.order.yep', ['ru' => 'Да']) . ' ",
                cancelButtonText: "' . Yii::t('message', 'frontend.views.order.cancel', ['ru' => 'Отмена']) . ' ",
                showLoaderOnConfirm: true,
            }).then(function(result) {
                if (result.dismiss === "cancel") {
                    swal.close();
                } else {
                    document.location = clicked.data("url")
                }
            });
        });

    });
    $(document).on("click", ".export-to-xls", function(e) {
        if($("#orderHistory").yiiGridView("getSelectedRows").length > 0){
            window.location.href = "' . $urlExport . '?selected=" +  $("#orderHistory").yiiGridView("getSelectedRows")+"&page="+current_page;  
        }
    });
    
    $(document).on("click", ".grid-report", function(e) {
        if($("#orderHistory").yiiGridView("getSelectedRows").length > 0){
            window.location.href = "' . $urlReport . '?selected=" +  $("#orderHistory").yiiGridView("getSelectedRows")+"&page="+current_page;  
        }
    });
    
    var current_page = 0;
     $(document).on("click", ".pagination a", function(e) {
          e.preventDefault();
          url = $(this).attr("href");

           $.ajax({
             url: "'.$urlSaveSelected.'?selected=" +  $("#orderHistory").yiiGridView("getSelectedRows")+"&page="+current_page,
             type: "GET",
             success: function(){
                 $.pjax.reload({container: "#order-list", url: url, timeout:30000});
             }
           });
           
           current_page = $(this).attr("data-page")
    });
JS;
$this->registerJs($js);


$this->registerCss("
    tr:hover{cursor: pointer;}
    #orderHistory a:not(.btn){color: #333;}
    .dataTable a{width: 100%; min-height: 17px; display: inline-block;}
    #select2-ordersearch-vendor_id-container, #select2-ordersearch-client_id-container{margin-top:0;}
    .select2-selection__clear{display: none;}
        ");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-history"></i> <?= Yii::t('message', 'frontend.views.order.orders', ['ru' => 'Заказы']) ?>
        <small><?= Yii::t('message', 'frontend.views.order.orders_list', ['ru' => 'Список всех созданных заказов']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']), 'url' => '/'],
        'links' => [
            Yii::t('message', 'frontend.views.order.orders_history', ['ru' => 'История заказов']),
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
                        <span class="info-box-text"><?= Yii::t('message', 'frontend.views.order.new', ['ru' => 'Новые']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status processing"><?= $processingCount ?></span>
                        <span class="info-box-text"><?= Yii::t('message', 'frontend.views.order.in_process', ['ru' => 'Выполняются']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status done"><?= $fulfilledCount ?></span>
                        <span class="info-box-text"><?= Yii::t('message', 'frontend.views.order.ended', ['ru' => 'Завершено']) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box bg-total-price">
                    <div class="info-box-content">
                        <span class="info-box-number"><?= isset($totalPrice) ? $totalPrice : '0' ?> <i
                                    class="fa fa-fw fa-rub"></i></span>
                        <span class="info-box-text"><?= Yii::t('message', 'frontend.views.order.summ_completed', ['ru' => 'Всего выполнено на сумму']) ?></span>
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
                        ->dropDownList(['0' => Yii::t('message', 'frontend.views.order.all', ['ru' => 'Все']), '1' => Yii::t('message', 'frontend.views.order.new', ['ru' => 'Новый']), '2' => Yii::t('message', 'frontend.views.order.canceled', ['ru' => 'Отменен']), '3' => Yii::t('message', 'frontend.views.order.in_process_two', ['ru' => 'Выполняется']), '4' => Yii::t('message', 'frontend.views.order.completed', ['ru' => 'Завершен'])], ['id' => 'statusFilter'])
                        ->label(Yii::t('message', 'frontend.views.order.status', ['ru' => 'Статус']), ['class' => 'label', 'style' => 'color:#555'])
                    ?>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <?php
                    if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                        echo $form->field($searchModel, 'vendor_id')->widget(\kartik\select2\Select2::classname(), [
                            'data' => $organization->getSuppliers(),
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                            'id' => 'orgFilter'
                        ])->label(Yii::t('message', 'frontend.views.order.vendors', ['ru' => 'Поставщики']), ['class' => 'label', 'style' => 'color:#555']);
                    } else {
                        echo $form->field($searchModel, 'client_id')->widget(\kartik\select2\Select2::classname(), [
                            'data' => $organization->getClients(),
                            'pluginOptions' => [
                                'allowClear' => true
                            ],
                        ])->label(Yii::t('message', 'frontend.views.order.rest', ['ru' => 'Рестораны']), ['class' => 'label', 'style' => 'color:#555']);
                    }
                    ?>
                </div>
                <div class="col-lg-5 col-md-6 col-sm-6">
                    <?= Html::label(Yii::t('message', 'frontend.views.order.begin_end', ['ru' => 'Начальная дата / Конечная дата']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                    <div class="form-group" style="width: 300px; height: 44px;">
                        <?=
                        DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'date_from',
                            'attribute2' => 'date_to',
                            'options' => ['placeholder' => Yii::t('message', 'frontend.views.order.date', ['ru' => 'Дата']), 'id' => 'dateFrom'],
                            'options2' => ['placeholder' => Yii::t('message', 'frontend.views.order.date_to', ['ru' => 'Конечная дата']), 'id' => 'dateTo'],
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
            <?php if ($organization->type_id == Organization::TYPE_SUPPLIER) { ?>
                <?= Html::submitButton('<i class="fa fa-file-excel-o"></i> ' . Yii::t('app', 'frontend.views.order.index.report', ['ru' => 'отчет xls']), ['class' => 'btn btn-success export-to-xls']) ?>
                <?= Html::submitButton('<i class="fa fa-th"></i> ' . Yii::t('app', 'frontend.views.order.index.grid-report', ['ru' => 'Сеточный отчет']), ['class' => 'btn btn-success grid-report']) ?>
            <?php } ?>
            <div class="row">
                <div class="col-md-12">
                <?= GridView::widget([
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
                                'visible' => ($organization->type_id == Organization::TYPE_SUPPLIER) ? true : false,
                                'class' => 'yii\grid\CheckboxColumn',
                                'contentOptions' => ['class' => 'small_cell_checkbox'],
                                'headerOptions' => ['style' => 'text-align:center;'],
                                'checkboxOptions' => function ($model, $key, $index, $widget) use ($selected) {
                                    return ['value' => $model['id'], 'class' => 'checkbox-export', 'checked' => in_array($model['id'], $selected)];
                                }
                            ],
                            [
                                'attribute' => 'id',
                                'label' => '№',
                                'contentOptions' => ['class' => 'small_cell_id'],
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a($data->id, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                            ],
                            $organization->type_id == Organization::TYPE_RESTAURANT ? [
                                'attribute' => 'vendor.name',
                                'value' => 'vendor.name',
                                'contentOptions' => ['class' => 'small_cell_supp'],
                                'label' => Yii::t('message', 'frontend.views.order.vendor', ['ru' => 'Поставщик']),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    $text = "<div class='col-md-10'>";
                                    $text .= Html::a($data->vendor->name, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                    $text .= "</div><div class='col-md-2'>";
                                    if (isset($data->vendor->ediOrganization->gln_code) && $data->vendor->ediOrganization->gln_code>0) {
                                        $alt = Yii::t('app', 'frontend.views.client.suppliers.edi_alt_text', ['ru' => 'Поставщик работает через систему электронного документооборота']);
                                        $text .= Html::img(Url::to('/img/edi-logo.png'), ['alt' => $alt, 'title' => $alt, 'width' => 35]);
                                    }
                                    $text .= "</div>";
                                    return $text;
                                },
                            ] : [
                                'attribute' => 'client.name',
                                'value' => 'client.name',
                                'label' => Yii::t('message', 'frontend.views.order.rest_two', ['ru' => 'Ресторан']),
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a($data->client->name, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                            ],
                            [
                                'attribute' => 'createdByProfile.full_name',
                                'value' => 'createdByProfile.full_name',
                                'label' => Yii::t('message', 'frontend.views.order.order_created_by', ['ru' => 'Заказ создал']),
                                'contentOptions' => ['class' => 'small_cell_sozdal'],
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a($data->createdByProfile->full_name ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                            ],
                            [
                                'attribute' => 'acceptedByProfile.full_name',
                                'value' => 'acceptedByProfile.full_name',
                                'format' => 'raw',
                                'value' => function ($data) {
                                    return Html::a($data->acceptedByProfile->full_name ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                                'label' => Yii::t('message', 'frontend.views.order.accepted_by', ['ru' => 'Заказ принял']),
                                'contentOptions' => ['class' => 'small_cell_prinyal'],
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'total_price',
                                'value' => function ($data) {
                                    return Html::a("<b>$data->total_price</b> " . $data->currency->symbol ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                                'label' => Yii::t('message', 'frontend.views.order.summ', ['ru' => 'Сумма']),
                                'contentOptions' => ['class' => 'small_cell_sum'],
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function ($data) {
                                    $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
                                    return Html::a('<i class="fa fa-fw fa-calendar""></i> ' . $date ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                                'label' => Yii::t('message', 'frontend.views.order.creating_date', ['ru' => 'Дата создания']),
                                'contentOptions' => ['style' => 'min-width:120px;'],

                            ],
                            [
                                'format' => 'raw',
                                'value' => function ($data) {

                                    $fdate = $data->actual_delivery ? $data->actual_delivery :
                                        ($data->requested_delivery ? $data->requested_delivery :
                                            $data->updated_at);

                                    $fdate = Yii::$app->formatter->asDatetime($fdate, "php:j M Y");
                                    return Html::a('<i class="fa fa-fw fa-calendar""></i> ' . $fdate ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                                'label' => Yii::t('message', 'frontend.views.order.final_date', ['ru' => 'Дата финальная']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'status',
                                'value' => function ($data) {
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
                                    return Html::a('<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>' ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
                                },
                                'label' => Yii::t('message', 'frontend.views.order.status_two', ['ru' => 'Статус']),
                                'contentOptions' => ['class' => 'small_cell_status'],
                            ],
                            [
                                'format' => 'raw',
                                'visible' => ($organization->type_id == Organization::TYPE_RESTAURANT),
                                'value' => function ($data) {
                                    switch ($data->status) {
                                        case Order::STATUS_DONE:
                                        case Order::STATUS_REJECTED:
                                        case Order::STATUS_CANCELLED:
                                            return Html::a(Yii::t('message', 'frontend.views.order.repeat', ['ru' => 'Повторить']), '#', [
                                                'class' => 'reorder btn btn-outline-processing',
                                                'data' => [
                                                    'toggle' => 'tooltip',
                                                    'original-title' => Yii::t('message', 'frontend.views.order.repeat_order', ['ru' => 'Повторить заказ']),
                                                    'url' => Url::to(['order/repeat', 'id' => $data->id])
                                                ],
                                            ]);
                                            break;
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                                        case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                        case Order::STATUS_PROCESSING:
                                            if ($data->isObsolete) {
                                                return Html::a(Yii::t('message', 'frontend.views.order.complete', ['ru' => 'Завершить']), '#', [
                                                    'class' => (isset($data->vendor->ediOrganization->gln_code) && $data->vendor->ediOrganization->gln_code>0) ? 'complete btn btn-outline-success completeEdi' : 'complete btn btn-outline-success',
                                                    'data' => [
                                                        'toggle' => 'tooltip',
                                                        'original-title' => Yii::t('message', 'frontend.views.order.complete_order', ['ru' => 'Завершить заказ']),
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
                    ]);
                    ?>
                </div>
            </div>
            <?php Pjax::end() ?>
            <!-- /.table-responsive -->
        </div>
        <!-- /.box-body -->
    </div>
</section>
