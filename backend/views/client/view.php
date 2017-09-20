<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->profile->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    if ($model->role_id === \common\models\Role::ROLE_FKEEPER_MANAGER) {
        echo Html::a('Редактировать', ['update', 'id' => $model->id], ['class' => 'btn btn-primary', 'style' => 'margin-bottom: 10px;']);
        echo Html::a('Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'style' => 'margin-bottom: 10px; margin-left: 10px;',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]);
    }
    ?>

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
            [
                'attribute' => 'logged_in_at',
                'label' => 'IP при авторизации',
                'value' => function ($data) {
                    return Yii::$app->formatter->asTime($data->logged_in_at, "php:j M Y, H:i:s");
                }
            ],
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
                'format' => 'raw',
                'value' => Html::a($model->organization_id, ['organization/view', 'id' => $model->organization_id]),
                'label' => 'ID организации',
            ],
            [
                'format' => 'raw',
                'value' => isset($model->organization) ? Html::a($model->organization->name, ['organization/view', 'id' => $model->organization_id]) : 'Отсутствует',
                'label' => 'Организация',
            ],
        ],
    ])
    ?>

</div>

<?php if(Yii::$app->session->hasFlash('Forgot-success')): ?>
    <div class="alert alert-info" role="alert">
        <?= Yii::$app->session->getFlash('Forgot-success') ?>
    </div>
<?php endif; ?>

<?php
$form = \yii\widgets\ActiveForm::begin(['method' => 'post']);
?>

<?= $form->field($newPassModel, 'email')->hiddenInput(['value'=>$model->email])->label(false); ?>

<?= Html::submitButton('Выслать письмо со сменой пароля', ['class' => 'btn btn-primary']) ?>

<?php \yii\widgets\ActiveForm::end(); ?>
