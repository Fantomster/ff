<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

frontend\assets\MainAsset::register($this);
$isIndex = ($this->context->id === 'site') && ($this->context->action->id === 'index');
if ($isIndex) {
    $js = <<<JS
function heightDetect() {
		$(".login__block").css("height", $(window).height());
	};
	heightDetect();
	$(window).resize(function() {
		heightDetect();
	});
                $('a[href^="#"]').bind('click.smoothscroll', function (e) {
                    e.preventDefault();

                    var target = this.hash,
                            target1 = $(target);

                    $('html, body').stop().animate({
                        'scrollTop': target1.offset().top
                    }, 900, 'swing', function () {
                        window.location.hash = target;
                    });
                });
                var header = $("#menu-fk");
                headers();
                function headers(e) {
                    if ($(this).scrollTop() > 50 && header.hasClass("default")) {
                        header.fadeOut('fast', function () {
                            $(this).removeClass("default")
                                    .addClass("fixed")
                                    .fadeIn('fast');
                        });
                    } else if ($(this).scrollTop() <= 50 && header.hasClass("fixed")) {
                        header.fadeOut('fast', function () {
                            $(this).removeClass("fixed")
                                    .addClass("default")
                                    .fadeIn('fast');
                        });
                    }
                }
                $(window).scroll(function () {
                    headers();
                });
JS;
} 
$this->registerJs($js, \yii\web\View::POS_READY);
    $js = <<<JS
            $("#menu__burger").click(function () {
                $(".nav_menu").slideToggle();
            });
            
            $(".top-mnu ul li a").click(function() {
                $(".top-mnu").fadeOut(600);
                $(".toggle-mnu").toggleClass("active");
                $(".header-h").css("opacity", "1");
                $(".toggle-mnu").removeClass("on");
            }).append("<span>");

            $(".toggle-mnu").click(function() {
            if ($(".top-mnu").is(":visible")) {
             $(".header-h").css("opacity", "1");
             $(".top-mnu").fadeOut(600);
             $(".top-mnu li a, .top-mnu li span, .phone-email").removeClass("fadeInUp animated");
             $(this).removeClass("on");
            } else {
             $(".header-h").css("opacity", ".1");
             $(".top-mnu").fadeIn(600);
             $(".top-mnu li a, .top-mnu li span, .phone-email").addClass("fadeInUp animated");
             $(this).addClass("on");
            };
         });            
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<?= Html::csrfMetaTags() ?>
        <title><?= Html::encode($this->title) ?></title>
        <link href="css/style.css" rel="stylesheet">
        <link rel="shortcut icon" href="images/favicon/favicon.ico" type="image/x-icon">
<?php $this->head() ?>
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style>	
            .fixed{
                background: #3f3e3e;	
            }
            .header-nav .nav_menu >li.active a {
                border-bottom: 1px solid #fff;
            }
        </style>
    </head>
    <body>	
<?php $this->beginBody() ?>
        <div id="loader-show"></div>

        <div class="wrapper">
            <header class="header-nav default <?= $isIndex ? '' : ' dark-bg' ?>" id="menu-fk">
                <div class="inside__block">
                    <div class="container-fluid">
                        <div class="logo__block">
                            <a class="logo__block-icon" href="<?= Yii::$app->homeUrl; ?>"></a>
                        </div>
                        <div class="phone__block">
                            <span class="phone__block-number">
                                <span class="glyphicon glyphicon-phone"></span><a style="color:#fff;text-decoration:none;" href="tel:84994041018">8-499-404-10-18</a>
                            </span>
                        </div>
                        <div class="registr-block">
                            <div class="registr__block">
<?= Html::a('вход / регистрация', ["/user/login"]) ?>
                            </div>
                        </div>
                        <div class="nav__block">
                            <span id="menu__burger">Меню</span>
