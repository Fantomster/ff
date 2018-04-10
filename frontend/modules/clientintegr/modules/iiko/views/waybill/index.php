<?php

use yii\widgets\Breadcrumbs;
use common\models\Order;
use kartik\grid\GridView;
use yii\helpers\Url;

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
                                    'order_code',
                                    'value' => function($data){
                                        return $data->order_code ?? $data->id;
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
                                    'value' => function ($model, $key, $index, $column) {
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