<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Description of UserAsset
 *
 * @author sharaf
 */
class AuthAsset extends AssetBundle {
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/auth.css', 
        'https://fonts.googleapis.com/css?family=Shadows+Into+Light',
    ];
    public $js = [
        'js/lib/modernizr-custom.js',
        'js/plugins/bs/modal.js',
        'js/separate/global.js',
        'js/helpers/resizer.js',
        'js/helpers/object-fit.js',
        'js/helpers/object-fit.js',
        'js/helpers/bs_modal_fix.js',
//        'js/components/enter.js',
        'js/components/data-modal.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapThemeAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'delocker\animate\AnimateAssetBundle',
        'frontend\assets\SlickCarouselAsset',
    ];
}
