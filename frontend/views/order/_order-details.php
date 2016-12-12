<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\TouchSpin;
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title">Подробности о товаре</h4>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <?= $baseProduct->product ?>
            <img src="<?= $baseProduct->imageUrl ?>" width="200px" height="200px" />
        </div>
    </div> 
</div>
<div class="modal-footer">
    <a href="#" class="btn btn-gray" data-dismiss="modal"><i class="icon fa fa-ban"></i> Закрыть</a>
</div>