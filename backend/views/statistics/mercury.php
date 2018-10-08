<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Аналитика по Меркурию';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs('
    $(document).ready(function(){
        var justSubmitted = false;
        $(document).on("change", "#dateFrom, #dateTo", function() {
            if (!justSubmitted) {
                $("#orderStatForm").submit();
                justSubmitted = true;
                setTimeout(function() {
                    justSubmitted = false;
                }, 1000);
            }
        });
    });
        ');

$gridColumns = [
    [
        'format' => 'raw',
        'attribute' => 'orgName',
        'label' => 'Организация',
    ],
    [
        'format' => 'raw',
        'attribute' => 'succCount',
        'label' => 'Кол-во погашенных ВСД',
        'filter' => false,
    ],
    [
        'format' => 'raw',
        'attribute' => 'errorCount',
        'label' => 'Кол-во ошибок',
        'filter' => false,
    ],
];
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php
    Pjax::begin(['enablePushState' => false, 'id' => 'orderStat',]);
    $form = ActiveForm::begin([
        'options' => [
            'data-pjax' => true,
            'id' => 'orderStatForm',
        ],
        'method' => 'get',
    ]);
    ?>

    <div class="row">
        <div class="col-md-12 text-center">
            <h3>Период </h3>
            <div class="form-group" style="width: 350px; margin: 0 auto; padding-bottom: 10px;">
                <?=
                DatePicker::widget([
                    'name' => 'date',
                    'name2' => 'date2',
                    'value' => date('d.m.Y', strtotime($searchModel->dateFrom)),
                    'value2' => date('d.m.Y', strtotime($searchModel->dateTo)),
                    'options' => ['placeholder' => 'Начальная Дата', 'id' => 'dateFrom'],
                    'options2' => ['placeholder' => 'Конечная дата', 'id' => 'dateTo'],
                    'separator' => '-',
                    'type' => DatePicker::TYPE_RANGE,
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy', //'d M yyyy',//
                        'autoclose' => true,
                        'endDate' => "0d",
                        'orientation' => "bottom auto"
                    ]
                ])
                ?>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]);
    ?>
    <?php Pjax::end(); ?></div>

