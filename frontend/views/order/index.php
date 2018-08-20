...

<?php

use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\date\DatePicker;
use common\components\SearchOrdersComponent;
use common\components\EchoRu;
use common\models\search\OrderSearch;
use common\models\Order;
use yii\grid\CheckboxColumn;
use common\components\UrlPjax;
use kartik\select2\Select2;

/**
 * Order list view [basic]
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-14
 * @author Mixcart
 * @module Frontend
 * @version 1.0
 */

/** @var $totalPrice int sum of total_price for orders */
/** @var $counts array Counts */
/** @var $searchParams array Search Params */
/** @var $searchModel OrderSearch */
/** @var $affiliated array */
/** @var $businessType string */
/** @var $selected array */

$forVendor = FALSE;
$forRestaurant = FALSE;
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
    $forVendor = TRUE;
}
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
    $forRestaurant = TRUE;
}

$headers = [
    'vendor' => EchoRu::echo ('frontend.views.order.vendor', 'Поставщик'),
    'client' => EchoRu::echo ('frontend.views.order.rest_two', 'Ресторан'),
];


$dataColumns = [

    // 1. ЧЕКБОКС (ТОЛЬКО ПОСТАВЩИКИ)
    [
        'visible' => $forVendor,
        'class' => CheckboxColumn::class,
        'contentOptions' => ['class' => 'small_cell_checkbox'],
        'headerOptions' => ['style' => 'text-align:center'],
        'checkboxOptions' => function ($model) use ($selected) {
            return ['value' => $model['id'], 'class' => 'checkbox-export', 'checked' => in_array($model['id'], $selected)];
        }
    ],

    // 2. ID заказа
    [
        'attribute' => 'id',
        'label' => '№',
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_id'],
        'value' => function ($data) {
            return UrlPjax::make($data->id, 'order/view');
        },
    ],

    // 3. Контрагент по договору поставки
    ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) ? [
        'attribute' => 'vendor.name',
        'label' => $headers['vendor'],
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_supp'],
        'value' => function ($data) {
            $text = NULL;
            if (isset($data->vendor->ediOrganization->gln_code) && $data->vendor->ediOrganization->gln_code > 0) {
                $alt = EchoRu::echo ('frontend.views.client.suppliers.edi_alt_text',
                    'Поставщик работает через систему электронного документооборота', 'app');
                $text = Html::img(Url::to('/img/edi-logo.png'), ['alt' => $alt, 'title' => $alt, 'width' => 35]);
            }
            $url = UrlPjax::make($data->vendor->name ?? '', 'order/view', $data->id);
            return "<div class='col-md-10'>" . $url . " </div><div class='col-md-2'>" . $text . "</div>";
        },
    ] : [
        'attribute' => 'client.name',
        'label' => $headers['client'],
        'format' => 'raw',
        'value' => function ($data) {
            return UrlPjax::make($data->client->name ?? '', 'order/view', $data->id);
        },
    ],

    // 4. Кто создал заказ
    [
        'attribute' => 'createdByProfile.full_name',
        'label' => EchoRu::echo ('frontend.views.order.order_created_by', 'Заказ создал'),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_sozdal'],
        'value' => function ($data) {
            return UrlPjax::make($data->createdByProfile->full_name ?? '', 'order/view', $data->id);
        },
    ],

    // 5. Кто принял заказ
    [
        'attribute' => 'acceptedByProfile.full_name',
        'label' => EchoRu::echo ('frontend.views.order.accepted_by', 'Заказ принял'),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_prinyal', 'style' => 'min-width: 120px;'],
        'value' => function ($data) {
            return UrlPjax::make($data->acceptedByProfile->full_name ?? '', 'order/view', $data->id);
        },
    ],

    // 6. Сумма
    [
        'attribute' => 'total_price',
        'label' => EchoRu::echo ('frontend.views.order.summ', 'Сумма'),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_sum', 'style' => 'min-width: 120px; text-align: right'],
        'value' => function ($data) {
            $title = $data->currency->symbol ?? '';
            return UrlPjax::make("<b>$data->total_price</b> " . $title, 'order/view', $data->id);
        },
    ],

    // 7. Дата создания
    [
        'attribute' => 'created_at',
        'label' => EchoRu::echo ('frontend.views.order.creating_date', 'Дата создания'),
        'format' => 'raw',
        'contentOptions' => ['style' => 'min-width: 120px; text-align: center'],
        'value' => function ($data) {
            $title = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
            return UrlPjax::make('<i class="fa fa-fw fa-calendar""></i> ' . $title, 'order/view', $data->id);
        },
    ],

    // 8. Дата завершения
    [
        'attribute' => 'finished_at',
        'label' => EchoRu::echo ('frontend.views.order.final_date', 'Дата финальная'),
        'format' => 'raw',
        'contentOptions' => ['style' => 'min-width: 120px; text-align: center'],
        'value' => function ($data) {
            $fdate = ($data->requested_delivery) ? $data->requested_delivery : $data->updated_at;
            $fdate = $data->actual_delivery ? $data->actual_delivery : $fdate;
            $fdate = Yii::$app->formatter->asDatetime($fdate, "php:j M Y");
            $title = '<i class="fa fa-fw fa-calendar""></i> ' . $fdate ?? '';
            return UrlPjax::make($title, 'order/view', $data->id);
        },
    ],

    // 9. Статус
    [
        'attribute' => 'status',
        'label' => EchoRu::echo ('frontend.views.order.status_two', 'Статус'),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_status'],
        'value' => function ($data) {
            $statusClass = '';
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
            $title = '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>' ?? '';
            return UrlPjax::make($title, 'order/view', $data->id);

        },
    ],
    [
        'visible' => $forRestaurant,
        'format' => 'raw',
        'value' => function ($data) {
            $class = $message = $message_orig = $url = NULL;
            if (in_array($data->status, [Order::STATUS_DONE, Order::STATUS_REJECTED, Order::STATUS_CANCELLED])) {
                $message_orig = EchoRu::echo ('frontend.views.order.repeat_order', 'Повторить заказ');
                $message = EchoRu::echo ('frontend.views.order.repeat', 'Повторить');
                $class = 'reorder btn btn-outline-processing';
                $url = 'order/repeat';
            } elseif ($data->isObsolete) {
                $message_orig = EchoRu::echo ('frontend.views.order.complete_order', 'Завершить заказ');
                $message = EchoRu::echo ('frontend.views.order.complete', 'Завершить');
                $class = 'complete btn btn-outline-success';
                if (isset($data->vendor->ediOrganization->gln_code) && $data->vendor->ediOrganization->gln_code > 0) {
                    $class = 'complete btn btn-outline-success completeEdi';
                }
                $url = 'complete-obsolete';
            }
            if ($class) {
                return Html::a($message, '#', [
                    'class' => $class,
                    'data' => ['toggle' => 'tooltip', 'original-title' => $message_orig,
                        'url' => Url::to([$url, 'id' => $data->id])],
                ]);
            }
            return NULL;
        },
        'contentOptions' => ['class' => 'text-center'],
        'headerOptions' => ['style' => 'width: 20px;']
    ],
];

