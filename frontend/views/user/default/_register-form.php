<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Organization;

$form = ActiveForm::begin([
            'id' => 'register-form',
            'enableAjaxValidation' => true,
            'action' => yii\helpers\Url::to(['/user/register']),
            'validateOnSubmit' => true,
            'options' => [
                'class' => 'auth-sidebar__form form-check reg js-reg',
            ],
            'fieldConfig' => ['template' => '{input}'],
        ]);
?>
<input type="email" name="fake_email" style="position: absolute; top: -100%;">
<input type="password" name="fake_pwd" style="position: absolute; top: -100%;">
<input type="hidden" name="_csrf-fk" value="<?=Yii::$app->request->getCsrfToken()?>" />
<div class="auth-sidebar__form-radios">
    <?=
            $form->field($organization, 'type_id')
            ->radioList(
                    [Organization::TYPE_RESTAURANT => 'Я покупаю', Organization::TYPE_SUPPLIER => 'Я продаю'], 
                    [
                        'item' => function($index, $label, $name, $checked, $value) use ($organization) {

                            $checked = $checked ? 'checked' : '';
                            $return = '<label class="modal-radio">';
                            $return .= '<input type="radio" name="' . $name . '" value="' . $value . '" '.$checked.'>';
                            $return .= '<i class="radio-ico"></i><span>' . $label . '</span>';
                            $return .= '</label>';

                            return $return;
                        }
                    ]
            )
            ->label(false);
    ?>
</div>
<div class="auth-sidebar__form-brims">
    <label>
        <?=
            $form->field($user, 'email')
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'Email'])
        ?><i class="fa fa-envelope-square"></i>
    </label>
    <label>
<?=
            $form->field($profile, 'phone')
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
            ->label(false)
            ->textInput(['class' => 'form-control', 'placeholder' => 'Телефон'])
    ?>        <i class="fa fa-phone-square"></i>
    </label>
    <label>
        <?=
            $form->field($user, 'newPassword')
            ->label(false)
            ->passwordInput(['class' => 'form-control', 'placeholder' => 'Пароль'])
    ?><i class="fa fa-lock"></i>
    </label>
</div>
<button type="submit" class="but but_green" id="btnRegister" data-loading-text="<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Регистрируемся..."><span>Зарегистрироваться</span><i class="ico"></i></button>
<div class="auth-sidebar__enter reg"><span>Уже зарегистрированы?</span><a href="#">войти в систему</a></div>
<?php ActiveForm::end(); ?>
