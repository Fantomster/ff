<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\Modal;

$this->title = Yii::t('message', 'market.views.site.index.main', ['ru'=>'MixCart главная']);
?>
<div class="row">
  <div class="col-md-12 min-padding">
    <h3><?= Yii::t('message', 'market.views.site.index.popular', ['ru'=>'Популярные товары']) ?></h3>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="row" id="product-block">
        <?php
        foreach($topProducts as $row){
        ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-product-block animated fadeIn">
                <div class="mp-rating">
                    <div class="Fr-star size-3" data-title="<?=$row->ratingStars?>" data-rating="<?=$row->ratingStars?>">
                        <div class="Fr-star-value" style="width:<?=$row->ratingPercent?>%"></div>
                        <div class="Fr-star-bg"></div>
                    </div>
                </div>
                <?=empty($row->vendor->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
                <?php if(isset($row->vendor->ediOrganization->gln_code) && $row->vendor->ediOrganization->gln_code>0){
                    $text = Yii::t('app', 'frontend.views.client.suppliers.edi_alt_text', ['ru' => 'Поставщик работает через систему электронного документооборота']);
                    echo "<div  class='pro-partner' style='border: 0; padding: 0'>" . Html::img(Url::to('/images/edi-logo.png'), ['alt' => $text, 'title' => $text, 'width' => 40]) . "</div>";
                }
                ?>
                <a href="<?=Url::to(['/site/product', 'id' => $row->id]);?>">
                <img class="product-image wow animated fadeInUp" src="<?= $row->imageUrl ?>">
                </a>
              <div class="row">
                <div class="col-md-12">
                  <div class="product-title">
                     <a href="<?=Url::to(['/site/product', 'id' => $row->id]);?>"><h3><?=$row->product; ?></h3></a>
                  </div>
                  <div class="product-category">
                      <h5><?= Yii::t('app', \common\models\CatalogBaseGoods::getCurCategory($row->category_id)->name); ?>/<?=Yii::t('app', $row->subCategory->name); ?></h5>
                  </div>
                  <div class="product-company">
                      <a href="<?=Url::to(['/site/supplier', 'id' => $row->vendor->id]);?>">
                     <h5><?=$row->vendor->name; ?></h5>
                      </a>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="product-price">
                      <?php if(empty($row->mp_show_price)){ ?>
                      <h4 style="color:#dfdfdf"><?= Yii::t('message', 'market.views.site.index.price', ['ru'=>'договорная цена']) ?></h4>
                      <?php } else {?>
                      <h4><?=number_format($row->price, 2, '.', ''); ?> <small><?= $row->catalog->currency->symbol; ?></small></h4>
                      <?php } ?>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="product-button">
                    <a href="#" class="btn btn-100 btn-outline-success add-to-cart" data-product-id="<?= $row->id ?>">
                        <isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;<?= Yii::t('message', 'market.views.site.index.buy', ['ru'=>'КУПИТЬ']) ?>
                    </a>
                  </div>  
                </div>
              </div>
            </div>  
        </div>    
        <?php    
        }
        ?> 
    </div>
    <div class="row">
      <div class="col-md-12 min-padding" style="margin-bottom: 10px">
        <a href="#" class="btn btn-100 btn-outline-default <?=$topProductsCount>6?'':'disabled'?>" id="product-more"><?= Yii::t('message', 'market.views.site.index.show_more', ['ru'=>'ПОКАЗАТЬ ЕЩЕ']) ?></a>
      </div>   
    </div>
    <div class="row">
      <div class="col-md-12 min-padding">
        <h3 class="pull-left"><?= Yii::t('message', 'market.views.site.index.vendors', ['ru'=>'Поставщики']) ?></h3>
        <a href="<?=Url::to(['/site/suppliers']);?>" class="pull-right text-success all-supplier-view"><?= Yii::t('message', 'market.views.site.index.all_vendors', ['ru'=>'Все поставщики']) ?></a>
      </div>
    </div>
    <div class="row" id="supplier-block">
        <?php
        foreach($topSuppliers as $row){
        ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block animated fadeIn">
         <div class="mp-rating">
            <div class="Fr-star size-3" data-title="<?=$row->ratingStars?>" data-rating="<?=$row->ratingStars?>">
                <div class="Fr-star-value" style="width:<?=$row->ratingPercent?>%"></div>
                <div class="Fr-star-bg"></div>
            </div>
         </div>
          <?=empty($row->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
            <?php if(isset($row->ediOrganization->gln_code) && $row->ediOrganization->gln_code > 0){
                $text = Yii::t('app', 'frontend.views.client.suppliers.edi_alt_text', ['ru' => 'Поставщик работает через систему электронного документооборота']);
                echo "<div  class='pro-partner' style='border: 0; padding: 0'>" . Html::img(Url::to('/images/edi-logo.png'), ['alt' => $text, 'title' => $text, 'width' => 40]) . "</div>";
            }
            ?>
          <a href="<?=Url::to(['/site/supplier', 'id' => $row->id]);?>">
            <img class="supplier-image  animated fadeInUp" src="<?= empty($row->picture) ? \common\models\Organization::DEFAULT_VENDOR_AVATAR : $row->pictureUrl ?>">
          </a>
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <a href="<?=Url::to(['/site/supplier', 'id' => $row->id]);?>">
                <h3><?=$row->name;?></h3>
                </a>
              </div>
              <div class="supplier-category">
                <h5><?=!empty($row->city) ? $row->city : '&nbsp;';?></h5>
              </div>
            </div>
            <div class="col-md-12">
              <div class="supplier-button">
                <a href="#" class="btn btn-100 btn-success invite-vendor" data-vendor-id="<?= $row->id ?>" style="width: 100%"><?= Yii::t('message', 'market.views.site.info.add', ['ru'=>'ДОБАВИТЬ']) ?></a>
              </div>
            </div>
          </div>
        </div>  
      </div>    
        <?php    
        }
        ?> 
    </div>
    <div class="row">
      <div class="col-md-12 min-padding">
        <a href="#" class="btn btn-100 btn-outline-default <?=$topSuppliersCount>6?'':'disabled'?>" id="supplier-more"><?= Yii::t('message', 'market.views.site.info.show_more', ['ru'=>'Показать еще']) ?></a>
      </div>   
    </div>
  </div> 
</div> 


<?php 
        
$productMoreUrl = Url::to(['site/ajax-product-more']);
$supplierMore = Url::to(['site/ajax-supplier-more']);

$customJs = <<< JS
var num = 6;
$('#product-more').on("click", function (e) {
    e.preventDefault();
    $('#product-more').addClass('disabled');
    $.ajax({
      url: "$productMoreUrl",
      type: "GET",
      data: {"num": num},
      cache: false,
      success: function(response){
          if(response == 0){
             //alert("Больше нет записей");
          }else{
             $("#product-block").append(response);
             num = num + 6;
             $('#product-more').removeClass('disabled');
          }
       }
    });
});
$('#supplier-more').on("click", function (e) {
    e.preventDefault();
    console.log('supplier click more');
    $.ajax({
      url: "$supplierMore",
      type: "GET",
      data: {"num": num},
      cache: false,
      success: function(response){
          if(response == 0){
             //alert("Больше нет записей");
          }else{
             $("#supplier-block").append(response);
             num = num + 6;
          }
       }
    });
});      
JS;
$this->registerJs($customJs, View::POS_READY);
?>
