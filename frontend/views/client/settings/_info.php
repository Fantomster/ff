<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>
<div class="row">
    <div class="col-lg-5">
        <?php $form = ActiveForm::begin([
            'id' => 'generalSettings',
            'enableAjaxValidation' => false,
          //  'action' => Url::toRoute(['client/ajax-update-user']),
            'options' => [
                'class' => 'user-form',
            ],
         //   'validationUrl' => Url::toRoute('client/ajax-validate-user')
            ]); ?>

        <?= $form->field($organization, 'name') ?>

        <?= $form->field($organization, 'city') ?>

        <?= $form->field($organization, 'address') ?>
        
        <?= $form->field($organization, 'zip_code') ?>
        
        <?= $form->field($organization, 'phone') ?>
        
        <?= $form->field($organization, 'email') ?>
        
        <?= $form->field($organization, 'website') ?>
        
        <div class="form-group">
            <?= Html::button('Отменить изменения', ['class' => 'btn btn-default']) ?>
            <?= Html::submitButton('Сохранить изменения', ['class' => 'btn btn-primary']) ?>
        </div>				
<?php ActiveForm::end(); ?>
    </div>
</div>