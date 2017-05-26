<?php
/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\ResendForm $model
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use nirvana\showloading\ShowLoadingAsset;

ShowLoadingAsset::register($this);
$this->registerJs(
        '$("document").ready(function(){
            $("#resend-form").on("submit", function(e) {
                $("#loader-show").showLoading();
            });
        });'
);
$this->registerCss('#loader-show {position:absolute;width:100%;height:100%;display:none}');

$this->title = Yii::t('user', 'Resend');
?>
<div class="login__block">
    <div class="login__inside">
        <a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/logo-inner.png" alt=""/></a>
        <div class="contact__form">
            <?php if ($flash = Yii::$app->session->getFlash('Resend-success')): ?>

                <div class="alert alert-success">
                    <p><?= $flash ?></p>
                </div>

            <?php else: ?>

                <?php $form = ActiveForm::begin(['id' => 'resend-form', 'validateOnSubmit' => false,]); ?>
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