<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */

$this->title = 'Изменить подключение';
$this->params['breadcrumbs'][] = [
    'label' => 'Управление лицензиями',
    'url'   => '/integration'
];
$this->params['breadcrumbs'][] = [
    'label' => 'Доступы Tillypad',
    'url'   => '/tillypad'
];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="organization-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
