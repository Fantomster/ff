<?php
/* @var $this yii\web\View */
/* @var $cb string */
/* @var $code string */
/* @var $waybill \common\models\Waybill */

// http://apidocs.ucs.ru/doku.php/whiteserver:api:sh_doc_receiving_report

$autoNumber = 'textcode="' . $waybill->outer_number_code . '" numcode="' . $waybill->outer_number_additional . '" ';
?><?= '<' ?>?xml version = "1.0" encoding = "utf-8"?>
<RQ cmd="sh_doc_receiving_report" tasktype="any_call" guid="<?= $guid ?>" callback="<?= $cb ?>">
    <PARAM name="object_id" val="<?= $code ?>"/>
    <DOC date="<?= Yii::$app->formatter->asDatetime($waybill->doc_date, "php:Y-m-d") ?>"
         corr="<?= $agentUid ?>"
         store="<?= $storeUid ?>"
         active="<?= $exportApproved ?>"
         duedate="1" note="<?= $waybill->outer_note ?>"
        <?= $autoNumber ?>
    <?= '>' . PHP_EOL ?>
    <?php
    foreach ($records as $rec) {
        echo '<ITEM rid="' . $rec['product_rid'] . '" quant="' . ($rec["quantity_waybill"] * 1000) . '" mu="' . $rec["unit_rid"] . '" sum="' . ($rec['sum_without_vat'] * 100) . '" vatrate="' . ($rec['vat_waybill']*100) . '" />' . PHP_EOL;
    }
    echo '</DOC>' . PHP_EOL;
    ?>
</RQ>