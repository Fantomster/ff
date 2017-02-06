<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>
<?php
foreach($restaurants as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
<div class="mp-suppiler-block  animated fadeIn">
    <a href="<?=Url::to(['/site/restaurant', 'id' => $row->organization->id]);?>">
  <img class="supplier-image animated fadeInUp" src="<?= empty($row->organization->picture) ? \common\models\Organization::DEFAULT_RESTAURANT_AVATAR : $row->organization->pictureUrl ?>">
    </a>
  <div class="row">
    <div class="col-md-12">
      <div class="supplier-title">
          <a href="<?=Url::to(['/site/restaurant', 'id' => $row->organization->id]);?>">
            <h3><?=$row->organization->name;?></h3>
          </a>
      </div>
      <div class="supplier-category">
        <h5><?=!empty($row->organization->city) ? $row->organization->city : '&nbsp;';?></h5>
      </div>
    </div>
    <div class="col-md-12">
      <div class="supplier-button">
        <a href="#" class="btn btn-100 btn-success send-service" style="width: 100%">Предложить услуги</a>
      </div>
    </div>
  </div>
</div>  
</div>    
<?php    
}
?> 