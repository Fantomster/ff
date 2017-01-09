<?php

namespace market\assets;

use yii\web\AssetBundle;

/**
 * Main market application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'fmarket/plugins/bootstrap-3.3.7/css/bootstrap.min.css',           
        'fmarket/plugins/font-awesome-4.7.0/css/font-awesome.min.css',
        'fmarket/plugins/animate/animate.css', 
        'fmarket/css/style.css', 
    ];
    public $js = [
        'fmarket/plugins/bootstrap-3.3.7/js/bootstrap.min.js',
        'fmarket/plugins/animate/wow.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
