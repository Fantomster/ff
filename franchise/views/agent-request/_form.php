<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\AgentRequest */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="agent-request-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'target_email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'comment')->textInput(['maxlength' => true]) ?>

    <div style="padding-top: 10px;padding-bottom: 10px;">Приложения:
        <?php foreach ($model->attachments as $attachment) { ?>
            <a href="<?= $attachment->getUploadUrl("attachment") ?>"><?= $attachment->attachment ?></a>
        <?php } ?>
    </div>

    <?= $form->field($attachment, 'attachment')->fileInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
