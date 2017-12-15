<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Breadcrumbs;

$vendor = $product->vendor;
$delivery = $vendor->delivery;
$currency = $product->catalog->currency->symbol;
?>

<?php
$this->title = Yii::t('message', 'market.views.site.product.info', ['ru'=>'MixCart информация о товаре']);
?>
<style>
    .mp-product-image{
    object-fit: cover;
    width: 100%;
    height: 145px;
    padding: 15px 0 0 0;    
    }   
    .mp-product-article{
    width:100%;
    display:inline-block;
    border-radius:0 0 3px 3px;
    font-size: 12px;
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
.text-overflow {
    white-space: nowrap;
    overflow: hidden;
   }
</style>
<div class="row">
  <div class="col-md-12 no-padding">
    <?=
    Breadcrumbs::widget([
        'options' => [
            'class' => 'breadcrumb text-overflow',
            'title' => Html::decode($product->product),
            'data-toggle' => 'tooltip',
            'data-placement' => 'bottom',
            'data-original-title' => Html::decode($product->product),
        ],
        'homeLink' => false,
        'links' => [
            Html::decode($product->mainCategory->name),
            [
                'label' => Html::decode($product->category->name),
                'url' => ['site/category', 'slug' => $product->category->slug],
            ],
            [
            'label' => Html::decode($product->product, ['style'=>'text-overflow: ellipsis']),
            'encode' => false,
            ]
        ],
    ])
    ?>
  </div>
</div>
<div class="row">
  <div class="col-md-12 mp-block">
      <div class="row">
        <div class="col-md-8 col-lg-8">
            <div class="row">
                <div class="col-md-12">
                    <h3><?= Html::decode(Html::decode($product->product)) ?> <br>
                        <a class="grey-link" href="<?=Url::to(['/site/supplier', 'id' => $vendor->id]);?>">
                            <small><?= $vendor->name ?></small>
                        </a>
                    </h3>
              <?php if(empty($product->mp_show_price)){ ?>
              <h2 style="color:#dfdfdf;padding-bottom:15px"><?= Yii::t('message', 'market.views.site.product.price', ['ru'=>'договорная цена']) ?></h2>
              <?php } else {?>
              <h2 style="padding-bottom:15px"><?=floatval($product->price); ?> <small><?= $currency ?></small></h2>
              <?php } ?>
                </div>
                <div class="col-xs-6 col-sm-6 col-md-6 show-supp-info">
                   <?=!empty($vendor->phone)? '<a id="supp-phone">' . Yii::t('message', 'market.views.site.product.show_phone', ['ru'=>'Показать телефон']) . ' </a>':'&nbsp;'?>
                </div>
                
                <div class="col-xs-6 col-sm-6 col-md-6 show-supp-info">
                    <?=!empty($vendor->email)? '<a id="supp-email">' . Yii::t('message', 'market.views.site.product.show_email', ['ru'=>'Показать E-mail']) . ' </a>':'&nbsp;'?>
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
    $register = Yii::t('message', 'market.views.site.product.register', ['ru'=>'Необходимо зарегистрироваться в системе MixCart']);
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
                              <a href="#" class="btn btn-100 btn-success add-to-cart" data-product-id="<?= $product->id ?>">
                                  <isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;<?= Yii::t('message', 'market.views.site.product.buy', ['ru'=>'КУПИТЬ']) ?>
                              </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mp-block-right">
                    <div class="row">
                        <div class="col-md-12 no-padding">
                            <div class="product-button">
                              <a href="#" class="btn btn-100 btn-outline-success invite-vendor" data-vendor-id="<?= $product->supp_org_id ?>">
                                  <span class="fa fa-user-plus icon-16"></span>
                                  <?= Yii::t('message', 'market.views.site.product.add_vendor', ['ru'=>'ДОБАВИТЬ ПОСТАВЩИКА']) ?>
                              </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-4">
                <img class="mp-product-image" src="<?= $product->imageUrl ?>">
                <div class="mp-product-article"><?= Yii::t('message', 'market.views.site.product.art', ['ru'=>'Артикул №']) ?> <?= $product->article ?></div>
        </div>
        <div class="col-md-12" style="padding-top:25px">
            <div class="row">
                <div class="col-md-6">
                    <h4><?= Yii::t('message', 'market.views.site.product.short', ['ru'=>'КОРОТКО О ТОВАРЕ']) ?></h4>
                </div>
                <div class="col-md-6">
                    <h4><?= Yii::t('message', 'market.views.site.product.conditions', ['ru'=>'УСЛОВИЯ ДОСТАВКИ']) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6"> 
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.country', ['ru'=>'Страна:']) ?></span> <?= empty($product->region) ? '<span class="noinfo">' . Yii::t('message', 'market.views.site.product.no_info', ['ru'=>'нет информации']) . ' </span>': Yii::t('app', $product->mpRegion->name) ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.measure', ['ru'=>'Ед. измерения:']) ?></span> <?= empty($product->ed) ? '<span class="noinfo">' . Yii::t('message', 'market.views.site.product.no_info_two', ['ru'=>'нет информации']) . '</span>':Yii::t('app', $product->ed) ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.weight', ['ru'=>'Вес:']) ?></span> <?= empty($product->weight) ? '<span class="noinfo">' . Yii::t('message', 'market.views.site.product.no_info_three', ['ru'=>'нет информации']) . '</span>':$product->weight ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.vendor', ['ru'=>'Производитель:']) ?></span> <?= empty($product->brand) ? '<span class="noinfo">' . Yii::t('message', 'market.views.site.product.no_info_four', ['ru'=>'нет информации']) . '</span>':$product->brand ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.frequency', ['ru'=>'Кратность поставки:']) ?></span> <?= empty($product->units) ? '<span class="noinfo">' . Yii::t('message', 'market.views.site.product.no_info_five', ['ru'=>'нет информации']) . '</span>':$product->units ?></h5>
                </div>
                <div class="col-md-6">
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.price_two', ['ru'=>'Стоимость доставки:']) ?></span> <?= $delivery->delivery_charge ?> <?= $currency ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.free_delivery', ['ru'=>'Бесплатная доставка от:']) ?></span> <?= $delivery->min_free_delivery_charge ?> <?= $currency ?></h5>
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.min', ['ru'=>'Минимальный заказ:']) ?></span> <?= $delivery->min_order_price ?> <?= $currency ?></h5>
                    <!--h5><span class="title-param">Адрес самовывоза:</span> </h5-->   
                    <h5><span class="title-param"><?= Yii::t('message', 'market.views.site.product.delivery_days', ['ru'=>'Дни доставки:']) ?></span> <?= $delivery->getDaysString() ?></h5>
                </div>
                <div class="col-md-12">
                    <h4><?= Yii::t('message', 'market.views.site.product.comment', ['ru'=>'КОММЕНТАРИЙ']) ?></h4>
                </div>
                <div class="col-md-12" style="padding-bottom:10px;">
                   <?= empty($product->note) ? '<span class="noinfo">' . Yii::t('message', 'market.views.site.product.no_info_six', ['ru'=>'нет информации']) . '</span>':$product->note ?>
                </div>
            </div>
        </div>
      </div>
  </div>
</div>
