<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Franchisee */

$this->title = "Create GEO Franchisee";
$this->params['breadcrumbs'][] = ['label' => 'Franchisees', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $franchisee->id, 'url' => ['view', 'id' => $franchisee->id]];
$this->params['breadcrumbs'][] = 'GEO CREATE';
?>
<div class="franchisee-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_geoForm', [
        'franchisee' => $franchisee,
        'franchiseeGeo' => $franchiseeGeo,
    ]) ?>

</div>
