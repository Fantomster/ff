<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use common\models\Organization;

if (!Yii::$app->user->isGuest) {
    $user = Yii::$app->user->identity;
    $organization = $user->organization;
}
?>
<section>
    <nav class="navbar navbar-inverse navbar-static-top example6 shadow-bottom">
        <div class="container" style="padding: 9px 30px">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar6">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand text-hide" href="<?= Url::home(); ?>">f-keeper</a>
            </div>
            <div id="navbar6" class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="active"><a href="<?= Url::home(); ?>">ГЛАВНАЯ</a></li>
                    <li><a href="http://f-keeper.ru/index.php?r=site%2Fabout">О&nbsp;НАС</a></li>
                    <li><a href="http://f-keeper.ru/index.php?r=site%2Fcontacts">КОНТАКТЫ</a></li>
                    <?php if (Yii::$app->user->isGuest) { ?>
                        <li><a class="btn-navbar" href="<?= Url::to(['/user/login']) ?>">войти / регистрация</a></li>
                    <?php } else { ?>
                        <?php if ($organization->type_id == Organization::TYPE_RESTAURANT) { ?>
                            <li>
                                <a href="http://f-keeper.ru/index.php?r=order/checkout">
                                    КОРЗИНА <sup><span class="badge cartCount"><?= $organization->getCartCount() ?></span></sup>
                                </a>
                            </li>
                        <?php } ?>
                        <li><a class="btn-navbar" href="<?= Url::to(['/user/logout']) ?>" data-method="post"><?= $user->profile->full_name ?> [выход]</a></li>
                        <?php } ?>
                </ul>
            </div>
            <!--/.nav-collapse -->
        </div>
        <!--/.container -->
    </nav>
</section> 
