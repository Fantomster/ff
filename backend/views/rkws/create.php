<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Organization */

$this->title = 'Создать доступ';
$this->params['breadcrumbs'][] = ['label' => 'Organizations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$smodel = \api\common\models\RkService::find()->andWhere('id = :id',[':id' => $service_id])->one();
?>
<div class="organization-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <h4> <?= 'Имя заведения в UCS: '. $smodel->name; ?> </h4>
    <h4> <?= 'Код заведения в UCS (object_id): '.$smodel->code; ?> </h4>
    <hr>

    <?= $this->render('_form', [
        'model' => $model,
        'service_id' => $service_id,
    ]) ?>

</div>
