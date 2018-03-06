<?php
 
namespace common\assets;
 
class CoreJsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@npm/core-js/client';
    public $js = [
        'core.min.js'
    ];
}