#-----------------------------------------------------------------------------------------------------------------------
# 1. ПАРАМЕТРЫ СТРАНИЦЫ (СЕРВИСНАЯ ИНФОРМАЦИЯ)
$this->title = EchoRu::echo ('frontend.views.order.order_four', 'Заказы');
#-----------------------------------------------------------------------------------------------------------------------

#-----------------------------------------------------------------------------------------------------------------------
# 2. ЛОКАЛИЗАЦИЯ, НАДПИСИ И ТЕКСТЫ
#-----------------------------------------------------------------------------------------------------------------------
# 2.1. СТАТА
$msg = [
    'new' => EchoRu::echo ('frontend.views.order.new', 'Новые'),
    'processing' => EchoRu::echo ('frontend.views.order.in_process', 'Выполняются'),
    'fulfilled' => EchoRu::echo ('frontend.views.order.ended', 'Завершено'),
    'totalPrice' => EchoRu::echo ('frontend.views.order.summ_completed', 'Всего выполнено на сумму'),
];
$stata = [
    'totalPrice' => Yii::$app->formatter->asDecimal($totalPrice, 2),
];

#-----------------------------------------------------------------------------------------------------------------------
# 2.2. КНОПКИ
$btn = [
    'excel' => EchoRu::echo ('frontend.views.order.index.report', 'отчет xls', 'app'),
    'grid' => EchoRu::echo ('frontend.views.order.index.grid-report', 'Сеточный отчет', 'app'),
];
#-----------------------------------------------------------------------------------------------------------------------

