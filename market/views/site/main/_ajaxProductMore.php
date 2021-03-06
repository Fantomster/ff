<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
?>
<?php
foreach ($pr as $row) {
    ?>
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 min-padding">
        <div class="mp-product-block animated fadeIn">
            <div class="mp-rating">
                <div class="Fr-star size-3" data-title="<?= $row->ratingStars ?>" data-rating="<?= $row->ratingStars ?>">
                    <div class="Fr-star-value" style="width:<?= $row->ratingPercent ?>%"></div>
                    <div class="Fr-star-bg"></div>
                </div>
            </div>
            <?= empty($row->vendor->partnership) ? '' : '<div class="pro-partner">PRO</div>' ?>
            <a href="<?= Url::to(['/site/product', 'id' => $row->id]); ?>">
                <img class="product-image animated fadeInUp" src="<?= $row->imageUrl ?>">
            </a>
            <div class="row">
                <div class="col-md-12">
                    <div class="product-title">
                        <a href="<?= Url::to(['/site/product', 'id' => $row->id]); ?>"><h3><?= Html::decode(Html::decode($row->product)) ?></h3></a>
                    </div>
                    <div class="product-category">
                        <h5><?= Yii::t('app', \common\models\CatalogBaseGoods::getCurCategory($row->category_id)->name); ?>/<?= Yii::t('app', $row->subCategory->name); ?></h5>
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
                            <h4 style="color:#dfdfdf"><?= Yii::t('message', 'market.views.site.main.price_two', ['ru'=>'договорная цена']) ?></h4>
                        <?php } else { ?>
                            <h4><?= number_format($row->price, 2, '.', ''); ?> <small><?= $row->catalog->currency->symbol ?></small></h4>
                        <?php } ?>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="product-button">
                        <a href="#" class="btn btn-100 btn-outline-success add-to-cart" data-product-id="<?= $row->id ?>"><isc class="icon-shopping-cart" aria-hidden="true"></isc>&nbsp;&nbsp;<?= Yii::t('message', 'market.views.site.main.buy_two', ['ru'=>'КУПИТЬ']) ?></a>
                    </div>  
                </div>
            </div>
        </div>  
    </div>    
    <?php
}
?>
