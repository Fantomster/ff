<?php

/**
 * IIKO integration service - order list view [basic]
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-17
 * @author Mixcart
 * @module Frontend
 * @version 1.0
 */

use yii\widgets\Breadcrumbs;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\date\DatePicker;
use common\components\EchoRu;
use kartik\select2\Select2;
use frontend\modules\clientintegr\modules\iiko\controllers\WaybillController;
use yii\grid\CheckboxColumn;
use api\common\models\iiko\iikoWaybill;
use kartik\grid\ExpandRowColumn;

/** @var $affiliated array */
/** @var $searchParams array Search Params */
/** @var $wbStatuses array */
/** @var $way mixed */

$msg = [
    'entries' => EchoRu::echo ('frontend.views.order.waybill.entries', 'Состав Заказа'),
    'push' => EchoRu::echo ('frontend.clientintegr.order.waybill.push', 'Выгрузить выбранные'),
];
$headers = [
    'id' => EchoRu::echo ('frontend.order.id', 'Номер заказа'),
    'invoice_relation' => EchoRu::echo ('frontend.clientintegr.order.waybill.id', '№ Накладной'),
    'vendor' => EchoRu::echo ('frontend.views.order.vendor', 'Поставщик'),
    'updated_at' => EchoRu::echo ('frontend.views.order.updated_at', 'Обновлено'),
    'finished_at' => EchoRu::echo ('frontend.views.order.final_date', 'Дата финальная'),
    'positionCount' => EchoRu::echo ('frontend.views.order.position_сount', 'Кол-во позиций'),
];

#-----------------------------------------------------------------------------------------------------------------------
# 3. ФИЛЬТРЫ (В виде инпутов или селектов)
#-----------------------------------------------------------------------------------------------------------------------
# 3.1. Заголовки фильтров
$filterLabels = [
    'orderId' => EchoRu::echo ('frontend.clientintegr.order.id', 'Номер заказа'),
    'orderAff' => EchoRu::echo ('frontend.clientintegr.vendors', 'Поставщики'),
    'orderLastUpdated' => EchoRu::echo ('frontend.clientintegr.order.last_updated.range',
        'Обновлено: начальная дата / Конечная дата'),
    'wbStatus' => EchoRu::echo ('frontend.clientintegr.order.waybill.status', 'Статус накладной'),
];
#-----------------------------------------------------------------------------------------------------------------------
# 3.2. Виджеты фильтров
$filterWidgetNames = [
    'orderAff' => Select2::class,
    'wbStatus' => Select2::class,
];
#-----------------------------------------------------------------------------------------------------------------------
# 3.3. Опции фильтров
$filterOptions = [
    'orderAff' => $affiliated,
    'wbStatus' => [
        array_search(WaybillController::ORDER_STATUS_ALL_DEFINEDBY_WB_STATUS, $wbStatuses) =>
            EchoRu::echo ('frontend.clientintegr.order.waybill.allstat', 'Все'),
        array_search(WaybillController::ORDER_STATUS_NODOC_DEFINEDBY_WB_STATUS, $wbStatuses) =>
            EchoRu::echo ('frontend.clientintegr.order.waybill.nodoc', 'Не сформирована'),
        array_search(WaybillController::ORDER_STATUS_FILLED_DEFINEDBY_WB_STATUS, $wbStatuses) =>
            EchoRu::echo ('frontend.clientintegr.order.waybill.filled', 'Сформирована'),
        array_search(WaybillController::ORDER_STATUS_READY_DEFINEDBY_WB_STATUS, $wbStatuses) =>
            EchoRu::echo ('frontend.clientintegr.order.waybill.ready', 'Готова к выгрузке'),
        // EchoRu::echo (WaybillController::ORDER_STATUS_OUTGOING_DEFINEDBY_WB_STATUS, 'Отправляется'),
        array_search(WaybillController::ORDER_STATUS_COMPLETED_DEFINEDBY_WB_STATUS, $wbStatuses) =>
            EchoRu::echo ('frontend.clientintegr.order.waybill.completed', 'Выгружена'),
    ],
];
$filterOptions['orderAff'][0] = EchoRu::echo ('frontend.clientintegr.order.select.aff.all', 'Все');
ksort($filterOptions['orderAff']);

