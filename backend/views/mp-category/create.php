<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\MpCategory */

$this->title = 'Create Mp Category';
$this->params['breadcrumbs'][] = ['label' => 'Mp Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mp-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
