<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\User $user
 * @var bool $success
 * @var bool $invalidToken
 */
$this->title = Yii::t('user', 'Reset');
?>
<div class="login__block">
    <div class="login__inside">
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/logo-inner.png" alt=""/></a>
        <div class="contact__form">
    <?php if (!empty($success)): ?>

        <div class="alert alert-success">

            <p><?= Yii::t("user", "Password has been reset") ?></p>
            <p><?= Html::a(Yii::t("user", "Log in here"), ["/user/login"]) ?></p>

        </div>

    <?php elseif (!empty($invalidToken)): ?>

        <div class="alert alert-danger">
            <p>Вход по данной разовой ссылке заблокирован. Вы можете зайти под своим логином и паролем, либо запросить свой пароль на почту</p>
        </div>

    <?php else: ?>


                <div class="alert alert-warning">
                    <p><?= Yii::t("user", "Email") ?> [ <?= $user->email ?> ]</p>
                </div>

                <?php $form = ActiveForm::begin(['id' => 'reset-form']); ?>

                    <div class="form-group">
                    <?= $form->field($user, 'newPassword')
                        ->label(false)
                        ->passwordInput(['class' => 'form-control', 'placeholder' => 'пароль']) ?>
                    <?= $form->field($user, 'newPasswordConfirm')
                        ->label(false)
                        ->passwordInput(['class' => 'form-control', 'placeholder' => 'повторите пароль']) ?>
                    </div>
                <?=
                Html::a(Yii::t('user', 'Reset'), '#', [
                    'data' => [
                        'method' => 'post',
                    ],
                    'class' => 'send__btn',
                ])
                ?>
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
                <?php ActiveForm::end(); ?>

    <?php endif; ?>
        </div>
    </div>

</div>