<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class ProgressBarAsset extends AssetBundle {

    public $basePath = '@webroot/modules/progressbar';
    public $baseUrl = '@web/modules/progressbar';
    public $css = [
        'jquery.lineProgressbar.css',

    ];
    public $js = [
        'jquery.lineProgressbar.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