#-----------------------------------------------------------------------------------------------------------------------
# 3. ФИЛЬТРЫ (В виде инпутов или селектов)
#-----------------------------------------------------------------------------------------------------------------------
# 3.1. Заголовки фильтров
$filterLabels = [
    'orderId' => EchoRu::echo ('frontend.views.order.id', 'Номер заказа'),
    'orderAff' => EchoRu::echo ('frontend.views.order.clients', 'Рестораны'),
    'orderLastUpdated' => EchoRu::echo ('frontend.views.order.last_updated.range',
        'Начальная дата / Конечная дата'),
    'orderStatus' => EchoRu::echo ('frontend.views.order.status', 'Статус'),
];
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
    $filterLabels['orderAff'] = EchoRu::echo ('frontend.views.order.vendors', 'Поставщики');
}
#-----------------------------------------------------------------------------------------------------------------------
# 3.2. Виджеты фильтров
$filterWidgetNames = [
    'orderAff' => Select2::class,
    'orderStatus' => Select2::class,
];
#-----------------------------------------------------------------------------------------------------------------------
# 3.3. Опции фильтров
$filterOptions = [
    'orderAff' => $affiliated,
    'orderStatus' => [
        EchoRu::echo ('frontend.views.order.all', 'Все'),
        EchoRu::echo ('frontend.views.order.new', 'Новый'),
        EchoRu::echo ('frontend.views.order.canceled', 'Отменен'),
        EchoRu::echo ('frontend.views.order.in_process_two', 'Выполняется'),
        EchoRu::echo ('frontend.views.order.completed', 'Завершен'),
    ],
];
$filterOptions['orderAff'][''] = EchoRu::echo ('frontend.views.order.select.aff.all', 'Все');
#-----------------------------------------------------------------------------------------------------------------------
# 3.4. Плейсхолдеры / значения фильтров (для селектов типа kartik - те же самые предустановленные значения фильтров)
$filterValues = [
    'orderId' => EchoRu::echo ('frontend.views.order.id', 'Номер заказа'),
    'orderAff' => $filterOptions['orderAff'][''],
    'orderStatus' => $filterOptions['orderStatus'][0],
    'dateFrom' => $searchParams['OrderSearch2']['date_from'] ?? '',
    'dateTo' => $searchParams['OrderSearch2']['date_to'] ?? '',
];
if (isset($searchParams['OrderSearch']['id']) && (int)$searchParams['OrderSearch']['id'] > 0) {
    $filterValues['id'] = (int)$searchParams['OrderSearch2']['id'];
}
#-----------------------------------------------------------------------------------------------------------------------
# 3.5. Динамичные идентификаторы (для ресторанов и для поставщиков типы контрагентов различаются)
$filterSwitcher = [
    'orderAff' => 'client_id',
];
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
    $filterSwitcher['orderAff'] = 'vendor_id';
}
#-----------------------------------------------------------------------------------------------------------------------

