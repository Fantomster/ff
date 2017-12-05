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
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/logo-fk-white.png" alt=""/></a>
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
                        ->passwordInput(['class' => 'form-control', 'placeholder' => Yii::t('app', 'franchise.views.site.user.default.password', ['ru'=>'Пароль'])])
                ?>
            </div>
            <?=
            Html::a(Yii::t('app', 'franchise.views.site.user.default.enter_personal', ['ru'=>'Войти в личный кабинет']), '#', [
                'data' => [
                    'method' => 'post',
                ],
                'class' => 'send__btn',
            ])
            ?>
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
            <div class="regist">
                <?= Html::a(Yii::t('app', 'franchise.views.site.user.default.pass', ['ru'=>"Восcтановить пароль"]), ["/user/forgot"], ['class' => 'small-login']) ?>
            </div>
            <div class="header-nav default pull-right">
                <ul style="list-style-type:none" >
                    <?=\common\widgets\LangSwitch::widget();?>
                </ul>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>