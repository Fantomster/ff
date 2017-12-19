<?php

$this->title = implode(" - ", [
    Yii::t('app', 'Аналитика'),
    Yii::t('app', 'Регистрации'),
]);

use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'Аналитика') ?>
        <small><?= Yii::t('app', 'Статистика по регистрациям') ?></small>
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
            'content' => $this->render("_registration", compact(
                                    'total', 'dateFilterFrom', 'dateFilterTo', 'clients', 'vendors', 'allTime', 'thisMonth', 'todayArr', 'todayCount', 'thisMonthCount', 'allTimeCount', 'dayLabels', 'dayStats'
            )),
            'active' => true
        ],
        [
            'label' => Yii::t('app', 'Заказы'),
            'url' => Url::to(["analytics/page2"]),
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
