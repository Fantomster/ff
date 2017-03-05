<?php

use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
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

            <div class="row" style="margin-top:50px">
                <div class="col-md-12"> 
                    <div class="row">
                        <div class="col-md-4 col-sm-12 text-center">
                            <h4>За все время (<?= $totalCount ?>)</h4>
                            <?=
                            ChartJs::widget([
                                'type' => 'pie',
                                'options' => [
                                    'height' => 200,
                                    'width' => 200,
                                    'responsive' => false,
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
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center"> 
                    <h3 class="box-title" style="margin-left:15px;">Заказов за период <span class="text-bold text-primary">(<?= $total ?>)</span></h3>
                    <div class="form-group" style="width: 350px;margin:0 auto;margin-bottom:15px">
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
                    'width' => 1200,
                ],
                'data' => [
                    'labels' => $dayLabels,
                    'datasets' => [
                        [
                            'label' => 'Все заказы',
                            'backgroundColor' => "rgba(54,140,191,.2)",
                            'borderColor' => "rgba(54,140,191,.8)",
                            'pointBackgroundColor' => "rgba(54,140,191,1)",
                            'pointBorderColor' => "rgba(54,140,191,1)",
                            'pointHoverBackgroundColor' => "rgba(54,140,191,1)",
                            'pointHoverBorderColor' => "rgba(54,140,191,1)",
                            'data' => $dayStats,
                            'spanGaps' => true,
                            'borderJoinStyle' => 'miter',
                            'fill' => false,
                        ],
                        [
                            'label' => 'Первые заказы',
                            'backgroundColor' => "rgba(126,188,89,.2)",
                            'borderColor' => "rgba(126,188,89,.8)",
                            'pointBackgroundColor' => "rgba(126,188,89,.1)",
                            'pointBorderColor' => "rgba(126,188,89,1)",
                            'pointHoverBackgroundColor' => "rgba(126,188,89,1)",
                            'pointHoverBorderColor' => "rgba(126,188,89,1)",
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

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>