<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\WhiteList */

$this->title = 'Create Business Info';
$this->params['breadcrumbs'][] = ['label' => 'Business Info', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="buisiness-info-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