#-----------------------------------------------------------------------------------------------------------------------
# 3.4. Плейсхолдеры / значения фильтров (для селектов типа kartik - те же самые предустановленные значения фильтров)
$filterValues = [
    'orderId' => EchoRu::echo ('frontend.clientintegr.order.id', 'Номер заказа'),
    'orderAff' => $filterOptions['orderAff'][0],
    'wbStatus' => $filterOptions['wbStatus'][0],
    'dateFrom' => $searchParams['OrderSearch2']['date_from'] ?? '',
    'dateTo' => $searchParams['OrderSearch2']['date_to'] ?? '',
];
if (isset($searchParams['OrderSearch2']['id']) && (int)$searchParams['OrderSearch2']['id'] > 0) {
    $filterValues['orderId'] = (int)$searchParams['OrderSearch2']['id'];
}
#-----------------------------------------------------------------------------------------------------------------------

$this->title = EchoRu::echo ('frontend.clientintegr.iiko.waybill', 'Интеграция с iiko Office');


$columns = array(
    // 1. ЧЕКБОКС
    [
        'class' => CheckboxColumn::class,
        'checkboxOptions' => function ($data) {
            $res = ['style' => 'width: 10px', 'class' => 'small_cell_id'];
            $nacl = iikoWaybill::findOne(['order_id' => $data->id]);
            if ($nacl['status_id'] !== 4 || $nacl['readytoexport'] === 0) {
                $res = [
                    'disabled' => true,
                    'style' => 'display: none;',
                    'class' => 'small_cell_id'
                ];
            }
            return $res;
        },
        'headerOptions' => ['style' => 'white-space: nowrap'],
        'contentOptions' => ['style' => 'width: 10px', 'class' => 'small_cell_id'],
    ],

    // 2. ID заказа
    [
        'attribute' => 'id',
        'label' => $headers['id'],
        'format' => 'raw',
        'contentOptions' => function ($data) {
            return ["id" => "way" . $data->id, 'style' => 'width: 120px; text-align: center; padding-right: 30px'];
        },
    ],
    // 3. № накладной
    [
        'attribute' => 'invoice_relation',
        'label' => $headers['invoice_relation'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'text-align: center'],
        'contentOptions' => ['style' => 'text-align: center'],
        'value' => function ($data) {
            return ($data->invoice) ? Html::encode($data->invoice->number) : '';
        },
    ],
    // 4. Контрагент по договору поставки
    [
        'attribute' => 'vendor.name',
        'label' => $headers['vendor'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'text-align: center'],
        'contentOptions' => ['style' => 'text-align: center'],
        'value' => function ($data) {
            return ($data->vendor) ? Html::encode($data->vendor->name) : '';
        },
    ],
    // 5. Дата последнего обновления документа
    [
        'attribute' => 'updated_at',
        'label' => $headers['updated_at'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'text-align: center'],
        'contentOptions' => ['style' => 'text-align: right; padding-right: 20px'],
        'value' => function ($data) {
            $title = Yii::$app->formatter->asDatetime($data->updated_at, "php:j M Y");
            return
                '<i class="fa fa-fw fa-calendar"></i> ' . $title;
        },
    ],
    // 6. Финальная дата
    [
        'attribute' => 'finished_at',
        'label' => $headers['finished_at'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'text-align: center'],
        'contentOptions' => ['style' => 'text-align: right; padding-right: 20px'],
        'value' => function ($data) {
            $fdate = ($data->requested_delivery) ? $data->requested_delivery : $data->updated_at;
            $fdate = $data->actual_delivery ? $data->actual_delivery : $fdate;
            $fdate = Yii::$app->formatter->asDatetime($fdate, "php:j M Y");
            return '<i class="fa fa-fw fa-calendar"></i> ' . $fdate;
        },
    ],
    // 7. Количество позиций
    [
        'attribute' => 'positionCount',
        'label' => $headers['positionCount'],
        'format' => 'raw',
        'headerOptions' => ['style' => 'text-align: center; white-space: nowrap'],
        'contentOptions' => ['style' => 'text-align: right; width: 100px'],
        'value' => function ($data) use ($msg) {
            return $data->positionCount .
                ' <a class="ajax-popover" data-container="body" data-content="Loading..."
                    data-html="data-html" data-placement="bottom" data-title="' . $msg['entries'] . '"
                    data-toggle="popover"  data-trigger="focus" data-url="' .
                Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/']) .
                '/getpopover"
                    role="button" tabindex="0" data-original-title="" title="" data-model="' .
                $data->id . '"><i class="fa fa-info-circle"></i></a>';
        },
    ],
    // 8. Сумма заказа
    [
        'attribute' => 'total_price',
        'label' => EchoRu::echo ('frontend.views.order.summ', 'Сумма'),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_sum', 'style' => 'text-align: right'],
        'value' => function ($data) {
            return "<b>$data->total_price</b> " . $data->currency->symbol;
        },
    ],
    // 9. Статус накладной
    [
        'value' => function ($data) {
            $nacl = iikoWaybill::findOne(['order_id' => $data->id]);
            if (isset($nacl->status)) {
                return $nacl->status->denom;
            } else {
                return 'Не сформирована';
            }
        },
        'label' => 'Статус накладной',
        'headerOptions' => ['style' => 'text-align: center'],
        'contentOptions' => ['style' => 'text-align: center'],
    ],

    # 10. Дополнительные действия
    [
        'class' => ExpandRowColumn::class,
        'width' => '50px',
        'value' => function ($model) use ($way) {
            $val = kartik\grid\GridView::ROW_COLLAPSED;
            if (($model->id == $way) or (Yii::$app->session->get('iiko_waybill') == $model->id)) {
                Yii::$app->session->set("iiko_waybill", 0);
                $val = kartik\grid\GridView::ROW_EXPANDED;
            }
            return $val;
        },
        'detail' => function ($model) use ($lic) {
            $wmodel = iikoWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id])->one();
            if ($wmodel) {
                $wmodel = iikoWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id]);
            } else {
                $wmodel = null;
            }
            $order_id = $model->id;
            $query_string = Yii::$app->getRequest()->getQueryString();
            Yii::$app->session->set("query_string", $query_string);
            return Yii::$app->controller->renderPartial('_expand-row-details',
                ['model' => $wmodel, 'order_id' => $order_id, 'lic' => $lic]);
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true,
    ]
);

