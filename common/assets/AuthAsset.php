<?php
namespace common\assets;

use yii\web\AssetBundle;

/**
 * Description of UserAsset
 *
 * @author sharaf
 */
class AuthAsset extends AssetBundle {
    public $sourcePath = '@common/assets/auth';
    public $css = [
        'css/auth.css', 
    ];
    public $depends = [
        'common\assets\HelpersAsset',
    ];
}
