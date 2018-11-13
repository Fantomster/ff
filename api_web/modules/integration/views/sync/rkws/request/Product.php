<?php

use api_web\modules\integration\classes\SyncLog;

/* @var $this yii\web\View */
/* @var $cb string */
/* @var $code string */
/* @var $productGroup int|null */
/* @var $group_model \common\models\OuterCategory */

SyncLog::trace('Render template: ' . __NAMESPACE__ . __FILE__);

// http://apidocs.ucs.ru/doku.php/whiteserver:api:sh_get_goods
?><?= '<' ?>?xml version = "1.0" encoding = "utf-8"?>
<RQ cmd="sh_get_goodgroups" tasktype="any_call" callback="<?= $cb ?>">
    <PARAM name="object_id" val="<?= $code ?>"/>
    <GROUPS>
        <?php foreach ($productGroup as $group_model): ?>
            <GROUP rid="<?= $group_model->outer_uid ?>" include_goods="1"/>
        <?php endforeach; ?>
    </GROUPS>
</RQ>