<?php

/**
 * @editedBy Basil A Konakov
 * @editedByKonakovAt 2018-08-10(*)
 */

use yii\widgets\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\web\View;
use kartik\grid\GridView;
use api\common\models\RkWaybill;
use common\models\User;
use common\models\Organization;
use kartik\date\DatePicker;

/* @var $searchModel common\models\search\OrderSearch **/
/* @var $dataProvider yii\data\ActiveDataProvider **/
/* @var $visible bool ??? **/
/* @var $way int ??? **/
/* @var $licucs \api\common\models\RkService ??? **/

$organization = Organization::findOne(User::findOne(Yii::$app->user->id)->organization_id);

$widgetMasterDataColumns = array (
    [
        'class' => 'kartik\grid\CheckboxColumn',
        'checkboxOptions' => function ($model) {
            $nacl = RkWaybill::findOne(['order_id' => $model->id]);
            if ($nacl['status_id'] !== 1 || $nacl['readytoexport'] === 0) {
                return [
                    'disabled' => true,
                    'style' => 'display: none;'
                ];
            }
        }
    ],
    [
        'attribute' => 'id',
        'label' => 'Номер заказа',
        'headerOptions' => ['style' => 'white-space: nowrap;'],
        'contentOptions' => function ($data) {
            return ["id" => "way" . $data->id, 'style' => 'width2: 10px'];
        },
    ],
    [
        'attribute' => 'invoice_relation',
        'label' => '№ Накладной',
        'format' => 'raw',
        'visible' => $visible,
        'value' => function ($data) {
            return ($data->invoice) ? \yii\helpers\Html::encode($data->invoice->number) : '';
        },
    ],
    [
        'attribute' => 'vendor.name',
        'label' => 'Поставщик',
//        'headerOptions' => ['class'=>'sorting',],
        'value' => 'vendor.name',
    ],
//     [
//         'format' => 'raw',
//         'attribute' => 'status',
//         'value' => function ($data) {
//             $statusClass = 'done';
//             return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>';
//         },
//         'label' => 'Статус Заказа',
//     ],
    [
        'attribute' => 'updated_at',
        'label' => 'Обновлено',
        'format' => 'date',
        'contentOptions' => ['style' => 'text-align: right']
    ],
    [
        'attribute' => 'final_date',
        'label' => 'Финальная дата',
        'format' => 'date',
        'value' => function ($data) {
            return $data->actual_delivery ? $data->actual_delivery :
                ($data->requested_delivery ? $data->requested_delivery : $data->updated_at);
        },
        'contentOptions' => ['style' => 'text-align: right']
    ],
    [
        'attribute' => 'positionCount',
        'label' => 'Кол-во позиций',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->positionCount .
                ' <a class="ajax-popover" data-container="body" data-content="Loading..."
                    data-html="data-html" data-placement="bottom" data-title="Состав Заказа"
                    data-toggle="popover"  data-trigger="focus" data-url="' .
                        Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/']) .
                        '/getpopover"
                    role="button" tabindex="0" data-original-title="" title="" data-model="' .
                        $data->id . '"><i class="fa fa-info-circle"></i></a>';
        },
        'contentOptions' => ['style' => 'text-align: right; width2: 100px'],
    ],
    [
        'attribute' => 'total_price',
        'label' => 'Итоговая сумма',
        'format' => ['decimal', 2],
        'contentOptions' => ['style' => 'text-align: right'],
    ],
    [
        'value' => function ($data) {
            $nacl = RkWaybill::findOne(['order_id' => $data->id]);
            if (isset($nacl->status)) {
                return $nacl->status->denom;
            } else {
                return 'Не сформирована';
            }
        },
        'label' => 'Статус накладной',
    ],
    [
        'class' => 'kartik\grid\ExpandRowColumn',
        'width' => '50px',
        'value' => function ($model) use ($way) {
            if (($model->id == $way) or (Yii::$app->session->get('rkws_waybill') == $model->id)) {
                Yii::$app->session->set("rkws_waybill", 0);
                return GridView::ROW_EXPANDED;
            }
            return GridView::ROW_COLLAPSED;
        },
        'detail' => function ($model) use ($lic, $licucs) {
            $wmodel = RkWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id])->one();

            if ($wmodel) {
                $wmodel = RkWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id]);
            } else {
                $wmodel = null;
            }
            $order_id = $model->id;
            $query_string = Yii::$app->getRequest()->getQueryString();
            Yii::$app->session->set("query_string", $query_string);
            return Yii::$app->controller->renderPartial('_expand-row-details', ['model' => $wmodel, 'order_id' => $order_id, 'lic' => $lic, 'licucs' => $licucs]);
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true,
    ]
);

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
         $(".box-body").on("change", "#orderFilter", function() {
            $("#search-form").submit();
        });
    });
