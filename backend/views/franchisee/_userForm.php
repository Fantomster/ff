<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use common\models\Role;
use kartik\checkbox\CheckboxX;

kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);
?>
<div class="user-form">

    <?php
    $form = ActiveForm::begin([
                'id' => 'user-form',
                'action' => $user->isNewRecord ? Url::toRoute(['franchisee/create-user', 'fr_id' => $fr_id]) : Url::toRoute(['franchisee/update-user', 'id' => $user->id, 'fr_id' => $fr_id]),
                'options' => [
                    'class' => 'user-form',
                ],
    ]);
    ?>
    <h4 class="modal-title"><?= $user->isNewRecord ? 'Новый пользователь' : 'Редактировать пользователя' ?></h4>

    <?= $form->field($user, 'email') ?>

    <?= $form->field($profile, 'full_name') ?>

    <?= ''//$form->field($profile, 'phone')->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',]) ?>

    <?=
    $form->field($profile, 'phone')->widget(\common\widgets\phone\PhoneInput::className(), [
        'jsOptions' => [
            'preferredCountries' => ['ru'],
            'nationalMode' => false,
            'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input'). '/build/js/utils.js',
        ],
        'options' => [
            'class' => 'form-control',
        ],
        
    ])
    ?>

    <?=
    $form->field($profile, 'sms_allow')->widget(CheckboxX::classname(), [
        //'initInputType' => CheckboxX::INPUT_CHECKBOX,
        'autoLabel' => true,
        'model' => $profile,
        'attribute' => 'sms_allow',
        'pluginOptions' => [
            'threeState' => false,
            'theme' => 'krajee-flatblue',
            'enclosedLabel' => false,
            'size' => 'md',
        ],
        'labelSettings' => [
            'label' => 'Разрешить СМС уведомление',
            'position' => CheckboxX::LABEL_RIGHT,
            'options' => ['style' => '']
        ]
    ])->label(false);
    ?>

        <?= $form->field($user, 'role_id')->dropDownList(Role::dropdown(common\models\Organization::TYPE_FRANCHISEE)) ?>

    <div class="form-group">
    <?= Html::submitButton($user->isNewRecord ? 'Create' : 'Update', ['class' => $user->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>
</div>