$this->registerJs('
$("document").ready(function(){
       $(".box-body").on("change", "#orderFilter", function () {
        var target = "http:";
        var w = window.location.protocol;
        if (w === "https:") {
            target = "https:";
        }
        target = target + \'//\' + window.location.hostname + \'/clientintegr/iiko/waybill/index?OrderSearch2[id]=\' + $("#orderFilter").val();
        window.location.href = target;
    });
    
    var justSubmitted = false;
    $(".box-body").on("change", "#dateFrom, #dateTo", function () {
        if (!justSubmitted) {
            $("#search-form").submit();
            justSubmitted = true;
            setTimeout(function () {
                justSubmitted = false;
            }, 500);
        }
    });
    
    $(".box-body").on("change", "#ordersearch2-vendor_id", function () {
        $("#search-form").submit();
    });
    
    $(".box-body").on("change", "#ordersearch2-wb_status", function () {
        $("#search-form").submit();
    });
});
');
#-----------------------------------------------------------------------------------------------------------------------
$css = <<< CSS
tr:hover {
    cursor: pointer;
}

.bg-default {
    background: #555
}

p {
    margin: 0;
}

#map {
    width: 100%;
    height: 200px;
}

#select2-ordersearch2-vendor_id-container,
#select2-ordersearch2-wb_status-container {
    margin-top: 0 !important
}

