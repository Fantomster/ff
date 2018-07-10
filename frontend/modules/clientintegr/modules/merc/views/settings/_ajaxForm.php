<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model \api\common\models\iiko\iikoPconst */
/* @var $dicConst \api\common\models\iiko\iikoDicconst */
/* @var $form yii\bootstrap\ActiveForm */
?>

    <?php $org = User::findOne(Yii::$app->user->id)->organization_id; ?>
    <?php $form = ActiveForm::begin([
        'id' => 'settings-form',
        //'enableAjaxValidation' => false,
        //'action' => Url::toRoute(['client/view-supplier', 'id' => $supplier_org_id]),
        /*'options' => [
            'class' => 'supplier-form',
        ],*/
    ]); ?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title"><?= Yii::t('message', 'frontend.client.integration.mercury.edit_settings', ['ru'=>'Редактирование настройки']) ?>: <strong> <?= $dicConst->comment?></strong></h4>
</div>
<div class="modal-body">
    <?php echo $form->errorSummary($model); ?>
    <?php
    switch ($dicConst->type) {
        case \api\common\models\merc\mercDicconst::TYPE_DROP :
            if ($dicConst->denom === 'enterprise_guid') {
                echo $form->field($model, 'value')->dropDownList($org_list)->label(false);
            }
            break;
        case \api\common\models\merc\mercDicconst::TYPE_PASSWORD:
            echo $form->field($model, 'value')->passwordInput(['maxlength' => true])->label(false);
        break;
        case \api\common\models\merc\mercDicconst::TYPE_CHECKBOX:
            echo $form->field($model, 'value')->checkbox(['label' => 'Только ручная загрузка ВСД']);
        break;
        default:
            echo $form->field($model, 'value')->textInput(['maxlength' => true])->label(false);
    }
    ?>
</div>
<div class="modal-footer">
    <?= Html::button('<i class="icon fa fa-save"></i> ' . Yii::t('message', 'frontend.views.client.supp.save_two', ['ru'=>'Сохранить']), ['class' => 'btn btn-success save-form']) ?>
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-remove"></i> <?= Yii::t('message', 'frontend.views.client.supp.close_four', ['ru'=>'Закрыть']) ?></a>
</div>
    <?php ActiveForm::end(); ?>



