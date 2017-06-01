<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'login-form',
            'enableAjaxValidation' => false,
            'validateOnSubmit' => false,
            'action' => Url::to(['/user/login']),
            'options' => [
                'class' => 'auth-sidebar__form form-check auth js-auth',
            ],
        ]);
?>    
<div class="auth-sidebar__form-brims">
    <label>
        <?=
        Html::activeTextInput($model, 'email', ['class' => 'form-control', 'placeholder' => 'Email']);
        ?><i class="fa fa-user"></i>
    </label>
    <label>
        <?=
        Html::activePasswordInput($model, 'password', ['class' => 'form-control', 'placeholder' => 'Пароль']);
        ?><i class="fa fa-lock"></i>
    </label>
</div>
<button type="submit" class="but but_green"><span>Войти</span><i class="ico"></i></button><a href="#" class="reestablish-link">Восстановить пароль</a>
<div class="auth-sidebar__enter"><span>Нет аккаунта?</span><a href="#" class="go-to-reg">зарегистрироваться</a></div>
<?php ActiveForm::end(); ?>
