<?php

use yii\widgets\ListView;
use yii\data\ActiveDataProvider;

/** @var $vendorDataProvider ActiveDataProvider */
/** @var $selectedVendor string */

$emptyText = Yii::t('message', 'frontend.views.order.guides.empty_list_three', ['ru' => 'Список пуст']);

?>

<table class="table table-hover">
    <tbody>
    <?= ListView::widget([
        'dataProvider' => $vendorDataProvider,
        'itemView' => function ($model) use ($selectedVendor) {
            return $this->render('_vendor-view', [
                'model' => $model,
                'selectedVendor' => $selectedVendor,
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