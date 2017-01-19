<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
$delivery = $vendor->delivery;

$inviteAction = Url::to(["site/ajax-invite-vendor"]);

$this->title = 'F-MARKET главная';

$js = <<<JS
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
$this->title = 'F-MARKET информация о поставщике';
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
      <h3>Поставщики / <?= $vendor->name ?></h3>
</div>
<div class="row">
  <div class="col-md-12 mp-block">
      <div class="row">
        <div class="col-md-8 col-lg-8">
            <div class="row">
                <div class="col-md-12">
                    <h3><?= $vendor->name ?></h3>
                    <h5><span class="title-param">Контактное лицо:</span> <?= $vendor->contact_name ?></h5>
                    <hr>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 show-supp-info">
                    <a id="supp-phone">Показать телефон</a>
                </div>
                
                <div class="col-xs-6 col-sm-6 col-md-6 show-supp-info">
                    <a id="supp-email">Показать E-mail</a>
                </div>
                <?php
if(!\Yii::$app->user->isGuest){
$js = <<<JS
 
$('#supp-phone').click(function(e){
    $(this).html('$vendor->phone &nbsp;').css('text-decoration','none'); 
   })   
$('#supp-email').click(function(e){
    $(this).html('$vendor->email &nbsp;').css('text-decoration','none'); 
   })
JS;
$this->registerJs($js, \yii\web\View::POS_READY);

}else{
                    
$js2 = <<<JS
$('#supp-phone,#supp-email').click(function(e){
alert('Необходимо зарегистрироваться в системе f-keeper');  
})
JS;
$this->registerJs($js2, \yii\web\View::POS_READY);
}
                ?>
                <div class="col-md-6 mp-block-left">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="<?=Url::to(['/site/supplier-products', 'id' => $vendor->id]);?>" class="btn btn-100 btn-success" data-product-id="">
                                  КАТАЛОГ
                              </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mp-block-right">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="#" class="btn btn-100 btn-outline-success invite-vendor" data-vendor-id="<?= $vendor->id ?>">
                                  ДОБАВИТЬ ПОСТАВЩИКА
                              </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
                <img class="mp-supplier-image" src="<?= $vendor->pictureUrl ?>">
        </div>
        <div class="col-md-12" style="padding-top:25px">
           
                <h5><span class="title-param">Адрес:</span> <?= $vendor->address ?></h5> 
       
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
                    <h5><span class="title-param">Клиентов:</span> <?= $vendor->getClientsCount() ?></h5>   
                    <h5><span class="title-param">Заказов:</span> <?= $vendor->getOrdersCount() ?></h5>  
                    <h5><span class="title-param">Категории:</span> <?= $vendor->getMarketGoodsCount() ?></h5> 
                </div>
                <div class="col-md-6">
                    <h5><span class="title-param">Стоимость доставки:</span> <?= $delivery->delivery_charge ?> руб.</h5>    
                    <h5><span class="title-param">Бесплатная доставка от:</span> <?= $delivery->min_free_delivery_charge ?> руб.</h5> 
                    <h5><span class="title-param">Минимальный заказ:</span> <?= $delivery->min_order_price ?> руб.</h5>   
                    <h5><span class="title-param">Адрес самовывоза:</span> </h5>   
                    <h5><span class="title-param">Дни доставки:</span> <?= $delivery->getDaysString() ?></h5>  
                </div>
                <div class="col-md-12">
                    <h4>ОПИСАНИЕ</h4>  
                </div>
                <div class="col-md-12" style="padding-bottom:10px;">
                   <?= $vendor->about ?>  
                </div>
            </div>
        </div>
      </div>
  </div>
</div>

