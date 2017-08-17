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
<style>
  @media (min-width: 768px) {
    ul.nav li.dropdown:hover ul.dropdown-menu{
    display: block;    
    }
  }
  @media (max-width: 767px) {
    ul.dropdown-menu {
        position: relative;
        top: 100%;
        left: 0;
        z-index: 1000;
        display: block;
        float: none; 
        min-width: 160px;
        padding: 5px 0;
        margin: 2px 0 0;
        font-size: 14px;
        list-style: none;
        background-color: none;
        background:none;
        border: none;
        box-shadow: none;
    }
    li.dropdown a span.caret{
        display:none;
    }
    .dropdown-menu > li > a:hover, .dropdown-menu > li > a:focus {
        color: #fff;
        text-decoration: none;
        background-color: none;
        background:none;
    }
    .dropdown-menu > li > a {
        text-align: center;
        color:#fff;
        font-size: 12px;
        font-family: "HelveticaBold",Arial,sans-serif;
    }
  }
  @media (min-width: 768px) {
    .navbar-inverse .navbar-nav li:nth-child(3) a{padding-bottom:6px;
    font-size: 12px;
    font-family: "HelveticaBold",Arial,sans-serif;}
    .navbar-inverse .navbar-nav li:nth-child(3) a:hover{      
        border:none;
    }
  }
  #locHeader{
    font-size: 19px;
    color: #84bf76;
    position: absolute;
    margin-top: 20px;
    margin-left: 5px;
    line-height: 18px;
    border-bottom: 1px dotted;    
  }
</style>
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
            <div id="navbar6" class="navbar-collapse collapse"><span id="locHeader" style="cursor:pointer"><?=Yii::$app->session->get('locality')?></span>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="<?= Url::to(['site/restaurants']) ?>">РЕСТОРАНЫ</a></li>
                    <li><a href="<?= Url::to(['site/suppliers']) ?>">ПОСТАВЩИКИ</a></li>
                    <li class="dropdown">
                        <a href="<?= Yii::$app->urlManagerFrontend->createUrl(['site/index']); ?>" class="dropdown-toggle">F-KEEPER <span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href="<?= Yii::$app->urlManagerFrontend->createUrl(['site/about']) ?>">О&nbsp;нас</a></li>
                            <li><a href="<?= Yii::$app->urlManagerFrontend->createUrl(['site/contacts']) ?>">Контакты</a></li>
                        </ul>
                      </li>
                    
                    <?php if (Yii::$app->user->isGuest) { ?>
                        <li><a class="btn-navbar" href="<?= Url::to(['/user/login']) ?>">войти / регистрация</a></li>
                    <?php } else { ?>
                        <?php if ($organization->type_id == Organization::TYPE_RESTAURANT) { ?>
                            <li>
                                <a href="<?= Yii::$app->urlManagerFrontend->createUrl(['order/checkout']) ?>">
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
<?php 
//\frontend\assets\GoogleMapsAsset::register($this);
if (!(Yii::$app->session->get('locality') || Yii::$app->session->get('country'))) {
$this->registerJs("
  $(\"#data-modal\").length>0&&$(\"#data-modal\").modal({backdrop: \"static\", keyboard: false});
",yii\web\View::POS_END);    
}
?>
<?php
\market\assets\GoogleMapsAsset::register($this);
echo $this->render("../site/main/_userLocation");
$userLocation = Url::to(['/site/location-user']);
$customJs = <<< JS
$(document).on("click","#locHeader", function () { 
    $("#data-modal").length>0&&$("#data-modal").modal({backdrop: "static", keyboard: false});
});       
JS;
$this->registerJs($customJs, View::POS_READY);
?>
