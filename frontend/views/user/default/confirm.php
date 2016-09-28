<?php

use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var bool $success
 * @var string $email
 */
$this->title = Yii::t('user', $success ? 'Confirmed' : 'Error');
?>
<div class="login__block">
    <div class="login__inside">
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/logo-inner.png" alt=""/></a>
        <div class="contact__form">
            <?php if ($success): ?>

                <div class="alert alert-success">

                    <p><?= Yii::t("user", "Your email [ {email} ] has been confirmed", ["email" => $email]) ?></p>

                    <?php if (Yii::$app->user->isLoggedIn): ?>

                        <p><?= Html::a(Yii::t("user", "Go to my account"), ["/user/login"]) ?></p>
                        <p><?= Html::a(Yii::t("user", "Go home"), Yii::$app->getHomeUrl()) ?></p>

                    <?php else: ?>

                        <p><?= Html::a(Yii::t("user", "Log in here"), ["/user/login"]) ?></p>

                    <?php endif; ?>

                </div>

            <?php elseif ($email): ?>

                <div class="alert alert-danger">[ <?= $email ?> ] <?= Yii::t("user", "Email is already active") ?></div>

            <?php else: ?>

                <div class="alert alert-danger"><?= Yii::t("user", "Invalid token") ?></div>

            <?php endif; ?>
        </div>
    </div>

</div>