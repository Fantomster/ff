<?php
/* @var $this yii\web\View */
/* @var $cb string */
/* @var $code string */
/* @var $guid string */

// http://apidocs.ucs.ru/doku.php/whiteserver:api:sh_get_stores

$xml = '<?xml version="1.0" encoding="utf-8"?>
    <RQ cmd="sh_get_stores" tasktype="any_call" guid="' . $guid . '" callback="' . $cb . '">
    <PARAM name="object_id" val="' . $code . '" />
    </RQ>';
echo $xml;