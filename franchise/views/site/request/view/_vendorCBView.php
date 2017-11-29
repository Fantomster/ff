<div class="col-md-12" style="padding:15px;border-radius:3px;background:#f7f7f7;margin-bottom:15px;">
  <div class="row">
    <div class="col-md-12">
      <div class="media">
        <div class="media-body" style="line-height: 1.6;">
          <div class="row">
            <div class="col-md-12" style="padding-right: 5px;">
              <h4 class="text-success"><?=$model->organization->name?>
              </h4>
              <h5><?= Yii::t('app', 'franchise.views.site.request.view.service_price', ['ru'=>'Стоимость услуги:']) ?> <span class="text-bold"><?=$model->price?> <?= Yii::t('app', 'franchise.views.site.view.rouble', ['ru'=>'руб.']) ?></span></h5>
              <p><b><?= Yii::t('app', 'franchise.views.site.request.view.comment', ['ru'=>'Комментарий поставщика:']) ?></b> <?=$model->comment?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
