<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use common\models\Order;
use common\models\Organization;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Breadcrumbs;
use dosamigos\chartjs\ChartJs;

$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        $(document).on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#turnoverStatForm").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 500);
            }
        });
    });
        ');
?>
<?php
Pjax::begin(['enablePushState' => false, 'id' => 'turnoverStat',]);
$form = ActiveForm::begin([
            'options' => [
                'data-pjax' => true,
                'id' => 'turnoverStatForm',
            ],
            'method' => 'post',
        ]);
?>

<div class="row" style="margin-top:50px">
    <div class="col-md-12"> 
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'franchise.views.anal.turnover', ['ru'=>'Оборот за период']) ?> &nbsp;<span class="text-bold text-success"><?= $total ?> <?= Yii::t('app', 'franchise.views.anal.rouble', ['ru'=>'руб']) ?></span></h3>
        </div>
        <div class="form-group" style="width: 350px;">
            <?=
            DatePicker::widget([
                'name' => 'date',
                'name2' => 'date2',
                'value' => $dateFilterFrom,
                'value2' => $dateFilterTo,
                'options' => ['placeholder' => Yii::t('app', 'franchise.views.anal.date_from', ['ru'=>'Начальная Дата']), 'id' => 'dateFrom'],
                'options2' => ['placeholder' => Yii::t('app', 'franchise.views.anal.date_to_two', ['ru'=>'Конечная дата']), 'id' => 'dateTo'],
                'separator' => '-',
                'type' => DatePicker::TYPE_RANGE,
                'pluginOptions' => [
                    'format' => 'dd.mm.yyyy', //'d M yyyy',//
                    'autoclose' => true,
                    'endDate' => "0d",
                ]
            ])
            ?>
        </div>
    </div>
    <div class="col-md-12">
            <?=
            ChartJs::widget([
                'type' => 'line',
                'options' => [
                    'height' => 400,
                    'width' => 1200,
                ],
                'data' => [
                    'labels' => $dayLabels,
                    'datasets' => [
                        [
                            'label' => Yii::t('app', 'franchise.views.anal.all_orders_two', ['ru'=>'Все заказы']),
                            'backgroundColor' => "rgba(54,140,191,.2)",
                            'borderColor' => "rgba(54,140,191,.8)",
                            'pointBackgroundColor' => "rgba(54,140,191,1)",
                            'pointBorderColor' => "rgba(54,140,191,1)",
                            'pointHoverBackgroundColor' => "rgba(54,140,191,1)",
                            'pointHoverBorderColor' => "rgba(54,140,191,1)",
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
<div class="row" style="margin-top:50px">
    <div class="col-md-12"> 
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'franchise.views.anal.full_turnover', ['ru'=>'Общий оборот по месяцам']) ?></h3>
        </div>
            <?=
            ChartJs::widget([
                'type' => 'bar',
                'options' => [
                    'height' => 400,
                    'width' => 1200,
                ],
                'data' => [
                    'labels' => $monthLabels,
                    'datasets' => [
                        [
                            'label' => Yii::t('app', 'franchise.views.anal.full_turnover_two', ['ru'=>'Общий оборот']),
                            'backgroundColor' => "rgba(54,140,191,.2)",
                            'borderColor' => "rgba(54,140,191,1)",
                            'data' => $totalSpent,
                        ],
                    ]
                ],
            ])
            ?>
    </div>
</div>
<div class="row" style="margin-top:50px">
    <div class="col-md-12"> 
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'franchise.views.anal.middle_turnover', ['ru'=>'Средний оборот в месяц (на 1 ресторан)']) ?></h3>
        </div>
            <?=
            ChartJs::widget([
                'type' => 'bar',
                'options' => [
                    'height' => 400,
                    'width' => 1200,
                ],
                'data' => [
                    'labels' => $monthLabels,
                    'datasets' => [
                        [
                            'label' => Yii::t('app', 'franchise.views.anal.middle_turnover_two', ['ru'=>'Средний оборот']),
                            'backgroundColor' => "rgba(54,140,191,.2)",
                            'borderColor' => "rgba(54,140,191,1)",
                            'data' => $averageSpent,
                        ],
                    ]
                ],
            ])
            ?>
    </div>
</div>
<div class="row" style="margin-top:50px">
    <div class="col-md-12"> 
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'franchise.views.anal.middle_check', ['ru'=>'Средний чек на 1 заказ']) ?></h3>
        </div>
            <?=
            ChartJs::widget([
                'type' => 'bar',
                'options' => [
                    'height' => 400,
                    'width' => 1200,
                ],
                'data' => [
                    'labels' => $monthLabels,
                    'datasets' => [
                        [
                            'label' => Yii::t('app', 'franchise.views.anal.middle_check_two', ['ru'=>'Средний чек']),
                            'backgroundColor' => "rgba(54,140,191,.2)",
                            'borderColor' => "rgba(54,140,191,1)",
                            'data' => $averageCheque,
                        ],
                    ]
                ],
            ])
            ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>