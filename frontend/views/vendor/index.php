<?php

use yii\widgets\Breadcrumbs;
use common\models\Order;
use common\models\Organization;
use kartik\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use dosamigos\chartjs\ChartJs;

$this->title = Yii::t('message', 'frontend.views.vendor.desktop', ['ru'=>'Рабочий стол']);

$this->registerCss('
    @media (max-width: 1320px){
       th{
        min-width:135px;
        }
    }
    .pac-container {
        z-index: 1100;
    }
    ');
if ($organization->step == Organization::STEP_SET_INFO) {
    \frontend\assets\AuthAsset::register($this);
    \frontend\assets\GoogleMapsAsset::register($this);
    echo $this->render("dashboard/_wizard", compact("profile", "organization"));
}
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('message', 'frontend.views.vendor.main', ['ru'=>'Главная']) ?>
        <small><?= Yii::t('message', 'frontend.views.vendor.desk', ['ru'=>'Рабочий стол']) ?></small>
    </h1>
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb',
        ],
    ])
    ?>
</section>
<section class="content">
    <div class="row">
        <div class="col-md-8  hidden-xs">
            <!-- AREA CHART -->
            <div class="box box-info" style="min-height: 286px;">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('message', 'frontend.views.vendor.sell_value', ['ru'=>'Объем продаж']) ?></h3><br><small><?= Yii::t('message', 'frontend.views.vendor.monthly', ['ru'=>'За месяц']) ?></small>
                    <div class="box-tools pull-right">
                        <?= Html::a(Yii::t('message', 'frontend.views.vendor.anal', ['ru'=>'Аналитика']), ['vendor/analytics'], ['class' => 'btn btn-success btn-sm']) ?>
                    </div>
                </div>
                <div class="box-body" style="display: block;">
                    <div style="position:relative;height:100%;width:100%;min-height: 286px;">
                        <?=
                        ChartJs::widget([
                            'type' => 'line',
                            'options' => [
                                'maintainAspectRatio' => false,
                                'responsive' => true,
                                'height' => '100%',
                            ],
                            'data' => [
                                'labels' => $arr_create_at,
                                'datasets' => [
                                    [
                                        'label' => Yii::t('message', 'frontend.views.vendor.sell_value_two', ['ru'=>"Объем продаж"]),
                                        'fillColor' => "rgba(0,0,0,.05)",
                                        'borderColor' => "#84bf76",
                                        'data' => $arr_price,
                                    ]
                                ],
                            ],
                        ]);
                        ?>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <div class="col-md-4">
            <!-- AREA CHART -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('message', 'frontend.views.vendor.stat', ['ru'=>'Статистика']) ?></h3>
                </div>
                <div class="box-body" style="display: block;">
                    <!--img style="width: 100%;" src="http://www.imageup.ru/img171/2601902/snimok-ehkrana-2016-11-16-v-154356-2.png"-->

                    <div class="panel-body" style="min-height: 307px;height:100%;">
                        <div>
                            <small class="stat-label text-bold"><?= Yii::t('message', 'frontend.views.vendor.curr_month', ['ru'=>'Текущий месяц']) ?></small>
                            <h2 class="m-xs text-success font-bold  text-bold">
                                <?= $stats['curMonth'] ? (float) $stats['curMonth'] . '<i class="fa fa-fw fa-rub"></i>' : 0 . '<i class="fa fa-fw fa-rub"></i>'; ?>
                            </h2>
                        </div>
                        <?php
                        $months = array(1 => Yii::t('message', 'frontend.views.vendor.jan', ['ru'=>'Январь']), 2 => Yii::t('message', 'frontend.views.vendor.feb', ['ru'=>'Февраль']), 3 => Yii::t('message', 'frontend.views.vendor.march', ['ru'=>'Март']), 4 => Yii::t('message', 'frontend.views.vendor.apr', ['ru'=>'Апрель']),
                            5 => Yii::t('message', 'frontend.views.vendor.may', ['ru'=>'Май']), 6 => Yii::t('message', 'frontend.views.vendor.june', ['ru'=>'Июнь']), 7 => Yii::t('message', 'frontend.views.vendor.july', ['ru'=>'Июль']), 8 => Yii::t('message', 'frontend.views.vendor.aug', ['ru'=>'Август']),
                            9 => Yii::t('message', 'frontend.views.vendor.sept', ['ru'=>'Сентябрь']), 10 => Yii::t('message', 'frontend.views.vendor.okt', ['ru'=>'Октябрь']), 11 => Yii::t('message', 'frontend.views.vendor.nov', ['ru'=>'Ноябрь']), 12 => Yii::t('message', 'frontend.views.vendor.dec', ['ru'=>'Декабрь']));
                        ?>
                        <div class="row">
                            <div class="col-xs-6">
                                <small class="stat-label text-bold"><?= Yii::t('message', 'frontend.views.vendor.today', ['ru'=>'Сегодня']) ?></small>
                                <h4 class="text-success">
                                    <?= $stats['curDay'] ? (float) $stats['curDay'] . '<i class="fa fa-fw fa-rub"></i>' : 0 . '<i class="fa fa-fw fa-rub"></i>'; ?>
                                </h4> 
                            </div>
                            <div class="col-xs-6">
                                <small class="stat-label text-bold"><?= Yii::t('message', 'frontend.views.vendor.curr_week', ['ru'=>'Текущая неделя']) ?></small>
                                <h4 class="text-success">
                                    <?= $stats['curWeek'] ? (float) $stats['curWeek'] . '<i class="fa fa-fw fa-rub"></i>' : 0 . '<i class="fa fa-fw fa-rub"></i>'; ?>
                                </h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-6">
                                <small class="stat-label text-bold"><?= $months[date('n', strtotime(" -1 months"))]; ?></small>
                                <h4 class="text-success">
                                    <?= $stats['lastMonth'] ? (float) $stats['lastMonth'] . '<i class="fa fa-fw fa-rub"></i>' : 0 . '<i class="fa fa-fw fa-rub"></i>'; ?>
                                </h4>
                            </div>
                            <div class="col-xs-6">
                                <small class="stat-label text-bold"><?= $months[date('n', strtotime(" -2 months"))]; ?></small>
                                <h4 class="text-success">
                                    <?= $stats['TwoLastMonth'] ? (float) $stats['TwoLastMonth'] . '<i class="fa fa-fw fa-rub"></i>' : 0 . '<i class="fa fa-fw fa-rub"></i>'; ?>
                                </h4>
                            </div>
                        </div>   
                    </div> 
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <div class="col-md-12">
            <!-- pie CHART -->
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('message', 'frontend.views.vendor.history', ['ru'=>'История заказов']) ?></h3>

                    <div class="box-tools pull-right">
                        <?= Html::a(Yii::t('message', 'frontend.views.vendor.history_two', ['ru'=>'История заказов']), ['order/index'], ['class' => 'btn btn-success btn-sm']) ?>
                        </button>
                    </div>
                </div>
                <div class="box-body" style="display: block;">
                    <div>
                        <?php
                        $columns = [
                            [
                                'attribute' => 'id',
                                'value' => 'id',
                                'label' => '№',
                            ],
                            [
                                'attribute' => 'client.name',
                                'value' => 'client.name',
                                'label' => Yii::t('message', 'frontend.views.vendor.rest', ['ru'=>'Ресторан']),
                            ],
                            [
                                'attribute' => 'createdByProfile.full_name',
                                'value' => 'createdByProfile.full_name',
                                'label' => Yii::t('message', 'frontend.views.vendor.ord_cr', ['ru'=>'Заказ создал']),
                            ],
                            [
                                'attribute' => 'acceptedByProfile.full_name',
                                'value' => 'acceptedByProfile.full_name',
                                'label' => Yii::t('message', 'frontend.views.vendor.ord_accept', ['ru'=>'Заказ принял']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'total_price',
                                'value' => function($data) {
                                    return "<b>$data->total_price</b> " . $data->currency->symbol;
                                },
                                'label' => Yii::t('message', 'frontend.views.vendor.summ', ['ru'=>'Сумма']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'created_at',
                                'value' => function($data) {
                                    $date = Yii::$app->formatter->asDatetime($data->created_at, "php:j M Y");
                                    return '<i class="fa fa-fw fa-calendar""></i> ' . $date;
                                },
                                'label' => Yii::t('message', 'frontend.views.vendor.cr_date', ['ru'=>'Дата создания']),
                            ],
                            [
                                'format' => 'raw',
                                'attribute' => 'status',
                                'value' => function($data) {
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
                                    return '<span class="status ' . $statusClass . '">' . Order::statusText($data->status) . '</span>'; //<i class="fa fa-circle-thin"></i> 
                                },
                                'label' => Yii::t('message', 'frontend.views.vendor.status', ['ru'=>'Статус']),
                            ],
                        ];
                        ?>
                        <?php Pjax::begin(['enablePushState' => false, 'timeout' => 10000, 'id' => 'order-analytic-list',]); ?>
                        <?=
                        GridView::widget([
                            'dataProvider' => $dataProvider,
                            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
                            'filterPosition' => false,
                            'columns' => $columns,
                            'tableOptions' => ['class' => 'table no-margin'],
                            'options' => ['class' => 'table-responsive'],
                            'bordered' => false,
                            'striped' => false,
                            'condensed' => false,
                            'responsive' => false,
                            'hover' => true,
                            'resizableColumns' => false,
                            'rowOptions' => function ($model, $key, $index, $grid) {
                                return ['id' => $model['id'], 'style' => 'cursor:pointer', 'onclick' => 'window.location.replace("' . Url::to(['order/view', 'id' => $model['id']]) . '");'];
                            },
                        ]);
                        ?> 
                        <?php Pjax::end(); ?>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>
</section>
