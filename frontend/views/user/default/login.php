<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = "Вход / регистрация";
?>

<div class="auth-sidebar h-fx_center auth">
    <button type="button" class="call-menu-but visible-xs visible-sm visible-md"><span></span><span></span><span></span></button>
    <div class="auth-sidebar__content">
        <div class="auth-sidebar__logo"><img src="images/tmp_file/logo.png" alt=""></div>
        <div class="auth-sidebar__annotation">Заполните поля<br>для входа в систему</div>
        <div class="form-slider">
            <?= $this->render('_login-form', compact('model')) ?>
            <?= $this->render('_register-form', compact('user', 'profile', 'organization')) ?>
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
