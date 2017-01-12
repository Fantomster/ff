<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
?>

<?php
$this->title = 'F-MARKET результаты поиска';
?>
<div class="row">
    <div class="col-md-12">
      <h3>Продукты <small><?=$count?></small></h3>
     <div class="row" id="mp-product-block">
      <?php 
        foreach($products as $row){
        ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-product-block">
                <a href="<?=Url::to(['/site/product', 'id' => $row->product_id]);?>">
                <img class="product-image" src="<?=!empty($row->product_image) ? 
        $row->product_image: \common\models\ES\Product::putNoImage(); ?>">
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
        url: "index.php?r=site/ajax-es-product-more",
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
      url: "index.php?r=site/ajax-es-product-more",
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