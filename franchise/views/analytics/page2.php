<?php
use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Аналитика
        <small>Статистика по заказам</small>
    </h1>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">
<?= 
Tabs::widget([
    'items' => [
        [
            'label' => 'Регистрации',
            'url' => Url::to(["analytics/index"]),
        ],
        [
            'label' => 'Заказы',
            'content' => $this->render("_orders", compact(
                    'total',
                    'dateFilterFrom', 
                    'dateFilterTo', 
                    'ordersStatThisMonth',
                    'ordersStatThisDay',
                    'labelsTotal',
                    'ordersStat',
                    'colorsTotal',
                    'totalCountThisMonth',
                    'totalCountThisDay',
                    'totalCount',
                    'firstDayStats',
                    'dayLabels',
                    'dayStats'
                    )),
            'active' => true
        ],
        [
            'label' => 'Оборот',
            'url' => Url::to(["analytics/page3"]),
        ],
    ],
    'options' => [
        'style' => "background-color: #f9f9f9;",
    ],
]);
?>
        </div>
    </div>         
</section>
