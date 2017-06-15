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
    ];
    public $depends = [
        'frontend\assets\HelpersAsset',
    ];
}
