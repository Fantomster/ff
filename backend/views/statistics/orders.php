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
        $(document).on("change", "[name=\'statuses[]\']", function() {
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
        <h3>Заказы за все время</h3>
    </div>
    <div class="col-md-4 col-sm-12">
        <?= Html::checkboxList('statuses', null, Order::getStatusList(), ['separator'=>'<br/>']) ?>
    </div>
</div>

<h3>Заказы за все время.</h3>

<div>Всего создано: <?= $orderCount ?></div>
<div>Отменено ресторанами: <?= $cancelledOrderCount ?></div>
<div>Принято поставщиками: <?= $acceptedOrderCount ?></div>
<div>Отменено поставщиками: <?= $rejectedOrderCount ?></div>

<h3>Создано с </h3><?= DatePicker::widget([
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

<div>Всего создано: <?= $orderCountSinceDate ?></div>
<div>Отменено ресторанами: <?= $cancelledOrderCountSinceDate ?></div>
<div>Принято поставщиками: <?= $acceptedOrderCountSinceDate ?></div>
<div>Отменено поставщиками: <?= $rejectedOrderCountSinceDate ?></div>

<br>
<?php foreach ($weekArray as $week) { ?>
<h4>Неделя с <?= $week['start'] ?> до <?= $week['end'] ?>:</h4>
<div>Всего создано: <?= $week['count'] ?></div>
<div>Отменено ресторанами: <?= $week['cancelled'] ?></div>
<div>Принято поставщиками: <?= $week['accepted'] ?></div>
<div>Отменено поставщиками: <?= $week['rejected'] ?></div>
<br>
<?php } ?>

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>