<?php
use yii\helpers\Url;
use yii\grid\GridView;
use common\models\Order;
use common\models\Organization;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

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
        $(".box-body").on("click", "td", function (e) {
            var id = $(this).closest("tr").data("id");
            if(e.target == this)
                location.href = "' . Url::to(['order/view']) . '&id=" + id;
        });
    });
        ');
$this->registerCss("
    tr:hover{cursor: pointer;}
        ");
?>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Заказы</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">

        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-new-count">
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
            <div class="info-box bg-fulfilled-count">
                <div class="info-box-content">
                    <span class="info-box-text">Завершено</span>
                    <span class="info-box-number"><?= $fulfilledCount ?></span>
                </div>
            </div>    
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
            <div class="info-box bg-total-price">
                <div class="info-box-content">
                    <span class="info-box-text">Всего выполнено на сумму</span>
                    <span class="info-box-number"><?= $totalPrice ?> руб</span>
                </div>
            </div>    
        </div>
                <div style="clear: both;">
        </div>
        <?php Pjax::begin(['enablePushState' => false, 'id' => 'order-list',]); 
$form = ActiveForm::begin([
            'options' => [
                'data-pjax' => true,
                'id' => 'search-form',
                'class' => "navbar-form",
                'role' => 'search',
            ],
            'enableClientValidation' => false,
            'method' => 'get',
        ]);
?>
        <?= $form->field($searchModel, 'status')->dropDownList(['0' => 'Все', '1' => 'Новый', '2' => 'Отменен', '3' => 'Выполняется', '4' => 'Завершен'], ['id' => 'statusFilter']) ?>
        <?php if ($organization->type_id == Organization::TYPE_RESTAURANT) {
            echo $form->field($searchModel, 'vendor_id')->dropDownList($organization->getSuppliers('', true), ['id' => 'orgFilter']);
        } else {
            echo $form->field($searchModel, 'client_id')->dropDownList($organization->getClients(), ['id' => 'orgFilter']);
        } ?>
        <div class="form-group" style="width: 300px; height: 44px;">
        <?= DatePicker::widget([
                                                        'model' => $searchModel,
                                                        'attribute' => 'date_from',
                                                        'attribute2' => 'date_to',
                                                        'options' => ['placeholder' => 'Start date', 'id' => 'dateFrom'],
                                                        'options2' => ['placeholder' => 'End date', 'id' => 'dateTo'],
                                                        'type' => DatePicker::TYPE_RANGE,
                                                        
                                                        'pluginOptions' => [
                                                            'format' => 'dd.mm.yyyy',
                                                            'autoclose' => true,
                                                            'endDate' => "0d",
                                                        ]
                                                    ]) ?>
        </div>
        <?php ActiveForm::end(); ?>
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
                    'label' => '#',
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
        <?php Pjax::end() ?>
        <!-- /.table-responsive -->
    </div>
    <!-- /.box-body -->
    <div class="box-footer clearfix">
    </div>
</div>
