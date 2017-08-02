<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = Yii::t('user', 'Reset');

$this->registerJs(
        '
            $("#reset-form").on("afterValidate", function (event, messages, errorAttributes) {
                for (var input in messages) {
                    if (messages[input] != "") {
                        $("#" + input).tooltip({title: messages[input], placement: "auto right", container: "body"});
                        $("#" + input).tooltip();
                        $("#" + input).tooltip("show");
                        break;
                    }
                }
                $("#btnSend").button("reset");
            });
            $("#reset-form").on("beforeValidate", function (e) {
                $("input").tooltip("destroy");
            });            
            $(".call-menu-but").on("click",function(){
                $(".present-wrapper").addClass("active"),$("body").addClass("over_hidden")
            });
            $(".close-menu-but").on("click",function(){
                $(this).removeClass("active"),$(".present-wrapper").removeClass("active"),$("body").removeClass("over_hidden")
            });
            $(window).on("resize",function(){
                $("body").outerHeight()<758?$(".auth-sidebar__contacts").addClass("low-margin"):$(".auth-sidebar__contacts").removeClass("low-margin")
            });
        '
);
$this->registerCss(
        '
            .intl-tel-input.allow-dropdown input, .intl-tel-input.allow-dropdown input[type=text] {
                padding-left: 62px;
            }
            .intl-tel-input.allow-dropdown .flag-container {
                padding-bottom: 7px;
                padding-left: 15px;
            }
            #loader-show {position:absolute;width:100%;height:100%;display:none}
        '
);
?>
<div class="main-page-wrapper <?php
if (!empty($success) || !empty($invalidToken)) {
    echo "success";
}
?>">
<?php if (!empty($success)): ?>
        <div class="success-message"><a href="<?= Yii::$app->homeUrl; ?>" class="success-message__ico"></a>
            <div class="success-message__text">
                <p><?= Yii::t("user", "Password has been reset") ?></p>
                <p><?= Html::a(Yii::t("user", "Log in here"), ["/user/login"]) ?></p>
            </div>
        </div>

<?php elseif (!empty($invalidToken)): ?>

        <div class="success-message"><a href="<?= Yii::$app->homeUrl; ?>" class="success-message__ico"></a>
            <div class="success-message__text">
                <p>Вход по данной разовой ссылке заблокирован. Вы можете зайти под своим логином и паролем, либо запросить свой пароль на почту</p>
            </div>
        </div>

<?php else: ?>
        <div class="auth-sidebar h-fx_center auth">
            <button type="button" class="call-menu-but visible-xs visible-sm visible-md"><span></span><span></span><span></span></button>
            <div class="auth-sidebar__content">
                <div class="auth-sidebar__logo"><a href="<?= Yii::$app->homeUrl; ?>"><img src="images/tmp_file/logo.png" alt=""></a></div>

                <div class="auth-sidebar__annotation">Введите новый пароль</div>
                <?php
                $form = ActiveForm::begin([
                            'id' => 'reset-form',
                            'enableAjaxValidation' => true,
                            'validateOnSubmit' => true,
                            'options' => [
                                'class' => 'auth-sidebar__form form-check auth js-auth',
                            ],
                            'fieldConfig' => ['template' => '{input}'],
                ]);
                ?>    
                <div class="auth-sidebar__form-brims">
                    <label>
                        <?=
                                $form->field($user, 'newPassword')
                                ->label(false)
                                ->passwordInput(['class' => 'form-control', 'placeholder' => 'Пароль'])
                        ?>
                        <i class="fa fa-lock"></i>
                    </label>
                    <label>
    <?=
            $form->field($user, 'newPasswordConfirm')
            ->label(false)
            ->passwordInput(['class' => 'form-control', 'placeholder' => 'Повторите пароль'])
    ?>
                        <i class="fa fa-lock"></i>
                    </label>
                </div>
                <button type="submit" id="btnSend" class="but but_green"><span><?= Yii::t('user', 'Reset') ?></span><i class="ico"></i></button>
    <?php ActiveForm::end(); ?>
                <div class="auth-sidebar__contacts">
                    <div class="auth-sidebar__contacts-item"><i class="fa fa-phone"></i><a href="tel:84994041018">8-499-404-10-18</a></div>
                    <div class="auth-sidebar__contacts-item"><i class="fa fa-envelope-o"></i><a href="mailto:info@f-keeper.ru">info@f-keeper.ru</a></div>
                </div>
            </div>
        </div>
<?php endif; ?>
    <div class="present-wrapper">
        <button type="button" class="close-menu-but visible-xs visible-sm visible-md"><span></span><span></span></button>
        <h1>Онлайн-сервис для автоматизации закупок</h1>
        <div class="present__media clearfix">
            <div class="present__image"><img src="images/tmp_file/flowers.png" alt=""></div>
<!--            <a href="#" class="appstore"><img src="images/tmp_file/appstore.png" alt=""></a>
            <a href="#" class="gplay"><img src="images/tmp_file/gplay.png" alt=""></a>-->
        </div>
    </div>
</div>