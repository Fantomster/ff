<?php
use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Промо-материалы
        <small>Логотипы, шрифты, картинки</small>
    </h1>
</section>
<section class="content body">
    <div class="row">
        <div class="col-md-12 nav-tabs-custom">
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
