<?php

use api_web\modules\integration\classes\SyncLog;

/* @var $this yii\web\View */
/* @var $cb string */
/* @var $code string */

SyncLog::trace('Render template: ' . __NAMESPACE__ . __FILE__);

// http://apidocs.ucs.ru/doku.php/whiteserver:api:sh_doc_receiving_report

$autoNumber = 'textcode="' . $waybill->text_code . '" numcode="' . $waybill->num_code . '" ';
?><?= '<' ?>?xml version = "1.0" encoding = "utf-8"?>
<RQ cmd="sh_doc_receiving_report" tasktype="any_call" guid="<?= $guid ?>" callback="<?= $cb ?>">
    <PARAM name="object_id" val="<?= $code ?>"/>
    <DOC date="<?= Yii::$app->formatter->asDatetime($waybill->doc_date, "php:Y-m-d") ?>"
         corr="<?= $waybill->corr_rid ?>"
         store="<?= $waybill->store->rid ?>"
         active="<?= $exportApproved ?>"
         duedate="1" note="<?= $waybill->note ?>"
    <?= $autoNumber ?>
    <?= '>' . PHP_EOL ?>
    <?php
    foreach ($records as $rec) {
        echo '<ITEM rid="' . $rec['prid'] . '" quant="' . ($rec["quant"] * 1000) . '" mu="' . $rec["unit_rid"] . '" sum="' . ($rec['sum'] * 100) . '" vatrate="' . ($rec['vat']) . '" />' . PHP_EOL;
    }
    echo '</DOC>' . PHP_EOL;
    ?>
</RQ>