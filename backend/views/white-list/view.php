<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\WhiteList */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'White Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="white-list-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'organization_id',
            [
                'format' => 'raw',
                'label' => 'Название',
                'value' => Html::a($model->organization->name, ['organization/view', 'id'=>$model->organization_id]),
            ],
            'info:ntext',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
