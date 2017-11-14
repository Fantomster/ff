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
                    <legend><?= Yii::t('app', 'Данные') ?> <?= ($organizationType == Organization::TYPE_RESTAURANT) ? Yii::t('app', "ресторана") : Yii::t('app', "поставщика") ?></legend>
                    <?= $form->field($organization, 'name')->textInput() ?>
                    <?= $form->field($organization, 'city')->textInput() ?>
                    <?= $form->field($organization, 'address')->textInput() ?>
                    <?= $form->field($organization, 'contact_name')->textInput()->label(Yii::t('app', "Контактное лицо")) ?>
                    <?=
                            $form->field($organization, 'phone', [
//                                'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                            ])
                            ->widget(\common\widgets\PhoneInput::className(), [
                                'jsOptions' => [
                                    'preferredCountries' => ['ru'],
                                    'nationalMode' => false,
                                    'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                ],
                                'options' => [
                                    'class' => 'form-control',
                                ],
                            ])
                            ->label(Yii::t('app', 'Телефон контактного лица'))
                    ?>   

                    <?= $form->field($organization, 'email')->textInput()->label(Yii::t('app', "Email контактного лица")) ?>
                    <?= $form->field($organization, 'website')->textInput() ?>
                    <?= $form->field($organization, 'about')->textarea(['rows' => 6, 'style' => 'height: 96px;']) ?>

                    <?= $form->field($organization, 'manager_id')->dropDownList($managersArray, ['prompt'=>Yii::t('app', 'Выберите менеджера')])->label(Yii::t('app', 'Менеджер')) ?>

                    <br>
                    <?php if (isset($user)) { ?>
                        <fieldset>
                            <legend><?= Yii::t('app', 'Первый пользователь (Менеджер)') ?></legend>
                            <?= $form->field($profile, 'full_name')->textInput() ?>

                            <?= $form->field($user, 'email')->textInput() ?>

                            <?=
                                    $form->field($profile, 'phone', [
//                    'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                                    ])
                                    ->widget(\common\widgets\PhoneInput::className(), [
                                        'jsOptions' => [
                                            'preferredCountries' => ['ru'],
                                            'nationalMode' => false,
                                            'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                        ],
                                        'options' => [
                                            'class' => 'form-control',
                                        ],
                                    ])
                                    ->label(Yii::t('app', 'Телефон'))
                                    ->textInput()
                            ?>
                        </fieldset>
                    <?php } ?>
            </div>
            <div class="col-md-6">
                <fieldset>
                    <legend><?= Yii::t('app', 'Реквизиты') ?></legend>
                    <?php
                    if ($organizationType == Organization::TYPE_SUPPLIER) {
                        echo $form->field($buisinessInfo, 'reward')->textInput()->label(Yii::t('app', "Процент с оборота"));
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
                    <?= $form->field($buisinessInfo, 'phone')->widget(\common\widgets\PhoneInput::className(), [
                                'jsOptions' => [
                                    'preferredCountries' => ['ru'],
                                    'nationalMode' => false,
                                    'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                ],
                                'options' => [
                                    'class' => 'form-control',
                                ],
                            ])->textInput() ?>
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
        $label = $organization->isNewRecord ? Yii::t('app', 'Добавить ') . (($organizationType == Organization::TYPE_RESTAURANT) ? Yii::t('app', "ресторан") : Yii::t('app', "поставщика")) : Yii::t('app', "Сохранить");
        echo Html::submitButton($label, ['class' => 'btn btn-success btn-sm'])
        ?>
    </div>
</div>
<?php ActiveForm::end(); ?>