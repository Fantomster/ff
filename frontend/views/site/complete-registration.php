<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\checkbox\CheckboxX;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = "Завершение регистрации";
?>
<div class="login__block">
    <div class="login__inside">
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/logo-inner.png" alt=""/></a>
        <div class="contact__form">
            <?php
            $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'enableAjaxValidation' => false,
                        'validateOnSubmit' => false,
            ]);
            ?>
            <div class="form-group">
    <?=
            $form->field($organization, 'name')
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'название организации'])
    ?>
    <?=
            $form->field($profile, 'full_name')
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'фио'])
    ?>
    <?=
            $form->field($profile, 'phone')
            ->widget(\yii\widgets\MaskedInput::className(), ['mask' => '+7 (999) 999 99 99',])
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'телефон'])
    ?>
    
    <?=
                    $form->field($profile, 'sms_allow')->widget(CheckboxX::classname(), [
        //'initInputType' => CheckboxX::INPUT_CHECKBOX,
        'autoLabel' => true,
        'model' => $profile,
        'attribute' => 'sms_allow',
        'pluginOptions'=>[
            'threeState'=>false,
            'theme' => 'krajee-flatblue',
            'enclosedLabel' => false,
            'size'=>'md',
            ],
        'labelSettings' => [
            'label' => 'Разрешить СМС уведомление',
            'position' => CheckboxX::LABEL_RIGHT,
            'options' =>['style'=>'']
            ]
        ])
                    ->label(false);
            ?>
    <?=
            $form->field($organization, 'city')
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'Город'])
    ?>
    <?=
            $form->field($organization, 'address')
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'Адрес'])
    ?>
    
            </div>
            <?=
            Html::a('Подтвердить', '#', [
                'data' => [
                    'method' => 'post',
                ],
                'class' => 'send__btn',
            ])
            ?>
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>