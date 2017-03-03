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
                                    'backgroundColor' => ["#7EBC59", "#368CBF"],
                                    'hoverBackgroundColor' => ["#7EBC59", "#368CBF"],
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
                                    'backgroundColor' => ["#7EBC59", "#368CBF"],
                                    'hoverBackgroundColor' => ["#7EBC59", "#368CBF"],
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
                                    'backgroundColor' => ["#7EBC59", "#368CBF"],
                                    'hoverBackgroundColor' => ["#7EBC59", "#368CBF"],
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
                            'backgroundColor' => "rgba(51,54,59,.2)",
                            'borderColor' => "rgba(51,54,59,.8)",
                            'pointBackgroundColor' => "rgba(51,54,59,1)",
                            'pointBorderColor' => "rgba(51,54,59,1)",
                            'pointHoverBackgroundColor' => "rgba(51,54,59,1)",
                            'pointHoverBorderColor' => "rgba(51,54,59,1)",
                            'data' => $dayStats,
                            'spanGaps' => true,
                            'borderJoinStyle' => 'miter',
                            'fill' => false,
                        ],
                        [
                            'label' => 'Рестораны',
                            'backgroundColor' => "rgba(126,188,89,.2)",
                            'borderColor' => "rgba(126,188,89,.8)",
                            'pointBackgroundColor' => "rgba(126,188,89,1)",
                            'pointBorderColor' => "rgba(126,188,89,1)",
                            'pointHoverBackgroundColor' => "rgba(126,188,89,1)",
                            'pointHoverBorderColor' => "rgba(126,188,89,1)",
                            'data' => $clients,
                            'spanGaps' => true,
                            'borderJoinStyle' => 'miter',
                            'fill' => false,
                        ],
                        [
                            'label' => 'Поставщики',
                            'backgroundColor' => "rgba(54,140,191,.2)",
                            'borderColor' => "rgba(54,140,191,.8)",
                            'pointBackgroundColor' => "rgba(54,140,191,1)",
                            'pointBorderColor' => "rgba(54,140,191,1)",
                            'pointHoverBackgroundColor' => "rgba(54,140,191,1)",
                            'pointHoverBorderColor' => "rgba(54,140,191,1)",
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