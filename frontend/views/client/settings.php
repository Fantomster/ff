<?php
use yii\bootstrap\Tabs;
?>

<?= Tabs::widget([
    'items' => [
        [
            'label' => 'Общие',
            'content' => $this->render('settings/_info', compact('organization')),
            'active' => true,
        ],
        [
            'label' => 'Пользователи',
            'content' => $this->render('settings/_users', compact('dataProvider', 'searchModel')),
        ],
        [
            'label' => 'Бюджет',
            'content' => $this->render('settings/_budget'),
        ],
    ],
]) ?>
