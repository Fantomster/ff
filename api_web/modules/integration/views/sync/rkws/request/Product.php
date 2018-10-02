<?php

use api_web\modules\integration\classes\SyncLog;

/* @var $this yii\web\View */
/* @var $cb string */
/* @var $code string */
/* @var $productGroup int|null */

SyncLog::trace('Render template: ' . __NAMESPACE__ . __FILE__);

if (isset($productGroup)) {
    $productGroup = '
    <PARAM name="goodgroup_rid" val="' . $productGroup . '"/>';
} else {
    $productGroup = '';
}

// http://apidocs.ucs.ru/doku.php/whiteserver:api:sh_get_goods

?><?= '<' ?>?xml version = "1.0" encoding = "utf-8"?>
<RQ cmd="sh_get_goods" tasktype="any_call" callback="<?= $cb ?>">
    <PARAM name="object_id" val="<?= $code ?>"/><?= $productGroup ?>
</RQ>