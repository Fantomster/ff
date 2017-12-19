<?php

$this->title = implode(" - ", [
    Yii::t('app', 'franchise.views.anal.anal_four', ['ru'=>'Аналитика']),
    Yii::t('app', 'franchise.views.anal.orders_two', ['ru'=>'Заказы']),
]);

use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.anal.anal_five', ['ru'=>'Аналитика']) ?>
        <small><?= Yii::t('app', 'franchise.views.anal.orders_stat', ['ru'=>'Статистика по заказам']) ?></small>
    </h1>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">
<?= 
Tabs::widget([
    'items' => [
        [
            'label' => Yii::t('app', 'franchise.views.anal.regs_three', ['ru'=>'Регистрации']),
            'url' => Url::to(["analytics/index"]),
        ],
        [
            'label' => Yii::t('app', 'franchise.views.anal.orders_three', ['ru'=>'Заказы']),
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
            'label' => Yii::t('app', 'franchise.views.anal.turnover_four', ['ru'=>'Оборот']),
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
