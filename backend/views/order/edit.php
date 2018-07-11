<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii2assets\pdfjs\PdfJs;
use yii\widgets\Pjax;

$this->registerCss('.container{width:100% !important;}');
?>

<div class="row">
    <div class="col-md-5">
        test
    </div>
    <div class="col-md-7">
        <?php Pjax::begin(['enablePushState' => true, 'timeout' => 10000, 'id' => 'attachment',]); ?>
        <div class="row">
            <div class="col-md-12">
                <?php
                foreach ($order->attachments as $attachment) {
                    echo Html::a($attachment->file, Url::to(['order/edit', 'id' => $order->id, 'attachment_id' => $attachment->id]), ['style' => 'border: 1px solid;padding:5px;']) . "&nbsp;";
                }
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="margin-top: 20px;">
                <?php
                if (isset($currentAttachment)) {
                    if (substr($currentAttachment->file, -4) === ".pdf") {
                        ?>
                        <?= PdfJs::widget(['url' => Url::to(['order/get-attachment', 'id' => $currentAttachment->id])]) ?>
                    <?php } else { ?>
                        <img style="max-width: 100%;" src="<?= Url::to(['order/get-attachment', 'id' => $currentAttachment->id]) ?>" />
                    <?php } ?>
                <?php } ?>
                <?php Pjax::end(); ?>
            </div>
        </div>
    </div>
</div>