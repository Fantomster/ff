<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
?>
<?php 
foreach($pr as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
    <div class="mp-product-block">
        <a href="<?=Url::to(['/site/product', 'id' => $row->product_id]);?>">
        <img class="product-image" src="<?=!empty($row->product_image) ? $row->product_image: \common\models\ES\Product::putNoImage(); ?>">
        </a>
      <div class="row">
        <div class="col-md-12">
          <div class="product-title">
              <a href="<?=Url::to(['/site/product', 'id' => $row->product_id]);?>"><h3><?=$row->product_name; ?></h3></a>
          </div>
          <div class="product-category">
              <h5><?= $row->product_category_name ?>/<?=$row->product_category_sub_name; ?></h5>
          </div>
          <div class="product-company">
              <a href="<?=Url::to(['/site/supplier', 'id' => $row->product_supp_id]);?>">
              <h5><?=$row->product_supp_name; ?></h5>
              </a>
          </div>
        </div>
        <div class="col-md-12">
          <div class="product-price">
              <?php /*if(empty($row->mp_show_price)){ ?>
              <h4 style="color:#dfdfdf">договорная цена</h4>
              <?php } else {*/?>
              <h4><?=floatval($row->product_price); ?> <small>руб.</small></h4>
              <?php /*} */?>
          </div>

        </div>
        <div class="col-md-12">
          <div class="product-button">
            <a href="#" class="btn btn-100 btn-outline-success"><isc class="icon-shopping-cart" aria-hidden="true"></isc> КУПИТЬ</a>
          </div>  
        </div>
      </div>
    </div>  
</div>    
<?php    
}
?>