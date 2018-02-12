<?php
namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Description of UserAsset
 *
 * @author sharaf
 */
class InfoPopupAsset extends AssetBundle {
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/info_popup.css', 
    ];
    public $depends = [
        'frontend\assets\HelpersAsset',
    ];
}
