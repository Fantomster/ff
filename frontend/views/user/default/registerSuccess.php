<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var amnah\yii2\user\models\forms\LoginForm $model
 */
$this->title = "Успешная регистрация!";
?>

<div class="main-page-wrapper success">

    <div class="success-message"><a href="<?= Yii::$app->homeUrl; ?>" class="success-message__ico"></a>
        <div class="success-message__text">Вы успешно зарегистрировались в системе <a href="<?= Yii::$app->homeUrl; ?>">f-keeper</a><br>На указанную Вами почту <b><?= $user->email ?></b> было выслано письмо с подтверждением.<br>Для продолжения работы в системе пройдите по ссылке в письме.</div>
    </div>
    <div class="present-wrapper">
        <button type="button" class="close-menu-but visible-xs visible-sm visible-md"><span></span><span></span></button>
        <h1>Онлайн-сервис для автоматизации закупок в сфере HoReCa</h1>
        <div class="present__media clearfix">
            <div class="present__image"><img src="images/tmp_file/flowers.png" alt=""></div><a href="#" class="appstore"><img src="images/tmp_file/appstore.png" alt=""></a><a href="#" class="gplay"><img src="images/tmp_file/gplay.png" alt=""></a>
        </div>
    </div>
</div>