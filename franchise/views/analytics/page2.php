<?php

$this->title = implode(" - ", [
    Yii::t('app', 'Аналитика'),
    Yii::t('app', 'Заказы'),
]);

use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'Аналитика') ?>
        <small><?= Yii::t('app', 'Статистика по заказам') ?></small>
    </h1>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">
<?= 
Tabs::widget([
    'items' => [
        [
            'label' => Yii::t('app', 'Регистрации'),
            'url' => Url::to(["analytics/index"]),
        ],
        [
            'label' => Yii::t('app', 'Заказы'),
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
            'label' => Yii::t('app', 'Оборот'),
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
