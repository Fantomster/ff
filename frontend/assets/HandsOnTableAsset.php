<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class HandsOnTableAsset extends AssetBundle {

    public $basePath = '@webroot/modules/handsontable/dist';
    public $baseUrl = '@web/modules/handsontable/dist';
    public $css = [
        'handsontable.full.min.css',
//        'bootstrap.css',
        'chosen.css',
        'pikaday/pikaday.css'
    ];
    public $js = [
        'pikaday/pikaday.js',
        'moment/moment.js',
        'numbro/numbro.min.js',
        'numbro/languages.min.js',
        'zeroclipboard/ZeroClipboard.js',
        'handsontable.min.js',
        'handsontable-chosen-editor.js',
        'chosen.jquery.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
