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
                $("#orderStatForm").submit();
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
Pjax::begin(['enablePushState' => false, 'id' => 'orderStat',]);
$form = ActiveForm::begin([
            'options' => [
                'data-pjax' => true,
                'id' => 'orderStatForm',
            ],
            'method' => 'post',
        ]);
?>

<div class="row">
    <div class="col-md-12 text-center">
        <h3>Заказы</h3>
    </div>
    <div class="col-md-4 col-sm-12 text-center">
        <h4>За все время (<?= $totalCount ?>)</h4>
        <?=
        ChartJs::widget([
            'type' => 'pie',
            'options' => [
                'height' => 200,
                'width' => 200,
            ],
            'data' => [
                'labels' => $labelsTotal,
                'datasets' => [
                    [
                        'data' => array_values($ordersStat),
                        'backgroundColor' => $colorsTotal,
                        'hoverBackgroundColor' => $colorsTotal,
                    ]
                ],
            ],
        ]);
        ?>
    </div>
    <div class="col-md-4 col-sm-12 text-center">
        <h4>За текущий месяц (<?= $totalCountThisMonth ?>)</h4>
        <?=
        ChartJs::widget([
            'type' => 'pie',
            'options' => [
                'height' => 200,
                'width' => 200,
            ],
            'data' => [
                'labels' => $labelsTotal,
                'datasets' => [
                    [
                        'data' => array_values($ordersStatThisMonth),
                        'backgroundColor' => $colorsTotal,
                        'hoverBackgroundColor' => $colorsTotal,
                    ]
                ],
            ],
        ]);
        ?>
    </div>
    <div class="col-md-4 col-sm-12 text-center">
        <h4>Сегодня (<?= $totalCountThisDay ?>)</h4>
        <?=
        ChartJs::widget([
            'type' => 'pie',
            'options' => [
                'height' => 200,
                'width' => 200,
            ],
            'data' => [
                'labels' => $labelsTotal,
                'datasets' => [
                    [
                        'data' => array_values($ordersStatThisDay),
                        'backgroundColor' => $colorsTotal,
                        'hoverBackgroundColor' => $colorsTotal,
                    ]
                ],
            ],
        ]);
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-center"> 
        <h3>Заказов в период </h3>
        <div class="form-group" style="width: 350px; margin: 0 auto; padding-bottom: 10px;">
            <?=
            DatePicker::widget([
                'name' => 'date',
                'name2' => 'date2',
                'value' => $dateFilterFrom,
                'value2' => $dateFilterTo,
                'options' => ['placeholder' => 'Начальная Дата', 'id' => 'dateFrom'],
                'options2' => ['placeholder' => 'Конечная дата', 'id' => 'dateTo'],
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
                'width' => 800,
            ],
            'data' => [
                'labels' => $dayLabels,
                'datasets' => [
                    [
                        'label' => 'Все заказы',
                        'backgroundColor' => "rgba(0,0,255,0.2)",
                        'borderColor' => "rgba(0,0,255,1)",
                        'pointBackgroundColor' => "rgba(0,0,255,1)",
                        'pointBorderColor' => "#00f",
                        'pointHoverBackgroundColor' => "#00f",
                        'pointHoverBorderColor' => "rgba(0,0,255,1)",
                        'data' => $dayStats,
                        'spanGaps' => true,
                        'borderJoinStyle' => 'miter',
                        'fill' => false,
                    ],
                    [
                        'label' => 'Первые заказы',
                        'backgroundColor' => "rgba(179, 66, 244,0.2)",
                        'borderColor' => "rgba(179, 66, 244,1)",
                        'pointBackgroundColor' => "rgba(179, 66, 244,1)",
                        'pointBorderColor' => "#f00",
                        'pointHoverBackgroundColor' => "#f00",
                        'pointHoverBorderColor' => "rgba(179, 66, 244,1)",
                        'data' => $firstDayStats,
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
<div class="row">
    <div class="col-md-12 text-center">
        <h3>Средний оборот в месяц</h3>
        <?=
        ChartJs::widget([
            'type' => 'bar',
            'options' => [
                'height' => 400,
                'width' => 800,
            ],
            'data' => [
                'labels' => $monthLabels,
                'datasets' => [
                    [
                        'label' => 'Средний оборот',
                        'backgroundColor' => "rgba(0,0,255,0.2)",
                        'borderColor' => "rgba(0,0,255,1)",
                        'data' => $averageSpent,
                    ],
                ]
            ],
        ])
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-center">
        <h3>Средний чек в месяц</h3>
        <?=
        ChartJs::widget([
            'type' => 'bar',
            'options' => [
                'height' => 400,
                'width' => 800,
            ],
            'data' => [
                'labels' => $monthLabels,
                'datasets' => [
                    [
                        'label' => 'Средний чек',
                        'backgroundColor' => "rgba(0,0,255,0.2)",
                        'borderColor' => "rgba(0,0,255,1)",
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