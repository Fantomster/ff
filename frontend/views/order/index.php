<?php

use common\models\OrderStatus;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use yii\widgets\Pjax;
use yii\helpers\Html;
use yii\helpers\Url;
//use yii\grid\GridView;
use kartik\grid\GridView;
use kartik\date\DatePicker;
use common\components\SearchOrdersComponent;
use common\models\search\OrderSearch;
use common\models\Order;
use yii\grid\CheckboxColumn;
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
$forVendor = false;
$forRestaurant = false;
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) {
    $forVendor = true;
}
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
    $forRestaurant = true;
}

$headers = [
    'vendor' => Yii::t('message', 'frontend.views.order.vendor', ['ru' => 'Поставщик']),
    'client' => Yii::t('message', 'frontend.views.order.rest_two', ['ru' => 'Ресторан']),
];

$urlSaveSelected = Url::to(['order/save-selected-orders']);

$dataColumns = [
    // 1. ЧЕКБОКС (ТОЛЬКО ПОСТАВЩИКИ)
   /*[
        'visible' => $forVendor,
        'class' => CheckboxColumn::class,
        'contentOptions' => ['class' => 'small_cell_checkbox'],
        'headerOptions' => ['style' => 'text-align:center'],
        'checkboxOptions' => function ($model) use ($selected) {
            return ['value' => $model['id'], 'class' => 'checkbox-export', 'checked' => in_array($model['id'], $selected)];
        }
    ],*/
    [
        'class' => 'common\components\multiCheck\CheckboxColumn',
        'visible' => $forVendor,
        'contentOptions' => ['class' => 'small_cell_checkbox'],
        'headerOptions' => ['style' => 'text-align:center;'],
        'onChangeEvents' => [
            'changeAll' => 'function(e) {
                                                            url      = window.location.href;
                                                            var value = [];
                                                            state = $(this).prop("checked") ? 1 : 0;
    
                                                           $(".checkbox-export").each(function() {
                                                                value.push($(this).val());
                                                            });    
                                                
                                                           value = value.toString();  
                                                           
                                                           $.ajax({
                                                             url: "'.$urlSaveSelected.'?selected=" +  value+"&state=" + state,
                                                             type: "POST",
                                                             data: {selected: value, state: state},
                                                             success: function(data){
                                                             //$.pjax.reload({container: "#order-list", url: url, timeout:30000});
                                                             }
                                                           }); }',
            'changeCell' => 'function(e) { 
                                                            state = $(this).prop("checked") ? 1 : 0;
                                                            selectedCount = parseInt($("#selected_info").text());
                                                             
                                                            url = window.location.href;
                                                            var value = $(this).val();
                                                          
                                                           $.ajax({
                                                             url: "'.$urlSaveSelected.'?selected=" +  value+"&state=" + state,
                                                             type: "POST",
                                                             data: {selected: value, state: state},
                                                             success: function(data){
                                                             //$.pjax.reload({container: "#order-list", url: url, timeout:30000});                                                             
                                                                
                                                             }
                                                           });}'
        ],
        'checkboxOptions' => function ($model, $key, $index, $widget) use ($selected) {
            if(in_array($model['id'], $selected)){
                return ['value' => $model['id'], 'class' => 'checkbox-export', 'checked' => 'checked'];
            } else {
                return ['value' => $model['id'], 'class' => 'checkbox-export'];
            }
        },
    ],
    // 2. ID заказа
    [
        'attribute' => 'id',
        'label' => '№',
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_id'],
        'value' => function ($data) {
            return Html::a($data->id, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
        },
    ],
    // 3. Контрагент по договору поставки
    ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) ? [
'attribute' => 'vendor.name',
 'label' => $headers['vendor'],
 'format' => 'raw',
 'contentOptions' => ['class' => 'small_cell_supp'],
 'value' => function ($data) {
    $text = null;
    if (isset($data->vendor->ediOrganization->gln_code) && $data->vendor->ediOrganization->gln_code > 0) {
        $alt = Yii::t('app', 'frontend.views.client.suppliers.edi_alt_text', ['ru' => 'Поставщик работает через систему электронного документооборота']);
        $text = Html::img(Url::to('/img/edi-logo.png'), ['alt' => $alt, 'title' => $alt, 'width' => 35]);
    }
    $url = Html::a($data->vendor->name ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
    return "<div class='col-md-10'>" . $url . " </div><div class='col-md-2'>" . $text . "</div>";
},
    ] : [
'attribute' => 'client.name',
 'label' => $headers['client'],
 'format' => 'raw',
 'value' => function ($data) {
    return Html::a($data->client->name ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
},
    ],
    // 4. Кто создал заказ
    [
        'attribute' => 'createdByProfile.full_name',
        'label' => Yii::t('message', 'frontend.views.order.order_created_by', ['ru' => 'Заказ создал']),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_sozdal'],
        'value' => function ($data) {
            return Html::a($data->createdByProfile->full_name ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
        },
    ],
    // 5. Кто принял заказ
    [
        'attribute' => 'acceptedByProfile.full_name',
        'label' => Yii::t('message', 'frontend.views.order.accepted_by', ['ru' => 'Заказ принял']),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_prinyal', 'style' => 'min-width: 120px;'],
        'value' => function ($data) {
            return Html::a($data->acceptedByProfile->full_name ?? '', Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
        },
    ],
    // 6. Сумма
    [
        'attribute' => 'total_price',
        'label' => Yii::t('message', 'frontend.views.order.summ', ['ru' => 'Сумма']),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_sum', 'style' => 'min-width: 120px; text-align: right'],
        'value' => function ($data) {
            $title = $data->currency->symbol ?? '';
            return Html::a("<b>$data->total_price</b> " . $title, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
        },
    ],
    // 7. Дата создания
    [
        'attribute' => 'created_at',
        'label' => Yii::t('message', 'frontend.views.order.creating_date', ['ru' => 'Дата создания']),
        'format' => 'raw',
        'contentOptions' => ['style' => 'min-width: 120px; text-align: center'],
        'value' => function ($data) {
            $title = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
            return Html::a('<i class="fa fa-fw fa-calendar""></i> ' . $title, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
        },
    ],
    // 8. Дата завершения
    [
        'attribute' => 'finished_at',
        'label' => Yii::t('message', 'frontend.views.order.final_date', ['ru' => 'Дата финальная']),
        'format' => 'raw',
        'contentOptions' => ['style' => 'min-width: 120px; text-align: center'],
        'value' => function ($data) {
            $fdate = ($data->requested_delivery) ? $data->requested_delivery : $data->updated_at;
            $fdate = $data->actual_delivery ? $data->actual_delivery : $fdate;
            $fdate = Yii::$app->formatter->asDatetime($fdate, "php:j M Y");
            $title = '<i class="fa fa-fw fa-calendar""></i> ' . $fdate ?? '';
            return Html::a($title, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
        },
    ],
    // 9. Статус
    [
        'attribute' => 'status',
        'label' => Yii::t('message', 'frontend.views.order.status_two', ['ru' => 'Статус']),
        'format' => 'raw',
        'contentOptions' => ['class' => 'small_cell_status'],
        'value' => function ($data) {
            $statusClass = '';
            switch ($data->status) {
                case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                case OrderStatus::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                    $statusClass = 'new';
                    break;
                case OrderStatus::STATUS_PROCESSING:
                    $statusClass = 'processing';
                    break;
                case OrderStatus::STATUS_DONE:
                    $statusClass = 'done';
                    break;
                case OrderStatus::STATUS_REJECTED:
                case OrderStatus::STATUS_CANCELLED:
                    $statusClass = 'cancelled';
                    break;
            }
            $title = '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>' ?? '';
            return Html::a($title, Url::to(['order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0"]);
        },
    ],
    [
        'visible' => $forRestaurant,
        'format' => 'raw',
        'value' => function ($data) {
            $class = $message = $message_orig = $url = null;
            $disabledString = (Yii::$app->user->identity->role_id == \common\models\Role::ROLE_RESTAURANT_ORDER_INITIATOR) ? " disabled" : "";
            if (in_array($data->status, [OrderStatus::STATUS_DONE, OrderStatus::STATUS_REJECTED, OrderStatus::STATUS_CANCELLED])) {
                $message_orig = Yii::t('message', 'frontend.views.order.repeat_order', ['ru' => 'Повторить заказ']);
                $message = Yii::t('message', 'frontend.views.order.repeat', ['ru' => 'Повторить']);
                $class = 'reorder btn btn-outline-processing';
                $url = 'order/repeat';
            } elseif ($data->isObsolete) {
                $message_orig = Yii::t('message', 'frontend.views.order.complete_order', ['ru' => 'Завершить заказ']);
                $message = Yii::t('message', 'frontend.views.order.complete', ['ru' => 'Завершить']);
                $class = "complete btn btn-outline-success$disabledString";
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
            return null;
        },
        'contentOptions' => ['class' => 'text-center'],
        'headerOptions' => ['style' => 'width: 20px;']
    ],
];

#-----------------------------------------------------------------------------------------------------------------------
# 1. ПАРАМЕТРЫ СТРАНИЦЫ (СЕРВИСНАЯ ИНФОРМАЦИЯ)
$this->title = Yii::t('message', 'frontend.views.order.order_four', ['ru' => 'Заказы']);
#-----------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------
# 2. ЛОКАЛИЗАЦИЯ, НАДПИСИ И ТЕКСТЫ
#-----------------------------------------------------------------------------------------------------------------------
# 2.1. СТАТА
$msg = [
    'new' => Yii::t('message', 'frontend.views.order.new', ['ru' => 'Новые']),
    'processing' => Yii::t('message', 'frontend.views.order.in_process', ['ru' => 'Выполняются']),
    'fulfilled' => Yii::t('message', 'frontend.views.order.ended', ['ru' => 'Завершено']),
    'totalPrice' => Yii::t('message', 'frontend.views.order.summ_completed', ['ru' => 'Всего выполнено на сумму']),
];
$stata = [
    'totalPrice' => Yii::$app->formatter->asDecimal($totalPrice, 2),
];

#-----------------------------------------------------------------------------------------------------------------------
# 2.2. КНОПКИ
$btn = [
    'excel' => Yii::t('app', 'frontend.views.order.index.report', ['ru' => 'отчет xls']),
    'grid' => Yii::t('app', 'frontend.views.order.index.grid-report', ['ru' => 'Сеточный отчет']),
];
#-----------------------------------------------------------------------------------------------------------------------
#-----------------------------------------------------------------------------------------------------------------------
# 3. ФИЛЬТРЫ (В виде инпутов или селектов)
#-----------------------------------------------------------------------------------------------------------------------
# 3.1. Заголовки фильтров
$filterLabels = [
    'orderId' => Yii::t('message', 'frontend.views.order.id', ['ru' => 'Номер заказа']),
    'orderAff' => Yii::t('message', 'frontend.views.order.clients', ['ru' => 'Рестораны']),
    'orderLastUpdated' => Yii::t('message', 'frontend.views.order.last_updated.range', ['ru' => 'Начальная дата / Конечная дата']),
    'orderStatus' => Yii::t('message', 'frontend.views.order.status', ['ru' => 'Статус']),
];
if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_RESTAURANT) {
    $filterLabels['orderAff'] = Yii::t('message', 'frontend.views.order.vendors', ['ru' => 'Поставщики']);
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
        Yii::t('message', 'frontend.views.order.all', ['ru' => 'Все']),
        Yii::t('message', 'frontend.views.order.new', ['ru' => 'Новый']),
        Yii::t('message', 'frontend.views.order.canceled', ['ru' => 'Отменен']),
        Yii::t('message', 'frontend.views.order.in_process_two', ['ru' => 'Выполняется']),
        Yii::t('message', 'frontend.views.order.completed', ['ru' => 'Завершен']),
    ],
];
$filterOptions['orderAff'][''] = Yii::t('message', 'frontend.views.order.select.aff.all', ['ru' => 'Все']);
#-----------------------------------------------------------------------------------------------------------------------
# 3.4. Плейсхолдеры / значения фильтров (для селектов типа kartik - те же самые предустановленные значения фильтров)
$filterValues = [
    'orderId' => Yii::t('message', 'frontend.views.order.id', ['ru' => 'Номер заказа']),
    'orderAff' => $filterOptions['orderAff'][''],
    'orderStatus' => $filterOptions['orderStatus'][0],
    'dateFrom' => $searchParams['OrderSearch2']['date_from'] ?? '',
    'dateTo' => $searchParams['OrderSearch2']['date_to'] ?? '',
];
if (isset($searchParams['OrderSearch']['id']) && (int) $searchParams['OrderSearch']['id'] > 0) {
    $filterValues['id'] = (int) $searchParams['OrderSearch2']['id'];
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
$titleCompleteEDI = Yii::t('app', 'frontend.views.order.complete_edi', ['ru' => $i]);
$btnYes = Yii::t('message', 'frontend.views.order.yep', ['ru' => 'Да']);
$btnNo = Yii::t('message', 'frontend.views.order.cancel', ['ru' => 'Нет']);

$url = Url::to('order');

$js = <<< JS
$("document").ready(function () {

    $(".box-body").on("change", "#orderFilter", function () {
        var target = "http:";
        var w = window.location.protocol;
        if (w === "https:") {
            target = "https:";
        }
        target = target + '//' + window.location.hostname + '/$url?OrderSearch2[id]=' + $("#orderFilter").val();
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
            window.location.href = "$urlExport";
        }

    });
    $(document).on("click", ".grid-report", function () {
        if ($("#orderHistory").yiiGridView("getSelectedRows").length > 0) {
            window.location.href = "$urlReport";
        }
    });

    /*var current_page = 0;
    $(document).on("click", ".pagination a", function (e) {
        e.preventDefault();
        var url = $(this).attr("href");

        $.ajax({
            url: "$urlSaveSelected?selected=" + $("#orderHistory").yiiGridView("getSelectedRows"),
            type: "GET",
            success: function () {
                $.pjax.reload({container: "#order-list", url: url, timeout: 30000});
            }
        });

        current_page = $(this).attr("data-page")
    });*/

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
$js2 = null;
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
        <i class="fa fa-history"></i> <?= Yii::t('message', 'frontend.views.order.orders', ['ru' => 'Заказы']) ?>
        <small>
            <?= Yii::t('message', 'frontend.views.order.orders_list', ['ru' => 'Список всех созданных заказов']) ?>
        </small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => ['class' => 'breadcrumb',],
        'homeLink' => ['label' => Yii::t('app', 'frontend.views.to_main', ['ru' => 'Главная']),
            'url' => '/'],
        'links' => [Yii::t('message', 'frontend.views.order.orders_history', ['ru' => 'История заказов'])],
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
                        'pluginOptions' => ['allowClear' => true],
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
                        <?=
                        DatePicker::widget([
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
                        'pluginOptions' => ['allowClear' => true], 'hideSearch' => true,
                        'id' => 'docStatus',
                    ])->label($filterLabels['orderStatus'], ['class' => 'label', 'style' => 'color:#555']);
                    ?>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="label" style="color:#555" for="statusFilter">&nbsp;</label><br/>
                    <a class="btn btn-warning" href="<?= Url::to(['/orders?OrderSearch2[reset]=1']) ?>">Сбросить фильтры</a>
                </div>
            </div>
            <?php if ($businessType == SearchOrdersComponent::BUSINESS_TYPE_VENDOR) { ?>
                <?= Html::button('<i class="fa fa-file-excel-o"></i> ' . $btn['excel'], ['class' => 'btn btn-success export-to-xls'])
                ?>
                <?= Html::button('<i class="fa fa-th"></i> ' . $btn['grid'], ['class' => 'btn btn-success grid-report'])
                ?>
            <?php } ?>
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
