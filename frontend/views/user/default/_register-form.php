<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\OrganizationType;
use kartik\select2\Select2;

$form = ActiveForm::begin([
    'id' => 'register-form',
    'enableAjaxValidation' => true,
    'action' => yii\helpers\Url::to(['/user/register']),
    ]);
?>
<div class="form-group">
    <?=
    $form->field($organization, 'type_id')->widget(
    Select2::className(),
            [
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
            $form->field($user, 'email')
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'email'])
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
            $form->field($user, 'newPassword')
            ->label(false)
            ->passwordInput(['class' => 'form-control', 'placeholder' => 'пароль'])
    ?>
</div>
<?=
Html::a('Зарегистрироваться', '#', [
    'data' => [
        'method' => 'post',
    ],
    'class' => 'send__btn',
])
?>
<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
<?php ActiveForm::end(); ?>
