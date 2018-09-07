<?php

use yii\data\ActiveDataProvider;
use yii\widgets\ListView;

/** @var $productDataProvider ActiveDataProvider */
/** @var $guideProductList array */

$emptyText = Yii::t('message', 'frontend.views.order.guides.empty_list_three', ['ru' => 'Список пуст']);

?>

<table class="table table-hover">
    <tbody>
    <?= ListView::widget([
        'dataProvider' => $productDataProvider,
        'itemView' => function ($model) use ($guideProductList) {
            return $this->render('_product-view', [
                'model' => $model,
                'guideProductList' => $guideProductList,
            ]);
        },
        'itemOptions' => [
            'tag' => 'tr',
        ],
        'pager' => [
            'maxButtonCount' => 5,
        ],
        'options' => [
            'class' => 'col-lg-12 list-wrapper inline no-padding'
        ],
        'layout' => "{items}<tr><td>{pager}</td></tr>",
        'emptyText' => '<tr><td>' . $emptyText . ' </td></tr>',
    ])
    ?>
    </tbody>
</table>