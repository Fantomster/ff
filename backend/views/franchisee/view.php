<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Franchisee */

$this->title = $model->signed . "[$model->legal_entity]";
$this->params['breadcrumbs'][] = ['label' => 'Franchisees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="franchisee-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('GEO', ['geo', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?=
        ''
//        Html::a('Delete', ['delete', 'id' => $model->id], [
//            'class' => 'btn btn-danger',
//            'data' => [
//                'confirm' => 'Are you sure you want to delete this item?',
//                'method' => 'post',
//            ],
//        ]) 
        ?>
    </p>

    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'type.name',
            'signed',
            'legal_entity',
            'legal_address',
            'legal_email:email',
            'inn',
            'kpp',
            'ogrn',
            'bank_name',
            'bik',
            'phone',
            'correspondent_account',
            'checking_account',
            'info:ntext',
            [
                'attribute' => 'created_at',
                'label' => 'Дата создания',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->created_at, "php:j M Y, H:i:s");
                }
            ],
            [
                'attribute' => 'updated_at',
                'label' => 'Последнее изменение',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->updated_at, "php:j M Y, H:i:s");
                }
            ],
            [
                'format' => 'raw',
                'label' => 'Пользователи',
                'value' => Html::a('Список', ['franchisee/users', 'id' => $model->id])
            ],
        ],
    ])
    ?>

</div>
