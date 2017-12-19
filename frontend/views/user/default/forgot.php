<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = Yii::t('user', 'Forgot password');

$this->registerJs(
        '
            $("#forgot-form").on("afterValidate", function (event, messages, errorAttributes) {
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
            $("#forgot-form").on("beforeValidate", function (e) {
                $("input").tooltip("destroy");
            });            
            $("#forgot-form").on("submit", function (e) {
                $("#btnSend").button("loading");
                return true;
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
if ($flash = Yii::$app->session->getFlash('Forgot-success')) {
    echo "success";
}
?>">
         <?php if ($flash): ?>
        <div class="success-message"><a href="<?= Yii::$app->homeUrl; ?>" class="success-message__ico"></a>
            <div class="success-message__text">
                <?= $flash ?>
            </div>
        </div>
    <?php else: ?>
        <div class="auth-sidebar h-fx_center auth">
            <button type="button" class="call-menu-but visible-xs visible-sm visible-md"><span></span><span></span><span></span></button>
            <div class="auth-sidebar__content">
                <div class="auth-sidebar__logo"><a href="<?= Yii::$app->homeUrl; ?>"><img src="/images/tmp_file/logo.png" alt=""></a></div>

                <div class="auth-sidebar__annotation">Введите email,<br>который вы использовали при регистрации</div>
                <?php
                $form = ActiveForm::begin([
                            'id' => 'forgot-form',
                            'enableAjaxValidation' => true,
                            'validateOnSubmit' => true,
                            'action' => Url::to(['/user/forgot']),
                            'options' => [
                                'class' => 'auth-sidebar__form form-check auth js-auth',
                            ],
                            'fieldConfig' => ['template' => '{input}'],
                ]);
                ?>    
                <div class="auth-sidebar__form-brims">
                    <label>
                        <?=
                                $form->field($model, 'email')
                                ->label(false)
                                ->textInput(['class' => 'form-control', 'placeholder' => 'Email']);
                        ?><i class="fa fa-user"></i>
                    </label>
                </div>
                <button type="submit" id="btnSend" class="but but_green" data-loading-text="<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Отправляем..."><span>Отправить</span><i class="ico"></i></button>
                    <?= Html::a("Войти / зарегистрироваться", ["/user/login"], ["class" => "reestablish-link"]) ?>
                <?php ActiveForm::end(); ?>
                <div class="auth-sidebar__contacts">
                    <div class="auth-sidebar__contacts-item"><i class="fa fa-phone"></i><a href="tel:84994041018">8-499-404-10-18</a></div>
                    <div class="auth-sidebar__contacts-item"><i class="fa fa-envelope-o"></i><a href="mailto:info@mixcart.ru">info@mixcart.ru</a></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <div class="present-wrapper">
        <button type="button" class="close-menu-but visible-xs visible-sm visible-md"><span></span><span></span></button>
        <h1>Онлайн-сервис для автоматизации закупок</h1>
        <div class="present__media clearfix">
            <div class="present__image"><img src="/images/tmp_file/flowers.png" alt=""></div>
<!--            <a href="#" class="appstore"><img src="images/tmp_file/appstore.png" alt=""></a>
            <a href="#" class="gplay"><img src="images/tmp_file/gplay.png" alt=""></a>-->
        </div>
    </div>
</div>