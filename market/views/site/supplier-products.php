<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
?>

<?php
$this->title = Yii::t('message', 'market.views.site.supp_prod.vendors_prods', ['ru'=>'MixCart Продукты поставщика']);
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
                [
                    'label' => Yii::t('message', 'market.views.site.supp_prod.all_vendors', ['ru'=>'Все поставщики']),
                    'url' => ['/site/suppliers'],
                ],
                [
                    'label' => $vendor->name,
                    'url' => ['/site/supplier', 'id' => $vendor->id],
                ],
                Yii::t('message', 'market.views.site.supp_prod.catalog', ['ru'=>'Каталог']),
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
                            <div class="Fr-star size-3" data-title="<?= $row->ratingStars ?>" data-rating="<?= $row->ratingStars ?>">
                                <div class="Fr-star-value" style="width:<?= $row->ratingPercent ?>%"></div>
                                <div class="Fr-star-bg"></div>
                            </div>
                        </div>
                        <?= empty($row->vendor->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
                        <a href="<?= Url::to(['/site/product', 'id' => $row->id]); ?>">
                            <img class="product-image" src="<?= $row->imageUrl ?>">
                        </a>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="product-title">
                                    <a href="<?= Url::to(['/site/product', 'id' => $row->id]); ?>"><h3><?= Html::decode(Html::decode($row->product)) ?></h3></a>
                                </div>
                                <div class="product-category">
                                    <h5><?= \common\models\CatalogBaseGoods::getCurCategory($row->category_id)->name; ?>/<?= $row->subCategory->name; ?></h5>
                                </div>
                                <div class="product-company">
                                    <a href="<?= Url::to(['/site/supplier', 'id' => $row->vendor->id]); ?>">
                                        <h5><?= $row->vendor->name; ?></h5>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="product-price">
                                    <?php if (empty($row->mp_show_price)) { ?>
                                        <h4 style="color:#dfdfdf"><?= Yii::t('message', 'market.views.site.supp_prod.price', ['ru'=>'договорная цена']) ?></h4>
                                    <?php } else { ?>
                                        <h4><?= floatval($row->price); ?> <small><?= Yii::t('message', 'market.views.site.supp_prod.rouble', ['ru'=>'руб.']) ?></small></h4>
                                    <?php } ?>
                                </div>

                            </div>
                            <div class="col-md-12">
                                <div class="product-button">
                                    <a href="#" class="btn btn-100 btn-outline-success add-to-cart" data-product-id="<?= $row->id ?>"><isc class="icon-shopping-cart" aria-hidden="true"></isc> <?= Yii::t('message', 'market.views.site.supp_prod.buy', ['ru'=>'КУПИТЬ']) ?></a>
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
                <a href="#" class="btn btn-100 btn-outline-default <?= $productsCount > 12 ? '' : 'disabled' ?>" id="product-more"><?= Yii::t('message', 'market.views.site.supp_prod.show_more', ['ru'=>'Показать еще']) ?></a>
            </div>   
        </div>
    </div>
</div>

<?php
$suppProductMoreUrl = Url::to(['site/ajax-supp-product-more']);

$customJs = <<< JS
var inProgress = false;
var num = 12;
$(window).scroll(function() {
if($(window).scrollTop() + $(window).height() >= $(document).height() - 200 && !inProgress) {
      $('#product-more').addClass('disabled');
      $.ajax({
        url: "$suppProductMoreUrl",
        type: "GET",
        data: {"num": num, "supp_org_id":$id},
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
      url: "$suppProductMoreUrl",
      type: "GET",
      data: {"num": num, "supp_org_id":$id},
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