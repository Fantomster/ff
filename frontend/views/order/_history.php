<?php

use yii\grid\GridView;
use common\models\Order;
use common\models\Organization;
?>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Заказы</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <div class="info-box-content">
                    <span class="info-box-text">Новые</span>
                    <span class="info-box-number"><?= $newCount ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-yellow">
                <div class="info-box-content">
                    <span class="info-box-text">Выполняются</span>
                    <span class="info-box-number"><?= $processingCount ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-green">
                <div class="info-box-content">
                    <span class="info-box-text">Завершено</span>
                    <span class="info-box-number"><?= $fulfilledCount ?></span>
                </div>
            </div>    
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-aqua">
                <div class="info-box-content">
                    <span class="info-box-text">Всего выполнено на сумму</span>
                    <span class="info-box-number"><?= $totalPrice ?> руб</span>
                </div>
            </div>    
        </div>
                <div style="clear: both;">
        </div>
        <?=
        GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterPosition' => false,
            'summary' => '',
            'tableOptions' => ['class' => 'table no-margin table-hover'],
            'options' => ['class' => 'table-responsive'],
            'columns' => [
                [
                    'attribute' => 'id',
                    'value' => 'id',
                    'label' => 'Номер заказа',
                ],
                $organization->type_id == Organization::TYPE_RESTAURANT ? [
                    'attribute' => 'vendor.name',
                    'value' => 'vendor.name',
                    'label' => 'Поставщик',
                        ] : [
                    'attribute' => 'client.name',
                    'value' => 'client.name',
                    'label' => 'Ресторан',
                        ],
                [
                    'attribute' => 'createdBy.profile.full_name',
                    'value' => 'createdBy.profile.full_name',
                    'label' => 'Заказ создал',
                ],
                [
                    'attribute' => 'acceptedBy.profile.full_name',
                    'value' => 'acceptedBy.profile.full_name',
                    'label' => 'Заказ принял',
                ],
                [
                    'attribute' => 'total_price',
                    'value' => 'total_price',
                    'label' => 'Сумма',
                ],
                [
                    'attribute' => 'created_at',
                    'value' => 'created_at',
                    'label' => 'Дата создания',
                ],
                [
                    'format' => 'raw',
                    'attribute' => 'status',
                    'value' => function($data) {
                        switch ($data->status) {
                            case Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR:
                            case Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT:
                                $statusClass = 'label-warning';
                                break;
                            case Order::STATUS_PROCESSING:
                                $statusClass = 'label-info';
                                break;
                            case Order::STATUS_DONE:
                                $statusClass = 'label-success';
                                break;
                            case Order::STATUS_REJECTED:
                            case Order::STATUS_CANCELLED:
                                $statusClass = 'label-danger';
                                break;
                        }
                        return '<span class="label ' . $statusClass . '">' . Order::statusText($data->status) . '</span>';
                    },
                    'label' => 'Статус',
                ],
            ],
            'rowOptions' => function ($model, $key, $index, $grid) {
        return ['data-id' => $model->id];
    },
        ]);
        ?>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
    </div>
</div>