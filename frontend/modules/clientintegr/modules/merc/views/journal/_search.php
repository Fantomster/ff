<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\search\JournalSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="journal-search row">
    <?php $form = \kartik\form\ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ]
    ]); ?>
    <div class="col-sm-3">
        <?php
        $model->organization_id = isset($model->organization_id ) ? $model->organization_id : (\Yii::$app->user->identity)->organization_id;
        echo $form->field($model, 'organization_id')->widget(\kartik\select2\Select2::classname(), [
            'data' => \yii\helpers\ArrayHelper::map($user->getAllOrganization(null), 'id', 'name'),
            'attribute' => 'organization_id',
            'value' => (\Yii::$app->user->identity)->organization_id,
            'pluginOptions' => [
                'allowClear' => false,
            ],
        ]);
        ?>
    </div>
    <div class="col-sm-3">
        <?php echo $form->field($model, 'operation_code')
            ->dropDownList(\yii\helpers\ArrayHelper::map(\common\models\AllServiceOperation::findAll(['service_id' => 4]), 'code', 'comment'),
                ['prompt'=>'Все']) ?>
    </div>
    <div class="col-sm-3">
        <?php echo $form->field($model, 'type')->dropDownList([null => 'Все', 'success' => 'Успех', 'error' => 'Ошибка']) ?>
    </div>
    <div class="col-sm-2">
        <?= Html::label('&nbsp;'); ?><br>
        <?= Html::submitButton('Поиск', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php \kartik\form\ActiveForm::end(); ?>
</div>
