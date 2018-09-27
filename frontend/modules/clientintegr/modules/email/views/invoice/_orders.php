<style>
    #alShowAllWaybills {
        margin-top: 4px;
        position: absolute;
        margin-left: -10px;
    }
</style>
<div class="row">
    <div class="col-md-3">
        <b>Выберите заказ для связи с накладной:</b>
    </div>
    <div class="col-md-5">
        <?= \yii\helpers\Html::checkbox('show_all_waybills', $show_waybill, ['label' => "<span style='min-height: 20px; padding-left: 20px; margin-bottom: 0;'>" . Yii::t('app', 'franchise.views.anal.all_orders_four', ['ru' => 'Все заказы']) . "</span>", 'id' => 'alShowAllWaybills']); ?>
    </div>
</div>
<br>
<?php

use \common\models\Order;
use common\models\OrderStatus;
use yii\web\View;
use yii\helpers\Url;

$columns = [
    [
        'header' => 'выбрать / ' . \yii\helpers\Html::tag('i', '', ['class' => 'fa fa-close clear_radio', 'style' => 'cursor:pointer;color:red']),
        'format' => 'raw',
        'value' => function ($data) {
            return \yii\helpers\Html::input('radio', 'order_id', $data->id, ['class' => 'orders_radio']);
        },
        'contentOptions' => ['class' => 'text-center'],
        'headerOptions' => ['style' => 'width: 100px;'],
    ],
    [
        'attribute' => 'id',
        'format' => 'raw',
        'value' => function ($data) {
            return \yii\helpers\Html::a($data->id, Url::to(['/order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0", 'target' => '_blank']);
        },
        'label' => '№',
    ],
    [
        'attribute' => 'waybill_number',
        'value' => 'waybill_number',
    ],
    [
        'attribute' => 'vendor.name',
        'value' => 'vendor.name',
        'label' => Yii::t('message', 'frontend.views.client.index.vendor', ['ru' => 'Поставщик']),
    ],
    [
        'attribute' => 'createdByProfile.full_name',
        'value' => 'createdByProfile.full_name',
        'label' => Yii::t('message', 'frontend.views.client.index.created', ['ru' => 'Заказ создал']),
    ],
    [
        'attribute' => 'acceptedByProfile.full_name',
        'value' => 'acceptedByProfile.full_name',
        'label' => Yii::t('message', 'frontend.views.client.index.rec', ['ru' => 'Заказ принял']),
    ],
    [
        'attribute' => 'positionCount',
        'label' => 'Кол-во позиций',
        'format' => 'raw',
        'value' => function ($data) {
            return $data->positionCount .
                '<a class="ajax-popover" data-container="body" data-content="Loading..." ' .
                'data-html="data-html" data-placement="bottom" data-title="Состав Заказа" ' .
                'data-toggle="popover"  data-trigger="focus" data-url="' .
                Url::base(true) . Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/']) .
                '/getpopover" role="button" tabindex="0" ' .
                'data-original-title="" title="" data-model="' . $data->id . '"> ' .
                '<i class="fa fa-info-circle"></i></a>';
        }

    ],
    [
        'format' => 'raw',
        'attribute' => 'total_price',
        'value' => function ($data) {
            return "<b>$data->total_price</b> " . $data->currency->symbol;
        },
        'label' => Yii::t('message', 'frontend.views.client.index.sum', ['ru' => 'Сумма']),
    ],
    [
        'format' => 'raw',
        'attribute' => 'created_at',
        'value' => function ($data) {
            $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
            return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
        },
        'label' => Yii::t('message', 'frontend.views.client.index.created_at', ['ru' => 'Дата создания']),
    ],
    [
        'format' => 'raw',
        'attribute' => 'status',
        'value' => function ($data) {
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
            return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>'; //<i class="fa fa-circle-thin"></i>
        },
        'label' => Yii::t('message', 'frontend.views.client.index.status', ['ru' => 'Статус']),
    ]
];
?>

<?php

\yii\widgets\Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]);

echo \yii\helpers\Html::input('hidden', 'vendor_id', $vendor_id);
echo \yii\helpers\Html::input('hidden', 'invoice_id', $invoice_id);
$dataProvider->pagination->pageParam = 'page_order';
$dataProvider->sort = false;
echo \kartik\grid\GridView::widget([
    'filterModel' => $searchModel,
    'filterPosition' => false,
    'dataProvider' => $dataProvider,
    'layout' => '{items}{pager}',
    'summary' => false,
    'pjax' => true,
    'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
    'columns' => $columns
]);

$url = \Yii::$app->urlManager->createUrl('/clientintegr/email/invoice');
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

$customJs = <<< JS
 $('#alShowAllWaybills').on('click', function(e) {
     var checked = $(this).prop('checked');
     $.get('$url/get-orders-torg12', {
                    OrderSearch: {vendor_search_id: $vendor_id, vendor_id: $vendor_id},
                    invoice_id:"$invoice_id",
                    show_waybill: checked
                }, function (data) {
                    $('#invoice-orders').html(data);
                    $('.orders').show();
                    //     $(this).data('vendor_id', result.value);
                    // $(this).html(vendors[result.value]);
                });
     });
JS;
$this->registerJs($customJs, View::POS_READY);

?>

<?php
$js = <<< 'SCRIPT'
$('.ajax-popover').click(function() {
    var e = $(this);
    if (e.data('loaded') !== true) {
        $.ajax({
      url: e.data('url'),
      type: "POST",
      data: {key: e.data('model')}, // данные, которые передаём на сервер
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


<?php \yii\widgets\Pjax::end(); ?>