');
$this->registerCss("
    tr:hover{cursor: pointer;}
");

?>

<style>
    .bg-default{background:#555} p{margin: 0;} #map{width:100%;height:200px;}
</style>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> Интеграция с R-keeper SH (White Server) 
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/vendorintegr'],
            ],
            'Интеграция с R-keeper WS',
        ],
    ])
    ?>
</section>

<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    <?= $this->render('/default/_license_no_active.php', ['lic' => $lic, 'licucs' => $licucs]); ?>
    ЗАВЕРШЁННЫЕ ЗАКАЗЫ
</section>

<section class="content">
    <div class="catalog-index">
    	<div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class ="box-body table-responsive no-padding orders-table">
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

                            <div class="col-lg-1 col-md-2 col-sm-6">
                                <?php
                                # 1. INPUT ORDER ID Filter field
                                $label = Yii::t('message', 'frontend.views.order.id',
                                    ['ru' => 'Номер заказа']);
                                echo
                                $form->field($searchModel, 'id')
                                    ->textInput(['id' => 'orderFilter', 'class' => 'form-control',
                                        'style' => 'width: 130px', 'placeholder' => $label])
                                    ->label($label, ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                            </div>

                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <?php
                                # 2. SELECT SUPPLIER Filter field
                                /** earlier - $organization->getSuppliers() */
                                if ($organization->type_id == Organization::TYPE_RESTAURANT) {
                                    $label = Yii::t('message', 'frontend.views.order.vendors',
                                        ['ru' => 'Поставщики']);
                                    $associates = $organization->getRestaurantSupplierAll();
                                } else {
                                    $label = Yii::t('message', 'frontend.views.order.rest',
                                        ['ru' => 'Рестораны']);
                                    $associates = $organization->getClients();
                                }
                                echo $form->field($searchModel, 'vendor_id')
                                    ->dropDownList($associates, ['id' => 'orgFilter'])
                                    ->label($label, ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                            </div>

                            <div class="col-lg-3 col-md-3 col-sm-6">
                                <?php
                                # 3. RANGE ORDER LAST_UPDATED Filter field
                                $label = Yii::t('message', 'frontend.views.order.last_updated.range',
                                    ['ru' => 'Обновлено: Начальная дата / Конечная дата ']);
                                echo Html::label($label, null, ['class' => 'label', 'style' => 'color:#555']);
                                ?>
                                <div class="form-group" style="width: 300px; height: 44px;">
                                    <?php
                                    $label_from = Yii::t('message', 'frontend.views.order.date',
                                        ['ru' => 'Дата']);
                                    $label_to = Yii::t('message', 'frontend.views.order.date_to',
                                        ['ru' => 'Конечная дата']);
                                    echo DatePicker::widget([
                                        'model' => $searchModel,
                                        'attribute' => 'date_from',
                                        'attribute2' => 'date_to',
                                        'options' => ['placeholder' => $label_from, 'id' => 'dateFrom'],
                                        'options2' => ['placeholder' => $label_to, 'id' => 'dateTo'],
                                        'separator' => '-',
                                        'type' => DatePicker::TYPE_RANGE,
                                        'pluginOptions' => [
                                            'format' => 'dd.mm.yyyy',
                                            'autoclose' => true,
                                            'endDate' => "0d",
                                        ]
                                    ]);
                                    ?>
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <?php
                                # 4. STATUS OF ASSOCIATED DOCUMENTS TYPE WAYBILL Filter field
                                $waybillStatusesLabel = Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.status');
                                $waybillStatusesValues = [
                                    '0' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.allstat'),
                                    '1' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.nodoc'),
                                    '2' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.ready'),
                                    '3' => Yii::t('message', 'frontend.clientintegr.rkws.views.waybill.completed'),
                                    // '4' => Yii::t('message', $pref.'cancelled', ['ru'=>'Отменено'])
                                ];
                                echo $form->field($searchModel, 'docStatus')
                                    ->dropDownList($waybillStatusesValues, ['id' => 'statusFilter'])
                                    ->label($waybillStatusesLabel, ['class' => 'label', 'style' => 'color:#555'])
                                ?>
                            </div>

                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label class="label" style="color:#555" for="statusFilter">&nbsp;</label><br />
                                <a class="btn btn-warning" href="<?= Url::to(['/clientintegr/rkws/waybill/index']) ?>">Сбросить фильтры</a>
                            </div>

                            <div class="col-lg-5 col-md-6 col-sm-6">
                                <?= \yii\helpers\Html::a('Выгрузить выбранные', false, ['class' => 'btn btn-md fk-button', 'id' => 'mk-all-nakl']); ?>
                            </div>

                        </div>
                        <?php

                        ActiveForm::end();

                        if ($organization->type_id == Organization::TYPE_SUPPLIER) {
                            Html::submitButton('<i class="fa fa-file-excel-o"></i> ' .
                                Yii::t('app', 'frontend.views.order.index.report', ['ru' => 'отчет xls']),
                                ['class' => 'btn btn-success export-to-xls']);
                        }

                        echo GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => true, // pjax is set to always true for this demo
//                            'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                            'pjaxSettings' => ['options' => ['id' => 'waybill_grid1'], 'loadingCssClass' => false],
                            'filterPosition' => false,
//                            'filterModel' => $searchModel,
                            'columns' => $widgetMasterDataColumns,
//                            'rowOptions' => function ($data, $key, $index, $grid) {
//                                return ['id' => $data['id'], 'onclick' => "console.log($(this).find(a).first())"];
//                            },
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped dataTable', 'role' => 'grid'],
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                            'bordered' => false,
                            'striped' => true,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => true,
                            'resizableColumns' => false,
                            'export' => [
                                'fontAwesome' => true,
                            ],
                        ]);

                        Pjax::end();

                     ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$url = Url::toRoute('waybill/sendws');