#-----------------------------------------------------------------------------------------------------------------------
# 4. ДОПОЛНЕНИЕ ДАННЫХ JS и CSS
#-----------------------------------------------------------------------------------------------------------------------
$urlExport = Url::to(['/order/export-to-xls']);
$urlReport = Url::to(['/order/grid-report']);
$urlSaveSelected = Url::to(['/order/save-selected-orders']);
$i = 'Внимание, данные о фактическом приёме товара будут направлены ПОСТАВЩИКУ! Вы подтверждаете корректность данных?';
$titleCompleteEDI = EchoRu::echo ('frontend.views.order.complete_edi', $i, 'app');
$btnYes = EchoRu::echo ('frontend.views.order.yep', 'Да');
$btnNo = EchoRu::echo ('frontend.views.order.cancel', 'Нет');
$js = <<< JS
$("document").ready(function () {

    $(".box-body").on("change", "#orderFilter", function () {
        var target = "http:";
        var w = window.location.protocol;
        if (w === "https:") {
            target = "https:";
        }
        target = target + '//' + window.location.hostname + '/order?OrderSearch2[id]=' + $("#orderFilter").val();
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

    $(".box-body").on("change", "#ordersearch2-doc_status", function () {
        $("#search-form").submit();
    });

    $(document).on("click", ".export-to-xls", function () {
        if ($("#orderHistory").yiiGridView("getSelectedRows").length > 0) {
            window.location.href = "$urlExport?selected=" + $("#orderHistory").yiiGridView("getSelectedRows") + "&page=" + current_page;
        }

    });
    $(document).on("click", ".grid-report", function () {
        if ($("#orderHistory").yiiGridView("getSelectedRows").length > 0) {
            window.location.href = "$urlReport?selected=" + $("#orderHistory").yiiGridView("getSelectedRows") + "&page=" + current_page;
        }
    });

    var current_page = 0;
    $(document).on("click", ".pagination a", function (e) {
        e.preventDefault();
        var url = $(this).attr("href");

        $.ajax({
            url: "$urlSaveSelected?selected=" + $("#orderHistory").yiiGridView("getSelectedRows") + "&page=" + current_page,
            type: "GET",
            success: function () {
                $.pjax.reload({container: "#order-list", url: url, timeout: 30000});
            }
        });

        current_page = $(this).attr("data-page")
    });

    $(".box-body").on("click", "td", function () {
        if ($(this).find("input").hasClass("checkbox-export")) {
            return true;
        }
        if ($(this).find("a").hasClass("reorder") ||
            $(this).find("a").hasClass("complete")
        ) {
            return true;
        }

        var url = $(this).parent("tr").data("url");
        if (url !== undefined) {
            location.href = url;
        }
    });

    $(document).on("click", ".reorder, .complete", function (e) {
        e.preventDefault();
        var title;
        var clicked = $(this);
        if ($(this).hasClass("completeEdi")) {
            title = "$titleCompleteEDI";
        } else {
            title = clicked.data("original-title") + "?";
        }
        swal({
            title: title,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "$btnYes",
            cancelButtonText: "$btnNo",
            showLoaderOnConfirm: true
        }).then(function (result) {
            if (result.dismiss === "cancel") {
                swal.close();
            } else {
                document.location = clicked.data("url")
            }
        });
    });

});   
JS;
$js2 = NULL;
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
    $js2 = <<< JS
$("document").ready(function () {
    $(".box-body").on("change", "#ordersearch2-vendor_id", function () {
        $("#search-form").submit();
    });
});
  
JS;
} elseif ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
    $js2 = <<< JS
 $("document").ready(function () {
    $(".box-body").on("change", "#ordersearch2-client_id", function () {
        $("#search-form").submit();
    });
});
JS;
}
$this->registerJs($js . $js2);
#-----------------------------------------------------------------------------------------------------------------------
$css = <<< CSS

