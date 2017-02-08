<?php

use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use dosamigos\chartjs\ChartJs;

$this->registerJs('
    $("document").ready(function(){
        var justSubmitted = false;
        $(document).on("change", "#date", function() {
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
    <div class="col-md-12" style="text-align: center;">
        <h3>Зарегистрировано</h3>
    </div>
    <div class="col-md-4 col-sm-12" style="text-align: center;">
        <h4>За все время.</h4>
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
                        'data' => $allTime,
                        'backgroundColor' => ["#FF0000", "#00FF00"],
                        'hoverBackgroundColor' => ["#FF0000", "#00FF00"],
                    ]
                ],
            ],
        ]);
        ?>
    </div>
    <div class="col-md-4 col-sm-12" style="text-align: center;">
        <h4>За текущий месяц</h4>
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
                        'backgroundColor' => ["#FF0000", "#00FF00"],
                        'hoverBackgroundColor' => ["#FF0000", "#00FF00"],
                    ]
                ],
            ],
        ]);
        ?>
    </div>
    <div class="col-md-4 col-sm-12" style="text-align: center;">
        <h4>Сегодня</h4>
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
                        'data' => $thisDay,
                        'backgroundColor' => ["#FF0000", "#00FF00"],
                        'hoverBackgroundColor' => ["#FF0000", "#00FF00"],
                    ]
                ],
            ],
        ]);
        ?>
    </div>
</div>

<h3>Зарегистрировано с </h3><?=
DatePicker::widget([
    'name' => 'date',
    'type' => DatePicker::TYPE_INPUT,
    'value' => $dateFilter,
    'options' => ['id' => 'date', 'style' => 'width: 100px;'],
    'pluginOptions' => [
        'autoclose' => true,
        'format' => 'dd.mm.yyyy',
        'endDate' => "0d",
    ]
])
?>

<?=
ChartJs::widget([
    'type' => 'line',
    'options' => [
        'height' => 400,
        'width' => 800,
    ],
    'data' => [
        'labels' => $weeks,
        'datasets' => [
            [
                'label' => 'Все организации',
                'backgroundColor' => "rgba(0,0,255,0.2)",
                'borderColor' => "rgba(0,0,255,1)",
                'pointBackgroundColor' => "rgba(0,0,255,1)",
                'pointBorderColor' => "#00f",
                'pointHoverBackgroundColor' => "#00f",
                'pointHoverBorderColor' => "rgba(0,0,255,1)",
                'data' => $all,
                'spanGaps' => false,
                'borderJoinStyle' => 'miter',
                'fill' => false,
            ],
            [
                'label' => 'Рестораны',
                'backgroundColor' => "rgba(255,0,0,0.2)",
                'borderColor' => "rgba(255,0,0,1)",
                'pointBackgroundColor' => "rgba(255,0,0,1)",
                'pointBorderColor' => "#f00",
                'pointHoverBackgroundColor' => "#f00",
                'pointHoverBorderColor' => "rgba(255,0,0,1)",
                'data' => $clients,
                'spanGaps' => false,
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
                'spanGaps' => false,
                'borderJoinStyle' => 'miter',
                'fill' => false,
            ],
        ]
    ],
])
?>

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>