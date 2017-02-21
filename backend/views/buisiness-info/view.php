<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\BusinessInfo */

$this->title = $model->organization->name;
$this->params['breadcrumbs'][] = ['label' => 'Business Info', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="buisiness-info-view">

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
                'label' => 'Тип',
                'value' => $model->organization->type->name,
            ],
            [
                'format' => 'raw',
                'label' => 'Название',
                'value' => Html::a($model->organization->name, ['organization/view', 'id'=>$model->organization_id]),
            ],
            [
                'format' => 'raw',
                'label' => 'Наш партнер',
                'value' => $model->organization->partnership ? 'Да' : 'Нет',
            ],
            'info:ntext',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
