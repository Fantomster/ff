<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Breadcrumbs;

$this->title = $category->title;
?>
<div class="row">
    <div class="col-md-12 no-padding">
      <?=$breadcrumbs ?>
    </div>
</div>
<div class="row">
  <div class="col-md-12">
    <div class="row">
        <h3><?=$message?></h3>
    </div>
  </div>
</div>

