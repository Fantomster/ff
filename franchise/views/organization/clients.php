<?php

$this->title = Yii::t('app', 'franchise.views.organization.your_rest', ['ru'=>'Ваши рестораны']);

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\grid\GridView;
use kartik\form\ActiveForm;
use kartik\date\DatePicker;
use kartik\export\ExportMenu;
use yii\bootstrap\Modal;
use yii\web\View;
use common\assets\CroppieAsset;

CroppieAsset::register($this);
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
kartik\select2\Select2Asset::register($this);
if(preg_match("~/organization/clients~",$_SERVER['REQUEST_URI'])) {
    $this->registerJs('
    $("document").ready(function(){
        $("#clientInfo").data("bs.modal", null);
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
        $("body").on("hidden.bs.modal", "#clientInfo", function() {
                $(this).data("bs.modal", null);
            });
        $("body").on("click", "td", function (e) {
            if ($(this).find("a").hasClass("stats")) {
                document.location = $(this).find("a").attr("href");
                return false;
            }
            
            var url = $(this).parent("tr").data("url");
            if (url !== undefined && !$(this).find("a").hasClass("f-delete")) {
                $("#clientInfo").modal({backdrop:"static",toggle:"modal"}).load(url);
            }
        });
        $("body").on("click", ".f-delete", function(e){
            e.preventDefault();
            if(!confirm("' . Yii::t('app', 'franchise.views.organization.shure', ['ru' => 'Вы уверены, что хотите удалить ресторан?']) . '")){
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
    });');
    $this->registerCss("
    tr:hover{cursor: pointer;}
        ");
}else{
    $this->registerJs('
        $(".allows").click(function(){
           $.ajax({
                url:location.href,
                data:{id_org:$(this).val()},
                type:"POST",
                success:function(response){
                    console.log("ok!!!");
                },
                error:function(){
                    console.log("Error!!!");
                }
           });
            
        });
    ');
}
?>


<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.organization.your_rest_two', ['ru'=>'Ваши рестораны']) ?>
        <small><?= Yii::t('app', 'franchise.views.organization.rest_info', ['ru'=>'Подключенные Вами рестораны и информация о них']) ?></small>
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
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <?= Html::label(Yii::t('app', 'franchise.views.organization.search_three', ['ru'=>'Поиск']), null, ['style' => 'color:#555']) ?>
                    <div class="input-group  pull-left">
                        <span class="input-group-addon">
                            <i class="fa fa-search"></i>
                        </span>
                        <?= Html::input('text', 'search', $searchModel['searchString'], ['class' => 'form-control', 'placeholder' => Yii::t('app', 'franchise.views.organization.search_four', ['ru'=>'Поиск']), 'id' => 'search', 'style'=>'width:300px']) ?>
                    </div>
                </div>

                <div class="col-lg-5 col-md-6 col-sm-6">
                    <?= Html::label(Yii::t('app', 'franchise.views.organization.date_from_to', ['ru'=>'Начальная дата / Конечная дата']), null, ['style' => 'color:#555']) ?>
                    <div class="form-group" style="width: 300px; height: 44px;">
                        <?=
                        DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'date_from',
                            'attribute2' => 'date_to',
                            'options' => ['placeholder' => Yii::t('app', 'franchise.views.organization.date_three', ['ru'=>'Дата']), 'id' => 'dateFrom'],
                            'options2' => ['placeholder' => Yii::t('app', 'franchise.views.organization.date_to_two', ['ru'=>'Конечная дата']), 'id' => 'dateTo'],
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
                        'filename' => Yii::t('app', 'franchise.views.organization.rest_three', ['ru'=>'Рестораны - ']) . date('Y-m-d'),
                        'encoding' => 'UTF-8',
                        'target' => ExportMenu::TARGET_SELF,
                        'showConfirmAlert' => false,
                        'showColumnSelector' => false,
                        'batchSize' => 200,
                        'timeout' => 0,
                        'dropdownOptions' => [
                            'label' => '<span class="text-label">' . Yii::t('app', 'franchise.views.organization.download_list', ['ru'=>'Скачать список']) . ' </span>',
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
                        'onRenderSheet' => function($sheet, $grid) {
                            $i=2;
                            while($sheet->cellExists("B".$i)){
                                $sheet->setCellValue("B".$i, html_entity_decode($sheet->getCell("B".$i)));
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
                        'id' => 'clientsList',
                        'dataProvider' => $dataProvider,
                        'pjax' => true,
                        'pjaxSettings' => ['options' => ['id' => 'kv-unique-id-1'], 'loadingCssClass' => false],
                        'filterModel' => $searchModel,
                        'filterPosition' => false,
                        'summary' => '',
                        'options' => ['class' => 'table-responsive'],
                        'tableOptions' => ['class' => 'table table-bordered', 'role' => 'grid'],
                        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
                        'bordered' => false,
                        'striped' => false,
                        'condensed' => false,
                        'responsive' => false,
                        'hover' => false,
                        'resizableColumns' => false,
                        'export' => [
                            'fontAwesome' => true,
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
                                        return $data['name'] . " &nbsp; <i title='" . Yii::t('app', 'franchise.views.organization.self_registered_two', ['ru'=>'Клиент самостоятельно зарегистрировался']) . "' class='text-success' aria-hidden='true'><img src='/images/new.png' alt='" . Yii::t('app', 'franchise.views.organization.self_registered_three', ['ru'=>'Клиент самостоятельно зарегистрировался']) . "'></i>";
                                    }
                                    return $data['name'];
                                },
                                'label' => Yii::t('app', 'franchise.views.organization.rest_name', ['ru'=>'Имя ресторана']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'vendor_count',
                                'value' => function ($data) {
                                    $progress = $data["vendor_count"] > 0 ? round($data["vendor_count_prev30"] * 100 / $data["vendor_count"], 2) : 0;
//                                            if ($progress > 0) {
                                    $divider = '<i class="fa fa-caret-up"></i>';
                                    //                                          }
                                    $class = "text-red";
                                    if ($progress > 20) {
                                        $class = "text-green";
                                    } elseif ($progress > 0) {
                                        $class = " text-orange";
                                    }
                                    return $data["vendor_count"] . " <span class='description-percentage $class'>$divider $progress%";
                                },
                                'label' => Yii::t('app', 'franchise.views.organization.vendors_quan', ['ru'=>'Кол-во поставщиков']),
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
                                'label' => Yii::t('app', 'franchise.views.organization.orders_quan_two', ['ru'=>'Кол-во заказов']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'order_sum',
                                'value' => function ($data) use($currencyData) {
                                    $progress = $data["order_sum"] > 0 ? round($data["order_sum_prev30"] * 100 / $data["order_sum"], 2) : 0;
//                                            if ($progress > 0) {
                                    $divider = '<i class="fa fa-caret-up"></i>';
                                    //                                          }
                                    $class = "text-red";
                                    if ($progress > 20) {
                                        $class = "text-green";
                                    } elseif ($progress > 0) {
                                        $class = " text-orange";
                                    }
                                    return ($data["order_sum"] ? $data["order_sum"] : 0) . " " . $currencyData['iso_code'] . " <span class='description-percentage $class'>$divider $progress%";
                                },
                                'label' => Yii::t('app', 'franchise.views.organization.clients.sum', ['ru'=>'Сумма заказов']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function ($data) {
                                    $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => Yii::t('app', 'franchise.views.organization.register_date_two', ['ru'=>'Дата регистрации']),
                            ],
                            [
                                'attribute' => 'contact_name',
                                'value' => 'contact_name',
                                'label' => Yii::t('app', 'franchise.views.organization.contact_two', ['ru'=>'Контакт']),
                            ],
                            [
                                'attribute' => 'phone',
                                'value' => 'phone',
                                'label' => Yii::t('app', 'franchise.views.organization.phone_three', ['ru'=>'Телефон']),
                            ],
                            [
                                'format' => 'raw',
                                'visible'=>(preg_match('~/organization/clients~',$_SERVER['REQUEST_URI'])),
                                'value' => function ($data) {
                                    return Html::a('<i class="fa fa-signal"></i>', ['analytics/client-stats', 'id' => $data["id"]], ['class' => 'stats']);
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'visible'=>(preg_match('~/organization/clients~',$_SERVER['REQUEST_URI'])),
                                'template' => '{delete}',
                                'buttons' => [
                                    'delete' => function ($url, $data) {
                                        return Html::a(
                                            '<span class="glyphicon glyphicon-trash text-red" title="' . Yii::t('app', 'franchise.views.organization.del', ['ru'=>'Удалить']) . ' "></span>',
                                            null, ['data-pjax' => '0', 'class' => 'f-delete', 'url' => Url::to(['organization/delete', 'id' => $data["franchisee_associate_id"]])]);
                                    },
                                ],
                            ],
                            [
                                'visible'=>(preg_match('~/organization/notifications~',$_SERVER['REQUEST_URI'])),
                                'header' => 'Уведомлять о новых заказах(да/нет)',
                                'cssClass'=>'allows',
                                'class' => 'yii\grid\CheckboxColumn', 'checkboxOptions' => function($model, $key, $index, $column) {
                        //print_r($model['id']);
                                    if(\common\models\RelationUserOrganization::findOne(['user_id'=>Yii::$app->user->id,'organization_id'=>$model['id']]))
                                    {
                                        $var = \common\models\notifications\EmailNotification::findOne(\common\models\RelationUserOrganization::findOne(['user_id'=>Yii::$app->user->id,'organization_id'=>$model['id']])->id);
                                        $notif = $var ? $var->order_created: 0;
                                    }else{
                                        $notif = null;
                                    }
                                    return $notif ? ['checked' => "checked", 'value'=>$model['id']] : ['value'=>$model['id']];
                            },
                            ],
                        ],
                        'rowOptions' => function ($model, $key, $index, $grid) {
                            return ['data-url' => Url::to(['organization/ajax-show-client', 'id' => $model["id"]])];
                        },
                    ]);
                    ?>
                </div>
            </div>
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
<?php
$url = Url::to(['organization/clients']);
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
     push: true,
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
