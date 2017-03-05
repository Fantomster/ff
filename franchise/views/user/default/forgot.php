<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\ForgotForm $model
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = Yii::t('user', 'Forgot password');
?>
<div class="login__block">
    <div class="login__inside">
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/logo-fk-white.png" alt=""/></a>
        <div class="contact__form">
            <?php if ($flash = Yii::$app->session->getFlash('Forgot-success')): ?>

                <div class="alert alert-success">
                    <p><?= $flash ?></p>
                </div>

            <?php else: ?>

                <?php $form = ActiveForm::begin(['id' => 'forgot-form', 'validateOnSubmit' => false,]); ?>
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
            <input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;" tabindex="-1" />
                <?php ActiveForm::end(); ?>

            <?php endif; ?>
        </div>
    </div>

</div>