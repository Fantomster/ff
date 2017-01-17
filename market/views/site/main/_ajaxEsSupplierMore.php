<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>
<?php
foreach($sp as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
<div class="mp-suppiler-block  animated fadeIn">
    <a href="<?=Url::to(['/site/supplier', 'id' => $row->supplier_id]);?>">
        <img class="supplier-image" src="<?=!empty($row->supplier_image) ? 
        $row->supplier_image: \common\models\ES\Product::putNoImage(); ?>">
    </a>
  <div class="row">
    <div class="col-md-12">
      <div class="supplier-title">
          <a href="<?=Url::to(['/site/supplier', 'id' => $row->supplier_id]);?>">
            <h3><?=$row->supplier_name;?></h3>
          </a>
      </div>
      <div class="supplier-category">
        <h5><?= !empty($row->supplier_city) ? $row->supplier_city : '&nbsp;';?></h5>
      </div>
    </div>
    <div class="col-md-12">
      <div class="supplier-button">
        <a href="#" class="btn btn-100 btn-success" style="width: 100%">Добавить</a>
      </div>
    </div>
  </div>
</div>  
</div>    
<?php    
}
?> 