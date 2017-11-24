<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;

$delivery = $vendor->delivery;
?>

<?php
$this->title = Yii::t('message', 'market.views.site.supplier.info', ['ru'=>'MixCart информация о поставщике']);
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
                    'label' => Yii::t('message', 'market.views.site.supplier.all', ['ru'=>'Все поставщики']),
                    'url' => ['/site/suppliers'],
                ],
                $vendor->name,
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
                    <h3><?= $vendor->name ?></h3>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.contact', ['ru'=>'Контактное лицо:']) ?></span> <?= empty($vendor->contact_name) ? '<span class="noinfo">' . Yii::t('error', 'market.views.site.supplier.no_info', ['ru'=>'нет информации']) . ' </span>':$vendor->contact_name ?></h5>
                    <hr>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 show-supp-info">
                   <?=!empty($vendor->phone)? '<a id="supp-phone">' . Yii::t('error', 'market.views.site.supplier.show_phone', ['ru'=>'Показать телефон']) . ' </a>':'&nbsp;'?>
                </div>
                
                <div class="col-xs-6 col-sm-6 col-md-6 show-supp-info">
                    <?=!empty($vendor->email)? '<a id="supp-email">' . Yii::t('error', 'market.views.site.supplier.show_email', ['ru'=>'Показать E-mail']) . ' </a>':'&nbsp;'?>
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
    $register = Yii::t('error', 'market.views.site.supplier.register', ['ru'=>'Необходимо зарегистрироваться в системе MixCart']);
                    
$js2 = <<<JS
$('#supp-phone,#supp-email').click(function(e){
alert('$register');  
})
JS;
$this->registerJs($js2, \yii\web\View::POS_READY);
}
                ?>
                <div class="col-md-6 mp-block-left">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="<?=Url::to(['/site/supplier-products', 'id' => $vendor->id]);?>" class="btn btn-100 btn-success view-catalog" data-product-id="">
                                  <isc></isc>&nbsp;&nbsp;<?= Yii::t('message', 'market.views.site.supplier.catalog', ['ru'=>'КАТАЛОГ']) ?>
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
                                  <?= Yii::t('message', 'market.views.site.supplier.add_vendor', ['ru'=>'ДОБАВИТЬ ПОСТАВЩИКА']) ?>
                              </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
                <img class="mp-supplier-image" src="<?= empty($vendor->picture) ? \common\models\Organization::DEFAULT_VENDOR_AVATAR : $vendor->pictureUrl ?>">
        </div>
        <div class="col-md-12" style="padding-top:25px">
           
                <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.address', ['ru'=>'Адрес:']) ?></span> <?= empty($vendor->address) ? '<span class="noinfo">' . Yii::t('error', 'market.views.site.supplier.no_info_two', ['ru'=>'нет информации']) . ' </span>':$vendor->address ?></h5>
       
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <h4><?= Yii::t('message', 'market.views.site.supplier.short_vendor', ['ru'=>'КОРОТКО О ПОСТАВЩИКЕ']) ?></h4>
                </div>
                <div class="col-md-6">
                    <h4><?= Yii::t('message', 'market.views.site.supplier.conditions', ['ru'=>'УСЛОВИЯ ДОСТАВКИ']) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.categories', ['ru'=>'Категории:']) ?></span>
                    <?= $vendor->getMarketGoodsCount() ?>
                    </h5> 
                </div>
                <div class="col-md-6">
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.', ['ru'=>'Стоимость доставки:']) ?></span>
                                      <?= $delivery->delivery_charge ?> <?= Yii::t('message', 'market.views.site.supplier.rouble', ['ru'=>'руб.']) ?>
                    </h5>    
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.free_delivery', ['ru'=>'Бесплатная доставка от:']) ?></span> <?= $delivery->min_free_delivery_charge ?><?= Yii::t('message', 'market.views.site.supplier.rouble_two', ['ru'=>' руб.']) ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.min_order', ['ru'=>'Минимальный заказ:']) ?></span> <?= $delivery->min_order_price ?><?= Yii::t('message', 'market.views.site.supplier.rouble_three', ['ru'=>' руб.']) ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.self_address', ['ru'=>'Адрес самовывоза:']) ?></span> <span class="noinfo"><?= Yii::t('message', 'market.views.site.supplier.no_info', ['ru'=>'нет информации']) ?></span></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.supplier.delivery_days', ['ru'=>'Дни доставки:']) ?></span> <?= $delivery->getDaysString() ?></h5>
                </div>
                <div class="col-md-12">
                    <h4><?= Yii::t('message', 'market.views.site.supplier.description', ['ru'=>'ОПИСАНИЕ']) ?></h4>
                </div>
                <div class="col-md-12" style="padding-bottom:10px;">
                   <?= empty($vendor->about) ? '<span class="noinfo">' . Yii::t('error', 'market.views.site.supplier.no_info_three', ['ru'=>'нет информации']) . ' </span>':$vendor->about ?>
                </div>
            </div>
        </div>
      </div>
  </div>
</div>

