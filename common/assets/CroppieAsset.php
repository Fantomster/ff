<?php

namespace common\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class CroppieAsset extends AssetBundle {

    public $sourcePath = '@npm/croppie';
    public $css = [
        'croppie.css',
    ];
    public $js = [
        'croppie.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
