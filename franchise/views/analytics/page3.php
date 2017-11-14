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
        <i class="fa fa-home"></i> <?= Yii::t('app', 'Аналитика') ?>
        <small><?= Yii::t('app', 'Статистика по обороту') ?></small>
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
            'url' => Url::to(["analytics/page2"]),
        ],
        [
            'label' => Yii::t('app', 'Оборот'),
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
