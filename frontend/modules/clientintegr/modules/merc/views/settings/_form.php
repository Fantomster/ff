<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;
use yii\jui\AutoComplete;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="dict-agent-form">
    <?php if (\Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-info alert-dismissable">
            <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
            <h4>
                <i class="icon fa fa-exclamation-circle"></i>Внимание!
            </h4>
            <?= \Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>
    <?php $org = User::findOne(Yii::$app->user->id)->organization_id; ?>
    <?php $form = ActiveForm::begin(); ?>
    <?php echo $form->errorSummary($model); ?>
    <?php
    switch ($dicConst->type) {
        case \api\common\models\merc\mercDicconst::TYPE_DROP :
            if ($dicConst->denom === 'enterprise_guid') {
                echo $form->field($model, 'value')->widget(
                    AutoComplete::className(), [
                    'clientOptions' => [
                        'source' => $org_list,
                    ],
                    'options'=>[
                        'class'=>'form-control'
                    ]
                ])->label(false);
                //echo $form->field($model, 'value')->dropDownList($org_list);
            }
        break;
        case \api\common\models\merc\mercDicconst::TYPE_PASSWORD:
            echo $form->field($model, 'value')->passwordInput(['maxlength' => true])->label(false);
        break;
        case \api\common\models\merc\mercDicconst::TYPE_CHECKBOX:
            echo $form->field($model, 'value')->checkbox(['label' => 'Только ручная загрузка ВСД'])->label(false);
        break;
        default:
            echo $form->field($model, 'value')->textInput(['maxlength' => true])->label(false);
    }
    ?>
    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? Yii::t('message', 'frontend.views.layouts.client.integration.create', ['ru' => 'Создать']) : Yii::t('message', 'frontend.views.layouts.client.integration.save', ['ru' => 'Сохранить']), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('message', 'Close'), ['index'], ['class' => 'btn btn-success btn-export']);?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

