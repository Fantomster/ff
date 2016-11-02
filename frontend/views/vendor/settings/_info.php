<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
?>
<div class="box box-info">
    <div class="box-header">
    </div>
    <?php
    $form = ActiveForm::begin([
                'id' => 'generalSettings',
                'enableAjaxValidation' => false,
                'action' => Url::toRoute(['vendor/ajax-update-organization']),
                'validationUrl' => Url::toRoute('vendor/ajax-validate-organization'),
                'options' => [
                    'class' => 'form-horizontal'
                ],
                'fieldConfig' => [
                    'template' => '{label}<div class="col-sm-5">{input}</div><div class="col-sm-9 pull-right">{error}</div>',
                    'labelOptions' => ['class' => 'col-sm-1 control-label'],
                ],
    ]);
    ?>

    <?=
            $form->field($organization, 'name')
            ->textInput(['placeholder' => $organization->getAttributeLabel('name')])
    ?>

    <?=
            $form->field($organization, 'city')
            ->textInput(['placeholder' => $organization->getAttributeLabel('city')])
    ?>

    <?=
            $form->field($organization, 'address')
            ->textInput(['placeholder' => $organization->getAttributeLabel('address')])
    ?>

    <?=
            $form->field($organization, 'zip_code')
            ->textInput(['placeholder' => $organization->getAttributeLabel('zip_code')])
    ?>

    <?=
            $form->field($organization, 'phone')
            ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
            ->textInput(['placeholder' => $organization->getAttributeLabel('phone')])
    ?>

    <?=
            $form->field($organization, 'email')
            ->textInput(['placeholder' => $organization->getAttributeLabel('email')])
    ?>

    <?=
            $form->field($organization, 'website')
            ->textInput(['placeholder' => $organization->getAttributeLabel('website')])
    ?>

    <div class="box-footer">
        <?= Html::button('<i class="icon fa fa-save"></i> Сохранить изменения', ['class' => 'btn btn-success', 'id' => 'saveOrg', 'disabled' => true]) ?>
        <?= Html::button('<i class="icon fa fa-ban"></i> Отменить изменения', ['class' => 'btn btn-gray', 'id' => 'cancelOrg', 'disabled' => true]) ?>
    </div>				
    <?php ActiveForm::end(); ?>    
</div>
