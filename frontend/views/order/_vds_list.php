<?php
use yii\helpers\Html;
?>
<div class="row">

<?= Html::beginForm(['order/update', 'id' => 'vsd-list'], 'post') ?>

<?php foreach ($mercVSDs as $one): ?>
    <div class="form-control">
        <?= $one['product_name'] ?> | <?= $one['amount'] ?>
        <?= Html::checkbox('vst_list[' . $one['id'] . ']', true) ?>
    </div>

<?php endforeach; ?>

<?= Html::endForm(); ?>

</div>
