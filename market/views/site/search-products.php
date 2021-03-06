<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use common\models\ES\Product;
?>

<?php
$this->title = Yii::t('message', 'market.views.site.search_prods.results', ['ru'=>'MixCart результаты поиска']);
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
                Yii::t('message', 'market.views.site.search_prods.no_goods', ['ru'=>'Товаров найдено: ']) . $count
            ],
        ])
        ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="row" id="mp-product-block">
            <?php
            foreach ($products as $row) {
                ?>
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
                    <div class="mp-product-block">
                        <div class="mp-rating">
                            <div class="Fr-star size-3" data-title="<?= number_format($row->product_rating / (Product::MAX_RATING / 5), 1) ?>" data-rating="<?= number_format($row->product_rating / (Product::MAX_RATING / 5), 1) ?>">
                                <div class="Fr-star-value" style="width:<?= ($row->product_rating / (Product::MAX_RATING / 5)) / 5 * 100 ?>%"></div>
                                <div class="Fr-star-bg"></div>
                            </div>
                        </div>
                        <?= empty($row->product_partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
                        <a href="<?= Url::to(['/site/product', 'id' => $row->product_id]); ?>">
                            <img class="product-image" src="<?=
                            !empty($row->product_image) ?
                                    $row->product_image :
                                    \market\components\ImagesHelper::getUrl($row->product_category_id);
                            ?>">
                        </a>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="product-title">
                                    <a href="<?= Url::to(['/site/product', 'id' => $row->product_id]); ?>"><h3><?= Html::decode(Html::decode($row->product_name)) ?></h3></a>
                                </div>
                                <div class="product-category">
                                    <h5><?= Yii::t('app', $row->product_category_name) ?>/<?= Yii::t('app', $row->product_category_sub_name); ?></h5>
                                </div>
                                <div class="product-company">
                                    <a href="<?= Url::to(['/site/supplier', 'id' => $row->product_supp_id]); ?>">
                                        <h5><?= $row->product_supp_name; ?></h5>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="product-price">
                                    <h4>
                                        <?php if (empty($row->product_show_price)) { ?>
                                            <h4 style="color: #dfdfdf"><?= Yii::t('message', 'market.views.site.search_prods.price', ['ru'=>'договорная цена']) ?></h4>
                                        <?php } else { ?>
                                            <h4><?= number_format($row->product_price, 2, '.', ''); ?> <small><?= $row->product_currency ?></small></h4>
                                        <?php } ?>
                                </div>

                            </div>
                            <div class="col-md-12">
                                <div class="product-button">
                                    <a href="#" class="btn btn-100 btn-outline-success add-to-cart" data-product-id="<?= $row->product_id ?>"><isc class="icon-shopping-cart" aria-hidden="true"></isc> <?= Yii::t('message', 'market.views.site.search_prods.to_buy', ['ru'=>'КУПИТЬ']) ?></a>
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
                <a href="#" class="btn btn-100 btn-outline-default <?= $count > 12 ? '' : 'disabled' ?>" id="product-more"><?= Yii::t('message', 'market.views.site.search_prods.show_more', ['ru'=>'Показать еще']) ?></a>
            </div>   
        </div>
    </div>
</div>
<?php
$esProductMoreUrl = Url::to(['site/ajax-es-product-more']);

$customJs = <<< JS
$('#search').val('$search');        
var inProgress = false;
var num = 12;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#product-more').addClass('disabled');
      $.ajax({
        url: "$esProductMoreUrl",
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
      url: "$esProductMoreUrl",
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