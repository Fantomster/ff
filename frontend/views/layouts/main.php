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
    $this->registerJs($js, \yii\web\View::POS_READY);
}
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
        <link href="<?= yii\helpers\Url::base() ?>/css/style.css" rel="stylesheet">
        <link rel="shortcut icon" href="/images/favicon/favicon.ico" type="image/x-icon">
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
            .modal{
                top: 100px;
                z-index: 99999;
            }
            .modal{
                top: 100px;
                z-index: 99999;
            }
            .header_callback_btn{
    color: #6be34f;
    /* display: block; */
    font-size: 13px;
    cursor: pointer;
    text-transform: uppercase;
    margin-top: 8px;
            }
        @media (max-width: 620px){
            .header_callback_btn{
                display: block;
            }
        }
        /* FORM */
.callback .input_text {
  width: 100%;
  padding: 0 10px;
  font-size: 16px;
  height: 50px;
  border: 1px solid #E0E0E0;
  color: #343434;
  @include transition(.3s);
  &:focus {
    border: 1px solid #66BC75; } }
.callback .textarea_form {
  width: 100%;
  padding: 10px;
  border: 1px solid #E0E0E0;
  font-size: 16px;
  resize: none;
  color: #333;
  @include transition(.3s);
  height: 150px;
  &:focus {
    border: 1px solid #66BC75; } }
.callback .input_file {
  display: inline-block;
  position: relative;
  padding-left: 30px;
  cursor: pointer;
  user-select: none;
  background: url("../img/decor_9.png") left center no-repeat;
  user-select: none;
.callback input {
    display: none; }
.callback span {
    font-size: 18px;
    color: #2E8CC7;
    border-bottom: 2px dotted #2E8CC7; } }
.callback .input_btn {
  width: 100%;
  padding: 10px;
  height: 45px;
  font-size: 16px;
  color: #333;
  @include transition(.3s);
  background: #66BC75;
  color: #fff;
  &:hover {
    opacity: .8; } }
.callback .select__wrap {
  position: relative;
  width: 100%;
  font-size: 16px;
  height: 50px;
  border: 1px solid #E0E0E0;
  color: #95989A;
  @include transition(.3s);
  .callback select {
    width: 100%;
    height: 100%;
    border: none;
    background: none;
    font-size: 14px;
    padding: 0 10px;
    color: #95989A; } }
.callback .select__wrap:after {
  content: "";
  position: absolute;
  top: 50%;
  right: 10px;
  margin-top: -2px;
  z-index: 10;
  display: block;
  pointer-events: none;
  border-style: solid;
  border-width: 5px 5px 0 5px;
  border-color: #95989A transparent transparent transparent; }
.callback .contact_us__form__row {
    margin-bottom: 20px;
}

  .send-form-callback:hover {
    background-color: #378a5f;
    text-decoration: none;
}
  .send-form-callback {
    display: inline-block;
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase;
    padding: 15px 40px;
    border: 0;
    width: 100%;
    border-radius: 3px;
    background: #84bf76;
    color: #fff;
}
.callback .modal_title, .callback_boyar .modal_title {
    font-size: 24px;
    display: block;
    margin-bottom: 20px;
    margin-top: 15px;
    text-align: center;
}
        </style>
    </head>
    <body>	
        <?php $this->beginBody() ?>
        <div id="loader-show"></div>

        <div class="wrapper" style="margin-bottom: -20px;">
            <header class="header-nav default <?= $isIndex ? '' : ' dark-bg' ?>" id="menu-fk">
                <div class="inside__block">
                    <div class="container-fluid">
                        <div class="logo__block">
                            <a class="logo__block-icon" href="<?= Yii::$app->homeUrl; ?>"></a>
                        </div>
                        <div class="phone__block">
                            <span class="phone__block-number">
                                <span class="glyphicon glyphicon-phone"></span><a style="color:#fff;text-decoration:none;" href="tel:84994041018">8-499-404-10-18</a>
                                <span class="header_callback_btn callback_form" data-modal="callback" data-lead="Заказать звонок">заказать звонок</span>
                            </span>
                        </div>
                        <div class="registr-block">
                            <div class="registr__block">
                                <?php
                                if (Yii::$app->user->isGuest) {
                                    echo Html::a('вход / регистрация', ["/user/login"]);
                                } else {
                                    echo Html::a(Yii::$app->user->identity->profile->full_name . ' [выход]', ["/user/logout"], ["data-method" => "post"]);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="nav__block">
                            <span id="menu__burger">Меню</span>
                            <?=
                            yii\widgets\Menu::widget([
                                'options' => ['class' => 'nav_menu'],
                                'items' => [
                                    ['label' => 'Главная', 'url' => ['/site/index']],
                                    ['label' => 'MIXMARKET', 'url' => 'https://market.mixcart.ru'],
                                    ['label' => 'Франшиза', 'url' => 'http://fr.mixcart.ru'],
                                    ['label' => 'Новости', 'url' => 'http://blog.mixcart.ru?news'],
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

                        <div class="col-md-4 col-sm-4">
                            <div class="footer__menu_block">
                                <span class="title__menu">Карта сайта</span>
                                <ul class="links">
                                    <li><?= Html::a('Новости', "http://blog.mixcart.ru?news") ?></li>
                                    <li><?= Html::a('Для ресторанов', "https://client.mixcart.ru") ?></li>
                                    <li><?= Html::a('Для поставщиков', ["/site/supplier"]) ?></li>
                                    <li><?= Html::a('О компании', ["/site/about"]) ?></li>
                                    <li><?= Html::a('Контакты', ["/site/contacts"]) ?></li>
                                </ul>
                            </div>	
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="footer__menu_block">
                                <span class="title__menu">связаться с нами</span>
                                <ul class="contacts">
                                    <li><span class="phone"><span class="glyphicon glyphicon-phone"></span> 8-499-404-10-18</span></li>
                                    <li><a href="mailto:info@mixcart.ru"><span class="email"><span class="glyphicon glyphicon-envelope"></span>info@mixcart.ru</span></a></li>
                                    <li><span class="address"><span class="glyphicon glyphicon-map-marker"></span>Москва, ул.Привольная, 70</li>
                                </ul>
                            </div>	
                        </div>
                        <div class="col-md-4 col-sm-4">
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
                                        <span>© <?= date('Y') ?> MixCart — ООО «Онлайн Маркет» </span>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="sot__set_block">
                                        <a href="https://vk.com/f_keeper" target="_blank"><i class="fa fa-vk"></i></a>
                                        <a href="https://www.instagram.com/f_keeper.ru/" target="_blank"><i class="fa fa-instagram"></i> </a>
<!--                                        <a href="#"><i class="fa fa-youtube"></i></a>
                                        <a href="#"><i class="fa fa-twitter"></i></a>-->
                                        <a href="https://www.facebook.com/fkeeper.ru/" target="_blank"><i class="fa fa-facebook"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>	
                    </div>
            </footer><!-- .footer -->
            <div class="modalOverlay"></div>
            <div class="modalWindowWrap callback">
                <div class="modalTable">
                    <div class="modalCell">
                        <div class="modalWindow">
                            <span class="modalWindowClose"></span>
                            <span class="modal_title">Заказать звонок</span>
                            <form action="https://partner.mixcart.ru/fr/post" class="callbackwidget-call-form">
                                <div class="contact_us__form__row">
                                    <select class="input_text type__form" name="FIELDS[formtype]" id="formtype" required>
                                        <option value="1">Стать партнером</option>
                                        <option selected="true" value="2">Ресторан</option>
                                        <option value="3">Поставщик</option>
                                    </select>
                                </div>
                                <div class="contact_us__form__row partner__form" style="display:none">
                                    <select class="input_text" name="FIELDS[partner]" required>
                                        <option value="1">Тариф 50 000</option>
                                        <option value="2">Тариф 500 000</option>
                                        <option value="3">Тариф 900 000</option>
                                    </select>
                                </div>
                                <div class="contact_us__form__row">
                                    <input type="text" class="input_text" name="FIELDS[name]" placeholder="Имя" required>
                                </div>
                                <div class="contact_us__form__row">
                                    <input type="text" class="input_text user_phone_mask" minlength="7" name="FIELDS[phone]" placeholder="Телефон" required>
                                </div>
                                <div class="contact_us__form__row">
                                    <input type="text" class="input_text" name="FIELDS[email]" placeholder="Почта">
                                </div>
                                <div class="contact_us__form__row">
                                    <input type="text" class="input_text" placeholder="Город" name="FIELDS[city]" required>
                                </div>
                                <div class="contact_us__form__row">
                                    <button type="submit" class="send-form-callback" data-loading-text="<span class='glyphicon-left glyphicon glyphicon-refresh spinning'></span> Получаем..."><span>Отправить</span></button>
                                </div>
                                <input type="hidden" name="FIELDS[sitepage]" value="fkeeper">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php
$js = <<<JS
$(".type__form").on("change", function(e){
 if($( this ).val() == 1){
    $(".partner__form").css('display','block');
 }else{
    $(".partner__form").css('display','none');
 }
})
$("form.callbackwidget-call-form").on("submit", function (h) {
        var form = $(this);
        var data = form.serialize();
        console.log(data)
        $('button[type="submit"]').button("loading");
        type = $(this).find(".form_type").val();
        $.ajax({
            url: form.attr("action"),
            data: data,
            type: "post",
            xhrFields: {withCredentials: true},
            crossDomain: true,
            processData: true,
            dataType: "json",
            success: function (response) {
                modalClose();
                $('button[type="submit"]').button("reset");
                if (response.result == "success") {
                    if($("#formtype").val() == 1){
                      yaCounter38868410.reachGoal('franch');  
                    }
                    if($("#formtype").val() == 2){
                      yaCounter38868410.reachGoal('resto');   
                    }
                    if($("#formtype").val() == 3){
                      yaCounter38868410.reachGoal('postav');  
                    }
                    (swal("Заявка успешно отправлена!", "Мы свяжемся с вами в ближайшее время.", "success"), form.trigger( 'reset' ));
                }
                if (response.result == "error") {
                    (swal("Ошибка", "Заявка не отправлена", "error"), form.trigger( 'reset' ));
                }
                if (response.result == "errorPhone") {
                    (swal("Ошибка", "Вы уже отправляли заявку", "error"));
                }
            },
            error: function () {
                modalClose();
                $('button[type="submit"]').button("reset");
                swal("Ошибка", "Заявка не отправлена", "error")
            }
        });
        return false;
});
/* modals */
function modalOpen(modal) {
    $('.' + modal).slideDown();
    overlayOpen();
}
function modalClose() {
    $(".modalWindowWrap").slideUp();
    overlayClose();
}
$("*[data-modal]").click(function () {
    var modal = $(this).attr("data-modal");
    modalOpen(modal);
});
$(".modalWindowClose").click(function () {
    modalClose();
});
$(".modalCell").click(function (event) {
    if ($(event.target).hasClass('modalCell')) {
        modalClose();
    }
});

/* overlay */
function overlayOpen() {
    $(".modalOverlay").fadeIn();
    $("html, body").addClass('no-scroll');
}
function overlayClose() {
    $(".modalOverlay").fadeOut();
    $("html, body").removeClass('no-scroll');
}           
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
            ?>
        </div><!-- .wrapper -->
        <?php
        echo $this->render('_yandex');
        $this->endBody()
        ?>
        <!-- BEGIN JIVOSITE CODE {literal} -->
        <script type='text/javascript'>
            (function () {
                var widget_id = 'RI0YDaTCe9';
                var s = document.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.src = '//code.jivosite.com/script/widget/' + widget_id;
                var ss = document.getElementsByTagName('script')[0];
                ss.parentNode.insertBefore(s, ss);
            })();</script>
        <!-- {/literal} END JIVOSITE CODE -->

    </body>
</html>
<?php $this->endPage() ?>
