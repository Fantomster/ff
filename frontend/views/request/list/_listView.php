<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<div class="col-md-12 req-items" data-url="<?=Url::to(["request/view", 'id' => $model->id])?>">
  <div class="row">
    <div class="col-md-6">
      <span class="req-name">№<?=$model->id;?> <?=$model->product?></span>
      <?php if ($model->rush_order){?>
      <span class="req-fire"><i class="fa fa-fire" aria-hidden="true"></i> <?= Yii::t('message', 'frontend.views.request.urgent', ['ru'=>'СРОЧНО']) ?></span>
      <?php } ?>
    </div>
    <div class="col-md-6">
      <div class="pull-right">
          <?php if (!$model->active_status){?>
          <span class="btn btn-danger btn-sm" style="font-size: 11px;padding: 0px 4px;margin-right:5px"><?= Yii::t('message', 'frontend.views.request.closed_two', ['ru'=>'Закрыта']) ?></span>
          <?php }?>
          <span class="req-cat"><?= Yii::t('message', 'frontend.views.request.category_three', ['ru'=>'Категория:']) ?></span>
          <span class="req-cat-name"> <b><?= isset($model->categoryName) ? Yii::t('app', $model->categoryName->name) : '' ?></b></span>
          <span class="req-nal-besnal"><i class="fa fa-money" aria-hidden="true"></i> 
              <b>
              <?=$model->paymentMethodName ?>
              </b>
          </span>
      </div>
    </div>
  </div>
  <div class="row" style="margin-top:5px;margin-bottom:5px;">
    <div class="col-md-12">  
        <span class="req-discription">
            <?=$model->comment?$model->comment:'<span style="color:#ccc">' . Yii::t('message', 'frontend.views.request.no_info_three', ['ru'=>'Нет информации']) . ' </span>' ?>
        </span>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <span class="req-created">
          <?= Yii::t('message', 'frontend.views.request.created_three', ['ru'=>'Создан:']) ?>
      </span>
        <span class="req-created"><b><?=Yii::$app->formatter->format($model->created_at, 'datetime') ?></b></span>
      
      <span class="req-created" style="margin-left:20px">  
          <?= Yii::t('message', 'frontend.views.request.executor_three', ['ru'=>'Исполнитель:']) ?>
      </span>
        <span class="req-created">
          <b>
            <?=$model->responsible_supp_org_id ? $model->vendor->name : '<span style="color:#ccc">' . Yii::t('message', 'frontend.views.request.not_set_two', ['ru'=>'не назначен']) . ' </span>';?>
          </b>
        </span>
    </div>
          
    <div class="col-md-4">
      <div class="pull-right">
        <span class="req-visits">
          <i class="fa fa-eye" aria-hidden="true"></i> <?=$model->counter?>
        </span>
        <span class="req-comments">
          <i class="fa fa-commenting-o" aria-hidden="true"></i> <?=$model->countCallback?>
        </span>
      </div>
    </div>
  </div>
</div>
