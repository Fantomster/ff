<?php
/* @var $this yii\web\View */
/* @var $cb string */
/* @var $code string */
/* @var $waybill \common\models\Waybill */
/* @var $exportApproved integer */
/* @var $storeUid string */
/* @var $agentUid string */
/* @var $code string */
/* @var $guid string */
/* @var $records array */
// http://apidocs.ucs.ru/doku.php/whiteserver:api:sh_doc_receiving_report
echo '<';
?>
?xml version = "1.0" encoding = "utf-8"?>
<RQ cmd="sh_doc_receiving_report" tasktype="any_call" guid="<?= $guid ?>" callback="<?= $cb ?>">
    <PARAM name="object_id" val="<?= $code ?>"/>
    <DOC date="<?= \api_web\helpers\WebApiHelper::asDatetime($waybill->doc_date) ?>"
         corr="<?= $agentUid ?>"
         store="<?= $storeUid ?>"
         active="<?= $exportApproved ?>"
         note="<?= $waybill->outer_note ?>"
         textcode="<?= $waybill->outer_number_code ?>"
         numcode="<?= $waybill->outer_number_additional ?>"
         duedate="1">
        <?php foreach ($records as $rec) : ?>
            <ITEM
                    mu="<?= $rec['mu'] ?>"
                    quant="<?= $rec['quant'] ?>"
                    rid="<?= $rec['rid'] ?>"
                    sum="<?= $rec['sum'] ?>"
                    vatrate="<?= $rec['vatrate'] ?>"
                    vatsum="<?= $rec['vatsum'] ?>"/>
        <?php endforeach; ?>
    </DOC>
</RQ>