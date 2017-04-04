<?php
use yii\widgets\Breadcrumbs;
$this->title = $title;
?>
<div class="row">
    <div class="col-md-12 no-padding">
        <?=Breadcrumbs::widget($breadcrumbs)?>
    </div>
</div>
<div class="row">
    <div class="col-md-12 no-padding">
      <h3><?=$title?><small></small></h3>
      <h5><?=$message?></h5>
    </div>
</div>

