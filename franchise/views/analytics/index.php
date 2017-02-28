<?php
use yii\bootstrap\Tabs;
use yii\helpers\Url;
?>
<section class="content-header">
    <h1>
        <i class="fa fa-home"></i> Аналитика
        <small>Статистика по регистрациям</small>
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
            'content' => $this->render("_registration", compact(
                                    'total', 'dateFilterFrom', 'dateFilterTo', 'clients', 'vendors', 'allTime', 'thisMonth', 'todayArr', 'todayCount', 'thisMonthCount', 'allTimeCount', 'dayLabels', 'dayStats'
            )),
            'active' => true
        ],
        [
            'label' => 'Заказы',
            'url' => Url::to(["analytics/page2"]),
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
