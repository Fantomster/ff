<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

//market\assets\AppAsset::register($this);

$this->title = 'F-MARKET главная';
?>
<div class="row">
  <div class="col-md-12 min-padding">
    <h3>Популярные товары</h3>  
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
                <a href="<?=Url::to(['/site/product', 'id' => $row->id]);?>">
                <img class="product-image wow animated fadeInUp" src="<?= $row->imageUrl ?>">
                </a>
              <div class="row">
                <div class="col-md-12">
                  <div class="product-title">
                     <a href="<?=Url::to(['/site/product', 'id' => $row->id]);?>"><h3><?=$row->product; ?></h3></a>
                  </div>
                  <div class="product-category">
                      <h5><?= \common\models\CatalogBaseGoods::getCurCategory($row->category_id)->name; ?>/<?=$row->subCategory->name; ?></h5>
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
                      <h4 style="color:#dfdfdf">договорная цена</h4>
                      <?php } else {?>
                      <h4><?=floatval($row->price); ?> <small>руб.</small></h4>
                      <?php } ?>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="product-button">
                    <a href="#" class="btn btn-100 btn-outline-success add-to-cart" data-product-id="<?= $row->id ?>">
                        <isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;КУПИТЬ
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
        <a href="#" class="btn btn-100 btn-outline-default <?=$topProductsCount>6?'':'disabled'?>" id="product-more">ПОКАЗАТЬ ЕЩЕ</a>  
      </div>   
    </div>
    <div class="row">
      <div class="col-md-12 min-padding">
        <h3 class="pull-left">Поставщики</h3>  
        <a href="<?=Url::to(['/site/suppliers']);?>" class="pull-right text-success all-supplier-view">Все поставщики</a>
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
          <?=empty($row->vendor->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
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
                <a href="#" class="btn btn-100 btn-success invite-vendor" data-vendor-id="<?= $row->id ?>" style="width: 100%">ДОБАВИТЬ</a>
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
        <a href="#" class="btn btn-100 btn-outline-default <?=$topSuppliersCount>6?'':'disabled'?>" id="supplier-more">Показать еще</a>  
      </div>   
    </div>
  </div> 
</div> 
<?php $customJs = <<< JS
var num = 6;
$('#product-more').on("click", function (e) {
    e.preventDefault();
    $('#product-more').addClass('disabled');
    console.log('product click more');
    $.ajax({
      url: "index.php?r=site/ajax-product-more",
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
      url: "index.php?r=site/ajax-supplier-more",
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
