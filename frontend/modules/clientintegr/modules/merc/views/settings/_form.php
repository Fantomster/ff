<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    <?php $org = User::findOne(Yii::$app->user->id)->organization_id; ?>
    <?php $form = ActiveForm::begin(); ?>
    <?php echo $form->errorSummary($model); ?>
    <?php
    switch ($dicConst->type) {
        case \api\common\models\merc\mercDicconst::TYPE_DROP :
            if ($dicConst->denom === 'enterprise_guid') {
                echo $form->field($model, 'value')->dropDownList($org_list);
            }
        break;
        case \api\common\models\merc\mercDicconst::TYPE_PASSWORD:
            echo $form->field($model, 'value')->passwordInput(['maxlength' => true]);
        break;
        default:
            echo $form->field($model, 'value')->textInput(['maxlength' => true]);
    }
    ?>
    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('message', 'frontend.views.layouts.client.integration.create', ['ru' => 'Создать']) : Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('message', 'Close'), ['index'], ['class' => 'btn btn-success btn-export']);?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

