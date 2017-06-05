<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model api\common\models\RkAccess */

$this->title = 'Create Rk Access';
$this->params['breadcrumbs'][] = ['label' => 'Rk Accesses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rk-access-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
