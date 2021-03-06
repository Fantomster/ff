<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */

$this->title = 'Изменить данные вендора: ' . $model->vendor_id;
$this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->vendor_id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="organization-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form-test-vendor', [
        'model' => $model,
    ]) ?>

</div>
