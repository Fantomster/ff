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
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="images/logo-fk-white.png" alt=""/></a>
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
                        $form->field($model, 'email')
                        ->label(false)
                        ->textInput(['class' => 'form-control', 'placeholder' => 'E-mail']);
                ?>
                <?=
                        $form->field($model, 'password')
                        ->label(false)
                        ->passwordInput(['class' => 'form-control', 'placeholder' => 'Пароль'])
                ?>
            </div>
            <?=
            Html::a('Войти в личный кабинет', '#', [
                'data' => [
                    'method' => 'post',
                ],
                'class' => 'send__btn',
            ])
            ?>
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
            <div class="regist">
                <?= Html::a("Восcтановить пароль", ["/user/forgot"], ['class' => 'small-login']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>