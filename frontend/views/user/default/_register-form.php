<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Organization;
use nirvana\showloading\ShowLoadingAsset;

ShowLoadingAsset::register($this);
//$this->registerJs(
//        '$("document").ready(function(){
//            $("#register-form").on("submit", function(e) {
//                $("#loader-show").showLoading();
//            });
//        });'
//);
$this->registerCss(
        '
            .intl-tel-input.allow-dropdown input, .intl-tel-input.allow-dropdown input[type=text] {
                padding-left: 62px;
            }
            .intl-tel-input.allow-dropdown .flag-container {
                padding-bottom: 7px;
                padding-left: 15px;
            }
        '
);
$this->registerCss('#loader-show {position:absolute;width:100%;height:100%;display:none}');

$form = ActiveForm::begin([
            'id' => 'register-form',
            'enableAjaxValidation' => true,
            'action' => yii\helpers\Url::to(['/user/register']),
            'validateOnSubmit' => true,
            'options' => [
                'class' => 'auth-sidebar__form form-check reg js-reg',
            ],
        ]);
?>
<input type="email" name="fake_email" style="position: absolute; top: -100%;">
<input type="password" name="fake_pwd" style="position: absolute; top: -100%;">
<div class="auth-sidebar__form-radios">
<!--    <label>
        <input type="radio" name="buy" checked><i class="radio-ico"></i><span>Я покупаю</span>
    </label>
    <label>
        <input type="radio" name="buy"><i class="radio-ico"></i><span>Я продаю</span>
    </label>-->
    <?=
            $form->field($organization, 'type_id')
            ->radioList(
                    [Organization::TYPE_RESTAURANT => 'Я покупаю', Organization::TYPE_SUPPLIER => 'Я продаю'], 
                    [
                        'item' => function($index, $label, $name, $checked, $value) {

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
<button type="submit" class="but but_green"><span>Зарегистрироваться</span><i class="ico"></i></button>
<div class="auth-sidebar__enter reg"><span>Уже зарегистрированы?</span><a href="#">войти в систему</a></div>
<?php ActiveForm::end(); ?>
