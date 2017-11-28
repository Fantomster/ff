<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="col-md-12" style="padding:15px;border-radius:3px;background:#f7f7f7;margin-bottom:15px;">
  <div class="row">
    <div class="col-md-12">
      <div class="media">
        <div class="media-left">
          <img src="<?=$model->organization->pictureUrl?>" class="media-object" style="width:160px">
        </div>
        <div class="media-body" style="line-height: 1.6;">
          <div class="row">
            <div class="col-md-12" style="padding-right: 5px;">
              <h4 class="text-success"><?=$model->organization->name?> 
                <!--a href="#" class="btn btn-gray pull-right disabled" style="font-size:16px;margin-top:-10px;margin-right:10px"><i class="fa fa-comment"></i></a-->
              </h4>
              <h5><?= Yii::t('message', 'frontend.views.request.service_price_nine', ['ru'=>'Стоимость услуги:']) ?> <span class="text-bold"><?=$model->price?> <?= Yii::t('message', 'frontend.views.request.rouble_two', ['ru'=>'руб.']) ?></span></h5>
              <p><b><?= Yii::t('message', 'frontend.views.request.vendors_comment_two', ['ru'=>'Комментарий поставщика:']) ?></b> <?=$model->comment?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
