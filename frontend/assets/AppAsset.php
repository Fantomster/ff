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
        //'css/site.css',
        'css/custom_style.less', 
        'css/datatables.min.css',
        'css/addsupp.css',
    ];
    public $js = [
        'js/js.cookie.js',
        'js/socket.io-1.4.5.js',
        'js/main.js',
        'js/datatables.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
       // 'yii\jui\JuiAsset',
        'yii\bootstrap\BootstrapAsset',
        'frontend\assets\BootboxAsset',
        '\rmrevin\yii\fontawesome\AssetBundle',
        'common\assets\SweetAlertAsset',
    ];
}
