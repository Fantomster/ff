<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
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
                    <h3>Рагу из молодого барашка</h3>
                    <h2>240098 <small>руб.</small></h2>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6"><a>Показать телефон</a></div>
                <div class="col-xs-6 col-sm-6 col-md-6"><a>Показать E-mail</a></div>
                <div class="col-md-6 mp-block-left">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="#" class="btn btn-sm btn-cart-active add-to-cart" data-product-id="">
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
                              <a href="#" class="btn btn-sm btn-cart" data-product-id="">
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
      </div>
  </div>
</div>