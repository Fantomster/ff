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
                $("#regStatForm").submit();
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
Pjax::begin(['enablePushState' => false, 'id' => 'regStat',]);
$form = ActiveForm::begin([
            'options' => [
                'data-pjax' => true,
                'id' => 'regStatForm',
            ],
            'method' => 'post',
        ]);
?>
    <div class="row">
        <div class="col-md-12">
            <h3>Зарегистрировано</h3>
            <div class="row">
                <div class="col-md-4 col-sm-12 text-center">
                    <h4>За все время (<?= $allTimeCount ?>)</h4>
                    <?=
                    ChartJs::widget([
                        'type' => 'pie',
                        'options' => [
                            'height' => 200,
                            'width' => 200,
                            'legend' => [
                                'display' => false,
                            ],
                        ],
                        'data' => [
                            'labels' => ['Рестораны', 'Поставщики'],
                            'datasets' => [
                                [
                                    'data' => $allTime,
                                    'backgroundColor' => ["#b342f4", "#00FF00"],
                                    'hoverBackgroundColor' => ["#b342f4", "#00FF00"],
                                ]
                            ],
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-4 col-sm-12 text-center">
                    <h4>За текущий месяц (<?= $thisMonthCount ?>)</h4>
                    <?=
                    ChartJs::widget([
                        'type' => 'pie',
                        'options' => [
                            'height' => 200,
                            'width' => 200,
                        ],
                        'data' => [
                            'labels' => ['Рестораны', 'Поставщики'],
                            'datasets' => [
                                [
                                    'data' => $thisMonth,
                                    'backgroundColor' => ["#b342f4", "#00FF00"],
                                    'hoverBackgroundColor' => ["#b342f4", "#00FF00"],
                                ]
                            ],
                        ],
                    ]);
                    ?>
                </div>
                <div class="col-md-4 col-sm-12 text-center">
                    <h4>Сегодня (<?= $todayCount ?>)</h4>
                    <?=
                    ChartJs::widget([
                        'type' => 'pie',
                        'options' => [
                            'height' => 200,
                            'width' => 200,
                        ],
                        'data' => [
                            'labels' => ['Рестораны', 'Поставщики'],
                            'datasets' => [
                                [
                                    'data' => $todayArr,
                                    'backgroundColor' => ["#b342f4", "#00FF00"],
                                    'hoverBackgroundColor' => ["#b342f4", "#00FF00"],
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
        <div class="col-md-12">
            <h3>Зарегистрировано в период</h3>
            <div class="form-group" style="width: 350px;">
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
            <span class="text-bold"><?= $total ?></span>
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
                            'label' => 'Все организации',
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
                            'label' => 'Рестораны',
                            'backgroundColor' => "rgba(179, 66, 244,0.2)",
                            'borderColor' => "rgba(179, 66, 244,1)",
                            'pointBackgroundColor' => "rgba(179, 66, 244,1)",
                            'pointBorderColor' => "#f00",
                            'pointHoverBackgroundColor' => "#f00",
                            'pointHoverBorderColor' => "rgba(179, 66, 244,1)",
                            'data' => $clients,
                            'spanGaps' => true,
                            'borderJoinStyle' => 'miter',
                            'fill' => false,
                        ],
                        [
                            'label' => 'Поставщики',
                            'backgroundColor' => "rgba(0,255,0,0.2)",
                            'borderColor' => "rgba(0,255,0,1)",
                            'pointBackgroundColor' => "rgba(0,255,0,1)",
                            'pointBorderColor' => "#0f0",
                            'pointHoverBackgroundColor' => "#0f0",
                            'pointHoverBorderColor' => "rgba(0,255,0,1)",
                            'data' => $vendors,
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