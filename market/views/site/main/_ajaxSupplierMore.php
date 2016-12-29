<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
?>
<?php
foreach($sp as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
<div class="mp-suppiler-block">
    <a href="<?=Url::to(['/site/supplier', 'id' => $row->vendor->id]);?>">
  <img class="supplier-image" src="<?= $row->imageUrl ?>">
    </a>
  <div class="row">
    <div class="col-md-12">
      <div class="supplier-title">
          <a href="<?=Url::to(['/site/supplier', 'id' => $row->vendor->id]);?>">
            <h3><?=$row->vendor->name;?></h3>
          </a>
      </div>
      <div class="supplier-category">
        <h5><?=!empty($row->vendor->city) ? $row->vendor->city : '&nbsp;';?></h5>
      </div>
    </div>
    <div class="col-md-12">
      <div class="supplier-button">
        <a href="#" class="btn btn-success" style="width: 100%">Добавить</a>
      </div>
    </div>
  </div>
</div>  
</div>    
<?php    
}
?> 
