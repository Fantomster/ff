<?php
use yii\helpers\Html;
use yii\helpers\Url;
$delivery = $model->organization->delivery;
?>
<div class="col-md-12" style="padding:15px;border-radius:3px;background:#f7f7f7;margin-bottom:15px;">
    <div class="row">
        <div class="col-md-8">
          <div class="media">
            <div class="media-left">
              <img src="<?=$model->organization->pictureUrl?>" class="media-object" style="width:122px">
            </div>
            <div class="media-body" style="line-height: 1.6;">
              <div class="req-vendor-name"><?=$model->organization->name?></div>
              <div class="req-vendor-price">Цена: <span class="text-bold"><?=$model->price?></span> руб.</div>
            </div>
          </div>
          <div class="req-vendor-info"><?=$model->comment?></div>
        </div>
        <div class="col-md-4 text-right" style="line-height:1.6">
            <div class="req-client-info">Стоимость доставки <span class=""><?=$delivery->delivery_charge ?></span></div>
            <div class="req-client-info">Бесплатная доставка от <span class=""><?=$delivery->min_free_delivery_charge ?></span></div>
            <div class="req-client-info">Минимальный заказ <span class=""><?=$delivery->min_order_price ?></span></div>
        </div>
    </div>
</div>
