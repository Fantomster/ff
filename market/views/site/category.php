<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Breadcrumbs;

common\assets\ReadMoreAsset::register($this);

$this->title = $category->title;
$this->registerMetaTag([
    'name' => 'description',
    'content' => $category->description,
]);
$this->registerMetaTag([
    'name' => 'keywords',
    'content' => $category->keywords,
]);
?>
<style>
    .filter{
    margin: 24px 0 12px 0;
    color:#76aa69;
    border-bottom: 1px dotted;
    float: right;
    margin-left:15px;
    }  
    @media (max-width: 767px){
        .filter{
        margin: -10px 0 15px 0;
        float: none;
        }     
    }
    .filter:hover,.filter:focus{text-decoration:none;color:#84bf76;}
    .caret.down {
        border-bottom: 4px dashed;
        border-top:0;
    }
    .caret.up {
        border-top: 4px dashed;
        border-bottom:0;
    }
</style>
<div class="row">
      <div class="col-xs-12 col-md-6 col-sm-6 min-padding">
      <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'homeLink' => false,
            'links' => [
                \common\models\MpCategory::getCategory($category->parent),
                \common\models\MpCategory::getCategory($category->id),
            ],
        ])
      ?>
    </div>
    <div class="col-xs-12 col-md-6 col-sm-6 min-padding">
        <?php
        $caretRating = "down"; $caretPrice = "down";
        if($filter == 'rating-up'){$caretRating = "up"; $caretPrice = "up";}
        if($filter == 'rating-down'){$caretRating = "down"; $caretPrice = "up";}
        if($filter == 'price-up'){$caretRating = "up"; $caretPrice = "up";}
        if($filter == 'price-down'){$caretRating = "up"; $caretPrice = "down";}
        echo "<a href=" . Url::to(['/site/category', 'slug' => $category->slug, 'filter' => 'rating-' . $caretRating]) . " class='filter'>" . Yii::t('message', 'market.views.site.category.rating', ['ru'=>'Рейтинг']) . "  <span class='caret " . $caretRating . "'></span></a>";
        echo "<a href=" . Url::to(['/site/category', 'slug' => $category->slug, 'filter' => 'price-' . $caretPrice]) . " class='filter'>" . Yii::t('message', 'market.views.site.category.price', ['ru'=>'Цена']) . "  <span class='caret " . $caretPrice . "'></span></a>";
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="category-text"><?= $category->text ?></div>
    </div>
</div>
<div class="row">
  <div class="col-md-12">
     <div class="row" id="mp-product-block">
      <?php
        foreach($products as $row){
        ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-product-block">
                <div class="mp-rating">
                    <div class="Fr-star size-3" data-title="<?=$row->ratingStars?>" data-rating="<?=$row->ratingStars?>">
                        <div class="Fr-star-value" style="width:<?=$row->ratingPercent?>%"></div>
                        <div class="Fr-star-bg"></div>
                    </div>
                </div>
                <?=empty($row->vendor->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
                <a href="<?=Url::to(['/site/product', 'id' => $row->id]);?>">
                <img class="product-image" src="<?= $row->imageUrl ?>">
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
                      <h4 style="color:#dfdfdf"><?= Yii::t('message', 'market.views.site.category.price_two', ['ru'=>'договорная цена']) ?></h4>
                      <?php } else {?>
                      <h4><?=floatval($row->price); ?> <small><?= $row->catalog->currency->symbol ?></small></h4>
                      <?php } ?>
                  </div>                 
                </div>
                <div class="col-md-12">
                  <div class="product-button">
                      <a href="#" class="btn btn-100 btn-outline-success add-to-cart" data-product-id="<?= $row->id ?>"><isc class="icon-shopping-cart" aria-hidden="true"></isc> <?= Yii::t('message', 'market.views.site.category.buy', ['ru'=>'КУПИТЬ']) ?></a>
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
        <a href="#" class="btn btn-100 btn-outline-default <?=$count>12?'':'disabled'?>" id="product-more"><?= Yii::t('message', 'market.views.site.category.show_more', ['ru'=>'Показать еще']) ?></a>
      </div>   
    </div>
  </div>
</div>
<?php 
$productCatLoaderUrl = Url::to(['site/ajax-product-cat-loader']);

$customJs = <<< JS
var inProgress = false;
var num = 12;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#product-more').addClass('disabled');
      $.ajax({
        url: "$productCatLoaderUrl",
        type: "GET",
        data: {"num": num, "category":$category->id},
        beforeSend: function() {
        inProgress = true;},
        cache: false,
        success: function(response){
            if(response == 0){
               //alert("Больше нет записей");
               $('#product-more').addClass('disabled');
               inProgress = false;
            }else{
               $("#mp-product-block").append(response);
               inProgress = false;
               num = num + 6;
               $('#product-more').removeClass('disabled');
            }
         }
      });
    }
});
$('#product-more').on("click", function (e) {
    e.preventDefault();
    $('#product-more').addClass('disabled');
    console.log('product click more');
    $.ajax({
      url: "$productCatLoaderUrl",
      type: "GET",
      data: {"num": num, "category":$category->id},
      cache: false,
      success: function(response){
          if(response == 0){
             //alert("Больше нет записей");
             $('#product-more').addClass('disabled');
          }else{
             $("#mp-product-block").append(response);
             num = num + 6;
             $('#product-more').removeClass('disabled');
          }
       }
    });
});       
$('.category-text').readmore({
    speed: 75,
    lessLink: '<a href="#" class="category-text-read-more">Свернуть</a>',
    moreLink: '<a href="#" class="category-text-read-more">Читать дальше</a>',
    collapsedHeight: 60,
});        
JS;
$this->registerJs($customJs, View::POS_READY);
?>
