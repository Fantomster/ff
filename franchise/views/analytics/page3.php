<?php

$this->title = implode(" - ", [
    Yii::t('app', 'Аналитика'),
    Yii::t('app', 'Оборот'),
]);

use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Аналитика
        <small>Статистика по обороту</small>
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
            'url' => Url::to(["analytics/page2"]),
        ],
        [
            'label' => 'Оборот',
            'content' => $this->render("_turnover", compact(
                    'total',
                    'totalSpent',
                    'monthLabels',
                    'averageSpent',
                    'averageCheque',
                    'dateFilterFrom', 
                    'dateFilterTo', 
                    'dayLabels',
                    'dayTurnover',
                    'dayCheque'
                    )),
            'active' => true
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