tr:hover {
    cursor: pointer;
}

#orderHistory a:not(.btn) {
    color: #333;
}

.dataTable a {
    width: 100%;
    min-height: 17px;
    display: inline-block;
}

#select2-ordersearch2-vendor_id-container, #select2-ordersearch2-client_id-container {
    margin-top: 0;
}

.select2-selection__clear {
    display: none;
}

#select2-ordersearch2-vendor_id-container,
#select2-ordersearch2-client_id-container,
#select2-ordersearch2-doc_status-container {
    margin-top: 0 !important
}

CSS;
$this->registerCss($css);
#-----------------------------------------------------------------------------------------------------------------------
?>

<section class="content-header">
    <h1>
        <i class="fa fa-history"></i> <?= EchoRu::echo ('frontend.views.order.orders', 'Заказы') ?>
        <small>
            <?= EchoRu::echo ('frontend.views.order.orders_list', 'Список всех созданных заказов') ?>
        </small>
    </h1>
    <?= Breadcrumbs::widget([
        'options' => ['class' => 'breadcrumb',],
        'homeLink' => ['label' => EchoRu::echo ('frontend.views.to_main', 'Главная', 'app'),
            'url' => '/'],
        'links' => [EchoRu::echo ('frontend.views.order.orders_history', 'История заказов')],
    ])
    ?>
</section>
<section class="content">
    <div class="box box-info order-history">
        <div class="box-body">
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status new"><?= $counts['new'] ?></span>
                        <span class="info-box-text"><?= $msg['new'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status processing"><?= $counts['processing'] ?></span>
                        <span class="info-box-text"><?= $msg['processing'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box">
                    <div class="info-box-content">
                        <span class="info-box-number status done"><?= $counts['fulfilled'] ?></span>
                        <span class="info-box-text"><?= $msg['fulfilled'] ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-3">
                <div class="info-box bg-total-price">
                    <div class="info-box-content">
                        <span class="info-box-number"><?= $stata['totalPrice'] ?> <i class="fa fa-fw fa-rub"></i></span>
                        <span class="info-box-text"><?= $msg['totalPrice'] ?></span>
                    </div>
                </div>
            </div>
            <div style="clear: both;">
            </div>
        </div>
    </div>
    <div class="box box-info order-history">
        <div class="box-body">
            <?php
            Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]);
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
                    echo $form->field($searchModel, $filterSwitcher['orderAff'])->widget($filterWidgetNames['orderAff'], [
                        'data' => $filterOptions['orderAff'],
                        'pluginOptions' => ['allowClear' => TRUE],
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
                    echo $form->field($searchModel, 'doc_status')->widget($filterWidgetNames['orderStatus'], [
                        'data' => $filterOptions['orderStatus'], 'options' => ['placeholder' => $filterValues['orderStatus']],
                        'pluginOptions' => ['allowClear' => TRUE], 'hideSearch' => TRUE,
                        'id' => 'docStatus',
                    ])->label($filterLabels['orderStatus'], ['class' => 'label', 'style' => 'color:#555']);
                    ?>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="label" style="color:#555" for="statusFilter">&nbsp;</label><br/>
                    <a class="btn btn-warning" href="<?= Url::to(['/orders']) ?>">Сбросить фильтры</a>
                </div>
            </div>
            <?php if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) { ?>
                <?= Html::submitButton('<i class="fa fa-file-excel-o"></i> ' . $btn['excel'],
                    ['class' => 'btn btn-success export-to-xls']) ?>
                <?= Html::submitButton('<i class="fa fa-th"></i> ' . $btn['grid'],
                    ['class' => 'btn btn-success grid-report']) ?>
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
                        'tableOptions' => ['class' => 'table table-bordered table-striped table-hover dataTable',
                            'role' => 'grid'],
                        'columns' => $dataColumns
                    ]);
                    ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
            <?php Pjax::end() ?>
        </div>
    </div>
</section>