$miltipleUrl = Url::toRoute('waybill/multi-send');
$js = <<< JS
    $(function () {
        $('.orders-table').on('click', '.export-waybill-btn', function () {
            console.log('Colonel');
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
                                console.log(data);
                                if (data === 'true') {
                                    swal.close();
                                    swal('Готово', '', 'success')
                                } else {
                                    console.log(data.error);
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
                                        console.log(data);
                                        if (data.success === true) {
                                            swal.close();
                                            swal('Готово', 'Выгруженно ' + data.count + ' накладных', 'success')
                                        } else {
                                            console.log(data.error);
                                            swal(
                                                'Ошибка',
                                                'Обратитесь в службу поддержки.',
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
   /*  echo 'Testing for ' . Html::tag('span', 'popover', [
    'title'=>'This is a test tooltip',
    'data-toggle'=>'popover',
    'data-trigger' => 'focus',
    'style'=>'text-decoration: underline; cursor:pointer;'
    ]);
*/
   // echo \yii\helpers\Html::a( '<i class="fa fa-sign-in" aria-hidden="true"></i>', '#',
   //     ['title' => 'Состав заказа', 'data-pjax'=>"0", 'data-toggle' => 'popover', 'data-trigger' => 'focus',
   //      'style' => 'text-decoration: underline; cursor:pointer;']);
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
$this->registerJs($js,View::POS_END);
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
$this->registerJs($js,View::POS_END);
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
$this->registerJs($js,View::POS_END);
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
});    
JS;
// Register tooltip/popover initialization javascript
$this->registerJs($js,View::POS_END);
?>