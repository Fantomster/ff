<?php

namespace common\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class ReadMoreAsset extends AssetBundle {

    public $sourcePath = '@npm/readmore-js';
    public $js = [
        'readmore.min.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
