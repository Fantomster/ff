<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Organization */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="organization-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'white_list')->checkbox(['maxlength' => true]) ?>

    <?= $form->field($model, 'partnership')->checkBox(['maxlength' => true]) ?>

    <?= $form->field($model, 'legal_entity')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'city')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'zip_code')->textInput(['maxlength' => true]) ?>

    <?= ''//$form->field($model, 'phone')->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])->textInput(['maxlength' => true]) ?>

    <?=
    $form->field($model, 'phone')->widget(\common\widgets\PhoneInput::className(), [
        'jsOptions' => [
            'preferredCountries' => ['ru'],
            'nationalMode' => false,
            'utilsScript' => Yii::$app->assetManager->getPublishedUrl('@bower/intl-tel-input'). '/build/js/utils.js',
        ],
        'options' => [
            'class' => 'form-control',
        ]
    ])
    ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'website')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'contact_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'about')->textarea() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
