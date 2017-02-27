<?php
use yii\widgets\Breadcrumbs;
use common\models\Order;
use common\models\Organization;
use common\models\Profile;
use kartik\date\DatePicker;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Главная
        <small>Рабочий стол</small>
    </h1>
</section>
<section class="content">
    <div class="row hidden-xs">
        <div class="col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Объем продаж ваших клиентов</h3>
                    <div class="box-tools pull-right">
<?= Html::a("Аналитика", ["analytics/index"], ["class" => "btn btn-success btn-sm"]) ?>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart">
                        <canvas id="lineChart" style="height: 383px; width: 760px;" height="250" width="760"></canvas>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <div class="row">
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span>
                                <h5 class="description-header">35,210.43 руб.</h5>
                                <span class="description-text">Ваша прибыль</span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>
                                <h5 class="description-header">10,390.90 руб.</h5>
                                <span class="description-text">Роялти f-keeper</span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 20%</span>
                                <h5 class="description-header">350</h5>
                                <span class="description-text">Общее кол-во заказов</span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block">
                                <span class="description-percentage text-red"><i class="fa fa-caret-down"></i> 18%</span>
                                <h5 class="description-header">1200</h5>
                                <span class="description-text">Клиентов</span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                    </div>
                    <!-- /.row -->
                </div>
            </div>
            <!-- /.box -->
        </div>
        <div class="col-md-4">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Статистика</h3>

                    <div class="box-tools pull-right">
<?= Html::a("Аналитика", ["analytics/index"], ["class" => "btn btn-success btn-sm"]) ?>
                    </div>
                </div>
                <div class="box-body">
                    <div id="suppliers-list" data-pjax-container="" data-pjax-timeout="10000">            <div id="w0" class="table-responsive hide-resize" data-krajee-grid="kvGridInit_0dd787c6">
                            <div id="w0-container" class="table-responsive kv-grid-container"><table class="table no-margin kv-grid-table table-hover kv-table-wrap"><tbody>
                                        <tr data-key="0"><td data-col-seq="0">Кол-во поставщиков:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200</h5></td></tr>

                                        <tr data-key="0"><td data-col-seq="0">Кол-во ресторанов:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200</h5></td></tr>
                                        <tr data-key="0"><td data-col-seq="0">Долг на начало месяца:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200 руб.</h5></td></tr>
                                        <tr data-key="0"><td data-col-seq="0">Долг на конец месяца:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200 руб.</h5></td></tr>
                                        <tr data-key="0"><td data-col-seq="0">Начислено:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200 руб.</h5></td></tr>
                                        <tr data-key="0"><td data-col-seq="0">Перерасчет:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200 руб.</h5></td></tr>
                                        <tr data-key="0"><td data-col-seq="0">Баланс за день:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200 руб. / <span style="color: #dd4b39;">2800 руб.</span></h5></td></tr>
                                        <tr data-key="0"><td data-col-seq="0">Баланс за месяц:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200 руб. / <span style="color: #dd4b39;">2800 руб.</span></h5></td></tr>
                                        <tr data-key="0"><td data-col-seq="0">Баланс за год:</td><td data-col-seq="1" style="float:right;"> <h5 class="description-header" style="font-size: 16px;">1200 руб. / <span style="color: #dd4b39;">2800 руб.</span></h5></td></tr>
                                    </tbody></table></div>
                        </div> 
                    </div>            </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">История заказов</h3>
                    <div class="box-tools pull-right">
                        <?= Html::a("История заказов", ['app/orders'], ["class" => "btn btn-success btn-sm"]) ?>
                    </div>
                </div>
                <div class="box-body" style="display: block;">
                    <!--grid-->
                    <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'order-analytic-list',]); ?>
                    <?=
                    GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterPosition' => false,
                        'columns' => [
                            [
                                'attribute' => 'id',
                                'value' => 'id',
                                'label' => "№",
                            ],
                            [
                                'attribute' => 'clientName',
                                'value' => 'client.name',
                                'label' => 'Ресторан',
                            ],
                            [
                                'attribute' => 'vendorName',
                                'value' => 'vendor.name',
                                'label' => 'Поставщик',
                            ],
                            [
                                'attribute' => 'clientManager',
                                'value' => 'createdByProfile.full_name',
                                'label' => 'Заказ создал',
                            ],
                            [
                                'attribute' => 'vendorManager',
                                'value' => 'acceptedByProfile.full_name',
                                'label' => 'Заказ принял',
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'total_price',
                                'value' => function($data) {
                                    return (float)$data['total_price'] . '<i class="fa fa-fw fa-rub"></i>';
                                },
                                'label' => 'Сумма',
                                'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold'],        
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function($data) {
                                    $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => 'Дата создания',
                            ],
                            [
                                'attribute' => 'status',
                                'label'=>'Статус',
                                'format' => 'raw',
                                'value' => function($data) {
                                    switch ($data['status']) {
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
                                    return '<span class="status ' . $statusClass . '"><i class="fa fa-circle-thin"></i> ' . Order::statusText($data['status']) . '</span>';//fa fa-circle-thin
                                },
                            ]
                        ],
                        'tableOptions' => ['class' => 'table no-margin'],
                        'options' => ['class' => 'table-responsive'],
                        'bordered' => false,
                        'striped' => false,
                        'condensed' => false,
                        'responsive' => false,
                        'hover' => false,
                        'resizableColumns' => false,
                    ]);
                    ?> 
                    <?php Pjax::end(); ?>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>    
    </div>
</section>