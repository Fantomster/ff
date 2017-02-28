<?php
use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Аналитика
        <small>Статистика по обороту</small>
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
