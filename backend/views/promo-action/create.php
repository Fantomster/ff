<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PromoAction */

$this->title = 'Добавление промо-акции';
$this->params['breadcrumbs'][] = ['label' => 'Промо-акции', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="promo-action-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
