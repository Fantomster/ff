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
                  <?php $model->request->responsible_supp_org_id == $model->supp_org_id? 
            $n = ['value'=>'Убрать исполнителя',
                'class'=>'btn btn-danger',
                'event'=>'exclude']: 
            $n =['value'=>'Назначить исполнителем',
                'class'=>'btn btn-success',
                'event'=>'appoint'];?>
            <?=Html::button($n['value'], ['class' => 'change pull-right ' . $n['class'],
                'style'=>'font-size:16px;margin-top:-10px;margin-right:10px',
                'data-supp-id'=>$model->supp_org_id,
                'data-req-id'=>$model->request_id,
                'data-event'=>$n['event']]) ?>
                 <?php 
                 if($model->request->responsible_supp_org_id == $model->supp_org_id){
                   if(!common\models\RelationSuppRest::find()->where([
                       'supp_org_id'=>$model->supp_org_id,
                       'rest_org_id'=>$model->request->rest_org_id])->exists()){
                       $n = ['value'=>'Добавить поставщика',
                        'class'=>'btn btn-success pull-right add-supplier',
                        'event'=>'add-supplier'];
                       }else{
                       $n = ['value'=>'Поставщик добавлен',
                        'class'=>'btn btn-gray pull-right disabled',
                        'event'=>''];    
                       }
                       echo Html::button($n['value'], ['class' => $n['class'],
                'style'=>'font-size:16px;margin-top:-10px;margin-right:10px',
                'data-supp-id'=>$model->supp_org_id,
                'data-req-id'=>$model->request_id,
                'data-event'=>$n['event']]);
                 }
                 ?>
                  <a href="#" class="btn btn-gray pull-right disabled" style="font-size:16px;margin-top:-10px;margin-right:10px"><i class="fa fa-comment"></i></a>
              </h4>
              <h5>Стоимость услуги: <span class="text-bold"><?=$model->price?> руб.</span></h5>
              <p><b>Комментарий поставщика:</b> <?=$model->comment?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
