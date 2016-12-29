<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
?>

<?php
$this->title = 'F-MARKET фильтр поиска';

?>
<div class="row">
  <div class="col-md-12">
      <h3>Продукты категории <small><?=\common\models\MpCategory::getCategory($id)?></small></h3>
     <div class="row" id="mp-product-block">
      <?php
        foreach($products as $row){
        ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-product-block">
                <a href="<?=Url::to(['/site/product', 'id' => $row->id]);?>">
                <img class="product-image" src="<?=!empty($row->image) ? $row->imageUrl: \common\models\ES\Product::putNoImage(); ?>">
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
                    <a href="#" class="btn btn-sm btn-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc> КУПИТЬ</a>
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
        <a href="#" class="btn btn-outline-ajax <?=$count>6?'':'disabled'?>" id="product-more">Показать еще</a>  
      </div>   
    </div>
  </div>
</div>
<?php $customJs = <<< JS
var inProgress = false;
var num = 6;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#product-more').addClass('disabled');
      $.ajax({
        url: "index.php?r=site/ajax-product-cat-loader",
        type: "GET",
        data: {"num": num, "category":$id},
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
      url: "index.php?r=site/ajax-product-cat-loader",
      type: "GET",
      data: {"num": num, "category":$id},
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