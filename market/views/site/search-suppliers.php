<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use common\models\ES\Supplier;
?>

<?php
$this->title = 'F-MARKET Поставщики';
?>
<div class="row">
    <div class="col-md-12">
      <h3>Поставщики <small><?=$count?></small></h3>
     <div class="row" id="mp-product-block">
      <?php
foreach($sp as $row){
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
<div class="mp-suppiler-block  animated fadeIn">
    <div class="mp-rating">
        <div class="Fr-star size-3" data-title="<?=number_format($row->supplier_rating / (Supplier::MAX_RATING/5),1)?>" data-rating="<?=number_format($row->supplier_rating / (Supplier::MAX_RATING/5),1)?>">
            <div class="Fr-star-value" style="width:<?=($row->supplier_rating / (Supplier::MAX_RATING/5))/5*100?>%"></div>
            <div class="Fr-star-bg"></div>
        </div>
    </div>
    <?=empty($row->supplier_partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
    <a href="<?=Url::to(['/site/supplier', 'id' => $row->supplier_id]);?>">
  <img class="supplier-image" src="<?=!empty($row->supplier_image) ? 
        $row->supplier_image : \common\models\Organization::DEFAULT_VENDOR_AVATAR; ?>">
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
        <a href="#" class="btn btn-100 btn-success invite-vendor" style="width: 100%" data-vendor-id="<?= $row->supplier_id ?>">Добавить</a>
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
        <a href="#" class="btn btn-100 btn-outline-default <?=$count>12?'':'disabled'?>" id="product-more">Показать еще</a>  
      </div>   
    </div>
  </div>
</div>

<?php $customJs = <<< JS
$('#search').val('$search');        
var inProgress = false;
var num = 12;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#product-more').addClass('disabled');
      $.ajax({
        url: "index.php?r=site/ajax-es-supplier-more",
        type: "GET",
        data: {"num": num, "search":"$search"},
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
      url: "index.php?r=site/ajax-es-supplier-more",
      type: "GET",
      data: {"num": num, "search":"$search"},
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
JS;
$this->registerJs($customJs, View::POS_READY);
?>