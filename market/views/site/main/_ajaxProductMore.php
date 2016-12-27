<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
?>
<?php
foreach($pr as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
    <div class="mp-product-block">
        <img class="product-image" src="<?= $row->imageUrl ?>">
      <div class="row">
        <div class="col-md-12">
          <div class="product-title">
             <h3><?=$row->product; ?></h3>
          </div>
          <div class="product-category">
              <h5><?= \common\models\CatalogBaseGoods::getCurCategory($row->category_id)->name; ?>/<?=$row->subCategory->name; ?></h5>
          </div>
          <div class="product-company">
             <h5><?=$row->vendor->name; ?></h5>
          </div>
        </div>
        <div class="col-md-12">
          <div class="product-price">
              <h4><?=floatval($row->price); ?> <small>руб.</small></h4>
          </div>

        </div>
        <div class="col-md-12">
          <div class="product-button">
            <a href="#" class="btn btn-sm btn-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;КУПИТЬ</a>
          </div>  
        </div>
      </div>
    </div>  
</div>    
<?php    
}
?>
