<?php

$this->title = implode(" - ", [
    Yii::t('app', 'franchise.views.anal.anal_six', ['ru'=>'Аналитика']),
    Yii::t('app', 'franchise.views.anal.turnover_five', ['ru'=>'Оборот']),
]);

use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> <?= Yii::t('app', 'franchise.views.anal.anal_seven', ['ru'=>'Аналитика']) ?>
        <small><?= Yii::t('app', 'franchise.views.anal.turnover_stat', ['ru'=>'Статистика по обороту']) ?></small>
    </h1>
</section>
<section class="content">
    <div class="box box-info">
        <div class="box-body">
<?= 
Tabs::widget([
    'items' => [
        [
            'label' => Yii::t('app', 'franchise.views.anal.regs_four', ['ru'=>'Регистрации']),
            'url' => Url::to(["analytics/index"]),
        ],
        [
            'label' => Yii::t('app', 'franchise.views.anal.orders_four', ['ru'=>'Заказы']),
            'url' => Url::to(["analytics/page2"]),
        ],
        [
            'label' => Yii::t('app', 'franchise.views.anal.turnover_six', ['ru'=>'Оборот']),
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
