<?php
$this->registerCss('

@font-face{font-family:"CirceRegular";src:url("../fonts/Circe_Regular/Circe-Regular.eot");src:url("../fonts/Circe_Regular/Circe-Regular.eot?iefix") format("eot"), url("../fonts/Circe_Regular/Circe-Regular.woff") format("woff"), url("../fonts/Circe_Regular/Circe-Regular.ttf") format("truetype"), url("../fonts/Circe_Regular/Circe-Regular.svg#webfont") format("svg");}
@font-face{font-family:"CirceBold";src:url("../fonts/Circe_Bold/Circe-Bold.eot");src:url("../fonts/Circe_Bold/Circe-Bold.eot?iefix") format("eot"), url("../fonts/Circe_Bold/Circe-Bold.woff") format("woff"), url("../fonts/Circe_Bold/Circe-Bold.ttf") format("truetype"), url("../fonts/Circe_Bold/Circe-Bold.svg#webfont") format("svg");}
@font-face{font-family:"CirceExtraBold";src:url("../fonts/Circe_ExtraBold/Circe-ExtraBold.eot");src:url("../fonts/Circe_ExtraBold/Circe-ExtraBold.eot?iefix") format("eot"), url("../fonts/Circe_ExtraBold/Circe-ExtraBold.woff") format("woff"), url("../fonts/Circe_ExtraBold/Circe-ExtraBold.ttf") format("truetype"), url("../fonts/Circe_ExtraBold/Circe-ExtraBold.svg#webfont") format("svg");}
@font-face{font-family:"CirceLight";src:url("../fonts/Circe_Light/Circe-Light.eot");src:url("../fonts/Circe_Light/Circe-Light.eot?iefix") format("eot"), url("../fonts/Circe_Light/Circe-Light.woff") format("woff"), url("../fonts/Circe_Light/Circe-Light.ttf") format("truetype"), url("../fonts/Circe_Light/Circe-Light.svg#webfont") format("svg");}

.wrapper{position:relative;max-width:1120px;margin:0 auto;padding:0 10px;}
.block_title{font-size:36px;text-transform:uppercase;text-align:center;}
.subtitle{text-align:center;}

.tarif{background:#F7F7F7;}
.tarif .wrapper{padding-top:40px;padding-bottom:30px;max-width: 40%;}
.tarif .block_title{color:#343434;}
.tarif .subtitle{font-size:16px;color:#8D9091;margin-top:10px;}
.tarif_w{widthoverflow:hidden;margin-top:50px;}
.tarif_w .after_text{text-transform:uppercase;text-align:center;font-size:26px;margin-top:15px;}
.tarif_w .after_text sup{font-family:"Tahoma";font-size:20px;}
.tarif_coll__wrap{margin:0 -15px;}
.tarif_coll__wrap:before, .tarif_coll__wrap:after{position: center; content:"";display:table;}
.tarif_coll__wrap:after{clear:both;}
.tarif_coll{width:50%;float:left;padding:0 15px;padding-bottom:30px;}
.tarif__item{-moz-transition:0.3s;-o-transition:0.3s;-webkit-transition:0.3s;transition:0.3s;}
.tarif__item__head{background:#343434;padding:10px 20px 20px;height:80px;text-align:center;color:#fff;}
.tarif__item__head .paket{font-family:"CirceLight";text-transform:uppercase;font-size:12px;display:block;}
.tarif__item__head .name{text-transform:uppercase;display:block;font-size:20px;margin-top:10px;}
.tarif__item__head .srok{display:block;color:#727475;font-size:12px;}
.tarif__item:before{content:"";display:block;width:100%;height:17px;background:url("../img/decor_top_line.png") center repeat-x;}
.tarif__item__row{height:120px;margin:0 30px;text-align:center;padding-top:15px;}
.tarif__item__row:nth-child(2){border-bottom:1px solid #EEEEEF;}
.tarif__item__row .price_text{display:block;color:#6A6A6A;text-transform:uppercase;font-size:14px;}
.tarif__item__row .price{display:block;font-family:"CirceBold";font-size:30px;color:#343434;margin-top:10px;}
.tarif__item__row .income_text{display:block;color:#95989A;text-transform:uppercase;font-size:14px;}
.tarif__item__row .income{display:block;font-family:"CirceBold";font-size:30px;color:#95989A;margin-top:10px;}
.tarif__item__row .income sup{color:#66BC75;font-family:"Tahoma";font-size:20px;}
.tarif__item__row .btn_style{width:230px;height:50px;line-height:52px;font-size:18px;text-transform:uppercase;margin-top:5px;}

@media only screen and (max-width:1000px){.tarif_coll__wrap{text-align:center;}
.tarif_coll{width:50%;float:none;display:inline-block;margin-right:-5px;margin-top:30px;min-width:300px;}
.tarif__item__row{height:auto;margin:5px 0;}
.tarif__item{padding-bottom:20px;}
footer{background:none;}
.footer__logo, .footer__center, .footer__social{text-align:center;float:none;width:100%;}
}
');
?>
<main class="content content-inner ">
    <!-- tarif -->
    <section class="tarif">
        <div class="wrapper">
            <h2 class="block_title">ПОДКЛЮЧЕНИЕ И АБОНЕНТСКАЯ ПЛАТА</h2>
            <p class="subtitle">Функции сервиса доступны в течение оплаченного периода</p>
            <div class="tarif_w">
                <!-- tarif_coll__wrap -->
                <div class="tarif_coll__wrap">

                    <div class="tarif_coll">
                        <div class="tarif__item">
                            <div class="tarif__item__head">
                                <span class="paket">тарификация</span>
                                <span class="name">РЕСТОРАН</span>
                            </div>
                            <div class="tarif__item__row">
                                <span class="price_text">стоимость подключения</span>
                                <span class="srok">единовременно</span>
                                <span class="price">10 000 Р</span>
                            </div>
                            <div class="tarif__item__row">
                                <span class="price_text">абонентская плата</span>
                                <span class="srok">период 30 календарных дней</span>
                                <span class="price">2 940 Р</span>
                            </div>
                        </div>
                    </div>

                    <div class="tarif_coll">
                        <div class="tarif__item">
                            <div class="tarif__item__head">
                                <span class="paket">тарификация</span>
                                <span class="name">ПОСТАВЩИК</span>
                            </div>
                            <div class="tarif__item__row">
                                <span class="price_text">стоимость подключения</span>
                                <span class="srok">единовременно</span>
                                <span class="price">30 000 Р</span>
                            </div>
                            <div class="tarif__item__row">
                                <span class="price_text">абонентская плата</span>
                                <span class="srok">период 30 календарных дней</span>
                                <span class="price">5 000 Р</span>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- end tarif_coll__wrap -->
            </div>
        </div>
    </section>
</main>