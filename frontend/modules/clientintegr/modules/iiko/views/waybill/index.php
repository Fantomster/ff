<?php


use yii\widgets\Breadcrumbs;
use common\models\Order;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'Интеграция с iiko Office';

?>
<section class="content-header">
    <h1>
        <i class="fa fa-upload"></i> <?= $this->title ?>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
        'links' => [
            [
                'label' => 'Интеграция',
                'url' => ['/clientintegr'],
            ],
            $this->title
        ],
    ])
    ?>
</section>
<section class="content-header">
    <?= $this->render('/default/_menu.php'); ?>
    <?=
    $this->render('/default/_license_no_active.php', ['lic' => $lic]);
    ?>
    ЗАВЕРШЕННЫЕ ЗАКАЗЫ
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding orders-table">
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => true,
                            'filterPosition' => false,
                            'columns' => [
                                [
                                    'attribute' => 'id',
                                    'contentOptions' => function($data) {
                                        return ["id" => "way".$data->id];
                                    }
                                ],
                                [
                                      'attribute'=>'invoice_relation',
                                      'format'=>'raw',
                                    'visible'=>$visible,
                                    'header'=>'№ Накладной',
                                        'value'=>function($data){
                                            return ($data->invoice)?\yii\helpers\Html::encode($data->invoice->number):'';
                                        }
                                ],
                                [
                                    'attribute' => 'vendor.name',
                                    'value' => 'vendor.name',
                                    'label' => 'Поставщик',
                                    //'headerOptions' => ['class'=>'sorting',],
                                ],
                                [
                                    'format' => 'raw',
                                    'attribute' => 'status',
                                    'value' => function ($data) {
                                        $statusClass = 'done';

                                        return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>';
                                    },
                                    'label' => 'Статус Заказа',
                                ],
                                [
                                    'attribute' => 'updated_at',
                                    'label' => 'Обновлено',
                                    'format' => 'date',
                                ],
                                [
                                    'attribute' => 'positionCount',
                                    'label' => 'Кол-во позиций',
                                    'format'=>'raw',
                                    'value' => function ($data) {
                                        return $data->positionCount .
                                            '<a class="ajax-popover" data-container="body" data-content="Loading..." '.
                                            'data-html="data-html" data-placement="bottom" data-title="Состав Заказа" '.
                                            'data-toggle="popover"  data-trigger="focus" data-url="'.
                                            Url::base(true).Yii::$app->getUrlManager()->createUrl(['clientintegr/rkws/waybill/']).
                                            '/getpopover" role="button" tabindex="0" '.
                                            'data-original-title="" title="" data-model="'.$data->id.'"> '.
                                            '<i class="fa fa-info-circle"></i></a>';
                                    }
                                ],
                                [
                                    'attribute' => 'total_price',
                                    'label' => 'Итоговая сумма',
                                    'format' => 'raw',
                                ],
                                [
                                    'value' => function ($data) {
                                        $nacl = \api\common\models\iiko\iikoWaybill::findOne(['order_id' => $data->id]);
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
                                    'value'=>function ($model, $key, $index, $column) use ($way) {
                                        if ($model->id == $way) {
                                            return GridView::ROW_EXPANDED;
                                        }
                                        return GridView::ROW_COLLAPSED;
                                    },
                                    'detail' => function ($model, $key, $index, $column) {
                                        $wmodel = \api\common\models\iiko\iikoWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id])->one();

                                        if ($wmodel) {
                                            $wmodel = \api\common\models\iiko\iikoWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id]);
                                        } else {
                                            $wmodel = null;
                                        }
                                        $order_id = $model->id;
                                        return Yii::$app->controller->renderPartial('_expand-row-details', ['model' => $wmodel, 'order_id' => $order_id]);
                                    },
                                    'headerOptions' => ['class' => 'kartik-sheet-style'],
                                    'expandOneOnly' => true,
                                ],
                            ],
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
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$url = Url::toRoute('waybill/send');
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
                        title: 'Идет оптравка',
                        text: 'Подождите пока закончится выгрузка...',
                        onOpen: () => {
                            swal.showLoading();
                            $.post(url, {id:id}, function (data) {
                                console.log(data);
                                if (data.success === true) {
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
                                $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:2000});
                            })
                            .fail(function() { 
                               swal(
                                    'Ошибка',
                                    'Обратитесь в службу поддержки.',
                                    'error'
                                );
                               $.pjax.reload({container:"#pjax_user_row_" + oid + '-pjax', timeout:2000});
                            });
                        }
                    })
                }
            })
        });
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
