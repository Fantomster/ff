<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

foreach($suppliers as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
<div class="mp-suppiler-block  animated fadeIn">
    <div class="mp-rating">
        <div class="Fr-star size-3" data-title="<?=$row->ratingStars?>" data-rating="<?=$row->ratingStars?>">
            <div class="Fr-star-value" style="width:<?=$row->ratingPercent?>%"></div>
            <div class="Fr-star-bg"></div>
        </div>
    </div>
    <?=empty($row->whiteList->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
    <a href="<?=Url::to(['/site/supplier', 'id' => $row->id]);?>">
  <img class="supplier-image animated fadeInUp" src="<?= empty($row->picture) ? \common\models\Organization::DEFAULT_VENDOR_AVATAR : $row->pictureUrl ?>">
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
        <a href="#" class="btn btn-100 btn-success invite-vendor" data-vendor-id="<?= $row->id ?>" style="width: 100%">Добавить</a>
      </div>
    </div>
  </div>
</div>  
</div>    
<?php    
}