.select2-selection__clear {
    display: none;
}
CSS;
#-----------------------------------------------------------------------------------------------------------------------
$this->registerCss($css);
#-----------------------------------------------------------------------------------------------------------------------
?>

<section class="content-header">
    <h1>
        <i class="fa fa-history"></i> <?=
        EchoRu::echo ('frontend.clientintegr.iiko.waybill', 'Интеграция с iiko Office') ?>
    </h1>
    <?= Breadcrumbs::widget([
        'options' => ['class' => 'breadcrumb',],
        'links' => [
            [
                'label' => EchoRu::echo ('frontend.clientintegr.index', 'Интеграция', 'app'),
                'url' => '/clientintegr/default'
            ],
            EchoRu::echo ('frontend.clientintegr.iiko.waybill', 'Интеграция с iiko Office'),
        ],
    ]);
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php') ?>
    <?= $this->render('/default/_license_no_active.php', ['lic' => $lic]) ?>
    ЗАВЕРШЁННЫЕ ЗАКАЗЫ
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding orders-table">
                        <?php
                        Pjax::begin(['enablePushState' => false, 'id' => 'order-list']);
                        $form = ActiveForm::begin([
                            'options' => [
                                'data-pjax' => true,
                                'id' => 'search-form',
                                'role' => 'search',
                            ],
                            'enableClientValidation' => false,
                            'method' => 'get',
                        ]);
                        ?>
                        <div class="row">
                            <div class="col-lg-1 col-md-2 col-sm-6" style="width: 150px;">
                                <?php
                                # 1. INPUT ORDER ID Filter field
                                echo $form->field($searchModel, 'id')
                                    ->textInput(['id' => 'orderFilter', 'class' => 'form-control', 'value' => '',
                                        'style' => 'width: 130px; margin-right: 20px', 'placeholder' => $filterValues['orderId']])
                                    ->label($filterLabels['orderId'], ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-6" style="width: 240px;">
                                <?php
                                # 2. SELECT SUPPLIER Filter field
                                echo $form->field($searchModel, 'vendor_id')->widget($filterWidgetNames['orderAff'], [
                                    'data' => $filterOptions['orderAff'], 'options' => ['placeholder' => $filterValues['orderAff']],
                                    'pluginOptions' => ['allowClear' => FALSE],
                                    'id' => 'orgFilter',
                                ])->label($filterLabels['orderAff'], ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-6" style="width: 440px;">
                                <?php
                                # 3. RANGE ORDER LAST_UPDATED Filter field
                                echo Html::label($filterLabels['orderLastUpdated'], null, ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                                <div class="form-group">
                                    <?= DatePicker::widget([
                                        'model' => $searchModel,
                                        'attribute' => 'date_from', 'attribute2' => 'date_to',
                                        'options' => ['placeholder' => $filterValues['dateFrom'], 'id' => 'dateFrom', 'style' => "min-width: 100px"],
                                        'options2' => ['placeholder' => $filterValues['dateTo'], 'id' => 'dateTo', 'style' => "min-width: 100px"],
                                        'separator' => '-', 'type' => DatePicker::TYPE_RANGE,
                                        'pluginOptions' => ['format' => 'dd.mm.yyyy', 'autoclose' => true, 'endDate' => "0d"],
                                    ]);
                                    ?>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6" style=" width: 240px;">
                                <?php
                                # 4. STATUS OF ASSOCIATED DOCUMENTS TYPE WAYBILL Filter field
                                echo $form->field($searchModel, 'wb_status')->widget($filterWidgetNames['wbStatus'], [
                                    'data' => $filterOptions['wbStatus'], 'options' => ['placeholder' => $filterValues['wbStatus']],
                                    'pluginOptions' => ['allowClear' => TRUE],
                                    'id' => 'wbStatus',
                                ])->label($filterLabels['wbStatus'], ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                            </div>
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label class="label" style="color:#555" for="statusFilter">&nbsp;</label><br/>
                                <a class="btn btn-warning" href="<?= Url::to(['/clientintegr/iiko/waybill']) ?>">Сбросить
                                    фильтры</a>
                            </div>
                            <div class="col-lg-5 col-md-6 col-sm-6">
                                <?php $title = EchoRu::echo ($msg['push'], 'Выгрузить выбранные');
                                echo Html::a($title, false, ['class' => 'btn btn-md fk-button', 'id' => 'mk-all-nakl']); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <?=
                                GridView::widget([
                                    'dataProvider' => $dataProvider,
                                    'pjax' => true,
                                    'pjaxSettings' => ['options' => ['id' => 'waybill_grid1'], 'loadingCssClass' => false],
                                    'filterPosition' => false,
                                    'columns' => $columns,
                                    'options' => ['class' => 'table-responsive'],
                                    'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                                    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                                    'bordered' => true,
                                    'striped' => true,
                                    'condensed' => true,
                                    'responsive' => true,
                                    'hover' => true,
                                    'resizableColumns' => true,
                                    'export' => [
                                        'fontAwesome' => true,
                                    ],
                                ]);
                                ?>
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>
                        <?php Pjax::end() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$url = Url::toRoute('waybill/send');
$miltipleUrl = Url::toRoute('waybill/multi-send');
$js = <<< JS
    $(function () {
        $('.orders-table').on('click', '.export-waybill', function () {
            var url = '$url';
            var id = $(this).data('id');
            var oid = $(this).data('oid');
            swal({
                title: 'Выполнить выгрузку накладной?',
                type: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Выгрузить',
                cancelButtonText: 'Отмена',
            }).then((result) => {
                if(result.value)
                {
                    swal({
                        title: 'Идёт отправка',
                        text: 'Подождите, пока закончится выгрузка...',
                        onOpen: () => {
                            swal.showLoading();
                            $.post(url, {id:id}, function (data) {
                                if (data.success === true) {
                                    swal.close();
                                    swal('Готово', '', 'success')
                                } else {
                                    swal(
                                        'Ошибка',
                                        'Обратитесь в службу поддержки.',
                                        'error'
                                    )
                                }
                                $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
                            })
                            .fail(function() { 
                               swal(
                                    'Ошибка',
                                    'Обратитесь в службу поддержки.',
                                    'error'
                                );
                               $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
                            });
                        }
                    })
                }
            })
        });
        
        FF = {};
        FF.sendCheckBoxes = {
        	init: function(){
        		$(document).on('click', '#mk-all-nakl', function () {
		            var keys = $('#w0').yiiGridView('getSelectedRows'),
		                ids = [],
		                url = '$miltipleUrl';
		            
		            keys.map(function(value){
		            	ids.push($('div [data-key='+ value +'] tbody>tr').data('key'));
		            });
		            
		            swal({
		                title: 'Выполнить массовую выгрузку накладной?',
		                type: 'info',
		                showCancelButton: true,
		                confirmButtonColor: '#3085d6',
		                cancelButtonColor: '#d33',
		                confirmButtonText: 'Выгрузить',
		                cancelButtonText: 'Отмена',
		            }).then((result) => {
		                if(result.value)
		                {
		                    swal({
		                        title: 'Идёт отправка',
		                        text: 'Подождите, пока закончится выгрузка...',
		                        onOpen: () => {
		                            swal.showLoading();
		                            $.post(url, {ids:ids}, function (data) {
		                                if (data.success === true) {
		                                    swal.close();
		                                    swal('Готово', 'Выгруженно ' + data.count + ' накладных', 'success')
		                                } else {
		                                    swal(
		                                        'Ошибка',
		                                        data.error,
		                                        'error'
		                                    )
		                                }
		                                // $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
		                            })
		                            .fail(function() {
		                               swal(
		                                    'Ошибка',
		                                    'Обратитесь в службу поддержки.',
		                                    'error'
		                                );
		                               // $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:1500});
		                            });
		                        }
		                    })
		                }
		            })
		        });
        	}
        };
        
        FF.sendCheckBoxes.init();
    });

JS;

$this->registerJs($js);
?>
<?php
$js = <<< 'SCRIPT'
/* To initialize BS3 tooltips set this below */
// $(function () {
// $("[data-toggle='tooltip']").tooltip();
// });;

/* To initialize BS3 popovers set this below */
$(function () {
$("[data-toggle='popover']").popover({
     container: 'body'
});
});

// $('.popover-dismiss').popover({
//  trigger: 'focus'
// });

// $('html').on('mouseup', function(e) {
//     if(!$(e.target).closest('.ajax-popover').length) {
//        $('.ajax-popover').each(function(){
//            $(this.previousSibling).popover('hide');
//        });
//    }
// });
SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js, View::POS_END);
?>

<?php
$js = <<< 'SCRIPT'
$('.ajax-popover').click(function() {
    var e = $(this);
    if (e.data('loaded') !== true) {
        $.ajax({
      url: e.data('url'),
      type: "POST",
      data: {key: e.data('model')}, // данные, которые передаем на сервер
      dataType: 'html',
      // dataType: "json", // тип ожидаемых данных в ответе
      success: function(data) {
            e.data('loaded', true);
            e.attr('data-content', data);
            var popover = e.data('bs.popover');
            popover.setContent();
            popover.$tip.addClass(popover.options.placement);
            var calculated_offset = popover.getCalculatedOffset(popover.options.placement, popover.getPosition(), popover.$tip[0].offsetWidth, popover.$tip[0].offsetHeight);
            popover.applyPlacement(calculated_offset, popover.options.placement);
        },
      error: function(jqXHR, textStatus, errorThrown) {
            return instance.content('Failed to load data');
        }
    });
  }
});
SCRIPT;
$this->registerJs($js, View::POS_END);
?>
<?php
$js = <<< 'SCRIPT'
$(document).on('pjax:complete', function() {

/* To initialize BS3 popovers set this below */
$(function () {
$("[data-toggle='popover']").popover({
     container: 'body'
});
});


$('.ajax-popover').click(function() {
    var e = $(this);
    if (e.data('loaded') !== true) {
        $.ajax({
      url: e.data('url'),
      type: "POST",
      data: {key: e.data('model')}, // данные, которые передаем на сервер
      dataType: 'html',
      // dataType: "json", // тип ожидаемых данных в ответе
      success: function(data) {
            e.data('loaded', true);
            e.attr('data-content', data);
            var popover = e.data('bs.popover');
            popover.setContent();
            popover.$tip.addClass(popover.options.placement);
            var calculated_offset = popover.getCalculatedOffset(popover.options.placement, popover.getPosition(), popover.$tip[0].offsetWidth, popover.$tip[0].offsetHeight);
            popover.applyPlacement(calculated_offset, popover.options.placement);
        },
      error: function(jqXHR, textStatus, errorThrown) {
            return instance.content('Failed to load data');
        }
    });
  }
});


})
SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js, View::POS_END);
?>

<?php
$js = <<< JS
$(document).ready(function () {
    if ($way > 0) {
        $('html, body').animate({
            scrollTop: $("#way$way").offset().top
        }, 1000);
       // jQuery('#w2').dropdown();
    }
    
        $(document).on("change", "#ordersearch-vendor_id", function() {
            $("#search-form").submit();
        });
});    
JS;
// Register tooltip/popover initialization javascript
$this->registerJs($js, View::POS_END);
?>
