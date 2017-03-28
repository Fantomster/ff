<?php
use yii\helpers\Html;

?>
<div class="col-md-12 req-items">
  <div class="row">
    <div class="col-md-6">
      <span class="req-name"><?=$request->product?></span>
      <?php if ($request->rush_order){?>
      <span class="req-fire"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</span>
      <?php } ?>
    </div>
    <div class="col-md-6">
      <div class="pull-right" style="margin-top:10px">
          <span class="req-cat">категория:</span><span class="req-cat-name"> <b><?=$request->categoryName->name ?></b></span>
          <span class="req-nal-besnal"><i class="fa fa-money" aria-hidden="true"></i> 
              <b>
              <?=$request->payment_method == \common\models\Request::NAL ? 
              'Наличный расчет':
              'безналичный расчет';?>
              </b>
          </span>
      </div>
    </div>
  </div>
  <div class="row" style="margin-top:15px;margin-bottom:15px;">
    <div class="col-md-12">  
        <span class="req-discription">
            <?=$request->comment?$request->comment:'<span style="color:#ccc">Нет информации</span>' ?>
        </span>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <span class="req-created">
          Создан: 
      </span>
        <span class="req-created"><b><?=$request->modifyDate ?></b></span>
      <span class="req-created" style="margin-left:20px">  
          Исполнитель: 
      </span>
        <span class="req-created">
          <b>
            <?=$request->responsible_supp_org_id ? $request->organization->name : 'не назначен';?>
          </b>
        </span>
    </div>
          
    <div class="col-md-4">
      <div class="pull-right">
        <span class="req-visits">
          <i class="fa fa-eye" aria-hidden="true"></i> <?=$request->counter?>
        </span>
        <span class="req-comments">
          <i class="fa fa-commenting-o" aria-hidden="true"></i> 18
        </span>
      </div>
    </div>
  </div>
</div>