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
<h3>Зарегистрировано за все время.</h3>

<div>Всего: <?= $totalCount ?></div>
<div>Ресторанов: <?= $clientTotalCount ?></div>
<div>Поставщиков: <?= $vendorTotalCount ?></div>

<h3>Зарегистрировано с </h3><?= DatePicker::widget([
    'name' => 'date',
    'type' => DatePicker::TYPE_INPUT,
    'value' => $dateFilter,
    'options' => ['id' => 'date', 'style' => 'width: 100px;'],
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => 'dd.mm.yyyy',
        'endDate' => "0d",
    ]
]) ?>

<div>Всего: <?= $countSinceDate ?></div>
<div>Ресторанов: <?= $clientCountSinceDate ?></div>
<div>Поставщиков: <?= $vendorCountSinceDate ?></div>

<br>
<?php foreach ($weekArray as $week) { ?>
<h4>Неделя с <?= $week['start'] ?> до <?= $week['end'] ?>:</h4>
<div>Всего: <?= $week['count'] ?></div>
<div>Ресторанов: <?= $week['clientCount'] ?></div>
<div>Поставщиков: <?= $week['vendorCount'] ?></div>
<br>
<?php } ?>

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>