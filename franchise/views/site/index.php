<?php

$this->title = Yii::t('app', 'franchise.views.site.desktop', ['ru'=>'Рабочий стол']);

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
use dosamigos\chartjs\ChartJs;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.site.main', ['ru'=>'Главная']) ?>
        <small><?= Yii::t('app', 'franchise.views.site.desktop_two', ['ru'=>'Рабочий стол']) ?></small>
    </h1>
</section>
<section class="content" style="min-height: 40px;">
    <div class="row">
    <div class="col-lg-2 col-md-2 col-sm-2">
        <?= Html::label(Yii::t('message', 'frontend.views.client.anal.currency', ['ru'=>'Валюта']), null, ['class' => 'label', 'style' => 'color:#555']) ?>
        <?=
        Html::dropDownList('filter_currency', $currencyId, $currencyList, ['class' => 'form-control', 'id' => 'filter_currency'])
        ?>
    </div>
    </div>
</section>
<section class="content">
    <div class="row hidden-xs">
        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'analytics-list',]); ?>
        <div class="col-md-8">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'franchise.views.site.sell_value', ['ru'=>'Объем продаж ваших клиентов за последние 30 дней']) ?></h3>
                    <div class="box-tools pull-right">
                        <?= Html::a(Yii::t('app', 'franchise.views.site.anal', ['ru'=>"Аналитика"]), ["analytics/index"], ["class" => "btn btn-success btn-sm"]) ?>
                    </div>
                </div>
                <div class="box-body">
                    <div class="chart">
                        <?=
                        ChartJs::widget([
                            'type' => 'line',
                            'options' => [
                                'height' => 396,
                                'width' => 1055,
                               // 'maintainAspectRatio' => false,
                            ],
                            'data' => [
                                'labels' => $dayLabels,
                                'datasets' => [
                                    [
                                        'label' => Yii::t('app', 'franchise.views.site.all_orders', ['ru'=>'Все заказы']),
                                        'backgroundColor' => "rgba(126,188,89,0.2)",
                                        'borderColor' => "rgba(126,188,89,1)",
                                        'pointBackgroundColor' => "rgba(126,188,89,1)",
                                        'pointBorderColor' => "#7EBC59",
                                        'pointHoverBackgroundColor' => "#7EBC59",
                                        'pointHoverBorderColor' => "rgba(126,188,89,1)",
                                        'data' => $dayTurnover,
                                        'spanGaps' => true,
                                        'borderJoinStyle' => 'miter',
                                        'fill' => false,
                                    ],
                                ]
                            ],
                        ])
                        ?>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <div class="row">
                        <?php if(isset($franchiseeType->share)): ?>
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green"><?= $franchiseeType->share ?>%</span>
                                <h5 class="description-header"><?= number_format($vendorsStats30['turnoverCut'] * $franchiseeType->share / 100, 2, '.', ' ') ?> <?= $iso_code ?></h5>
                                <span class="description-text"><?= Yii::t('app', 'franchise.views.site.your_income', ['ru'=>'Ваша прибыль']) ?></span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-yellow"><?= 100 - $franchiseeType->share ?>%</span>
                                <h5 class="description-header"><?= number_format($vendorsStats30['turnoverCut'] * (100 - $franchiseeType->share) / 100, 2, '.', ' ') ?> <?= $iso_code ?></h5>
                                <span class="description-text"><?= Yii::t('app', 'franchise.views.site.royalty', ['ru'=>'Роялти MixCart']) ?></span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                        <?php endif; ?>
                        <!-- /.col -->
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-green">&nbsp;
                                    <!--<i class="fa fa-caret-up"></i> 20%-->
                                </span>
                                <h5 class="description-header"><?= $dataProvider->getTotalCount() ?></h5>
                                <span class="description-text"><?= Yii::t('app', 'franchise.views.site.order_quan', ['ru'=>'Общее кол-во заказов']) ?></span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                        <!-- /.col -->
                        <div class="col-sm-3 col-xs-6">
                            <div class="description-block">
                                <span class="description-percentage text-red">&nbsp;
                                    <!--<i class="fa fa-caret-down"></i> 18%-->
                                </span>
                                <h5 class="description-header"><?= $total30Count ?></h5>
                                <span class="description-text"><?= Yii::t('app', 'franchise.views.site.clients', ['ru'=>'Клиентов']) ?></span>
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
                    <h3 class="box-title"><?= Yii::t('app', 'franchise.views.site.stat', ['ru'=>'Статистика']) ?></h3>
                    <div class="box-tools pull-right">
                        <a class="btn btn-success btn-sm" href="<?= Url::to(['finance/index']) ?>"><?= Yii::t('app', 'franchise.views.site.fin_anal', ['ru'=>'Финансовая аналитика']) ?></a>          </div>
                </div>

                <div class="box-body">
                    <div class="home-pay-chek">
                        <table class="pay-table" width="100%">
                            <tbody><tr>
                                    <th><?= Yii::t('app', 'franchise.views.site.custom_anal', ['ru'=>'Общая финансовая аналитика']) ?></th>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.site.vendors_to_me', ['ru'=>'Поставщики Мне:']) ?></td>
                                    <td style="text-align: right; font-size: 18px; color: rgba(51, 54, 59, 0.8); font-weight: bold;"><?= number_format($vendorsStats['turnoverCut'], 2, '.', ' ') ?> <?= $iso_code ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.site.mix_me', ['ru'=>'MixCart Мне:']) ?></td>
                                    <td style="text-align: right; font-size: 18px; color: #7EBC59; font-weight: bold;"><span style="font-size: 14px;"><i class="fa fa-fw fa-plus"></i></span> 0 <?= $iso_code ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.site.me_to_mix', ['ru'=>"Я MixCart\'у:"]) ?></td>
                                    <td style="text-align: right; font-size: 18px; color: #FB3640; font-weight: bold;"><span style="font-size: 14px;"><i class="fa fa-fw fa-minus"></i></span> <?= number_format($vendorsStats['turnoverCut'] * (100 - $franchiseeType->share) / 100, 2, '.', ' ') ?> <?= $iso_code ?></td>
                                </tr>
                                <tr style="border-top: 1px dotted rgba(51, 54, 59, 0.1);">
                                    <td style="text-align: left; font-weight: bold;"><?= Yii::t('app', 'franchise.views.site.total_income', ['ru'=>'Итого заработано:']) ?></td>
                                    <td style="text-align: right; font-size: 22px; font-weight: bold;"><?= number_format($vendorsStats['turnoverCut'] - ($vendorsStats['turnoverCut'] * (100 - $franchiseeType->share) / 100), 2, '.', ' ') ?> <?= $iso_code ?></td>
                                </tr>
                                <tr>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th><?= Yii::t('app', 'franchise.views.site.registered', ['ru'=>'Зарегистрировано']) ?></th>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.site.vendors', ['ru'=>'Поставщиков:']) ?></td>
                                    <td style="text-align: right; font-size: 18px; font-weight: bold;"> <?= $vendorsCount ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.site.rest', ['ru'=>'Ресторанов:']) ?></td>
                                    <td style="text-align: right; font-size: 18px; font-weight: bold;"> <?= $clientsCount ?></td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <th><?= Yii::t('app', 'franchise.views.site.orders_and_turnover', ['ru'=>'Заказы и оборот']) ?></th>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.site.orders', ['ru'=>'Заказов:']) ?></td>
                                    <td style="text-align: right; font-size: 18px; font-weight: bold;"> <?= $dataProvider->getTotalCount() ?></td>
                                </tr>
                                <tr>
                                    <td style="text-align: left;"><?= Yii::t('app', 'franchise.views.site.turnover', ['ru'=>'Оборот:']) ?></td>
                                    <td style="text-align: right; font-size: 18px; font-weight: bold;"> <?= number_format($totalIncome, 2, '.', ' ') ?> <?= $iso_code ?></td>
                                </tr>
                            </tbody></table>
                    </div>         
                </div>
                <!-- /.box-body -->
            </div>            <!-- /.box -->
        </div>
        <?php Pjax::end(); ?>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'franchise.views.site.orders_history', ['ru'=>'История заказов']) ?></h3>
                    <div class="box-tools pull-right">
                        <?= Html::a(Yii::t('app', 'franchise.views.site.orders_history_two', ['ru'=>"История заказов"]), ['site/orders'], ["class" => "btn btn-success btn-sm"]) ?>
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
                                'label' => Yii::t('app', 'franchise.views.site.rest_two', ['ru'=>'Ресторан']),
                            ],
                            [
                                'attribute' => 'vendorName',
                                'value' => 'vendor.name',
                                'label' => Yii::t('app', 'franchise.views.site.vendor', ['ru'=>'Поставщик']),
                            ],
                            [
                                'attribute' => 'clientManager',
                                'value' => 'createdByProfile.full_name',
                                'label' => Yii::t('app', 'franchise.views.site.created', ['ru'=>'Заказ создал']),
                            ],
                            [
                                'attribute' => 'vendorManager',
                                'value' => 'acceptedByProfile.full_name',
                                'label' => Yii::t('app', 'franchise.views.site.accepted', ['ru'=>'Заказ принял']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'total_price',
                                'value' => function($data) {
                                    return (float) $data['total_price'] . ' ' . $data->currency->symbol;
                                },
                                'label' => Yii::t('app', 'franchise.views.site.sum', ['ru'=>'Сумма']),
                                'contentOptions' => ['style' => 'vertical-align:middle;font-weight:bold'],
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function($data) {
                                    $date = Yii::$app->formatter->asDatetime($data['created_at'], "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => Yii::t('app', 'franchise.views.site.creating_date', ['ru'=>'Дата создания']),
                            ],
                            [
                                'attribute' => 'status',
                                'label' => Yii::t('app', 'franchise.views.site.status', ['ru'=>'Статус']),
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
                                        default: $statusClass='';
                                    }
                                    return '<span class="status ' . $statusClass . '"><i class="fa fa-circle-thin"></i> ' . Order::statusText($data['status']) . '</span>'; //fa fa-circle-thin
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

<?php
$analyticsUrl = Url::to(['/']);

$customJs = <<< JS

$("#filter_currency").on("change", function () {
$("#filter_currency").attr('disabled','disabled')      
       
var filter_currency =  $("#filter_currency").val();

    $.pjax({
     type: 'GET',
     push: false,
     timeout: 10000,
     url: "$analyticsUrl",
     container: "#analytics-list",
     data: {
         filter_currency: filter_currency
           }
   }).done(function() { $("#filter_currency").removeAttr('disabled') });
});

JS;

$this->registerJs($customJs, View::POS_READY);
