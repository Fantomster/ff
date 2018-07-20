<?php


use yii\widgets\Breadcrumbs;
use common\models\Order;
use kartik\grid\GridView;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

$this->title = 'Интеграция с iiko Office';
$this->registerCss("
    #select2-ordersearch-vendor_id-container{margin-top:0;}
");

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
    <?php
    $columns = array(
        [
            'attribute' => 'id',
            'contentOptions' => function ($data) {
                return ["id" => "way" . $data->id];
            },
            'format' => 'raw',
            'value' => function ($data) {
                return \yii\helpers\Html::a($data->id, Url::to(['/order/view', 'id' => $data->id]), ['class' => 'target-blank', 'data-pjax' => "0", 'target' => '_blank']);
            }
        ],
        [
            'attribute' => 'invoice_relation',
            'format' => 'raw',
            'visible' => $visible,
            'header' => '№ Накладной',
            'value' => function ($data) {
                return ($data->invoice) ? \yii\helpers\Html::encode($data->invoice->number) : '';
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
            'value' => function ($model, $key, $index, $column) use ($way) {
                if (($model->id == $way) or (Yii::$app->session->get('iiko_waybill') == $model->id)) {
                    Yii::$app->session->set("iiko_waybill", 0);
                    return GridView::ROW_EXPANDED;
                }
                return GridView::ROW_COLLAPSED;
            },
            'detail' => function ($model, $key, $index, $column) use ($lic) {
                $wmodel = \api\common\models\iiko\iikoWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id])->one();

                if ($wmodel) {
                    $wmodel = \api\common\models\iiko\iikoWaybill::find()->andWhere('order_id = :order_id', [':order_id' => $model->id]);
                } else {
                    $wmodel = null;
                }
                $order_id = $model->id;
                $query_string = Yii::$app->getRequest()->getQueryString();
                Yii::$app->session->set("query_string", $query_string);
                return Yii::$app->controller->renderPartial('_expand-row-details', ['model' => $wmodel, 'order_id' => $order_id, 'lic' => $lic]);
            },
            'headerOptions' => ['class' => 'kartik-sheet-style'],
            'expandOneOnly' => true,
        ]
    );
    ?>
    ЗАВЕРШЁННЫЕ ЗАКАЗЫ
</section>
<section class="content">
    <div class="catalog-index">
        <div class="box box-info">
            <div class="box-header with-border">
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
                <div class="panel-body">
                    <div class="box-body table-responsive no-padding orders-table">
                        <div class="row">
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <?php echo $form->field($searchModel, 'vendor_id')->widget(\kartik\select2\Select2::classname(), [
                                    'data' => $organization->getSuppliers(),
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'name' => 'sd',
                                    ],
                                    'id' => 'orgFilter',

                                ])->label(Yii::t('message', 'frontend.views.order.vendors', ['ru' => 'Поставщики']), ['class' => 'label', 'style' => 'color:#555']); ?>
                            </div>
                        </div>
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'pjax' => true,
                            'summary' => '',
                            'filterPosition' => false,
                            'columns' => $columns,
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
                <?php ActiveForm::end(); ?>
                <?php Pjax::end() ?>
            </div>
        </div>
    </div>
</section>

<?php
$url = Url::toRoute('waybill/send');
$js = <<< JS
    $(function () {
        $('.orders-table').on('click', '.export-waybill', function () {
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
