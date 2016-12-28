<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
?>

<?php
$this->title = 'F-MARKET информация о товаре';
?>
<style>
    .mp-supplier-image{
    object-fit: cover;
    width: 100%;
    height: 193px;
    padding: 15px 0px;    
    }   
    .mp-supplier-article{
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
    btn-cart-active i{ 
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
.title-param{
font-family: "HelveticaBold",Arial,sans-serif;    
}
</style>
<div class="row">
      <h3>Поставщики / ООО "Рики Тики Тави"</h3>
</div>
<div class="row">
  <div class="col-md-12 mp-block">
      <div class="row">
        <div class="col-md-8 col-lg-8">
            <div class="row">
                <div class="col-md-12">
                    <h3>ООО "Рики Тики Тави"</h3>
                    <h5><span class="title-param">Контактное лицо:</span> Попов Павел Александрович</h5>
                    <hr>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6"><a>Показать телефон</a></div>
                <div class="col-xs-6 col-sm-6 col-md-6"><a>Показать E-mail</a></div>
                <div class="col-md-6 mp-block-left">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="#" class="btn btn-sm btn-cart-active" data-product-id="">
                                  <i class="fa fa-eye"></i>&nbsp;&nbsp;ПОСМОТРЕТЬ КАТАЛОГ
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
                <img class="mp-supplier-image" src="http://yenisafak.feo.doracdn.com/resize/47uQufiZbmsgHk3H/400/0/resim/upload/2016/02/03/04/23/ea936dd22bff6c1108e1607cf5d5e04bf5811f75_k.jpg">
        </div>
        <div class="col-md-12" style="padding-top:25px">
           
                <h5><span class="title-param">Адрес:</span> г.Москва, ул.Оршанская 5</h5> 
       
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <h4>КОРОТКО О ПОСТАВЩИКЕ</h4>  
                </div>
                <div class="col-md-6">
                    <h4>УСЛОВИЯ ДОСТАВКИ</h4>  
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6"> 
                    <h5><span class="title-param">Клиентов:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Заказов:</span> asdasdasd</h5>  
                    <h5><span class="title-param">Категории:</span> asdasdasd</h5> 
                </div>
                <div class="col-md-6">
                    <h5><span class="title-param">Стоимость доставки:</span> asdasdasd</h5>    
                    <h5><span class="title-param">Бесплатная доставка от:</span> asdasdasd</h5> 
                    <h5><span class="title-param">Минимальный заказ:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Адрес самовывоза:</span> asdasdasd</h5>   
                    <h5><span class="title-param">Дни доставки:</span> asdasdasd</h5>  
                </div>
                <div class="col-md-12">
                    <h4>ОПИСАНИЕ</h4>  
                </div>
                <div class="col-md-12" style="padding-bottom:10px;">
                   фывфывфывфыв  
                </div>
            </div>
        </div>
      </div>
  </div>
</div>

