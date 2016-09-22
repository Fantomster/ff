<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\ResendForm $model
 */
$this->title = Yii::t('user', 'Resend');
?>
<div class="login__block">
    <div class="login__inside">
        <img src="/images/login-logo.png" alt=""/>
        <div class="contact__form">
            <?php if ($flash = Yii::$app->session->getFlash('Resend-success')): ?>

                <div class="alert alert-success">
                    <p><?= $flash ?></p>
                </div>

            <?php else: ?>

                <?php $form = ActiveForm::begin(['id' => 'resend-form']); ?>
                <div class="form-group">
                    <?=
                            $form->field($model, 'email')
                            ->label(false)
                            ->textInput(['class' => 'form-control', 'placeholder' => 'email']);
                    ?>
                </div>
                <?=
                Html::a(Yii::t('user', 'Submit'), '#', [
                    'data' => [
                        'method' => 'post',
                    ],
                    'class' => 'send__btn',
                ])
                ?>
                <?php ActiveForm::end(); ?>

            <?php endif; ?>
        </div>
    </div>

</div>