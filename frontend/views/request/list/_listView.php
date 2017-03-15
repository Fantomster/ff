<?php
use yii\helpers\Html;
?>
<div class="col-md-12 req-items">
  <div class="row">
    <div class="col-md-8">
      <span class="req-name"><?=$model->name?></span>
      <span class="req-fire"><i class="fa fa-fire" aria-hidden="true"></i> СРОЧНО</span>
    </div>
    <div class="col-md-4">
      <div class="pull-right" style="margin-top:10px">
          <span class="req-cat">категория:</span><span class="req-cat-name"> <b>овощи</b></span>
          <span class="req-nal-besnal"><i class="fa fa-money" aria-hidden="true"></i> <b>наличный расчет</b></span>
      </div>
    </div>
  </div>
  <div class="row" style="margin-top:15px;margin-bottom:15px;">
    <div class="col-md-12">  
        <span class="req-discription">
            Нужно привезти Азербайджанские помидоры в Ресторан "МаммаМия"
        </span>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <span class="req-created">
          Заказ создан: 
      </span>
        <span class="req-created"><b><?=$model->created_at?></b></span>
    </div>
    <div class="col-md-4">
      <div class="pull-right">
        <span class="req-visits">
          <i class="fa fa-eye" aria-hidden="true"></i> 1800
        </span>
        <span class="req-comments">
          <i class="fa fa-commenting-o" aria-hidden="true"></i> 18
        </span>
      </div>
    </div>
  </div>
</div>