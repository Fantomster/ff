<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use kartik\checkbox\CheckboxX;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($profile, 'full_name') ?>

    <?= $form->field($profile, 'phone')->widget(\common\widgets\PhoneInput::className(), [
        'jsOptions' => [
            'preferredCountries' => ['ru'],
            'nationalMode' => false,
            'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input') . '/build/js/utils.js',
        ],
        'options' => [
            'class' => 'form-control',
        ],
    ]) ?>

    <?=
    $form->field($user, 'status')->widget(CheckboxX::classname(), [
        //'initInputType' => CheckboxX::INPUT_CHECKBOX,
        'autoLabel' => true,
        'model' => $user,
        'attribute' => 'status',
        'pluginOptions' => [
            'threeState' => false,
            'theme' => 'krajee-flatblue',
            'enclosedLabel' => false,
            'size' => 'md',
        ],
        'labelSettings' => [
            'label' => 'Активен',
            'position' => CheckboxX::LABEL_RIGHT,
            'options' => ['style' => '']
        ]
    ])->label(false);
    ?>

    <?= $user->role_id === common\models\Role::ROLE_FKEEPER_MANAGER ? $form->field($user, 'organization_id')->textInput(['maxlength' => true]) : '' ?>

    <div class="form-group">
        <?= Html::submitButton($user->isNewRecord ? 'Create' : 'Update', ['class' => $user->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
