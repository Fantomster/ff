<?php

$this->title = Yii::t('app', 'franchise.views.organization.your_vendors', ['ru' => 'Ваши поставщики']);

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use yii\bootstrap\Modal;
use kartik\export\ExportMenu;
use common\assets\CroppieAsset;
use yii\web\View;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);

$this->registerJs('
    $("document").ready(function(){
        $("#vendorInfo").data("bs.modal", null);
        var justSubmitted = false;
        var timer;
//        $("body").on("change", "#dateFrom, #dateTo", function() {
//            if (!justSubmitted) {
//                $("#searchForm").submit();
//                justSubmitted = true;
//                setTimeout(function() {
//                    justSubmitted = false;
//                }, 500);
//            }
//        });
//        $("body").on("change keyup paste cut", "#searchString", function() {
//                if (timer) {
//                    clearTimeout(timer);
//                }
//                timer = setTimeout(function() {
//                    $("#searchForm").submit();
//                }, 700);
//            });
        $("body").on("hidden.bs.modal", "#vendorInfo", function() {
                $(this).data("bs.modal", null);
            });
        $("body").on("click", "td", function (e) {
            if ($(this).find("a").hasClass("stats")) {
                document.location = $(this).find("a").attr("href");
                return false;
            }
            
            var url = $(this).parent("tr").data("url");
            if (url !== undefined && !$(this).find("a").hasClass("f-delete")) {
                $("#vendorInfo").modal({backdrop:"static",toggle:"modal"}).load(url);
            }
        });
        $("body").on("click", ".f-delete", function(e){
            e.preventDefault();
            if(!confirm("' . Yii::t('app', 'franchise.views.organization.sure_two', ['ru' => 'Вы уверены, что хотите удалить поставщика?']) . '")){
                return false;
            }
            var url = $(this).attr("url");
            var obj = $(this);
            $.ajax({
              url: url,
            }).done(function() {
              obj.parent("td").parent("tr").fadeOut("fast", function() {});
            });
        });
    });
        ');
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
?>

    <section class="content-header">
        <h1>
            <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.your_vendors_two', ['ru' => 'Ваши поставщики']) ?>
            <small><?= Yii::t('app', 'franchise.views.organization.plugged_vendors', ['ru' => 'Подключенные Вами поставщики и информация о них']) ?></small>
        </h1>
        <?=
        ''
        //    Breadcrumbs::widget([
        //        'options' => [
        //            'class' => 'breadcrumb',
        //        ],
        //        'links' => [
        //            'Список ваших поставщиков',
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
                    <div class="col-lg-3 col-md-3 col-sm-6">
                        <?= Html::label(Yii::t('app', 'franchise.views.organization.search_five', ['ru' => 'Поиск']), null, ['style' => 'color:#555']) ?>
                        <div class="input-group  pull-left">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                            <?= Html::input('text', 'search', $searchModel['searchString'], ['class' => 'form-control', 'placeholder' => Yii::t('app', 'franchise.views.organization.search_six', ['ru' => 'Поиск']), 'id' => 'search', 'style' => 'width:300px']) ?>
                        </div>
                    </div>

                    <div class="col-lg-5 col-md-6 col-sm-6">
                        <?= Html::label(Yii::t('app', 'franchise.views.organization.date_from_to_two', ['ru' => 'Начальная дата / Конечная дата']), null, ['style' => 'color:#555']) ?>
                        <div class="form-group" style="width: 300px; height: 44px;">
                            <?=
                            DatePicker::widget([
                                'model' => $searchModel,
                                'attribute' => 'date_from',
                                'attribute2' => 'date_to',
                                'options' => ['placeholder' => Yii::t('app', 'franchise.views.organization.date_four', ['ru' => 'Дата']), 'id' => 'dateFrom'],
                                'options2' => ['placeholder' => Yii::t('app', 'franchise.views.organization.date_to_three', ['ru' => 'Конечная дата']), 'id' => 'dateTo'],
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
                    <?php
                    ActiveForm::end();
                    ?>

                    <div class="col-lg-2 col-md-2 col-sm-6" id="alCurrencies">
                        <?php if (count($currencyData['currency_list']) > 0): ?>
                            <?= Html::label(Yii::t('message', 'frontend.views.client.anal.currency', ['ru' => 'Валюта']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
                            <?=
                            Html::dropDownList('filter_currency', null, $currencyData['currency_list'], ['class' => 'form-control', 'id' => 'filter_currency'])
                            ?>
                        <?php endif; ?>
                    </div>

                    <div class="pull-right" style="margin-top: 30px; margin-right: 10px;">
                        <?= ExportMenu::widget([
                            'dataProvider' => $dataProvider,
                            'columns' => $exportColumns,
                            'fontAwesome' => true,
                            'filename' => Yii::t('app', 'franchise.views.organization.vendor_six', ['ru' => 'Поставщики - ']) . date('Y-m-d'),
                            'encoding' => 'UTF-8',
                            'target' => ExportMenu::TARGET_SELF,
                            'showConfirmAlert' => false,
                            'showColumnSelector' => false,
                            'batchSize' => 200,
                            'timeout' => 0,
                            'dropdownOptions' => [
                                'label' => '<span class="text-label">' . Yii::t('app', 'franchise.views.organization.download_list_two', ['ru' => 'Скачать список']) . ' </span>',
                                'class' => ['btn btn-outline-default btn-sm'],
                                'style' => 'margin-right:10px;',
                            ],
                            'exportConfig' => [
                                ExportMenu::FORMAT_HTML => false,
                                ExportMenu::FORMAT_TEXT => false,
                                ExportMenu::FORMAT_EXCEL => false,
                                ExportMenu::FORMAT_PDF => false,
                                ExportMenu::FORMAT_CSV => false,
                                ExportMenu::FORMAT_EXCEL_X => [
                                    'label' => Yii::t('kvexport', 'Excel'),
                                    'icon' => 'file-excel-o',
                                    'iconOptions' => ['class' => 'text-success'],
                                    'linkOptions' => [],
                                    'options' => ['title' => Yii::t('kvexport', 'Microsoft Excel 2007+ (xlsx)')],
                                    'alertMsg' => Yii::t('kvexport', 'Файл EXCEL( XLSX ) будет генерироваться для загрузки'),
                                    'mime' => 'application/application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    'extension' => 'xlsx',
                                    //'writer' => 'Excel2007',
                                    'styleOptions' => [
                                        'font' => [
                                            'bold' => true,
                                            'color' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                        ],
                                        'fill' => [
                                            'type' => PHPExcel_Style_Fill::FILL_NONE,
                                            'startcolor' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                            'endcolor' => [
                                                'argb' => 'FFFFFFFF',
                                            ],
                                        ],
                                    ]
                                ],
                            ],
                            'onRenderSheet' => function ($sheet, $grid) {
                                $i = 2;
                                while ($sheet->cellExists("B" . $i)) {
                                    $sheet->setCellValue("B" . $i, html_entity_decode($sheet->getCell("B" . $i)));
                                    $i++;
                                }
                            }
                        ]);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?=
                        GridView::widget([
                            'id' => 'vendorsList',
                            'dataProvider' => $dataProvider,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            'filterModel' => $searchModel,
                            'filterPosition' => false,
                            'pjax' => true,
                            'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                            'summary' => '',
                            'options' => ['class' => 'table-responsive'],
                            'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable', 'role' => 'grid'],
                            'pager' => [
                                'maxButtonCount' => 5, // Set maximum number of page buttons that can be displayed
                            ],
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
                                        if ($data['self_registered'] == \common\models\FranchiseeAssociate::SELF_REGISTERED) {
                                            return $data['name'] . " &nbsp; <i title='" . Yii::t('app', 'franchise.views.organization.self_registered_six', ['ru' => 'Клиент самостоятельно зарегистрировался']) . "' class='text-success' aria-hidden='true'><img src='/images/new.png' alt='" . Yii::t('app', 'franchise.views.organization.self_registered_seven', ['ru' => 'Клиент самостоятельно зарегистрировался']) . "'></i>";
                                        }
                                        return $data['name'];
                                    },
                                    'label' => Yii::t('app', 'franchise.views.organization.vendors_name_three', ['ru' => 'Имя поставщика']),
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'client_count',
                                    'value' => function ($data) {
                                        $progress = $data["client_count"] > 0 ? round($data["client_count_prev30"] * 100 / $data["client_count"], 2) : 0;
//                                            if ($progress > 0) {
                                        $divider = '<i class="fa fa-caret-up"></i>';
                                        //                                          }
                                        $class = "text-red";
                                        if ($progress > 20) {
                                            $class = "text-green";
                                        } elseif ($progress > 0) {
                                            $class = " text-orange";
                                        }
                                        return $data["client_count"] . " <span class='description-percentage $class'>$divider $progress%";
                                    },
                                    'label' => Yii::t('app', 'franchise.views.organization.rest_quantity_two', ['ru' => 'Кол-во ресторанов']),
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'order_count',
                                    'value' => function ($data) {
                                        $progress = $data["order_count"] > 0 ? round($data["order_count_prev30"] * 100 / $data["order_count"], 2) : 0;
//                                            if ($progress > 0) {
                                        $divider = '<i class="fa fa-caret-up"></i>';
                                        //                                          }
                                        $class = "text-red";
                                        if ($progress > 20) {
                                            $class = "text-green";
                                        } elseif ($progress > 0) {
                                            $class = " text-orange";
                                        }
                                        return $data["order_count"] . " <span class='description-percentage $class'>$divider $progress%";
                                    },
                                    'label' => Yii::t('app', 'franchise.views.organization.orders_quan_five', ['ru' => 'Кол-во заказов']),
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'order_sum',
                                    'value' => function ($data) use ($currencyData) {
                                        $progress = $data["order_sum"] ? round($data["order_sum_prev30"] * 100 / $data["order_sum"], 2) : 0;
//                                            if ($progress > 0) {
                                        $divider = '<i class="fa fa-caret-up"></i>';
                                        //                                          }
                                        $class = "text-red";
                                        if ($progress > 20) {
                                            $class = "text-green";
                                        } elseif ($progress > 0) {
                                            $class = " text-orange";
                                        }
                                        return ($data["order_sum"] ? $data["order_sum"] : 0) . " " . $currencyData['iso_code'] . "  <span class='description-percentage $class'>$divider $progress%";
                                    },
                                    'label' => Yii::t('app', 'franchise.views.organization.orders_sum_three', ['ru' => 'Сумма заказов']),
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'created_at',
                                    'value' => function ($data) {
                                        $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                        return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                    },
                                    'label' => Yii::t('app', 'franchise.views.organization.reg_date_three', ['ru' => 'Дата регистрации']),
                                ],
                                [
                                    'attribute' => 'contact_name',
                                    'value' => 'contact_name',
                                    'label' => Yii::t('app', 'franchise.views.organization.contact_five', ['ru' => 'Контакт']),
                                ],
                                [
                                    'attribute' => 'phone',
                                    'value' => 'phone',
                                    'label' => Yii::t('app', 'franchise.views.organization.phone_eight', ['ru' => 'Телефон']),
                                ],
                                [
                                    'format' => 'raw',
                                    'value' => function ($data) {
                                        return Html::a('<i class="fa fa-signal"></i>', ['analytics/vendor-stats', 'id' => $data["id"]], ['class' => 'stats']);
                                    },
                                ],
                                [
                                    'class' => 'yii\grid\ActionColumn',
                                    'template' => '{delete}',
                                    'buttons' => [
                                        'delete' => function ($url, $data) {
                                            return Html::a(
                                                '<span class="glyphicon glyphicon-trash text-red" title="' . Yii::t('app', 'franchise.views.organization.del', ['ru' => 'Удалить']) . ' "></span>',
                                                null, ['data-pjax' => '0', 'class' => 'f-delete', 'url' => Url::to(['organization/delete', 'id' => $data["franchisee_associate_id"]])]);
                                        },
                                    ],
                                ],
                            ],
                            'rowOptions' => function ($model, $key, $index, $grid) {
                                return ['data-url' => Url::to(['organization/ajax-show-vendor', 'id' => $model["id"]])];
                            },
                        ]);
                        ?>
                    </div>
                </div>
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
<?php
$url = Url::to(['organization/vendors']);
$analyticsCurrencyUrl = Url::to(['organization/ajax-update-currency']);
$customJs = <<< JS

$(document).on("change", "#dateFrom,#dateTo", function () {   
var filter_from_date =  $("#dateFrom").val();
var filter_to_date =  $("#dateTo").val();        
    $.pjax({
     type: 'GET',
     push: true,
     timeout: 10000,
     url: "$analyticsCurrencyUrl",
     container: "#alCurrencies",
     data: {
         filter_from_date: filter_from_date,
         filter_to_date: filter_to_date
           }
   }).done(function() {});
});

$(document).on("change", "#filter_currency", function () {
$("#filter_currency").attr('disabled','disabled')      
       
var filter_currency =  $("#filter_currency").val();

    $.pjax({
     type: 'GET',
     push: false,
     timeout: 10000,
     url: "$url",
     container: "#kv-unique-id-1",
     data: {
         filter_currency: filter_currency
           }
   }).done(function() { $("#filter_currency").removeAttr('disabled') });
});

var timer;
$('#search').on("keyup", function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

$(document).on("change", '#dateFrom', function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

$(document).on("change", '#dateTo', function () {
window.clearTimeout(timer);
timer = setTimeout(function () {
$.pjax({
type: 'GET',
push: true,
timeout: 10000,
url: '$url',
container: '#kv-unique-id-1',
data: {searchString: $('#search').val(), date_from: $('#dateFrom').val(), date_to: $('#dateTo').val()}
})
}, 700);
});

JS;
$this->registerJs($customJs, View::POS_READY);
