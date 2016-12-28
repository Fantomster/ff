<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$addAction = Url::to(["site/ajax-add-to-cart"]);

$this->title = 'F-MARKET главная';

$js = <<<JS
        $(document).on("click", ".add-to-cart", function(e) {
            e.preventDefault();
            //alert($(this).data("product-id"));
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
<div class="row">
  <div class="col-md-12 min-padding">
    <h3>Популярные товары</h3>  
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="row" id="product-block">
        <?php
        foreach($topProducts as $row){
        ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
            <div class="mp-product-block">
                <img class="product-image" src="<?= $row->imageUrl ?>">
              <div class="row">
                <div class="col-md-12">
                  <div class="product-title">
                     <h3><?=$row->product; ?></h3>
                  </div>
                  <div class="product-category">
                      <h5><?= \common\models\CatalogBaseGoods::getCurCategory($row->category_id)->name; ?>/<?=$row->subCategory->name; ?></h5>
                  </div>
                  <div class="product-company">
                     <h5><?=$row->vendor->name; ?></h5>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="product-price">
                      <h4><?=floatval($row->price); ?> <small>руб.</small></h4>
                  </div>
                  
                </div>
                <div class="col-md-12">
                  <div class="product-button">
                    <a href="#" class="btn btn-sm btn-cart add-to-cart" data-product-id="<?= $row->id ?>"><isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;КУПИТЬ</a>
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
      <div class="col-md-12 min-padding" style="margin-bottom: 10px">
        <a href="#" class="btn btn-outline-ajax <?=$topProductsCount>6?'':'disabled'?>" id="product-more">Показать еще</a>  
      </div>   
    </div>
    <div class="row">
      <div class="col-md-12 min-padding">
        <h3>Поставщики</h3>  
      </div>
    </div>
    <div class="row" id="supplier-block">
        <?php
        foreach($topSuppliers as $row){
        ?>
        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-suppiler-block">
          <img class="supplier-image" src="<?= $row->imageUrl ?>">
          <div class="row">
            <div class="col-md-12">
              <div class="supplier-title">
                <h3><?=$row->vendor->name;?></h3>
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
    </div>
    <div class="row">
      <div class="col-md-12 min-padding">
        <a href="#" class="btn btn-outline-ajax <?=$topSuppliersCount>6?'':'disabled'?>" id="supplier-more">Показать еще</a>  
      </div>   
    </div>
  </div> 
</div> 
<?php $customJs = <<< JS
var num = 6;
$('#product-more').on("click", function (e) {
    e.preventDefault();
    $('#product-more').addClass('disabled');
    console.log('product click more');
    $.ajax({
      url: "index.php?r=site/ajax-product-more",
      type: "GET",
      data: {"num": num},
      cache: false,
      success: function(response){
          if(response == 0){
             //alert("Больше нет записей");
          }else{
             $("#product-block").append(response);
             num = num + 6;
             $('#product-more').removeClass('disabled');
          }
       }
    });
});
$('#supplier-more').on("click", function (e) {
    e.preventDefault();
    console.log('supplier click more');
    $.ajax({
      url: "index.php?r=site/ajax-supplier-more",
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
