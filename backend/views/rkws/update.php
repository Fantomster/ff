<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */

$this->title = 'Изменить параметры доступа: ';

// $smodel = \api\common\models\RkService::find()->andWhere('id = :s_id',[':s_id' => $model->service_id])



// $this->params['breadcrumbs'][] = ['label' => 'Организации', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = 'Update';
?>
<div class="organization-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <h4> <?= 'Имя заведения в UCS: '. $model->service->name; ?> </h4>
    <h4> <?= 'Код заведения в UCS (object_id): '.$model->service->code; ?> </h4>
    <hr>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
