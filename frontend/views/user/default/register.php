<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\OrganizationType;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\Module $module
 * @var common\models\User $user
 * @var common\models\Profile $profile
 * @var common\models\Organization $organization
 * @var string $userDisplayName
 */
$module = $this->context->module;
$this->title = Yii::t('user', 'Register');
?>
<div class="login__block">
    <div class="login__inside">
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/logo-inner.png" alt=""/></a>
        <div class="contact__form">
            <?php if ($flash = Yii::$app->session->getFlash("Register-success")): ?>

                <div class="alert alert-success">
                    <p><?= $flash ?></p>
                </div>

            <?php else: ?>
                <?php
                $form = ActiveForm::begin(['id' => 'login-form']);
                ?>
                <div class="form-group">
                    <?=
                            $form->field($organization, 'type_id')
                            ->label(false)
                            ->dropDownList(OrganizationType::getList(), [
                                'prompt' => 'Выберите тип бизнеса',
                                'class' => 'form-control'])
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
                <div class="regist">
                    <?= Html::a(Yii::t("user", "Login"), ["/user/login"]) ?>
                </div>
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        </div>
    </div>

</div>