<?=
yii\widgets\Menu::widget([
    'options' => ['class' => 'nav_menu'],
    'items' => [
        ['label' => 'Главная', 'url' => ['/site/index']],
        ['label' => 'F-MARKET', 'url' => 'https://market.f-keeper.ru'],
        ['label' => 'Франшиза', 'url' => 'https://partner.f-keeper.ru'],
        ['label' => 'Новости', 'url' => 'http://blog.f-keeper.ru?news'],
        ['label' => 'Вопрос / ответ', 'url' => ['/site/faq']],
        ['label' => 'О компании', 'url' => ['/site/about']],
        ['label' => 'Контакты', 'url' => ['/site/contacts']],
    ]
])
?>
                        </div>
                    </div>
                </div>
            </header><!-- .header-nav-->

<?= $content ?>
            <footer class="footer">
                <div class="inside__block">
                    <div class="container-fluid">

                        <div class="col-md-3 col-sm-3">
                            <div class="footer__menu_block">
                                <span class="title__menu">Карта сайта</span>
                                <ul class="links">
                                    <li><?= Html::a('Новости', "http://blog.f-keeper.ru?news") ?></li>
                                    <li><?= Html::a('Для ресторанов', ["/site/restaurant"]) ?></li>
                                    <li><?= Html::a('Для поставщиков', ["/site/supplier"]) ?></li>
                                    <li><?= Html::a('О компании', ["/site/about"]) ?></li>
                                    <li><?= Html::a('Контакты', ["/site/contacts"]) ?></li>
                                </ul>
                            </div>	
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="footer__menu_block">
                                <span class="title__menu">связаться с нами</span>
                                <ul class="contacts">
                                    <li><span class="phone"><span class="glyphicon glyphicon-phone"></span> 8-499-404-10-18</span></li>
                                    <li><a href="mailto:info@f-keeper.ru"><span class="email"><span class="glyphicon glyphicon-envelope"></span>info@f-keeper.ru</span></a></li>
                                    <li><span class="address"><span class="glyphicon glyphicon-map-marker"></span>Москва ул. Оршанская 5</li>
                                </ul>
                            </div>	
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="footer__menu_block">
                                <span class="title__menu">ФОТО / видео</span>
                                <ul class="links">
                                    <li><a href="#">Фото архив</a></li>
                                    <li><a href="#">Видео архив</a></li>
                                </ul>
                            </div>	
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="footer__menu_block">
                                <span class="title__menu">Вход / регистрация</span>
                                <ul class="links">
                                    <li><?= Html::a('Вход', ["/user/login"]) ?></li>
                                    <li><?= Html::a('Регистрация', ["/user/register"]) ?></li>
                                </ul>
                            </div>	
                        </div>

                        <div class="clear">
                            <div class="bottom__footer">
                                <div class="col-md-4 col-sm-4">
                                    <div class="footer__logo">
                                        <a href="<?= Yii::$app->homeUrl; ?>"></a>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="copy">
                                        <span>© <?= date('Y') ?> F-Keeper — ООО «Онлайн Маркет» </span>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="sot__set_block">
                                        <a href="#"><i class="demo-icon icon-vk">&#xe800;</i></a>
                                        <a href="#"><i class="demo-icon icon-linkedin">&#xe801;</i> </a>
                                        <a href="#"><i class="demo-icon icon-youtube">&#xe802;</i></a>
                                        <a href="#"><i class="demo-icon icon-twitter">&#xe803;</i></a>
                                        <a href="#"><i class="demo-icon icon-facebook">&#xe804;</i></a>
                                    </div>
                                </div>
                            </div>
                        </div>	
                    </div>



            </footer><!-- .footer -->

        </div><!-- .wrapper -->
<?php
echo $this->render('_yandex');
$this->endBody()
?>
        <!-- BEGIN JIVOSITE CODE {literal} -->
<script type='text/javascript'>
(function(){ var widget_id = 'RI0YDaTCe9';
var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);})();</script>
<!-- {/literal} END JIVOSITE CODE -->

    </body>
</html>
<?php $this->endPage() ?>