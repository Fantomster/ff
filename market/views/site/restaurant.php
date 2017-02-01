<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

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
      <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'homeLink' => false,
            'links' => [
                [
                    'label' => 'Все рестораны',
                    'url' => ['/site/restaurants'],
                ],
                $restaurant->name,
            ],
        ])
      ?>
</div>
<div class="row">
  <div class="col-md-12 mp-block">
      <div class="row">
        <div class="col-md-8 col-lg-8">
            <div class="row">
                <div class="col-md-12">
                    <h3><?= $restaurant->name ?></h3>
                    <h5><span class="title-param">Контактное лицо:</span> <?= empty($restaurant->contact_name) ? '<span class="noinfo">нет информации</span>':$restaurant->contact_name ?></h5>
                    <hr>
                </div>
                <?php
if(!\Yii::$app->user->isGuest){
$js = <<<JS
 
$('#supp-phone').click(function(e){
    $(this).html('$restaurant->phone &nbsp;').css('text-decoration','none'); 
   })   
$('#supp-email').click(function(e){
    $(this).html('$restaurant->email &nbsp;').css('text-decoration','none'); 
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
                <div class="col-md-8 mp-block-left">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                                
                              <a href="#" class="btn btn-100 btn-success send-service" data-product-id="">
                                  <i class="fa fa-truck" aria-hidden="true"></i>&nbsp;&nbsp;Предложить свои услуги
                              </a>
                                <h5>* <small>Если вы поставщик, предложите ресторану свои услуги</small></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
                <img class="mp-supplier-image" src="<?= empty($restaurant->picture) ? \common\models\Organization::DEFAULT_RESTAURANT_AVATAR : $restaurant->pictureUrl ?>">
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <h4>КОРОТКО О РЕСТОРАНЕ</h4>  
                </div>
                <div class="col-md-6"> 
                    <h4>ОПИСАНИЕ</h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6"> 
                    <h5><span class="title-param">Адрес:</span> <?= empty($restaurant->address) ? '<span class="noinfo">нет информации</span>':$restaurant->address ?></h5>
                    <h5><span class="title-param">Кол-во заказов:</span></h5>   
                    <h5><span class="title-param">Кол-во поставщиков:</span></h5>  
                    <h5><span class="title-param">Категории:</span></h5> 
                </div>
                <div class="col-md-6">
                    <h5><?= empty($restaurant->about) ? '<span class="noinfo">нет информации</span>':$restaurant->about ?></h5>  
                </div>
            </div>
        </div>
      </div>
  </div>
</div>

