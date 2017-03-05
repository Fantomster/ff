<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use common\models\Organization;
?>
<?php $form = ActiveForm::begin(); ?>
<div class="box box-info">
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <fieldset>
                    <legend>Данные <?= ($organizationType == Organization::TYPE_RESTAURANT) ? "ресторана" : "поставщика" ?></legend>
                    <?= $form->field($organization, 'name')->textInput() ?>
                    <?= $form->field($organization, 'city')->textInput() ?>
                    <?= $form->field($organization, 'address')->textInput() ?>
                    <?= $form->field($organization, 'contact_name')->textInput()->label("Контактное лицо") ?>
                    <?=
                            $form->field($organization, 'phone', [
//                                'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                            ])
                            ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                            ->label('Телефон контактного лица')
                            ->textInput()
                    ?>                    
                    <?= $form->field($organization, 'website')->textInput() ?>
                    <?= $form->field($organization, 'about')->textarea(['rows' => 6, 'style' => 'height: 96px;']) ?>
                    <br>
                    <?php if (isset($user)) { ?>
                        <fieldset>
                            <legend>Первый пользователь (Менеджер)</legend>
                            <?= $form->field($profile, 'full_name')->textInput() ?>

                            <?= $form->field($user, 'email')->textInput() ?>

                            <?=
                                    $form->field($profile, 'phone', [
//                    'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                                    ])
                                    ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
                                    ->label('Телефон')
                                    ->textInput()
                            ?>
                        </fieldset>
                    <?php } ?>
            </div>
            <div class="col-md-6">
                <fieldset>
                    <legend>Реквизиты</legend>
                    <?php
                    if ($organizationType == Organization::TYPE_SUPPLIER) {
                        echo $form->field($buisinessInfo, 'reward')->textInput()->label("Процент с оборота");
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
            </div>
        </div>
    </div>
</div>
<div class="box-footer">
    <div class="form-group">
        <?php
        $label = $organization->isNewRecord ? 'Добавить ' . (($organizationType == Organization::TYPE_RESTAURANT) ? "ресторан" : "поставщика") : "Сохранить";
        echo Html::submitButton($label, ['class' => 'btn btn-success btn-sm'])
        ?>
    </div>
</div>
<?php ActiveForm::end(); ?>