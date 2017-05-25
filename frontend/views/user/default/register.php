<?php

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\Module $module
 * @var common\models\User $user
 * @var common\models\Profile $profile
 * @var common\models\Organization $organization
 * @var string $userDisplayName
 */
use yii\helpers\Html;

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

                <?php
            else:
                ?>
                    <?= $this->render('_register-form', compact("user", "profile", "organization")) ?>
                <div class="regist">
                <?= Html::a(Yii::t("user", "Login"), ["/user/login"]) ?>
                </div>
            <?php
            endif;
            ?>
        </div>
    </div>

</div>