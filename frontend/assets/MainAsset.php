<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Description of UserAsset
 *
 * @author sharaf
 */
class MainAsset extends AssetBundle {
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
//        'css/style.css',
        'css/media.css', 
        'https://fonts.googleapis.com/css?family=Open+Sans:300,400'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapThemeAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        '\rmrevin\yii\fontawesome\AssetBundle',
        'common\assets\SweetAlertAsset',
    ];
}
