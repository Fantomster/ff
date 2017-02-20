<?php

namespace franchise\assets;

use yii\web\AssetBundle;

/**
 * Main franchise application asset bundle.
 */
class LandingAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700&amp;amp;subset=cyrillic-ext',
        'css/style-plugins.min.css',
        'css/style.min.css',
    ];
    public $js = [
        'https://api-maps.yandex.ru/2.1/?lang=ru_RU',
        'js/all.min.js',
        'js/animateNumber.js',
        'js/jquery.inputmask.bundle.min.js',
        'js/jquery.magnific-popup.min.js',
        'js/jquery.mask.min.js',
        'js/owl.carousel.js',
        'js/sweetalert.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
