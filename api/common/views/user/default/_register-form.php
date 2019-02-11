<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\OrganizationType;
use kartik\select2\Select2;
use nirvana\showloading\ShowLoadingAsset;
use kartik\checkbox\CheckboxX;
kartik\checkbox\KrajeeFlatBlueThemeAsset::register($this);

ShowLoadingAsset::register($this);
$this->registerJs(
        '$("document").ready(function(){
            $("#register-form").on("submit", function(e) {
                $("#loader-show").showLoading();
            });
        });'
);
$this->registerCss('#loader-show {position:absolute;width:100%;height:100%;display:none}');

$form = ActiveForm::begin([
            'id' => 'register-form',
            'enableAjaxValidation' => true,
            'action' => yii\helpers\Url::to(['/user/register']),
            'validateOnSubmit' => false,
        ]);
?>
<div class="form-group">
    <?=
    $form->field($organization, 'type_id')->widget(
            Select2::className(), [
        'model' => $organization,
        'attribute' => 'type_id',
        'hideSearch' => true,
        'data' => OrganizationType::getList(),
        'theme' => Select2::THEME_BOOTSTRAP,
        'options' => [
            'placeholder' => 'Выберите тип бизнеса',
            'class' => 'form-control',
        ],
        'pluginOptions' => [
            'allowClear' => false,
        ],
            ]
    )->label(false);
//    Select2::widget([
//        'model' => $organization,
//        'attribute' => 'type_id',
//        'hideSearch' => true,
//        'data' => OrganizationType::getList(),
//        'theme' => Select2::THEME_BOOTSTRAP,
//        'options' => [
//            'placeholder' => 'Выберите тип бизнеса',
//            'class' => 'form-control',
//        ],
//        'pluginOptions' => [
//            'allowClear' => false,
//        ],
//    ]);
//                            $form->field($organization, 'type_id')
//                            ->label(false)
//                            ->dropDownList(OrganizationType::getList(), [
//                                'prompt' => 'Выберите тип бизнеса',
//                                'class' => 'form-control'])
    ?>
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
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'телефон'])
    ?>
    
    <?=$form->field($profile, 'sms_allow')->widget(CheckboxX::classname(), [
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
        ])->label(false);?>
    
    <?=
            $form->field($user, 'email')
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'email'])
    ?>
    <?=
            $form->field($user, 'newPassword')
            ->label(false)
            ->passwordInput(['class' => 'form-control', 'placeholder' => 'пароль'])
    ?>
</div>
<?=
Html::a('Зарегистрироваться', '#', [
    'id' => 'btnRegister',
    'data' => [
        'method' => 'post',
    ],
    'class' => 'send__btn',
])
?>
<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
<?php ActiveForm::end(); ?>
