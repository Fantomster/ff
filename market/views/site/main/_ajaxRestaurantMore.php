<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\bootstrap\Modal;
?>
<?php
foreach($restaurants as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
<div class="mp-suppiler-block  animated fadeIn">
    <a href="<?=Url::to(['/site/restaurant', 'id' => $row->id]);?>">
  <img class="supplier-image animated fadeInUp" src="<?= empty($row->picture) ? \common\models\Organization::DEFAULT_RESTAURANT_AVATAR : $row->pictureUrl ?>">
    </a>
  <div class="row">
    <div class="col-md-12">
      <div class="supplier-title">
          <a href="<?=Url::to(['/site/restaurant', 'id' => $row->id]);?>">
            <h3><?=$row->name;?></h3>
          </a>
      </div>
      <div class="supplier-category">
        <h5><?=!empty($row->city) ? $row->city : '&nbsp;';?></h5>
      </div>
    </div>
    <div class="col-md-12">
      <div class="supplier-button">
        <?=Html::a('предложить услуги', ['send-service',
                'id' => $row->id], [
                'data' => [
                    'target' => '#sendService',
                    'toggle' => 'modal',
                    'backdrop' => 'static',
                ],
                'class' => 'btn btn-success send-service',
                'style' => 'width:100%',
        ]);
        ?>
      </div>
    </div>
  </div>
</div>  
</div>    
<?php    
}
?> 
