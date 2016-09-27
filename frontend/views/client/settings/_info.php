<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'generalSettings',
            'enableAjaxValidation' => false,
            'action' => Url::toRoute(['client/ajax-update-organization']),
            'validationUrl' => Url::toRoute('client/ajax-validate-organization')
        ]);
?>
<div class="row org-info" style="padding-top: 10px;">
    <div class="col-lg-5">

        <?=
                $form->field($organization, 'name')
                ->label(false)
                ->textInput(['placeholder' => $organization->getAttributeLabel('name')])
        ?>

        <?=
                $form->field($organization, 'city')
                ->label(false)
                ->textInput(['placeholder' => $organization->getAttributeLabel('city')])
        ?>

        <?=
                $form->field($organization, 'address')
                ->label(false)
                ->textInput(['placeholder' => $organization->getAttributeLabel('address')])
        ?>

        <?=
                $form->field($organization, 'zip_code')
                ->label(false)
                ->textInput(['placeholder' => $organization->getAttributeLabel('zip_code')])
        ?>

        <?=
                $form->field($organization, 'phone')
                ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                ->label(false)
                ->textInput(['placeholder' => $organization->getAttributeLabel('phone')])
        ?>

<?=
        $form->field($organization, 'email')
        ->label(false)
        ->textInput(['placeholder' => $organization->getAttributeLabel('email')])
?>

<?=
        $form->field($organization, 'website')
        ->label(false)
        ->textInput(['placeholder' => $organization->getAttributeLabel('website')])
?>

        <div class="form-group">
<?= Html::button('Отменить изменения', ['class' => 'btn btn-default', 'id' => 'cancelOrg', 'disabled' => true]) ?>
<?= Html::button('Сохранить изменения', ['class' => 'btn btn-primary', 'id' => 'saveOrg', 'disabled' => true]) ?>
        </div>				
    </div>
</div>
<?php ActiveForm::end(); ?>
