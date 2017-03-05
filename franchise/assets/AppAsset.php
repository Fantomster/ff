<?php

namespace franchise\assets;

use yii\web\AssetBundle;

/**
 * Main franchise application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/fonts.css',
        'css/franchise.css',
        'css/new.css',
        'css/custom.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'frontend\assets\BootboxAsset',
        '\rmrevin\yii\fontawesome\AssetBundle',
        'yii\bootstrap\BootstrapAsset',
    ];
}
