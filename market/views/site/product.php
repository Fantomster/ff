<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$addAction = Url::to(["site/ajax-add-to-cart"]);
$inviteAction = Url::to(["site/ajax-invite-vendor"]);

$this->title = 'F-MARKET главная';

$js = <<<JS
        $(document).on("click", ".add-to-cart", function(e) {
            e.preventDefault();
            $.post(
                "$addAction",
                {product_id: $(this).data("product-id")}
            ).done(function (result) {
                if (result) {
                    alert("Yes, we can!");
                } else {
                    alert("Fail!");
                }
            });
        });
        $(document).on("click", ".invite-vendor", function(e) {
            e.preventDefault();
            $.post(
                "$inviteAction",
                {vendor_id: $(this).data("vendor-id")}
            ).done(function (result) {
                if (result) {
                    alert("Invited!");
                } else {
                    alert("Fail!");
                }
            });
        });
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>

<?php
$this->title = 'F-MARKET информация о товаре';
?>
<style>
    .mp-product-image{
    object-fit: cover;
    width: 100%;
    height: 160px;
    padding: 15px 0px;    
    }   
    .mp-product-article{
    width:100%;
    display:inline-block;
    background:#343435;
    color:#fff;
    padding:5px;
    text-align: center;
    }
    .btn-cart-active{
    padding:10px;    
    }
    .btn-cart{
    padding:10px; 
    }
    .btn-cart i{ 
    line-height: 2;
    }
    @media (min-width: 992px) {
	.mp-block-left {
	padding-right:7.5px;    
	}
        .mp-block-right {
	padding-left:7.5px;    
	}
}
.mp-block-show-phone{padding-top:20px}
.mp-block-show-email{padding-top:20px}
.title-param{
font-family: "HelveticaBold",Arial,sans-serif;    
}
</style>
<div class="row">
  <div class="col-md-12">
      <h3>123123</h3>
  </div>
</div>
<div class="row">
  <div class="col-md-12 mp-block">
      <div class="row">
        <div class="col-md-8 col-lg-8">
            <div class="row">
                <div class="col-md-12">
                    <h3><?= $product->product ?></h3>
                    <h2 style="padding-bottom:15px"><?= $product->price ?> <small>руб.</small></h2>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6"><a>Показать телефон</a></div>
                <div class="col-xs-6 col-sm-6 col-md-6"><a>Показать E-mail</a></div>
                <div class="col-md-6 mp-block-left">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="#" class="btn btn-sm btn-cart-active add-to-cart" data-product-id="<?= $product->id ?>">
                                  <isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;КУПИТЬ
                              </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mp-block-right">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="#" class="btn btn-sm btn-cart invite-vendor" data-vendor-id="<?= $product->supp_org_id ?>">
                                  <i class="fa fa-plus"></i>&nbsp;&nbsp;ДОБАВИТЬ ПОСТАВЩИКА
                              </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
                <img class="mp-product-image" src="http://yenisafak.feo.doracdn.com/resize/47uQufiZbmsgHk3H/400/0/resim/upload/2016/02/03/04/23/ea936dd22bff6c1108e1607cf5d5e04bf5811f75_k.jpg">
                <div class="mp-product-article">Артикул № 2293322444123232</div>
        </div>
        <div class="col-md-12" style="padding-top:25px">
            <div class="row">
                <div class="col-md-6">
                    <h4>КОРОТКО О ТОВАРЕ</h4>  
                </div>
                <div class="col-md-6">
                    <h4>УСЛОВИЯ ДОСТАВКИ</h4>  
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6"> 
                    <h5><span class="title-param">Страна:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Ед. измерения:</span> asdasdasd</h5>  
                    <h5><span class="title-param">Вес:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Производитель:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Кратность поставки:</span> asdasdasd</h5>   
                </div>
                <div class="col-md-6">
                    <h5><span class="title-param">Стоимость доставки:</span> asdasdasd</h5>    
                    <h5><span class="title-param">Бесплатная доставка от:</span> asdasdasd</h5> 
                    <h5><span class="title-param">Минимальный заказ:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Адрес самовывоза:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Дни доставки:</span> asdasdasd</h5>  
                </div>
                <div class="col-md-12">
                    <h4>КОММЕНТАРИЙ</h4>  
                </div>
                <div class="col-md-12" style="padding-bottom:10px;">
                   фывфывфывфыв  
                </div>
            </div>
        </div>
      </div>
  </div>
</div>