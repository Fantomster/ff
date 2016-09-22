<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = Yii::t('user', 'Login');
?>
<div class="login__block">
    <div class="login__inside">
        <img src="/images/login-logo.png" alt=""/>
        <div class="contact__form">
            <?php
            $form = ActiveForm::begin(['id' => 'login-form']);
            ?>
            <div class="form-group">
                <?=
                        $form->field($model, 'email')
                        ->label(false)
                        ->textInput(['class' => 'form-control', 'placeholder' => 'email']);
                ?>
                <?=
                        $form->field($model, 'password')
                        ->label(false)
                        ->passwordInput(['class' => 'form-control', 'placeholder' => 'пароль'])
                ?>
            </div>
            <?=
            Html::a('войти в f-keeper', '#', [
                'data' => [
                    'method' => 'post',
                ],
                'class' => 'send__btn',
            ])
            ?>
            <div class="regist">
<!--                <a href="#">Зарегистрироваться</a>
                <a href="#">Забыли пароль?</a>-->
            <?= Html::a(Yii::t("user", "Register"), ["/user/register"]) ?>
            <?= Html::a(Yii::t("user", "Forgot password") . "?", ["/user/forgot"]) ?>
            <?= Html::a(Yii::t("user", "Resend confirmation email"), ["/user/resend"]) ?>
            </div>
<?php ActiveForm::end(); ?>
        </div>
    </div>

</div>