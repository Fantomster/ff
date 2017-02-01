<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\WhiteList */

$this->title = 'Create White List';
$this->params['breadcrumbs'][] = ['label' => 'White Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="white-list-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
