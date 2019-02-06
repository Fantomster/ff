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
                    <legend><?= Yii::t('app', 'franchise.views.organization.data', ['ru'=>'Данные']) ?> <?= ($organizationType == Organization::TYPE_RESTAURANT) ? Yii::t('app', 'franchise.views.organization.rest', ['ru'=>"ресторана"]) : Yii::t('app', 'franchise.views.organization.vendor', ['ru'=>"поставщика"]) ?></legend>
                    <?= $form->field($organization, 'name')->textInput() ?>
                    <?= $form->field($organization, 'city')->textInput() ?>
                    <?= $form->field($organization, 'address')->textInput() ?>
                    <?= $form->field($organization, 'contact_name')->textInput()->label(Yii::t('app', 'franchise.views.organization.contact', ['ru'=>"Контактное лицо"])) ?>
                    <?=
                            $form->field($organization, 'phone', [
//                                'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                            ])
                            ->widget(\common\widgets\phone\PhoneInput::className(), [
                                'jsOptions' => [
                                    'preferredCountries' => ['ru'],
                                    'nationalMode' => false,
                                    'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                ],
                                'options' => [
                                    'class' => 'form-control',
                                ],
                            ])
                            ->label(Yii::t('app', 'franchise.views.organization.contact_phone', ['ru'=>'Телефон контактного лица']))
                    ?>   

                    <?= $form->field($organization, 'email')->textInput()->label(Yii::t('app', 'franchise.views.organization.contact_email', ['ru'=>"Email контактного лица"])) ?>
                    <?= $form->field($organization, 'website')->textInput() ?>
                    <?= $form->field($organization, 'about')->textarea(['rows' => 6, 'style' => 'height: 96px;']) ?>

                    <?= $form->field($organization, 'manager_id')->dropDownList($managersArray, ['prompt'=>Yii::t('app', 'franchise.views.organization.choose_manager', ['ru'=>'Выберите менеджера'])])->label(Yii::t('app', 'franchise.views.organization.manager', ['ru'=>'Менеджер'])) ?>

                    <br>
                    <?php if (isset($user)) { ?>
                        <fieldset>
                            <legend><?= Yii::t('app', 'franchise.views.organization.first_user', ['ru'=>'Первый пользователь (Менеджер)']) ?></legend>
                            <?= $form->field($profile, 'full_name')->textInput() ?>

                            <?= $form->field($user, 'email')->textInput() ?>

                            <?=
                                    $form->field($profile, 'phone', [
//                    'addon' => ['prepend' => ['content' => '<i class="fa fa-phone"></i>']]
                                    ])
                                    ->widget(\common\widgets\phone\PhoneInput::className(), [
                                        'jsOptions' => [
                                            'preferredCountries' => ['ru'],
                                            'nationalMode' => false,
                                            'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
                                        ],
                                        'options' => [
                                            'class' => 'form-control',
                                        ],
                                    ])
                                    ->label(Yii::t('app', 'franchise.views.organization.phone', ['ru'=>'Телефон']))
                                    ->textInput()
                            ?>
                        </fieldset>
                    <?php } ?>
            </div>
            <div class="col-md-6">
                <fieldset>
                    <legend><?= Yii::t('app', 'franchise.views.organization.requisits', ['ru'=>'Реквизиты']) ?></legend>
                    <?php
                    if ($organizationType == Organization::TYPE_SUPPLIER) {
                        echo $form->field($buisinessInfo, 'reward')->textInput()->label(Yii::t('app', 'franchise.views.organization.turnover_percent', ['ru'=>"Процент с оборота"]));
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
                    <?= $form->field($buisinessInfo, 'phone')->widget(\common\widgets\phone\PhoneInput::className(), [
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
        $label = $organization->isNewRecord ? Yii::t('app', 'franchise.views.organization.add', ['ru'=>'Добавить ']) . (($organizationType == Organization::TYPE_RESTAURANT) ? Yii::t('app', 'franchise.views.organization.rest_two', ['ru'=>"ресторан"]) : Yii::t('app', 'franchise.views.organization.vendors', ['ru'=>"поставщика"])) : Yii::t('app', 'franchise.views.organization.save', ['ru'=>"Сохранить"]);
        echo Html::submitButton($label, ['class' => 'btn btn-success btn-sm'])
        ?>
    </div>
</div>
<?php ActiveForm::end(); ?>