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

<div class="row">
    <div class="col-md-12 box box-primary"> 
        <div class="box-header with-border">
            <h3 class="box-title">Оборот в период</h3>
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
            <span class="text-bold"><?= $total ?> руб </span>
        </div>
        <div class="box-body">
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
</div>
<div class="row">
    <div class="col-md-12 box box-primary"> 
        <div class="box-header with-border">
            <h3 class="box-title">Общий оборот в месяц</h3>
        </div>
        <div class="box-body">
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
                            'label' => 'Общий оборот',
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
</div>
<div class="row">
    <div class="col-md-12 box box-primary"> 
        <div class="box-header with-border">
            <h3 class="box-title">Средний оборот в месяц (на 1 ресторан)</h3>
        </div>
        <div class="box-body">
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
                            'label' => 'Средний оборот',
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
</div>
<div class="row">
    <div class="col-md-12 box box-primary"> 
        <div class="box-header with-border">
            <h3 class="box-title">Средний чек в месяц (на 1 заказ)</h3>
        </div>
        <div class="box-body">
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
                            'label' => 'Средний чек',
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
</div>

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>