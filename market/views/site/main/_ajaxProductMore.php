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
              <h4><?=floatval($row->price); ?> <small>руб.</small></h4>
          </div>

        </div>
        <div class="col-md-12">
          <div class="product-button">
            <a href="#" class="btn btn-100 btn-outline-success add-to-cart" data-product-id="<?= $row->id ?>"><isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;КУПИТЬ</a>
          </div>  
        </div>
      </div>
    </div>  
</div>    
<?php    
}
?>
