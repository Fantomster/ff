<?php
use yii\helpers\Html;
use yii\helpers\Url;
$delivery = $model->organization->delivery;
?>
<div class="col-md-12" style="padding:15px;border-radius:3px;background:#efefef;margin-bottom:15px;">
    <div class="row">
        <div class="col-md-8">
          <div class="media">
            <div class="media-left">
              <img src="<?=$model->supp_org_id?>" class="media-object" style="width:100px">
            </div>
            <div class="media-body">
              <h5 class="media-heading"><?=$model->organization->name?></h5>
              <p><?=$model->comment?></p>
            </div>
          </div>  
        </div>
        <div class="col-md-4">
            <div class="">Стоимость доставки <span class=""><?=$delivery->delivery_charge ?></span></div>
            <div class="">Бесплатная доставка от <span class=""><?=$delivery->min_free_delivery_charge ?></span></div>
            <div class="">Минимальный заказ <span class=""><?=$delivery->min_order_price ?></span></div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
        <?= Html::button('Назначить исполнителем', ['class' => 'change btn btn-sm btn-success','data-supp-id'=>$model->supp_org_id,'data-req-id'=>$model->request_id]) ?>
        </div>
    </div>
</div>
