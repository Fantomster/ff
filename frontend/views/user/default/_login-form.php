<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            'id' => 'login-form',
            'enableAjaxValidation' => true,
            'validateOnSubmit' => true,
            'enableClientValidation' => true,
            'action' => Url::to(['/user/login']),
            'options' => [
                'class' => 'auth-sidebar__form form-check auth js-auth',
            ],
            'fieldConfig' => ['template' => '{input}'],
        ]);
?>    
<input type="hidden" name="_csrf-fk" value="<?=Yii::$app->request->getCsrfToken()?>" />
<div class="auth-sidebar__form-brims">
    <label>
        <?=
                $form->field($model, 'email')
                ->label(false)
                ->textInput(['class' => 'form-control', 'placeholder' => 'Email']);
        ?>
        <i class="fa fa-user"></i>
    </label>
    <label>
        <?=
                $form->field($model, 'password')
                ->label(false)
                ->passwordInput(['class' => 'form-control', 'placeholder' => 'Пароль'])
        ?>
        <i class="fa fa-lock"></i>
    </label>
</div>
<button type="submit" class="but but_green"><span>Войти</span><i class="ico"></i></button>
<?= Html::a("Восстановить пароль", ["/user/forgot"], ["class" => "reestablish-link"]) ?>
<div class="auth-sidebar__enter"><span>Нет аккаунта?</span><a href="#" class="go-to-reg">зарегистрироваться</a></div>
<?php ActiveForm::end(); ?>
