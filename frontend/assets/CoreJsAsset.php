<?php
 
namespace frontend\assets;
 
class CoreJsAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/core.js/client';
    public $js = [
        'core.min.js'
    ];
}