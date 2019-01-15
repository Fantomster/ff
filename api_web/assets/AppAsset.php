<?php

namespace api_web\assets;

use yii\web\AssetBundle;

/**
 * Main api application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $sourcePath = '@api_web';

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset'
    ];
}
