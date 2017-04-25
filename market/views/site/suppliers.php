<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
?>
<div class="row">
    <div class="col-md-12">
      <h3>Поставщики <small></small></h3>
        <div class="row" id="supplier-block">
            <?php
            foreach($suppliers as $row){
            ?>
            <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-suppiler-block">
                <div class="mp-rating">
                    <div class="Fr-star size-3" data-title="<?=$row->ratingStars?>" data-rating="<?=$row->ratingStars?>">
                        <div class="Fr-star-value" style="width:<?=$row->ratingPercent?>%"></div>
                        <div class="Fr-star-bg"></div>
                    </div>
                </div>
                <?=empty($row->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
              <a href="<?=Url::to(['/site/supplier', 'id' => $row->id]);?>">
                <img class="supplier-image" src="<?= empty($row->picture) ? \common\models\Organization::DEFAULT_VENDOR_AVATAR : $row->pictureUrl ?>">
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
                    <a href="#" class="btn btn-success invite-vendor" data-vendor-id="<?= $row->id ?>" style="width: 100%">Добавить</a>
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
            <a href="#" class="btn btn-outline-ajax <?=$suppliersCount>12?'':'disabled'?>" id="product-more">Показать еще</a>  
          </div>   
        </div>
    </div>
</div>

<?php 
$supplierMoreUrl = Url::to(['site/ajax-supplier-more']);

$customJs = <<< JS
var inProgress = false;
var num = 12;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#supplier-more').addClass('disabled');
      $.ajax({
        url: "$supplierMoreUrl",
        type: "GET",
        data: {"num": num},
        beforeSend: function() {
        inProgress = true;},
        cache: false,
        success: function(response){
            if(response == 0){
               //alert("Больше нет записей");
               $('#product-more').addClass('disabled');
               inProgress = false;
            }else{
               $("#supplier-block").append(response);
               inProgress = false;
               num = num + 6;
               $('#supplier-more').removeClass('disabled');
            }
         }
      });
    }
});
$('#supplier-more').on("click", function (e) {
    e.preventDefault();
    console.log('supplier click more');
    $.ajax({
      url: "$supplierMoreUrl",
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
