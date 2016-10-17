<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle {

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/custom_style.less', 
        'https://fonts.googleapis.com/css?family=Open+Sans'
    ];
    public $js = [
        'js/js.cookie.js',
        'js/socket.io-1.4.5.js',
        'js/main.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'frontend\assets\BootboxAsset',
        'fedemotta\datatables\DataTablesAsset',
        '\rmrevin\yii\fontawesome\AssetBundle',
        'kartik\growl\GrowlAsset',
        'kartik\base\AnimateAsset',
    ];
}
