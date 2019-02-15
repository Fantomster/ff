<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PromoAction */

$this->title = 'Редактирование промо-акции: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Промо-акции', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="promo-action-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
