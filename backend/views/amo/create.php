<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Franchisee */

$this->title = 'Поля amoCRM';
$this->params['breadcrumbs'][] = ['label' => 'AMO', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="franchisee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
