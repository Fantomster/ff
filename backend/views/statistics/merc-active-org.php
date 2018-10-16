<?php

use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Использование Меркурия за последний месяц';
?>

<h3>Организации которые использовали Меркурий за последний месяц</h3>
<?= GridView::widget([
    'dataProvider' => $dataProviderIn,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'name',
            'label' => 'Название организации',
            'value' => function ($data) {
                return Html::a(Html::encode($data['name']), ['organization/view', 'id' => $data['id']]);
            },
            'format' => 'raw',
        ],
    ],
]); ?>

<h3>Организации которые не использовали Меркурий за последний месяц</h3>
<?= GridView::widget([
    'dataProvider' => $dataProviderNotIn,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'id',
        ],
        [
            'attribute' => 'name',
            'label' => 'Название организации',
            'value' => function ($data) {
                return Html::a(Html::encode($data['name']), ['organization/view', 'id' => $data['id']]);
            },
            'format' => 'raw',
        ],
    ],
]); ?>
