<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->profile->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            //'role.name',
            [
                'value' => $model->role->name,
                'label' => 'Роль',
            ],
            'status',
            'email:email',
            'logged_in_ip',
            'logged_in_at',
            'created_ip',
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
//            'banned_at',
//            'banned_reason',
//            'organization_id',
            [
                'value' => $model->organization_id,
                'label' => 'ID организации',
            ],
            [
                'format' => 'raw',
                'value' => Html::a($model->organization->name, ['organization/view', 'id' => $model->organization_id]),
                'label' => 'Организация',
            ],
        ],
    ])
    ?>

</div>
