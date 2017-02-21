<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use common\models\Organization;
?>

<div class="create-organization-form">

    <?php $form = ActiveForm::begin(); ?>
    <fieldset>
        <legend><?= ($organizationType == Organization::TYPE_RESTAURANT) ? "Ресторан" : "Поставщик" ?></legend>

        <?= $form->field($organization, 'name')->textInput() ?>

        <?= $form->field($organization, 'city')->textInput() ?>

        <?= $form->field($organization, 'address')->textInput() ?>

        <?=
                $form->field($organization, 'phone', [
                    'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                ])
                ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                ->label('Телефон')
                ->textInput()
        ?>

        <?= $form->field($organization, 'website')->textInput() ?>

        <?= $form->field($organization, 'about')->textarea(['rows' => 6]) ?>
    </fieldset>
    <fieldset>
        <legend>Первый пользователь</legend>
        
        <?= $form->field($profile, 'full_name')->textInput() ?>
        
        <?= $form->field($user, 'email')->textInput() ?>
        
        <?=
                $form->field($profile, 'phone', [
                    'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                ])
                ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                ->label('Телефон')
                ->textInput()
        ?>
    </fieldset>
    <fieldset>
        <legend>Реквизиты</legend>

        <?php
        if ($organizationType == Organization::TYPE_SUPPLIER) {
            echo $form->field($buisinessInfo, 'reward')->textInput();
        }
        ?>
        
        <?= $form->field($buisinessInfo, 'signed')->textInput() ?>

        <?= $form->field($buisinessInfo, 'legal_entity')->textInput() ?>

        <?= $form->field($buisinessInfo, 'legal_address')->textInput() ?>

        <?= $form->field($buisinessInfo, 'legal_email')->textInput() ?>

        <?= $form->field($buisinessInfo, 'inn')->textInput() ?>

        <?= $form->field($buisinessInfo, 'kpp')->textInput() ?>

        <?= $form->field($buisinessInfo, 'ogrn')->textInput() ?>

        <?= $form->field($buisinessInfo, 'bank_name')->textInput() ?>

        <?= $form->field($buisinessInfo, 'bik')->textInput() ?>

        <?= $form->field($buisinessInfo, 'phone')->textInput() ?>

        <?= $form->field($buisinessInfo, 'correspondent_account')->textInput() ?>

        <?= $form->field($buisinessInfo, 'checking_account')->textInput() ?>

        <?= $form->field($buisinessInfo, 'info')->textarea(['rows' => 6]) ?>
    </fieldset>

    <div class="form-group">
        <?= Html::submitButton('Создать', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
