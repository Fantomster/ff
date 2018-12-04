<?php
/* @var $this yii\web\View */
/* @var $cb string */
/* @var $code string */

// http://apidocs.ucs.ru/doku.php/whiteserver:api:sh_get_goodgroups

?><?= '<' ?>?xml version = "1.0" encoding = "utf-8"?>
<RQ cmd="sh_get_goodgroups" tasktype="any_call" callback="<?= $cb ?>">
    <PARAM name="object_id" val="<?= $code ?>"/>
</RQ>