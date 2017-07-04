<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = "Вход / регистрация";

$this->registerJs(
        '
            $("#login-form").on("afterValidate", function (event, messages, errorAttributes) {
                for (var input in messages) {
                    if (messages[input] != "") {
                        $("#" + input).tooltip({title: messages[input], placement: "auto right", container: "body"});
                        $("#" + input).tooltip();
                        $("#" + input).tooltip("show");
                        break;
                    }
                }
            });
            $("#register-form").on("afterValidate", function (event, messages, errorAttributes) {
                for (var input in messages) {
                    if ((input != "organization-type_id") && (messages[input] != "")) {
                        $("#" + input).tooltip({title: messages[input], placement: "auto right", container: "body"});
                        $("#" + input).tooltip();
                        $("#" + input).tooltip("show");
                        break;
                    }
                }
                $("#btnRegister").button("reset");
            });
            $("#register-form").on("beforeValidate", function (e) {
                $("input").tooltip("destroy");
            });            
            $("#login-form").on("beforeValidate", function (e) {
                $("input").tooltip("destroy");
            });            
            $("#register-form").on("submit", function (e) {
                $("#btnRegister").button("loading");
                return true;
            });            
            $(".form-slider").slick({arrows:!1,dots:!1,swipe:!1,infinite:!1,fade:!0,adaptiveHeight:true});'.
            ($registerFirst ? (
            '$(".go-to-reg").on("click",function(e){
                e.preventDefault(),$(".form-slider").slick("slickPrev")
            });
            $(".auth-sidebar__enter.reg a").on("click",function(e){
                e.preventDefault(),$(".form-slider").slick("slickNext")
            });'
            ) : (
            '$(".go-to-reg").on("click",function(e){
                e.preventDefault(),$(".form-slider").slick("slickNext")
            });
            $(".auth-sidebar__enter.reg a").on("click",function(e){
                e.preventDefault(),$(".form-slider").slick("slickPrev")
            });'
            )).
            '$(".call-menu-but").on("click",function(){
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
<div class="main-page-wrapper">
    <div class="auth-sidebar h-fx_center auth">
        <button type="button" class="call-menu-but visible-xs visible-sm visible-md"><span></span><span></span><span></span></button>
        <div class="auth-sidebar__content">
            <div class="auth-sidebar__logo"><a href="<?= Yii::$app->homeUrl; ?>"><img src="images/tmp_file/logo.png" alt=""></a></div>
            <div class="auth-sidebar__annotation">Заполните поля<br>для входа в систему</div>
            <div class="form-slider">
                <?= $registerFirst ? $this->render('_register-form', compact('user', 'profile', 'organization')) : $this->render('_login-form', compact('model')) ?>
                <?= $registerFirst ? $this->render('_login-form', compact('model')) : $this->render('_register-form', compact('user', 'profile', 'organization')) ?>
            </div>
            <div class="auth-sidebar__contacts">
                <div class="auth-sidebar__contacts-item"><i class="fa fa-phone"></i><a href="tel:84994041018">8-499-404-10-18</a></div>
                <div class="auth-sidebar__contacts-item"><i class="fa fa-envelope-o"></i><a href="mailto:info@f-keeper.ru">info@f-keeper.ru</a></div>
            </div>
        </div>
    </div>
    <div class="present-wrapper">
        <button type="button" class="close-menu-but visible-xs visible-sm visible-md"><span></span><span></span></button>
        <h1>Онлайн-сервис для автоматизации закупок в сфере HoReCa</h1>
        <div class="present__media clearfix">
            <div class="present__image"><img src="images/tmp_file/flowers.png" alt=""></div><a href="#" class="appstore"><img src="images/tmp_file/appstore.png" alt=""></a><a href="#" class="gplay"><img src="images/tmp_file/gplay.png" alt=""></a>
        </div>
    </div>
</div>