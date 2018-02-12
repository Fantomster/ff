<?php
namespace common\assets;

use yii\web\AssetBundle;

/**
 * Description of UserAsset
 *
 * @author sharaf
 */
class HelpersAsset extends AssetBundle {
    public $sourcePath = '@common/assets/helpers';
    public $css = [
        'https://fonts.googleapis.com/css?family=Shadows+Into+Light',
    ];
    public $js = [
        'js/modernizr-custom.js',
        'js/modal.js',
        'js/global.js',
        'js/resizer.js',
        'js/object-fit.js',
        'js/bs_modal_fix.js',
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
