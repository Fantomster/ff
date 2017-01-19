<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Breadcrumbs;

$addAction = Url::to(["site/ajax-add-to-cart"]);

$js = <<<JS
        $(document).on("click", ".add-to-cart", function(e) {
            e.preventDefault();
            $.post(
                "$addAction",
                {product_id: $(this).data("product-id")}
            ).done(function (result) {
                if (result) {
                    alert("Yes, we can!");
                } else {
                    alert("Fail!");
                }
            });
        });
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>

<?php
$this->title = 'F-MARKET фильтр поиска';

?>
<div class="row">
    <div class="col-md-12 no-padding">
      <?=
        Breadcrumbs::widget([
            'options' => [
                'class' => 'breadcrumb',
            ],
            'homeLink' => false,
            'links' => [
                \common\models\MpCategory::getCategory($category->parent),
                \common\models\MpCategory::getCategory($category->id),
            ],
        ])
      ?>
    </div>
</div>
<div class="row">
  <div class="col-md-12">
     <div class="row" id="mp-product-block">
      <?php
        foreach($products as $row){
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
                      <?php if(empty($row->mp_show_price)){ ?>
                      <h4 style="color:#dfdfdf">договорная цена</h4>
                      <?php } else {?>
                      <h4><?=floatval($row->price); ?> <small>руб.</small></h4>
                      <?php } ?>
                  </div>                 
                </div>
                <div class="col-md-12">
                  <div class="product-button">
                    <a href="#" class="btn btn-100 btn-outline-success add-to-cart"><isc class="icon-shopping-cart" aria-hidden="true"></isc> КУПИТЬ</a>
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
var inProgress = false;
var num = 12;
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