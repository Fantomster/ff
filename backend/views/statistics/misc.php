<?php
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;
use dosamigos\chartjs\ChartJs;

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
        <h3>Период </h3>
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
</div>
<div>Всего ресторанов: <?= $totalClients ?></div>
<div>Ресторанов с 1 заказом: <?= $clientsStats["c1"] ?></div>
<div>Ресторанов с 2 заказами: <?= $clientsStats["c2"] ?></div>
<div>Ресторанов с 3 заказами: <?= $clientsStats["c3"] ?></div>
<div>Ресторанов с 4 заказами: <?= $clientsStats["c4"] ?></div>
<div>Ресторанов с 5 заказами: <?= $clientsStats["c5"] ?></div>
<div>Ресторанов с большим количеством заказов: <?= $clientsStats["cn"] ?></div>
<br>
<div>Поставщиков с каталогами: <?= $vendorsWithGoodsCount ?></div>
<div>Всего размещено товаров: <?= $productsCount ?></div>
<div>Из них на маркете: <?= $productsOnMarketCount ?></div>

<?php ActiveForm::end(); ?>

<?php Pjax::end() ?>