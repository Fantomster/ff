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
        <h3>Заказы за все время (<?= $totalCount ?>)</h3>
    </div>
    <div class="col-md-4 col-sm-12">
        <?= Html::checkboxList('statuses', $statuses, Order::getStatusList(), ['separator'=>'<br/>']) ?>
    </div>
    <div class="col-md-4 col-sm-12">
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
</div>

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>